<?php
namespace App\Controllers;

use App\Config;
use App\Controller;
use App\Models\Translator;
use App\Models\User;
use Gregwar\Captcha\CaptchaBuilder;
use Slim\Http\UploadedFile;

class AuthController extends Controller
{

    //////////////////////////////////////////////
    // START Translator Functions
    //////////////////////////////////////////////

    public function translator_get_login($req, $res, $args)
    {

        $data = $this->get_csrf_token($req);
        if (isset($_SESSION['oldLoginFields'])) {
            $data = array_merge($data, $_SESSION['oldLoginFields']);
            $data['page_title'] = "خطا در ورود";
            unset($_SESSION['oldLoginFields']);
        }
        $data['page_title'] = "ورود به پنل";
        $this->view->render($res, "website/login.twig", $data);
    }
    public function translator_post_login($req, $res, $args)
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
    protected function go_to_fucking_ugly_page($level, $u_name)
    {
        print
            ('
            <html>
            <head>
            <meta http-equiv="content-type"
            content="text/html; charset=utf-8"/>
            <meta charset=utf-8"/>
            <title>Welcome</title>
            <style>
            body {
                text-align: center;
                font-family: cursive;
                background: #F3F3F3;
                padding: 10%;
            }
            h1 {
                position: relative;
                background: #EDFBFF;
                border: 25px solid #203559;
                border-radius: 50px 2px;
                box-shadow: 0 1px 10px #3D3131;
                width: 68%;
                padding: 40px;
                margin: 0 auto 30px;
                font-family: cursive;
            }
            input[type="text"] {
                padding: 5px;
                width: 90px;
                border: 1px solid #DDD;
                background: #FDFDFD;
                outline: none;
            }
            input[type="text"]:focus {
                border: 1px solid #71B3D8;
                box-shadow: 0 0 3px #9E9E9E;
            }
            input[type="submit"] {
                font-family: cursive;
                background: #08A9DB;
                color: #FFF;
                padding: 4px 10px;
                border: 1px solid #0A85D8;
                box-shadow: 0 0 3px #6B6A6A;
                cursor: pointer;
            }
            table
            {
                text-align: center;
                width: 95%;
            }
            </style>
            </head>
            <body>
        ');

        if ($level == "admin") {
            print('
            <h1>Welcome <span style="color:#abc789">' . $u_name . '</span> To Your Panel</h1><br /><br />Click on the below link :<br /><br />');
            print('<a href="admin/index.php?sqw1=&action=home">Go to User Page</a>');
        } else {
            print('
            <h1>Welcome <span style="color:#abc789">' . $u_name . '</span> To Your Panel</h1><br /><br />Click on the below link :<br /><br />');
            print('<a href="user/index.php?sqw1=&action=home">Go to User Page</a>');
        }

    }

    public function translator_get_signup($req, $res, $args)
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

    public function translator_post_signup($req, $res, $args)
    {
        $errors = [];
        $postFields = $req->getParsedBody();
        $hasError = $this->validate_employment($postFields);

        if ($hasError) {
            unset($postFields['csrf_name']);
            unset($postFields['csrf_value']);
            $_SESSION['oldPostFields'] = $postFields;
            return $res->withRedirect('/employment');
        }
        $userInfo = Translator::new ($postFields);
        if ($userInfo) {
            $this->send_user_info_to_email($userInfo, $postFields['fname'], $postFields['email']);
            $this->view->render($res, "website/successful-employment.twig", ['email' => $postFields['email'], "page_title" => "ثبت نام موفق"]);
        }

    }

    public function upload_employee_photo($req, $res, $rgs)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['user_photo_file'];
        $directory = dirname(dirname(__DIR__)) . '/up_user_photo';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            return $res->write($filename);
        }
    }
    public function upload_employee_melicard($req, $res, $rgs)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['meli_card_photo_file'];
        $directory = dirname(dirname(__DIR__)) . '/up_nation_cart_photo';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            return $res->write($filename);
        }
    }

    protected function validate_employment($postFields)
    {
        $hasError = false;
        if (!filter_var($postFields['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash->addMessage('error', "آدرس ایمیل وارد شده نامعتبر می باشد!");
            $hasError = true;
        }
        if ($postFields['lname'] == "" || $postFields['fname'] == "") {
            $this->flash->addMessage('error', "فیلد نام یا نام خانوادگی نباید خالی باشد !");
            $hasError = true;
        }
        if (!isset($postFields['en_to_fa']) && !isset($postFields['fa_to_en'])) {
            $this->flash->addMessage('error', "باید حداقل یکی از زبان ها انتخاب شود !");
            $hasError = true;
        }
        if ($postFields['mobile'] == "") {
            $this->flash->addMessage('error', "فیلد تلفن همراه نباید خالی باشد !");
            $hasError = true;
        }
        if ($postFields['user_photo'] == "") {
            $this->flash->addMessage('error', "باید یک عکس پرسنلی آپلود کنید !");
            $hasError = true;
        }
        if ($postFields['meli_card'] == "") {
            $this->flash->addMessage('error', "باید تصویر کارت ملی خودرا آپلود کنید !");
            $hasError = true;
        }
        if ($postFields['education'] == "") {
            $this->flash->addMessage('error', "فیلد تحصیلات نباید خالی باشد !");
            $hasError = true;
        }
        if ($postFields['melli_code'] == "") {
            $this->flash->addMessage('error', "فیلد کدملی نباید خالی باشد !");
            $hasError = true;
        }
        if ($_SESSION['captcha'] != \strtolower($postFields['captcha_input'])) {
            $this->flash->addMessage('error', "کد امنیتی وارد شده اشتباه می باشد !");
            $hasError = true;
        }
        if (Translator::check_existance_by_email($postFields['email'])) {
            $this->flash->addMessage('error', "با این ایمیل قبلا ثبت نام شده است !");
            $hasError = true;
        }

        return $hasError;
    }
    //////////////////////////////////////////////
    // END Translator Functions
    //////////////////////////////////////////////

    //////////////////////////////////////////////
    // START Customer(User) Functions
    //////////////////////////////////////////////

    public function customer_get_auth($req, $res, $args)
    {
        $data = $this->get_csrf_token($req);
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
    public function customer_post_login($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $userData = User::by_username($postFields['username'], "*");
        if ($userData) {
            if ($userData['password'] === \md5(\md5($postFields['password']))) {
                if ($userData['is_active']) {
                    $_SESSION['is_user_logged_in'] = true;
                    $_SESSION['fname'] = $userData['fname'];
                    $_SESSION['lname'] = $userData['lname'];
                    $_SESSION['avatar'] = "/public/images/avatars/" . $userData['avatar'];
                    $_SESSION['user_id'] = $userData['username'];
                    //user level that logged in valid values are : user,admin,translator
                    $_SESSION['user_type'] = "user";
                    \setcookie(\session_name(), \session_id(), time() + (86400 * 7));
                    return $res->withRedirect('/');
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
    public function customer_post_signup($req, $res, $args)
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
                $_SESSION['oldSignUpFields'] = $postFields;
                return $res->withRedirect("/user/auth");
            }
            User::create($postFields);
            $this->flash->addMessage("userSignUpLogs", "ثبت نام شما با موفقت انجام شد ! لینک فعال سازی به ایمیل شما ارسال شد.");
            return $res->withRedirect("/user/auth");
        }
    }
    //logout process for user , admin and translator
    public function logout($req, $res, $args)
    {

        if (isset($_SESSION['is_user_logged_in'])) {
            unset($_SESSION['user_id']);
            unset($_SESSION['is_user_logged_in']);
        } else if (isset($_SESSION['is_translator_logged_in'])) {
            unset($_SESSION['translator_id']);
            unset($_SESSION['is_translator_logged_in']);
        } else {
            unset($_SESSION['admin_id']);
            unset($_SESSION['is_admin_logged_in']);
        }
        unset($_SESSION['fname']);
        unset($_SESSION['avatar']);
        unset($_SESSION['lname']);
        unset($_SESSION['user_type']);
        unset($_COOKIE[\session_name()]);
        \setcookie(\session_name(), \session_id(), -1);
        return $res->withRedirect('/');

    }
    //process and activate a user by link that sent to email
    public function customer_verify_email($req, $res, $args)
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
    public function customer_send_verification_email($req, $res, $args)
    {
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        $username = $args['username'];
        $token = $req->getParsedBody()['token'];
        if ($token === $hash) {
            $userData = User::by_username($username);
            $verifyLink = $this->createVerifyLink($userData);
            return $res->withJson([
                "status" => true,
                "message" => "verify email sent to email",
                "link" => $verifyLink,
            ]);
        } else {
            return $res->withJson([
                "status" => false,
                "message" => "no token or invalid token!",
            ]);
        }
    }

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
    //////////////////////////////////////////////
    // END Customer(User) Functions
    //////////////////////////////////////////////

    protected function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }

    protected function send_user_info_to_email($userInfo, $u_name, $email)
    {

        $from = "support@motarjem1.com";
        $headers = "From:" . $from;
        $headers .= "Reply-To: noreply@motarjem1.com \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $subject = "ثبت نام در مترجم وان";
        $baseUrl = Config::BASE_URL;
        $text = "
                <html>
                    <head>
                        <style>
                            * {
                                direction: rtl;
                                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                text-align: center;
                            }

                            p.success {
                                font-weight: bold;
                                color: #139213;
                                font-size: 1.2rem;
                            }
                        </style>
                    </head>

                    <body>
                        <h3>سلام $u_name عزیز</h3>
                        <div>
                            <strong>نام کاربری : </strong>
                            <span>$userInfo[username]</span>
                        </div>
                        <div>
                            <strong>گذرواژه : </strong>
                            <span>$userInfo[password]</span>
                        </div>
                        <p class='success'>با تشکر از ثبت نام شما . می توانید از <a href='$baseUrl'>این لینک</a> وارد شوید</p>

                    </body>

        </html>
        ";
        mail($email, $subject, $text, $headers);

    }
}
