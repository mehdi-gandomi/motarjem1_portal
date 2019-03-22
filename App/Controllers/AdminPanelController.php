<?php
namespace App\Controllers;
use Core\Controller;
use Core\Config;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Translator;
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
        $data['total_revenue']=number_format(Admin::get_total_revenue());
        //get revenue of this month
        $data['month_revenue']=number_format(Admin::get_monthly_revenue());
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
}