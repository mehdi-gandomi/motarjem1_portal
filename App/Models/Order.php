<?php
namespace App\Models;

use Core\Model;

class Order extends Model
{

    public static function new ($postInfo) {
        try {
            $date = jstrftime('%Y/%m/%e ,%A, %r', time() + 60);
            $publish_date = date("Y-m-d");
            $priceInfo = self::calculatePrice($postInfo['language'], $postInfo['type'], $postInfo['translation_quality'], $postInfo['delivery_type'], $postInfo['words']);
            $orderInfo = array(
                'translation_kind' => $postInfo['translation_quality'],
                'field_of_study' => $postInfo['field_of_study'],
                'word_number' => $postInfo['words'],
                'translation_quality' => $priceInfo['quality'],
                'translation_lang' => $postInfo['language'],
                'delivery_type' => $postInfo['delivery_type'],
                'page_number' => $priceInfo['pageNumber'],
                'during' => $priceInfo['duration'],
                'order_price' => $priceInfo['price'],
                'tarikh' => $date,
                'publish_date' => $publish_date,
                'done_file' => $postInfo['files'],
            );
            static::insert("yg_order", $orderInfo);
            return array(
                'priceInfo'=>$priceInfo,
                'orderId'=>static::get_last_inserted_id()
            );
        } catch (\Exeption $e) {
            return false;
        }
    }
    public static function new_buyer($postInfo,$orderId)
    {
        try {
            $buyerInfo = array(
                'fk_order_id' => $orderId,
                'u_name' => $postInfo['fullname'],
                'tell' => $postInfo['phone_number'],
                'email' => $postInfo['email'],
                'message' => $postInfo['description'],
            );
            static::insert("yg_payer_info", $buyerInfo);
            return static::get_last_inserted_id();
        } catch (\Exeption $e) {
            return false;
        }
    }
    public static function buyer_by_order_id($id)
    {
        try {

            return static::select("yg_payer_info", "*",['fk_order_id'=>$id], true);

        } catch (\Exeption $e) {
            return false;
        }
    }
    public static function by_id($id)
    {
        try {
            return static::select("yg_order", "*",['id'=>$id], true);

        } catch (\Exeption $e) {
            return false;
        }
    }


    public static function update_by_id($data,$orderId)
    {
        try{
            static::update("yg_order",$data,"id='$orderId'");
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

        if ($translate_language == "en_to_fa") {
            switch ($quality) {
                case "gold":
                    if ($type == "common") {
                        $basePrice = 32;

                    } else if ($type == "specialist") {
                        $basePrice = 44;
                    }
                    break;
                case "silver":
                    if ($type == "common") {
                        $basePrice = 20;

                    } else if ($type == "specialist") {
                        $basePrice = 40;
                    }
                    break;
            }

        } else if ($translate_language == "fa_to_en") {

            switch ($quality) {
                case "gold":
                    if ($type == "common") {
                        $basePrice = 40;

                    } else if ($type == "specialist") {
                        $basePrice = 60;
                    }
                    break;
                case "silver":
                    if ($type == "common") {
                        $basePrice = 32;

                    } else if ($type == "specialist") {
                        $basePrice = 52;
                    }
                    break;
            }

        }
        $finalPrice = $wordsNumber * $basePrice;

        if ($delivery_type == "normal") {$coefficient = 1;
            $baseDuration = 5;} else if ($delivery_type == "half_an_instant") {$coefficient = 1.2;
            $baseDuration = 6;} else if ($delivery_type == "instantaneous") {$coefficient = 1.5;
            $baseDuration = 8;}
        $finalPrice = $finalPrice * $coefficient;
        $durend = $page_number / $baseDuration;
        $durend = \ceil($durend);
        if ($quality == "gold") {
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

}
