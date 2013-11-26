<?php
/**
 * User: khodl
 * Date: 26.11.13
 * Time: 09:33
 */

class fmDataValidator {

	private $root,$app ;
	private $key = "";


	/**
	 * @param $app A Silex app
	 */
	public function __construct($app){
		$this->app = $app ;
	}

	/**
	 * @param $key The salt key used for the folder keys
	 */
	public function setKey($key){
		$this->key = $key ;
	}

	/**
	 * @param $root String containing the main folder
	 * @throws Exception When the folder doesn't exist
	 */
	public function setRoot($root){
		$exception = "Root '$root' doesn't exist";
		$root = realpath($root) ;
		if(! $root) throw new Exception($exception);
		$this->root = $root ;
	}

	/**
	 * @param $parameters array of parameters
	 * @param $request a Silex Request
	 * @return bool
	 */
	public function checkRequestParameters($parameters,$request){
		foreach($parameters AS $name){
			if(! $request->get($name)) return $this->app->abort(400,"Parameter '$name' not found"); ;
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

		$fullPath = realpath($path) ;
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
		return substr(md5($path.$type.$this->key),0,10);
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
	private function getWriteKey($path) {return $this->getKey('write',$path);}
	private function getReadKey($path) {return $this->getKey('read',$path);}

	// Path URL
	public function getWriteURL($path){ return $this->getKeyURL($this->getWriteKey($path),$path) ; }
	public function getReadURL($path){ return $this->getKeyURL($this->getReadKey($path),$path) ; }

} 