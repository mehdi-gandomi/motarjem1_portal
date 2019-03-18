<?php
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();


//auth routes
$app->get('/admin/login', "App\Controllers\AdminPanelController:get_login");
$app->post('/admin/login', "App\Controllers\AdminPanelController:post_login");
$app->get('/admin/logout', "App\Controllers\AdminPanelController:logout");
//admin panel routes (needs login)
$app->group('/admin', function ($app) use ($container) {
    $app->get('', "App\Controllers\AdminPanelController:dashboard");
    $app->get("/translator-info/all/json","App\Controllers\AdminPanelController:all_translator_info_json");
    $app->get("/ticket-details/json","App\Controllers\AdminPanelController:ticket_details_json");
    $app->post("/translator/employ","App\Controllers\AdminPanelController:post_employ_translator");
    $app->post("/translator/deny","App\Controllers\AdminPanelController:post_deny_translator");
})->add(function ($req, $res, $next) use ($container) {
    if (isset($_SESSION['is_admin_logged_in'])) {
        return $next($req, $res);
    } else {
        return $res->withRedirect("/admin/login");
    }
});
