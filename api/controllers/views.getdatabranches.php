<?php

use Symfony\Component\HttpFoundation\Request;

$getDataBranchesController = $app['controllers_factory'];

$getDataBranchesController->match('/', function (Request $request) use ($app,$fmValidator) {

	$vm =  new viewManager($app,$request,$fmValidator);
	return $vm->setMode('data')->runGetBranches();

})->bind("views_getdatabranches");

return $getDataBranchesController;