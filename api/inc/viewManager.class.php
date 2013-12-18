<?php
/**
 * User: khodl
 * Date: 17/12/13
 * Time: 14:22
 */

class viewManager {

	private $app, $request, $fmValidator,$mode,$ucMode ;

	function __construct(Silex\Application $app, \Symfony\Component\HttpFoundation\Request $request, fmDataValidator $fmValidator){
		$this->app = $app;
		$this->request = $request ;
		$this->fmValidator = $fmValidator ;

		return $this ;
	}

	public function setMode($mode){
		if(! in_array($mode,array('view','data'))) throw new Exception("Wrong mode");
		$this->mode = $mode ;
		$this->ucMode = $this->mode ;
		$this->ucMode{0} = strtoupper($this->ucMode{0});
		return $this ;
	}

	public function runGet(){

		$request = $this->request ;
		$fmValidator = $this->fmValidator ;
		$app = $this->app ;

		$fmValidator->checkPathKey($request,"Get".$this->ucMode);
		$fmValidator->checkRequestParameters(array('name'),$request);

		$branchName = $request->get('branch');
		if(! $branchName)
			$branchName = $app['config']['branch']['defaultBranchName'] ;

		$fmValidator->checkFilename($branchName,'branch');
		$fmValidator->checkFilename($branchName,'name');

		$dirName = $app['config'][$this->mode]['folderName'] ;
		$viewExt = $app['config'][$this->mode]['extension'] ;

		$path = $fmValidator->getWorkingPath($request->get('path')).'/'.$dirName.'/'.$branchName.'/'.$request->get('name').'.'.$viewExt ;
		if(! file_exists($path)){
			$app->abort(404,"File '$path' not found");
		}

		return $app->json(array(
			'_branchName' => $branchName,
			'_revisionName' => basename($path),
			'_data' => json_decode(file_get_contents($path))
		)) ;

	}

	public function runSetData(){

		$request = $this->request ;
		$fmValidator = $this->fmValidator ;
		$app = $this->app ;

		$fmValidator->checkPathKey($request,"Set".$this->ucMode."Data");

		$branchName = $request->get('branch');
		if(! $branchName)
			$branchName = $app['config']['branch']['defaultBranchName'] ;

		$name = microtime(true)*100;

		$fmValidator->checkFilename($branchName,'branch');

		$dirName = $app['config'][$this->mode]['folderName'] ;
		$viewExt = $app['config'][$this->mode]['extension'] ;

		// Folder creation when needed
		$pathfile = $fmValidator->getWorkingPath($request->get('path')).'/'.$dirName ;
		if(! file_exists($pathfile)) mkdir($pathfile) ;
		$pathfile .= '/'.$branchName ;
		if(! file_exists($pathfile)) mkdir($pathfile) ;

		// Checking folder creation
		if(! file_exists($pathfile))
			$app->abort(500,"Impossible to create the branch folder");

		$pathfile = $fmValidator->getWorkingPath($request->get('path')).'/'.$dirName.'/'.$branchName.'/'.$name.'.'.$viewExt ;
		$content = json_encode($_POST) ;

		// Writing file
		file_put_contents($pathfile,$content);
		if(! file_exists($pathfile)) $app->abort(500,"Cannot write in file");

		$path = $fmValidator->getPublicPath($request->get('path'));

		return $app->json(array(
			'name' => $name,
			'get'. $this->ucMode .'URL' => $fmValidator->getActionUrl("views_get".$this->mode,$fmValidator->{"getGet".$this->ucMode."Key"}($path),array("path"=>$fmValidator->getPublicPath($path),"name"=>$name,"branch"=>$branchName))
			//'path' => $fmValidator->getPublicPath($path)
		)) ;
	}

} 