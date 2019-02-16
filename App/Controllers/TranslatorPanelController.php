<?php
namespace App\Controllers;

use App\Models\Translator;
use Core\Config;
use Core\Controller;
use Gregwar\Captcha\CaptchaBuilder;
use Slim\Http\UploadedFile;

class TranslatorPanelController extends Controller
{
    public function get_dashboard($req,$res,$args)
    {
        $study_fields=Translator::get_study_fields();
        
        return $this->view->render($res,"admin/translator/dashboard.twig",['is_employed'=>false,'study_fields'=>$study_fields]);
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