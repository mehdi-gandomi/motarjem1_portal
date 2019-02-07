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
        "ﺷﻤﺎره ﻛﺎرت ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "11",
        "ﻣﻮﺟﻮدی ﻛﺎﻓﻲ ﻧﻴﺴﺖ" <= "12",
        "رﻣﺰ ﻧﺎدرﺳﺖ اﺳﺖ" <= "13",
        "ﺗﻌﺪاد دﻓﻌﺎت وارد ﻛﺮدن رﻣﺰ ﺑﻴﺶ از ﺣﺪ ﻣﺠﺎز اﺳﺖ" <= "14",
        "ﻛﺎرت ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "15",
        "دﻓﻌﺎت ﺑﺮداﺷﺖ وﺟﻪ ﺑﻴﺶ از ﺣﺪ ﻣﺠﺎز اﺳﺖ" <= "16",
        "ﻛﺎرﺑﺮ از اﻧﺠﺎم ﺗﺮاﻛﻨﺶ ﻣﻨﺼﺮف ﺷﺪه اﺳﺖ" <= "17",
        "ﺗﺎرﻳﺦ اﻧﻘﻀﺎی ﻛﺎرت ﮔﺬﺷﺘﻪ اﺳﺖ" <= "18",
        "ﻣﺒﻠﻎ ﺑﺮداﺷﺖ وﺟﻪ ﺑﻴﺶ از ﺣﺪ ﻣﺠﺎز اﺳﺖ" <= "19",
        "ﺻﺎدر ﻛﻨﻨﺪه ﻛﺎرت ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "111",
        "ﺧﻄﺎی ﺳﻮﻳﻴﭻ ﺻﺎدر ﻛﻨﻨﺪه ﻛﺎرت" <= "112",
        "ﭘﺎﺳﺨﻲ از ﺻﺎدر ﻛﻨﻨﺪه ﻛﺎرت درﻳﺎﻓﺖ ﻧﺸﺪ" <= "113",
        "دارﻧﺪه ﻛﺎرت ﻣﺠﺎز ﺑﻪ اﻧﺠﺎم اﻳﻦ ﺗﺮاﻛﻨﺶ ﻧﻴﺴﺖ" <= "114",
        "ﭘﺬﻳﺮﻧﺪه ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "21",
        "ﺧﻄﺎی اﻣﻨﻴﺘﻲ رخ داده اﺳﺖ" <= "23",
        "اﻃﻼﻋﺎت ﻛﺎرﺑﺮی ﭘﺬﻳﺮﻧﺪه ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "24",
        "ﻣﺒﻠﻎ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "25",
        "ﭘﺎﺳﺦ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "31",
        "ﻓﺮﻣﺖ اﻃﻼﻋﺎت وارد ﺷﺪه ﺻﺤﻴﺢ ﻧﻤﻲ ﺑﺎﺷﺪ" <= "32",
        "ﺣﺴﺎب ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "33",
        "ﺧﻄﺎی ﺳﻴﺴﺘﻤﻲ" <= "34",
        "ﺗﺎرﻳﺦ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "35",
        "ﺷﻤﺎره درﺧﻮاﺳﺖ ﺗﻜﺮاری اﺳﺖ" <= "41",
        "شناسه قبض نادرست است" <= "412",
        "ﺷﻨﺎﺳﻪ ﭘﺮداﺧﺖ ﻧﺎدرﺳﺖ اﺳﺖ" <= "413",
        "سازﻣﺎن ﺻﺎدر ﻛﻨﻨﺪه ﻗﺒﺾ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "414",
        "زﻣﺎن ﺟﻠﺴﻪ ﻛﺎری ﺑﻪ ﭘﺎﻳﺎن رسیده است" <= "415",
        "ﺧﻄﺎ در ﺛﺒﺖ اﻃﻼﻋﺎت" <= "416",
        "ﺷﻨﺎﺳﻪ ﭘﺮداﺧﺖ ﻛﻨﻨﺪه ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "417",
        "اﺷﻜﺎل در ﺗﻌﺮﻳﻒ اﻃﻼﻋﺎت ﻣﺸﺘﺮی" <= "418",
        "ﺗﻌﺪاد دﻓﻌﺎت ورود اﻃﻼﻋﺎت از ﺣﺪ ﻣﺠﺎز ﮔﺬﺷﺘﻪ اﺳﺖ" <= "419",
        "IP نامعتبر است" <= "421",
        "ﺗﺮاﻛﻨﺶ ﺗﻜﺮاری اﺳﺖ" <= "51",
        "ﺗﺮاﻛﻨﺶ ﻣﺮﺟﻊ ﻣﻮﺟﻮد ﻧﻴﺴﺖ" <= "54",
        "ﺗﺮاﻛﻨﺶ ﻧﺎﻣﻌﺘﺒﺮ اﺳﺖ" <= "55",
        "ﺧﻄﺎ در واریز" <= "61"  
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
                $payResult['error']=$result;
            }
        }else{    
            $client->call('bpReversalRequest', $this->info, $namespace);
            $payResult['hasError']=true;
            $payResult['error']=$result;
        }

        return $payResult;
    }
}

    