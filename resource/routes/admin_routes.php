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
    $app->get("/translator/basic-info/json","App\Controllers\AdminPanelController:basic_translator_info_json");
    $app->get("/ticket-details/json","App\Controllers\AdminPanelController:ticket_details_json");
    $app->get("/order/info/json/{order_number}","App\Controllers\AdminPanelController:order_info_json");
    $app->post("/translator/employ","App\Controllers\AdminPanelController:post_employ_translator");
    $app->post("/translator/deny","App\Controllers\AdminPanelController:post_deny_translator");
    $app->post("/translator-order-request/accept","App\Controllers\AdminPanelController:accept_translator_order_request");
    $app->post("/translator-order-request/deny","App\Controllers\AdminPanelController:deny_translator_order_request");
    $app->post("/ticket/reply","App\Controllers\AdminPanelController:post_reply_ticket");
})->add(function ($req, $res, $next) use ($container) {
    if (isset($_SESSION['is_admin_logged_in'])) {
        return $next($req, $res);
    } else {
        return $res->withRedirect("/admin/login");
    }
});
