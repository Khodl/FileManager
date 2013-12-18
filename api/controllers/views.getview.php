<?php

use Symfony\Component\HttpFoundation\Request;

$getViewController = $app['controllers_factory'];

$getViewController->match('/', function (Request $request) use ($app,$fmValidator) {

	$vm =  new viewManager($app,$request,$fmValidator);
	return $vm->setMode('view')->runGet();

})->bind("views_getview");

return $getViewController;