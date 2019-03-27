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
    $app->get("/tickets/customer","App\Controllers\AdminPanelController:customer_tickets_page");
    $app->get("/tickets/customer/json","App\Controllers\AdminPanelController:customer_tickets_json");
    $app->get("/tickets/translator","App\Controllers\AdminPanelController:translator_tickets_page");
    $app->get("/tickets/translator/json","App\Controllers\AdminPanelController:translator_tickets_json");
    $app->get("/ticket/view/{ticket_number}","App\Controllers\AdminPanelController:get_ticket_details_page");
    $app->get("/ticket/view/{ticket_number}/json","App\Controllers\AdminPanelController:get_ticket_details_json");
    $app->get("/new-translators","App\Controllers\AdminPanelController:get_new_unemployed_translators_page");
    $app->get("/translator/order-requests","App\Controllers\AdminPanelController:get_translators_order_requests_page");
    $app->get("/hired-translators","App\Controllers\AdminPanelController:get_hired_translators_page");
    $app->get("/translator/info/{username}","App\Controllers\AdminPanelController:get_translator_info_page");
    $app->get("/order/view/{order_number}","App\Controllers\AdminPanelController:get_order_details_page");
    $app->get("/order/orderer-info/{orderer_id}","App\Controllers\AdminPanelController:get_orderer_data_json");
    $app->get("/order/translator-info/{translator_id}","App\Controllers\AdminPanelController:get_translator_data_json");
    $app->get("/pending-orders","App\Controllers\AdminPanelController:get_pending_orders_page");
    $app->get("/completed-orders","App\Controllers\AdminPanelController:get_completed_orders_page");
    $app->get("/translator/payment-requests","App\Controllers\AdminPanelController:get_translators_payment_requests_page");
    $app->get("/translator/payment-requests/json","App\Controllers\AdminPanelController:get_translators_payment_requests_json");
    $app->get("/translator/payment-requests/set-payment-info","App\Controllers\AdminPanelController:get_payment_info_json");
    $app->get("/translators/account-info","App\Controllers\AdminPanelController:get_translators_account_info_page");
    $app->get("/site-revenue","App\Controllers\AdminPanelController:get_website_revenue_page");
    $app->get("/notifications","App\Controllers\AdminPanelController:get_notifications_page");
    $app->get("/notifications/new","App\Controllers\AdminPanelController:get_new_notification_page");
    $app->post("/translator/employ","App\Controllers\AdminPanelController:post_employ_translator");
    $app->post("/translator/deny","App\Controllers\AdminPanelController:post_deny_translator");
    $app->post("/translator-order-request/accept","App\Controllers\AdminPanelController:accept_translator_order_request");
    $app->post("/translator-order-request/deny","App\Controllers\AdminPanelController:deny_translator_order_request");
    $app->post("/ticket/reply","App\Controllers\AdminPanelController:post_reply_ticket");
    $app->post("/translator/payment-requests/accept","App\Controllers\AdminPanelController:accept_translator_payment_request");
    $app->post("/translator/payment-requests/deny","App\Controllers\AdminPanelController:deny_translator_payment_request");
    $app->post("/translator/payment-requests/set-payment-info","App\Controllers\AdminPanelController:post_set_payment_info");
    
})->add(function ($req, $res, $next) use ($container) {
    if (isset($_SESSION['is_admin_logged_in'])) {
        return $next($req, $res);
    } else {
        return $res->withRedirect("/admin/login");
    }
});
