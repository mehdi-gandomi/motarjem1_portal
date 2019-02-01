<?php
namespace App\Controllers;

use Core\Config;
use Core\Controller;
use App\Dependencies\Pay\Payment;
use App\Models\Order;
use Slim\Http\UploadedFile;

class OrderController extends Controller
{
    private $combos = array(
        'lang_type' => array(
            '1' => "انگلیسی - فارسی",
            '2' => "فارسی - انگلیسی",
        ),
        'translate_type' => array(
            '1' => "عمومی",
            '2' => 'تخصصی',
        ),
        'delivery_types' => array(
            '1' => array(
                'icon' => 'walker.svg',
                'name' => 'عادی',
            ),
            '2' => array(
                'icon' => 'running-man.svg',
                'name' => 'نیمه فوری',
            ),
            '3' => array(
                'icon' => 'rocket-launch.svg',
                'name' => 'فوری',
            ),

        ),
        'field_types' => array(
            "90" => "ادبیات و زبان شناسی",
            "89" => "اسناد تجاری",
            "88" => "اقتصاد",
            "86" => "برق و الکترونیک",
            "91" => "تاریخ",
            "41" => "ترجمه کاتالوگ",
            "76" => "جغرافیا",
            "75" => "حسابداری",
            "74" => "حقوق",
            "70" => "روان شناسی",
            "71" => "ریاضی",
            "72" => "زمین شناسی و معدن",
            "43" => "زیرنویس فیلم",
            "73" => "زیست شناسی",
            "67" => "شیمی",
            "68" => "صنایع",
            "69" => "صنایع غذایی",
            "62" => "علوم اجتماعی",
            "63" => "علوم سیاسی",
            "64" => "عمران",
            "61" => "عمومی",
            "44" => "فایل صوتی تصویری",
            "57" => "فقه و علوم اسلامی",
            "58" => "فلسفه",
            "59" => "فناوری اطلاعات",
            "60" => "فیزیک",
            "50" => "متالورژی و مواد",
            "51" => "محیط زیست",
            "49" => "مدیریت",
            "54" => "منابع طبیعی و شیلات",
            "53" => "مکانیک",
            "47" => "نفت،گاز و پتروشیمی",
            "92" => "هنر و معماری",
            "46" => "ورزش و تربیت بدنی",
            "85" => "پزشکی",
            "93" => "ژنتیک و میکروبیولوژی",
            "55" => "کامپیوتر",
            "56" => "کشاورزی",
        ),
    );
    private $gateways = ['mellat', 'zarinpal'];
    private $payment;
    private $payment_gateway;

    public function get($req, $res, $args)
    {
        $getParams = $req->getQueryParams();
        $tokenArray = $this->get_csrf_token($req);
        $data = array_merge($this->combos, $tokenArray);
        $data['page_title']="سفارش ترجمه";
        if (isset($getParams['words'])) {
            $data = array_merge($data, $getParams);
        }
        $data=array_merge($data,array("latestPosts"=>$_SESSION["latestPosts"]));
        if(isset($_SESSION['is_user_logged_in']) || (isset($getParams['signup']) && $getParams['signup']=="false")){
            return $this->view->render($res, "website/order.twig", $data);
        }
        return $res->withRedirect("/order-method");
    }
    public function order_method_page($req,$res,$args)
    {
        return $this->view->render($res,"website/order-type-choice.twig",['latestPosts'=>$_SESSION['latestPosts']]);
    }
    public function save_order_info($req, $res, $args)
    {
        $postInfo = $req->getParsedBody();
        if(!isset($_SESSION['is_user_logged_in'])){
            $user_id=\App\Models\User::create([
                'username'=>$postInfo['email'],
                'password'=>$postInfo['phone_number'],
                'fname'=>explode(" ",$postInfo['fullname'])[0],
                'lname'=>explode(" ",$postInfo['fullname'])[1],
                'email'=>$postInfo['email'],
                'phone'=>$postInfo['phone_number']
            ]);
            $postInfo['orderer_id']=$user_id;
        }else{
            $postInfo['orderer_id']=$_SESSION['user_id'];
        }
        //TODO create order logs and insert it to database and of course i have to change user panel as well
        // creating a new order
        $orderData = Order::new($postInfo);
        $priceInfo = $orderData['priceInfo'];
        $orderId = $orderData['orderId'];
        //creating order logs
        $logResult=Order::new_order_log([
            'order_id'=>$orderId,
            'order_step'=>1
        ]);
        if ($orderId && $logResult) {
            $tokenArray = $this->get_csrf_token($req);
            $data = array(
                'success' => true,
                'translation_type' => $postInfo['type'] == "common" ? "عمومی" : "تخصصی",
                'translation_quality' => $postInfo['translation_quality'] == "silver" ? "نقره ای" : "طلایی",
                'page_number' => $priceInfo['pageNumber'],
                'duration' => $priceInfo['duration'],
                'final_price' => $priceInfo['price'],
                'order_id' => $orderId,
                'page_title'=>"پرداخت سفارش"
            );
            $data=\array_merge($data, $tokenArray);
            $data=array_merge($data,array("latestPosts"=>$_SESSION["latestPosts"]));
            $this->view->render($res, "website/order-result.twig",$data );
        }
    }

    public function order_payment($req, $res, $args)
    {
        $orderId = $args['order_id'];
        // $_SESSION['pending_order_id']=$orderId;
        $postFields = $req->getParsedBody();
        $this->payment_gateway = $postFields['gateway'];
        if (!in_array($this->payment_gateway, $this->gateways)) {
            return $res->write("خطایی در پرداخت رخ داد");
        }
        switch ($this->payment_gateway) {
            case "zarinpal":
                $result = $this->zarinpal_payment($orderId, $this->payment_gateway);
                if ($result->Status == 100) {
                    $this->view->render($res, "website/redirect-page.twig", ['redirect_url' => "https://www.zarinpal.com/pg/StartPay/" . $result->Authority, 'message' => "در حال هدایت به درگاه زرین پال", "message_below" => "لطفا صبر کنید ..."]);
                } else {
                    echo "خطایی رخ داد";
                }
                break;
            case "mellat":
                $result = $this->mellat_payment($orderId, $this->payment_gateway);
                break;
        }

    }
    protected function mellat_payment($orderId, $gateway)
    {
        $orderData = Order::by_id($orderId);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $orderPriceRial=\intval($orderData['order_price'])*10;
        $payment->set_info(array(
            'order_id' => $orderId,
            'price' => $orderPriceRial,
            'callback_url' => Config::BASE_URL . '/payment-success/' . $orderData['order_id'],
        ));
        $result = $payment->pay();
    }
    protected function zarinpal_payment($orderId, $gateway)
    {
        $orderData = Order::by_id($orderId);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $payment->set_info(array(
            'callback_url' => Config::BASE_URL . '/payment-success/' . $orderData['order_id'],
            'price' => $orderData['order_price'],
            'description' => 'خرید از وبسایت مترجم وان',
        ));
        $result = $payment->pay();
        return $result;

    }
    public function payment_result_zarinpal($req, $res, $args)
    {
        $queryParams = $req->getQueryParams();
        if($queryParams['Status']=="NOK"){
            
            return $this->view->render($res, "website/order-successful.twig", ['status' => false,"page_title"=>"خطا در پرداخت","latestPosts"=>$_SESSION["latestPosts"]]);
        }
        $orderId = $args['order_id'];
        $orderData = Order::by_id($orderId,false,true);
        $payment = new Payment();
        $payment->set_info(['price' => $orderData['order_price']]);
        $result = $payment->validate($queryParams['Authority']);

        if ($result->Status > 0) {
            $refId = $result->RefID;
            $updateResult = Order::update_order_log(array(
                'transactionscode' => $refId,
                'step' => 3,
            ), $orderId);
            // if (!$updateResult) {
            //     return $this->view->render($res, "order-successful.twig", ['status' => false]);
            // }
            
            $data = array(
                'contact_phone' => '٠٩٣٠٩٥٨٩١٢٢',
                'contact_email' => 'Motarjem1@yahoo.com',
                'order_id' => $orderId,
                'ref_id' => $refId,
                'words' => $orderData['word_numbers'],
                'page_number' => \ceil($orderData['word_numbers']/250),
                'translation_language' => $orderData['translation_lang']=="1" ? "انگلیسی به فارسی":"فارسی به انگلیسی",
                'translation_quality' => $orderData['translation_kind'] == "1" ? "نقره ای" : "طلایی",
                'order_price' => $orderData['order_price'],
                'customer_name' => $orderData['orderer_fname']." ".$orderData['orderer_lname'],
                'email' => $orderData['email'],
                'description' => $orderData['description'],
                'success'=>true,
                "page_title"=>"پرداخت موفق"
            );
            $this->send_invoice_to_email($orderData['email'], $orderData, $refId);
            $data=array_merge($data,array("latestPosts"=>$_SESSION["latestPosts"]));
            return $this->view->render($res, "website/order-successful.twig", $data);
            
        } else {
            
            return $this->view->render($res, "website/order-successful.twig", ['status' => false, 'ref_id' => $result->RefID,"page_title"=>"خطا در پرداخت","latestPosts"=>$_SESSION["latestPosts"]]);
        }
    }
    public function payment_result_mellat($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $orderId = $args['order_id'];
        $orderData = Order::by_id($orderId,false,true);
        if ($postFields['ResCode'] == '0') {
            $payment = new Payment("mellat");
            $paymentResult = $payment->validate($postFields);

            if ($paymentResult['hasError']) {
                
                return $this->view->render($res, "website/order-successful.twig", ['status' => false, 'ref_id' => $postFields['SaleOrderId'],"page_title"=>"خطا در پرداخت","latestPosts"=>$_SESSION["latestPosts"]]);
            } else {
                $updateResult = Order::update_order_log(array(
                    'transactionscode' => $postFields['SaleOrderId'],
                    'step' => 3,
                ), $orderId);
                $data = array(
                    'contact_phone' => '٠٩٣٠٩٥٨٩١٢٢',
                    'contact_email' => 'Motarjem1@yahoo.com',
                    'order_id' => $orderId,
     -               'ref_id' => $postFields['SaleOrderId'],
                    'words' => $orderData['word_numbers'],
                    'page_number' => \ceil($orderData['word_numbers']/250),
                    'translation_language' => $orderData['translation_lang']=="1" ? "انگلیسی به فارسی":"فارسی به انگلیسی",
                    'translation_quality' => $orderData['translation_kind'] == "1" ? "نقره ای" : "طلایی",
                    'order_price' => $orderData['order_price'],
                    'customer_name' => $orderData['orderer_fname']." ".$orderData['orderer_lname'],
                    'email' => $orderData['email'],
                    'description' => $orderData['description'],
                    'success'=>true,
                    "page_title"=>"پرداخت موفق"
                );
                $this->send_invoice_to_email($orderData['email'],$orderData,$postFields['SaleOrderId']);
                $data=array_merge($data,array("latestPosts"=>$_SESSION["latestPosts"]));
                return $this->view->render($res, "website/order-successful.twig", $data);
                
            }
        } else {
            return $this->view->render($res, "website/order-successful.twig", ['status' => false, 'ref_id' => $postFields['ResCode'],"page_title"=>"خطا در پرداخت"]);
        }

    }



    protected function send_invoice_to_email($email, $orderData, $refId)
    {

        $from = "support@motarjem1.com";
        $headers = "From:" . $from;
        $headers .= "Reply-To: noreply@motarjem1.com \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $subject = "مترجم وان / رسید سفارش";
        $quality=$orderData['translation_kind'];
        $words=$orderData['word_number'];
        $language=$orderData['translation_lang']=="english_to_farsi" ? "انگلیسی به فارسی":"فارسی به انگلیسی";
        $pages=$orderData['page_number'];
        $price=$orderData['order_price'];
        $date=$orderData['tarikh'];
        $msg = "
        <html>

        <head>
            <style>
                * {
                    direction: rtl;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    text-align: center;
                }

                table {
                    border: 1px solid rgba(0, 0, 0, 0.6);
                    margin: auto;
                }

                th,
                td {
                    border: 1px solid rgb(185, 173, 173);
                    margin: 0;
                    padding: 1rem;
                    font-size: 1rem;
                }

                p {
                    font-weight: bold;
                    color: #139213;
                    font-size: 1.2rem;
                }
            </style>
        </head>

        <body>
            <h3>رسید خرید شما</h3>

            <table>
                <thead>
                    <th>شماره پیگیری</th>
                    <th>کیفیت سفارش</th>
                    <th>تعداد کلمات</th>
                    <th>زبان ترجمه</th>
                    <th>تعداد صفحات</th>
                    <th>مبلغ کل سفارش</th>
                    <th>تاریخ ثبت سفارش</th>
                </thead>
                <tbody>
                    <tr>
                        <td>$refId</td>
                        <td>$quality</td>
                        <td>$words</td>
                        <td>$language</td>
                        <td>$pages</td>
                        <td>$price تومان</td>
                        <td>$date</td>
                    </tr>
                </tbody>
            </table>
            <p>ممنون از اینکه مترجم وان را انتخاب کردید !</p>

        </body>

        </html>";

        \mail($email, $subject, $msg, $headers);
    }

    public function upload_file($req, $res, $args)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        $directory = dirname(dirname(__DIR__)) . '/public/uploads/order';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            try{
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $res->write($filename);
            }catch(\Exception $e){
                $res->write("error while uploading file "+$e->geetMessage())->withStatus(500);
            }
        }else{
            $res->write($uploadedFile->getError())->withStatus(500);
        }
    }

    protected function moveUploadedFile($directory, UploadedFile $uploadedFile)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        $filename = sprintf('%s.%0.8s', $basename, $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }
}
