<?php

use Symfony\Component\HttpFoundation\Request;

$dirController = $app['controllers_factory'];

$dirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkPathKey($request,"Dir");
	$path = $request->get('path') ;


	$output = array();
	$dir = opendir($fmValidator->getWorkingPath($path));
	while($file = readdir($dir)){
		if($file{0} != '.'){

			$fullPath = $path.'/'.$file ;
			$wp = $fmValidator->getWorkingPath($fullPath) ;

			$isFolder = is_dir($wp) ;

			// Folder + file content
			$i = array(
				'name' => $file,
				'file' => $fullPath,
				'isFolder' => $isFolder,
				'lastEdition' => filectime($wp),
			);

			// Folder content
			if($isFolder){
				$i['isLazy'] = true ;
				$i['dirURL'] = $fmValidator->getActionUrl("action_dir",$fmValidator->getDirKey($fullPath),array("path"=>$fullPath)) ;
				$i['mkdirURL'] = $fmValidator->getActionUrl("action_mkdir",$fmValidator->getMkDirKey($fullPath),array("path"=>$fullPath)) ;
				$i['rmdirURL'] = $fmValidator->getActionUrl("action_rmdir",$fmValidator->getRmDirKey($fullPath),array("path"=>$fullPath)) ;
				$i['createURL'] = $fmValidator->getActionUrl("action_create",$fmValidator->getCreateKey($fullPath),array("path"=>$fullPath)) ;
			}else{
			// File content
				$i['readURL'] = $fmValidator->getActionUrl("action_load",$fmValidator->getLoadKey($fullPath),array("path"=>$fullPath)) ;
				$i['writeURL'] = $fmValidator->getActionUrl("action_save",$fmValidator->getSaveKey($fullPath),array("path"=>$fullPath)) ;
				$i['deleteURL'] = $fmValidator->getActionUrl("action_delete",$fmValidator->getDeleteKey($fullPath),array("path"=>$fullPath)) ;
			}


			$output['result'][] = $i ;

		}
	}

    return $app->json($output) ;

})->bind("action_dir");

return $dirController ;