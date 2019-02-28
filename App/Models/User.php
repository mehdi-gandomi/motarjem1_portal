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
    public static function by_email($email, $fields = "*")
    {
        try {
            return static::select("users", $fields, ['email' => $email], true);

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

            $userData['register_date_persian'] = get_current_date_persian();
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
            $sql = "SELECT orders.order_id,orders.word_numbers,orders.translation_kind,orders.translation_lang,orders.translation_quality,orders.delivery_type,order_logs.is_accepted,order_logs.transaction_code,orders.order_price,orders.translator_id FROM orders  INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE orders.orderer_id=:orderer_id";
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
            $sql="SELECT COUNT(*) AS orders_count FROM `orders` INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE `orderer_id`= :orderer_id ";

            if(is_array($filtering_Options) && count($filtering_Options)>0){
                
                if(isset($filtering_Options['is_done'])){
                    $sql.=" AND `is_done` = :is_done";
                }
                if(isset($filtering_Options['is_accepted'])){
                    $sql.=" AND `is_accepted` = :is_accepted";
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
    //change the password for reset password page
    public static function change_password($username,$password)
    {
        try{
            static::update("users",[
                'password'=>\md5(\md5($password))
            ],"username = '$username'");
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function get_current_date_persian()
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
