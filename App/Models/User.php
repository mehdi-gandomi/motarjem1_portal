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
    public static function check_existance_by_username($postFields)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT username FROM users WHERE username='" . $postFields['username'] ."'";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return true;

        }

    }
    public static function check_existance_by_email($postFields)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT email FROM users WHERE email='" . $postFields['email'] . "'";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return true;

        }

    }
    public static function create($userData)
    {
        try {

            $userData['register_date_persian'] = self::get_current_date_persian();
            $userData['password'] = \md5(\md5($userData['password']));
            $userData['is_active'] = 0;
            static::insert("users", $userData);
            return static::get_last_inserted_id();

        } catch (\Exception $e) {
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
            $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.translation_kind,orders.translation_lang,orders.translation_quality,orders.delivery_type,order_logs.is_accepted,order_logs.transaction_code,orders.order_price,order_logs.translator_id FROM orders  INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE orders.orderer_id=:orderer_id";
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
    //this function is for getting orders by user id . you can limit the result by passing the second argument to your number of choice and also you can filter results
    public static function get_orders_with_filter_by_user_id($userId, $page, $amount,$filtering_Options=null)
    {
        try {
            $db = static::getDB();
            $page_limit = ($page - 1) * $amount;
            $userId=intval($userId);
            $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.translation_kind,orders.translation_lang,orders.translation_quality,orders.delivery_type,order_logs.is_accepted,order_logs.transaction_code,orders.order_price,study_fields.title as study_field FROM orders INNER JOIN order_logs ON orders.order_id = order_logs.order_id INNER JOIN study_fields ON orders.field_of_study = study_fields.id WHERE order_logs.transaction_code != '0' AND orders.orderer_id=:orderer_id";
            if(is_array($filtering_Options) && count($filtering_Options)>0){
                $sql.=" AND ";
                $arr=[];
                foreach($filtering_Options as $key=>$option){
                    array_push($arr,"`$key` IN ('".implode("','",$option)."')");
                }
                $sql.=implode(" AND ",$arr);
                $arr=null;
            }
            $sql.=" ORDER BY order_date DESC LIMIT $page_limit,$amount";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":orderer_id",$userId);
            $result=$stmt->execute();
            return $result ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
        } catch (\Exception $e) {
            return false;
        }
    }
    //this function gets count of orders by user id . you can limit the result by passing the second argument to your number of choice and also you can filter results
    public static function get_orders_count_with_filter_by_user_id($userId,$filtering_Options=null)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT COUNT(*) AS orders_count FROM orders  INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE order_logs.transaction_code != '0' AND orders.orderer_id=:orderer_id";
            if(is_array($filtering_Options) && count($filtering_Options)>0){
                $sql.=" AND ";
                $arr=[];
                foreach($filtering_Options as $key=>$option){
                    array_push($arr,"`$key` IN ('".implode("','",$option)."')");
                }
                $sql.=implode(" AND ",$arr);
                $arr=null;
            }
            $sql.=" ORDER BY order_date DESC";
            $stmt = $db->prepare($sql);
            $result=$stmt->execute(['orderer_id'=>$userId]);
            return $result ? $stmt->fetch(PDO::FETCH_ASSOC)['orders_count'] : 0;
        } catch (\Exception $e) {
            return 0;
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
            $existData=self::by_id($userId,"username,email");
            if($existData['username']!=$userData['username'] && self::check_existance_by_username($userData)){
                return ['status'=>false,'message'=>'این نام کاربری از قبل موجود است !'];
            }
            if($existData['email']!=$userData['email'] && self::check_existance_by_email($userData)){
                return ['status'=>false,'message'=>'این ایمیل از قبل موجود است !'];
            }
            static::update("users",$userData,"`user_id` = '$userId'");
            return ['status'=>true,'message'=>'اطلاعات با موفقیت ثبت شد !'];
        }catch(\Exception $e){
            return ['status'=>false,'message'=>'خطایی در ذخیره اطلاعات رخ داد !'];
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

    public static function get_all_by_filtering($page, $offset, $fields="*", $filteringOptions=null)
    {
        try{
            $db=static::getDB();
            $sql="SELECT $fields FROM users";
            $page_limit = ($page - 1) * $offset;
            if (is_array($filteringOptions) && count($filteringOptions) > 0){
                $sql.=" WHERE ";
                $arr=[];
                foreach($filteringOptions as $key=>$option){
                    array_push($arr,"`$key` IN ('".implode("','",$option)."')");
                }
                $sql.=implode(" AND ",$arr);
                $arr=null;
            }
            $sql.=" LIMIT $page_limit,$offset";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
        }catch (\Exception $e){
            return [];
        }
    }
    public static function get_all_count_by_filtering($filteringOptions)
    {
        try{
            $db=static::getDB();
            $sql="SELECT COUNT(*) AS users_count FROM users WHERE";
            $arr=[];
            foreach($filteringOptions as $key=>$option){
                array_push($arr,"`$key` IN ('".implode("','",$option)."')");
            }
            $sql.=implode(" AND ",$arr);
            $arr=null;
            $result=$db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['users_count']:0;
        }catch (\Exception $e){
            return [];
        }
    }

    public static function deactivate_by_user_id($userId)
    {
        try {
            static::update("users", ["is_active" => 0], "user_id='$userId'");
            return true;
        } catch (\Exception $e) {
            return false;

        }
    }
    public static function activate_by_user_id($userId)
    {
        try {
            static::update("users", ["is_active" => 1], "user_id='$userId'");
            return true;
        } catch (\Exception $e) {
            return false;

        }
    }
}
