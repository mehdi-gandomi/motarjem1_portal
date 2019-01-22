<?php
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();

$app->group('/user', function($app) use ($container){
    $app->get('', function ($request, $response, $args) {
        $this->view->render($response,"admin/user/dashboard.twig");
    });
    $app->get('/auth',"App\Controllers\AuthController:customer_get_auth")->add($container->get('csrf'));
    $app->post('/login',"App\Controllers\AuthController:customer_post_login")->add($container->get('csrf'));
    $app->post('/signup',"App\Controllers\AuthController:customer_post_signup")->add($container->get('csrf'));
    $app->get('/logout',"App\Controllers\AuthController:logout");
    $app->post('/verify/{username}',"App\Controllers\AuthController:customer_send_verification_email");
    $app->get('/confirm',"App\Controllers\AuthController:customer_verify_email");
});
