<?php
namespace App\Controllers;

use App\Dependencies\Pay\Payment;
use App\Models\User;
use App\Models\Ticket;
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
        $unreadTicketsCount = Ticket::get_unread_tickets_count_by_user_id($_SESSION['user_id'],"1");
        $lastThreeTickets = Ticket::get_tickets_by_user_id($_SESSION['user_id'],"1", 1, 3);
        
        $this->view->render($res, "admin/user/dashboard.twig", ['orders' => $userOrders, 'completedOrdersCount' => $completedOrdersCount, 'workingOrdersCount' => $workingOrdersCount, 'unreadTicketsCount' => $unreadTicketsCount, 'lastTickets' => $lastThreeTickets]);
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
        $orderData = \App\Models\Order::by_number($args['order_number'],false,false,$_SESSION['user_id']);
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

    public function get_ticket_details($req, $res, $args)
    {
        Ticket::set_as_read($args['ticket_number']);
        $ticketDetails = Ticket::get_details_by_ticket_number($args['ticket_number']);
        if(!$ticketDetails){
            return var_dump("تیکت موجود نمی باشد");
        }
        if($ticketDetails['creator_id']!=$_SESSION['user_id']){
            return var_dump("شما اجازه دسترسی به این تیکت را ندارید");
        }
        $ticketMessages=Ticket::get_ticket_messages_by_ticket_number($args['ticket_number']);
        $lastTicketId=$ticketMessages[0]['ticket_id'];        
        return $this->view->render($res, "admin/user/view-ticket.twig", ['ticket_details' => $ticketDetails,'ticket_messages'=>$ticketMessages,"last_ticket_id"=>$lastTicketId]);
    }
    public function get_ticket_details_json($req, $res, $args)
    {
        Ticket::set_as_read($args['ticket_number']);
        $ticketDetails = Ticket::get_details_by_ticket_number($args['ticket_number']);
        if($ticketDetails['creator_id']!=$_SESSION['user_id']){
            return $res->withJson(['status'=>false,'message'=>"شما اجازه دسترسی به این تیکت را ندارید"]);
        }
        $ticketMessages=Ticket::get_ticket_messages_by_ticket_number($args['ticket_number']);
        return $res->withJson(['status'=>true,'tickets'=>$ticketMessages,'date'=>User::get_current_date_persian()]);
        
    }
    public function get_tickets_page($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $state = $req->getQueryParam("state") === null ? ['read','unread','waiting','answered'] : \explode(",", $req->getQueryParam("state"));
        $userTickets = Ticket::get_tickets_by_user_id($_SESSION['user_id'],"1", $page, 10, ['state'=>$state]);
        $userTicketsCount = Ticket::get_tickets_count_by_user_id($_SESSION['user_id'],"1", $state);
        return $this->view->render($res, "admin/user/tickets.twig", ["tickets" => $userTickets,'current_page'=>$page, 'tickets_count' => $userTicketsCount,'state'=>$state]);
    }
    public function get_tickets_json($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $state = $req->getQueryParam("state") === null ? ['answered','waiting'] : \explode(",", $req->getQueryParam("state"));
        $read = $req->getQueryParam("read") === null ? [0,1] : \explode(",", $req->getQueryParam("read"));
        $userTickets = Ticket::get_tickets_by_user_id($_SESSION['user_id'],"1", $page, 10, ['state'=>$state,'read'=>$read]);
        $userTicketsCount = Ticket::get_tickets_count_by_user_id($_SESSION['user_id'],"1", $state);
        return $res->withJson(['tickets' => $userTickets, 'tickets_count' => intval($userTicketsCount), 'current_page' => $page]);
    }

    //this function gets message data that user sends and return a json respose if it all goes well
    public function post_send_ticket($req, $res, $args)
    {
        $ticketNumber = Ticket::create($_SESSION['user_id'],"1", $req->getParsedBody());
        if($ticketNumber){
            return $res->withJson([
                'status' => true,
                'ticket_number'=>$ticketNumber
            ]);
        }
        return $res->withJson([
            'status' => false
        ]);
    }

    //this function gets reply message data that user sends and return a json respose if it all goes well
    public function post_reply_ticket($req, $res, $args)
    {
        $ticketData=$req->getParsedBody();
        $ticketData['parent_ticket_id']=$args['ticket_id'];
        $result = Ticket::create_reply($_SESSION['user_id'], $ticketData);
        return $res->withJson([
            'status' => $result
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
        $orderNumber = $args['order_number'];
        // $_SESSION['pending_order_id']=$orderId;
        $postFields = $req->getParsedBody();
        $this->payment_gateway = $postFields['gateway'];
        if (!in_array($this->payment_gateway, $this->gateways)) {
            return $res->write("خطایی در پرداخت رخ داد");
        }
        switch ($this->payment_gateway) {
            case "zarinpal":
                $result = $this->zarinpal_payment($orderNumber, $this->payment_gateway);
                if ($result->Status == 100) {
                    $this->view->render($res, "website/redirect-page.twig", ['redirect_url' => "https://www.zarinpal.com/pg/StartPay/" . $result->Authority, 'message' => "در حال هدایت به درگاه زرین پال", "message_below" => "لطفا صبر کنید ..."]);
                } else {
                    echo "خطایی رخ داد";
                }
                break;
            case "mellat":
                $result = $this->mellat_payment($orderNumber, $this->payment_gateway);
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
        $orderNumber = $orderData['orderNumber'];
        var_dump($orderData);
        //creating order logs
        $logResult = \App\Models\Order::new_order_log($orderNumber,[
            'order_step'=>1
        ]);
        if ($orderNumber && $logResult) {
            $tokenArray = $this->get_csrf_token($req);
            $data = array(
                'success' => true,
                'translation_type' => $postInfo['type'] == "1" ? "عمومی" : "تخصصی",
                'translation_quality' => $postInfo['translation_quality'] == "5" ? "نقره ای" : "طلایی",
                'page_number' => $priceInfo['pageNumber'],
                'duration' => $priceInfo['duration'],
                'final_price' => $priceInfo['price'],
                'order_id' => $orderNumber,
                'page_title' => "پرداخت سفارش",
            );
            $data = \array_merge($data, $tokenArray);
            $this->view->render($res, "website/order-result.twig", $data);
        }
    }

    // START payment functions for unpaid order
    protected function mellat_payment($orderNumber, $gateway)
    {
        $orderData = \App\Models\Order::by_number($orderNumber);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $orderPriceRial = \intval($orderData['order_price']) * 10;
        $payment->set_info(array(
            'order_id' => $orderNumber,
            'price' => $orderPriceRial,
            'callback_url' => Config::BASE_URL . '/payment-success/' . $orderData['order_number'],
        ));
        return $payment->pay();
    }
    protected function zarinpal_payment($orderNumber, $gateway)
    {
        $orderData = \App\Models\Order::by_number($orderNumber);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $payment->set_info(array(
            'callback_url' => Config::BASE_URL . '/payment-success/' . $orderData['order_number'],
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
