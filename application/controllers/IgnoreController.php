<?php

class IgnoreController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->loadResource->pseudoshareSetUp();
    }
    
    protected function _ignoreForm()
    {
        static $form = '';
        
        if(!is_object($form))
        {
            require_once APPLICATION_PATH . '/forms/Ignore.php';
            
            $userInfo = Zend_Registry::getInstance()->get("userInfo");
            
            $form = new Form_Ignore(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "contact_relationship_ignore"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public function switchAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $Ignore = ML_Ignore::getInstance();
        
        $request = $this->getRequest();
        
        $userInfo = $registry->get("userInfo");
        if(!$auth->hasIdentity()) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "login"), array("exit"));
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        if($auth->getIdentity() == $userInfo['id']) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "index"), array("exit"));
        
        $ignore_status = $Ignore->status($auth->getIdentity(), $userInfo['id']);
        
        if(is_array($ignore_status)) $registry->set("is_ignored", true);
        
        $form = $this->_ignoreForm();//has to be loaded after the line above
        
        if($request->isPost() && $form->isValid($request->getPost()))
        {
            $formInfo = $form->getValues();
            if(isset($formInfo['ignore']))
            {
                //ignore user
                $Ignore->set($auth->getIdentity(), $userInfo['id']);
                $what_action = "blocked";
            } elseif(isset($formInfo['removeignore'])) {
                //remove ignore
                $Ignore->remove($auth->getIdentity(), $userInfo['id']);
                $what_action = "unblocked";
            }
            
            if(isset($what_action)) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "profile"), array("exit"));
        }
        
        $this->view->ignore_form = $form;
        
        $this->view->ignore_status = $ignore_status;
    }
}