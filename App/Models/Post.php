<?php
namespace App\Models;
use App\Model;
class Post extends Model{

    public static function get_all_posts(){
        return static::select("posts");
    }

}