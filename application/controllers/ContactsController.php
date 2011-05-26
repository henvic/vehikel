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
		
		$request = $this->getRequest();
		
		$page = $request->getUserParam("page");
		
		$userInfo = $registry->get("userInfo");
		
		$Contacts = ML_Contacts::getInstance();
		
		$paginator = $Contacts->getReverseContactsPage($userInfo['id'], 30, $page);
		
		//Test if there is enough pages or not
		if((!$paginator->count() && $page != 1) || $paginator->getCurrentPageNumber() != $page) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "contactsrev_1stpage"), array("exit"));
		
		$this->view->paginator = $paginator;
	}
	
	public function listAction()
	{
		$auth = Zend_Auth::getInstance();
		$registry = Zend_Registry::getInstance();
		
		$request = $this->getRequest();
		
		$page = $request->getUserParam("page");
		
		$userInfo = $registry->get("userInfo");
		
		$Contacts = ML_Contacts::getInstance();
		
		$paginator = $Contacts->getContactsPage($userInfo['id'], 30, $page);
		
		//Test if there is enough pages or not
		if((!$paginator->count() && $page != 1) || $paginator->getCurrentPageNumber() != $page) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "contacts_1stpage"), array("exit"));
		
		$this->view->paginator = $paginator;
	}
	
	public function ignorelistAction()
	{
		$auth = Zend_Auth::getInstance();
		$registry = Zend_Registry::getInstance();
		
		$request = $this->getRequest();
		$page = $request->getUserParam("page");
		
		$userInfo = $registry->get("userInfo");
		
		if($userInfo['id'] != $auth->getIdentity()) throw new Exception("403 Forbidden: you can see your own ignored list only.");
		
		$Ignore = ML_Ignore::getInstance();
		
		$paginator = $Ignore->getIgnorePage($userInfo['id'], 30, $page);
		
		//Test if there is enough pages or not
		if((!$paginator->count() && $page != 1) || $paginator->getCurrentPageNumber() != $page) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "ignore_1stpage"), array("exit"));
		
		$this->view->paginator = $paginator;
	}
		
	protected function _relationshipForm()
	{
		static $form = '';

		if(!is_object($form))
		{
			$registry = Zend_Registry::getInstance();
			$userInfo = $registry->get("userInfo");
			require_once APPLICATION_PATH . '/forms/Relationship.php';
			
			$form = new Form_Relationship(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "contact_relationship"),
                'method' => 'post',
			));
			
		}
		return $form;
	}
	
	public function relationshipAction()
	{
		$auth = Zend_Auth::getInstance();
		$registry = Zend_Registry::getInstance();
		$request = $this->getRequest();
		
		$userInfo = $registry->get("userInfo");
		
		$Contacts = ML_Contacts::getInstance();
		$Ignore = ML_Ignore::getInstance();
		
	    if(!Zend_Auth::getInstance()->hasIdentity()) {
    	    Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
    	}
    	
		if($auth->getIdentity() == $userInfo['id']) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "contacts_1stpage"), array("exit"));
		//throw new Exception("Can't have a self-relationship.");
		
		$relationship = $Contacts->getInfo($auth->getIdentity(), $userInfo['id']);
		
		$ignore_status = (isset($relationship['id'])) ? false : $Ignore->status($auth->getIdentity(), $userInfo['id']);
		if(is_array($ignore_status)) $this->view->cannot_add_ignored = $ignore_status;
		else {
			$form = $this->_relationshipForm();
			
			if(isset($relationship['id'])) $form->getElement("contact_relation")->setOptions(array("checked" => true));
			
			if($request->isPost() && $form->isValid($request->getPost()))
			{
				$wantContact = $form->getElement("contact_relation")->isChecked();
				
				if($wantContact && !isset($relationship['id']))
				{
					$Contacts->getAdapter()->query("INSERT IGNORE INTO `".$Contacts->getTableName()."` (uid, has, friend) SELECT ?, ?, ? FROM DUAL WHERE not exists (select * from `ignore` where ignore.uid = ? AND ignore.ignore = ?)", array($auth->getIdentity(), $userInfo['id'], 0, $userInfo['id'], $auth->getIdentity()));
					$change_rel = "?new_contact";
				}
				elseif(!$wantContact && isset($relationship['id']))
				{
					$Contacts->delete(
						$Contacts->getAdapter()->quoteInto("uid = ? AND ", $auth->getIdentity()).
						$Contacts->getAdapter()->quoteInto("has = ?", $userInfo['id'])
					);
					
					$change_rel = "?removed_contact";
				} else $change_rel = '';
				
				$this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "profile").$change_rel, array("exit"));
				
				//if(isset($new_rel) && $new_rel) $form->getElement("contact_relation")->setOptions(array("checked" => true));
			}
		}
		
		$this->view->relationshipForm = $form;
	}
}
