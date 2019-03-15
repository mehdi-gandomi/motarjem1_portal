<?php
namespace App\Controllers;

use App\Models\User;
use Core\Config;
use Core\Controller;

class UserAuthController extends Controller
{

    #region Auth functions
    //////////////////////////////////////////////
    // START Customer(User) Auth Functions
    //////////////////////////////////////////////

    public function get_auth($req, $res, $args)
    {
        $data = $this->get_csrf_token($req);
        if (isset($_SESSION['is_user_logged_in'])) {
            return $res->withRedirect("/user");
        }
        if (isset($_SESSION['login_username'])) {
            $data = array_merge($data, ["login_username" => $_SESSION['login_username']]);
            unset($_SESSION['login_username']);
        }
        if (isset($_SESSION['oldSignUpFields'])) {
            $data = array_merge($data, $_SESSION['oldSignUpFields']);
            unset($_SESSION['oldSignUpFields']);
        }
        $this->view->render($res, "website/login_signup.twig", $data);
    }
    //login process of user (customer) if user is not active, an activation link will be sent to user's email
    public function post_login($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $userData = User::by_username($postFields['username'], "*");
        if ($userData) {
            if ($userData['password'] === \md5(\md5($postFields['password']))) {
                if ($userData['is_active']) {
                    $_SESSION['is_user_logged_in'] = true;
                    $_SESSION['fname'] = $userData['fname'];
                    $_SESSION['lname'] = $userData['lname'];
                    $_SESSION['avatar'] = $userData['avatar'];
                    $_SESSION['user_id'] = $userData['user_id'];
                    // $_SESSION['username'] = $userData['user_id'];
                    $_SESSION['phone'] = $userData['phone'];
                    $_SESSION['email'] = $userData['email'];
                    //user level that logged in valid values are : user,admin,translator
                    $_SESSION['user_type'] = "user";                    
                    return $res->withRedirect('/user');
                } else {
                    $this->flash->addMessage('userActivationError', "حساب کاربری شما غیرفعال می باشد ! لطفا از طریق <strong><a  onclick='sendVerificationCode(\"$postFields[username]\")'>این لینک</a></strong> آن را فعال کنید.");
                    $_SESSION['login_username'] = $postFields['username'];
                    return $res->withRedirect('/user/auth');
                }
            } else {
                $this->flash->addMessage('userLoginError', "پسورد وارد شده صحیح نمیباشد!");
                $_SESSION['login_username'] = $postFields['username'];
                return $res->withRedirect('/user/auth');
            }
        } else {
            $this->flash->addMessage('userLoginError', "نام کاربری وارد شده صحیح نمی باشد !");
            $_SESSION['login_username'] = $postFields['username'];
            return $res->withRedirect('/user/auth');
        }

    }
    // signup process for users (customer)
    public function post_signup($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $hasError = $this->valiate_user_signup($postFields);
        if ($hasError) {
            unset($postFields['csrf_value']);
            unset($postFields['csrf_name']);
            $_SESSION['oldSignUpFields'] = $postFields;
            return $res->withRedirect("/user/auth");
        } else {
            unset($postFields['csrf_value']);
            unset($postFields['csrf_name']);
            unset($postFields['confirm_password']);
            if (User::check_user_existance($postFields)) {
                $this->flash->addMessage('userSignupErrors', "با این ایمیل یا نام کاربری قبلا ثبت نام شده است !");
                unset($postFields['csrf_name']);
                unset($postFields['csrf_value']);
                $_SESSION['oldSignUpFields'] = $postFields;
                return $res->withRedirect("/user/auth");
            }
            User::create($postFields);
            $userData = User::by_username($postFields['username']);
            $verifyLink = $this->createVerifyLink($userData);
            $this->send_user_info_to_email($postFields, $verifyLink);
            $this->flash->addMessage("userSignUpLogs", "ثبت نام شما با موفقیت انجام شد ! لینک فعال سازی به ایمیل شما ارسال شد.<a style='cursor:pointer;color:#5842d4' onclick='sendVerificationCode(\"$userData[username]\",\".signupLogs\")'>ارسال مجدد</a>");
            return $res->withRedirect("/user/auth");
        }
    }
    //logout process for user
    public function logout($req, $res, $args)
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['is_user_logged_in']);
        unset($_SESSION['fname']);
        unset($_SESSION['avatar']);
        unset($_SESSION['lname']);
        unset($_SESSION['user_type']);
        unset($_SESSION['phone']);
        unset($_SESSION['email']);
        unset($_COOKIE[\session_name()]);
        // \setcookie(\session_name(), "", \time() - 3600);
        return $res->withRedirect('/');

    }
    //process and activate a user by link that sent to email
    public function verify_email($req, $res, $args)
    {
        $username = \trim(\urldecode($req->getQueryParams()["user"]));
        $userData = User::by_username($username);
        //checks if user is activated already , then send a message to the that your account is activated
        if ($userData['is_active']) {
            $this->flash->addMessage('userActivationSuccess', "اکانت شما قبلا غعال بوده است!");
            return $res->withRedirect('/user/auth');
        }

        //token from the link
        $verifyToken = trim(\urldecode($req->getQueryParams()["verify_token"]));
        //this is the token that created by database info for the user
        $createdTokenBasedOnUser = $this->createVerifyLink($userData, true);
        // var_dump($verifyToken,$createdTokenBasedOnUser);
        if ($verifyToken === $createdTokenBasedOnUser) {
            User::activate($username);
            $this->flash->addMessage('userActivationSuccess', "حساب شما با موفقیت فعال شد ! حالا می توانید وارد شوید.");
            return $res->withRedirect('/user/auth');
        } else {
            $this->flash->addMessage('userActivationError', "خطا : توکن اشتباه می باشد!");
        }
    }

//create and send a verification link to user to activate the account
    public function send_verification_email($req, $res, $args)
    {
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        $username = $args['username'];
        $token = $req->getParsedBody()['token'];
        if ($token === $hash) {
            $userData = User::by_username($username);
            if (!$userData) {
                return $res->withJson([
                    "status" => false,
                    "message" => "ایمیل وارد شده در سیستم موجود نمی باشد!",
                ]);
            }
            $verifyLink = $this->createVerifyLink($userData);
            $result = $this->send_user_info_to_email($userData, $verifyLink);
            if ($result) {
                return $res->withJson([
                    "status" => true,
                    "message" => "لینک فعال سازی به ایمیل شما ارسال شد !",
                ]);
            }
            return $res->withJson([
                "status" => false,
                "message" => "خطایی در ارسال لینک به ایمیل رخ داد !",
            ]);

        }
        return $res->withJson([
            "status" => false,
            "message" => "توکن ارسال شده نامعتبر می باشد!",
        ]);

    }

    public function get_forget_password_page($req, $res, $args)
    {
        $tokens = $this->get_csrf_token($req);
        $tokens['action']="/user/forget-password";
        $this->view->render($res, "website/forgot_password.twig", $tokens);
    }

//create password reset link and send it to user's email
    public function send_password_reset_link($req, $res, $args)
    {
        $email = $req->getParsedBody()['email'];
        $result = $this->create_password_reset_link($email, true);
        if ($result['status']) {
            // $this->send_password_reset_to_email($email, $result['link']);
            // $this->flash->addMessage('success', "لینک تغییر پسورد به ایمیل شما ارسال شد !");
            // return $res->withRedirect("/user/forget-password");
            var_dump($result['link']);
        } else {
            $this->flash->addMessage('error', $result['error']);
            return $res->withRedirect("/user/forget-password");
        }
    }

//this function gets token and validates the token and if the condition is met, then user can change his password
    public function reset_password_process($req, $res, $args)
    {
        $token = $req->getParam("token");
        $username = $req->getParam("user");
        $forgetPasswordData = \Core\Model::select("forgot_password", "*", ['token' => $token], true);
        
        
        $tokens = $this->get_csrf_token($req);
        $data=array(
            'validationErrors' => [],
            'token_is_valid' => false,
            'csrf_name' => $tokens['csrf_name'],
            'csrf_value' => $tokens['csrf_value'],
            'username'=>$username,
            'action'=>"/user/password-reset"
        );
        if ($forgetPasswordData) {
            $userData=User::by_id($forgetPasswordData['user_id'],"username");
            if($userData['username']===$username){
                if (time() < intval($forgetPasswordData['expire_date'])) {
                    $data['validationSuccess'] = "توکن معتبر می باشد حالا می توانید پسوردتان را تغییر دهید";
                    $data['token_is_valid'] = true;
                } else {
                    array_push($data['validationErrors'], "اعتبار توکن به اتمام رسیده است !");
                    \Core\Model::delete("forgot_password","token = '".$token."'");
                }
            }else{
                array_push($data['validationErrors'], "اطلاعات لینک نامعتبر می باشد !");
            }
            
        } else {
            array_push($data['validationErrors'], "توکن نامعتبر می باشد !");
        }
        return $this->view->render($res, "website/reset-password.twig", $data);
    }

//this funtion gets data from change password page and saves it to database
    public function post_change_password($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $validationErrors = [];
        $validationSuccess = "";
        $tokens = $this->get_csrf_token($req);
        $userData = User::by_username($postFields['username'], "user_id");
        if ($postFields['password'] == "") {
            array_push($validationErrors, "فیلد پسورد نباید خالی باشد !");
        }
        if ($postFields['confirmPassword'] == "") {
            array_push($validationErrors, "فیلد تایید پسورد نباید خالی باشد");
        }
        if ($postFields['password'] != $postFields['confirmPassword']) {
            array_push($validationErrors, "فیلد پسورد با تایید پسورد مطابقت ندارد");
        } else {
            try {
                User::change_password($postFields['username'], $postFields['password']);
                \Core\Model::delete("forgot_password", "user_id = '" . $userData['user_id'] . "' AND user_type='1'");
                $validationSuccess = "پسورد شما با موفقیت تغییر کرد حالا می توانید با استفاده از <a href='/user/auth'>این لینک</a> وارد شوید";
            } catch (\Exception $e) {
                array_push($validationErrors, "خطایی در تغییر پسورد رخ داد");
            }
        }
        return $this->view->render($res, "website/reset-password.twig", ['token_is_valid' => true, 'validationErrors' => $validationErrors, 'validationSuccess' => $validationSuccess, 'csrf_name' => $tokens['csrf_name'], 'csrf_value' => $tokens['csrf_value'], 'username' => $postFields['username']]);
    }

    //////////////////////////////////////////////
    // END Customer(User) Auth Functions
    //////////////////////////////////////////////

#endregion

//user auth utitlity functions

    //create email verification link to be send to user
    protected function createVerifyLink($userData, $onlyKey = false)
    {
        $verifyKey = $userData['email'] . "my name is mehdi" . $userData['phone'] . $userData['register_date'];
        $verifyKey = \sha1(\md5($verifyKey));

        if ($onlyKey) {
            return $verifyKey;
        } else {
            return Config::BASE_URL . "/user/confirm?user=" . \urlencode($userData['username']) . "&verify_token=" . \urlencode($verifyKey);
        }
    }

    protected function send_verification_mail_to_customer($userData)
    {
        $verifyLink = $this->createVerifyKey($userData);

    }

    protected function send_user_info_to_email($userInfo, $verifyLink)
    {

        $from = "support@motarjem1.com";
        $headers = "From:" . $from;
        $headers .= "Reply-To: noreply@motarjem1.com \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        if (!filter_var($userInfo['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $subject = "ثبت نام در مترجم وان";
        $userFname = $userInfo['fname'];
        $text = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='َUTF-8'>
            <style type='text/css'>
                @import('https://cdn.rawgit.com/rastikerdar/vazir-font/v19.1.0/dist/font-face.css');
            </style>
        </head>
            <body style='margin:0;padding:0;font-family: Vazir, Tahoma, DejaVu Sans, helvetica, arial, freesans, sans-serif;'>

            <div style='width:100%!important;min-width:300px;height:100%;margin:0;padding:0;line-height:1.5;color:#333;background-color:#f2f2f2'>
            <table style='width:100%;padding:30px 0 0 0'>
                <tbody>
                    <tr>
                        <td align='center'>
                            <img src='http://motarjem1.com/public/images/logo.png' class='CToWUd' />
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style='padding:5px;width:100%;max-width:620px;margin:0 auto;color:#515151'>
                <tbody>
                    <tr>
                        <td>
                            <table style='width:100%;margin:0;padding:0 0 20px'>
                                <tbody>
                                    <tr style='margin:0;padding:0'>
                                        <td style='margin:0;padding:0'>
                                            <table style='width:100%;max-width:620px;padding:30px;margin:20px auto 5px;background-color:#fff;border-radius:4px;text-align:right'>
                                                <tbody>
                                                    <tr style='width:100%'>
                                                        <td style='width:100%'>
                                                            <h2 style='font-size:25px;line-height:1.3;font-weight:600;color:#757575;width:100%;margin:0 auto 5px;'>
                                                                $userFname عزیز
                                                            </h2>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            به وبسایت مترجم وان خوش آمدید ! از ثبت نام شما متشکریم
                                                            <br />
                                                            لطفا برای تایید حساب کاربری تان روی لینک زیر کلیک کنید
                                                            <div style='text-align:center;margin-top:28px;margin-bottom:28px'>
                                                                <a style='font-size:16px;font-weight:400;display:inline-block;padding:0 16px;border:none;border-radius:2px;text-transform:uppercase;text-decoration:none;text-align:center;vertical-align:baseline;letter-spacing:0;opacity:1;outline:none!important;color:#ffffff;background-color:#03a9f4;line-height:40px;margin:10px 0' href='$verifyLink' target='_blank' data-saferedirecturl='$verifyLink'>تایید حساب کاربری</a>
                                                            </div>
                                                            با تشکر, وبسایت مترجم وان
                                                        </td>
                                                    </tr>
                                                 </tbody>
                                            </table>
                                            </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                    <table style='width:100%'>
                        <tbody>
                            <tr>
                                <td>
                                    <table style='width:100%;margin:10px 0;padding:0'>
                                <tbody>
                                    <tr>
                                        <td>
                                            <p style='text-align:center;color:#666;font-size:12px;font-weight:400;display:block;width:100%;margin:0;padding:0;direction:rtl'>
                                                طراحی توسط
                                                <a href='https://coderguy.ir' target='_blank' data-saferedirecturl='https://coderguy.ir'>coderguy</a>
                                            </p>

                                            <p style='text-align:center;color:#666;font-size:12px;font-weight:400;display:block;width:100%;margin:0;padding:0'>
                                                میدان انقلاب ابتدای کارگر شمالی کوچه رستم پ ۲۱ و ۸
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                        </table>
                            </td>
                        </tr>
                        </tbody>
                </table>

                    <img
                    src='https://ci6.googleusercontent.com/proxy/x1hIVdOPqG1u7nFBLvrvow3A7rXWw6G0YolfgKSfhAJWSkkBNfGon9YTINQ6I2SyfGqYw7up59T-NdDUxBBgz4E14G8p4q4NoP93Weg4bUJvvy66sNJX4EpSMh9hXn7LowGlNVamYUA=s0-d-e1-ft#https://mandrillapp.com/track/open.php?u=30121732&amp;id=ee001c7acb1741cfa420738ecd825d99'
                    height='1'
                    width='1'
                    class='CToWUd'
                    />
                </div>
                </body>
</html>

        ";
        return mail($userInfo['email'], $subject, $text, $headers);

    }
    protected function create_password_reset_link($email, $saveToDB = false)
    {
        $userData = User::by_email($email);
        if ($userData) {
            $token = $userData['phone'] . \random_bytes(20) . $userData['register_date'] . Config::ENCRYPTION_KEY;
            $token = \sha1(\md5($token));
            $resetLink = Config::BASE_URL . "/user/reset-password?token=" . $token . "&user=" . $userData['username'];
            if ($saveToDB) {
                try {
                    $forgetPasswordData = \Core\Model::select("forgot_password", "user_id", ['user_id' => $userData['user_id']]);
                    if ($forgetPasswordData) {
                        \Core\Model::update("forgot_password", [
                            'token' => $token,
                            'expire_date' => \time() + 86400,
                        ], "user_id = '" . $userData['user_id'] . "'");
                    } else {
                        \Core\Model::insert("forgot_password", [
                            'user_id' => $userData['user_id'],
                            'user_type' => 1,
                            'token' => $token,
                            'expire_date' => \time() + 86400,
                        ]);
                    }

                } catch (\Exception $e) {
                    return [
                        'status' => false,
                        'error' => "مشکلی در ایجاد لینک رخ داد !",
                    ];
                }
            }
            return [
                'status' => true,
                'link' => $resetLink,
            ];
        } else {
            return [
                'status' => false,
                'error' => "ایمیل وارد شده موجود نمی باشد !",
            ];
        }
    }
    public function send_password_reset_to_email($email, $link)
    {
        $from = "support@motarjem1.com";
        $headers = "From:" . $from;
        $headers .= "Reply-To: noreply@motarjem1.com \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $subject = "لینک تغییر پسورد";
        $userFname = User::by_email($email, "fname")['fname'];
        $text = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='َUTF-8'>
            <style type='text/css'>
                @import('https://cdn.rawgit.com/rastikerdar/vazir-font/v19.1.0/dist/font-face.css');
            </style>
        </head>
            <body style='margin:0;padding:0;font-family: Vazir, Tahoma, DejaVu Sans, helvetica, arial, freesans, sans-serif !important;'>
            <div style='width:100%!important;min-width:300px;height:100%;margin:0;padding:0;line-height:1.5;color:#333;background-color:#f2f2f2'>
            <table style='width:100%;padding:30px 0 0 0'>
                <tbody>
                    <tr>
                        <td align='center'>
                            <img src='http://motarjem1.com/public/images/logo.png' class='CToWUd' />
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style='padding:5px;width:100%;max-width:620px;margin:0 auto;color:#515151'>
                <tbody>
                    <tr>
                        <td>
                            <table style='width:100%;margin:0;padding:0 0 20px'>
                                <tbody>
                                    <tr style='margin:0;padding:0'>
                                        <td style='margin:0;padding:0'>
                                            <table style='width:100%;max-width:620px;padding:30px;margin:20px auto 5px;background-color:#fff;border-radius:4px;text-align:right'>
                                                <tbody>
                                                    <tr style='width:100%'>
                                                        <td style='width:100%'>
                                                            <h2 style='font-size:25px;line-height:1.3;font-weight:600;color:#757575;width:100%;margin:0 auto 5px;'>
                                                                $userFname عزیز
                                                            </h2>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <p>با سلام شما درخواست تغییر رمز عبور خودتان را داده بودید</p>
                                                            <br />
                                                            <strong style='margin-top:2rem;'>لطفا برای تغییر دادن رمز عبور روی لینک زیر کلیک کنید</strong>
                                                            <div style='text-align:center;margin-top:28px;margin-bottom:28px'>
                                                                <a style='font-size:16px;font-weight:400;display:inline-block;padding:0 16px;border:none;border-radius:2px;text-transform:uppercase;text-decoration:none;text-align:center;vertical-align:baseline;letter-spacing:0;opacity:1;outline:none!important;color:#ffffff;background-color:#03a9f4;line-height:40px;margin:10px 0' href='$link' target='_blank' data-saferedirecturl='$link'>تغییر رمز عبور</a>
                                                            </div>
                                                            با تشکر, وبسایت مترجم وان
                                                        </td>
                                                    </tr>
                                                 </tbody>
                                            </table>
                                            </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    </td>
                                </tr>
                        </tbody>
                    </table>
                    <table style='width:100%'>
                        <tbody>
                            <tr>
                                <td>
                                    <table style='width:100%;margin:10px 0;padding:0'>
                                <tbody>
                                    <tr>
                                        <td>
                                            <p style='text-align:center;color:#666;font-size:12px;font-weight:400;display:block;width:100%;margin:0;padding:0;direction:rtl'>
                                                طراحی توسط
                                                <a href='https://coderguy.ir' target='_blank' data-saferedirecturl='https://coderguy.ir'>coderguy</a>
                                            </p>

                                            <p style='text-align:center;color:#666;font-size:12px;font-weight:400;display:block;width:100%;margin:0;padding:0'>
                                                میدان انقلاب ابتدای کارگر شمالی کوچه رستم پ ۲۱ و ۸
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                        </table>
                            </td>
                        </tr>
                        </tbody>
                </table>

                    <img
                    src='https://ci6.googleusercontent.com/proxy/x1hIVdOPqG1u7nFBLvrvow3A7rXWw6G0YolfgKSfhAJWSkkBNfGon9YTINQ6I2SyfGqYw7up59T-NdDUxBBgz4E14G8p4q4NoP93Weg4bUJvvy66sNJX4EpSMh9hXn7LowGlNVamYUA=s0-d-e1-ft#https://mandrillapp.com/track/open.php?u=30121732&amp;id=ee001c7acb1741cfa420738ecd825d99'
                    height='1'
                    width='1'
                    class='CToWUd'
                    />
                </div>
                </body>
</html>

        ";
        mail($email, $subject, $text, $headers);

    }
    protected function valiate_user_signup($postFields)
    {
        $hasError = false;
        if ($postFields['fname'] == "") {
            $this->flash->addMessage('userSignupErrors', "فیلد نام نباید خالی باشد !");
            $hasError = true;
        }
        if ($postFields['lname'] == "") {
            $this->flash->addMessage('userSignupErrors', "فیلد نام خانوادگی نباید خالی باشد !");
            $hasError = true;
        }
        if ($postFields['email'] == "") {
            $this->flash->addMessage('userSignupErrors', "فیلد ایمیل نباید خالی باشد !");
            $hasError = true;
        }
        if (!filter_var($postFields['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash->addMessage('userSignupErrors', "ایمیل وارد شده نامعتبر است !");
            $hasError = true;
        }
        if ($postFields['password'] == "") {
            $this->flash->addMessage('userSignupErrors', "فیلد پسورد نباید خالی باشد !");
            $hasError = true;
        }
        if ($postFields['confirm_password'] == "") {
            $this->flash->addMessage('userSignupErrors', "فیلد تایید نباید خالی باشد !");
            $hasError = true;
        }
        if ($postFields['confirm_password'] != $postFields['password']) {
            $this->flash->addMessage('userSignupErrors', "فیلد پسورد با فیلد تایید مطابقت ندارد !");
            $hasError = true;
        }
        if (strlen($postFields['phone']) > 11 || strlen($postFields['phone']) < 11) {
            $this->flash->addMessage('userSignupErrors', "شماره وارد شده نامعتبر است !");
            $hasError = true;
        }
        return $hasError;
    }

}
