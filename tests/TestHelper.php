<?php
error_reporting(E_ALL | E_STRICT);

define("APPLICATION_PATH", realpath(__DIR__ . "/../application"));
define("LIBRARY_PATH", realpath(APPLICATION_PATH . "/../library"));
require APPLICATION_PATH . "/configs/Environment.php.dist";

define('HOST_MODULE', 'testing');

set_include_path(implode(PATH_SEPARATOR,
array(EXTERNAL_LIBRARY_PATH, LIBRARY_PATH, get_include_path())));

require EXTERNAL_LIBRARY_PATH . '/Zend/Loader/Autoloader.php';

Zend_Loader_Autoloader::getInstance()->registerNamespace('Ml_');

$resourceLoader =
new Zend_Loader_Autoloader_Resource(array('basePath' => APPLICATION_PATH,
'namespace' => 'Ml'));

$resourceLoader->addResourceType('form', 'forms/', 'Form')
->addResourceType('models', 'models/ML/', 'Model')
->addResourceType('resources', 'resources/', 'Resource');