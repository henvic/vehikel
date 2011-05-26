<?php

class UserfeedController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->loadResource->pseudoshareSetUp();
	}
	
	public function userfeedAction()
	{
		$registry = Zend_Registry::getInstance();
		$config = $registry->get("config");
		
		$request = $this->getRequest();
		
		$userInfo = $registry->get("userInfo");
		
		$Share = ML_Share::getInstance();
		
		$paginator = $Share->getPages($userInfo['id'], 20, 1);
		
		$currentItems = $paginator->getCurrentItems()->toArray();
		
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$doc = new ML_Dom();
		$doc->formatOutput = true;
		$doc->encoding = "utf-8";
		
		$root_element = $doc->createElement("rss");
		$root_element->appendChild($doc->newTextAttribute("version", "2.0"));
		$root_element->appendChild($doc->newTextAttribute("xmlns:atom", "http://www.w3.org/2005/Atom"));
		
		$doc->appendChild($root_element);
		
		$channel_element = $doc->createElement("channel");
		
		$channel_element->appendChild($doc->newTextElement("description", "Recent uploads to ".$config->applicationname."."));
		
		$root_element->appendChild($channel_element);
		
		$user_link = "http://".$config->webhost. Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "filestream_1stpage");
		
		/*Instead of...
		 * $firstElement = current($currentItems);
		if(!is_array($firstElement)) $x = 1;
		else $x =2;
		should see the last changed or published of the items...
		*/
		
		$user_data = array(
			"title" => "Shared files from ".$userInfo['name'],
			"link" => $user_link,
			//"description" => //$profileInfo['about_filtered'], //=> it's doesn't render as expected!
			"generator" => "http://".$config->webhost,
		//http://validator.w3.org/feed/docs/rss2.html
			"docs" => "http://blogs.law.harvard.edu/tech/rss",
			"ttl" => "180",
			//@todo pubdate and lastbuilddate maybe are important
		); $user_data['generator'] .= (empty($config->webroot)) ? '/' : "/".$config->webroot."/";
		
		foreach($user_data as $field => $value)
		{
				$channel_element->appendChild($doc->newTextElement($field, $value));
		}
		
		$avatarInfo = unserialize($userInfo['avatarInfo']);
		$iconsecret = (isset($avatarInfo['secret'])) ? $avatarInfo['secret'] : '';
		if(!empty($iconsecret))
		{
			$image_element = $doc->createElement("image");
			$picUri = $config->services->S3->headshotsBucketAddress.$userInfo['id'].'-'.$iconsecret.'-s.jpg';
			$image_element->appendChild($doc->newTextElement("url", $picUri));
			$image_element->appendChild($doc->newTextElement("title", "Shares from ".$userInfo['name']));
			$image_element->appendChild($doc->newTextElement("link", $user_link));
			
			$channel_element->appendChild($image_element);
		}
		
		$atom_link = $doc->createElement("atom:link");
		$atom_link->appendChild($doc->newTextAttribute("href", "http://".$config->webhost . Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias']), "userfeed")));
		$atom_link->appendChild($doc->newTextAttribute("rel", "self"));
		$atom_link->appendChild($doc->newTextAttribute("type", "application/rss+xml"));
		$channel_element->appendChild($atom_link);
		
		foreach($currentItems as $share)
		{
			$share_element = $doc->createElement("item");
			
			$description = (empty($share['description_filtered'])) ? $this->view->escape($share['short']) : $share['description_filtered'];
			
			$link = "http://".$config->webhost. Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $share['id']), "sharepage_1stpage");
			
			$share_date = new Zend_Date($share['uploadedTime'], Zend_Date::ISO_8601);
			
			$share_data = array("title" => $share['title'], "link" => $link, "description" => $description, "pubDate" => $share_date->get(Zend_Date::RSS));
			foreach($share_data as $field => $data)
			{
				$share_element->appendChild($doc->newTextElement($field, $data));
			}
			
			$share_element->appendChild($doc->newTextElement("guid", $link));
			
			
			$enclosure_element = $doc->createElement("enclosure");
			$enclosure_element->appendChild($doc->newTextAttribute("url", $this->view->escape($config->services->S3->sharesBucketAddress.$userInfo['alias']."/".$share['id']."-".$share['download_secret']."/".$share['filename'])));
			$enclosure_element->appendChild($doc->newTextAttribute("length", $share['fileSize']));
			$enclosure_element->appendChild($doc->newTextAttribute("type", $share['type']));
			
			$share_element->appendChild($enclosure_element);
			
			$channel_element->appendChild($share_element);
		}
		
		$request = $this->getRequest();
		
		$response = new Zend_Controller_Response_Http;
		
		//I'm not serving as rss+xml for browsers because they get it wrong
		if(isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], "Mozilla"))
		{
			$contenttype = 'text/xml'; 
		} else $contenttype = 'application/rss+xml';
		
		header('Content-Type: '.$contenttype.'; charset=utf-8');
		echo $doc->saveXML();
		exit;
	}
}