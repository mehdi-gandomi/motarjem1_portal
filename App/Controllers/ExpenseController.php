<?php
namespace App\Controllers;
use App\Controller;

class ExpenseController extends Controller{
  private $combos=array(
    'lang_type'=>array(
      'en_to_fa'=>"انگلیسی - فارسی",
      'fa_to_en'=>"فارسی - انگلیسی"
    ),
    'translate_type'=>array(
      'common'=>"عمومی",
      'specialist'=>'تخصصی'
    ),
    'delivery_types'=>array(
      'normal'=>'عادی',
      'half_an_instant'=>'نیمه فوری',
      'instantaneous'=>'فوری'
    )
  );
  

  public function get($req,$res,$args){
    
    $data=array_merge($this->combos,array("latestPosts"=>$_SESSION["latestPosts"]));
    $this->view->render($res,"website/calculate-expense.twig",$data);
  }
  public function post($req,$res,$args){
    $post_vars=$req->getParsedBody();
    $priceResult=$this->calculatePrice($post_vars['language'],$post_vars['type'],$post_vars['delivery_type'],intval($post_vars['words']));
    $data=array_merge($this->combos,$post_vars,$priceResult);
    $data['show_result']=true;
    $data=array_merge($data,array("latestPosts"=>$_SESSION["latestPosts"]));
    $this->view->render($res,"website/calculate-expense.twig",$data);
  }

  protected function calculatePrice($translate_language, $type, $delivery_type, $wordsNumber) {
    $goldBasePrice = 0;
    $silverBasePrice = 0;
    $goldFinalPrice = 0;
    $silverFinalPrice = 0;
    $coefficient = 1;
    $baseDuration = 1;
    $page_number = \round($wordsNumber / 250);
    if ($page_number < 1)
        $page_number = 1;


    if ($translate_language == "en_to_fa") {
        if ($type == "common") {
            $goldBasePrice = 32;
            $silverBasePrice = 20;

        } else if ($type == "specialist") {
            $goldBasePrice = 44;
            $silverBasePrice = 40;
        }

    } else if ($translate_language == "fa_to_en") {


        if (type == "common") {
            $goldBasePrice = 40;
            $silverBasePrice = 32;

        } else if (type == "specialist") {
            $goldBasePrice = 60;
            $silverBasePrice = 52;
        }

    }
    $goldFinalPrice = $wordsNumber * $goldBasePrice;
    $silverFinalPrice = $wordsNumber * $silverBasePrice;

    if ($delivery_type == "normal") { $coefficient = 1; $baseDuration = 5; }

    else if ($delivery_type == "half_an_instant") { $coefficient = 1.2; $baseDuration = 6; }

    else if ($delivery_type == "instantaneous") { $coefficient = 1.5;$baseDuration = 8; }
    $goldFinalPrice = $goldFinalPrice * $coefficient;
    $silverFinalPrice = $silverFinalPrice * $coefficient;
    $durend = $page_number / $baseDuration;
    $durend=\ceil($durend);

    return array(
        "goldPrice"=> $goldFinalPrice.' تومان در مدت '.$durend." روز",
        "silverPrice"=> $silverFinalPrice.' تومان در مدت '.$durend." روز",
        "pageNumber"=> "تعداد صفحات : ".$page_number
    );

}
}