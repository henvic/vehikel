<?php

class FilesController extends Zend_Controller_Action
{
	public function listAction()
	{
		$registry = Zend_Registry::getInstance();
		$config = $registry->get("config");
		
		$request = $this->getRequest();
		
		$this->_helper->loadApiresource->user();
		
		$userInfo = $registry->get("userInfo");
		
		$params = $request->getParams();
		
		$Share = ML_Share::getInstance();
		
		$per_page = $request->getParam("per_page", 20);
		if($per_page > 100) $per_page = 100;
		elseif($per_page < 1 || $per_page > 100) $per_page = 20;
		
		$page = $request->getParam("page", 1);
		if(!is_natural($page)) $page = 1;
		
		$paginator = $Share->getPages($userInfo['id'], $per_page, $page);
		
		if($paginator->count() < $page) $page = $paginator->count();
		
		$doc = new ML_Dom();
		$doc->formatOutput = true;
		
		$root_element = $doc->createElement("files");
		
		$doc->appendChild($root_element);
		
		$share_data = array(
			"page" => $page,
			"pages" => $paginator->count(),
			"per_page" => $per_page,
			"total" => $paginator->getTotalItemCount(),
		);
		
		foreach($share_data as $field => $data)
		{
			$root_element->appendChild($doc->newTextAttribute($field, $data));
		}
	
		foreach($paginator->getCurrentItems()->toArray() as $share)
		{
			$share_element = $doc->createElement("file");
			
			$share_element->appendChild($doc->newTextAttribute("id", $share['id']));
			$share_element->appendChild($doc->newTextAttribute("posted", $share['uploadedTime']));
			
			$share_data = array("title" => $share['title'], "filename" => $share['filename'], "filetype" => $share['type'], "short" => $share['short']);
			foreach($share_data as $field => $data)
			{
				$share_element->appendChild($doc->newTextElement($field, $data));
			}
			
			$filesize_element = $doc->createElement("filesize");
			$filesize_element->appendChild($doc->newTextAttribute("bits", $share['fileSize']));
			$filesize_element->appendChild($doc->newTextAttribute("kbytes", ceil($share['fileSize']/(1024*8))));
			$share_element->appendChild($filesize_element);
			
			$root_element->appendChild($share_element);
		}
	
		$this->_helper->printResponse($doc);
	}
	
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
		$Favorites = ML_Favorites::getInstance();
		$Comments = ML_Comments::getInstance();
		$Tags = ML_Tags::getInstance();
		
		$this->_helper->loadApiresource->share();
		$shareInfo = $registry->get("shareInfo");
		
		$userInfo = $People->getById($shareInfo['byUid']);
		
		$tagsList = $Tags->getShareTags($shareInfo['id']);
		$count_favs = $Favorites->count($shareInfo['id']);
		$count_comments = $Comments->count($shareInfo['id']);
		
		
		//begin of response
		$doc = new ML_Dom();
		$doc->formatOutput = true;
		
		$root_element = $doc->createElement("file");
		$doc->appendChild($root_element);
		
		$root_element->appendChild($doc->newTextAttribute('id', $shareInfo['id']));
		$root_element->appendChild($doc->newTextAttribute('secret', $shareInfo['secret']));
		$root_element->appendChild($doc->newTextAttribute('download_secret', $shareInfo['download_secret']));
		
		$owner_element = $doc->createElement("owner");
		
		$owner_data = array(
			"id" => $userInfo['id'],
			"username" => $userInfo['alias'],
			"realname" => $userInfo['name'],
		);
		
		foreach($owner_data as $field => $data)
		{
			$owner_element->appendChild($doc->newTextAttribute($field, $data));
		}
		
		$root_element->appendChild($owner_element);
		
		$share_data = array(
			"title" => $shareInfo['title'],
			"filename" => $shareInfo['filename'],
			"filetype" => $shareInfo['type'],
			"short" => $shareInfo['short'],
			"description" => $shareInfo['description_filtered'],
			"url" => "http://".$config['webhost']. $router->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage"),
			"dataurl" => $config['services']['S3']['sharesBucketAddress'].$userInfo['alias']."/".$shareInfo['id']."-".$shareInfo['download_secret']."/".$shareInfo['filename'],
			"shorturl" => $config['URLshortening']['addr'].base58_encode($shareInfo['id']),
			//"views" => $shareInfo['views'],
			"comments" => $count_comments,
			"favorites" => $count_favs
		);
		
		foreach($share_data as $field => $data)
		{
			$root_element->appendChild($doc->newTextElement($field, $data));
		}
		
		$filesize_element = $doc->createElement("filesize");
		$filesize_element->appendChild($doc->newTextAttribute("bits", $shareInfo['fileSize']));
		$filesize_element->appendChild($doc->newTextAttribute("kbytes", ceil($shareInfo['fileSize']/(1024*8))));
		$root_element->appendChild($filesize_element);
		
		$checksum_element = $doc->createElement("checksum");
		
		$checksum_element->appendChild($doc->newTextAttribute("hash", "md5"));
		$checksum_element->appendChild($doc->newTextAttribute("value", $shareInfo['md5']));
		
		$root_element->appendChild($checksum_element);
		
		$visibility_element = $doc->createElement("visibility");
		$visibility_element->appendChild($doc->newTextAttribute("ispublic", "1"));
		$root_element->appendChild($visibility_element);
		
		$dates_data = array(
			"posted" => $shareInfo['uploadedTime'],
			"lastupdate" => $shareInfo['lastChange'],
		);
		
		$dates_element = $doc->createElement("dates");
		
		foreach($dates_data as $field => $data)
		{
			$dates_element->appendChild($doc->newTextAttribute($field, $data));
		}
		
		$root_element->appendChild($dates_element);
		
		
		$tags_element = $doc->createElement("tags");
		foreach($tagsList as $tag)
		{
			$tag_element = $doc->createElement("tag");
			$tag_element->appendChild($doc->newTextAttribute("id", $tag['id']));
			$tag_element->appendChild($doc->newTextAttribute("raw", $tag['raw']));
			
			$tag_element->appendChild($doc->createTextNode($tag['clean']));
			
			$tags_element->appendChild($tag_element);
		}
		
		$root_element->appendChild($tags_element);
		
		$this->_helper->printResponse($doc);
	}
	
	public function setmetaAction()
	{
		$registry = Zend_Registry::getInstance();
		
		$request = $this->getRequest();
		
		$this->_helper->verifyIdentity();
		
		$this->_helper->loadApiresource->share();
		
		$Share = new ML_Upload();
		
		$form = $Share->_apiSetMetaForm();
		
		if($request->isPost())
		{
			if($form->isValid($request->getPost()))//should work with PUT also
			{
				$shareInfo = $registry->get("shareInfo");
				$authedUserInfo = $registry->get("authedUserInfo");
				
				$Share->setMeta($authedUserInfo, $shareInfo, $form->getValues(), $form->getErrors());
			} else throw new Exception("Invalid post.");
		} else throw new Exception("Not POST HTTP call.");
	}
}