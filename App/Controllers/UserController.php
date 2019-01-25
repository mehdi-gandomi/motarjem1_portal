<?php
namespace App\Controllers;

use App\Models\User;
use Core\Config;
use Core\Controller;
use Slim\Http\UploadedFile;

class UserController extends Controller
{

    //////////////////////////////////////////////
    // START Customer(User) Auth Functions
    //////////////////////////////////////////////

    public function get_auth($req, $res, $args)
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

                    \setcookie(\session_name(), \session_id(), time() + (86400 * 7));
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
                $_SESSION['oldSignUpFields'] = $postFields;
                return $res->withRedirect("/user/auth");
            }
            User::create($postFields);
            $this->flash->addMessage("userSignUpLogs", "ثبت نام شما با موفقت انجام شد ! لینک فعال سازی به ایمیل شما ارسال شد.<a style='cursor:pointer;color:#5842d4' onclick='sendVerificationCode(\"coderguy\",\".signupLogs\")'>ارسال مجدد</a>");
            return $res->withRedirect("/user/auth");
        }
    }
    //logout process for user , admin and translator
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
        \setcookie(\session_name(), "", \time() - 3600);
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
    // END Customer(User) Auth Functions
    //////////////////////////////////////////////

    //////////////////////////////////////////////
    // START Customer(User) ADMIN Functions
    //////////////////////////////////////////////

    public function get_dashboard($req, $res, $args)
    {
        $userOrders = User::get_orders_by_user_id($_SESSION['user_id'], 1, 3);
        $workingOrdersCount = User::get_orders_count_by_user_id($_SESSION['user_id'], ['is_done'=>0]);
        $completedOrdersCount = User::get_orders_count_by_user_id($_SESSION['user_id'],['is_done'=>1]);
        $unreadMessagesCount = User::get_unread_messages_count_by_user_id($_SESSION['user_id']);
        $lastThreeMessages = User::get_messages_by_user_id($_SESSION['user_id'], 3);

        $this->view->render($res, "admin/user/dashboard.twig", ['orders' => $userOrders, 'completedOrdersCount' => $completedOrdersCount, 'workingOrdersCount' => $workingOrdersCount, 'unreadMessagesCount' => $unreadMessagesCount, 'lastMessages' => $lastThreeMessages]);
    }
    //get translator info and send that as json
    public function get_translator_info($req, $res, $args)
    {
        $translatorId = $args['id'];
        $translatorData = \App\Models\Translator::get_translator_data_by_id($translatorId, "fname,lname,cell_phone,email,avatar");
        return $res->withJson($translatorData);

    }
    public function get_user_orders($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page"):1;
        $pendingOrders = $req->getQueryParam("pending");
        $completedOrders = $req->getQueryParam("completed");
        $filtering_options=[];
        if($pendingOrders && !$completedOrders){
            $filtering_options['is_done']=0;
            $pending=true;
            $completed=false;
        }else if ($completedOrders && !$pendingOrders){
            $filtering_options['is_done']=1;
            $pending=false;
            $completed=true;
        }else{
            $pending=true;
            $completed=true;
        }
        $userOrdersCount = User::get_orders_count_by_user_id($_SESSION['user_id'],$filtering_options);
        $userOrders = User::get_orders_by_user_id($_SESSION['user_id'], $page, 10, $filtering_options);
        return $this->view->render($res, "admin/user/orders.twig", ['orders' => $userOrders, 'current_page' => $page, 'orders_count' => 120,'completed'=>$completed,'pending'=>$pending]);
    }
    public function get_user_orders_json($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page"):1;
        $pendingOrders = $req->getQueryParam("pending");
        $completedOrders = $req->getQueryParam("completed");
        $filtering_options=[];
        if($pendingOrders && !$completedOrders){
            $filtering_options['is_done']=0;
        }else if ($completedOrders && !$pendingOrders){
            $filtering_options['is_done']=1;
        }
        $userOrdersCount = User::get_orders_count_by_user_id($_SESSION['user_id'],$filtering_options);
        $userOrders = User::get_orders_by_user_id($_SESSION['user_id'], $page, 10, $filtering_options);
        return $res->withJson(array(
            'orders'=>$userOrders,
            'orders_count'=>120,
            'current_page'=>$page
        ));
    }
    //////////////////////////////////////////////
    // END Customer(User) ADMIN Functions
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
