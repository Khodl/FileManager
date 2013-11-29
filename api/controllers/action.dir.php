<?php

use Symfony\Component\HttpFoundation\Request;

$dirController = $app['controllers_factory'];

$dirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"Dir");
	//$path = $fmValidator->getPath($request);
	$path = $request->get('path') ;


	$output = array();
	$dir = opendir($fmValidator->getWorkingPath($path));
	while($file = readdir($dir)){
		if($file{0} != '.'){

			$fullPath = $path.'/'.$file ;

			$isFolder = is_dir($fmValidator->getWorkingPath($fullPath)) ;

			// Folder + file content
			$i = array(
				'name' => $file,
				'file' => $fullPath,
				'isFolder' => $isFolder,
				'lastEdition' => filectime($fullPath),
			);

			// Folder content
			if($isFolder){
				$i['isLazy'] = true ;
				$i['dirURL'] = $fmValidator->getActionUrl("action_dir",$fmValidator->getDirKey($fullPath),array("path"=>$fullPath)) ;
				$i['mkdirURL'] = $fmValidator->getActionUrl("action_mkdir",$fmValidator->getMkDirKey($fullPath),array("path"=>$fullPath)) ;
				$i['rmdirURL'] = $fmValidator->getActionUrl("action_rmdir",$fmValidator->getRmDirKey($fullPath),array("path"=>$fullPath)) ;
				$i['createURL'] = $fmValidator->getActionUrl("action_create",$fmValidator->getCreateKey($fullPath),array("path"=>$fullPath)) ;
			}

			// File content

			$output['result'][] = $i ;

		}
	}

    return $app->json($output) ;

})->bind("action_dir");

return $dirController ;