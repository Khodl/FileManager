<?php

// Require
require_once __DIR__.'/../vendor/autoload.php';
require_once './inc/fmDataValidator.class.php';

// Use
use Silex\Application ;
use Symfony\Component\Debug\ExceptionHandler;

// Exceptions
ExceptionHandler::register();

// Starting a new app
$app = new Silex\Application();
$app->boot();

// File Manager validator
$fmValidator = new fmDataValidator($app);
$fmValidator->setRoot("_files");
$fmValidator->setKey("1234567890");

// Error handler
$app->error(function (\Exception $e) use ($app){
    return $app->json(array('error'=>$e->getMessage())) ;
});

// Loading controllers
$prefixCommand = '/exec/';
$app->mount($prefixCommand.'dir', include "./controllers/action.dir.php");

// Run app
$app->run();

