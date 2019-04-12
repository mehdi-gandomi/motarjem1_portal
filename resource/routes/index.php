<?php
use Slim\Http\Request;
use Slim\Http\Response;
use App\Dependencies\CurlRequest;
$container = $app->getContainer();
function wpPosts ()
{

    $curl = new \App\Dependencies\CurlRequest("http://www.motarjem1.com/blog/wp-json/wp/v2/posts?per_page=3&page=1");
    $posts = [];
    $postData = $curl->execute_and_parse_json(true);
    $monthNames = ["", 'فروردین', "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"];
    foreach ($postData as $post) {
        $data = array(
            'title' => $post["title"]["rendered"],
            "previewText" => $post["excerpt"]["rendered"],
            "link" => $post["guid"]["rendered"],
        );
        $date = new \DateTime($post["date"]);
        $dateParts = explode("-", $date->format("Y-m-d"));
        $persian = gregorian_to_jalali($dateParts[0], $dateParts[1], $dateParts[2]);

        $data["date"] = $persian[2] . " " . $monthNames[$persian[1]] . " " . $persian[0];
        $curl->set_url($post["_links"]['wp:featuredmedia'][0]['href']);
        $postDetails = $curl->execute_and_parse_json(true);
        if (isset($postDetails["media_details"])) {
            $data["thumbnail"] = $postDetails["media_details"]["sizes"]["medium_thumb"]["source_url"];
        } else {
            $curl->set_url($post["_links"]['wp:attachment'][0]['href']);
            $postDetails = $curl->execute_and_parse_json(true);

            $data["thumbnail"] = $postDetails[0]["guid"]["rendered"];
        }

        $curl->set_url($post["_links"]["wp:term"][0]["href"]);
        $postCategories = $curl->execute_and_parse_json(true);
        $links = [];
        foreach ($postCategories as $category) {
            array_push($links, array(
                'link' => $category["link"],
                'name' => $category["name"],
            ));
        }
        $data["categories"] = json_encode($links);

        $posts[] = $data;
    }
    return $posts;

};

$wpPostsMV=function($request, $response, $next)
{
    
    if (!isset($_SESSION['latestPosts']) || count($_SESSION['latestPosts'])<1) {
        $posts=\Core\Model::select("posts");
        $posts=array_map(function($post){
            $post['categories']=json_decode($post['categories'],true);
            return $post;
        },$posts);
        $_SESSION["latestPosts"] = $posts;
    }
    $response = $next($request, $response);
    return $response;
    
};

// i added this route group to execute the wpPosts middleware only on this group!
$app->group('/', function ($app) use ($container) {
    $app->get('', function (Request $request, Response $response, array $args) {
        $this->view->render($response, "website/home.twig", ["page_title" => "صفحه اصلی", "latestPosts" => $_SESSION["latestPosts"]]);
    });
    $app->get('about-us', function (Request $request, Response $response, array $args) {
        $this->view->render($response, "website/about-us.twig", ["page_title" => "درباره ما", "latestPosts" => $_SESSION["latestPosts"]]);
    });
    $app->get('contact-us', function (Request $request, Response $response, array $args) {
        $this->view->render($response, "website/contact-us.twig", ["page_title" => "تماس باما", "latestPosts" => $_SESSION["latestPosts"]]);
    });
    $app->get('automation', function (Request $request, Response $response, array $args) {
        $posts=wpPosts();
        var_dump($posts);
        if($posts){
            $sql="DELETE FROM posts";
            $db=\Core\Model::getDB();
            $result=$db->query($sql);
            if($result){
                for($i=0;$i<count($posts);$i++){
                    $sql="INSERT INTO posts (title,previewText,link,date,thumbnail,categories) VALUES (:title,:previewText,:link,:date,:thumbnail,:categories)";
                    $stmt=$db->prepare($sql);
                    $stmt->execute($posts[$i]);
                }

            }

        }
    });
    $app->get('payment-success/{order_number}', "App\Controllers\OrderController:payment_result_zarinpal");
    $app->get('expense-calculator', "App\Controllers\ExpenseController:get");
    $app->get('order', "App\Controllers\OrderController:get")->add($container->get("csrf"));
    $app->get("order/discount-code/validate","App\Controllers\OrderController:validate_coupon_code");
    $app->get('order-method', "App\Controllers\OrderController:order_method_page");

    $app->post('expense-calculator', "App\Controllers\ExpenseController:post");
    $app->post('order-completed', "App\Controllers\OrderController:save_order_info")->add($container->get('csrf'));
    $app->post('payment-success/{order_number}', "App\Controllers\OrderController:payment_result_mellat");

})->add($wpPostsMV);

$app->post('/upload-order-file', "App\Controllers\OrderController:upload_file");
$app->post('/order-payment/{order_number}', "App\Controllers\OrderController:order_payment")->add($container->get('csrf'));
