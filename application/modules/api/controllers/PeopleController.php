<?php

class PeopleController extends Zend_Controller_Action
{
	public function infoAction()
	{
		//@todo route: do it the right way!
		$router = new Zend_Controller_Router_Rewrite();
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/defaultRoutes.ini');
		$router->addConfig($config, 'routes');
		
		$registry = Zend_Registry::getInstance();
		$config = $registry->get("config");
		
		$request = $this->getRequest();
		
		$params = $request->getParams();
		
		$People = ML_People::getInstance();
		$Profile = new ML_Profile();
		$Share = ML_Share::getInstance();
		
		if(isset($params['username']))
		{
			$userInfo = $People->getByUsername($params['username']);
		} elseif(isset($params['user_id']))
		{
			$userInfo = $People->getById($params['user_id']);
		} elseif(isset($params['email']))
		{
			$userInfo = $People->getByEmail($params['email']);
			
			if(!empty($userInfo) && $userInfo['private_email'] == true) {
				$registry->set("notfound", true);
				throw new Exception("User not found.");
			}
		}
		else throw new Exception("No user params were given.");
		
		if(empty($userInfo)) {
			$registry->set("notfound", true);
			throw new Exception("User not found.");
		}
		
		$profileInfo = $Profile->getById($userInfo['id']);
		
		$doc = new ML_Dom();
		$doc->formatOutput = true;
		
		$root_element = $doc->createElement("person");
		$doc->appendChild($root_element);
		
		$root_element->appendChild($doc->newTextAttribute('id', $userInfo['id']));
		
		$avatarInfo = unserialize($userInfo['avatarInfo']);
		$iconsecret = (isset($avatarInfo['secret'])) ? $avatarInfo['secret'] : '';
		
		$root_element->appendChild($doc->newTextAttribute('iconsecret', $iconsecret));
		
		$userData = array(
			"username" => $userInfo['alias'],
			"realname" => $userInfo['name'],
		);
		
		if(!$userInfo['private_email']) $userData["mbox_sha1sum"] = sha1("mailto:".$userInfo['email']);
		
		$userData["location"] = $profileInfo['location'];
		
		$userData["url"] = "http://".$config->webhost. $router->assemble(array("username" => $userInfo['alias']), "filestream_1stpage");
		
		foreach($userData as $field => $data)
		{
			$root_element->appendChild($doc->newTextElement($field, $data));
		}
		
		$shares_counter = $Share->getAdapter()->fetchOne($Share->select()->from($Share->getTableName(), 'count(*)')->where("byUid = ?", $userInfo['id']));
		
		$shares_element = $doc->createElement("files");
		$shares_counter_element = $doc->createElement("count");
		$shares_counter_element->appendChild($doc->createTextNode($shares_counter));
		$shares_element->appendChild($shares_counter_element);
		$root_element->appendChild($shares_element);
		
		$this->_helper->printResponse($doc);
	}
}