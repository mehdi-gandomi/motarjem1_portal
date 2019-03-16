<?php
namespace App\Models;

use Core\Model;
use \PDO;

class Admin extends Model
{
        public static function by_username($username, $fields = "*")
        {
            try {
                return static::select("translators", $fields, ['username' => $username], true);

            } catch (\Exception $e) {
                return false;
            }

        }
        //Admin panel functions
        public static function get_employment_requests()
        {
            try{
                $db=static::getDB();
                $sql="SELECT translators.translator_id,translators.avatar AS translator_avatar,translators.username AS translator_username,translators.fname AS translator_fname,translators.lname AS translator_lname,translators.register_date_persian,translators.degree AS translator_degree,translators.exp_years AS translator_exp_years FROM translator_tests INNER JOIN translators ON translator_tests.translator_id=translators.translator_id";
                $result=$db->query($sql);
                return $result ? $result->fetchAll(PDO::FETCH_ASSOC):false;
            }catch(\Exception $e){
                return false;
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
}