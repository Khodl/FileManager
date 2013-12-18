<?php

use Symfony\Component\HttpFoundation\Request;

$setViewDataController = $app['controllers_factory'];

$setViewDataController->post('/', function (Request $request) use ($app,$fmValidator) {

	$vm =  new viewManager($app,$request,$fmValidator);
	return $vm->setMode('view')->runSetData();

})->bind("views_setviewdata") ;

return $setViewDataController;