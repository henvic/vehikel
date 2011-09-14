<?php

class Ml_Resource_Mysession extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $config = $registry->get("config");
        
        $cookieLifetime = $config['resources']['session']['cookie_lifetime'];
        
        /* @todo fix issue of system with incoherent behavior when the session
        system has a issue, such as when the savehandler doesn't work as
        expected when it's off-line which results in differents
        catched / uncatched exception when the resource (page) loads
        */
        Zend_Session::
        setSaveHandler(new Ml_Session_SaveHandler_PlusCache($registry->get("memCache")));
        
        Zend_Session::
        getSaveHandler()->setLifetime($cookieLifetime, true);
        
        Zend_Session::start();
        
        $defaultNamespace = new Zend_Session_Namespace();
        
        if (!isset($defaultNamespace->initialized)) {
            Zend_Session::regenerateId();
            $defaultNamespace->initialized = true;
        }
        
        if ($auth->hasIdentity()) {
            $people = Ml_Model_People::getInstance();
            $signedUserInfo = $people->getById($auth->getIdentity());
            $registry->set('signedUserInfo', $signedUserInfo);
        }
        
        $globalHash = Ml_Model_MagicCookies::getInstance()->getLast(true);
        $registry->set("globalHash", $globalHash);
    }
}
