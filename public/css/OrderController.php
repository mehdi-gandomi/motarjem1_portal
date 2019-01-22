<?php
namespace App\Controllers;

use App\Config;
use App\Controller;
use App\Dependencies\Pay\Payment;
use App\Models\Order;
use Slim\Http\UploadedFile;

class OrderController extends Controller
{
    private $combos = array(
        'lang_type' => array(
            'en_to_fa' => "انگلیسی - فارسی",
            'fa_to_en' => "فارسی - انگلیسی",
        ),
        'translate_type' => array(
            'common' => "عمومی",
            'specialist' => 'تخصصی',
        ),
        'delivery_types' => array(
            'normal' => array(
                'icon' => 'walker.svg',
                'name' => 'عادی',
            ),
            'half_an_instant' => array(
                'icon' => 'running-man.svg',
                'name' => 'نیمه فوری',
            ),
            'instantaneous' => array(
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
        if (isset($getParams['words'])) {
            $data = array_merge($data, $getParams);
        }
        $this->view->render($res, "order.twig", $data);
    }
    public function save_order_info($req, $res, $args)
    {
        $postInfo = $req->getParsedBody();
        $orderData = Order::new ($postInfo);
        $priceInfo = $orderData['priceInfo'];
        $orderId = $orderData['orderId'];
        $buy_id = Order::new_buyer($postInfo, $orderId);
        if ($orderId && $buy_id) {
            $tokenArray = $this->get_csrf_token($req);
            $data = array(
                'success' => true,
                'translation_type' => $postInfo['type'] == "common" ? "عمومی" : "تخصصی",
                'translation_quality' => $postInfo['translation_quality'] == "silver" ? "نقره ای" : "طلایی",
                'page_number' => $priceInfo['pageNumber'],
                'duration' => $priceInfo['duration'],
                'final_price' => $priceInfo['price'],
                'order_id' => $orderId,

            );
            $this->view->render($res, "order-result.twig", \array_merge($data, $tokenArray));
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
                    $this->view->render($res, "redirect-page.twig", ['redirect_url' => "https://www.zarinpal.com/pg/StartPay/" . $result->Authority, 'message' => "در حال هدایت به درگاه زرین پال", "message_below" => "لطفا صبر کنید ..."]);
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
            'callback_url' => Config::BASE_URL . 'payment-success/' . $orderData['id'],
        ));
        $result = $payment->pay();
    }
    protected function zarinpal_payment($orderId, $gateway)
    {
        $orderData = Order::by_id($orderId);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $payment->set_info(array(
            'callback_url' => Config::BASE_URL . 'payment-success/' . $orderData['id'],
            'price' => $orderData['order_price'],
            'description' => 'خرید از وبسایت مترجم وان',
        ));
        $result = $payment->pay();
        return $result;

    }
    public function payment_result_zarinpal($req, $res, $args)
    {
        $queryParams = $req->getQueryParams();
        $orderId = $args['order_id'];
        $orderData = Order::by_id($orderId);
        $payment = new Payment();
        $payment->set_info(['price' => $orderData['order_price']]);
        $result = $payment->validate($queryParams['Authority']);

        if ($result->Status == 100) {
            $refId = $result->RefID;
            $updateResult = Order::update_by_id(array(
                'transactionscode' => $refId,
                'step' => 3,
            ), $orderId);
            if (!$updateResult) {
                return $this->view->render($res, "order-successful.twig", ['status' => false]);
            }
            $data = array(
                'contact_phone' => '٠٩٣٠٩٥٨٩١٢٢',
                'contact_email' => 'Motarjem1@yahoo.com',
                'order_id' => $orderId,
                'ref_id' => $refId,
                'words' => $orderData['word_number'],
                'page_number' => $orderData['page_number'],
                'translation_language' => $orderData['translation_lang'],
                'translation_quality' => $orderData['translation_kind'] == "silver" ? "نقره ای" : "طلایی",
                'order_price' => $orderData['order_price'],
                'customer_name' => $customerData['u_name'],
                'email' => $customerData['email'],
                'description' => $customerData['message'],

            );
            return $this->view->render($res, "order-successful.twig", $data);
            $this->send_invoice_to_email($customerData['email'], $orderData, $refId);
        } else {
            return $this->view->render($res, "order-successful.twig", ['status' => false, 'ref_id' => $result->RefID]);
        }
    }
    public function payment_result_mellat($req, $res, $args)
    {
        $postFields = $req->getParsedBody();
        $orderId = $args['order_id'];
        $orderData = Order::by_id($orderId);
        $customerData = Order::buyer_by_order_id($orderId);

        if ($postFields['ResCode'] == '0') {
            $payment = new Payment("mellat");
            $paymentResult = $payment->validate($postFields);

            if ($paymentResult['hasError']) {
                return $this->view->render($res, "order-successful.twig", ['status' => false, 'ref_id' => $postFields['SaleOrderId']]);
            } else {
                $updateResult = Order::update_by_id(array(
                    'transactionscode' => $postFields['SaleOrderId'],
                    'step' => 3,
                ), $orderId);
                $data = array(
                    'contact_phone' => '٠٩٣٠٩٥٨٩١٢٢',
                    'contact_email' => 'Motarjem1@yahoo.com',
                    'order_id' => $orderId,
                    'ref_id' => $postFields['SaleOrderId'],
                    'words' => $orderData['word_number'],
                    'page_number' => $orderData['page_number'],
                    'translation_language' => $orderData['translation_lang'],
                    'order_price' => $orderData['order_price'],
                    'customer_name' => $customerData['u_name'],
                    'email' => $customerData['email'],
                    'description' => $customerData['message'],

                );
                $this->view->render($res, "order-successful.twig", $data);
                $this->send_invoice_to_email($customerData['email'],$orderData,$postFields['SaleOrderId']);
            }
        } else {
            return $this->view->render($res, "order-successful.twig", ['status' => false, 'ref_id' => $postFields['ResCode']]);
        }

    }


    public function mellat_pay_test($req,$res,$args)
    {
        $orderId=$args['order_id'];
        $orderData = Order::by_id($orderId);
        $payment = new Payment();
        $payment->set_gateway("mellat");
        $payment->set_info(array(
            'order_id' => $orderId,
            'price' => "1000",
            'callback_url' => Config::BASE_URL . 'payment-success/' . $orderData['id'],
        ));
        $result = $payment->pay();
        
    }


    public function zarin_pay_test($req,$res,$args)
    {
        $orderId=$args['order_id'];
        $orderData = Order::by_id($orderId);
        $payment = new Payment();
        $payment->set_gateway("zarinpal");
        $payment->set_info(array(
            'callback_url' => Config::BASE_URL . 'payment-success/' . $orderData['id'],
            'price' => "100",
            'description' => 'خرید از وبسایت مترجم وان',
        ));
        $result = $payment->pay();
        $this->view->render($res, "redirect-page.twig", ['redirect_url' => "https://www.zarinpal.com/pg/StartPay/" . $result->Authority, 'message' => "در حال هدایت به درگاه زرین پال", "message_below" => "لطفا صبر کنید ..."]);
    }


    protected function send_invoice_to_email($email, $orderInfo, $refId)
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
                        <td>$orderData[translation_kind]</td>
                        <td>$orderData[word_number]</td>
                        <td>$orderData[translation_lang]</td>
                        <td>$orderData[page_number]</td>
                        <td>$orderData[order_price] تومان</td>
                        <td>$orderData[tarikh]</td>
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
            $filename = $this->moveUploadedFile($directory, $uploadedFile);
            $res->write($filename);
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
