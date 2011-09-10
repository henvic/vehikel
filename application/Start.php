<?php

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


set_include_path(EXTERNAL_LIBRARY_PATH . PATH_SEPARATOR .
    LIBRARY_PATH . PATH_SEPARATOR .
    APPLICATION_PATH . '/models' . PATH_SEPARATOR //.
    //get_include_path()
);

date_default_timezone_set(getEnv("DEFAULT_TIMEZONE"));

require EXTERNAL_LIBRARY_PATH . '/Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

require EXTERNAL_LIBRARY_PATH . '/Zend/Registry.php';
require EXTERNAL_LIBRARY_PATH . '/Zend/Cache/Core.php';

//@todo refactory callFilter.php
//@todo redo the library/ML/RouteModule.php the proper way
require APPLICATION_PATH . '/models/callFilter.php';

/** Ml_Application */
require EXTERNAL_LIBRARY_PATH . '/Zend/Application.php';
require LIBRARY_PATH . '/ML/Application.php';

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
