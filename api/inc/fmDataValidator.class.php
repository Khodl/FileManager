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
	 * @param $type
	 * @param $path
	 * @return string
	 *
	 * Calculate key, depending on the type
	 */
	private function getKey($type,$path){
		return substr(md5(realpath($this->rootRelative.'/'.$path).$type.$this->key),0,10);
	}

	/**
	 * @param Request $request
	 * @param $type
	 *
	 * Check key and path
	 */
	public function checkPathKey(Request $request,$type){

		// Check request
		$this->checkRequestParameters(array('path','key'),$request);

		// Get parameters
		$path = $request->get('path');
		$givenKey = $request->get('key');

		// Check path
		$this->checkPath($path);

		// Check key
		$key = $this->{'get'.$type.'Key'}($path);
		if($key != $givenKey)
			$this->app->abort(403,"Key should be: ".$key." (and not ".$givenKey.")");

		return true ;
	}

	public function getActionUrl($route,$key,$parameters=array()){
		return $this->app['url_generator']->generate($route, array_merge(array('key' => $key),$parameters));
	}

	/**
	 * @param $key
	 * @param $path
	 * @return string
	 */
	private function getKeyURL($key,$path){
		return "./explorer/".$key."/".$path ;
	}

	// Key calculations
	public function getSaveKey($path) {return $this->getKey('save',$path);}
	public function getLoadKey($path) {return $this->getKey('load',$path);}
	public function getDeleteKey($path) {return $this->getKey('delete',$path);}
	public function getMkDirKey($path) {return $this->getKey('mkDir',$path);}
	public function getDirKey($path) {return $this->getKey('dir',$path);}
	public function getRmDirKey($path) {return $this->getKey('rmDir',$path);}
	public function getCreateKey($path) {return $this->getKey('create',$path);}

} 