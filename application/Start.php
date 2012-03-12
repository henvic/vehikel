<?php

define("APPLICATION_PATH", realpath(__DIR__));
define("LIBRARY_PATH", realpath(APPLICATION_PATH . "/../library"));
require __DIR__ . "/configs/Environment.php.dist";

set_include_path(implode(PATH_SEPARATOR,
array(EXTERNAL_LIBRARY_PATH, LIBRARY_PATH, get_include_path())));

require EXTERNAL_LIBRARY_PATH . '/Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance()->registerNamespace('Ml_');

$resourceLoader =
new Zend_Loader_Autoloader_Resource(array('basePath' => APPLICATION_PATH,
'namespace' => 'Ml'));

$resourceLoader->addResourceType('form', 'forms/', 'Form')
->addResourceType('models', 'models/Ml/', 'Model')
->addResourceType('resources', 'resources/', 'Resource');

require EXTERNAL_LIBRARY_PATH . '/Zend/Registry.php';
require EXTERNAL_LIBRARY_PATH . '/Zend/Cache/Core.php';

//@todo redo the library/Ml/RouteModule.php the proper way
/** Ml_Application */
require EXTERNAL_LIBRARY_PATH . '/Zend/Application.php';
require LIBRARY_PATH . '/Ml/Application.php';

$sysCache = new Zend_Cache_Core(array('automatic_serialization' => true));
$sysCache->setBackend(new Zend_Cache_Backend_File(array("cache_dir" => CACHE_PATH)));

Zend_Registry::getInstance()->set("sysCache", $sysCache);

// Create application, bootstrap, and run
try {
    $application = new Ml_Application(APPLICATION_ENV, APPLICATION_CONF_FILE, 
    $sysCache, true);
    
    $application->bootstrap()->run();
} catch (Exception $e) {
    //don't throw it because there's no exception wrapper to treat it
    new Ml_Model_Exception("Exception: " . $e->getMessage(), $e->getCode(), $e);
}
