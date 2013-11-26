<?php

use Symfony\Component\HttpFoundation\Request;

$dirController = $app['controllers_factory'];

$dirController->match('/', function (Request $request) use ($app,$fmValidator) {

	$fmValidator->checkRequestParameters(array('path'),$request);
	$path = $request->get('path');
	$fmValidator->checkFolder($path);

	$output = array();
	$dir = opendir($path);
	while($file = readdir($dir)){
		if($file{0} != '.'){
			$fullPath = $path.'/'.$file ;
			$output['result'][] = array(
				'name' => $file,
				'file' => $fullPath,
				'isFolder' => is_dir($fullPath),
				'lastEdition' => filectime($fullPath),
				'writeURL' => $fmValidator->getWriteURL($fullPath),
				'readURL' => $fmValidator->getReadURL($fullPath),
			);
		}
	}

    return $app->json($output) ;

});

return $dirController ;