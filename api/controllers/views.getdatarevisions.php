<?php

use Symfony\Component\HttpFoundation\Request;

$getDataRevisionsController = $app['controllers_factory'];

$getDataRevisionsController->match('/', function (Request $request) use ($app,$fmValidator) {

	$vm =  new viewManager($app,$request,$fmValidator);
	return $vm->setMode('data')->runGetRevisions();

})->bind("views_getdatarevisions");

return $getDataRevisionsController;