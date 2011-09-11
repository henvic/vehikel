<?php
class ContactsController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->loadResource->pseudoshareSetUp();
    }
    
    public function reverselistAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $page = $request->getUserParam("page");
        
        $userInfo = $registry->get("userInfo");
        
        $contacts = Ml_Contacts::getInstance();
        
        $paginator = $contacts->getReverseContactsPage($userInfo['id'], 30, $page);
        
        //Test if there is enough pages or not
        if ((! $paginator->count() && $page != 1) ||
         $paginator->getCurrentPageNumber() != $page) {
         $this->_redirect($router->assemble(array("username"
            => $userInfo['alias']), 
            "contactsrev_1stpage"), array("exit"));
        }
        
        $this->view->paginator = $paginator;
    }
    
    public function listAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $page = $request->getUserParam("page");
        
        $userInfo = $registry->get("userInfo");
        
        $contacts = Ml_Contacts::getInstance();
        
        $paginator = $contacts->getContactsPage($userInfo['id'], 30, $page);
             //Test if there is enough pages or not
        if ((! $paginator->count() && $page != 1) ||
         $paginator->getCurrentPageNumber() != $page) {
            $this->_redirect($router->assemble(array("username" => $userInfo['alias']), 
            "contacts_1stpage"), array("exit"));
        }
        
        $this->view->paginator = $paginator;
    }
    
    public function ignorelistAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        $page = $request->getUserParam("page");
        
        $userInfo = $registry->get("userInfo");
        
        if($userInfo['id'] != $auth->getIdentity()) throw new Exception("403 Forbidden: you can see your own ignored list only.");
        
        $ignore = Ml_Ignore::getInstance();
        
        $paginator = $ignore->getIgnorePage($userInfo['id'], 30, $page);
        
        //Test if there is enough pages or not
        if ((! $paginator->count() && $page != 1) ||
         $paginator->getCurrentPageNumber() != $page) {
            $this->_redirect($router->assemble(array("username"
            => $userInfo['alias']), 
            "ignore_1stpage"), array("exit"));
        }
        
        $this->view->paginator = $paginator;
    }
    
    public function relationshipAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $request = $this->getRequest();
        
        $userInfo = $registry->get("userInfo");
        
        $contacts = Ml_Contacts::getInstance();
        $ignore = Ml_Ignore::getInstance();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new Ml_Plugins_LoginRedirect());
        }
        
        //avoids self-relationship
        if ($auth->getIdentity() == $userInfo['id']) {
            $this->_redirect($router->assemble(array("username"
             => $userInfo['alias']), "contacts_1stpage"), array("exit"));
        }
        
        $relationship = $contacts->getInfo($auth->getIdentity(), $userInfo['id']);
        
        if (isset($relationship['id'])) {
            $ignoreStatus = false;
        } else {
            $ignoreStatus = $ignore->status($auth->getIdentity(), $userInfo['id']);
        }
        
        
        if (is_array($ignoreStatus)) {
            $this->view->cannotAddIgnored = $ignoreStatus;
        } else {
            $form = $contacts->relationshipForm();
            
            if (isset($relationship['id'])) {
                $form->getElement("contact_relation")->setOptions(array("checked" => true));
            }
            
            if ($request->isPost() && $form->isValid($request->getPost())) {
                $wantContact = $form->getElement("contact_relation")->isChecked();
                
                if ($wantContact && ! isset($relationship['id'])) {
                    $contacts->getAdapter()->query("INSERT IGNORE INTO `" .
                     $contacts->getTableName() .
                     "` (uid, has, friend) SELECT ?, ?, ? FROM DUAL WHERE not exists (select * from `ignore` where ignore.uid = ? AND ignore.ignore = ?)",
                     array($auth->getIdentity(), $userInfo['id'], 0, 
                    $userInfo['id'], $auth->getIdentity()));
                    $changeRel = "?new_contact";
                } else if (! $wantContact && isset($relationship['id'])) {
                    $contacts->delete($contacts->getAdapter()
                    ->quoteInto("uid = ? AND ", $auth->getIdentity()).
                    $contacts->getAdapter()
                    ->quoteInto("has = ?", $userInfo['id'])
                    );
                    
                    $changeRel = "?removed_contact";
                } else {
                    $changeRel = '';
                }
                
                $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "profile").$changeRel, array("exit"));
                
                /*if (isset($new_rel) && $new_rel) {
                    $form->getElement("contact_relation")->setOptions(
                    array("checked" => true));
                }*/
            }
        }
        
        $this->view->relationshipForm = $form;
    }
}
