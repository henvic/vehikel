<?php

class Ml_Resource_Mysession extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();

        $sc = $registry->get("sc");

        $config = $registry->get("config");
        
        $sessionConfig = $config['resources']['session'];
        
        $cookieLifetime = $sessionConfig['cookie_lifetime'];

        $saveHandler = new Ml_Session_SaveHandler_PlusCache(
            $registry->get("memCache"),
            $config['session']['prefix'],
            $config['lastActivity']['prefix'],
            $auth
        );
        
        Zend_Session::setSaveHandler($saveHandler);
        
        Zend_Session::
        getSaveHandler()->setLifetime($cookieLifetime, true);
        
        Zend_Session::start();
        
        $defaultNamespace = new Zend_Session_Namespace();
        
        if (!isset($defaultNamespace->initialized)) {
            Zend_Session::regenerateId();
            $defaultNamespace->initialized = true;
        }
        
        if ($auth->hasIdentity()) {
            $people =  $sc->get("people");
            /** @var $people \Ml_Model_People() */

            $signedUserInfo = $people->getById($auth->getIdentity());

            if (! is_array($signedUserInfo)) {
                throw new Exception("Can't retrieve the signed userInfo data or user deactivated");
            }

            if (! isset($signedUserInfo["active"]) || ! $signedUserInfo["active"]) {
                //log out deactivated user
                $session =  $sc->get("session");
                /** @var $session \Ml_Model_Session() */

                $session->logout();
                throw new Exception("Trying to load deactivated user session");
            }

            $registry->set('signedUserInfo', $signedUserInfo);
        }
        
        $globalHash = Ml_Model_MagicCookies::getInstance()->getLast(true);
        $registry->set("globalHash", $globalHash);
    }
}
