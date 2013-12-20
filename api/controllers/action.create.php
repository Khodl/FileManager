<?php

use Symfony\Component\HttpFoundation\Request;

$createController = $app['controllers_factory'];

$createController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"Create");
	$fmValidator->checkRequestParameters(array('filename','content'),$request);

	$filename = $request->get('filename') ;
	$filename = str_replace('<timestamp>',time(),$filename);
	$filename = str_replace('<random>',rand(0,pow(10,10)),$filename);
	$fmValidator->checkFilename($filename,'filename');
	$path = $fmValidator->getWorkingPath($request->get('path')).'/'.$filename;


	if(file_exists($path)) $app->abort(409,"File '$filename' already exists");

	file_put_contents($path,$request->get('content'));

	return $app->json(array(
		'result' => array(
			'message'=>"File '$filename' created",
			'filename' => $filename,
			// Todo : writeURL & readURL
		)
	)) ;

})->bind("action_create");

return $createController;