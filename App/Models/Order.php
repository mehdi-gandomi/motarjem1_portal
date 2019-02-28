<?php
namespace App\Models;

use Core\Model;
use PDO;

class Order extends Model
{

    function new ($postInfo) {
        try {
            unset($postInfo['csrf_value']);
            unset($postInfo['csrf_name']);
            unset($postInfo['email']);
            unset($postInfo['phone_number']);
            unset($postInfo['fullname']);
            unset($postInfo['file']);
            $orderNumber=bin2hex(random_bytes(4));
            $postInfo['order_number']=$orderNumber;
            $postInfo['order_date_persian'] = static::getCurrentDatePersian();
            $priceInfo = self::calculatePrice($postInfo['translation_lang'], $postInfo['translation_kind'], $postInfo['translation_quality'], $postInfo['delivery_type'], $postInfo['word_numbers']);
            $postInfo['order_price'] = $priceInfo['price'];
            $postInfo['delivery_days'] = $priceInfo['duration'];
            static::insert("orders", $postInfo);
            return array(
                'priceInfo' => $priceInfo,
                'orderNumber' =>$orderNumber,
            );
        } catch (\Exeption $e) {
            return false;
        }
    }

    public static function by_id($id, $with_translator_data = false, $with_orderer_data = false, $ordererId = false)
    {
        try {
            $db = static::getDB();
            if (!$with_translator_data && !$with_orderer_data) {
                $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.order_files,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,orders.translation_lang,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,study_fields.title AS study_field  FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE orders.order_id = :order_id";
            } else if ($with_translator_data && !$with_orderer_data) {
                $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.order_files,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,orders.translation_lang,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id,study_fields.title AS study_field  FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study  INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN translators ON order_logs.translator_id=translators.translator_id WHERE orders.order_id= :order_id";
            } else if ($with_orderer_data && !$with_translator_data) {
                $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.order_files,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,orders.translation_lang,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,users.fname AS orderer_fname,users.lname AS orderer_lname,users.user_id,users.email,users.phone,study_fields.title AS study_field  FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study INNER JOIN users ON orders.orderer_id = users.user_id INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE orders.order_id= :order_id";
            } else if ($with_translator_data && $with_orderer_data) {
                $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.order_files,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,orders.translation_lang,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,users.fname AS orderer_fname,users.lname AS orderer_lname,users.user_id,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id ,study_fields.title AS study_field  FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study  INNER JOIN users ON orders.orderer_id = users.user_id INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN translators ON order_logs.translator_id=translators.translator_id WHERE orders.order_id= :order_id";
            }
            if ($ordererId) {
                $sql .= " AND orders.orderer_id='$ordererId'";
            }
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":order_id", $id);
            return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

        } catch (\Exeption $e) {
            return false;
        }
    }
    public static function by_number($number, $with_translator_data = false, $with_orderer_data = false, $ordererId = false)
    {
        try {
            $db = static::getDB();
            if (!$with_translator_data && !$with_orderer_data) {
                $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.order_files,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,orders.translation_lang,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,study_fields.title AS study_field  FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE orders.order_number = :order_number";
            } else if ($with_translator_data && !$with_orderer_data) {
                $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.order_files,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,orders.translation_lang,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id,study_fields.title AS study_field  FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study  INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN translators ON order_logs.translator_id=translators.translator_id WHERE orders.order_number= :order_number";
            } else if ($with_orderer_data && !$with_translator_data) {
                $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.order_files,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,orders.translation_lang,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,users.fname AS orderer_fname,users.lname AS orderer_lname,users.user_id,users.email,users.phone,study_fields.title AS study_field  FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study INNER JOIN users ON orders.orderer_id = users.user_id INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE orders.order_number= :order_number";
            } else if ($with_translator_data && $with_orderer_data) {
                $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.order_files,orders.description,orders.translation_kind,orders.translation_quality,orders.delivery_type,orders.translation_lang,order_logs.is_accepted,orders.order_price,orders.delivery_days,order_logs.transaction_code,orders.order_date_persian,order_logs.accept_date_persian,orders.field_of_study,order_logs.order_step,order_logs.is_done,users.fname AS orderer_fname,users.lname AS orderer_lname,users.user_id,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id ,study_fields.title AS study_field  FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study  INNER JOIN users ON orders.orderer_id = users.user_id INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN translators ON order_logs.translator_id=translators.translator_id WHERE orders.order_number= :order_number";
            }
            if ($ordererId) {
                $sql .= " AND orders.orderer_id='$ordererId'";
            }
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":order_number", $number);
            return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

        } catch (\Exeption $e) {
            return false;
        }
    }
    

    public function new_order_log($orderNumber,$isDone=0)
    {
        try {
            $orderData=self::by_number($orderNumber);
            static::insert("order_logs", [
                'order_id'=>$orderData['order_id'],
                'is_done'=>$isDone
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function get_orders_count_by_user_id($userId)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT COUNT(*) AS orders_count FROM `orders` INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE order_logs.translator_id='$userId'";
            $result = $db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['orders_count'] : 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    //get orders that is not accepted and translator did not request it
    public static function get_orders_without_requested_by_user_id($userId, $page = 1, $offset = 10)
    {
        try {
            $db = static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql = "SELECT orders.order_id,orders.word_numbers,orders.translation_lang,study_fields.title AS study_field,orders.translation_quality,orders.order_price FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE order_logs.is_accepted = '0' AND orders.order_id NOT IN (SELECT order_id FROM translator_order_request WHERE translator_id='$userId') LIMIT $page_limit,$offset";
            $result = $db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
        } catch (\Exception $e) {
            return false;
        }
    }
    public static function get_requested_orders_by_user_id($userId, $idsAsArray = false)
    {
        try {
            if ($idsAsArray) {
                $db = static::getDB();
                $sql = "SELECT order_id FROM translator_order_request WHERE translator_id = :translator_id AND state='1' ";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute(['translator_id' => $userId]) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                return array_map(function ($data) {
                    return $data['order_id'];
                }, $result);
            }else{

            }
            $db = static::getDB();
            $sql = "SELECT * FROM translator_order_request WHERE translator_id = :translator_id AND state='1' ";
            $stmt = $db->prepare($sql);
            return $stmt->execute(['translator_id' => $userId]) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        } catch (\Exception $e) {
            return false;
        }
    }
    //get orders that a translator requested to do
    public static function get_requested_orders_data_by_user_id($userId,$page=1,$offset=10)
    {
        try{
            $db=static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql="SELECT orders.order_id,orders.word_numbers,orders.translation_lang,study_fields.title AS study_field,orders.translation_quality,orders.order_price FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study INNER JOIN order_logs ON orders.order_id = order_logs.order_id INNER JOIN translator_order_request ON orders.order_id=translator_order_request.order_id WHERE order_logs.is_accepted = '0' AND translator_order_request.state = '1' AND translator_order_request.translator_id='$userId' LIMIT $page_limit,$offset";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):false;
        }catch(\Exception $e){
            return false;
        }
    }
    //get orders that a translator denied to do
    public static function get_denied_orders_data_by_user_id($userId,$page=1,$offset=10)
    {
        try{
            $db=static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql="SELECT orders.order_id,orders.word_numbers,orders.translation_lang,study_fields.title AS study_field,orders.translation_quality,orders.order_price FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study INNER JOIN order_logs ON orders.order_id = order_logs.order_id INNER JOIN translator_order_request ON orders.order_id=translator_order_request.order_id WHERE order_logs.is_accepted = '0' AND translator_order_request.state = '0' AND translator_order_request.translator_id='$userId' LIMIT $page_limit,$offset";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):false;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function get_denied_orders_by_user_id($userId, $idsAsArray = false)
    {
        try {
            if ($idsAsArray) {
                $db = static::getDB();
                $sql = "SELECT order_id FROM translator_order_request WHERE translator_id = :translator_id AND state='0' ";
                $stmt = $db->prepare($sql);
                $result = $stmt->execute(['translator_id' => $userId]) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                return array_map(function ($data) {
                    return $data['order_id'];
                }, $result);
            }
            $db = static::getDB();
            $sql = "SELECT * FROM translator_order_request WHERE translator_id = :translator_id AND state='0' ";
            $stmt = $db->prepare($sql);
            return $stmt->execute(['translator_id' => $userId]) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        } catch (\Exception $e) {
            return false;
        }
    }

    public static function request_order($translatorId, $orderNumber)
    {
        try {
            static::insert("translator_order_request", [
                'translator_id' => $translatorId,
                'order_number' => $orderNumber,
                'state' => 1,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public static function deny_order($translatorId, $orderNumber)
    {
        try {
            static::insert("translator_order_request", [
                'translator_id' => $translatorId,
                'order_number' => $orderNumber,
                'state' => 0,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    //count new orderss
    public static function new_orders_count($userId,$choice="new")
    {
        try{
            $db=static::getDB();
            if(!$userId) return false;
            if($choice=="new"){
                $sql="SELECT COUNT(*) AS orders_count FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE order_logs.is_accepted='0' AND orders.order_id NOT IN (SELECT order_id FROM translator_order_request WHERE translator_id='$userId') ";
            }else if($choice=="requested"){
                $sql="SELECT COUNT(*) AS orders_count FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN translator_order_request ON orders.order_id=translator_order_request.order_id WHERE order_logs.is_accepted='0' AND translator_order_request.state='1' AND translator_order_request.translator_id='$userId'";
            }else{
                $sql="SELECT COUNT(*) AS orders_count FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN translator_order_request ON orders.order_id=translator_order_request.order_id WHERE order_logs.is_accepted='0' AND translator_order_request.state='0' AND translator_order_request.translator_id='$userId'";
            }
            $result=$db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['orders_count']:0;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function update_order_log($data, $orderNumber)
    {
        try {
            $orderData=self::by_number($orderNumber);
            static::update("order_logs", $data, "order_id='".$orderData['order_id']."'");
            return true;
        } catch (\Exception $e) {
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
    public static function get_study_fields()
    {
        try {
            $db = static::getDB();
            // $sql = "SELECT * FROM `study_fields` WHERE id NOT IN ('0','41','43','44')";
            $sql = "SELECT * FROM `study_fields` WHERE id NOT IN ('0')";
            $result = $db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected static function getCurrentDatePersian()
    {
        $now = new \DateTime("NOW");
        $year = $now->format("Y");
        $month = $now->format("m");
        $day = $now->format("d");
        $time = $now->format("H:i");
        $persianDate = gregorian_to_jalali($year, $month, $day);
        return $persianDate[0] . "/" . $persianDate[1] . "/" . $persianDate[2] . " " . $time;
    }
}
