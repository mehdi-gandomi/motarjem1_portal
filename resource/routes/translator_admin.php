<?php

$container = $app->getContainer();

$app->group('/translator', function ($app) use ($container) {

});

$app->get('/translator/login', "App\Controllers\TranslatorController:get_login")->add($container->get('csrf'));
$app->post('/translator/login', "App\Controllers\TranslatorController:post_login")->add($container->get('csrf'));
$app->get('/translator/logout', "App\Controllers\TranslatorController:logout");
