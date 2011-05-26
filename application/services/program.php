#!/usr/local/zend/bin/php -q
<?php
//services/program.php
//should be called by ./program.php, use chmod +x program.php
define("HOST_MODULE", "services");//not really a 'host' here, but whatever

define("APPLICATION_PATH", getEnv("APPLICATION_PATH"));

require_once APPLICATION_PATH."/Start.php";