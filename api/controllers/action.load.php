<?php

use Symfony\Component\HttpFoundation\Request;

$loadController = $app['controllers_factory'];

$loadController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"Load");

	$path = $fmValidator->getWorkingPath($request->get('path'));
	$filename = basename($path) ;

	if(! file_exists($path)) $app->abort(404,"File '$filename' doesn't exist");

	return $app->json(array(
		'result' => array(
			'message' => "File '$filename' loaded",
			'filename' => $filename,
			'content' => file_get_contents($path)
		)
	)) ;

})->bind("action_load");

return $loadController;