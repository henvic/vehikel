<?php
class ProfileController extends Zend_Controller_Action
{
	public function profileAction()
	{
		$this->_helper->loadResource->pseudoshareSetUp();
	}
}