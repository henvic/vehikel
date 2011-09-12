<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initRun()
    {
        $registry = Zend_Registry::getInstance();
        
        //sysCache initialized in Start.php
        $sysCache = $registry->get("sysCache");
        Zend_Date::setOptions(array('cache' => $sysCache));
        Zend_Locale::setCache($sysCache);
        Zend_Translate::setCache($sysCache);
        
        if (HOST_MODULE == 'default' || HOST_MODULE == 'api') {
            $this->registerPluginResource("Uri");
        }
        
        $configArray = $this->getOptions();
        
        $registry->set('config', $configArray);
        
        $memcacheConfig = $configArray['cache']['backend']['memcache']['servers']['global'];
        
        $memCache = new Zend_Cache_Core(array('automatic_serialization' => true));
        $memCache->setBackend(new Zend_Cache_Backend_Memcached($memcacheConfig));
        $registry->set("memCache", $memCache);
        
        Zend_Loader_Autoloader::getInstance()->registerNamespace('Ml_');
    }
    
    protected function _initDatabase()
    {
        try {
            $this->bootstrap('db');
            
            $db = $this->getResource('db');
            
            Zend_Db_Table_Abstract::setDefaultMetadataCache("sysCache");
            
            Zend_Registry::getInstance()->set("database", $db);
        } catch(Exception $e)
        {
            echo "Error connecting to database.\n";
            throw $e;
        }
    }
    
    protected function _initRequest()
    {
        require APPLICATION_PATH . '/resources/Request' . HOST_MODULE . 'NotPlugin.php';
    }
}
