<?php
namespace App\Controllers;

use App\Models\Order;
use App\Models\Translator;
use App\Models\Ticket;
use App\Models\Notification;
use Core\Controller;

class TranslatorPanelController extends Controller
{
    private $bankNames = ["ملت", "ملی", "صادرات", "پاسارگاد", "آینده", "پارسیان", "سامان", "مهر اقتصاد", "مهر ایران", "سپه", "کشاورزی", "قوامین", "انصار", "اقتصاد نوین", "رسالت", "پست بانک", "رفاه کارگران", "شهر", "دی", "گردشگری", "تجارت", "مسکن"];
    public function get_dashboard($req, $res, $args)
    {
        $data = [];
        if ($_SESSION['is_employed']) {
            $data['new_orders'] = Order::get_orders_without_requested_by_user_id($_SESSION['user_id'], 1, 3);
            $data['lastMessages'] = Ticket::get_tickets_by_user_id($_SESSION['user_id'],"2", 1, 3);
            $data['unread_messages_count'] = Ticket::get_unread_tickets_count_by_user_id($_SESSION['user_id'],"2");
            $data['translator_orders_count'] = Order::get_orders_count_by_user_id($_SESSION['user_id']);
            $data['translator_revenue'] = number_format(\Core\Model::select("translator_account", "account_credit", ['translator_id' => $_SESSION['user_id']], true)['account_credit']);
        } else {
            $data['study_fields'] = Order::get_study_fields();
        }
        return $this->view->render($res, "admin/translator/dashboard.twig", $data);
    }
    public function get_last_tickets_json($req,$res,$args)
    {
        $lastThreeTickets = Ticket::get_tickets_by_user_id($_SESSION['user_id'],"2", 1, 3);
        return $res->withJson(["tickets"=>$lastThreeTickets]);
    }
    public function get_test_json($req, $res, $args)
    {
        $language = $req->getParam("language");
        $studyField = $req->getParam("study_field");
        $test = Translator::get_test_by_filtering($language, $studyField);
        $test['status'] = $test ? true : false;
        return $res->withJson($test);
    }
    public function save_test_data($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $body['translator_id'] = $_SESSION['user_id'];
        $result = Translator::save_test_data($body);
        if ($result) {
            return $res->withJson(array(
                'status' => true,
            ));
        }
        return $res->withJson(array(
            'status' => false,
            'error' => 'مشکلی در ذخیره پاسخ شما رخ داد !',
        ));
    }
    public function get_order_info($req, $res, $args)
    {
        $orderNumber = $args['order_number'];
        $orderData = Order::by_number($orderNumber, false, true);
        return $res->withJson($orderData);
    }
    //requesting to do the translation by translator
    public function request_order($req, $res, $args)
    {
        $body = $req->getParsedBody();
        if (Order::request_order($body['translator_id'], $body['order_number'])) {
            return $res->withJson(['status' => true]);
        }
        return $res->withJson(['status' => false]);
    }

    //declining the translation by translator
    public function decline_order($req, $res, $args)
    {
        $body = $req->getParsedBody();
        if (Order::deny_order($body['translator_id'], $body['order_number'])) {
            return $res->withJson(['status' => true]);
        }
        return $res->withJson(['status' => false]);
    }
    //get new unaccepted orders as json
    public function get_new_orders_json($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $offset = $req->getQueryParam("offset") ? $req->getQueryParam("offset") : 10;
        $choice = $req->getQueryParam("choice") ? $req->getQueryParam("choice") : "new";
        $orders_count = Order::new_orders_count($_SESSION['user_id'], $choice);
        if ($choice == "new") {
            $orders = Order::get_orders_without_requested_by_user_id($_SESSION['user_id'], $page, $offset);
        } else if ($choice == "requested") {
            $orders = Order::get_requested_orders_data_by_user_id($_SESSION['user_id'], $page, $offset);
        } else {
            $orders = Order::get_denied_orders_data_by_user_id($_SESSION['user_id'], $page, $offset);
        }
        return $res->withJson(['orders' => $orders, 'status' => true, 'choice' => $choice, 'current_page' => $page, 'orders_count' => $orders_count, 'translator_id' => $_SESSION['user_id']]);
    }
    //get new orders and render the page
    public function get_new_orders($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $offset = $req->getQueryParam("offset") ? $req->getQueryParam("offset") : 10;
        $choice = $req->getQueryParam("choice") ? $req->getQueryParam("choice") : "new";
        if ($choice == "new") {
            $data['new_orders'] = Order::get_orders_without_requested_by_user_id($_SESSION['user_id'], $page, $offset);
        } else if ($choice == "requested") {
            $data['new_orders'] = Order::get_requested_orders_data_by_user_id($_SESSION['user_id'], $page, $offset);
        } else {
            $data['new_orders'] = Order::get_denied_orders_data_by_user_id($_SESSION['user_id'], $page, $offset);
        }
        $data['new_orders_count'] = Order::new_orders_count($_SESSION['user_id'], $choice);
        $data['current_page'] = $page;
        $data['choice'] = $choice;
        return $this->view->render($res, "admin/translator/new-orders.twig", $data);
    }
    //get orders that translator have to do or did and render the page with that
    public function get_translator_orders($req, $res, $args)
    {
        $data = [];
        $page = $req->getParam("page") ? $req->getParam("page") : 1;
        $isDone = $req->getParam("done") != null || strlen($req->getParam("done")) > 0 ? \explode(",", $req->getParam("done")) : [1, 0];
        $data['pending'] = true;
        $data['completed'] = true;
        if (\count($isDone) < 2) {
            switch ($isDone[0]) {
                case "0":
                    $data['completed'] = false;
                    break;
                case "1":
                    $data['pending'] = false;
                    break;
            }
        }
        $data['orders'] = Translator::get_translator_orders_by_user_id($_SESSION['user_id'], $page, 10, ['is_done' => $isDone]);
        $data['orders_count'] = Translator::get_translator_orders_count_by_user_id($_SESSION['user_id'], ['is_done' => $isDone]);
        $data['current_page'] = $page;
        return $this->view->render($res, "admin/translator/translator_orders.twig", $data);
    }
    //get orders that translator have to do or did and render the page with that
    public function get_translator_orders_json($req, $res, $args)
    {
        $data = [];
        $page = $req->getParam("page") ? $req->getParam("page") : 1;
        $isDone = $req->getParam("done") != null || strlen($req->getParam("done")) > 0 ? \explode(",", $req->getParam("done")) : [1, 0];
        $data['orders'] = Translator::get_translator_orders_by_user_id($_SESSION['user_id'], $page, 10, ['is_done' => $isDone]);
        $data['orders_count'] = Translator::get_translator_orders_count_by_user_id($_SESSION['user_id'], ['is_done' => $isDone]);
        $data['current_page'] = $page;
        return $res->withJson($data);
    }

    //get bank account info and render the page
    public function get_account_info_page($req, $res, $args)
    {
        $bankInfo = Translator::get_bank_info_by_user_id($_SESSION['user_id']);
        if ($bankInfo) {
            $bankInfo['card_number'] = $this->format_credit_card($bankInfo['card_number'], "-");
            $bankInfo['shaba_number'] = "IR ".$this->format_shaba_number($bankInfo['shaba_number'], "-");
        }
        return $this->view->render($res, "admin/translator/bank-info.twig", $bankInfo);
    }

    //render back info edit page
    public function get_bank_info_edit_page($req, $res, $args)
    {
        if (isset($_SESSION['oldBankInfoData'])) {
            $bankInfo = $_SESSION['oldBankInfoData'];
            unset($_SESSION['oldBankInfoData']);
        } else {
            $bankInfo = Translator::get_bank_info_by_user_id($_SESSION['user_id']);
            if ($bankInfo) {
                $bankInfo['card_number'] = $this->format_credit_card($bankInfo['card_number']);
                $bankInfo['shaba_number'] = $this->format_shaba_number($bankInfo['shaba_number']);
            }
        }
        $bankInfo['banks'] = $this->bankNames;
        return $this->view->render($res, "admin/translator/edit-bank-info.twig", $bankInfo);
    }
    //edit bank info for translator
    public function post_edit_bank_info($req, $res, $args)
    {
        $body = $req->getParsedBody();
        $result = Translator::save_bank_info($_SESSION['user_id'], $body);
        if ($result) {
            return $res->withRedirect("/translator/bank-info");
        }
        $this->flash->addMessage('bankInfoErrors', "خطایی در ذخیره اطلاعات رخ داد !");
        $_SESSION['oldBankInfoData'] = $body;
        return $res->withRedirect("/translator/bank-info/edit");
    }

    public function get_account_report_page($req,$res,$args)
    {
        $data=[];
        $orderPage=$req->getParam("order_page") ? $req->getParam("order_page"):1;
        $checkoutPage=$req->getParam("checkout_page") ? $req->getParam("checkout_page"):1;
        $checkoutRequestPage=$req->getParam("checkout_request_page") ? $req->getParam("checkout_request_page"):1;
        //get revenue and ccount credit
        $accountInfo=Translator::get_account_info_by_user_id($_SESSION['user_id']);
        //get orders that user completed
        $completedOrders=Translator::get_completed_orders_by_user_id($_SESSION['user_id'],$orderPage,10);
        //get count of orders that user completed
        $completedOrdersCount=Translator::get_completed_orders_count_by_user_id($_SESSION['user_id']);
        //get all checkouts done by translator
        $checkouts=Translator::get_account_checkouts_by_user_id($_SESSION['user_id'],$checkoutPage,10);
        //get all checkouts count done by translator
        $checkoutsCount=Translator::get_account_checkouts_count_by_user_id($_SESSION['user_id']);
        
        //get checkout requests that trnslator sent to admin
        $checkoutRequests=Translator::get_account_checkout_requests_by_user_id($_SESSION['user_id'],$checkoutRequestPage,10);
        //get count of checkout requests that trnslator sent to admin
        $checkoutRequestsCount=Translator::get_account_checkout_requests_count_by_user_id($_SESSION['user_id']);
        //get total checkout price done by translator
        $totalCheckouts=Translator::get_total_checkout_price_by_user_id($_SESSION['user_id']);
        $totalCheckouts=$totalCheckouts ? $totalCheckouts:0;

        $data['revenue']=number_format($accountInfo['revenue']);
        $data['account_balance']=number_format($accountInfo['account_credit']);
        $data['completed_orders']=$completedOrders;
        $data['completed_orders_count']=$completedOrdersCount;
        $data['completed_orders_current_page']=$orderPage;
        $data['checkout_logs']=$checkouts;
        $data['checkouts_count']=$checkoutsCount;
        $data['checkouts_current_page']=$checkoutPage;
        $data['checkoutـrequest_logs']=$checkoutRequests;
        $data['checkoutـrequest_logs_count']=$checkoutRequestsCount;
        $data['checkoutـrequest_logs_current_page']=$checkoutRequestPage;
        $data['total_checkouts']=\number_format($totalCheckouts);
        // $data=array_merge($data,$this->get_csrf_token($req));
        return $this->view->render($res,"admin/translator/account-report.twig",$data);
    }

    //save translator checkout request in db
    public function post_request_checkout($req,$res,$args)
    {
        $body=$req->getParsedBody();
        //remove ',' from amount value
        $body['amount']=\preg_replace("/\,/","",$body['amount']);
        //check if the amount that a translator requested is not more than his credit
        $accountInfo=Translator::get_account_info_by_user_id($_SESSION['user_id']);
        if(intval($body['amount']) > intval($accountInfo['account_credit'])) return $res->withJson(['status'=>false,'message'=>'مبلغ درخواستی نمی تواند بیشتر از موجودی حسابتان باشد !']);
        $totalCheckoutRequestPrice=Translator::get_total_checkout_requests_price_by_user_id($_SESSION['user_id']);
        $totalCheckoutRequestPrice= $totalCheckoutRequestPrice ? intval($totalCheckoutRequestPrice):$totalCheckoutRequestPrice;
        if(($totalCheckoutRequestPrice+intval($body['amount']))>$accountInfo['account_credit']) return $res->withJson(['status'=>false,'message'=>'مجموع درخواست های شما از موجودی حساب می باشد !']);
        //save the request
        $result=Translator::request_checkout($body);
        if(!$result) return $res->withJson(['status'=>false,'message'=>'خطایی در ارسال درخواست رخ داد !']);
        return $res->withJson(['status'=>true]);
    }

    //get checkout requests data as json
    public function get_checkout_requests_json($req,$res,$args)
    {
        $page=$req->getParam("page") ? $req->getParam("page"):1;
        //get checkout requests that trnslator sent to admin
        $checkoutRequests=Translator::get_account_checkout_requests_by_user_id($_SESSION['user_id'],$page,10);
        //get count of checkout requests that trnslator sent to admin
        $checkoutRequestsCount=Translator::get_account_checkout_requests_count_by_user_id($_SESSION['user_id']);
        return $res->withJson(['requests'=>$checkoutRequests,'count'=>$checkoutRequestsCount,'current_page'=>$page]);
    }

    public function get_tickets_page($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $state = $req->getQueryParam("state") === null ? ['read','unread','waiting','answered'] : \explode(",", $req->getQueryParam("state"));
        $userTickets = Ticket::get_tickets_by_user_id($_SESSION['user_id'],"2", $page, 10, ['state'=>$state]);
        $userTicketsCount = Ticket::get_tickets_count_by_user_id($_SESSION['user_id'],"2", $state);
        return $this->view->render($res, "admin/translator/tickets.twig", ["tickets" => $userTickets,'current_page'=>$page, 'tickets_count' => $userTicketsCount,'state'=>$state]);
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
        return $this->view->render($res, "admin/translator/view-ticket.twig", ['ticket_details' => $ticketDetails,'ticket_messages'=>$ticketMessages,"last_ticket_id"=>$lastTicketId]);
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
    public function get_tickets_json($req, $res, $args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $state = $req->getQueryParam("state") === null ? ['read','unread','waiting','answered'] : \explode(",", $req->getQueryParam("state"));
        $userTickets = Ticket::get_tickets_by_user_id($_SESSION['user_id'],"2", $page, 10, ['state'=>$state]);
        $userTicketsCount = Ticket::get_tickets_count_by_user_id($_SESSION['user_id'],"2", ['state'=>$state]);
        return $res->withJson(['tickets' => $userTickets, 'tickets_count' => intval($userTicketsCount), 'current_page' => $page]);
    }

    //this function gets message data that user sends and return a json respose if it all goes well
    public function post_send_ticket($req, $res, $args)
    {
        $ticketNumber = Ticket::create($_SESSION['user_id'],"2", $req->getParsedBody());
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

    //render edit profile page
    public function get_edit_profile_page($req,$res,$args)
    {
        $tokens = $this->get_csrf_token($req);
        $userData = Translator::by_id($_SESSION['user_id']);
        $data = ['userData' => $userData];
        $data = array_merge($data, $tokens);
        return $this->view->render($res, "admin/translator/edit-profile.twig", $data);
    }
    //edit profile data 
    public function post_edit_profile($req,$res,$args)
    {
        $postFields=$req->getParsedBody();
        $status=true;
        $message="";
        if (!isset($postFields['new_password']) || $postFields['new_password'] == "") {
            unset($postFields['new_password']);
            unset($postFields['old_password']);
            unset($postFields['new_password_confirm']);
            if(!isset($postFields['avatar']) || $postFields['avatar']==""){
                unset($postFields['avatar']);
            } 
            if(!isset($postFields['melicard_photo']) || $postFields['melicard_photo']==""){
                unset($postFields['melicard_photo']);
            }
            $result = Translator::edit_by_id($_SESSION['user_id'], $postFields);
            if ($result['status']) {
                $_SESSION['fname'] = $postFields['fname'];
                $_SESSION['lname'] = $postFields['lname'];
                if(isset($postFields['avatar'])){
                    $_SESSION['avatar']= $postFields['avatar'];
                }
                $_SESSION['phone'] = $postFields['cell_phone'];
                $_SESSION['email'] = $postFields['email'];
                $message=$result['message'];
            } else {
                $status=false;
                $message=$result['message'];
            }
        } else {
            $oldPassword = Translator::by_id($_SESSION['user_id'], "password")['password'];
            if ($oldPassword === md5(md5($postFields['old_password']))) {
                if ($postFields['new_password'] === $postFields['new_password_confirm']) {
                    $postFields['password'] = $postFields['new_password'];
                    unset($postFields['new_password']);
                    unset($postFields['old_password']);
                    unset($postFields['new_password_confirm']);
                    if(!isset($postFields['avatar']) || $postFields['avatar']==""){
                        unset($postFields['avatar']);
                    } 
                    if(!isset($postFields['melicard_photo']) || $postFields['melicard_photo']==""){
                        unset($postFields['melicard_photo']);
                    }
                    $result = Translator::edit_by_id($_SESSION['user_id'], $postFields);
                    if ($result['status']) {
                        $_SESSION['fname'] = $postFields['fname'];
                        $_SESSION['lname'] = $postFields['lname'];
                        if(isset($postFields['avatar'])){
                            $_SESSION['avatar']= $postFields['avatar'];
                        }
                        $_SESSION['phone'] = $postFields['cell_phone'];
                        $_SESSION['email'] = $postFields['email'];
                        $message="اطلاعات با موفقیت ویرایش شد";
                    } else {
                        $status=false;
                        $message=$result['message'];
                    }
                    
                } else {
                    $status=false;
                    $message="فیلد پسورد با فیلد تایید پسورد مطابقت ندارد !";
                }
            } else {
                $status=false;
                $message= "پسورد قبلی اشتباه می باشد !";
            }
        }
        return $res->withJson(['status'=>$status,'message'=>$message]);
    }


    //validate profile page values
    // protected function validate_profile_data($req,$res,$args)
    // {
        
    // }
    //upload the translator avatar
    public function upload_avatar($req,$res,$args)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        $directory = dirname(dirname(__DIR__)) . '/public/uploads/avatars/translator';
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

    //upload the translator melicard photo
    public function upload_melicard_photo($req,$res,$args)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        $directory = dirname(dirname(__DIR__)) . '/public/uploads/translator/melicard';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            try {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                return $res->withJson(['filename' => $filename]);
            } catch (\Exception $e) {
                $res->write("error while uploading file "+$e->getMessage())->withStatus(500);
            }
        } else {
            $res->write($uploadedFile->getError())->withStatus(500);
        }
    }
    //render notification page
    public function get_notifications_page($req,$res,$args)
    {
        $globalPage=$req->getParam("global_page") ? $req->getParam("global_page"):1;
        $privatePage=$req->getParam("private_page") ? $req->getParam("private_page"):1;
        $notifications=Notification::get_global_notifications($globalPage,10);
        $notificationsCount=Notification::get_global_notifications_count();
        $translatorNotifications=Notification::get_private_notifications_by_user_id($_SESSION['user_id'],$privatePage,10);
        $translatorNotificationsCount=Notification::get_private_notifications_count_by_user_id($_SESSION['user_id']);
        return $this->view->render($res,"/admin/translator/notifications.twig",['global_notifications'=>$notifications,"private_notifications"=>$translatorNotifications,"global_notifications_count"=>$notificationsCount,"private_notifications_count"=>$translatorNotificationsCount,"global_current_page"=>$globalPage,"private_current_page"=>$privatePage]);
    }

    //format credit card
    protected function format_credit_card($creditCard, $delimiter = " ")
    {
        // $creditCardNew=preg_replace("(\d{4})","$0{$delimiter}",$creditCard);
        // return substr($creditCardNew,0,strlen($creditCardNew)-1);
        preg_match_all("(\d{4})", $creditCard, $matches);
        return \implode($delimiter, $matches[0]);
    }
    protected function format_shaba_number($shaba, $delimiter = " ")
    {

        preg_match_all("((\d{2})(\d{4})(\d{4})(\d{4})(\d{4})(\d{4})(\d{2}))", $shaba, $matches);
        unset($matches[0]);
        $matches = array_map(function ($match) {
            return $match[0];
        }, $matches);
        return implode($delimiter, $matches);
    }
}
