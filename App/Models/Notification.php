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
}
