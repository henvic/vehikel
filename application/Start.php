<?php
defined('EXTERNAL_LIBRARY_PATH')
    or define('EXTERNAL_LIBRARY_PATH', getenv('EXTERNAL_LIBRARY_PATH'));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH',
              dirname(__FILE__));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'development'));

defined('PUBLIC_PATH')
    or define('PUBLIC_PATH', realpath(APPLICATION_PATH.'/../public'));

defined('LIBRARY_PATH')
    or define('LIBRARY_PATH', realpath(APPLICATION_PATH.'/../library'));

set_include_path(
	EXTERNAL_LIBRARY_PATH . PATH_SEPARATOR .
    LIBRARY_PATH . PATH_SEPARATOR .
    APPLICATION_PATH . '/models' . PATH_SEPARATOR .
    get_include_path()
);

date_default_timezone_set(getEnv("DEFAULT_TIMEZONE"));

require APPLICATION_PATH . '/models/callFilter.php';

/** Zend_Application */
require EXTERNAL_LIBRARY_PATH . '/Zend/Application.php';

// Create application, bootstrap, and run
try {
$application = new Zend_Application(
    APPLICATION_ENV,
    getenv("PLIFK_CONF_FILE")
);

$application->bootstrap()->run();
} catch(Exception $e)
{
        header('HTTP/1.1 500 Internal Server Error');
        if(APPLICATION_ENV == "production")
        {
                header('HTTP/1.1 500 Internal Server Error');
                echo("Error loading application.\n");
                exit(1);
        }
        throw $e;
}
