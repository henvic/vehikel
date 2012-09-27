<?php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected $_sysCache = null;
    protected $_memCache = null;
    protected $_container = null;
    protected $_registry = null;

    protected function _initRun()
    {
        $registry = Zend_Registry::getInstance();
        $this->_registry = $registry;
        
        //sysCache initialized in Start.php
        $sysCache = $registry->get("sysCache");
        $this->_sysCache = $sysCache;
        Zend_Date::setOptions(array('cache' => $sysCache));
        Zend_Locale::setCache($sysCache);
        // @todo enable Zend_Translate caching if / when appropriate
        //Zend_Translate::setCache($sysCache);

        $configArray = $this->getOptions();
        
        $registry->set('config', $configArray);
        
        $memcacheConfig = $configArray['cache']['backend']['memcache']['servers']['global'];
        
        $memCache = new Zend_Cache_Core(array('automatic_serialization' => true));
        $memCache->setBackend(new Zend_Cache_Backend_Memcached($memcacheConfig));
        
        $registry->set("memCache", $memCache);
        $this->_memCache = $memCache;
    }
    
    protected function _initDatabase()
    {
        $this->bootstrap('db');

        $db = $this->getResource('db');
        
        Zend_Db_Table_Abstract::setDefaultMetadataCache("sysCache");

        $this->_registry->set("database", $db);

        $scCacheFile = CACHE_PATH . "/ServiceContainerCache.php";

        if ("development" != APPLICATION_ENV && file_exists($scCacheFile)) {
            require $scCacheFile;
            $container = new MyCachedContainer();
        } else {
            $container = new ContainerBuilder();
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
            $loader->load(APPLICATION_PATH . "/configs/services.yml");

            if ("development" != APPLICATION_ENV) {
                $container->compile();
                $dumper = new PhpDumper($container);
                file_put_contents($scCacheFile, $dumper->dump(array('class' => 'MyCachedContainer')));
            }
        }

        $container->set("sysCache", $this->_sysCache);
        $container->set("memCache", $this->_memCache);
        $memCache = $container->get("memCache");

        $this->_container = $container;
    }
    
    protected function _initRequest()
    {
        $this->_registry->set("sc", $this->_container);
        $this->_container->set("config", $this->_registry->get("config"));
        $this->_container->set("zendAuth", Zend_Auth::getInstance());
        $this->_container->set("memCache", $this->_registry->get("memCache"));

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
                ->registerPluginResource("Ml_Resource_Default");
                break;
            }
            
            case "api" : {
                $this
                ->registerPluginResource("Ml_Resource_Api")
                ->unregisterPluginResource("session")
                ->unregisterPluginResource("view")
                ->unregisterPluginResource("layout");
                break;
            }
            
            case "services" : {
                $this
                ->registerPluginResource("Ml_Resource_Services")
                ->unregisterPluginResource("session")
                ->unregisterPluginResource("view")
                ->unregisterPluginResource("layout");
                break;
            }
            
            default : throw new Exception("Invalid HOST_MODULE called on the application bootstrap");
        }

        $frontController = Zend_Controller_Front::getInstance();
        $router = $frontController->getRouter();
        $this->_container->set("router", $router);
    }
}
