<?php

use Symfony\Component\HttpFoundation\Request;

$rmdirController = $app['controllers_factory'];

$rmdirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"RmDir");

	$path = $fmValidator->getWorkingPath($request->get('path'));
	$dirname = basename($path);

	if(! is_dir($path)) $app->abort(404,"Folder '$dirname' not found");
	rmdir($path);
	if(is_dir($path)) $app->abort(403,"Impossible to remove the folder '$dirname'");

	return $app->json(array(
		'result' => array(
			'message'=> "Folder '$dirname' removed",
			'dirname'=> $dirname
		)
	)) ;

})->bind("action_rmdir");

return $rmdirController;