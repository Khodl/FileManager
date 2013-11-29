<?php

use Symfony\Component\HttpFoundation\Request;

$mkdirController = $app['controllers_factory'];

$mkdirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"MkDir");
	$fmValidator->checkRequestParameters(array('dirname'),$request);

	$dirname = $request->get('dirname') ;
	$path = $fmValidator->getWorkingPath($request->get('path')).'/'.$dirname;

	if(file_exists($path)) $app->abort(409,"Folder '$dirname' already exists");

	mkdir($path);
	$output = array(
		'result' => "Folder '$dirname' created"
	);

	return $app->json($output) ;

})->bind("action_mkdir");

return $mkdirController;