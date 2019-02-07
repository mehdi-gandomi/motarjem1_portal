<?php
namespace App\Dependencies\Pay;
require __DIR__."/Soap/nusoap.php";

class Mellat{
    protected $info=array(
        'terminalId'=>"3669456",
        'userName'=>'motarajem1',
        'userPassword'=>'80818328'
    );
    protected $error_codes=array(
        "11"=>"شماره کارت نامعتبر می باشد",
        "12"=>"ﻣﻮﺟﻮدی ﻛﺎﻓﻲ ﻧﻴﺴﺖ",
        "13"=>"رﻣﺰ ﻧﺎدرﺳﺖ اﺳﺖ",
        "14"=>"ﺗﻌﺪاد دﻓﻌﺎت وارد ﻛﺮدن رﻣﺰ ﺑﻴﺶ از ﺣﺪ ﻣﺠﺎز اﺳﺖ",
        "15"=>"ﻛﺎرت ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "16"=>"دﻓﻌﺎت ﺑﺮداﺷﺖ وﺟﻪ ﺑﻴﺶ از ﺣﺪ ﻣﺠﺎز اﺳﺖ",
        "17"=>"ﻛﺎرﺑﺮ از اﻧﺠﺎم ﺗﺮاﻛﻨﺶ ﻣﻨﺼﺮف ﺷﺪه اﺳﺖ",
        "18"=>"ﺗﺎرﻳﺦ اﻧﻘﻀﺎی ﻛﺎرت ﮔﺬﺷﺘﻪ اﺳﺖ",
        "19"=>"ﻣﺒﻠﻎ ﺑﺮداﺷﺖ وﺟﻪ ﺑﻴﺶ از ﺣﺪ ﻣﺠﺎز اﺳﺖ",
        "111"=>  "ﺻﺎدر ﻛﻨﻨﺪه ﻛﺎرت ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "112"=>"ﺧﻄﺎی ﺳﻮﻳﻴﭻ ﺻﺎدر ﻛﻨﻨﺪه ﻛﺎرت",
        "113"=>"ﭘﺎﺳﺨﻲ از ﺻﺎدر ﻛﻨﻨﺪه ﻛﺎرت درﻳﺎﻓﺖ ﻧﺸﺪ",
        "114"=>"دارﻧﺪه ﻛﺎرت ﻣﺠﺎز ﺑﻪ اﻧﺠﺎم اﻳﻦ ﺗﺮاﻛﻨﺶ ﻧﻴﺴﺖ",
        "21"=>"ﭘﺬﻳﺮﻧﺪه ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "23"=>"ﺧﻄﺎی اﻣﻨﻴﺘﻲ رخ داده اﺳﺖ",
        "24"=>"اﻃﻼﻋﺎت ﻛﺎرﺑﺮی ﭘﺬﻳﺮﻧﺪه ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "25"=>"ﻣﺒﻠﻎ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "31"=>"ﭘﺎﺳﺦ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "32"=>"ﻓﺮﻣﺖ اﻃﻼﻋﺎت وارد ﺷﺪه ﺻﺤﻴﺢ ﻧﻤﻲ ﺑﺎﺷﺪ",
        "33"=>"ﺣﺴﺎب ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "34"=>"ﺧﻄﺎی ﺳﻴﺴﺘﻤﻲ",
        "35"=>"ﺗﺎرﻳﺦ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "41"=>"ﺷﻤﺎره درﺧﻮاﺳﺖ ﺗﻜﺮاری اﺳﺖ",
        "412"=>"شناسه قبض نادرست است",
        "413"=>"ﺷﻨﺎﺳﻪ ﭘﺮداﺧﺖ ﻧﺎدرﺳﺖ اﺳﺖ",
        "414"=>"سازﻣﺎن ﺻﺎدر ﻛﻨﻨﺪه ﻗﺒﺾ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "415"=>"زﻣﺎن ﺟﻠﺴﻪ ﻛﺎری ﺑﻪ ﭘﺎﻳﺎن رسیده است",
        "416"=>"ﺧﻄﺎ در ﺛﺒﺖ اﻃﻼﻋﺎت",
        "417"=>"ﺷﻨﺎﺳﻪ ﭘﺮداﺧﺖ ﻛﻨﻨﺪه ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "418"=>"اﺷﻜﺎل در ﺗﻌﺮﻳﻒ اﻃﻼﻋﺎت ﻣﺸﺘﺮی",
        "419"=>"ﺗﻌﺪاد دﻓﻌﺎت ورود اﻃﻼﻋﺎت از ﺣﺪ ﻣﺠﺎز ﮔﺬﺷﺘﻪ اﺳﺖ",
        "421"=>"IP شما نامعتبر است",
        "51"=>"ﺗﺮاﻛﻨﺶ ﺗﻜﺮاری اﺳﺖ",
        "54"=>"ﺗﺮاﻛﻨﺶ ﻣﺮﺟﻊ ﻣﻮﺟﻮد ﻧﻴﺴﺖ",
        "55"=>"ﺗﺮاﻛﻨﺶ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ",
        "61"=>"ﺧﻄﺎ در واریز" 
    );
    public function set_info($info)
    {
        $this->info['orderId']=$info['order_id'];
        $this->info['amount']=$info['price'];
        $this->info['callBackUrl']=$info['callback_url'];
        $this->info['localDate']=date('Ymd');
        $this->info['localTime']=date('Gis');
        $this->info['additionalData']='';
        $this->info['payerId']=0;
    }

    public function pay()
    {
        $client = new \nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
        $namespace='http://interfaces.core.sw.bps.com/';
        $result=$client->call('bpPayRequest', $this->info, $namespace);
        var_dump($result);
        if ($client->fault)
        {
            return new \Exception ("There was a problem connecting to Bank");
            
        }
        else
        {
            $err = $client->getError();
            if ($err)
            {
                
                return new \Exception ("Error in Payment");
                
            }
            else
            {
                $res = explode (',',$result);
                $ResCode = $res[0];
                if ($ResCode == "0")
                {
                    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
                    header("Cache-Control: post-check=0, pre-check=0", false);
                    header("Pragma: no-cache");
                    echo '<form name="frmname" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="POST">
                            <input type="hidden" id="RefId" name="RefId" value="'. $res[1] .'">
                        </form>
                        <script type="text/javascript">window.onload = formSubmit; function formSubmit() { document.forms[0].submit(); }</script>';
                }
                else
                {
                
                    return new \Exception("Error on Loading Payment Page : ". $result);
                    
                }
            }
        }
    
    }


    public function validate($postData)
    {

        if($postData['ResCode'] != "0"){
            try{
                $error=$this->error_codes[$postData['ResCode']];
            }catch(\Exception $e){
                $error="خطایی در پرداخت هزینه رخ داد!";
            }
            return [
                "hasError"=>true,
                "error"=>$error
            ];
        }

        $client    = new \nusoap_client('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
        $namespace = 'http://interfaces.core.sw.bps.com/';
        $this->info['orderId']=$postData['SaleOrderId'];
        $this->info['saleOrderId']=$postData['SaleOrderId'];
        $this->info['saleReferenceId']=$postData['SaleReferenceId'];    
        $result= $client->call('bpVerifyRequest', $this->info, $namespace);
        $payResult=[];
        if($result==0){
            $result = $client->call('bpSettleRequest', $this->info, $namespace);
            if($result==0){
                $payResult['hasError']=false;
            }else {
                $client->call('bpReversalRequest', $this->info, $namespace);
                $payResult['hasError']=true;
                try{
                    $payResult['error']=$this->error_codes[$result];
                }catch(\Exception $e){
                    $payResult['error']="خطایی در پرداخت هزینه رخ داد!";
                }
            }
        }else{    
            $client->call('bpReversalRequest', $this->info, $namespace);
            $payResult['hasError']=true;
            try{
                $payResult['error']=$this->error_codes[$result];
            }catch(\Exception $e){
                $payResult['error']="خطایی در پرداخت هزینه رخ داد!";
            }
        }

        return $payResult;
    }
}

    