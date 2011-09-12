<?php

class IgnoreController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->loadResource->pseudoshareSetUp();
    }
    
    public function switchAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $ignore = Ml_Model_Ignore::getInstance();
        
        $request = $this->getRequest();
        
        $userInfo = $registry->get("userInfo");
        if (! $auth->hasIdentity()) {
            $this->_redirect($router->assemble(array(), "login"), array("exit"));
        }
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        if ($auth->getIdentity() == $userInfo['id']) {
            $this->_redirect($router->assemble(array(), "index"), array("exit"));
        }
        
        $ignoreStatus = $ignore->status($auth->getIdentity(), $userInfo['id']);
        
        if (is_array($ignoreStatus)) {
            $registry->set("is_ignored", true);
        }
        
        //the form has to be loaded after the line above
        $form = $ignore->form();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $formInfo = $form->getValues();
            if (isset($formInfo['ignore'])) {
                //ignore user
                $ignore->set($auth->getIdentity(), $userInfo['id']);
                $whatAction = "blocked";
            } else if (isset($formInfo['removeignore'])) {
                //remove ignore
                $ignore->remove($auth->getIdentity(), $userInfo['id']);
                $whatAction = "unblocked";
            }
            
            if (isset($whatAction)) {
                $this->_redirect($router->assemble(array("username"
                => $userInfo['alias']), "profile"), array("exit"));
            }
        }
        
        $this->view->ignoreForm = $form;
        
        $this->view->ignoreStatus = $ignoreStatus;
    }
}