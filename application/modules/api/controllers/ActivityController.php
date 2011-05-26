<?php

class ActivityController extends Zend_Controller_Action
{
	public function recentAction()
	{
		//@todo route: do it the right way!
		$router = new Zend_Controller_Router_Rewrite();
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/defaultRoutes.ini');
		$router->addConfig($config, 'routes');
		
		$registry = Zend_Registry::getInstance();
		
		$config = $registry->get("config");
		
		$this->_helper->verifyIdentity();
		$Recent = new ML_Recent();
		
		if(!$registry->isRegistered("authedUserInfo")) throw new Exception("Not authenticated.");
		
		$userInfo = $registry->get("authedUserInfo");
		
		$uploads = $Recent->contactsUploads($userInfo['id']);
		
		//send response
		$doc = new ML_Dom();
		$doc->formatOutput = true;
		
		$root_element = $doc->createElement("items");
		$doc->appendChild($root_element);
		
		foreach($uploads as $share)
		{
			$share_element = $doc->createElement("item");
			
			$avatarInfo = unserialize($share['people.avatarInfo']);
			$iconsecret = (isset($avatarInfo['secret'])) ? $avatarInfo['secret'] : '';
			
			$share_data = array(
				"type" => "file",
				"id" => $share['id'],
			);
			
			foreach($share_data as $name => $field)
			{
				$share_element->appendChild($doc->newTextAttribute($name, $field));
			}
			
			$share_data = array(
				"title" => $share['share.title'],
				"short" => $share['share.short'],
				"url" => "http://".$config->webhost. $router->assemble(array("username" => $share['people.alias'], "share_id" => $share['id']), "sharepage_1stpage"),
			);
			
			foreach($share_data as $name => $field)
			{
				$share_element->appendChild($doc->newTextElement($name, $field));
			}
			
			$filesize_element = $doc->createElement("filesize");
			$filesize_element->appendChild($doc->newTextAttribute("bits", $share['share.fileSize']));
			$filesize_element->appendChild($doc->newTextAttribute("kbytes", ceil($share['share.fileSize']/(1024*8))));
			$share_element->appendChild($filesize_element);
			
			$owner_element = $doc->createElement("owner");
			
			$share_data = array(
				"id" => $share['people.id'],
				"alias" => $share['people.alias'],
				"realname" => $share['people.name'],
				"iconsecret" => $iconsecret,
			);
			
			foreach($share_data as $name => $field)
			{
				$owner_element->appendChild($doc->newTextAttribute($name, $field));
			}
			
			$share_element->appendChild($owner_element);
			
			$root_element->appendChild($share_element);
		}
		
		$this->_helper->printResponse($doc);
	}
}
