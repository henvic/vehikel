<?php
/**
 * This is a example configuration file
 * Make a copy of it to Environment.php.dist
 * Or use the bin/install script to create and configure it
 * 
 * The values below are set with what you most likely want for development
 */

define("APPLICATION_ENV", (defined("PHPUnit_MAIN_METHOD")) ? "testing" : "development");
define("EXTERNAL_LIBRARY_PATH", realpath(__DIR__ . "/../../vendor"));
define("CACHE_PATH", realpath(__DIR__ . "/../../data/cache"));
define("APPLICATION_CONF_FILE", realpath(__DIR__ . "/../../application/configs/application.ini.dist"));
date_default_timezone_set("GMT");
