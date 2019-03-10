<?php
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();


//auth routes



//admin panel routes (needs login)
$app->group('/admin', function ($app) use ($container) {
    $app->get('', "App\Controllers\AdminPanelController:dashboard");
})->add(function ($req, $res, $next) use ($container) {
    return $next($req, $res);
    // if (isset($_SESSION['is_translator_logged_in'])) {
    //     return $next($req, $res);
    // } else {

    //     return $res->withRedirect("/translator/login");

    // }
});
