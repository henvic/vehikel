<?php

date_default_timezone_set(getEnv("DEFAULT_TIMEZONE"));

defined('EXTERNAL_LIBRARY_PATH')
    or define('EXTERNAL_LIBRARY_PATH',
    realpath(getenv('EXTERNAL_LIBRARY_PATH')));


defined('CACHE_PATH')
    or define('CACHE_PATH', realpath(getenv('CACHE_PATH')));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH',
              dirname(__FILE__));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              getenv('APPLICATION_ENV'));

defined('PUBLIC_PATH')
    or define('PUBLIC_PATH', realpath(APPLICATION_PATH.'/../public'));

defined('LIBRARY_PATH')
    or define('LIBRARY_PATH', realpath(APPLICATION_PATH.'/../library'));

// don't use get_include_path() here to include the default include path of the
// server so you always know exactly what's loaded or not
set_include_path(implode(PATH_SEPARATOR,
array(EXTERNAL_LIBRARY_PATH, LIBRARY_PATH)));

require EXTERNAL_LIBRARY_PATH . '/Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance()->registerNamespace('Ml_');

$resourceLoader =
new Zend_Loader_Autoloader_Resource(array('basePath' => APPLICATION_PATH,
'namespace' => 'Ml'));

$resourceLoader->addResourceType('form', 'forms/', 'Form')
->addResourceType('models', 'models/ML/', 'Model');

require EXTERNAL_LIBRARY_PATH . '/Zend/Registry.php';
require EXTERNAL_LIBRARY_PATH . '/Zend/Cache/Core.php';

//@todo refactory callFilter.php
//@todo redo the library/Ml/RouteModule.php the proper way
require APPLICATION_PATH . '/models/callFilter.php';

/** Ml_Application */
require EXTERNAL_LIBRARY_PATH . '/Zend/Application.php';
require LIBRARY_PATH . '/Ml/Application.php';

require EXTERNAL_LIBRARY_PATH."/php-on-couch/lib/couch.php";
require EXTERNAL_LIBRARY_PATH."/php-on-couch/lib/couchClient.php";
require EXTERNAL_LIBRARY_PATH."/php-on-couch/lib/couchDocument.php";


$sysCache = new Zend_Cache_Core(array('automatic_serialization' => true));
$sysCache->setBackend(new Zend_Cache_Backend_File(array("cache_dir" => CACHE_PATH)));

Zend_Registry::getInstance()->set("sysCache", $sysCache);

// Create application, bootstrap, and run
try {
$application = new Ml_Application(
    APPLICATION_ENV,
    getenv("PLIFK_CONF_FILE"),
    $sysCache,
    true
);

$application->bootstrap()->run();
} catch(Exception $e)
{
        header('HTTP/1.1 500 Internal Server Error');
        if (APPLICATION_ENV == "production") {
                header('HTTP/1.1 500 Internal Server Error');
                echo("Error loading application.\n");
                exit(1);
        }
        throw $e;
}
