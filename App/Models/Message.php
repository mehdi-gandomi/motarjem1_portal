
<?php
namespace App\Models;
use Core\Model;
use PDO;
class Message extends Model{
    public static function getCurrentDatePersian()
    {
        $now = new \DateTime("NOW");
        $year = $now->format("Y");
        $month = $now->format("m");
        $day = $now->format("d");
        $time = $now->format("H:i");
        $persianDate = gregorian_to_jalali($year, $month, $day);
        return  $persianDate[0] . "/" . $persianDate[1] . "/" . $persianDate[2] . " " . $time;
    }
    public static function create($userId,$msgData)
    {
        try{
            $persianDate=self::getCurrentDatePersian();
            $msgData['create_date_persian']=$persianDate;
            $msgData['update_date_persian']=$persianDate;
            $msgData['user_type']=1;
            $msgData['reciever_id']=0;
            $msgData['sender_id']=$userId;
            static::insert("messaging",$msgData);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function create_reply($userId,$msgData)
    {
        try{
            $persianDate=self::getCurrentDatePersian();
            $msgData['parent_msg_id']=$msgData['msg_id'];
            unset($msgData['msg_id']);
            $msgData['create_date_persian']=$persianDate;
            $msgData['update_date_persian']=$persianDate;
            $msgData['user_type']=1;
            $msgData['reciever_id']=0;
            $msgData['sender_id']=$userId;
            static::insert("messaging",$msgData);
            return true;
        }catch(\Exception $e){
            return false;
        }
    }
    
    public static function get_details_by_id($msgId)
    {
        try{
            $db=static::getDB();
            $sql="SELECT messaging.msg_id,messaging.subject,messaging.body,sender_id,reciever_id,messaging.parent_msg_id,messaging.create_date_persian,messaging.update_date_persian FROM messaging WHERE messaging.msg_id = :msg_id OR messaging.parent_msg_id = :parent_msg_id  ORDER BY messaging.update_date DESC";
            $stmt=$db->prepare($sql);
            $stmt->bindParam(":parent_msg_id",$msgId);
            $stmt->bindParam(":msg_id",$msgId);
            return $stmt->execute() ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function set_message_reply_as_read($msgId)
    {
        try{
            return static::update("messaging",["is_read"=>1],"parent_msg_id = '$msgId'");
        }catch(\Exception $e){
            return false;
        }
    }
}