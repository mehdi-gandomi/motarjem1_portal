<?php
namespace App\Models;

use Core\Model;
use PDO;

class Notification extends Model
{
    public static function get_global_notifications($page,$offset)
    {
        try{
            $db=static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql="SELECT * FROM notifications WHERE notif_type='0' LIMIT $page_limit,$offset";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):0;
        }catch(\Exception $e){
            return [];
        }
    }
    public static function get_global_notifications_count()
    {
        try{
            $db=static::getDB();
            $sql="SELECT COUNT(*) as notifications_count FROM notifications WHERE notif_type='0'";
            $result=$db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['notifications_count']:0;
        }catch(\Exception $e){
            return [];
        }
    }
    public static function get_private_notifications_by_user_id($userId,$page,$offset)
    {
        try{
            $db=static::getDB();
            $page_limit = ($page - 1) * $offset;
            $sql="SELECT notifications.notif_id,notifications.title,notifications.body,notifications.importance,notifications.attach_files,notifications.sent_date_persian FROM notif_translator INNER JOIN notifications ON notifications.notif_id=notif_translator.notif_id INNER JOIN translators ON notif_translator.translator_id=translators.translator_id WHERE notifications.notif_type='1' AND notif_translator.translator_id='$userId' LIMIT $page_limit,$offset";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
        }catch(\Exception $e){
            return [];
        }
    }
    public static function get_private_notifications_count_by_user_id($userId)
    {
        try{
            $db=static::getDB();
            $sql="SELECT COUNT(*) AS notifications_count FROM notif_translator INNER JOIN notifications ON notifications.notif_id=notif_translator.notif_id INNER JOIN translators ON notif_translator.translator_id=translators.translator_id WHERE notifications.notif_type='1' AND notif_translator.translator_id='$userId'";
            $result=$db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['notifications_count']:0;
        }catch(\Exception $e){
            return [];
        }
    }

    public static function get_data_by_id($notifId)
    {
        try{
            return static::select("notifications","*",['notif_id'=>$notifId],true);
        }catch(\Exception $e){
            return false;
        }
    }

    public static function delete_by_id($notifId)
    {
        try{
            static::delete("notifications","notif_id = '$notifId'");
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    public static function new($notification)
    {
        try{
            unset($notification['file']);
            $notification['sent_date_persian']=self::getCurrentDatePersian();
            if ($notification['notif_type']=="1"){
                $recipients=$notification['recipients'];
                unset($notification['recipients']);
                $notifId=static::insert("notifications",$notification);
                foreach ($recipients as $recipientId){
                    static::insert("notif_translator",['notif_id'=>$notifId,'translator_id'=>$recipientId]);
                }
                return true;
            }else{
                static::insert("notifications",$notification);
                return true;
            }


        }catch (\Exception $e){
            file_put_contents("err.txt",$e->getMessage());
            return false;
        }
    }

    protected static function getCurrentDatePersian()
    {
        $now = new \DateTime("NOW");
        $year = $now->format("Y");
        $month = $now->format("m");
        $day = $now->format("d");
        $time = $now->format("H:i");
        $persianDate = gregorian_to_jalali($year, $month, $day);
        return $persianDate[0] . "/" . $persianDate[1] . "/" . $persianDate[2] . " " . $time;
    }

    public static function get_data_with_recipients_by_id($notif_id)
    {
        try{
            $db=static::getDB();
            $sql="SELECT notifications.notif_id,notifications.title,notifications.importance,notifications.body,notifications.attach_files,GROUP_CONCAT(notif_translator.translator_id,',') AS recipients FROM `notif_translator` INNER JOIN notifications ON notif_translator.notif_id = notifications.notif_id INNER JOIN translators ON notif_translator.translator_id = translators.translator_id WHERE notif_translator.notif_id = '$notif_id' GROUP BY notifications.notif_id,notif_translator.notif_id";
            $result=$db->query($sql);
            if ($result){
                $result=$result->fetch(PDO::FETCH_ASSOC);
                $result['recipients']=explode(",",$result['recipients']);
                $result['recipients']=array_filter($result['recipients']);
                return $result;
            }
            return [];
        }catch (\Exception $e){
            return [];
        }
    }
}
