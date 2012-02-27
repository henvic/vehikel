<?php
error_reporting(E_ALL | E_STRICT);

define('HOST_MODULE', 'testing');

defined('EXTERNAL_LIBRARY_PATH')
    or define('EXTERNAL_LIBRARY_PATH',
    realpath(getenv('EXTERNAL_LIBRARY_PATH')));

defined('CACHE_PATH')
    or define('CACHE_PATH', realpath(getenv('CACHE_PATH')));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH',
              realpath(dirname(__FILE__) . '/../application'));

defined('LIBRARY_PATH')
    or define('LIBRARY_PATH', realpath(APPLICATION_PATH . '/../library'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

set_include_path(implode(PATH_SEPARATOR,
array(EXTERNAL_LIBRARY_PATH, LIBRARY_PATH)));