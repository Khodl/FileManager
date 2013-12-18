<?php

use Symfony\Component\HttpFoundation\Request;

$setDataDataController = $app['controllers_factory'];

$setDataDataController->post('/', function (Request $request) use ($app,$fmValidator) {

	$vm =  new viewManager($app,$request,$fmValidator);
	return $vm->setMode('data')->runSetData();

})->bind("views_setdatadata") ;

return $setDataDataController;