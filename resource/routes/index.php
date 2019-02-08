<?php
use Slim\Http\Request;
use Slim\Http\Response;
use App\Dependencies\CurlRequest;
$container = $app->getContainer();

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
    $app->get('payment-success/{order_id}', "App\Controllers\OrderController:payment_result_zarinpal");
    $app->get('expense-calculator', "App\Controllers\ExpenseController:get");
    $app->get('order', "App\Controllers\OrderController:get")->add($container->get("csrf"));
    $app->get('order-method', "App\Controllers\OrderController:order_method_page");

    $app->post('expense-calculator', "App\Controllers\ExpenseController:post");
    $app->post('order-completed', "App\Controllers\OrderController:save_order_info")->add($container->get('csrf'));
    $app->post('payment-success/{order_id}', "App\Controllers\OrderController:payment_result_mellat");

})->add($wpPostsMV);

$app->post('/upload-order-file', "App\Controllers\OrderController:upload_file");
$app->post('/order-payment/{order_id}', "App\Controllers\OrderController:order_payment")->add($container->get('csrf'));
