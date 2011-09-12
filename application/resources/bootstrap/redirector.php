<?php
/**
 * This is NOT a plugin
 * It is here just for modularization
 * 
 * It should be loaded for default an API modules
 * 
 */

$this->unregisterPluginResource("session");
$this->unregisterPluginResource("view");
$this->unregisterPluginResource("layout");
$this->unregisterPluginResource("FrontController");

$registry = Zend_Registry::getInstance();

$config = $registry->get("config");

$uri = $_SERVER['REQUEST_URI'];

if ($uri == '/') {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: http://".$config['webhost']."/");
    exit();
}

//clear the first and the last '/'
if (mb_substr($uri, -1) == '/') {
    $uri = mb_substr($uri, 1, -1);
} else {
    $uri = mb_substr($uri, 1);
}

$id =  base58_decode($uri);

if ($id) {
    //Is it a valid share ID?
    $share = new Ml_Model_Share();
    $people = new Ml_Model_People();
    
    $shareInfo = $share->getById($id);
    
    if ($shareInfo) {
        $userInfo = $people->getById($shareInfo['byUid']);
        
        $link =
        "http://" . $config['webhost'] . "/" .
         urlencode($userInfo['alias']) . "/" . $shareInfo['id'];
        
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: ".$link);
        exit(); //nothing more to do
    }
}

//If nothing matches
$link = "http://" . $config['webhost'] . "/not-found/" . urlencode(utf8_encode($uri));
header("Location: ".$link);
exit(); //nothing more to do