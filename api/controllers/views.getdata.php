<?php

use Symfony\Component\HttpFoundation\Request;

$getDataController = $app['controllers_factory'];

$getDataController->match('/', function (Request $request) use ($app,$fmValidator) {

	$vm =  new viewManager($app,$request,$fmValidator);
	return $vm->setMode('data')->runGet();

})->bind("views_getdata");

return $getDataController;