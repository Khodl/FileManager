<?php

use Symfony\Component\HttpFoundation\Request;

$rmdirController = $app['controllers_factory'];

$rmdirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"RmDir");

	$path = $fmValidator->getWorkingPath($request->get('path'));

	if(! is_dir($path)) $app->abort(404,"Folder not found");
	rmdir($path);
	if(is_dir($path)) $app->abort(403,"Impossible to remove this folder");

	return $app->json(array(
		'result' => "Folder removed"
	)) ;

})->bind("action_rmdir");

return $rmdirController ;