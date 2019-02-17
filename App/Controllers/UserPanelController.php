<?php
namespace App\Controllers;

use App\Dependencies\Pay\Payment;
use App\Models\User;
use Core\Config;
use Core\Controller;

class UserPanelController extends Controller
{

    private $gateways = ['mellat', 'zarinpal'];
    private $payment;
    private $payment_gateway;


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


}
