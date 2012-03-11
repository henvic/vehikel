<?php
// Define application environment configuration
//This configuration file might be created by bin/install
define("APPLICATION_ENV", (defined("PHPUnit_MAIN_METHOD")) ? "testing" : "production");
define("EXTERNAL_LIBRARY_PATH", realpath(__DIR__ . "/../../vendor"));
define("CACHE_PATH", realpath(__DIR__ . "/../../data/cache"));
define("APPLICATION_CONF_FILE", realpath(__DIR__ . "/../../application/configs/application.ini.dist"));
date_default_timezone_set("GMT");
