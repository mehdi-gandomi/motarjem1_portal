<?php
namespace App\Models;

use Core\Model;
use PDO;
class Order extends Model
{

    public static function new ($postInfo) {
        try {
            unset($postInfo['csrf_value']);
            unset($postInfo['csrf_name']);
            unset($postInfo['email']);
            unset($postInfo['phone_number']);
            unset($postInfo['fullname']);
            unset($postInfo['file']);
            $postInfo['order_date_persian']=static::getCurrentDatePersian();
            $priceInfo= self::calculatePrice($postInfo['translation_lang'], $postInfo['translation_kind'], $postInfo['translation_quality'], $postInfo['delivery_type'], $postInfo['word_numbers']);
            $postInfo['order_price']=$priceInfo['price'];
            $postInfo['delivery_days']=$priceInfo['duration'];
            static::insert("orders", $postInfo);
            return array(
                'priceInfo'=>$priceInfo,
                'orderId'=>static::get_last_inserted_id()
            );
        } catch (\Exeption $e) {
            return false;
        }
    }

    public static function by_id($id,$with_translator_data=false,$with_orderer_data=false)
    {
        try {
            $db=static::getDB();
            if(!$with_translator_data && !$with_orderer_data){
                $sql="SELECT * FROM `orders` INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE orders.order_id = :order_id";
            }else if($with_translator_data && !$with_orderer_data){
                $sql = "SELECT orders.order_id,orders.word_numbers,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id FROM orders INNER JOIN translators ON orders.translator_id=translators.translator_id INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE orders.order_id= :order_id";
            }else if($with_orderer_data && !$with_translator_data){
                $sql = "SELECT orders.order_id,orders.word_numbers,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,users.fname AS orderer_fname,users.lname AS orderer_lname,users.user_id,users.email,users.phone FROM orders INNER JOIN users ON orders.orderer_id = users.user_id INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE orders.order_id= :order_id";
            }else if($with_translator_data && $with_orderer_data){
                $sql = "SELECT orders.order_id,orders.word_numbers,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,users.fname AS orderer_fname,users.lname AS orderer_lname,users.user_id,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id FROM orders INNER JOIN translators ON orders.translator_id=translators.translator_id INNER JOIN users ON orders.orderer_id = users.user_id INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE orders.order_id= :order_id";
            }
            $stmt=$db->prepare($sql);
            $stmt->bindParam(":order_id",$id);
            return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

        } catch (\Exeption $e) {
            return false;
        }
    }
    
    public function new_order_log($data)
    {
        try{
            static::insert("order_logs",$data);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    public static function update_order_log($data,$orderId)
    {
        try{
            static::update("order_logs",$data,"order_id='$orderId'");
            return true;
        }catch(\Exception $e){
            return false;
        }
    }


    protected static function calculatePrice($translate_language, $type, $quality, $delivery_type, $wordsNumber)
    {
        $basePrice = 0;
        $finalPrice = 0;
        $coefficient = 1;
        $baseDuration = 1;
        $page_number = \round($wordsNumber / 250);
        if ($page_number < 1) {
            $page_number = 1;
        }

        if ($translate_language == "1") {
            switch ($quality) {
                case "10":
                    if ($type == "1") {
                        $basePrice = 32;

                    } else if ($type == "2") {
                        $basePrice = 44;
                    }
                    break;
                case "5":
                    if ($type == "1") {
                        $basePrice = 20;

                    } else if ($type == "2") {
                        $basePrice = 40;
                    }
                    break;
            }

        } else if ($translate_language == "2") {

            switch ($quality) {
                case "10":
                    if ($type == "1") {
                        $basePrice = 40;

                    } else if ($type == "2") {
                        $basePrice = 60;
                    }
                    break;
                case "5":
                    if ($type == "1") {
                        $basePrice = 32;

                    } else if ($type == "2") {
                        $basePrice = 52;
                    }
                    break;
            }

        }
        $finalPrice = $wordsNumber * $basePrice;

        if ($delivery_type == "1") {$coefficient = 1;
            $baseDuration = 5;} else if ($delivery_type == "2") {$coefficient = 1.2;
            $baseDuration = 6;} else if ($delivery_type == "3") {$coefficient = 1.5;
            $baseDuration = 8;}
        $finalPrice = $finalPrice * $coefficient;
        $durend = $page_number / $baseDuration;
        $durend = \ceil($durend);
        if ($quality == "10") {
            $translationQuality = 10;
        } else {
            $translationQuality = 0;
        }

        return array(
            "price" => $finalPrice,
            "quality" => $translationQuality,
            "pageNumber" => $page_number,
            'duration' => $durend,
        );

    }
    protected static function getCurrentDatePersian()
    {
        $now = new \DateTime("NOW");
        $year = $now->format("Y");
        $month = $now->format("m");
        $day = $now->format("d");
        $time = $now->format("H:i");
        $persianDate = gregorian_to_jalali($year, $month, $day);
        return  $persianDate[0] . "/" . $persianDate[1] . "/" . $persianDate[2] . " " . $time;
    }
}
