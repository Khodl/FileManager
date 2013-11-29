<?php

use Symfony\Component\HttpFoundation\Request;

$mkdirController = $app['controllers_factory'];

$mkdirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"Create");
	$fmValidator->checkRequestParameters(array('filename','content'),$request);

	$filename = $request->get('filename') ;
	$path = $fmValidator->getWorkingPath($request->get('path')).'/'.$filename;

	if(file_exists($path)) $app->abort(409,"File '$filename' already exists");

	file_put_contents($path,$request->get('content'));

	return $app->json(array(
		'result' => "File '$filename' created"
	)) ;

})->bind("action_create");

return $mkdirController;