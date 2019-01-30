<?php
namespace App\Dependencies\Pay;
require __DIR__."/Soap/nusoap.php";

class Mellat{
    protected $info=array(
        'terminalId'=>"3669456",
        'userName'=>'motarajem1',
        'userPassword'=>'80818328'
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

    