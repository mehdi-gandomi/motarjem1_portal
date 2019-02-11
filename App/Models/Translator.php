<?php
namespace App\Models;
use Core\Model;
use \PDO;
class Translator extends Model{
    public static function by_username($username,$fields="*")
    {
        try{
            return static::select("translators",$fields,['username'=>$username],true);

        }catch(\Exception $e){
            return false;
        }

    }
    public static function by_email($email,$fields="*")
    {
        try{
            return static::select("translators",$fields,['email'=>$email],true);

        }catch(\Exception $e){
            return false;
        }

    }
    public static function by_id($translatorId,$fields="*")
    {
        try{
            return static::select("translators",$fields,['translator_id'=>$translatorId],true);

        }catch(\Exception $e){
            return false;
        }

    }
    public static function check_existance($postFields)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT username FROM translators WHERE username='".$postFields['username']."' OR email='".$postFields['email']."'";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return true;

        }

    }
    public static function login_check($username,$password)
    {
        try{
            $userData=self::by_username($username,"u_name,password");
        }catch(\Exception $e){
            return false;
        }
    }
    public static function new($postFields)
    {
        try{
            unset($postFields['confirm_pass']);
            unset($postFields['captcha_input']);
            unset($postFields['csrf_name']);
            unset($postFields['csrf_value']);
            $postFields['register_date_persian'] = self::get_current_date_persian();
            $postFields['password'] = \md5(\md5($postFields['password']));
            $postFields['is_active'] = 0;
            static::insert("translators",$postFields);
            return array(
                'username'=>$postFields['username'],
                'password'=>$postFields['password']
            );

        }catch(\Exception $e){
            return false;
        }
    }
    public static function login($username,$password)
    {

        $username = \str_replace("'", "", $username);
        $username = \str_replace("`", "", $username);
        $username = \str_replace("#", "", $username);
        $username = \str_replace("&", "", $username);
        $username = \strtolower($username);

        $password = \htmlspecialchars($password);
        $password = \str_replace("'", "", $password);
        $password = \str_replace("`", "", $password);
        $password = \str_replace("#", "", $password);
        $password = \str_replace("&", "", $password);
        $password = \strtolower($password);
    ////////////
        $username = \md5(\md5($username));
        $password = \md5(\md5($password));	
        
         $userData=self::by_username($username,"u_name,password,id,photo,level,active");
         
         if (!$userData) {
            return array(
                'hasError'=>true,
                'error'=>'نام کاربری وارد شده صحیح نمی باشد'
            );
        }
        if ($userData['password']==$password) {
            if($userData['active'] == "0" || $userData['active'] == 0)
            {
                return array(
                    'hasError'=>true,
                    'error'=>'شما به این صفحه دسترسی ندارید! لطفا با ما تماس بگیرید'
                );
            }
            $userid = $userData['id'];
            $user_agent = $_SERVER['REMOTE_ADDR'];
            /** Secound Session For athour **/
            $_SESSION['name'] = $userData['u_name'];
            $_SESSION['userid'] = $userid;
            $_SESSION['photo'] = $userData['photo'];
            if($userData['level']=="admin")
            {
                $_SESSION['LogedIn'] = $user_agent;   
            }else
            {
                $_SESSION['userLogedIn'] = $user_agent;
            }
            \setcookie(\session_name(), \session_id(), time() + (86400 * 7));
            return array(
                'hasError'=>false,
                'level'=>$userData['level'],
                'u_name'=>$userData['u_name']
            );    

        } else {
            return array(
                'hasError'=>true,
                'error'=>"پسورد وارد شده اشتباه می باشد"
            );
            
        }

    }
    public static function get_translator_data_by_id($id,$fields="*")
    {
        try{
            return static::select("translators",$fields,['translator_id'=>$id],true);
        }catch(\Exception $e){
            return false;
        }
    }
    protected static function get_current_date_persian(){
        $now = new \DateTime("NOW");
        $year = $now->format("Y");
        $month = $now->format("m");
        $day = $now->format("d");
        $time = $now->format("H:i");
        $persianDate = gregorian_to_jalali($year, $month, $day);
        return $persianDate[0] . "/" . $persianDate[1] . "/" . $persianDate[2] . " " . $time;
    }
}