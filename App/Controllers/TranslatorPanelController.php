<?php
namespace App\Controllers;

use App\Models\Translator;
use Core\Config;
use Core\Controller;
use App\Models\Order;

class TranslatorPanelController extends Controller
{
    public function get_dashboard($req,$res,$args)
    {
        $data=[];
        if($_SESSION['is_employed']){
            $data['new_orders']=Order::get_orders_without_requested_by_user_id($_SESSION['user_id'],1,3);
            $data['lastMessages']= Translator::get_messages_by_id($_SESSION['user_id'], 1, 3);
            $data['unread_messages_count']=Translator::get_unread_messages_count_by_user_id($_SESSION['user_id']);
            $data['translator_orders_count']=Order::get_orders_count_by_user_id($_SESSION['user_id']);
            $data['translator_revenue']=\Core\Model::select("translators","revenue",[],true)['revenue'];
        }else{
            $data['study_fields']=Order::get_study_fields();
        }
        return $this->view->render($res,"admin/translator/dashboard.twig",$data);
    }
    public function get_test_json($req,$res,$args)
    {
        $language=$req->getParam("language");
        $studyField=$req->getParam("study_field");
        $test=Translator::get_test_by_filtering($language,$studyField);
        $test['status']=$test ? true:false;
        return $res->withJson($test);
    }
    public function save_test_data($req,$res,$args)
    {
        $body=$req->getParsedBody();
        $body['translator_id']=$_SESSION['user_id'];
        $result=Translator::save_test_data($body);
        if($result){
            return $res->withJson(array(
                'status'=>true
            )); 
        }
        return $res->withJson(array(
            'status'=>false,
            'error'=>'مشکلی در ذخیره پاسخ شما رخ داد !'
        )); 
    }
    public function get_order_info($req,$res,$args)
    {
        $orderId=$args['order_id'];
        $orderData=Order::by_id($orderId,false,true);
        return $res->withJson($orderData);
    }
    //requesting to do the translation by translator
    public function request_order($req,$res,$args)
    {
        $body=$req->getParsedBody();
        if(Order::request_order($body['translator_id'],$body['order_id'])){
            return $res->withJson(['status'=>true]);
        }
        return $res->withJson(['status'=>false]);
    }

    //declining the translation by translator
    public function decline_order($req,$res,$args)
    {
        $body=$req->getParsedBody();
        if(Order::deny_order($body['translator_id'],$body['order_id'])){
            return $res->withJson(['status'=>true]);
        }
        return $res->withJson(['status'=>false]);
    }
    //get new unaccepted orders as json
    public function get_new_orders_json($req,$res,$args)
    {
        $page=$req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $offset=$req->getQueryParam("offset") ? $req->getQueryParam("offset") : 10;
        $choice=$req->getQueryParam("choice") ? $req->getQueryParam("choice") : "new";
        $orders_count=Order::new_orders_count($_SESSION['user_id'],$choice);
        if($choice=="new"){
            $orders=Order::get_orders_without_requested_by_user_id($_SESSION['user_id'],$page,$offset);
        }else if ($choice=="requested") {
            $orders=Order::get_requested_orders_data_by_user_id($_SESSION['user_id'],$page,$offset);
        }else{
            $orders=Order::get_denied_orders_data_by_user_id($_SESSION['user_id'],$page,$offset);
        }
        return $res->withJson(['orders'=>$orders,'status'=>true,'choice'=>$choice,'current_page'=>$page,'orders_count'=>$orders_count,'translator_id'=>$_SESSION['user_id']]);
    }
    //get new orders and render the page
    public function get_new_orders($req,$res,$args)
    {
        $page=$req->getQueryParam("page") ? $req->getQueryParam("page") : 1;
        $offset=$req->getQueryParam("offset") ? $req->getQueryParam("offset") : 10;
        $choice=$req->getQueryParam("choice") ? $req->getQueryParam("choice") : "new";
        if($choice=="new"){
            $data['new_orders']=Order::get_orders_without_requested_by_user_id($_SESSION['user_id'],$page,$offset);
        }else if ($choice=="requested") {
            $data['new_orders']=Order::get_requested_orders_data_by_user_id($_SESSION['user_id'],$page,$offset);
        }else{
            $data['new_orders']=Order::get_denied_orders_data_by_user_id($_SESSION['user_id'],$page,$offset);
        }
        $data['new_orders_count']=Order::new_orders_count($_SESSION['user_id'],$choice);
        $data['current_page']=$page;
        $data['choice']=$choice;
        return $this->view->render($res,"admin/translator/new-orders.twig",$data);
    }
    //get orders that translator have to do or did and render the page with that
    public function get_translator_orders($req,$res,$args)
    {
        $data=[];
        $page=$req->getParam("page") ? $req->getParam("page") : 1;
        $isDone=$req->getParam("done") !=null || strlen($req->getParam("done"))>0 ? \explode(",",$req->getParam("done")) : [1,0];
        $data['pending']=true;
        $data['completed']=true;
        if(\count($isDone) < 2){
            switch($isDone[0]){
                case "0":
                    $data['completed']=false;
                break;
                case "1":
                    $data['pending']=false;
                break;
            }
        }
        $data['orders']=Translator::get_translator_orders_by_user_id($_SESSION['user_id'],$page,10,['is_done'=>$isDone]);
        $data['orders_count']=Translator::get_translator_orders_count_by_user_id($_SESSION['user_id'],['is_done'=>$isDone]);
        $data['current_page']=$page;
        return $this->view->render($res,"admin/translator/translator_orders.twig",$data);
    }
    //get orders that translator have to do or did and render the page with that
    public function get_translator_orders_json($req,$res,$args)
    {
        $data=[];
        $page=$req->getParam("page") ? $req->getParam("page") : 1;
        $isDone=$req->getParam("done") !=null || strlen($req->getParam("done"))>0 ? \explode(",",$req->getParam("done")) : [1,0];
        $data['orders']=Translator::get_translator_orders_by_user_id($_SESSION['user_id'],$page,10,['is_done'=>$isDone]);
        $data['orders_count']=Translator::get_translator_orders_count_by_user_id($_SESSION['user_id'],['is_done'=>$isDone]);
        $data['current_page']=$page;
        return $res->withJson($data);
    }
}