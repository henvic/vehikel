<?php

class AccountdeleteController extends Zend_Controller_Action
{
    public function init()
    {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
        }
    }
    
    public function indexAction()
    {
        //shares/avatar files are deleted by an off-line routine in crontab
        $request = $this->getRequest();
        
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $Credential = ML_Credential::getInstance();
        
        $People_deleted = ML_PeopleDeleted::getInstance();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $form = $People_deleted->_getDeleteAccountForm();
        
        if($request->isPost()) {
            $select = $Credential->select()->where("uid = ?", $auth->getIdentity());
            $credentialInfo = $Credential->fetchRow($select);
            
            if(!is_object($credentialInfo))
            {   
                throw new Exception("Fatal error on checking credential in account controller.");
            }
                
            $credentialInfoData = $credentialInfo->toArray();
            $registry->set('credentialInfoDataForPasswordChange', $credentialInfoData);
            
            if($form->isValid($request->getPost()))
            {
                $registry->set("canDeleteAccount", true);
                
                $People_deleted->deleteAccount($signedUserInfo, sha1(serialize($signedUserInfo)));
                
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