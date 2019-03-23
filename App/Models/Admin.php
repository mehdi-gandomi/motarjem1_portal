<?php
namespace App\Models;

use Core\Model;
use \PDO;

class Admin extends Model
{
        public static function by_username($username, $fields = "*")
        {
            try {
                $db=static::getDB();
                $sql="SELECT * FROM translators WHERE username=:username AND level='1'";
                $stmt=$db->prepare($sql);
                $stmt->bindParam(":username",$username);
                return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC):false;
            } catch (\Exception $e) {
                return false;
            }

        }
        //Admin panel functions
        public static function get_employment_requests($page,$amount)
        {
            try{
                $db=static::getDB();
                $page_limit = ($page - 1) * $amount;
                $sql="SELECT translators.translator_id,translators.avatar AS translator_avatar,translators.username AS translator_username,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.register_date_persian,translators.degree AS translator_degree,translators.exp_years AS translator_exp_years FROM translator_tests INNER JOIN translators ON translator_tests.translator_id=translators.translator_id WHERE translators.is_denied='0' AND translators.is_employed='0' LIMIT $page_limit,$amount";
                $result=$db->query($sql);
                return $result ? $result->fetchAll(PDO::FETCH_ASSOC):false;
            }catch(\Exception $e){
                return false;
            }
        }
        //get count of translator employment requests
        public static function get_employment_requests_count()
        {
            try{
                $db=static::getDB();
                $sql="SELECT COUNT(*) AS requests_count FROM translator_tests INNER JOIN translators ON translator_tests.translator_id=translators.translator_id WHERE translators.is_denied='0' AND translators.is_employed='0'";
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC)['requests_count']:0;
            }catch(\Exception $e){
                return 0;
            }
        }

        //get denied requests
        public static function get_denied_requests($page,$amount)
        {
            try{
                $db=static::getDB();
                $page_limit = ($page - 1) * $amount;
                $sql="SELECT translators.translator_id,translators.avatar AS translator_avatar,translators.username AS translator_username,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.register_date_persian,translators.degree AS translator_degree,translators.exp_years AS translator_exp_years FROM translators WHERE translators.is_denied='1' LIMIT $page_limit,$amount";
                $result=$db->query($sql);
                return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
            }catch(\Exception $e){
                return [];
            }

        }

        //count of get denied requests
        public static function get_denied_requests_count()
        {
            try{
                $db=static::getDB();
                $sql="SELECT COUNT(*) AS denied_count FROM translators WHERE translators.is_denied='1'";
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC)['denied_count']:0;
            }catch(\Exception $e){
                return 0;
            }

        }
        //get translator data and test (text that they have to translate) data in one function
        public static function get_translator_test_info_by_user_id($userId)
        {
            try{
                $db=static::getDB();
                $sql="SELECT translators.translator_id,translators.avatar,translators.fname,translators.lname,translators.sex,translators.address,translators.melicard_photo,translators.meli_code,translators.degree,translators.exp_years,translators.register_date_persian,translators.en_to_fa,translators.fa_to_en,translators.phone,translators.cell_phone,translators.email,translator_tests.translated_text,study_fields.title AS study_field_title,tests.text AS question_text,tests.language_id FROM translator_tests INNER JOIN translators ON translators.translator_id=translator_tests.translator_id INNER JOIN tests ON translator_tests.test_id=tests.id INNER JOIN study_fields ON tests.study_field_id=study_fields.id WHERE translator_tests.translator_id='$userId'";
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC):[];
            }catch(\Exception $e){
                return [];
            }
        }
        //get translator info from db by user id
        public static function get_translator_basic_info_by_user_id($userId)
        {
            try{
                return static::select("translators","fname,lname,avatar,degree,fa_to_en,en_to_fa,email,phone,cell_phone",['translator_id'=>$userId],true);
            }catch(\Exception $e){
                return false;
            }
        }
        //get admin total revenue 
        public static function get_total_revenue()
        {
            try{
                $db=static::getDB();
                $sql="SELECT revenue FROM translator_account INNER JOIN translators ON translator_account.translator_id=translators.translator_id WHERE translators.level='1'";
                $result=$db->query($sql);
                return $result ? (intval($result->fetch(PDO::FETCH_ASSOC)['revenue'])*15)/100:0;
            }catch(\Exception $e){
                return 0;
            }
        }
        //get revenue of this month and calculate admin share of it and return it
        public static function get_monthly_revenue()
        {
            try{
                $db=static::getDB();
                $now=new \DateTime("NOW");
                $sql="SELECT SUM(orders.order_price) AS revenue FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id WHERE order_logs.transaction_code != '0' AND order_logs.is_done = '1' AND orders.order_date BETWEEN :last_month AND :today";
                $stmt=$db->prepare($sql);
                $stmt->execute([
                    'today'=>$now->format("Y-m-d"),
                    'last_month'=>$now->modify("-1 month")->format("Y-m-d")
                ]);
                if($stmt){
                    $revenue=$stmt->fetch(PDO::FETCH_ASSOC)['revenue'];
                    $revenue=(intval($revenue)*15)/100;
                    return $revenue;
                }
                return 0;
            }catch(\Exception $e){
                return 0;
            }
        }
        //get tickets that is sent to admin but admin hasn't read it
        public static function get_unread_tickets_count()
        {
            try{
                $db=static::getDB();
                $sql="SELECT COUNT(*) AS unread_count FROM Tickets WHERE state='waiting'";
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC)['unread_count']:0;
            }catch(\Exception $e){
                return 0;
            }
        }
        //translators request to do the order
        public static function get_translator_order_requests($page,$amount)
        {
            try{
                $page_limit = ($page - 1) * $amount;
                $db=static::getDB();
                $sql="SELECT translator_order_request.id,translator_order_request.request_date_persian,orders.order_price,orders.order_number,orders.order_id,orders.order_date_persian,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id FROM `translator_order_request` INNER JOIN orders ON orders.order_id=translator_order_request.order_id INNER JOIN translators ON translators.translator_id=translator_order_request.translator_id WHERE translator_order_request.is_denied='0' LIMIT $page_limit,$amount";
                $result=$db->query($sql);
                return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
            }catch(\Exception $e){
                return [];
            }
        }
        //count of translators request to do the order
        public static function get_translator_order_requests_count()
        {
            try{
                $db=static::getDB();
                $sql="SELECT COUNT(*) AS requests_count FROM `translator_order_request` WHERE translator_order_request.is_denied='0'";
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC)['requests_count']:0;
            }catch(\Exception $e){
                return 0;
            }
        }
        public static function get_translator_denied_order_requests($page,$offset)
        {
            try{
                $page_limit = ($page - 1) * $offset;
                $db=static::getDB();
                $sql="SELECT translator_order_request.id,translator_order_request.request_date_persian,orders.order_price,orders.order_number,orders.order_id,orders.order_date_persian,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.translator_id FROM `translator_order_request` INNER JOIN orders ON orders.order_id=translator_order_request.order_id INNER JOIN translators ON translators.translator_id=translator_order_request.translator_id WHERE translator_order_request.is_denied='1' LIMIT $page_limit,$offset";
                $result=$db->query($sql);
                return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
            }catch(\Exception $e){
                return [];
            }
        }
        public static function get_translator_denied_order_requests_count()
        {
            try{
                $page_limit = ($page - 1) * $offset;
                $db=static::getDB();
                $sql="SELECT COUNT(*) AS requests_count FROM `translator_order_request`  WHERE translator_order_request.is_denied='1' LIMIT $page_limit,$offset";
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC)['requests_count']:0;
            }catch(\Exception $e){
                return 0;
            }
        }
        public static function get_all_pending_orders($page,$offset)
        {
            try{
                $page_limit = ($page - 1) * $offset;
                $db=static::getDB();
                $sql="SELECT orders.order_id,orders.order_number,orders.translation_quality,orders.translation_lang,orders.order_price,orders.word_numbers,study_fields.title AS study_field FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN study_fields ON orders.field_of_study = study_fields.id WHERE order_logs.is_done='0' AND order_logs.translator_id != '0' LIMIT $page_limit,$offset";
                $result=$db->query($sql);
                return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
            }catch(\Exception $e){
                return [];
            }
        }
        public static function get_all_pending_orders_count()
        {
            try{
                $db=static::getDB();
                $sql="SELECT COUNT(*) AS orders_count FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN study_fields ON orders.field_of_study = study_fields.id WHERE order_logs.is_done='0' AND order_logs.translator_id != '0'";
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC)['orders_count']:0;
            }catch(\Exception $e){
                return 0;
            }
        }
        public static function get_all_completed_orders($page,$offset)
        {
            try{
                $page_limit = ($page - 1) * $offset;
                $db=static::getDB();
                $sql="SELECT orders.order_id,orders.order_number,orders.translation_quality,orders.translation_lang,orders.order_price,orders.word_numbers,study_fields.title AS study_field FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN study_fields ON orders.field_of_study = study_fields.id WHERE order_logs.is_done='1' AND order_logs.translator_id != '0' LIMIT $page_limit,$offset";
                $result=$db->query($sql);
                return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
            }catch(\Exception $e){
                return [];
            }
        }
        public static function get_all_completed_orders_count()
        {
            try{
                $db=static::getDB();
                $sql="SELECT COUNT(*) AS orders_count FROM orders INNER JOIN order_logs ON orders.order_id=order_logs.order_id INNER JOIN study_fields ON orders.field_of_study = study_fields.id WHERE order_logs.is_done='1' AND order_logs.translator_id != '0'";
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC)['orders_count']:0;
            }catch(\Exception $e){
                return 0;
            }
        }
        //get translators payment requests
        public static function get_translator_payment_requests($page,$offset,$filtering_options)
        {
            try{
                $db=static::getDB();
                $page_limit = ($page - 1) * $offset;
                $sql="SELECT translator_checkout_request.id,translator_checkout_request.amount,translator_checkout_request.request_date_persian,translator_checkout_request.is_paid,translator_checkout_request.state,translators.translator_id,translators.fname AS translator_fname,translators.lname AS translator_lname FROM translator_checkout_request INNER JOIN translators ON translator_checkout_request.translator_id = translators.translator_id";
                if(is_array($filtering_options) && count($filtering_options)>0){
                    $sql.=" WHERE ";
                    $arr=[];
                    foreach($filtering_options as $key=>$option){
                        array_push($arr,"`$key` IN ('".implode("','",$option)."')");
                    }
                    $sql.=implode(" AND ",$arr);
                    $arr=null;
                }
                $sql.=" LIMIT $page_limit,$offset";
                $result=$db->query($sql);
                return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
            }catch(\Exception $e){
                return [];
            }
        }
        //get count of translators payment requests
        public static function get_translator_payment_requests_count($filtering_options)
        {
            try{
                $db=static::getDB();
                $sql="SELECT COUNT(*) AS payment_requests FROM translator_checkout_request INNER JOIN translators ON translator_checkout_request.translator_id = translators.translator_id";
                if(is_array($filtering_options) && count($filtering_options)>0){
                    $sql.=" WHERE ";
                    $arr=[];
                    foreach($filtering_options as $key=>$option){
                        array_push($arr,"`$key` IN ('".implode("','",$option)."')");
                    }
                    $sql.=implode(" AND ",$arr);
                    $arr=null;
                }
                $result=$db->query($sql);
                return $result ? $result->fetch(PDO::FETCH_ASSOC)['payment_requests']:0;
            }catch(\Exception $e){
                return 0;
            }
        }
}