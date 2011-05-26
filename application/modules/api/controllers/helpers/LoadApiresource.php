<?php

class Zend_Controller_Action_Helper_LoadApiresource extends
                Zend_Controller_Action_Helper_Abstract
{
	public function user()
	{
		$registry = Zend_Registry::getInstance();
		
		$request = $this->getRequest();
		
		$params = $request->getParams();
		
		$People = ML_People::getInstance();
		
		if(!isset($params['user_id'])) throw new Exception("User param not given.");
		
		$userInfo = $People->getById($params['user_id']);
		if(empty($userInfo)) {
			$registry->set("notfound", true);
			throw new Exception("User not found.");
		}
		
		$registry->set("userInfo", $userInfo);
	}
	
	public function share()
	{
		$registry = Zend_Registry::getInstance();
		
		$request = $this->getRequest();
		
		$params = $request->getParams();
		
		$Share = ML_Share::getInstance();
		
		if(!isset($params['file_id'])) throw new Exception("File ID param not given.");
		
		$shareInfo = $Share->getById($params['file_id']);
		if(empty($shareInfo)) {
			$registry->set("notfound", true);
			throw new Exception("File not found.");
		}
		
		$registry->set("shareInfo", $shareInfo);
	}
}