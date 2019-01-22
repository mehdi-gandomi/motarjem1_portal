<?php
namespace App\Models;
use App\Model;
use \PDO;
class User extends Model{
    public static function by_username($username,$fields="*")
    {
        try{
            return static::select("users",$fields,['username'=>$username],true);

        }catch(\Exception $e){
            return false;

        }

    }
    public static function activate($username)
    {
        try{
            return static::update("users",["is_active"=>1],"username='$username'");
            
        }catch(\Exception $e){
            return false;

        }

    }

    public static function check_user_existance($userData)
    {
        try{
            $db=static::getDB();
            $sql="SELECT user_id FROM users WHERE username='$userData[username]' OR email='$userData[email]'";
            return $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        }catch(\Exception $e){
            return false;

        }

    }
    public static function create($userData)
    {
        try{
            $now=new \DateTime("NOW");
            $year=$now->format("Y");
            $month=$now->format("m");
            $day=$now->format("d");
            $time=$now->format("H:i");
            $persianDate=gregorian_to_jalali($year,$month,$day);
            $userData['register_date_persian']=$persianDate[0]."/".$persianDate[1]."/".$persianDate[2]." ".$time;
            $userData['password']=\md5(\md5($userData['password']));
            $userData['is_active']=0;
            return static::insert("users",$userData);
            
        }catch(\Exception $e){
            var_dump($e);
            return false;

        }

    }
    

}