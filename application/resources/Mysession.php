<?php

class Mysession extends Zend_Application_Resource_ResourceAbstract
{
	public function init()
	{
		$config = array(
		    'name'           => 'session',
    		'primary'        => 'id',
		    'modifiedColumn' => 'modified',
    		'dataColumn'     => 'data',
    		'lifetimeColumn' => 'lifetime'
		);
    	
        Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable($config));
        
        $sessionLifetime = (60 * 60 * 24 * 6);
        Zend_Session::getSaveHandler()->setLifetime($sessionLifetime)->setOverrideLifetime(true);
		Zend_Session::start();
		
		// Create auth instance
		$auth = Zend_Auth::getInstance();
		
		
		$defaultNamespace = new Zend_Session_Namespace();
		
		if (!isset($defaultNamespace->initialized)) {
			Zend_Session::regenerateId();
			$defaultNamespace->initialized = true;
		}
		
		if($auth->hasIdentity())
		{
			if(!isset($defaultNamespace->AuthInitialized))
			{
				 $session = ML_Session::getInstance();
				 
				 $session->update(
       				Array("uid" => $auth->getIdentity(),),
       				$session->getAdapter()->quoteInto("id = ?", Zend_Session::getId()));
        		
        		$defaultNamespace->AuthInitialized = true;
        		
			}
			
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
        	
        	Zend_Registry::getInstance()->set('signedUserInfo', $defaultNamespace->cachedSignedUserInfo);
		}
		
		$globalHash = ML_MagicCookies::getInstance()->getLast(true);
	    Zend_Registry::getInstance()->set("globalHash", $globalHash);
	}
}
?>