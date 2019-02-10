<?php
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();

$app->group('/translator', function ($app) use ($container) {

});

$app->get('/translator/login', "App\Controllers\TranslatorController:get_login")->add($container->get('csrf'));
$app->post('/translator/login', "App\Controllers\TranslatorController:post_login")->add($container->get('csrf'));
$app->get('/translator/logout', "App\Controllers\TranslatorController:logout");
//these routes dont get affected by wpPosts middleware
$app->get('/translator/employment', "App\Controllers\TranslatorController:get_employment")->add($container->get('csrf'));
$app->get('/new-captcha', function (Request $request, Response $response, array $args) {
    $builder = new Gregwar\Captcha\CaptchaBuilder;
    $builder->build();
    $captcha = $builder->inline();
    $_SESSION['captcha'] = $builder->getPhrase();
    return $response->withJson(array(
        'captcha' => $captcha,
    ));
});
$app->get('/testing', function (Request $request, Response $response, array $args) {
    $this->view->render($response, "website/successful-employment.twig", ['email' => "coderguy1999@gmail.com", "page_title" => "ثبت نام موفق"]);
});

$app->post('/translator/employment', "App\Controllers\TranslatorController:post_employment")->add($container->get('csrf'));
$app->post('/upload-employee-photo', "App\Controllers\TranslatorController:upload_photo");
$app->post('/upload-employee-melicard', "App\Controllers\TranslatorController:upload_melicard_photo");
$app->post('/translator/send-verify/{username}', "App\Controllers\TranslatorController:send_verify_link_again");
