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
$app->get('/user/auth', "App\Controllers\UserAuthController:get_auth")->add($container->get('csrf'));
$app->post('/user/login', "App\Controllers\UserAuthController:post_login")->add($container->get('csrf'));
$app->post('/user/signup', "App\Controllers\UserAuthController:post_signup")->add($container->get('csrf'));
$app->get('/user/logout', "App\Controllers\UserAuthController:logout");
$app->post('/user/verify/{username}', "App\Controllers\UserAuthController:send_verification_email");
$app->get('/user/confirm', "App\Controllers\UserAuthController:verify_email");
$app->get("/user/forget-password", "App\Controllers\UserAuthController:get_forget_password_page")->add($container->get('csrf'));
$app->post('/user/forget-password', "App\Controllers\UserAuthController:send_password_reset_link")->add($container->get('csrf'));
$app->get('/user/reset-password', "App\Controllers\UserAuthController:reset_password_process")->add($container->get('csrf'));
$app->post('/user/password-reset', "App\Controllers\UserAuthController:post_change_password")->add($container->get('csrf'));

//user admin routes
$app->group('/user', function ($app) use ($container) {
    $app->get('', "App\Controllers\UserPanelController:get_dashboard");
    $app->get('/translator/getinfo/{id}',"App\Controllers\UserPanelController:get_translator_info");
    $app->get('/orders',"App\Controllers\UserPanelController:get_user_orders");
    $app->get("/orders/json","App\Controllers\UserPanelController:get_user_orders_json");
    $app->get("/order/new","App\Controllers\UserPanelController:user_new_order_page")->add($container->get('csrf'));
    $app->post("/order/save","App\Controllers\UserPanelController:save_user_order_info")->add($container->get('csrf'));
    $app->get("/order/view/{order_id}","App\Controllers\UserPanelController:get_order_details")->add($container->get('csrf'));
    $app->get("/ticket/view/{ticket_number}","App\Controllers\UserPanelController:get_ticket_details");
    $app->get("/tickets","App\Controllers\UserPanelController:get_tickets_page");
    $app->get("/tickets/json","App\Controllers\UserPanelController:get_tickets_json");
    $app->post("/ticket/send","App\Controllers\UserPanelController:post_send_ticket");
    $app->post("/ticket/reply","App\Controllers\UserPanelController:post_reply_ticket");
    $app->get("/edit-profile","App\Controllers\UserPanelController:edit_profile_page")->add($container->get('csrf'));
    $app->post("/edit-profile","App\Controllers\UserPanelController:post_edit_profile")->add($container->get('csrf'));
    $app->post("/edit-profile/upload-avatar","App\Controllers\UserPanelController:upload_avatar");
    $app->post('/order-payment/{order_id}', "App\Controllers\UserPanelController:order_payment")->add($container->get('csrf'));
})->add($authMV);
