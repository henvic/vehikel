<?php
class My_View_Helper_staticversion extends Zend_View_Helper_Abstract
{
	protected static $_cachefiles = array();
	
	protected static $_pre_path = "";
	
	function __construct()
	{
		$registry = Zend_Registry::getInstance();
		self::$_pre_path = mb_substr($registry->get("config")->services->S3->designBucketAddress, 0, -1);
		
		require APPLICATION_PATH . "/configs/static-versions.php";
		
		self::$_cachefiles = $cache_files;
	}
	
	/**
	 * For caching:
	 * store data with an eternal live
	 * so when there is a need to change it
	 * save it with a new name
	 * 
	 * By doing that we can save bandwidth
	 * This function is to set the ?version
	 * it is being used right now.
	 * 
	 * @param $path
	 * @return path to the last version of the element
	 */
	public function staticversion($path)
	{
		//if(APPLICATION_ENV != "production") return $path;
		return
		self::$_pre_path.((!array_key_exists($path, self::$_cachefiles))
		? 
		$path : self::$_cachefiles[$path]);
	}
}
