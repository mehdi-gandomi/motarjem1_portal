<?php
namespace App\Models;
use Core\Model;
use PDO;
class Ticket extends Model{
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
    public static function create($userId,$userType,$ticketData)
    {
        try{
            $persianDate=self::getCurrentDatePersian();
            $ticketNumber=bin2hex(random_bytes(3));
            $result=static::insert("Tickets",[
                'ticket_number'=>$ticketNumber,
                'creator_id'=>$userId,
                'user_type'=>$userType,
                'subject'=>$ticketData['subject'],
                'create_date_persian'=>$persianDate,
                'update_date_persian'=>$persianDate,
                'state'=>"waiting"
            ]);
            if($result){
                $ticketData['ticket_number']=$ticketNumber;
                $ticketData['sent_date_persian']=$persianDate;
                $ticketData['sender_id']=$userId;
                static::insert("Ticket_Messages",$ticketData);
            }
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
    //this function gets  user messages by id
    public static function get_tickets_by_user_id($userId,$userType,$page, $amount,$filteringOptions=null)
    {
        try{
            $db=static::getDB();
            $result=false;
            $page_limit = ($page - 1) * $amount;
            $sql="SELECT * FROM Tickets WHERE creator_id = :creator_id AND user_type = :user_type";
            if(is_array($filteringOptions) && count($filteringOptions)>0){
                if(isset($filteringOptions['state'])){
                    $sql.=" AND state IN (".implode(",",$filteringOptions['state']).")";
                }
                if(isset($filteringOptions['read'])){
                    $sql.=" AND is_read IN (".implode(",",$filteringOptions['read']).")";
                }
                
            }
            $sql.=" ORDER BY update_date DESC LIMIT $page_limit,$amount";
            $stmt = $db->prepare($sql);
            var_dump($sql);
            return $stmt->execute(['creator_id'=>$userId,'user_type'=>$userType]) ? $stmt->fetchAll(PDO::FETCH_ASSOC) : false;
        }catch(\Exception $e){
            return false;
        }

        
    }
    //this function gets  user messages count by user id
    public static function get_tickets_count_by_user_id($userId,$userType,$filteringOptions)
    {
        try{
            $db=static::getDB();
            $result=false;
            $page_limit = ($page - 1) * $amount;
            $sql="SELECT COUNT(*) AS tickets_count FROM Tickets WHERE creator_id = :creator_id AND user_type = :user_type";
            if(is_array($filteringOptions) && count($filteringOptions)>0){
                if(isset($filteringOptions['state'])){
                    $sql.=" AND state IN (".implode(",",$filteringOptions['state']).")";
                }
                if(isset($filteringOptions['read'])){
                    $sql.=" AND is_read IN (".implode(",",$filteringOptions['read']).")";
                }
                
            }
            $stmt = $db->prepare($sql);
            var_dump($sql);
            return $stmt->execute(['creator_id'=>$userId,'user_type'=>$userType]) ? $stmt->fetch(PDO::FETCH_ASSOC)['tickets_count'] : 0;
        }catch(\Exception $e){
            return false;
        }
    }
    //this function gets unread messages by user id
    public static function get_unread_tickets_count_by_user_id($userId,$userType)
    {
        try {
            $db = static::getDB();
            $sql = "SELECT COUNT(*) AS tickets_count FROM `Tickets` WHERE `creator_id`= '$userId' AND user_type='$userType' AND `is_read`= '0'";
            $result = $db->query($sql);
            return $result ? $result->fetch(PDO::FETCH_ASSOC)['tickets_count'] : false;

        } catch (\Exception $e) {
            return false;
        }
    }


    public static function get_details_by_ticket_number($ticketNumber)
    {
        try{
            return static::select("Tickets","*",['ticket_number'=>$ticketNumber],true);
        }catch(\Exception $e){
            return false;
        }
    }
    public static function get_ticket_messages_by_ticket_number($ticketNumber)
    {
        try{
            return static::select("Ticket_Messages","*",['ticket_number'=>$ticketNumber]);
        }catch(\Exception $e){
            return false;
        }
    }
    public static function set_as_read($ticketNumber)
    {
        try{
            return static::update("Tickets",["state"=>'read'],"ticket_nuber = '$ticketNumber'");
        }catch(\Exception $e){
            return false;
        }
    }
}