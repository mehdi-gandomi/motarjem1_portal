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
            return $ticketNumber;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function create_reply($userId,$msgData,$state="waiting")
    {
        try{
            $persianDate=self::getCurrentDatePersian();
            $msgData['sent_date_persian']=$persianDate;
            $msgData['sender_id']=$userId;
            static::update("Tickets",[
                'update_date_persian'=>$persianDate,
                'update_date'=>date("Y-m-d H:i:s"),
                'state'=>$state
            ],"ticket_number='".$msgData['ticket_number']."'");
            static::insert("Ticket_Messages",$msgData);
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
                    if(is_array($filteringOptions['state']) && \count($filteringOptions['state'])>0){
                        $sql.=" AND state IN ('".implode("','",$filteringOptions['state'])."')";
                    }else if ($filteringOptions['state']==-1) {
                        $sql.=" AND state NOT IN ('answered','waiting') ";
                    }
                    
                }
                
            }
            $sql.=" ORDER BY update_date DESC LIMIT $page_limit,$amount";
            $stmt = $db->prepare($sql);
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
            $sql="SELECT COUNT(*) AS tickets_count FROM Tickets WHERE creator_id = :creator_id AND user_type = :user_type";
            if(is_array($filteringOptions) && count($filteringOptions)>0){
                if(isset($filteringOptions['state']) && \count($filteringOptions['state'])>0){
                    $sql.=" AND state IN (".implode(",",$filteringOptions['state']).")";
                }
            }
            $stmt = $db->prepare($sql);
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
            $sql=$userType=="1" ? $sql." AND state = 'answered'":$sql;
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
            return static::select("Ticket_Messages","*",['ticket_number'=>$ticketNumber],false,"ORDER BY sent_date DESC");
        }catch(\Exception $e){
            return false;
        }
    }
    //get full details including sender name by a ticket number
    public static function admin_get_details_by_ticket_number($ticketNumber,$userType='user')
    {
        try{
            $db=static::getDB();
            if($userType=="translator"){
                $sql="SELECT Tickets.ticket_number,Tickets.creator_id,Tickets.user_type,Tickets.subject,Tickets.create_date_persian,Tickets.update_date_persian,Tickets.state,translators.fname AS creator_fname,translators.lname AS creator_lname FROM `Tickets` INNER JOIN translators ON translators.translator_id=Tickets.creator_id WHERE Tickets.ticket_number=:ticket_number";
            }else{
                $sql="SELECT Tickets.ticket_number,Tickets.creator_id,Tickets.user_type,Tickets.subject,Tickets.create_date_persian,Tickets.update_date_persian,Tickets.state,users.fname AS creator_fname,users.lname AS creator_lname FROM `Tickets` INNER JOIN users ON users.user_id=Tickets.creator_id WHERE Tickets.ticket_number=:ticket_number";
            }
            $stmt=$db->prepare($sql);
            $stmt->bindParam(":ticket_number",$ticketNumber);
            return $stmt->execute() ? $stmt->fetch(PDO::FETCH_ASSOC):false;
        }catch(\Exception $e){
            return false;
        }
    }
    public static function set_as_read($ticketNumber)
    {
        try{
            return static::update("Tickets",["is_read"=>1],"ticket_number = '$ticketNumber'");
        }catch(\Exception $e){
            return false;
        }
    }
    //get all tickets that translators sent to admin
    public static function get_translator_tickets($page,$amount)
    {
        try{
            //user type 2 means translator and 1 means user(customer)
            $db=static::getDB();
            $page_limit = ($page - 1) * $amount;
            $sql="SELECT translators.fname AS creator_fname,translators.lname AS creator_lname,Tickets.ticket_number,Tickets.subject,Tickets.create_date_persian FROM Tickets INNER JOIN translators ON translators.translator_id=Tickets.creator_id WHERE Tickets.user_type='2' LIMIT $page_limit,$amount";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
        }catch(\Exception $e){
            return [];
        }
    }
    public static function get_customer_tickets($page,$amount)
    {
        try{
            //user type 2 means translator and 1 means user(customer)
            $db=static::getDB();
            $page_limit = ($page - 1) * $amount;
            $sql="SELECT users.fname AS creator_fname,users.lname AS creator_lname,Tickets.ticket_number,Tickets.subject,Tickets.create_date_persian FROM Tickets INNER JOIN users ON users.user_id=Tickets.creator_id WHERE Tickets.user_type='1' LIMIT $page_limit,$amount";
            $result=$db->query($sql);
            return $result ? $result->fetchAll(PDO::FETCH_ASSOC):[];
        }catch(\Exception $e){
            return [];
        }        
    }
}