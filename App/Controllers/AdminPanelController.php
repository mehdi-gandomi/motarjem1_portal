<?php
namespace App\Controllers;
use Core\Controller;
use App\Models\Translator;
class AdminPanelController extends Controller
{
    public function dashboard($req,$res,$args)
    {
        $data=[];
        $data['translator_employment_requests']=Translator::get_employment_requests();
        return $this->view->render($res,"admin/admin/dashboard.twig",$data);
    }
    public function all_translator_info_json($req,$res,$args)
    {
        $translatorId=$req->getParam("translator_id");
        $data=['status'=>true];
        //get translator data and test data in one function and send it as json
        $translatorAndTestData=Translator::get_translator_test_info_by_user_id($translatorId);
        $data['status']=$translatorAndTestData && count($translatorAndTestData)<1 ? false:true;
        $data['info']=$translatorAndTestData;
        return $res->withJson($data);
    }
}
