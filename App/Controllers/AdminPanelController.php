<?php
namespace App\Controllers;
use Core\Controller;
use Core\Config;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Translator;
use App\Models\User;
use App\Models\Notification;
class AdminPanelController extends Controller
{

    //START auth functions
    public function get_login($req,$res,$args)
    {
        if(isset($_SESSION['is_admin_logged_in'])) return $res->withRedirect("/admin");
        return $this->view->render($res,"/admin/admin/login.twig");
    }
    public function post_login($req,$res,$args)
    {
        $postFields = $req->getParsedBody();
        $username = $postFields['username'];
        $password = $postFields['password'];
        if ($username == "") {
            $this->flash->addMessage('loginError', "فیلد نام کاربری نباید خالی باشد !");
            return $res->withRedirect('/admin/login');
        }
        if ($password == "") {
            $this->flash->addMessage('loginError', "فیلد پسورد نباید خالی باشد !");
            $_SESSION['oldLoginFields'] = array(
                'username' => $username,
            );
            return $res->withRedirect('/admin/login');
        }
        $adminData = Admin::by_username($username, "*");
        if ($adminData) {
            if ($adminData['password'] == \md5(\md5($password))) {
                    //check if user is admin or not
                    if($adminData['level']=="2"){
                        $this->flash->addMessage('loginError', "شما نمی توانید به پنل ادمین دسترسی داشته باشید :)");
                        return $res->withRedirect("/admin/login");
                    }
                    $_SESSION['is_admin_logged_in'] = true;
                    $_SESSION['fname'] = $adminData['fname'];
                    $_SESSION['lname'] = $adminData['lname'];
                    $_SESSION['avatar'] = $adminData['avatar'];
                    $_SESSION['user_id'] = $adminData['translator_id'];
                    // $_SESSION['username'] = $userData['user_id'];
                    $_SESSION['phone'] = $adminData['cell_phone'];
                    $_SESSION['email'] = $adminData['email'];
                    //user level that logged in valid values are : user,admin,translator
                    $_SESSION['user_type'] = "admin";
                    return $res->withRedirect('/admin');
            } else {
                $this->flash->addMessage('loginError', "پسورد وارد شده اشتباه می باشد !");
                $_SESSION['oldLoginFields'] = array(
                    'username' => $username,
                    'password' => $password,
                );
                return $res->withRedirect('/admin/login');
            }
        } else {
            $this->flash->addMessage('loginError', "کاربری با این مشخصات یافت نشد !");
            $_SESSION['oldLoginFields'] = array(
                'username' => $username,
                'password' => $password,
            );
            return $res->withRedirect('/admin/login');
        }

    }
    //logout process for admin
    public function logout($req, $res, $args)
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['is_admin_logged_in']);
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
    //END auth functions

    public function dashboard($req,$res,$args)
    {
        $data=[];
        //count of new orders that is not accepted by any translator yet
        $data['new_orders_count']=Order::get_new_unaccepted_orders_count();
        //count of orders that is accepted but is not done anymore
        $data['pending_orders_count']=Order::get_pending_orders_count();
        //count of orders that is accepted and a translator had completed it
        $data['completed_orders_count']=Order::get_completed_orders();
        //get total revenue till now
        $data['total_revenue']=Admin::get_total_revenue_with_filtering(['done'=>["0","1"],'from_date'=>"0",'to_date'=>jstrftime("%Y/%m/%d %H:%M","","","","en")]);
        $data['admin_revenue']=(intval($data['total_revenue'])*15)/100;
        $data['masoud_revenue']=(intval($data['total_revenue'])*15)/100;
        $data['translators_revenue']=(intval($data['total_revenue'])*70)/100;
        $data['payment_requests_count']=Admin::get_translator_payment_requests_count(['state'=>['-1','0','1'],'is_paid'=>['0','1']]);
        //get unread messages
        $data['unread_messages_count']=Admin::get_unread_tickets_count();
        //get last three tickets sent by translators
        $data['last_translator_tickets']=Ticket::get_translator_tickets(1,3);
        //get last three tickets sent by customers
        $data['last_customer_tickets']=Ticket::get_customer_tickets(1,3);
        //get new translator employment requests
        $data['translator_employment_requests']=Admin::get_employment_requests(1,3);
        //get translator requests for doing orders
        $data['translator_order_requests']=Admin::get_translator_order_requests(1,3);
        return $this->view->render($res,"admin/admin/dashboard.twig",$data);
    }
    
    public function all_translator_info_json($req,$res,$args)
    {
        $translatorId=$req->getParam("translator_id");
        $data=['status'=>true];
        //get translator data and test data in one function and send it as json
        $translatorAndTestData=Admin::get_translator_test_info_by_user_id($translatorId);
        $data['status']=$translatorAndTestData && count($translatorAndTestData)<1 ? false:true;
        $data['info']=$translatorAndTestData;
        return $res->withJson($data);
    }
    public function basic_translator_info_json($req,$res,$args)
    {
        $translatorId=$req->getParam("translator_id");
        $data=['status'=>true];
        //get translator data and test data in one function and send it as json
        $translatorData=Admin::get_translator_basic_info_by_user_id($translatorId);
        $data['status']=$translatorData && count($translatorData)<1 ? false:true;
        $data['info']=$translatorData;
        return $res->withJson($data);
    }
    public function ticket_details_json($req,$res,$args)
    {
        $ticketNumber=$req->getParam("ticket_number");
        $userType=$req->getParam("user_type");
        $ticketDetails=Ticket::admin_get_details_by_ticket_number($ticketNumber,$userType);
        $ticketMessages=Ticket::get_ticket_messages_by_ticket_number($ticketNumber);
        $lastMessageId=$ticketMessages[0]['ticket_id'];
        if(!$ticketDetails){
            return $res->withJson(['status'=>false,'message'=>'پیام درخواستی یافت نشد !']);
        }
        return $res->withJson(['status'=>true,'info'=>$ticketDetails,'messages'=>$ticketMessages,'last_ticket_id'=>$lastMessageId]);
    }
    //get order details and send it as json
    public function order_info_json($req,$res,$args)
    {
        $orderNumber = $args['order_number'];
        $orderData = Order::by_number($orderNumber, false, true);
        return $res->withJson($orderData);
    }

    //employ the new translator
    public function post_employ_translator($req,$res,$args)
    {
        $postFields=$req->getParsedBody();
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        if($postFields['token']===$hash){
            $result=Translator::employ($postFields['translator_id']);
            if($result){
                return $res->withJson(['status'=>true]);
            }
            return $res->withJson(['status'=>false,'message'=>'error in saving data!']);
        }else{
            return $res->withJson(['status'=>false,'message'=>'invalid token!']);
        }
    }
    //deny employment request from translator
    public function post_deny_translator($req,$res,$args)
    {
        $postFields=$req->getParsedBody();
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        if($postFields['token']===$hash){
            $result=Translator::deny_employment($postFields['translator_id']);
            if($result){
                return $res->withJson(['status'=>true]);
            }
            return $res->withJson(['status'=>false,'message'=>'error in saving data!']);
        }else{
            return $res->withJson(['status'=>false,'message'=>'invalid token!']);
        }
    }
    public static function accept_translator_order_request($req,$res,$args)
    {
        $postFields=$req->getParsedBody();
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        if($postFields['token']===$hash){
            $result=Order::accept_translator_order_request($postFields['request_id'],$postFields['translator_id']);
            if($result){
                return $res->withJson(['status'=>true]);
            }
            return $res->withJson(['status'=>false,'message'=>'error in saving data!']);
        }else{
            return $res->withJson(['status'=>false,'message'=>'invalid token!']);
        }
    }
    public static function deny_translator_order_request($req,$res,$args)
    {
        $postFields=$req->getParsedBody();
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        if($postFields['token']===$hash){
            $result=Order::deny_translator_order_request($postFields['request_id']);
            if($result){
                return $res->withJson(['status'=>true]);
            }
            return $res->withJson(['status'=>false,'message'=>'error in saving data!']);
        }else{
            return $res->withJson(['status'=>false,'message'=>'invalid token!']);
        }
    }

    //reply to tickets
    public function post_reply_ticket($req,$res,$args)
    {
        $postFields=$req->getParsedBody();
        $result=Ticket::create_reply("0",$postFields,"answered");
        if($result){
            return $res->withJson(['status'=>true]);
        }
        return $res->withJson(['status'=>false,'message'=>'خطایی در ارسال پیام شما رخ داد !']);
    }
    //render customer tickets page
    public function customer_tickets_page($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $state = $req->getQueryParam("state") === null ? ['waiting','answered'] : \explode(",", $req->getQueryParam("state"));
        //get tickets sent by customers
        $tickets=Ticket::get_customer_tickets($page,10,['state'=>$state]);
        $ticketsCount=Ticket::get_tickets_count(1,['state'=>$state]);
        return $this->view->render($res,"/admin/admin/tickets.twig",["tickets" => $tickets,'current_page'=>$page, 'tickets_count' => $ticketsCount,'state'=>$state,'page_title'=>'پیام های مشتریان','page_url'=>'/admin/tickets/customer','user_type'=>'user']);
    }
    //render customer tickets as json
    public function customer_tickets_json($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $state = $req->getQueryParam("state") === null ? ['waiting','answered'] : \explode(",", $req->getQueryParam("state"));
        //get tickets sent by customers
        $tickets=Ticket::get_customer_tickets($page,10,['state'=>$state]);
        $ticketsCount=Ticket::get_tickets_count(1,['state'=>$state]);
        return $res->withJson(["tickets" => $tickets,'current_page'=>$page, 'tickets_count' => $ticketsCount,'user_type'=>'user']);
    }

    //render translator tickets page
    public function translator_tickets_page($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $state = $req->getQueryParam("state") === null ? ['waiting','answered'] : \explode(",", $req->getQueryParam("state"));
        //get tickets sent by customers
        $tickets=Ticket::get_translator_tickets($page,10,['state'=>$state]);
        $ticketsCount=Ticket::get_tickets_count(2,['state'=>$state]);
        return $this->view->render($res,"/admin/admin/tickets.twig",["tickets" => $tickets,'current_page'=>$page, 'tickets_count' => $ticketsCount,'state'=>$state,'page_title'=>'پیام های مترجمان','page_url'=>'/admin/tickets/translator','user_type'=>'translator']);
    }

    //render translator tickets as json
    public function translator_tickets_json($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $state = $req->getQueryParam("state") === null ? ['waiting','answered'] : \explode(",", $req->getQueryParam("state"));
        //get tickets sent by customers
        $tickets=Ticket::get_translator_tickets($page,10,['state'=>$state]);
        $ticketsCount=Ticket::get_tickets_count(2,['state'=>$state]);
        return $res->withJson(["tickets" => $tickets,'current_page'=>$page, 'tickets_count' => $ticketsCount,'user_type'=>'translator']);
    }

    //render view ticket page
    public function get_ticket_details_page($req,$res,$args)
    {
        $userType=$req->getParam("type");
        $ticketDetails = Ticket::admin_get_details_by_ticket_number($args['ticket_number'],$userType);
        if(!$ticketDetails){
            return var_dump("تیکت موجود نمی باشد");
        }
        $ticketMessages=Ticket::get_ticket_messages_by_ticket_number($args['ticket_number']);
        $lastTicketId=$ticketMessages[0]['ticket_id'];        
        return $this->view->render($res, "admin/admin/view-ticket.twig", ['ticket_details' => $ticketDetails,'ticket_messages'=>$ticketMessages,"last_ticket_id"=>$lastTicketId]);
    }
    //render view ticket as json
    public function get_ticket_details_json($req,$res,$args)
    {
        $userType=$req->getParam("type");
        $ticketDetails = Ticket::admin_get_details_by_ticket_number($args['ticket_number'],$userType);
        $ticketMessages=Ticket::get_ticket_messages_by_ticket_number($args['ticket_number']);
        return $res->withJson(['status'=>true,'date'=>Ticket::getCurrentDatePersian(),'ticket_details' => $ticketDetails,'tickets'=>$ticketMessages]);
    }
    //get new translators employment requests and render the page
    public function get_new_unemployed_translators_page($req,$res,$args)
    {
        $newPage = $req->getQueryParam("new_page") ? $req->getQueryParam("new_page") : 1;
        $deniedPage=$req->getQueryParam("deny_page") ? $req->getQueryParam("deny_page") : 1;
        // $acceptedPage=$req->getQueryParam("accept_page") ? $req->getQueryParam("accept_page") : 1;
        $data=[];
        $data['translator_employment_requests']=Admin::get_employment_requests($newPage,10);
        $data['translator_employment_requests_count']=Admin::get_employment_requests_count();
        $data['new_current_page']=$newPage;

        $data['denied_requests']=Admin::get_denied_requests($deniedPage,10);
        $data['denied_requests_count']=Admin::get_denied_requests_count();
        $data['denied_current_page']=$deniedPage;
        
        // $data['accepted_requests']=Admin::get_accepted_requests($page,10);
        // $data['accepted_requests_count']=Admin::get_accepted_requests_count();
        // $data['accepted_current_page']=$deniedPage;

        return $this->view->render($res,"/admin/admin/new-translators.twig",$data);
    }
    //get new translator requests and render the page
    public function get_translators_order_requests_page($req,$res,$args)
    {
        $newPage = $req->getQueryParam("new_page") ? $req->getQueryParam("new_page") : 1;
        $deniedPage=$req->getQueryParam("deny_page") ? $req->getQueryParam("deny_page") : 1;
        // $acceptedPage=$req->getQueryParam("accept_page") ? $req->getQueryParam("accept_page") : 1;
        $data=[];
        $data['translator_order_requests']=Admin::get_translator_order_requests($newPage,10);
        $data['translator_order_requests_count']=Admin::get_translator_order_requests_count();
        $data['denied_requests']=Admin::get_translator_denied_order_requests($deniedPage,10);
        $data['denied_requests_count']=Admin::get_translator_denied_order_requests_count();
        $data['new_current_page']=$newPage;
        $data['denied_current_page']=$deniedPage;
        return $this->view->render($res,"/admin/admin/translator-order-requests.twig",$data);
    }

    //get hired translators and render a page
    public function get_hired_translators_page($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $hiredTranslators=Translator::get_hired_translators($page,10);
        $hiredTranslatorsCount=Translator::get_hired_translators_count();
        return $this->view->render($res,"/admin/admin/hired-translators.twig",['hired_translators'=>$hiredTranslators,"count"=>$hiredTranslatorsCount,'current_page'=>$page]);
    }
    public function get_translator_info_page($req,$res,$args)
    {
        $translatorInfo=Translator::by_username($args['username']);
        if(!$translatorInfo) {
            echo("مترجمی با این مشخصات یافت نشد !");
            return;
        }
        $completedPage = $req->getQueryParam("c_page") ? $req->getQueryParam("c_page") : 1;
        $completedOrders=Translator::get_completed_orders_by_user_id($translatorInfo['translator_id'],$completedPage,10);
        $completedOrdersCount=Translator::get_completed_orders_count_by_user_id($translatorInfo['translator_id']);

        $pendingPage = $req->getQueryParam("p_page") ? $req->getQueryParam("p_page") : 1;
        $pendingOrders=Translator::get_pending_orders_by_user_id($translatorInfo['translator_id'],$pendingPage,10);
        $pendingOrdersCount=Translator::get_pending_orders_count_by_user_id($translatorInfo['translator_id']);

        return $this->view->render($res,"/admin/admin/translator-info.twig",['info'=>$translatorInfo,'completed_orders'=>$completedOrders,'pending_orders'=>$pendingOrders,'pending_count'=>$pendingOrdersCount,'completed_count'=>$completedOrdersCount,'pending_current_page'=>$pendingPage,'completed_current_page'=>$completedPage]);
    }

    //get order details and render the page
    public function get_order_details_page($req,$res,$args)
    {
        $orderData=Order::by_number($args['order_number'],true,true);
        if($orderData){
            $orderData['found']=count($orderData) ? true:false;
            $orderData['order_files']=explode(",",$orderData['order_files']);
        }
        return $this->view->render($res,"/admin/admin/view-order.twig",$orderData);
    }

    //get orderer data (customer) and render as json
    public function get_orderer_data_json($req,$res,$args)
    {
        $ordererData=User::by_id($args['orderer_id']);
        if($ordererData){
            return $res->withJson(['status'=>true,'info'=>$ordererData]);
        }
        return $res->withJson(['status'=>false,'message'=>'user not found !']);
    }
    //get translator data (customer) and render as json
    public function get_translator_data_json($req,$res,$args)
    {
        $translatorData=Translator::by_id($args['translator_id']);
        if($translatorData){
            return $res->withJson(['status'=>true,'info'=>$translatorData]);
        }
        return $res->withJson(['status'=>false,'message'=>'translator not found !']);
    }
    //get pending orders(orders that is accepted but it's not done)
    public function get_pending_orders_page($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $pendingOrders=Admin::get_all_pending_orders($page,10);
        $pendingOrdersCount=Admin::get_all_pending_orders_count();
        return $this->view->render($res,"/admin/admin/pending-orders.twig",['pending_orders'=>$pendingOrders,'count'=>$pendingOrdersCount,'current_page'=>$page]);
    }
    //get completed orders(orders that is accepted and it's done)
    public function get_completed_orders_page($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $completedOrders=Admin::get_all_completed_orders($page,10);
        $completedOrdersCount=Admin::get_all_completed_orders_count();
        return $this->view->render($res,"/admin/admin/completed-orders.twig",['completed_orders'=>$completedOrders,'count'=>$completedOrdersCount,'current_page'=>$page]);
    }
    //get payments requests of translator and render a page
    public function get_translators_payment_requests_page($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $paid=$req->getQueryParam("paid") ? explode(",",$req->getQueryParam("paid")) : ['0','1'];
        $state=$req->getQueryParam("state") ? explode(",",$req->getQueryParam("state")) : ['-1','0','1'];
        $paymentRequests=Admin::get_translator_payment_requests($page,10,['state'=>$state,'is_paid'=>$paid]);
        $paymentRequestsCount=Admin::get_translator_payment_requests_count(['state'=>$state,'is_paid'=>$paid]);
        return $this->view->render($res,"/admin/admin/payment-requests.twig",['payment_requests'=>$paymentRequests,'count'=>$paymentRequestsCount,'current_page'=>$page,'state'=>$state,'paid'=>$paid]);
    }
    //get payments requests and render as json
    public function get_translators_payment_requests_json($req,$res,$args)
    {
        $page = $req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $paid=$req->getQueryParam("paid") ? explode(",",$req->getQueryParam("paid")) : ['0','1'];
        $state=$req->getQueryParam("state") ? explode(",",$req->getQueryParam("state")) : ['-1','0','1'];
        $paymentRequests=Admin::get_translator_payment_requests($page,10,['state'=>$state,'is_paid'=>$paid]);
        $paymentRequestsCount=Admin::get_translator_payment_requests_count(['state'=>$state,'is_paid'=>$paid]);
        return $res->withJson(['payment_requests'=>$paymentRequests,'count'=>$paymentRequestsCount,'current_page'=>$page]);
    }
    //accept translator's payment request
    public function accept_translator_payment_request($req,$res,$args)
    {
        $postFields=$req->getParsedBody();
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        if($postFields['token']===$hash){
            $result=Admin::accept_translator_payment_request($postFields['request_id']);
            if($result){
                return $res->withJson(['status'=>true]);
            }
            return $res->withJson(['status'=>false,'message'=>'error in saving data!']);
        }else{
            return $res->withJson(['status'=>false,'message'=>'invalid token!']);
        }        
    }
    //deny translator's payment request
    public function deny_translator_payment_request($req,$res,$args)
    {
        $postFields=$req->getParsedBody();
        $hash = md5(md5(Config::VERIFY_EMAIL_KEY));
        if($postFields['token']===$hash){
            $result=Admin::deny_translator_payment_request($postFields['request_id']);
            if($result){
                return $res->withJson(['status'=>true]);
            }
            return $res->withJson(['status'=>false,'message'=>'error in saving data!']);
        }else{
            return $res->withJson(['status'=>false,'message'=>'invalid token!']);
        }        
    }
    //set payment info by admin
    public function post_set_payment_info($req,$res,$args)
    {
        $body=$req->getParsedBody();
        $result=Admin::set_payment_info($body);
        if ($result){
            return $res->withJson(['status'=>true]);
        }
        return $res->withJson(['status'=>false,'message'=>'error in saving data']);
    }
    //get payment info as json
    public function get_payment_info_json($req,$res,$args)
    {
        $logId=$req->getParam("log_id");
        $paymentInfo=\Core\Model::select("payment_logs","*",['id'=>$logId],true);
        if ($paymentInfo) return $res->withJson(['status'=>true,'info'=>$paymentInfo]);
        return $res->withJson(['status'=>false,'message'=>'payment info not found']);
    }
    //get all translators account info and render the page
    public function get_translators_account_info_page($req,$res,$args)
    {
        $page=$req->getParam("page") ? $req->getParam("page"):1;
        $translatorsAccounts=Admin::get_all_translators_account_info($page,10);
        $translatorsAccountsCount=Admin::get_all_translators_account_info_count();
        return $this->view->render($res,"admin/admin/translators-account-info.twig",['infos'=>$translatorsAccounts,'current_page'=>$page,'count'=>$translatorsAccountsCount]);
    }
    //get website revenue and currency info and render a page
    public function get_website_revenue_page($req,$res,$args)
    {
        $done=$req->getParam("done") != null ? explode(",",$req->getParam("done")):['0','1'];
        $fromDate=$req->getParam("from_date") ? $req->getParam("from_date"):0;
        $toDate=$req->getParam("to_date") ? $req->getParam("to_date"): jstrftime("%Y/%m/%d %H:%M","","","","en");
        $paymentState=$req->getParam("payment_state") ? explode(",",$req->getParam("payment_state")) : ['-1','0','1'];
        $paid=$req->getParam("paid") ? explode(",",$req->getParam("paid")) : ['0','1'];
        $data=[];
        $data['total_revenue']=Admin::get_total_revenue_with_filtering(['done'=>$done,'from_date'=>$fromDate,'to_date'=>$toDate]);
        $data['admin_revenue']=(intval($data['total_revenue'])*15)/100;
        $data['masoud_revenue']=(intval($data['total_revenue'])*15)/100;
        $data['translators_revenue']=(intval($data['total_revenue'])*70)/100;
        $data['pending_orders']=Admin::get_orders_count_by_date(['from_date'=>$fromDate,'to_date'=>$toDate,'done'=>["0"]]);
        $data['completed_orders']=Admin::get_orders_count_by_date(['from_date'=>$fromDate,'to_date'=>$toDate,'done'=>["1"]]);
        $data['filtered_orders']=Admin::get_all_orders_by_filters(1,10,['done'=>$done,'from_date'=>$fromDate,'to_date'=>$toDate]);
        $data['filtered_orders_count']=Admin::get_orders_count_by_date(['from_date'=>$fromDate,'to_date'=>$toDate,'done'=>$done]);
        $data['payment_requests']=120;
        $data['payment_requests_sum']=Admin::get_translator_payment_requests_sum(['state'=>$paymentState,'is_paid'=>$paid],['from_date'=>$fromDate,'to_date'=>$toDate]);
        $data['filtered_payment_requests']=Admin::get_translator_payment_requests(1,10,['state'=>$paymentState,'is_paid'=>$paid],['from_date'=>$fromDate,'to_date'=>$toDate]);
        $data['done']=$done;
        $data['payment_state']=$paymentState;
        $data['paid']=$paid;
        return $this->view->render($res,"admin/admin/site-revenue.twig",$data);
    }
    //filter financial data based on given value and return data as json
    public function post_filter_site_revenue($req,$res,$args)
    {
        $data=[];
        $page=$req->getParam("page") ? $req->getParam("page"):1;
        $requestsPage=$req->getParam("request_page") ? $req->getParam("request_page"):1;
        $postFields=$req->getParsedBody();
        $postFields['from_date']=(!isset($postFields['from_date']) || $postFields['from_date']=== "") ? 0:$this->persian_num_to_english($postFields['from_date']);
        $postFields['to_date']=(!isset($postFields['to_date']) || $postFields['to_date'] === "") ? jstrftime("%Y/%m/%d %H:%M","","","","en"):$this->persian_num_to_english($postFields['to_date']) ;
        $postFields['done']= !isset($postFields['done']) ? ['0','1']:$postFields['done'];
        $postFields['payment_state']=!isset($postFields['payment_state']) ? ['-1','0','1']:$postFields['payment_state'];
        $postFields['paid']=!isset($postFields['paid']) ? ['0','1']:$postFields['paid'];
        $totalRevenue=Admin::get_total_revenue_with_filtering($postFields);
        $data['total_revenue']=number_format($totalRevenue);
        $data['admin_revenue']=number_format((intval($totalRevenue)*15)/100);
        $data['masoud_revenue']=number_format((intval($totalRevenue)*15)/100);
        $data['translators_revenue']=number_format((intval($totalRevenue)*70)/100);
        $data['pending_orders']=Admin::get_orders_count_by_date($postFields);
        $data['completed_orders']=Admin::get_orders_count_by_date($postFields);
        $data['filtered_orders']=Admin::get_all_orders_by_filters($page,10,$postFields);
        $data['filtered_orders_count']=Admin::get_orders_count_by_date($postFields);
        $data['payment_requests']=120;
        $data['payment_requests_sum']=Admin::get_translator_payment_requests_sum(['state'=>$postFields['payment_state'],'is_paid'=>$postFields['paid']],['from_date'=>$postFields['from_date'],'to_date'=>$postFields['to_date']]);
        $data['filtered_payment_requests']=Admin::get_translator_payment_requests($requestsPage,10,['state'=>$postFields['payment_state'],'is_paid'=>$postFields['paid']],['from_date'=>$postFields['from_date'],'to_date'=>$postFields['to_date']]);
        $data['current_page']=$page;
        $data['requests_current_page']=$requestsPage;
        return $res->withJson(['status'=>true,'info'=>$data]);
    }
    //get all notifications and render the page
    public function get_notifications_page($req,$res,$args)
    {
        $data=[];
        $publicPage=$req->getParam("public_page") ? $req->getParam("public_page"):1;
        $privatePage=$req->getParam("private_page") ? $req->getParam("private_page"):1;
        $data['public_notifications']=Admin::get_all_public_notifications($publicPage,10);
        $data['public_notifications_count']=Admin::get_all_public_notifications_count();
        $data['public_current_page']=$publicPage;
        $data['private_notifications']=Admin::get_all_private_notifications($privatePage,10);
        $data['private_notifications_count']=Admin::get_all_private_notifications_count();
        $data['private_current_page']=$privatePage;
        return $this->view->render($res,"admin/admin/notifications.twig",$data);
    }
    //render a page to create new notification
    public function get_new_notification_page($req,$res,$args)
    {

    }

    public function get_public_notification_info_json($req,$res,$args)
    {
        $notifId=$req->getParam("notif_id");
        $notificationData=Notification::get_data_by_id($notifId);
        if ($notificationData){
            return $res->withJson(['status'=>true,'info'=>$notificationData]);
        }
        return $res->withJson(['status'=>false,'message'=>'notification not found or an error occurred']);
    }
    public function get_private_notification_info_json($req,$res,$args)
    {
        $notifId=$req->getParam("notif_id");
        $notificationData=Admin::get_private_notification_data_by_id($notifId);
        if ($notificationData){
            return $res->withJson(['status'=>true,'info'=>$notificationData]);
        }
        return $res->withJson(['status'=>false,'message'=>'notification not found or an error occurred']);
    }

    public function delete_notification($req,$res,$args)
    {
        $notifId=$req->getParam("notif_id");
        $result=Notification::delete_by_id($notifId);
        if ($result){
            return $res->withJson(['status'=>true]);
        }
        return $res->withJson(['status'=>true,'message'=>'an error occured i deleting notification']);
    }

    //convert english numbers to english one
    protected function persian_num_to_english($str)
    {
        $persian_nums = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
        $english_nums = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        return str_replace($persian_nums, $english_nums, $str);
    }
}
