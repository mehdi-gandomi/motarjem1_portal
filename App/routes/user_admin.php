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
    $app->get('', function ($request, $response, $args) {
        $this->view->render($response, "admin/user/dashboard.twig");
    });

})->add($authMV);

