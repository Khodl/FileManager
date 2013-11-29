<?php
/**
 * Create the environment and call the controllers
 */


// Require
require_once __DIR__.'/../vendor/autoload.php';
require_once './inc/fmDataValidator.class.php';

// Use
use Silex\Application ;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Debug\ErrorHandler ;
use Symfony\Component\Yaml\Parser;

// Errors & exceptions display
$debug = true ;
ExceptionHandler::register($debug);
ErrorHandler::register($debug);

// Starting a new app
$app = new Silex\Application();
$app->boot();
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// YML
$yaml = new Parser();
$app['config'] = $yaml->parse(file_get_contents('./config.yml'));
//$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/config.yml'));

// File Manager validator
$fmValidator = new fmDataValidator($app);
$fmValidator->setRoot($app['config']['directory']['rootURL']);
$fmValidator->setKey($app['config']['directory']['rootURL']);

// Error handler
$app->error(function (\Exception $e) use ($app){
    return $app->json(array('error'=>$e->getMessage())) ;
});

// Loading controllers
$prefixCommand = 'exec/{key}/';
$app->mount($prefixCommand.'dir', include "./controllers/action.dir.php");
$app->mount($prefixCommand.'mkdir', include "./controllers/action.mkdir.php");
$app->mount($prefixCommand.'rmdir', include "./controllers/action.rmdir.php");

// Run app
$app->run();

