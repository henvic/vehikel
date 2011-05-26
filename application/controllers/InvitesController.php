<?php

class InvitesController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$auth = Zend_Auth::getInstance();
		
		if(!$auth->hasIdentity()) {
			$this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "index"), array("exit"));
		}
	}
}
