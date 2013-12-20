<?php

use Symfony\Component\HttpFoundation\Request;

$getViewBranchesController = $app['controllers_factory'];

$getViewBranchesController->match('/', function (Request $request) use ($app,$fmValidator) {

	$vm =  new viewManager($app,$request,$fmValidator);
	return $vm->setMode('view')->runGetBranches();

})->bind("views_getviewbranches");

return $getViewBranchesController;