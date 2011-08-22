<?php

class Mysession extends Zend_Application_Resource_ResourceAbstract
{
	public function init()
	{
		$registry = Zend_Registry::getInstance();
		$auth = Zend_Auth::getInstance();
		
		$config = $registry->get("config");
		
        Zend_Session::setSaveHandler(new ML_Session_SaveHandler_PlusCache($registry->get("memCache")));
        
        Zend_Session::getSaveHandler()->setLifetime($config->resources->session->cookie_lifetime, true);
		Zend_Session::start();
		
		$defaultNamespace = new Zend_Session_Namespace();
		
		if (!isset($defaultNamespace->initialized)) {
			Zend_Session::regenerateId();
			$defaultNamespace->initialized = true;
		}
		
		if($auth->hasIdentity())
		{
			//@todo make a better caching mechanism, if necessary for performance
			//be aware that certain code may be negatively affected in terms of security
			//like 'just after setting another email' and having a old cache in another browser's session
        	/*if(!isset($defaultNamespace->cachedSignedUserInfo) || mt_rand(0, 1000) < 50)
        	{*/
        		$People = ML_People::getInstance();
        		$defaultNamespace->cachedSignedUserInfo = $People->getById($auth->getIdentity());
        		/*
        		if(!$defaultNamespace->cachedSignedUserInfo) throw new Exception("Can not set up session chachedSignedUserInfo");
        	}*/
        	
        	$registry->set('signedUserInfo', $defaultNamespace->cachedSignedUserInfo);
		}
		
		$globalHash = ML_MagicCookies::getInstance()->getLast(true);
	    $registry->set("globalHash", $globalHash);
	}
}
?>