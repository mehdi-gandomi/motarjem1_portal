<?php
/******************************* LOADING & INITIALIZING BASE APPLICATION ****************************************/
// Configuration for error reporting, useful to show every little problem during development

session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);
date_default_timezone_set('Asia/Tehran');
// Load Composer's PSR-4 autoloader (necessary to load Slim, Mini etc.)
require 'vendor/autoload.php';


//loading jdf library to convert date to persian format
require_once "App/Dependencies/jdf.php";

//loading app container config including views and slim flash and slim csrf

require_once "AppConfig.php";

$app = new \Slim\App($c);

/*******************************End Of LOADING & INITIALIZING BASE APPLICATION ****************************************/

// loading routes automatically

// require_once("App/routes/index.php");
// require_once("App/routes/user_admin.php");
// require_once("App/routes/user_admin.php");
foreach(array_diff(scandir(__DIR__."/App/routes"), array('.', '..')) as $route){
    require_once(__DIR__."/App/routes/".$route);
}


//running project
$app->run();

