<?php

class Mysession extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $config = $registry->get("config");
        
        Zend_Session::setSaveHandler(new ML_Session_SaveHandler_PlusCache($registry->get("memCache")));
        
        Zend_Session::getSaveHandler()->setLifetime($config['resources']['session']['cookie_lifetime'], true);
        Zend_Session::start();
        
        $defaultNamespace = new Zend_Session_Namespace();
        
        if (!isset($defaultNamespace->initialized)) {
            Zend_Session::regenerateId();
            $defaultNamespace->initialized = true;
        }
        
        if($auth->hasIdentity())
        {
            $People = ML_People::getInstance();
            $signedUserInfo = $People->getById($auth->getIdentity());
            $registry->set('signedUserInfo', $signedUserInfo);
        }
        
        $globalHash = ML_MagicCookies::getInstance()->getLast(true);
        $registry->set("globalHash", $globalHash);
    }
}
