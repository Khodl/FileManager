<?php

use Symfony\Component\HttpFoundation\Request;

$getViewRevisionsController = $app['controllers_factory'];

$getViewRevisionsController->match('/', function (Request $request) use ($app,$fmValidator) {

	$vm =  new viewManager($app,$request,$fmValidator);
	return $vm->setMode('view')->runGetRevisions();

})->bind("views_getviewrevisions");

return $getViewRevisionsController;