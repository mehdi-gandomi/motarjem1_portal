<?php
namespace App\Controllers;

use App\Models\Translator;
use Core\Config;
use Core\Controller;
use Gregwar\Captcha\CaptchaBuilder;

class TranslatorAuthController extends Controller
{

//////////////////////////////////////////////
    // START Translator Auth Functions
    //////////////////////////////////////////////

    public function get_login($req, $res, $args)
    {

        $data = $this->get_csrf_token($req);
        if (isset($_SESSION['oldLoginFields'])) {
            $data = array_merge($data, $_SESSION['oldLoginFields']);
            $data['page_title'] = "خطا در ورود";
            unset($_SESSION['oldLoginFields']);
        }
        $data['page_title'] = "ورود به پنل";
        $this->view->render($res, "website/translator_login.twig", $data);
    }
    public function post_login($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $username = $postFields['username'];
        $password = $postFields['password'];

        $result = Translator::login($username, $password);

        if ($result['hasError']) {
            $this->flash->addMessage('loginError', $result['error']);
            $_SESSION['oldLoginFields'] = array(
                'username' => $username,
                'password' => $password,
            );
            return $res->withRedirect('/login');
        } else {
            $this->go_to_fucking_ugly_page($result['level'], $result['u_name']);
        }

    }

    public function get_employment($req, $res, $args)
    {
        $tokenArray = $this->get_csrf_token($req);
        $data = $tokenArray;
        $builder = new CaptchaBuilder;
        $builder->build();
        $captcha = $builder->inline();
        $_SESSION['captcha'] = $builder->getPhrase();
        $data['captcha'] = $captcha;
        if (isset($_SESSION['oldPostFields'])) {
            $data = \array_merge($data, $_SESSION['oldPostFields']);
            $data['page_title'] = "خطا در ثبت نام";
            unset($_SESSION['oldPostFields']);
        } else {
            $data['page_title'] = "استخدام مترجم";
        }
        return $this->view->render($res, "website/employment.twig", $data);
    }

    public function post_employment($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $hasError = $this->validate_employment($postFields);

        if ($hasError) {
            unset($postFields['csrf_name']);
            unset($postFields['csrf_value']);
            $_SESSION['oldPostFields'] = $postFields;
            return $res->withRedirect('/translator/employment');
        }
        if (Translator::check_existance($postFields)) {
            unset($postFields['csrf_name']);
            unset($postFields['csrf_value']);
            $_SESSION['oldPostFields'] = $postFields;
            $this->flash->addMessage('error', "این نام کاربری یا ایمیل قبلا در سیستم ثبت شده است !");
            return $res->withRedirect('/translator/employment');
        } else {
            $translatorInfo = Translator::new ($postFields);
            if ($translatorInfo) {
                $translatorData = Translator::by_username("coderguy", "register_date,phone,email,username");
                $verifyLink = $this->createVerifyLink($translatorData);
                $this->send_link_to_email($translatorData, $verifyLink);
                $this->view->render($res, "website/successful-employment.twig", ['email' => $postFields['email'], 'username' => $postFields['username'], "page_title" => "ثبت نام موفق"]);
            }
        }

    }

    //create and send a verification link to translator to activate the account
    public function send_verify_link_again($req, $res, $args)
    {
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        $username = $args['username'];
        $token = $req->getParsedBody()['token'];
        if ($token === $hash) {
            $translatorData = Translator::by_username($username);
            if (!$translatorData) {
                return $res->withJson([
                    "status" => false,
                    "message" => "ایمیل وارد شده در سیستم موجود نمی باشد!"
                ]);
            }
            $verifyLink = $this->createVerifyLink($translatorData);
            $result = $this->send_link_to_email($translatorData, $verifyLink);
            if ($result) {
                return $res->withJson([
                    "status" => true,
                    "message" => "لینک فعال سازی به ایمیل شما ارسال شد !",
                    "link"=>$verifyLink
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

    public function upload_photo($req, $res, $rgs)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['user_photo_file'];
        $directory = dirname(dirname(__DIR__)) . '/uploads/avatars/translator';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            try {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                return $res->write($filename);
            } catch (\Exception $e) {
                $res->write("error while uploading file "+$e->getMessage())->withStatus(500);
            }
        } else {
            $res->write($uploadedFile->getError())->withStatus(500);
        }
    }
    public function upload_melicard_photo($req, $res, $rgs)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['meli_card_photo_file'];
        $directory = dirname(dirname(__DIR__)) . '/uploads/translator/melicard';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            try {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                return $res->write($filename);
            } catch (\Exception $e) {
                $res->write("error while uploading file "+$e->getMessage())->withStatus(500);
            }
        } else {
            $res->write($uploadedFile->getError())->withStatus(500);
        }
    }

    public function get_forget_password_page($req, $res, $args)
    {
        $tokens = $this->get_csrf_token($req);
        $tokens['action']="/translator/forget-password";
        $this->view->render($res, "website/forgot_password.twig", $tokens);
    }

//create password reset link and send it to user's email
    public function send_password_reset_link($req, $res, $args)
    {
        $email = $req->getParsedBody()['email'];
        $result = $this->create_password_reset_link($email, true);
        if ($result['status']) {
            $this->send_password_reset_to_email($email, $result['link']);
            $this->flash->addMessage('success', "لینک تغییر پسورد به ایمیل شما ارسال شد !");
            return $res->withRedirect("/user/forget-password");
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
        $validationErrors = [];
        $validationSuccess = "";
        $tokens = $this->get_csrf_token($req);
        $tokenIsValid = false;
        if ($forgetPasswordData) {
            if (time() < intval($forgetPasswordData['expire_date'])) {
                $validationSuccess = "توکن معتبر می باشد حالا می توانید پسوردتان را تغییر دهید";
                $tokenIsValid = true;
            } else {
                array_push($validationErrors, "اعتبار توکن به اتمام رسیده است !");
            }
        } else {
            array_push($validationErrors, "توکن نامعتبر می باشد !");
        }
        return $this->view->render($res, "website/reset-password.twig", ['token_is_valid' => $tokenIsValid, 'validationErrors' => $validationErrors, 'validationSuccess' => $validationSuccess, 'csrf_name' => $tokens['csrf_name'], 'csrf_value' => $tokens['csrf_value'], 'username' => $username]);
    }

//this funtion gets data from change password page and saves it to database
    public function post_change_password($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $validationErrors = [];
        $validationSuccess = "";
        $tokens = $this->get_csrf_token($req);
        $translatorData = User::by_username($postFields['username'], "user_id");
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
                \Core\Model::delete("forgot_password", "user_id = '" . $translatorData['user_id'] . "'");
                $validationSuccess = "پسورد شما با موفقیت تغییر کرد حالا می توانید با استفاده از <a href='/user/auth'>این لینک</a> وارد شود";
            } catch (\Exception $e) {
                array_push($validationErrors, "خطایی در تغییر پسورد رخ داد");
            }
        }
        return $this->view->render($res, "website/reset-password.twig", ['token_is_valid' => true, 'validationErrors' => $validationErrors, 'validationSuccess' => $validationSuccess, 'csrf_name' => $tokens['csrf_name'], 'csrf_value' => $tokens['csrf_value'], 'username' => $postFields['username']]);
    }


    //////////////////////////////////////////////
    // END Translator Auth Functions
    //////////////////////////////////////////////



    // utitlity methods

    protected function validate_employment($postFields)
    {
        $hasError = false;
        if (!filter_var($postFields['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash->addMessage('error', "آدرس ایمیل وارد شده نامعتبر می باشد!");
            $hasError = true;
        }
        if ($postFields['lname'] == "") {
            $this->flash->addMessage('error', "فیلد نام خانوادگی نباید خالی باشد !");
            $hasError = true;
        }
        if ($postFields['fname'] == "") {
            $this->flash->addMessage('error', "فیلد نام نباید خالی باشد !");
            $hasError = true;
        }
        if (!isset($postFields['en_to_fa']) && !isset($postFields['fa_to_en'])) {
            $this->flash->addMessage('error', "باید حداقل یکی از زبان ها انتخاب شود !");
            $hasError = true;
        }
        if ($postFields['cell_phone'] == "") {
            $this->flash->addMessage('error', "فیلد تلفن همراه نباید خالی باشد !");
            $hasError = true;
        } else if (strlen($postFields['cell_phone']) != 11) {
            $this->flash->addMessage('error', "تلفن همراه نامعتبر می باشد !");
            $hasError = true;
        }
        // if ($postFields['avatar'] == "") {
        //     $this->flash->addMessage('error', "باید یک عکس پرسنلی آپلود کنید !");
        //     $hasError = true;
        // }
        // if ($postFields['melicard_photo'] == "") {
        //     $this->flash->addMessage('error', "باید تصویر کارت ملی خودرا آپلود کنید !");
        //     $hasError = true;
        // }
        if ($postFields['degree'] == "") {
            $this->flash->addMessage('error', "فیلد تحصیلات نباید خالی باشد !");
            $hasError = true;
        }
        if ($postFields['meli_code'] == "") {
            $this->flash->addMessage('error', "فیلد کدملی نباید خالی باشد !");
            $hasError = true;
        } else if (strlen($postFields['meli_code']) != 10) {
            $this->flash->addMessage('error', "کد ملی نامعتبر می باشد !");
            $hasError = true;
        }
        if ($postFields['password'] == "") {
            $this->flash->addMessage('error', "فیلد پسورد نباید خالی باشد !");
            $hasError = true;
        } else if ($postFields['password'] != $postFields['confirm_pass']) {
            $this->flash->addMessage('error', "فیلد پسورد با فیلد تایید آن مطابقت ندارد !");
            $hasError = true;
        }
        if ($postFields['username'] == "") {
            $this->flash->addMessage('error', "فیلد نام کاربری نباید خالی باشد!");
            $hasError = true;
        } else if (\strlen($postFields['username']) < 5) {
            $this->flash->addMessage('error', "فیلد نام کاربری نباید کمتر از 5 کاراکتر باشد!");
            $hasError = true;
        }
        if ($_SESSION['captcha'] != \strtolower($postFields['captcha_input'])) {
            $this->flash->addMessage('error', "کد امنیتی وارد شده اشتباه می باشد !");
            $hasError = true;
        }
        if (Translator::check_existance($postFields)) {
            $this->flash->addMessage('error', "با این ایمیل یا نام کاربری قبلا ثبت نام شده است !");
            $hasError = true;
        }

        return $hasError;
    }        
    //create email verification link to be sent to translator
    protected function createVerifyLink($translatorData, $onlyKey = false)
    {
        $verifyKey = $translatorData['email'] . "my name is mehdi" . $translatorData['phone'] . $translatorData['register_date'];
        $verifyKey = \sha1(\md5($verifyKey));

        if ($onlyKey) {
            return $verifyKey;
        } else {
            return Config::BASE_URL . "/translator/confirm?user=" . \urlencode($translatorData['username']) . "&verify_token=" . \urlencode($verifyKey);
        }
    }
    protected function send_link_to_email($translatorInfo, $verifyLink)
    {

        $from = "support@motarjem1.com";
        $headers = "From:" . $from;
        $headers .= "Reply-To: noreply@motarjem1.com \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (!filter_var($translatorInfo['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $subject = "استخدام در مترجم وان";
        $userFname = $translatorInfo['fname'];
        $text = "
        <!DOCTYPE html>
        <html>
        <head>
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
        return mail($translatorInfo['email'], $subject, $text, $headers);

    }

    protected function create_password_reset_link($email, $saveToDB = false)
    {
        $translatorData = Translator::by_email($email);
        if ($translatorData) {
            $token = $translatorData['phone'] . \random_bytes(20) . $translatorData['register_date'] . Config::ENCRYPTION_KEY;
            $token = \sha1(\md5($token));
            $resetLink = Config::BASE_URL . "/translator/reset-password?token=" . $token . "&user=" . $translatorData['username'];
            if ($saveToDB) {
                try {
                    $forgetPasswordData = \Core\Model::select("forgot_password", "user_id", ['user_id' => $translatorData['user_id']]);
                    if ($forgetPasswordData) {
                        \Core\Model::update("forgot_password", [
                            'token' => $token,
                            'expire_date' => \time() + 86400,
                        ], "user_id = '" . $translatorData['user_id'] . "'");
                    } else {
                        \Core\Model::insert("forgot_password", [
                            'user_id' => $translatorData['user_id'],
                            'user_type' => 2,
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
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $subject = "لینک تغییر پسورد";
        $userFname = Translator::by_email($email, "fname")['fname'];
        $text = "
        <!DOCTYPE html>
        <html>
        <head>
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

}
