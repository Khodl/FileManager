<?php
/**
 * User: khodl
 * Date: 17/12/13
 * Time: 14:22
 */

class viewManager {

	private $app, $request, $fmValidator,$mode,$ucMode ;

	/**
	 * @param \Silex\Application $app
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param fmDataValidator $fmValidator
	 */
	function __construct(Silex\Application $app, \Symfony\Component\HttpFoundation\Request $request, fmDataValidator $fmValidator){
		$this->app = $app;
		$this->request = $request ;
		$this->fmValidator = $fmValidator ;

		return $this ;
	}


	/**
	 * @param $mode
	 * @return $this
	 * @throws Exception
	 */
	public function setMode($mode){
		if(! in_array($mode,array('view','data'))) throw new Exception("Wrong mode");
		$this->mode = $mode ;
		$this->ucMode = $this->mode ;
		$this->ucMode{0} = strtoupper($this->ucMode{0});
		return $this ;
	}


	/**
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function runGet(){

		$request = $this->request ;
		$fmValidator = $this->fmValidator ;
		$app = $this->app ;

		$isReadOnly = $fmValidator->checkPathKey($request,"Get".$this->ucMode);

		$fmValidator->checkRequestParameters(array('name'),$request);

		$branchName = $request->get('branch');
		if(! $branchName)
			$branchName = $app['config']['branch']['defaultBranchName'] ;

		$fmValidator->checkFilename($branchName,'branch');
		$fmValidator->checkFilename($branchName,'name');

		$dirName = $app['config'][$this->mode]['folderName'];
		$viewExt = $app['config'][$this->mode]['extension'];

		$path = $fmValidator->getWorkingPath($request->get('path')).'/'.$dirName.'/'.$branchName.'/'.$request->get('name').'.'.$viewExt ;
		if(! file_exists($path)){
			$app->abort(404,"File '$path' not found");
		}

		$ro = "" ;
		if($isReadOnly) $ro = "RO" ;

		$response = array(
			'branchName' => $branchName,
			'revisionName' => $request->get('name'),
			'isReadOnly' => $isReadOnly,
			'getBranchesURL' => $fmValidator->getActionUrl("views_get".$this->mode."branches",$fmValidator->{"getGet".$this->ucMode."BranchesKey".$ro}($request->get('path')),array("path"=>$fmValidator->getPublicPath($request->get('path')))),
			'getRevisionsURL' => $fmValidator->getActionUrl("views_get".$this->mode."revisions",$fmValidator->{"getGet".$this->ucMode."RevisionsKey".$ro}($request->get('path')),array("path"=>$fmValidator->getPublicPath($request->get('path')),'branch'=>$branchName)),
		);

		if(! $isReadOnly){
			$response['setDataURL'] = $fmValidator->getActionUrl("views_set".$this->mode."data",$fmValidator->{"getSet".$this->ucMode."DataKey"}($request->get('path')),array("path"=>$fmValidator->getPublicPath($request->get('path')),"branch"=>$branchName)) ;
		}

		$response['data'] = json_decode(file_get_contents($path));

		return $app->json($response) ;

	}


	/**
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
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
			'getURL' => $fmValidator->getActionUrl("views_get".$this->mode,$fmValidator->{"getGet".$this->ucMode."Key"}($path),array("path"=>$fmValidator->getPublicPath($path),"name"=>$name,"branch"=>$branchName)),
			'getBranchesURL' => $fmValidator->getActionUrl("views_get".$this->mode."branches",$fmValidator->{"getGet".$this->ucMode."BranchesKey".$ro}($request->get('path')),array("path"=>$fmValidator->getPublicPath($request->get('path')))),
			'getRevisionsURL' => $fmValidator->getActionUrl("views_get".$this->mode."revisions",$fmValidator->{"getGet".$this->ucMode."RevisionsKey".$ro}($request->get('path')),array("path"=>$fmValidator->getPublicPath($request->get('path')),'branch'=>$branchName)),
		)) ;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function runGetBranches(){

		$request = $this->request ;
		$fmValidator = $this->fmValidator ;
		$app = $this->app ;

		$dirName = $app['config'][$this->mode]['folderName'] ;


		$isReadOnly = $fmValidator->checkPathKey($request,"Get".$this->ucMode."Branches");
		$pathfile = $fmValidator->getWorkingPath($request->get('path').'/'.$dirName) ;
		$path = $fmValidator->getPublicPath($request->get('path'));

		$branches = array();
		$ro = "";
		if($isReadOnly) $ro = "RO";

		if($r = opendir($pathfile)){
			while($f = readdir($r)){
				if($f{0} != '.' && is_dir($pathfile.'/'.$f)) {
					$i = array(
						'name' => $f,
						'getRevisionsURL' => $fmValidator->getActionUrl("views_get".$this->mode."revisions",$fmValidator->{"getGet".$this->ucMode."RevisionsKey".$ro}($path),array("path"=>$path,"branch"=>$f))
					);
					$branches[] = $i ;
				}
			}
		}

		$r = array(
			'isReadOnly' => $isReadOnly,
			'branches' => $branches
		);

		return $app->json($r);
	}


	/**
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function runGetRevisions(){

		$request = $this->request ;
		$fmValidator = $this->fmValidator ;
		$app = $this->app ;

		$isReadOnly = $fmValidator->checkPathKey($request,"Get".$this->ucMode."Revisions");

		$fmValidator->checkRequestParameters(array('branch'),$request);
		$dirName = $app['config'][$this->mode]['folderName'] ;
		$ext = $app['config'][$this->mode]['extension'] ;
		$branchName = $request->get('branch');
		$fmValidator->checkFilename($branchName,'branch');

		$fullpath = $fmValidator->getWorkingPath($request->get('path')).'/'.$dirName.'/'.$branchName ;
		if(! $d = opendir($fullpath))
			$app->abort(404,"Branch '$branchName' doesn't exist");

		$r = array();
		$ro = "";
		if($isReadOnly) $ro = "RO";

		while($f = readdir($d)){
			if($f{0} != '.'){
				// Is correct extension
				if(strpos($f,$ext) == strlen($f)-strlen($ext)){
					$name = substr($f,0,-1*strlen($ext)-1) ;
					$i = array(
						'name' => $name,
						'timestamp' => floor($name/100),
						'getURL' => $fmValidator->getActionUrl("views_get".$this->mode,$fmValidator->{"getGet".$this->ucMode."Key".$ro}($request->get('path')),array("path"=>$fmValidator->getPublicPath($request->get('path')),'branch'=>$branchName,'name'=>$name)),
					);

					if(! $isReadOnly){
						$i['setDataURL'] = $fmValidator->getActionUrl("views_set".$this->mode."data",$fmValidator->{"getSet".$this->ucMode."DataKey"}($request->get('path')),array("path"=>$fmValidator->getPublicPath($request->get('path')))) ;
					}

					$r[] = $i ;
				}
			}
		}

		return $app->json(array(
			'isReadOnly' => $isReadOnly,
			'revisions' => $r
		));
	}

} 