<?php

use Symfony\Component\HttpFoundation\Request;

$dirController = $app['controllers_factory'];

$dirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$isReadOnly = false ;
	// Readonly
	if(	$fmValidator->checkPathKey($request,"DirRO"))
		$isReadOnly = true ;
	else
		$fmValidator->checkPathKey($request,"Dir");
	$path = $request->get('path') ;


	$output = array();
	$dir = opendir($fmValidator->getWorkingPath($path));
	while($file = readdir($dir)){
		//if($file != '..'){
		if($file{0} != '.' OR $file == '.'){

			$fullPath = $path.'/'.$file ;
			$wp = $fmValidator->getWorkingPath($fullPath) ;

			$isFolder = is_dir($wp) ;

			// Folder + file content
			$i = array(
				'title' => $file,
				'path' => $fullPath,
				'folder' => $isFolder,
				'lastEdition' => filectime($wp),
			);

			// Folder content
			if($isFolder){

				$readOnlyURL = $fmValidator->getActionUrl("action_dir",$fmValidator->getDirROKey($fullPath),array("path"=>$fullPath)) ;

				$i['lazy'] = true ;
				if(! $isReadOnly){
					$i['mkdirURL'] = $fmValidator->getActionUrl("action_mkdir",$fmValidator->getMkDirKey($fullPath),array("path"=>$fullPath)) ;
					$i['rmdirURL'] = $fmValidator->getActionUrl("action_rmdir",$fmValidator->getRmDirKey($fullPath),array("path"=>$fullPath)) ;
					$i['createURL'] = $fmValidator->getActionUrl("action_create",$fmValidator->getCreateKey($fullPath),array("path"=>$fullPath)) ;
					$i['dirURL'] = $fmValidator->getActionUrl("action_dir",$fmValidator->getDirKey($fullPath),array("path"=>$fullPath)) ;
				}else{
					$i['dirURL'] = $readOnlyURL ;
				}
				$i['dirReadOnlyURL'] = $readOnlyURL ;

			}else{
			// File content
				$i['readURL'] = $fmValidator->getActionUrl("action_load",$fmValidator->getLoadKey($fullPath),array("path"=>$fullPath)) ;
				if(! $isReadOnly){
					$i['writeURL'] = $fmValidator->getActionUrl("action_save",$fmValidator->getSaveKey($fullPath),array("path"=>$fullPath)) ;
					$i['deleteURL'] = $fmValidator->getActionUrl("action_delete",$fmValidator->getDeleteKey($fullPath),array("path"=>$fullPath)) ;
				}
			}


			//$output['result'][] = $i ;
			$output[] = $i ;

		}
	}

	usort($output, function($a, $b){
		if($a['folder'] && !$b['folder']) return -1;
		if(!$a['folder'] && $b['folder']) return 1;
		return strcmp($a['title'],$b['title']);
	});

    return $app->json($output) ;

})->bind("action_dir");

return $dirController ;