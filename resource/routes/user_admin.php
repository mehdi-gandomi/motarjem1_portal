<?php

$container = $app->getContainer();

$authMV = function ($req, $res, $next) use ($container) {

    if (isset($_SESSION['is_user_logged_in'])) {
        return $next($req, $res);
    } else {

        return $res->withRedirect("/user/auth");

    }

};


//user authentication routes
$app->get('/user/auth', "App\Controllers\UserController:get_auth")->add($container->get('csrf'));
$app->post('/user/login', "App\Controllers\UserController:post_login")->add($container->get('csrf'));
$app->post('/user/signup', "App\Controllers\UserController:post_signup")->add($container->get('csrf'));
$app->get('/user/logout', "App\Controllers\UserController:logout");
$app->post('/user/verify/{username}', "App\Controllers\UserController:send_verification_email");
$app->get('/user/confirm', "App\Controllers\UserController:verify_email");


//user admin routes
$app->group('/user', function ($app) use ($container) {
    $app->get('', "App\Controllers\UserController:get_dashboard");
    $app->get('/translator/getinfo/{id}',"App\Controllers\UserController:get_translator_info");
    $app->get('/orders',"App\Controllers\UserController:get_user_orders");
    $app->get("/orders/json","App\Controllers\UserController:get_user_orders_json");
    $app->get("/order/new","App\Controllers\UserController:user_new_order_page");
    $app->get("/order/view/{order_id}","App\Controllers\UserController:get_order_details");
    $app->get("/message/view/{msg_id}","App\Controllers\UserController:get_message_details");
    $app->get("/messages","App\Controllers\UserController:get_messages_page");
    $app->get("/messages/json","App\Controllers\UserController:get_messages_json");
    $app->post("/message/send","App\Controllers\UserController:post_send_message");
    $app->post("/message/reply","App\Controllers\UserController:post_reply_message");
    $app->get("/edit-profile","App\Controllers\UserController:edit_profile_page")->add($container->get('csrf'));
    $app->post("/edit-profile","App\Controllers\UserController:post_edit_profile")->add($container->get('csrf'));
})->add($authMV);

