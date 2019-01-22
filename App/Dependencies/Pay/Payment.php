<?php
namespace App\Dependencies\Pay;
use App\Dependencies\Pay\Mellat;
use App\Dependencies\Pay\ZarinPal;

class Payment{
    protected $gateway;
    public function __construct($gateway="zarinpal")
    {
        return $this->set_gateway($gateway);
    }
    public function set_gateway($gateway){
        if ($gateway=="zarinpal"){
            $this->gateway=new ZarinPal();
        }else if($gateway=="mellat"){
            $this->gateway=new Mellat();
        }else{
            return new \Exception("Wrong gateway!");
        }
    }
    public function set_info($info)
    {
        $this->gateway->set_info($info);
    }

    public function pay()
    {
        return $this->gateway->pay();
    }
    public function validate($authority)
    {
        return $this->gateway->validate($authority);
    }
}