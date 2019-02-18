<?php
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();


//auth routes

$app->get('/translator/login', "App\Controllers\TranslatorAuthController:get_login")->add($container->get('csrf'));
$app->post('/translator/login', "App\Controllers\TranslatorAuthController:post_login")->add($container->get('csrf'));
$app->get('/translator/logout', "App\Controllers\TranslatorAuthController:logout");
$app->get('/translator/employment', "App\Controllers\TranslatorAuthController:get_employment")->add($container->get('csrf'));
$app->get('/new-captcha', function (Request $request, Response $response, array $args) {
    $builder = new Gregwar\Captcha\CaptchaBuilder;
    $builder->build();
    $captcha = $builder->inline();
    $_SESSION['captcha'] = $builder->getPhrase();
    return $response->withJson(array(
        'captcha' => $captcha,
    ));
});

$app->post('/translator/employment', "App\Controllers\TranslatorAuthController:post_employment")->add($container->get('csrf'));
$app->post('/upload-employee-photo', "App\Controllers\TranslatorAuthController:upload_photo");
$app->post('/upload-employee-melicard', "App\Controllers\TranslatorAuthController:upload_melicard_photo");
$app->post('/translator/send-verify/{username}', "App\Controllers\TranslatorAuthController:send_verify_link_again");
$app->get("/translator/confirm", "App\Controllers\TranslatorAuthController:verify_user_process");
$app->get("/translator/forget-password", "App\Controllers\TranslatorAuthController:get_forget_password_page")->add($container->get('csrf'));
$app->post('/translator/forget-password', "App\Controllers\TranslatorAuthController:send_password_reset_link")->add($container->get('csrf'));
$app->get('/translator/reset-password', "App\Controllers\TranslatorAuthController:reset_password_process")->add($container->get('csrf'));
$app->post('/translator/password-reset', "App\Controllers\TranslatorAuthController:post_change_password")->add($container->get('csrf'));


$app->group('/translator', function ($app) use ($container) {

    $app->get('', "App\Controllers\TranslatorPanelController:get_dashboard");
    $app->get('/test/filter', "App\Controllers\TranslatorPanelController:get_test_json");
    $app->post("/test/send","App\Controllers\TranslatorPanelController:save_test_data");
    $app->get('/order/info/{order_id}', "App\Controllers\TranslatorPanelController:get_order_info");
    $app->post('/order/request', "App\Controllers\TranslatorPanelController:request_order");
    $app->post('/order/decline', "App\Controllers\TranslatorPanelController:decline_order");
    $app->get('/new-orders', "App\Controllers\TranslatorPanelController:get_new_orders");
    $app->get('/new-orders/json', "App\Controllers\TranslatorPanelController:get_new_orders_json");
    
})->add(function ($req, $res, $next) use ($container) {

    if (isset($_SESSION['is_translator_logged_in'])) {
        return $next($req, $res);
    } else {

        return $res->withRedirect("/translator/login");

    }
});
