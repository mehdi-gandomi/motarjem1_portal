<?php
namespace App\Controllers;
use Core\Config;
use Core\Controller;
use App\Models\Translator;
use Gregwar\Captcha\CaptchaBuilder;
use Slim\Http\UploadedFile;

class TranslatorController extends Controller
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

    public function get_signup($req, $res, $args)
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

    public function post_signup($req, $res, $args)
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
    // END Translator Auth Functions
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




    

 