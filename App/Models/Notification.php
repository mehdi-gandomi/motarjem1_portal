<?php
namespace App\Models;

use Core\Model;
use PDO;

class Order extends Model
{
    public function get_all()
    {
        try{
            return static::select("notifications");
        }catch(\Exception $e){

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
