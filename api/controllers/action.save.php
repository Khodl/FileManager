<?php

use Symfony\Component\HttpFoundation\Request;

$saveController = $app['controllers_factory'];

$saveController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"Save");
	$fmValidator->checkRequestParameters(array('content'),$request);
	$path = $fmValidator->getWorkingPath($request->get('path'));
	$filename = basename($path);

	file_put_contents($path,$request->get('content'));

	return $app->json(array(
		'result' => array(
			'message'=>"File '$filename' updated",
			'filename' => $filename,
		)
	)) ;

})->bind("action_save");

return $saveController;