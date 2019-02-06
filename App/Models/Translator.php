<?php
namespace App\Models;
use Core\Model;
class Translator extends Model{
    public static function by_username($username,$fields="*")
    {
        try{
            return static::select("translators",$fields,['username'=>$username],true);

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
    public static function check_existance_by_email($email)
    {
        try{
            $result=static::select("translators","id",['email'=>$email],true);
            return isset($result['id']);
        }catch(\Exception $e){
            return false;
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
            $date=jstrftime('%Y/%m/%e ,%A, %r',time()+60);
            $u_name = $postFields['fname'].' '.$postFields['lname'];
            // $ro = rand(9999,9999999);
            $password = rand(1000000 , 9999999);
            $username=$postFields['email'];
            $data=array(
                'u_name'=>$u_name,
                'email'=>$postFields['email'],
                'tell'=>$postFields['mobile'],
                'home_tell'=>$postFields['phone'],
                'username'=>md5(md5($username)),
                'password'=>md5(md5($password)),
                'level'=>"user",
                'active'=>0,
                'cv_year'=>$postFields['experience'],
                'sex'=>$postFields['sex'],
                'nation_code'=>$postFields['melli_code'],
                'address'=>$postFields['address'],
                'en_to_fa'=>isset($postFields['en_to_fa']) ? 1:0,
                'fa_to_en'=>isset($postFields['fa_to_en']) ? 1:0,
                'user_photo'=>$postFields['user_photo'],
                'nation_cart_photo'=>$postFields['meli_card'],
                'registration_date'=>$date
            );

            static::insert("translators",$data);
            return array(
                'username'=>$username,
                'password'=>$password
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
}