<?php
class Ml_View_Helper_staticVersion extends Zend_View_Helper_Abstract
{
    protected static $_cacheFiles = array();
    
    protected static $_prePath = "";
    
    protected function loadVersions()
    {
        return require APPLICATION_PATH . "/configs/StaticVersions.php";
    }

    public function __construct()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        self::$_prePath = mb_substr($config['cdn'], 0, -1);
        self::$_cacheFiles = $this->loadVersions();
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
        if ((!array_key_exists($path, self::$_cacheFiles))) {
            return self::$_prePath . $path;
        } else {
            return self::$_prePath . self::$_cacheFiles[$path];
        }
    }
}
