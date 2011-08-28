<?php

class StaticController extends Zend_Controller_Action
{
	public function docsAction()
	{
		$request = $this->getRequest();
		$registry = Zend_Registry::getInstance();
		$config = $registry->get("config");
		
		$int_req_uri = mb_substr($_SERVER['REQUEST_URI'], mb_strlen($config['webroot']));
		
		$uri = explode("?", $int_req_uri, 2);
		$find_path = (mb_substr($uri[0], -1) != '/') ? mb_substr($uri[0], 1) : mb_substr($uri[0], 1, -1);
		
		if(!mb_strpos($find_path, ".") && !mb_strpos($find_path, "\\"))
		{
			$get_fs_path = APPLICATION_PATH."/views/scripts/static/".$find_path.".phtml";
			$find_path_realpath = realpath($get_fs_path);//security... check if exists, etc
			if($find_path_realpath && $find_path_realpath == $get_fs_path) //check if it's really a path that exists
			{
				$found = true;
				//instead of docs.phtml...
				$this->_helper->viewRenderer->setScriptAction($find_path);
			}
		}
		
		//IF NOT FOUND
		if(!isset($found)) {
			$this->_forward("notstatic");//workaround to say a page does not exists
		}
	}
}