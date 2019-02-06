<?php
namespace App\Models;
use Core\Model;
class Post extends Model{

    public static function get_all_posts(){
        return static::select("posts");
    }

}