<?php
namespace App\Models;

use Core\Model;
use \PDO;

class Translator extends Model
{
    public static function by_username($username, $fields = "*")
    {
        try {
            $db=static::getDB();
            $sql="SELECT * FROM translators WHERE username=:username AND level='2'";
            $stmt=$db->prepare($sql);
            $stmt->bindParam(":username",$username);
            return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC):false;
        } catch (\Exception $e) {
            return false;
        }

    }
    public static function by_email($email, $fields = "*")
    {
        try {
            $db=static::getDB();
            $sql="SELECT * FROM translators WHERE email=:email AND level='2'";
            $stmt=$db->prepare($sql);
            $stmt->bindParam(":email",$email);
            return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC):false;
        } catch (\Exception $e) {
            return false;
        }

    }
    public static function by_id($translatorId, $fields = "*")
    {
        try {
            $db=static::getDB();
            $sql="SELECT * FROM translators WHERE translator_id=:translator_id AND level='2'";
            $stmt=$db->prepare($sql);
            $stmt->bindParam(":translator_id",$translatorId);
            return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC):false;
        } catch (\Exception $e) {
            return false;
        }

    }
    public static function check_existance($postFields)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT username FROM translators WHERE username='" . $postFields['username'] . "' OR email='" . $postFields['email'] . "'";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return true;

        }

    }
    public static function check_existance_by_username($postFields)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT username FROM translators WHERE username='" . $postFields['username'] ."'";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return true;

        }

    }
    public static function check_existance_by_email($postFields)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT email FROM translators WHERE email='" . $postFields['email'] . "'";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return true;

        }

    }
    public static function login_check($username, $password)
    {
        try {
            $userData = self::by_username($username, "u_name,password");
        } catch (\Exception $e) {
            return false;
        }
    }
    public static function new ($postFields) {
        try {
            unset($postFields['confirm_pass']);
            unset($postFields['captcha_input']);
            unset($postFields['csrf_name']);
            unset($postFields['csrf_value']);
            if(isset($postFields['user_photo_file'])){
                unset($postFields['user_photo_file']);
            }
            if(isset($postFields['meli_card_photo_file'])){
             unset($postFields['meli_card_photo_file']);   
            }
            $postFields['register_date_persian'] = self::get_current_date_persian();
            $postFields['password'] = \md5(\md5($postFields['password']));
            $postFields['is_active'] = 0;
            static::insert("translators", $postFields);
            return array(
                'username' => $postFields['username'],
                'password' => $postFields['password'],
            );   

        } catch (\Exception $e) {
            return false;
        }
         
    }
    //this function lets you update translator data by user id
    public static function edit_by_id($userId,$userData)
    {
        try{
            if(isset($userData['password'])){
                $userData['password'] = \md5(\md5($userData['password']));                
            }
            $userData['en_to_fa']= isset($userData['en_to_fa']) ? 1:0;
            $userData['fa_to_en']= isset($userData['fa_to_en']) ? 1:0;
            
            $translatorData=self::by_id($userId,"username,email");
            if($translatorData['username']!=$userData['username'] && self::check_existance_by_username($userData)){
                return ['status'=>false,'message'=>'این نام کاربری از قبل موجود است !'];
            }
            if($translatorData['email']!=$userData['email'] && self::check_existance_by_email($userData)){
                return ['status'=>false,'message'=>'این ایمیل از قبل موجود است !'];
            }
            static::update("translators",$userData,"`translator_id` = '$userId'");
            return ['status'=>true,'message'=>'اطلاعات با موفقیت ثبت شد !'];
        }catch(\Exception $e){
            return ['status'=>false,'message'=>'خطایی در ذخیره اطلاعات رخ داد !'];
        }
    }

    public static function get_translator_data_by_id($id, $fields = "*")
    {
        try {
            return static::select("translators", $fields, ['translator_id' => $id], true);
        } catch (\Exception $e) {
            return false;
        }
    }

    //change the password for reset password page
    public static function change_password($username, $password)
    {
        try {
            static::update("translators", [
                'password' => \md5(\md5($password)),
            ], "username = '$username'");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    //this method activtes the account by username given to it
    public function activate($username)
    {
        try {
            return static::update("translators", ["is_active" => 1], "username='$username'");

        } catch (\Exception $e) {
            return false;

        }
    }
    public static function get_test_by_filtering($language,$fieldId)
    {
        try{
            $db=static::getDB();
            $sql="SELECT tests.id AS test_id,tests.study_field_id,tests.text,study_fields.title FROM tests INNER JOIN study_fields ON study_fields.id=tests.study_field_id  WHERE study_field_id = :field_id AND language_id = :language_id";
            $stmt=$db->prepare($sql);
            $stmt->execute([
                ':field_id'=>$fieldId,
                ':language_id'=>$language
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }catch(\Exception $e){
            return false;
        }
    }


    //save translated text from translator (step before employment)
    public static function save_test_data($data)
    {
        try{
            static::insert("translator_tests",$data);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    //this function gets  messages that sent by translator
    public static function get_messages_by_id($userId,$page, $amount,$filtering_Options=null)
    {
        try{
            $db=static::getDB();
            $result=false;
            $page_limit = ($page - 1) * $amount;
            $sql="SELECT messaging.msg_id,messaging.parent_msg_id,messaging.create_date_persian,messaging.update_date_persian,messaging.subject,messaging.body,messaging.is_answered,messaging.is_read FROM messaging WHERE  messaging.sender_id = :sender_id AND user_type='2'";
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
    //get unread messages count by user id
    public static function get_unread_messages_count_by_user_id($userId)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT COUNT(*) AS messages_count FROM `messaging` WHERE `reciever_id`= '$userId' AND user_type='2' AND `is_read`= '0'";
            $result = $db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['messages_count'] : 0;

        } catch (\Exception $e) {
            return false;
        }
    }
    
    public static function get_translator_orders_by_user_id($userId,$page,$offset,$filteringOptions)
    {
        try{
            $db = static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql = "SELECT orders.order_id,orders.order_number,orders.word_numbers,orders.translation_lang,study_fields.title AS study_field,orders.translation_quality,orders.order_price FROM orders INNER JOIN study_fields ON study_fields.id=orders.field_of_study INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE order_logs.is_accepted = '1' AND order_logs.translator_id = '$userId' AND order_logs.is_done IN (".\implode(",",$filteringOptions['is_done']).")  LIMIT $page_limit,$offset";
            $result = $db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
        }catch(\Exception $e){
            return false;
        }
    }

    public static function get_translator_orders_count_by_user_id($userId,$filteringOptions)
    {
        try{
            $db = static::getDB();
            $sql = "SELECT COUNT(*) AS orders_count FROM orders INNER JOIN order_logs ON orders.order_id = order_logs.order_id WHERE order_logs.is_accepted = '1' AND order_logs.translator_id = '$userId' AND order_logs.is_done IN (".\implode(",",$filteringOptions['is_done']).")";
            $result = $db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['orders_count'] : false;
        }catch(\Exception $e){
            return false;
        }
    }

    //get translator's bank info by user id
    public static function get_bank_info_by_user_id($userId)
    {
        try{
            $result=static::select("translator_account","*",[],true);
            return $result ? $result : [];
        }catch(\Exception $e){
            return [];
        }
    }

    //insert or update translator's bank info #endregion
    public static function save_bank_info($userId,$data)
    {
        try{
            $isBankInfoExists=self::get_bank_info_by_user_id($userId);
            $data['card_number']=preg_replace("(\s)","",$data['card_number']);
            $data['shaba_number']=preg_replace("(\s)","",$data['shaba_number']);
            if($isBankInfoExists){
                static::update("translator_account",$data,"translator_id = '$userId'");
            }else{
                $data['translator_id']=$userId;
                static::insert("translator_account",$data);
            }
            return true;
        }catch(\Exception $e){
            return false;
        }
    }

    //get account info by given user id
    public static function get_account_info_by_user_id($userId)
    {
        try{
            return static::select("translator_account","account_credit,revenue",['translator_id'=>$userId],true);
        }catch(\Exception $e){
            return false;
        }
    }
    //get orders that translator completed successfully by user id
    public static function get_completed_orders_by_user_id($userId,$page=1,$offset=10)
    {
        try{
            $db=static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql="SELECT orders.order_id,orders.order_number,orders.translation_quality,orders.translation_lang,orders.order_price FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE order_logs.is_done='1' AND order_logs.translator_id='$userId' LIMIT $page_limit,$offset";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):false;
        }catch(\Exception $e){
            return false;
        }
    }
    //get orders count that translator completed successfully by user id
    public static function get_completed_orders_count_by_user_id($userId)
    {
        try{
            $db=static::getDB();
            $sql="SELECT COUNT(*) AS orders_count FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE order_logs.is_done='1' AND order_logs.translator_id='$userId'";
            $result=$db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['orders_count']:0;
        }catch(\Exception $e){
            return false;
        }
    }
    //get account withdrawals or account checkouts by user id
    public static function get_account_checkouts_by_user_id($userId,$page=1,$offset=10)
    {
        try{
            $db=static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql="SELECT * FROM payment_logs WHERE translator_id='$userId' LIMIT $page_limit,$offset";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):false;
        }catch(\Exception $e){
            return false;
        }
    }
    //get count of account withdrawals or account checkouts by user id
    public static function get_account_checkouts_count_by_user_id($userId)
    {
        try{
            $db=static::getDB();
            $sql="SELECT COUNT(*) AS checkout_count FROM payment_logs WHERE translator_id='$userId'";
            $result=$db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['checkout_count']:false;
        }catch(\Exception $e){
            return false;
        }
    }
    //get requests for checkouts that translator sent to admin and is not paid yet
    public static function get_account_checkout_requests_by_user_id($userId,$page,$offset)
    {
        try{
            $db=static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql="SELECT * FROM translator_checkout_request WHERE translator_id='$userId' LIMIT $page_limit,$offset";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):false;
        }catch(\Exception $e){
            return false;
        }
    }
    //get count of requests for checkouts that translator sent to admin and is not paid yet
    public static function get_account_checkout_requests_count_by_user_id($userId)
    {
        try{
            $db=static::getDB();
            $sql="SELECT COUNT(*) AS checkout_count FROM translator_checkout_request WHERE translator_id='$userId'";
            $result=$db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['checkout_count']:false;
        }catch(\Exception $e){
            return false;
        }
    }
    //get total price of all trnslator checkouts by user id
    public static function get_total_checkout_price_by_user_id($userId)
    {
        try{
            $db=static::getDB();
            $sql="SELECT SUM(amount) AS totalCheckoutsPrice FROM payment_logs WHERE translator_id='$userId'";
            $result=$db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['totalCheckoutsPrice']:false;
        }catch(\Exception $e){
            return false;
        }
    }
    //get total of checkout requests by user id
    public static function get_total_checkout_requests_price_by_user_id($userId)
    {
        try{
            $db=static::getDB();
            $sql="SELECT SUM(amount) AS totalCheckoutsPrice FROM translator_checkout_request WHERE translator_id='$userId'";
            $result=$db->query($sql);
            return $result ? intval($result->fetch(PDO::FETCH_ASSOC)['totalCheckoutsPrice']):false;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function request_checkout($data)
    {
        try{
            $data['request_date_persian']=self::get_current_date_persian();
            $data['translator_id']=$_SESSION['user_id'];
            static::insert("translator_checkout_request",$data);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
    //this function checks if translator has sent the test to admin.if thats true then dont show the test section
    public static function check_if_translator_has_tested_by_user_id($userId)
    {
        try{
            $result=static::select("translator_tests","translator_id",['translator_id'=>$userId],true);
            return ($result && isset($result['translator_id'])) ? true:false;
        }catch(\Exception $e){
            return false;
        }
    }
    //START admin functions
    public static function employ($translatorId)
    {
        try{
            static::update("translators",["is_employed"=>'1'],"translator_id = '$translatorId'");
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function deny_employment($translatorId)
    {
        try{
            static::update("translators",["is_denied"=>'1'],"translator_id = '$translatorId'");
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
