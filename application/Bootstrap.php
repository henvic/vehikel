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
        
        $configArray = $this->getOptions();
        
        $registry->set('config', $configArray);
        
        $memcacheConfig = $configArray['cache']['backend']['memcache']['servers']['global'];
        
        $memCache = new Zend_Cache_Core(array('automatic_serialization' => true));
        $memCache->setBackend(new Zend_Cache_Backend_Memcached($memcacheConfig));
        
        $registry->set("memCache", $memCache);
    }
    
    protected function _initDatabase()
    {
        $this->bootstrap('db');
        
        $db = $this->getResource('db');
        
        Zend_Db_Table_Abstract::setDefaultMetadataCache("sysCache");
        
        Zend_Registry::getInstance()->set("database", $db);
    }
    
    protected function _initRequest()
    {
        switch (HOST_MODULE) {
            case "redirector" : {
                $redirector = new Ml_Resource_Redirector();
                $redirector->shortLink();
                exit;
            }
            
            case "default" : {
                $this->registerPluginResource("Ml_Resource_Uri")
                ->registerPluginResource("Ml_Resource_Mysession")
                ->registerPluginResource("Ml_Resource_Myview")
                ->registerPluginResource("Ml_Resource_Default")
                ;
                break;
            }
            
            case "api" : {
                $this
                ->registerPluginResource("Ml_Resource_Api")
                ->unregisterPluginResource("session")
                ->unregisterPluginResource("view")
                ->unregisterPluginResource("layout")
                ;
                break;
            }
            
            case "services" : {
                $this
                ->registerPluginResource("Ml_Resource_Services")
                ->unregisterPluginResource("session")
                ->unregisterPluginResource("view")
                ->unregisterPluginResource("layout")
                ;
                break;
            }
        }
    }
}
