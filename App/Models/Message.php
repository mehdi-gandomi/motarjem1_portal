<?php
namespace App\Models;
use Core\Model;
use PDO;
class Message extends Model{
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