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

if($uri == '/')
{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: http://".$config->webhost."/");
	exit();
}

//clear the first and the last '/'
if(mb_substr($uri, -1) == '/') $uri = mb_substr($uri, 1, -1);
else $uri = mb_substr($uri, 1);

/*$part = mb_substr($uri, 0, 1);
if($part == '~')
{
	//permanent redirect to webhost/shares/uri
	$link = "http://".$config->webhost."/~".urlencode(utf8_encode($uri));
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ".$link);
	exit();
}*/

$id =  base58_decode($uri);

if($id)//is it an ID?
{
	$Share = new ML_Share();
	$People = new ML_People();
	
	$shareInfo = $Share->getById($id);
	
	if($shareInfo)
	{
		$userInfo = $People->getById($shareInfo['byUid']);
		
		/*$router = new Zend_Controller_Router_Rewrite();
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/defaultRoutes.ini');
		$router->addConfig($config, 'routes');
		
		$host = $config->webhost;
		
		$link = "http://".$host.$router->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage");
		*/
		
		$link = "http://" . $config->webhost . "/" . urlencode($userInfo['alias']) . "/" . $shareInfo['id'];
		
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ".$link);
		exit();
	}
}

//If nothing matches
//$link = "http://".$config->webhost."/".urlencode(utf8_encode($uri));
$link = "http://".$config->webhost."/not-found/".urlencode(utf8_encode($uri));
header("Location: ".$link);
exit();
