<?php
class Ml_View_Helper_StaticVersion extends Zend_View_Helper_Abstract
{
    protected static $_cacheFiles = array();
    
    protected static $_prePath = "";
    
    function __construct()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        self::$_prePath = mb_substr($config['cdn'], 0, -1);
        
        require APPLICATION_PATH . "/configs/static-versions.php";
        
        self::$_cacheFiles = $cacheFiles;
    }
    
    /**
     * For caching:
     * store data with an eternal lifetime
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
    public function staticVersion($path)
    {
        return
        self::$_prePath.((!array_key_exists($path, self::$_cacheFiles))
        ? 
        $path : self::$_cacheFiles[$path]);
    }
}
