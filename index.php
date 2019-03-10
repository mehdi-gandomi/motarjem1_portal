<?php
/******************************* LOADING & INITIALIZING BASE APPLICATION ****************************************/
// Configuration for error reporting, useful to show every little problem during development

//starting the session and set cookie lifetime to 2 days
session_start([
    'cookie_lifetime' => 172800,
]);
error_reporting(E_ALL);
ini_set("display_errors", 1);
// ini_set('memory_limit', '256M'); 
date_default_timezone_set('Asia/Tehran');
use Slim\Http\Request;
use Slim\Http\Response;
// Load Composer's PSR-4 autoloader (necessary to load Slim, Mini etc.)
require 'vendor/autoload.php';


//loading jdf library to convert date to persian format
require_once "App/Dependencies/jdf.php";

//loading app container config including views and slim flash and slim csrf

require_once "AppConfig.php";

$app = new \Slim\App($c);

// remove trailing slash (slash after a url)
$app->add(function (Request $request, Response $response, callable $next) {
    $uri = $request->getUri();
    $path = $uri->getPath();
    if ($path != '/' && substr($path, -1) == '/') {
        // permanently redirect paths with a trailing slash
        // to their non-trailing counterpart
        $uri = $uri->withPath(substr($path, 0, -1));
        
        if($request->getMethod() == 'GET') {
            return $response->withRedirect((string)$uri, 301);
        }
        else {
            return $next($request->withUri($uri), $response);
        }
    }

    return $next($request, $response);
});
/*******************************End Of LOADING & INITIALIZING BASE APPLICATION ****************************************/

// loading routes automatically

// require_once("App/routes/index.php");
// require_once("App/routes/user_admin.php");
// require_once("App/routes/user_admin.php");
foreach(array_diff(scandir(__DIR__."/resource/routes"), array('.', '..')) as $route){
    require_once(__DIR__."/resource/routes/".$route);
}


//running project
$app->run();

