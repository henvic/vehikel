<?php

class AccountdeleteController extends Zend_Controller_Action
{
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
    }
    
    public function indexAction()
    {
        // shares/avatar files are deleted by an off-line routine in crontab
        $request = $this->getRequest();
        
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $credential = Ml_Model_Credential::getInstance();
        
        $peopleDelete = Ml_Model_PeopleDelete::getInstance();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $form = $peopleDelete->deleteAccountForm();
        
        if ($request->isPost()) {
            $credentialInfo = $credential->getByUid($auth->getIdentity());
            
            if (! $credentialInfo) {
                throw new Exception("Fatal error on checking credential in account delete controller.");
            }
            
            $registry->set('credentialInfoDataForPasswordChange', $credentialInfo);
            
            if ($form->isValid($request->getPost())) {
                $registry->set("canDeleteAccount", true);
                
                $peopleDelete->deleteAccount($signedUserInfo, sha1(serialize($signedUserInfo)));
                
                $auth->clearIdentity();
                
                Zend_Session::namespaceUnset('Zend_Auth');
                Zend_Session::regenerateId();
                Zend_Session::destroy(true);
                
                $this->_redirect("/account/terminated", array("exit"));
            }
        }
        
        $this->view->deleteAccountForm = $form;
    }
}