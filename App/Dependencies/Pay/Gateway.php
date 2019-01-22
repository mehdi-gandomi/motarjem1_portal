<?php
namespace App\Dependencies\Pay;

interface Gateway{
    public function set_info($info);
    public function pay();
}