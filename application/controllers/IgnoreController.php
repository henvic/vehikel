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
        
        if (! is_object($form)) {
            $registry = Zend_Registry::getInstance();
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            require APPLICATION_PATH . '/forms/Ignore.php';
            
            $userInfo = $registry->get("userInfo");
            
            $form = new Form_Ignore(array(
                'action' => $router->assemble(array("username" =>
                 $userInfo['alias']), "contactRelationshipIgnore"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public function switchAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $ignore = Ml_Ignore::getInstance();
        
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
        
        $form = $this->_ignoreForm();//has to be loaded after the line above
        
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