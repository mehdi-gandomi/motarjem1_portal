<?php
namespace App\Controllers;

use App\Dependencies\Pay\Payment;
use App\Models\Order;
use App\Models\User;
use Core\Config;
use Core\Controller;

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
        
    );
    private $gateways = ['mellat', 'zarinpal'];
    private $payment;
    private $payment_gateway;
    public function get($req, $res, $args)
    {
        $this->combos['field_types']=Order::get_study_fields();
        $getParams = $req->getQueryParams();
        $tokenArray = $this->get_csrf_token($req);
        $data = array_merge($this->combos, $tokenArray);
        $data['page_title'] = "سفارش ترجمه";
        if (isset($getParams['words'])) {
            $data = array_merge($data, $getParams);
        }
        $data = array_merge($data, array("latestPosts" => $_SESSION["latestPosts"]));
        if (isset($_SESSION['is_user_logged_in']) || (isset($getParams['signup']) && $getParams['signup'] == "false")) {
            return $this->view->render($res, "website/order.twig", $data);
        }
        return $res->withRedirect("/order-method");
    }
    public function order_method_page($req, $res, $args)
    {
        return $this->view->render($res, "website/order-type-choice.twig", ['latestPosts' => $_SESSION['latestPosts']]);
    }
    public function save_order_info($req, $res, $args)
    {
        $postInfo = $req->getParsedBody();
        if (!isset($postInfo['order_files']) && $postInfo['order_files'] == ""){
            return $res->withRedirect("/order");
        }
        if (!isset($_SESSION['is_user_logged_in'])) {
            $exists = User::check_user_existance(['email' => $postInfo['email'], 'username' => $postInfo['email']]);
            if ($exists) {
              $user_id=$exists['user_id'];
            } else {
                $user_id = \App\Models\User::create([
                    'username' => $postInfo['email'],
                    'password' => $postInfo['phone_number'],
                    'fname' => explode(" ", $postInfo['fullname'])[0],
                    'lname' => explode(" ", $postInfo['fullname'])[1],
                    'email' => $postInfo['email'],
                    'phone' => $postInfo['phone_number'],
                ]);
            }
            $postInfo['orderer_id'] = $user_id;
        } else {
            $postInfo['orderer_id'] = $_SESSION['user_id'];
        }

        // creating a new order
        $orderData = Order::new ($postInfo);
        $priceInfo = $orderData['priceInfo'];
        $orderNumber = $orderData['orderNumber'];
        //creating order logs
        $logResult = Order::new_order_log($orderNumber,['order_step'=>1]);
        if ($orderNumber && $logResult) {
            $tokenArray = $this->get_csrf_token($req);
            $data = array(
                'success' => true,
                'translation_type' => $postInfo['type'] == "1" ? "عمومی" : "تخصصی",
                'translation_quality' => $postInfo['translation_quality'] == "5" ? "نقره ای" : "طلایی",
                'page_number' => $priceInfo['pageNumber'],
                'duration' => $priceInfo['duration'],
                'final_price' => $priceInfo['price'],
                'price_with_discount'=>$priceInfo['priceWithDiscount'],
                'order_id' => $orderNumber,
                'page_title' => "پرداخت سفارش",
            );
            $data = \array_merge($data, $tokenArray);
            $data = array_merge($data, array("latestPosts" => $_SESSION["latestPosts"]));
            $this->view->render($res, "website/order-result.twig", $data);
        }
    }

    public function order_payment($req, $res, $args)
    {
        $orderNumber = $args['order_number'];
        // $_SESSION['pending_order_id']=$orderId;
        $postFields = $req->getParsedBody();
        $this->payment_gateway = $postFields['gateway'];
        if (!in_array($this->payment_gateway, $this->gateways)) {
            return $res->write("خطایی در پرداخت رخ داد");
        }
        switch ($this->payment_gateway) {
            case "zarinpal":
                $result = $this->zarinpal_payment($orderNumber, $this->payment_gateway);
                if ($result->Status == 100) {
                    $this->view->render($res, "website/redirect-page.twig", ['redirect_url' => "https://www.zarinpal.com/pg/StartPay/" . $result->Authority, 'message' => "در حال هدایت به درگاه زرین پال", "message_below" => "لطفا صبر کنید ..."]);
                } else {
                    echo "خطایی رخ داد";
                    var_dump($result);
                }
                break;
            case "mellat":
                $result = $this->mellat_payment($orderNumber, $this->payment_gateway);
                break;
        }

    }
    protected function mellat_payment($orderNumber, $gateway)
    {
        $orderData = Order::by_number($orderNumber);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $orderPriceRial = \intval($orderData['order_price']) * 10;
        $payment->set_info(array(
            'order_id' => $orderData['order_id'],
            'price' => $orderPriceRial,
            'callback_url' => Config::BASE_URL . '/payment-success/' . $orderData['order_number'],
        ));
        $result = $payment->pay();
    }
    protected function zarinpal_payment($orderNumber, $gateway)
    {
        $orderData = Order::by_number($orderNumber);
        $payment = new Payment();
        $payment->set_gateway($gateway);
        $payment->set_info(array(
            'callback_url' => Config::BASE_URL . '/payment-success/' . $orderData['order_number'],
            'price' => $orderData['order_price'],
            'description' => 'خرید از وبسایت مترجم وان',
        ));
        $result = $payment->pay();
        return $result;

    }
    public function payment_result_zarinpal($req, $res, $args)
    {
        $queryParams = $req->getQueryParams();
        if ($queryParams['Status'] == "NOK") {
            return $this->view->render($res, "website/order-successful.twig", ['status' => false, 'refId' => "دریافت نشد!", "page_title" => "خطا در پرداخت", 'error' => "پرداخت انجام نشد", "latestPosts" => $_SESSION["latestPosts"]]);
        }
        $orderNumber = $args['order_number'];
        $orderData = Order::by_number($orderNumber, false, true);
        $payment = new Payment();
        $payment->set_info(['price' => $orderData['order_price']]);
        $result = $payment->validate($queryParams['Authority']);
        if ($result['Status'] > 0) {
            $refId = $result['RefID'];
            $updateResult = Order::update_order_log(array(
                'transaction_code' => $refId,
                'order_step' => 2,
            ), $orderNumber);
            // if (!$updateResult) {
            //     return $this->view->render($res, "order-successful.twig", ['status' => false]);
            // }

            $data = array(
                'contact_phone' => '٠٩٣٠٩٥٨٩١٢٢',
                'contact_email' => 'Motarjem1@yahoo.com',
                'order_id' => $orderNumber,
                'refId' => $refId,
                'words' => $orderData['word_numbers'],
                'page_number' => \ceil($orderData['word_numbers'] / 250),
                'translation_language' => $orderData['translation_lang'] == "1" ? "انگلیسی به فارسی" : "فارسی به انگلیسی",
                'translation_quality' => $orderData['translation_kind'] == "1" ? "نقره ای" : "طلایی",
                'order_price' => $orderData['order_price'],
                'customer_name' => $orderData['orderer_fname'] . " " . $orderData['orderer_lname'],
                'email' => $orderData['email'],
                'description' => $orderData['description'],
                'success' => true,
                "page_title" => "پرداخت موفق",
            );
            $this->send_invoice_to_email($orderData['email'], $orderData, $refId);
            $data = array_merge($data, array("latestPosts" => $_SESSION["latestPosts"]));
            return $this->view->render($res, "website/order-successful.twig", $data);

        } else {

            return $this->view->render($res, "website/order-successful.twig", ['status' => false, 'refId' => $result['RefID'], 'error' => "خطایی در پرداخت رخ داد", "page_title" => "خطا در پرداخت", "latestPosts" => $_SESSION["latestPosts"]]);
        }
    }
    public function payment_result_mellat($req, $res, $args)
    {
        $orderNumber = $args['order_number'];
        $orderData = Order::by_number($orderNumber, false, true);
        $payment = new Payment("mellat");
        $paymentResult = $payment->validate($_POST);
        $refId = $_POST['RefId'];
        if ($paymentResult['hasError']) {
            return $this->view->render($res, "website/order-successful.twig", ['status' => false, 'refId' => $refId, "page_title" => "خطا در پرداخت", "latestPosts" => $_SESSION["latestPosts"], "error" => $paymentResult['error']]);
        } else {
            $updateResult = Order::update_order_log(array(
                'transaction_code' => $refId,
                'order_step' => 2,
            ), $orderNumber);
            $data = array(
                'contact_phone' => '٠٩٣٠٩٥٨٩١٢٢',
                'contact_email' => 'Motarjem1@yahoo.com',
                'order_id' => $orderNumber,
                'refId' => $refId,
                'words' => $orderData['word_numbers'],
                'page_number' => \ceil($orderData['word_numbers'] / 250),
                'translation_language' => $orderData['translation_lang'] == 1 ? "انگلیسی به فارسی" : "فارسی به انگلیسی",
                'translation_quality' => $orderData['translation_kind'] == 1 ? "نقره ای" : "طلایی",
                'order_price' => $orderData['order_price'],
                'customer_name' => $orderData['orderer_fname'] . " " . $orderData['orderer_lname'],
                'email' => $orderData['email'],
                'description' => $orderData['description'],
                'success' => true,
                "page_title" => "پرداخت موفق",
            );
            $this->send_invoice_to_email($orderData['email'], $orderData, $refId);
            $data = array_merge($data, array("latestPosts" => $_SESSION["latestPosts"]));
            return $this->view->render($res, "website/order-successful.twig", $data);

        }

    }

    public function validate_coupon_code($req,$res,$args)
    {
        $couponCode=$req->getParam("coupon_code");
        $result=\Core\Model::select("coupons","*",['coupon_code'=>$couponCode],true);
        if ($result){
            return $res->withJson(['valid'=>true,'info'=>$result]);
        }
        return $res->withJson(['valid'=>false]);
    }
    protected function send_invoice_to_email($email, $orderData, $refId)
    {

        $from = "support@motarjem1.com";
        $headers = "From:" . $from;
        $headers .= "Reply-To: noreply@motarjem1.com \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $orderNumber=$orderData['order_number'];
        $subject = "مترجم وان / رسید سفارش";
        $quality = $orderData['translation_quality'] == 5 ? "نقره ای" : "طلایی";
        $word_numbers = $orderData['word_numbers'];
        $language = $orderData['translation_lang'] == 1 ? "انگلیسی به فارسی" : "فارسی به انگلیسی";
        $page_number = \ceil($orderData['word_numbers'] / 250);
        $price = $orderData['order_price'];
        $date = $orderData['order_date_persian'];
        $fullname = $orderData['orderer_fname'] . " " . $orderData['orderer_lname'];
        $msg = "
<!DOCTYPE html>
<html>
  <head>
    <meta charset='َUTF-8'>
    <style type='text/css'>
      .order {
        border: 1px solid #eee;
        border-collapse: collapse;
        margin: 1.5rem 0;
      }
      .order td,
      th {
        border: 2px solid #d8cdcd;
        border-spacing: 0;
        margin: 0;
        padding: 1rem;
      }
    </style>
    
  </head>
  <body
    style='margin:0;padding:0;font-family: Vazir,tahoma, DejaVu Sans, helvetica, arial, freesans, sans-serif;'
  >
    <div
      style='width:100%!important;min-width:300px;height:100%;margin:0;padding:0;line-height:1.5;color:#333;background-color:#f2f2f2'
    >
      <table style='width:100%;padding:30px 0 0 0'>
        <tbody>
          <tr>
            <td align='center'>
              <img
                src='http://motarjem1.com/public/images/logo.png'
                class='CToWUd'
              />
            </td>
          </tr>
        </tbody>
      </table>
      <table
        style='padding:5px;width:100%;max-width:620px;margin:0 auto;color:#515151'
      >
        <tbody>
          <tr>
            <td>
              <table style='width:100%;margin:0;padding:0 0 20px'>
                <tbody>
                  <tr style='margin:0;padding:0'>
                    <td style='margin:0;padding:0'>
                      <table
                        style='width:100%;max-width:620px;padding:30px;margin:20px auto 5px;background-color:#fff;border-radius:4px;text-align:right'
                      >
                        <tbody>
                          <tr>
                            <td style='font-weight:bold;font-size:0.85rem;'>
                              خانم/آقای $fullname
                            </td>
                          </tr>
                          <tr>
                            <td style='color:#738598;font-weight:bold'>
                              سفارش شما با موفقیت ثبت شد و در اسرع وقت به آن
                              رسیدگی خواهد شد
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <table class='order'>
                                <thead>
                                  <tr>
                                    <th>شماره سفارش</th>
                                    <th>شماره پیگیری</th>
                                    <th>کیفیت <span class='il'>سفارش</span></th>
                                    <th>تعداد کلمات</th>
                                    <th>زبان ترجمه</th>
                                    <th>تعداد صفحات</th>
                                    <th>
                                      مبلغ کل <span class='il'>سفارش</span>
                                    </th>
                                    <th>
                                      تاریخ ثبت <span class='il'>سفارش</span>
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td>$orderNumber</td>
                                    <td>$refId</td>
                                    <td>$quality</td>
                                    <td>$word_numbers</td>
                                    <td>$language</td>
                                    <td>$page_number</td>
                                    <td>$price تومان</td>
                                    <td>
                                      $date
                                    </td>
                                  </tr>
                                </tbody>
                              </table>
                            </td>
                          </tr>

                          <tr>
                            <td
                              style='font-weight:bold;color:#5dc0a6;font-size:1rem'
                            >
                              🙏 با تشکر از خرید شما
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
        </tbody>
      </table>
      <table style='width:100%'>
        <tbody>
          <tr>
            <td>
              <table style='width:100%;margin:10px 0;padding:0'>
                <tbody>
                  <tr>
                    <td>
                      <p
                        style='text-align:center;color:#666;font-size:12px;font-weight:400;display:block;width:100%;margin:0;padding:0;direction:rtl'
                      >
                        طراحی توسط
                        <a
                          href='https://coderguy.ir'
                          target='_blank'
                          data-saferedirecturl='https://coderguy.ir'
                          >coderguy</a
                        >
                      </p>

                      <p
                        style='text-align:center;color:#666;font-size:12px;font-weight:400;display:block;width:100%;margin:0;padding:0'
                      >
                        میدان انقلاب ابتدای کارگر شمالی کوچه رستم پ ۲۱ و ۸
                      </p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </td>
          </tr>
        </tbody>
      </table>

      <img
        src='https://ci6.googleusercontent.com/proxy/x1hIVdOPqG1u7nFBLvrvow3A7rXWw6G0YolfgKSfhAJWSkkBNfGon9YTINQ6I2SyfGqYw7up59T-NdDUxBBgz4E14G8p4q4NoP93Weg4bUJvvy66sNJX4EpSMh9hXn7LowGlNVamYUA=s0-d-e1-ft#https://mandrillapp.com/track/open.php?u=30121732&amp;id=ee001c7acb1741cfa420738ecd825d99'
        height='1'
        width='1'
        class='CToWUd'
      />
    </div>
  </body>
</html>

";

        \mail($email, $subject, $msg, $headers);
    }
 
    public function upload_file($req, $res, $args)
    {
        $uploadedFiles = $req->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        $directory = dirname(dirname(__DIR__)) . '/public/uploads/order';
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            try {
                $filename = $this->moveUploadedFile($directory, $uploadedFile);
                $res->write($filename);
            } catch (\Exception $e) {
                $res->write("error while uploading file "+$e->getMessage())->withStatus(500);
            }
        } else {
            $res->write($uploadedFile->getError())->withStatus(500);
        }
    }

}
