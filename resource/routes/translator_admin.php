<?php
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();

$app->group('/translator', function ($app) use ($container) {

});


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
$app->get('/testing', function (Request $request, Response $response, array $args) {
    $this->view->render($response, "website/successful-employment.twig", ['email' => "coderguy1999@gmail.com", "page_title" => "ثبت نام موفق"]);
});

$app->post('/translator/employment', "App\Controllers\TranslatorAuthController:post_employment")->add($container->get('csrf'));
$app->post('/upload-employee-photo', "App\Controllers\TranslatorAuthController:upload_photo");
$app->post('/upload-employee-melicard', "App\Controllers\TranslatorAuthController:upload_melicard_photo");
$app->post('/translator/send-verify/{username}', "App\Controllers\TranslatorAuthController:send_verify_link_again");
