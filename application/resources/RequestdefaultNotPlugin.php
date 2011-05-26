<?php
/**
 * This is NOT a plugin
 * It is here just for modularization
 * 
 * It should be loaded for default an API modules
 * 
 */

#
class MyReservedUsersPlugin extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $registry = Zend_Registry::getInstance();
	    $config = $registry->get("config");
    	$params = $request->getParams();
    	
            //avoid rendering /index.php/... 10 = letters in index.php/
		if(mb_substr($_SERVER['REQUEST_URI'], 0, 10 + mb_strlen($config->webroot)) ==  $config->webroot . '/index.php') {
			$request->setControllerName('doesnotexists')
                ->setActionName('withindexdotphp');
		}
		
    	//@todo this is a workaround, the better approach was to get the string 'as it is'
    	if(isset($params['username']) && mb_strstr($_SERVER['REQUEST_URI'], "?", true) != 'proxy')
    	{
    		require APPLICATION_PATH . "/configs/reserved-usernames.php";
    		
    		if(in_array($params['username'], $reservedUsernames))
    		{
    			$request->setControllerName('static')
                ->setActionName('docs');
    		}
    	}
    }
}

require 'RequestNotPlugin.php';
$router->addConfig($routerConfig, "routes");

$frontController->registerPlugin(new MyReservedUsersPlugin());
Zend_Controller_Action_HelperBroker::getStaticHelper("Redirector")->setPrependBase(false);
$registry = Zend_Registry::getInstance();
$config = $registry->get("config");
$frontController->setBaseUrl($config->webroot);


$this->registerPluginResource("Mysession");
$this->registerPluginResource("Myview");

$documentType = new Zend_View_Helper_Doctype();
$documentType->doctype('XHTML1_STRICT');//mudarpara strict
$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
$viewRenderer->initView();
$viewRenderer->view->doctype('XHTML1_STRICT');

//don't change to (X)HTML because of the ReCaptcha
//$response = new Zend_Controller_Response_Http;

if(isset($_SERVER['HTTP_USER_AGENT']))
{
	if(!strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") && isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], "xhtml+xml")))
	{
		//$response->setHeader('Content-Type', 'application/xhtml+xml; charset=utf-8', true);
		//$frontController->setResponse($response);
		//$registry->set("usingXhtmlHeader", true);
	}
}
