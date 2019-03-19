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
        if(!$ticketDetails){
            return $res->withJson(['status'=>false,'message'=>'پیام درخواستی یافت نشد !']);
        }
        return $res->withJson(['status'=>true,'info'=>$ticketDetails,'messages'=>$ticketMessages]);
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
}
