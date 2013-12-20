<?php
/**
 * User: khodl
 * Date: 26.11.13
 * Time: 09:33
 */

use \Symfony\Component\HttpFoundation\Request ;
use Silex\Application ;

class fmDataValidator {

	private $root,$rootRelative ;
	private $app ;
	private $key = "";

	/**
	 * @param $app A Silex app
	 */
	public function __construct(Application $app){
		$this->app = $app ;
	}

	/**
	 * @param $key The salt key used for the folder keys
	 */
	public function setKey($key){
		$this->key = $key ;
	}

	/**
	 * @param $path
	 * @return string
	 *
	 * Should not be displayed
	 */
	public function getWorkingPath($path){
		return realpath($this->rootRelative.'/'.$path);
	}

	/**
	 * @param $root String containing the main folder
	 * @throws Exception When the folder doesn't exist
	 */
	public function setRoot($rootRelative){
		$root = realpath($rootRelative) ;
		if(! $root) throw new Exception("Root '$rootRelative' doesn't exist");
		$this->root = $root ;
		$this->rootRelative = $rootRelative ;
	}

	/**
	 * @param $request
	 * @return string
	 */
	public function getPath($request){
		$this->checkRequestParameters(array('path'),$request);
		return $this->rootRelative.$request->get('path');
	}

	/**
	 * @param $parameters array of parameters
	 * @param $request a Silex Request
	 * @return bool
	 */
	public function checkRequestParameters(Array $parameters,Request $request){
		foreach($parameters AS $name){
			if(! $request->get($name)) $this->app->abort(400,"Parameter '$name' not found");
		}
		return true ;
	}

	/**
	 * @param $path
	 * @return bool
	 *
	 * Check if a path exists, and if it is allowed
	 */
	public function checkPath($path){

		$abort = function() use ($path){
			$this->app->abort(404,"Path '$path' is not found");
		};

		$fullPath = realpath($this->rootRelative.$path) ;
		if(! $fullPath) $abort() ;
		if(substr($fullPath,0,strlen($this->root)) != $this->root) $abort() ;

		return true ;
	}

	/**
	 * @param $path
	 * @return bool
	 *
	 * Check if a file exists, and is a folder
	 */
	function checkFolder($path){
		$this->checkPath($path);
		if(! is_dir($path))
			$this->app->abort(400,"File '$path' is not an folder");
		return true ;
	}

	/**
	 * @param $path
	 */
	function getPublicPath($path){
		$name = substr(realpath($this->rootRelative.$path),
			(
				strlen(realpath($this->rootRelative))
				-
				strlen(realpath($this->rootRelative-$path))
			)
		) ;
		if(! $name) $name = '/' ;
		return $name ;
	}

	/**
	 * @param $type
	 * @param $path
	 * @return string
	 *
	 * Calculate key, depending on the type
	 */
	private function getKey($type,$path){
		//$this->app->abort(404,$path);
		return substr(md5(realpath($this->rootRelative.'/'.$path).$type.$this->key),0,10);
	}

	/**
	 * @param Request $request
	 * @param $type
	 *
	 * Check key and path
	 */
	public function checkPathKey(Request $request,$type,$abortIfWrongKey = true){

		// Check request
		$this->checkRequestParameters(array('path','key'),$request);

		// Get parameters
		$path = $request->get('path');
		$givenKey = $request->get('key');

		// Check path
		$this->checkPath($path);

		// Check key
		$method = 'get'.$type.'Key' ;
		if(method_exists($this,$method."RO"))
			if($givenKey == $this->{$method."RO"}($path))
				//$this->app->abort(403,"Read only");
				return true ;

		if(! method_exists($this,$method)) throw new Exception("Cannot get key with '$method'");
		$key = $this->{$method}($path);

		//$this->app->abort(404,"Checking with $method");

		if($key != $givenKey){
			//(! $abortIfWrongKey) return false ;
			//$this->app->abort(403,"Key should be: ".$key." (and not ".$givenKey.")");
			$this->app->abort(403,"Key '$givenKey' is wrong $key"); // Todo: hide key
		}

		return false ;
	}

	/**
	 * @param $var
	 * @param $parametername
	 * @return bool
	 *
	 * Detects special chars and abort if necessary
	 */
	public function checkFilename($var,$parametername){
		if(preg_match("#[^a-z0-9_\.-]#i",$var) OR $var{0} == '.')
			$this->app->abort(403,"'$parametername' is not valid.");
		return true ;
	}

	/**
	 * @param $route
	 * @param $key
	 * @param array $parameters
	 * @return mixed
	 *
	 * Generate URL with a key
	 */
	public function getActionUrl($route,$key,$parameters=array()){
		return $this->app['url_generator']->generate($route, array_merge(array('key' => $key),$parameters));
	}

	/**
	 * Key calculations
	 * Add RO when should it be interpreted as a read only key
	 */

	// File Manager
	public function getSaveKey($path) {return $this->getKey('save',$path);}
	public function getLoadKey($path) {return $this->getKey('load',$path);}
	public function getDeleteKey($path) {return $this->getKey('delete',$path);}
	public function getMkDirKey($path) {return $this->getKey('mkDir',$path);}
	public function getDirKey($path) {return $this->getKey('dir',$path);}
	public function getDirKeyRO($path) {return $this->getKey('dirro',$path);}
	public function getRmDirKey($path) {return $this->getKey('rmDir',$path);}
	public function getCreateKey($path) {return $this->getKey('create',$path);}

	// Views
	public function getGetViewKey($path) {return $this->getKey('getView',$path);}
	public function getGetViewKeyRO($path) {return $this->getKey('getViewRO',$path);}
	public function getGetDataKey($path) {return $this->getKey('getData',$path);}
	public function getGetDataKeyRO($path) {return $this->getKey('getDataRO',$path);}
	public function getSetViewDataKey($path) {return $this->getKey('setViewData',$path);}
	public function getSetDataDataKey($path) {return $this->getKey('setDataData',$path);}
	public function getGetViewBranchesKey($path) {return $this->getKey('getViewBranches',$path);}
	public function getGetViewBranchesKeyRO($path) {return $this->getKey('getViewBranchesRO',$path);}
	public function getGetDataBranchesKey($path) {return $this->getKey('getDataBranches',$path);}
	public function getGetDataBranchesKeyRO($path) {return $this->getKey('getDataBranchesRO',$path);}
	public function getGetViewRevisionsKey($path) {return $this->getKey('getViewRevisions',$path);}
	public function getGetViewRevisionsKeyRO($path) {return $this->getKey('getViewRevisionsRO',$path);}
	public function getGetDataRevisionsKey($path) {return $this->getKey('getDataRevisions',$path);}
	public function getGetDataRevisionsKeyRO($path) {return $this->getKey('getDataRevisionsRO',$path);}

} 