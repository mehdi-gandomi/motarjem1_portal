<?php
namespace App\Dependencies\Pay;

use App\Dependencies\CurlRequest;

class ZarinPal
{
    protected $info;
    protected $merchant_id = '49461c24-2d69-11e8-b721-000c295eb8fc';
    public function set_info($info)
    {
        $this->info = $info;
    }

    public function pay()
    {
        $request = new CurlRequest('https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json');
        $request->set_post_fields(array(
            'MerchantID' => $this->merchant_id,
            'Amount' => $this->info['price'],
            'CallbackURL' => $this->info['callback_url'],
            'Description' => $this->info['description'],
        ), true);
        $request->set_custom_options(array(
            CURLOPT_USERAGENT => 'ZarinPal Rest Api v1',
            CURLOPT_CUSTOMREQUEST => "POST",
        ));
        $result = $request->execute_and_parse_json();
        return $result;
    }
    public function validate($authority)
    {
        $request = new CurlRequest('https://www.zarinpal.com/pg/rest/WebGate/PaymentVerification.json');
        $request->set_post_fields(array(
            'MerchantID' => $this->merchant_id,
            'Authority' => $authority,
            'Amount' => $this->info['price'],
        ), true);
        $request->set_custom_options(array(
            CURLOPT_USERAGENT => 'ZarinPal Rest Api v1',
            CURLOPT_CUSTOMREQUEST => "POST",
        ));
        $result = $request->execute_and_parse_json();
        return $result;
    }

}
