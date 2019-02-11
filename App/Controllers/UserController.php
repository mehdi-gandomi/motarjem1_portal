<?php
namespace App\Controllers;

use App\Dependencies\Pay\Payment;
use App\Models\User;
use Core\Config;
use Core\Controller;

class UserController extends Controller
{

    private $gateways = ['mellat', 'zarinpal'];
    private $payment;
    private $payment_gateway;
    private $studyFields=array(
        "90" => "ادبیات و زبان شناسی",
        "89" => "اسناد تجاری",
        "88" => "اقتصاد",
        "86" => "برق و الکترونیک",
        "91" => "تاریخ",
        "41" => "ترجمه کاتالوگ",
        "76" => "جغرافیا",
        "75" => "حسابداری",
        "74" => "حقوق",
        "70" => "روان شناسی",
        "71" => "ریاضی",
        "72" => "زمین شناسی و معدن",
        "43" => "زیرنویس فیلم",
        "73" => "زیست شناسی",
        "67" => "شیمی",
        "68" => "صنایع",
        "69" => "صنایع غذایی",
        "62" => "علوم اجتماعی",
        "63" => "علوم سیاسی",
        "64" => "عمران",
        "61" => "عمومی",
        "44" => "فایل صوتی تصویری",
        "57" => "فقه و علوم اسلامی",
        "58" => "فلسفه",
        "59" => "فناوری اطلاعات",
        "60" => "فیزیک",
        "50" => "متالورژی و مواد",
        "51" => "محیط زیست",
        "49" => "مدیریت",
        "54" => "منابع طبیعی و شیلات",
        "53" => "مکانیک",
        "47" => "نفت،گاز و پتروشیمی",
        "92" => "هنر و معماری",
        "46" => "ورزش و تربیت بدنی",
        "85" => "پزشکی",
        "93" => "ژنتیک و میکروبیولوژی",
        "55" => "کامپیوتر",
        "56" => "کشاورزی",
    );
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

                    \setcookie(\session_name(), \session_id(), time() + (86400 * 7));
                    var_dump($_SESSION);
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
            if(!$userData){
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
                \Core\Model::delete("forgot_password", "user_id = '" . $userData['user_id'] . "'");
                $validationSuccess = "پسورد شما با موفقیت تغییر کرد حالا می توانید با استفاده از <a href='/user/auth'>این لینک</a> وارد شود";
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

    #region Admin functions
    //////////////////////////////////////////////
    // START Customer(User) ADMIN Functions
    //////////////////////////////////////////////

    public function get_dashboard($req, $res, $args)
    {
        $userOrders = User::get_orders_by_user_id($_SESSION['user_id'], 1, 3);
        $workingOrdersCount = User::get_orders_count_by_user_id($_SESSION['user_id'], ['is_done' => 0, 'is_accepted' => 1]);
        $completedOrdersCount = User::get_orders_count_by_user_id($_SESSION['user_id'], ['is_done' => 1, 'is_accepted' => 1]);
        $unreadMessagesCount = User::get_unread_messages_count_by_user_id($_SESSION['user_id']);
        $lastThreeMessages = User::get_messages_by_id($_SESSION['user_id'], 1, 3);
        $this->view->render($res, "admin/user/dashboard.twig", ['orders' => $userOrders, 'completedOrdersCount' => $completedOrdersCount, 'workingOrdersCount' => $workingOrdersCount, 'unreadMessagesCount' => $unreadMessagesCount, 'lastMessages' => $lastThreeMessages]);
    }
    //get translator info and send that as json
    public function get_translator_info($req, $res, $args)
    {
        $translatorId = $args['id'];
        $translatorData = \App\Models\Translator::get_translator_data_by_id($translatorId, "fname,lname,cell_phone,email,avatar");
        return $res->withJson($translatorData);

    }
    //this function gets user orders from db and renders the page
    public function get_user_orders($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $pendingOrders = $req->getQueryParam("pending");
        $completedOrders = $req->getQueryParam("completed");
        $filtering_options = [];
        if ($pendingOrders && !$completedOrders) {
            $filtering_options['is_done'] = 0;
            $pending = true;
            $completed = false;
        } else if ($completedOrders && !$pendingOrders) {
            $filtering_options['is_done'] = 1;
            $pending = false;
            $completed = true;
        } else {
            $pending = true;
            $completed = true;
        }
        $userOrdersCount = User::get_orders_count_by_user_id($_SESSION['user_id'], $filtering_options);
        $userOrders = User::get_orders_by_user_id($_SESSION['user_id'], $page, 10, $filtering_options);
        
        return $this->view->render($res, "admin/user/orders.twig", ['orders' => $userOrders, 'current_page' => $page, 'orders_count' => $userOrdersCount, 'completed' => $completed, 'pending' => $pending]);
    }
    //this function gets user orders from db and returns them as json
    public function get_user_orders_json($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $pendingOrders = $req->getQueryParam("pending");
        $completedOrders = $req->getQueryParam("completed");
        $filtering_options = [];
        if ($pendingOrders && !$completedOrders) {
            $filtering_options['is_done'] = 0;
        } else if ($completedOrders && !$pendingOrders) {
            $filtering_options['is_done'] = 1;
        }
        $userOrdersCount = User::get_orders_count_by_user_id($_SESSION['user_id'], $filtering_options);
        $userOrders = User::get_orders_by_user_id($_SESSION['user_id'], $page, 10, $filtering_options);
        return $res->withJson(array(
            'orders' => $userOrders,
            'orders_count' => $userOrdersCount,
            'current_page' => $page,
        ));
    }
    //this function renders new order page for user
    public function user_new_order_page($req, $res, $args)
    {
        $userData = User::by_id($_SESSION['user_id'], "fname,lname,phone,email");
        $tokenArray = $this->get_csrf_token($req);
        $userData=array_merge($userData,$tokenArray);
        return $this->view->render($res, "admin/user/new-order.twig", $userData);
    }
    //this function gets order details from db and renders the page
    public function get_order_details($req, $res, $args)
    {
        $orderData = \App\Models\Order::by_id($args['order_id'],false,false,$_SESSION['user_id']);
        if($orderData){
            $orderData['found']=true;
            if ($orderData['translator_id'] != "0") {
                $translatorData = \App\Models\Translator::by_id($orderData['translator_id'], "fname,lname");
                $orderData['translator_fname'] = $translatorData['fname'];
                $orderData['translator_lname'] = $translatorData['lname'];
            }
            
            $orderData['field_of_study']=$orderData['field_of_study'] !='0' ? $this->studyFields[$orderData['field_of_study']] : "عمومی";
            
            $tokenArray = $this->get_csrf_token($req);
            $orderData = array_merge($orderData, $tokenArray);
            return $this->view->render($res, "admin/user/order-details.twig", $orderData);
        }else{
            return $this->view->render($res, "admin/user/order-details.twig", ['found'=>false]);
        }
        
    }

    public function get_message_details($req, $res, $args)
    {
        \App\Models\Message::set_message_reply_as_read($args['msg_id']);
        $messageDetails = \App\Models\Message::get_details_by_id($args['msg_id']);
        return $this->view->render($res, "admin/user/view-message.twig", ['messages' => $messageDetails]);
    }
    public function get_messages_page($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $readQS = $req->getQueryParam("read") === null ? 'unset' : \explode(",", $req->getQueryParam("read"));
        $answeredQS = $req->getQueryParam("answered") === null ? 'unset' : \explode(",", $req->getQueryParam("answered"));
        $filtering_options = [];
        $read = true;
        $unread = true;
        $answered = true;
        $unanswered = true;
        if ($readQS != 'unset') {
            $filtering_options['is_read'] = $readQS;
            if (count($readQS)<2) {
                
                if ($readQS[0] == "0") {
                    
                    $unread = true;
                    $read = false;
                } else {
                    $unread = false;
                    $read = true;
                }
            }
        }
        if ($answeredQS != 'unset') {
            $filtering_options['is_answered'] = $answeredQS;
            if (count($answeredQS)< 2) {
                if ($answeredQS[0] == "0") {
                    $unanswered = true;
                    $answered = false;
                } else {
                    $unanswered = false;
                    $answered = true;
                }
            }
        }

        $userMessages = User::get_messages_by_id($_SESSION['user_id'], $page, 10, $filtering_options);
        $userMessagesCount = User::get_messages_count_by_id($_SESSION['user_id'], $filtering_options);

        return $this->view->render($res, "admin/user/messages.twig", ["messages" => $userMessages,'current_page'=>$page, 'messages_count' => $userMessagesCount, 'read' => $read, 'unread' => $unread, 'answered' => $answered, 'unanswered' => $unanswered]);
    }
    public function get_messages_json($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $readQS = $req->getQueryParam("read") === null ? 'unset' : \explode(",", $req->getQueryParam("read"));
        $answeredQS = $req->getQueryParam("answered") === null ? 'unset' : \explode(",", $req->getQueryParam("answered"));
        $filtering_options = [];
        if ($readQS != 'unset') {
            $filtering_options['is_read'] = $readQS;
        }
        if ($answeredQS != 'unset') {
            $filtering_options['is_answered'] = $answeredQS;
        }

        $userMessages = User::get_messages_by_id($_SESSION['user_id'], $page, 10, $filtering_options);
        $userMessagesCount = User::get_messages_count_by_id($_SESSION['user_id'], $filtering_options);

        return $res->withJson(['messages' => $userMessages, 'messages_count' => intval($userMessagesCount), 'current_page' => $page]);
    }

    //this function gets message data that user sends and return a json respose if it all goes well
    public function post_send_message($req, $res, $args)
    {
        $result = \App\Models\Message::create($_SESSION['user_id'], $req->getParsedBody());
        return $res->withJson([
            'status' => $result,
        ]);
    }

    //this function gets reply message data that user sends and return a json respose if it all goes well
    public function post_reply_message($req, $res, $args)
    {

        $result = \App\Models\Message::create_reply($_SESSION['user_id'], $req->getParsedBody());
        return $res->withJson([
            'status' => $result,
        ]);
    }
    public function edit_profile_page($req, $res, $args)
    {
        $tokens = $this->get_csrf_token($req);
        $userData = User::by_id($_SESSION['user_id'], "username,email,phone,fname,lname");
        $data = ['userData' => $userData];
        $data = array_merge($data, $tokens);
        return $this->view->render($res, "admin/user/edit-profile.twig", $data);
    }
    public function post_edit_profile($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        unset($postFields['csrf_name']);
        unset($postFields['csrf_value']);
        if (!isset($postFields['new_password']) || $postFields['new_password'] == "") {
            unset($postFields['new_password']);
            unset($postFields['old_password']);
            unset($postFields['new_password_confirm']);
            if(!isset($postFields['avatar']) || $postFields['avatar']=="") unset($postFields['avatar']);
            $result = User::edit_by_id($_SESSION['user_id'], $postFields);
            if ($result) {
                $_SESSION['fname'] = $postFields['fname'];
                $_SESSION['lname'] = $postFields['lname'];
                $_SESSION['avatar'] = $postFields['avatar'];
                $_SESSION['phone'] = $postFields['phone'];
                $_SESSION['email'] = $postFields['email'];
                $this->flash->addMessage('profileEditSuccess', "اطلاعات با موفقیت ویرایش شد");
            } else {
                $this->flash->addMessage('profileEditErrors', "خطایی در ثبت اطلاعات رخ داد !");
            }
        } else {
            $oldPassword = User::by_id($_SESSION['user_id'], "password")['password'];
            if ($oldPassword === md5(md5($postFields['old_password']))) {
                if ($postFields['new_password'] === $postFields['new_password_confirm']) {
                    $postFields['password'] = $postFields['new_password'];
                    unset($postFields['new_password']);
                    unset($postFields['old_password']);
                    unset($postFields['new_password_confirm']);
                    if(!isset($postFields['avatar']) || $postFields['avatar']=="") unset($postFields['avatar']);
                    $result = User::edit_by_id($_SESSION['user_id'], $postFields);
                    if ($result) {
                        $this->flash->addMessage('profileEditSuccess', "اطلاعات با موفقیت ویرایش شد");
                    } else {
                        $this->flash->addMessage('profileEditErrors', "خطایی در ثبت اطلاعات رخ داد !");
                    }
                } else {
                    $this->flash->addMessage('profileEditErrors', "فیلد پسورد با فیلد تایید پسورد مطابقت ندارد !");
                }
            } else {
                $this->flash->addMessage('profileEditErrors', "پسورد قبلی اشتباه می باشد !");
            }
        }
        return $res->withRedirect("/user/edit-profile");

    }

    public function upload_avatar($req, $res, $args)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        $directory = dirname(dirname(__DIR__)) . '/public/uploads/avatars/user';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            try {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $_SESSION['avatar'] = $filename;
                return $res->withJson(['filename' => $filename]);
            } catch (\Exception $e) {
                $res->write("error while uploading file "+$e->getMessage())->withStatus(500);
            }
        } else {
            $res->write($uploadedFile->getError())->withStatus(500);
        }
    }

    //order payment for unpaid orders
    public function order_payment($req, $res, $args)
    {
        $orderId = $args['order_id'];
        // $_SESSION['pending_order_id']=$orderId;
        $postFields = $req->getParsedBody();
        $this->payment_gateway = $postFields['gateway'];
        if (!in_array($this->payment_gateway, $this->gateways)) {
            return $res->write("خطایی در پرداخت رخ داد");
        }
        switch ($this->payment_gateway) {
            case "zarinpal":
                $result = $this->zarinpal_payment($orderId, $this->payment_gateway);
                if ($result->Status == 100) {
                    $this->view->render($res, "website/redirect-page.twig", ['redirect_url' => "https://www.zarinpal.com/pg/StartPay/" . $result->Authority, 'message' => "در حال هدایت به درگاه زرین پال", "message_below" => "لطفا صبر کنید ..."]);
                } else {
                    echo "خطایی رخ داد";
                }
                break;
            case "mellat":
                $result = $this->mellat_payment($orderId, $this->payment_gateway);
                break;
        }
    }

    public function save_user_order_info($req, $res, $args)
    {
        $postInfo = $req->getParsedBody();
        $postInfo['orderer_id'] = $_SESSION['user_id'];
        
        // creating a new order
        $orderData = \App\Models\Order::new ($postInfo);
        $priceInfo = $orderData['priceInfo'];
        $orderId = $orderData['orderId'];
        //creating order logs
        $logResult = \App\Models\Order::new_order_log([
            'order_id' => $orderId,
            'order_step' => 1,
        ]);
        if ($orderId && $logResult) {
            $tokenArray = $this->get_csrf_token($req);
            $data = array(
                'success' => true,
                'translation_type' => $postInfo['type'] == "1" ? "عمومی" : "تخصصی",
                'translation_quality' => $postInfo['translation_quality'] == "5" ? "نقره ای" : "طلایی",
                'page_number' => $priceInfo['pageNumber'],
                'duration' => $priceInfo['duration'],
                'final_price' => $priceInfo['price'],
                'order_id' => $orderId,
                'page_title' => "پرداخت سفارش",
            );
            $data = \array_merge($data, $tokenArray);
            $this->view->render($res, "website/order-result.twig", $data);
        }
    }

    // START payment functions for unpaid order
    protected function mellat_payment($orderId, $gateway)
    {
        $orderData = \App\Models\Order::by_id($orderId);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $orderPriceRial = \intval($orderData['order_price']) * 10;
        $payment->set_info(array(
            'order_id' => $orderId,
            'price' => $orderPriceRial,
            'callback_url' => Config::BASE_URL . '/payment-success/' . $orderData['order_id'],
        ));
        return $payment->pay();
    }
    protected function zarinpal_payment($orderId, $gateway)
    {
        $orderData = \App\Models\Order::by_id($orderId);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $payment->set_info(array(
            'callback_url' => Config::BASE_URL . '/payment-success/' . $orderData['order_id'],
            'price' => $orderData['order_price'],
            'description' => 'خرید از وبسایت مترجم وان',
        ));
        $result = $payment->pay();
        return $result;

    }
    // END payment functions for unpaid order
    //////////////////////////////////////////////
    // END Customer(User) ADMIN Functionsخقیث
    //////////////////////////////////////////////

    #endregion

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
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (!filter_var($userInfo['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $subject = "ثبت نام در مترجم وان";
        $userFname = $userInfo['fname'];
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
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $subject = "لینک تغییر پسورد";
        $userFname = User::by_email($email, "fname")['fname'];
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
