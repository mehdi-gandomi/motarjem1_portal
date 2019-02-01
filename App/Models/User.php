<?php
namespace App\Models;

use Core\Model;
use \PDO;

class User extends Model
{
    public static function by_username($username, $fields = "*")
    {
        try {
            return static::select("users", $fields, ['username' => $username], true);

        } catch (\Exception $e) {
            return false;

        }

    }
    public static function by_id($userId, $fields = "*")
    {
        try {
            return static::select("users", $fields, ['user_id' => $userId], true);

        } catch (\Exception $e) {
            return false;

        }

    }
    public static function activate($username)
    {
        try {
            return static::update("users", ["is_active" => 1], "username='$username'");

        } catch (\Exception $e) {
            return false;

        }

    }

    public static function check_user_existance($userData)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT user_id FROM users WHERE username='$userData[username]' OR email='$userData[email]'";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return false;

        }

    }
    public static function create($userData)
    {
        try {
            $now = new \DateTime("NOW");
            $year = $now->format("Y");
            $month = $now->format("m");
            $day = $now->format("d");
            $time = $now->format("H:i");
            $persianDate = gregorian_to_jalali($year, $month, $day);
            $userData['register_date_persian'] = $persianDate[0] . "/" . $persianDate[1] . "/" . $persianDate[2] . " " . $time;
            $userData['password'] = \md5(\md5($userData['password']));
            $userData['is_active'] = 0;
            static::insert("users", $userData);
            return static::get_last_inserted_id();

        } catch (\Exception $e) {
            var_dump($e);
            return false;

        }

    }
    //this function is for getting orders by user id . you can limit the result by passing the second argument to your number of choice
    public static function get_orders_by_user_id($userId, $page, $amount,$filtering_Options=null)
    {
        try {
            $db = static::getDB();
            $result=false;
            $page_limit = ($page - 1) * $amount;
            $sql = "SELECT orders.order_id,orders.word_numbers,orders.translation_kind,orders.translation_quality,orders.delivery_type,order_logs.is_accepted,orders.order_price,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id FROM orders INNER JOIN translators ON orders.translator_id=translators.translator_id INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE orders.orderer_id=:orderer_id";
            
            if(is_array($filtering_Options) && count($filtering_Options)>0){
                
                if(isset($filtering_Options['is_done'])){
                    $sql.=" AND `is_done` = :is_done";
                }
                $filtering_Options['orderer_id']=$userId;
                $sql.=" ORDER BY order_date DESC LIMIT $page_limit,$amount";
                $stmt = $db->prepare($sql);
                $result=$stmt->execute($filtering_Options);
            }else{
                
                $sql.=" ORDER BY order_date DESC LIMIT $page_limit,$amount";
                $stmt = $db->prepare($sql);
                $result=$stmt->execute(['orderer_id'=>$userId]);
            }
            return $result ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    //this function is for getting orders count!,you can set second argument to get working orders
    public static function get_orders_count_by_user_id($userId, $filtering_Options=null)
    {
        try {
            $db = static::getDB();
            $result=false;
            $sql="SELECT COUNT(*) AS orders_count FROM `orders` WHERE `orderer_id`= :orderer_id";

            if(is_array($filtering_Options) && count($filtering_Options)>0){
                
                if(isset($filtering_Options['is_done'])){
                    $sql.=" AND `is_done` = :is_done";
                }
                $filtering_Options['orderer_id']=$userId;
                $stmt = $db->prepare($sql);
                $result=$stmt->execute($filtering_Options);
            }else{
                
                $stmt = $db->prepare($sql);
                $result=$stmt->execute(['orderer_id'=>$userId]);
            }
            
            return $result ? $stmt->fetch(PDO::FETCH_ASSOC)['orders_count'] : false;
        } catch (\Exception $e) {
            return false;
        }
    }
    //this function gets unread messages by user
    public static function get_unread_messages_count_by_user_id($userId)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT COUNT(*) AS messages_count FROM `messaging` WHERE `reciever_id`= '$userId' AND `is_read`= '0'";
            $result = $db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['messages_count'] : false;

        } catch (\Exception $e) {
            return false;
        }
    }

    //this function gets  user messages by id
    public static function get_messages_by_id($userId,$page, $amount,$filtering_Options=null)
    {
        try{
            $db=static::getDB();
            $result=false;
            $page_limit = ($page - 1) * $amount;
            $sql="SELECT messaging.msg_id,messaging.parent_msg_id,messaging.create_date_persian,messaging.update_date_persian,messaging.subject,messaging.body,messaging.is_answered,messaging.is_read FROM messaging WHERE  messaging.sender_id = :sender_id";
            if(is_array($filtering_Options) && count($filtering_Options)>0){
                
                if(isset($filtering_Options['is_read'])){
                    $sql.=" AND `is_read` IN (".\implode(",",$filtering_Options['is_read']).")";
                }
                if(isset($filtering_Options['is_answered'])){
                    $sql.=" AND `is_answered` IN (".\implode(",",$filtering_Options['is_answered']).")";
                }   
            }
            $sql.=" ORDER BY update_date DESC LIMIT $page_limit,$amount";
            $stmt = $db->prepare($sql);
            // $stmt->bindParam(":reciever_id",$userId);
            return $stmt->execute(['sender_id'=>$userId]) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
        }catch(\Exception $e){
            return false;
        }

        
    }
    //this function gets  user messages count by id
    public static function get_messages_count_by_id($userId,$filtering_Options)
    {
        try{
            $db=static::getDB();
            $sql="SELECT COUNT(*) AS messages_count FROM messaging WHERE  messaging.sender_id = :sender_id";
            if(is_array($filtering_Options) && count($filtering_Options)>0){
                
                if(isset($filtering_Options['is_read'])){
                    $sql.=" AND `is_read` IN (".\implode(",",$filtering_Options['is_read']).")";
                }
                if(isset($filtering_Options['is_answered'])){
                    $sql.=" AND `is_answered` IN (".\implode(",",$filtering_Options['is_answered']).")";
                }   
            }
            $stmt = $db->prepare($sql);
            return $stmt->execute(['sender_id'=>$userId]) ? $stmt->fetch(PDO::FETCH_ASSOC)['messages_count'] : false;
        }catch(\Exception $e){
            return false;
        }
    }
    //this function lets you update user data by user id
    public static function edit_by_id($userId,$userData)
    {
        try{
            if(isset($userData['password'])){
                $userData['password'] = \md5(\md5($userData['password']));                
            }
            static::update("users",$userData,"`user_id` = '$userId'");
            return true;
        }catch(\Exception $e){
            
            return false;
        }
    }
}
