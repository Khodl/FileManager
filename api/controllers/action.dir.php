<?php

use Symfony\Component\HttpFoundation\Request;

$dirController = $app['controllers_factory'];

$dirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$isReadOnly = $fmValidator->checkPathKey($request,"Dir") ;

	$path = $request->get('path') ;

	$output = array();
	$dir = opendir($fmValidator->getWorkingPath($path));
	while($file = readdir($dir)){
		//if($file != '..'){
		if($file{0} != '.' OR $file == '.'){

			$fullPath = $path.'/'.$file ;
			$wp = $fmValidator->getWorkingPath($fullPath) ;
			$fullPath = $fmValidator->getPublicPath($fullPath);

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

				$readOnlyURL = $fmValidator->getActionUrl("action_dir",$fmValidator->getDirKeyRO($fullPath),array("path"=>$fullPath)) ;
				$i['lazy'] = ($file{0} != '.') ;
				if(! $isReadOnly){
					$i['mkdirURL'] = $fmValidator->getActionUrl("action_mkdir",$fmValidator->getMkDirKey($fullPath),array("path"=>$fullPath)) ;
					$i['rmdirURL'] = $fmValidator->getActionUrl("action_rmdir",$fmValidator->getRmDirKey($fullPath),array("path"=>$fullPath)) ;
					$i['createURL'] = $fmValidator->getActionUrl("action_create",$fmValidator->getCreateKey($fullPath),array("path"=>$fullPath)) ;
					$i['dirURL'] = $fmValidator->getActionUrl("action_dir",$fmValidator->getDirKey($fullPath),array("path"=>$fullPath)) ;
					$i['setViewDataURL'] = $fmValidator->getActionUrl("views_setviewdata",$fmValidator->getSetViewDataKey($fullPath),array("path"=>$fullPath)) ;
					$i['setDataDataURL'] = $fmValidator->getActionUrl("views_setdatadata",$fmValidator->getSetDataDataKey($fullPath),array("path"=>$fullPath)) ;
					$i['getViewBranchesURL'] = $fmValidator->getActionUrl("views_getviewbranches",$fmValidator->getGetViewBranchesKey($fullPath),array("path"=>$fullPath)) ;
					$i['getViewBranchesReadOnlyURL'] = $fmValidator->getActionUrl("views_getviewbranches",$fmValidator->getGetViewBranchesKeyRO($fullPath),array("path"=>$fullPath)) ;
					$i['getDataBranchesURL'] = $fmValidator->getActionUrl("views_getdatabranches",$fmValidator->getGetDataBranchesKey($fullPath),array("path"=>$fullPath)) ;
					$i['getDataBranchesReadOnlyURL'] = $fmValidator->getActionUrl("views_getdatabranches",$fmValidator->getGetDataBranchesKeyRO($fullPath),array("path"=>$fullPath)) ;
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