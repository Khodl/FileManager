<?php

use Symfony\Component\HttpFoundation\Request;

$deleteController = $app['controllers_factory'];

$deleteController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"Delete");

	$path = $fmValidator->getWorkingPath($request->get('path'));
	$filename = basename($path) ;

	if(! file_exists($path)) $app->abort(404,"File '$filename' doesn't exist");
	unlink($path);
	if(file_exists($path)) $app->abort(403,"Impossible to remove '$filename'");

	return $app->json(array(
		'result' => array(
			'message' => "File '$filename' deleted",
			'filename' => $filename
		)
	)) ;

})->bind("action_delete");

return $deleteController;