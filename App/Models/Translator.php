<?php
namespace App\Models;

use Core\Model;
use \PDO;

class Translator extends Model
{
    public static function by_username($username, $fields = "*")
    {
        try {
            return static::select("translators", $fields, ['username' => $username], true);

        } catch (\Exception $e) {
            return false;
        }

    }
    public static function by_email($email, $fields = "*")
    {
        try {
            return static::select("translators", $fields, ['email' => $email], true);

        } catch (\Exception $e) {
            return false;
        }

    }
    public static function by_id($translatorId, $fields = "*")
    {
        try {
            return static::select("translators", $fields, ['translator_id' => $translatorId], true);

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
    public static function login_check($username, $password)
    {
        try {
            $userData = self::by_username($username, "u_name,password");
        } catch (\Exception $e) {
            return false;
        }
    }
    function new ($postFields) {
        try {
            unset($postFields['confirm_pass']);
            unset($postFields['captcha_input']);
            unset($postFields['csrf_name']);
            unset($postFields['csrf_value']);
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
            $sql="SELECT tests.study_field_id,tests.text,study_fields.title FROM tests INNER JOIN study_fields ON study_fields.id=tests.study_field_id  WHERE study_field_id = :field_id AND language_id = :language_id";
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
    public static function get_study_fields()
    {
        try{
            $db=static::getDB();
            $sql="SELECT * FROM `study_fields` WHERE id NOT IN ('0','41','43','44')";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : false;
        }catch(\Exception $e){
            return false;
        }
    }

    protected static function get_current_date_persian()
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
