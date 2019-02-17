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
            $data['new_orders']=Order::new_unaccepted_orders(3);
            $data['lastMessages']= Translator::get_messages_by_id($_SESSION['user_id'], 1, 3);
            $data['unread_messages_count']=Translator::get_unread_messages_count_by_user_id($_SESSION['user_id']);
            $data['translator_orders_count']=Translator::get_orders_count_by_user_id($_SESSION['user_id']);
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
}