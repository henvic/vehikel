<?php

class UploadController extends Zend_Controller_Action
{
	protected function _uploadForm()
    {
		$router = new Zend_Controller_Router_Rewrite();
		$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/defaultRoutes.ini');
		$router->addConfig($config, 'routes');
    	
    	static $form = '';
    	
    	if(!is_object($form))
    	{
    		require_once APPLICATION_PATH . '/forms/api/uploadForm.php';
    		
    		$form = new Form_Upload(array(
    			'action' => $router->assemble(array(), "upload"),
    			'method' => 'post',
    		));
    	}
    	
    	return $form;
    }
	
	public function indexAction()
	{
		$this->_helper->verifyIdentity();
		
		$registry = Zend_Registry::getInstance();
		
		$config = $registry->get("config");
		
		$request = $this->getRequest();
		
		if(!$config->upload->available) throw new Exception("Not receiving uploads now. Try later.");
		
		$userInfo = $registry->get("authedUserInfo");
		
		$Share = new ML_Upload();
		
		$uploadStatus = $Share->getUploadStatus($userInfo['id']);
		
		$registry->set("uploadStatus", $uploadStatus);
		
		$form = $this->_uploadForm();
		
		if($request->isPost()) {
			ignore_user_abort(true);
			//it doesn't seems to be working
			//the idea is to let it open to the user to exit the page if it takes too long
			//not a major problem since the upload to s3 should be real quick
		}
		
		if($request->isPost() && $form->isValid($request->getPost()))
		{
			// Returns all known internal file information
			$files = $form->file->getFileInfo();

			$fileErrors = array();
			$fileInfo = array();
			
			$file = key($files);
			$info = current($files);
			
			if($info['error'] != 0 || $info['tmp_name'] == '' || !is_uploaded_file($info['tmp_name'])) {
				throw new Exception("Upload error. File info problem.");
			}
			
			set_time_limit(100);
			
			$details = array();
			$details['short'] = $form->getValue("short");
			$details['title'] = $form->getValue("title");
			$details['description'] = $form->getValue("description");
			
			$newFileId = $Share->addFile($info, $userInfo, false, $details);
			if($newFileId) {
				$shareInfo = $Share->getById($newFileId);
				
				$doc = new ML_Dom();
				$doc->formatOutput = true;
				$file_element = $doc->createElement("file");
				$id_element = $doc->createElement("id");
        		$id_element->appendChild($doc->createTextNode($newFileId));
        		$file_element->appendChild($id_element);
        		$md5_element = $doc->createElement("md5sum");
        		$md5_element->appendChild($doc->createTextNode($shareInfo['md5']));
        		$file_element->appendChild($md5_element);
        		$doc->appendChild($file_element);
				$this->_helper->printResponse($doc);
			}
			else throw new Exception("Upload error.");
		} else {
			throw new Exception("Upload error. Not valid.");
		}
	}
	
	public function statusAction()
	{
		$this->_helper->verifyIdentity();
		
		$registry = Zend_Registry::getInstance();
		
		$userInfo = $registry->get("authedUserInfo");
		
		$Share = new ML_Upload();
		
		$uploadStatus = $Share->getUploadStatus($userInfo['id']);
		
		$doc = new ML_Dom();
		$doc->formatOutput = true;
		
		$root_element = $doc->createElement("user");
		$doc->appendChild($root_element);
		
		$root_element->appendChild($doc->newTextAttribute('id', $userInfo['id']));
		
		$username_element = $doc->createElement("username");
        $username_element->appendChild($doc->createTextNode($userInfo['alias']));
		$root_element->appendChild($username_element);
        
		$bandwidth = $uploadStatus['bandwidth'];
		$bandwidthInfo = array(
			"maxbytes" => $bandwidth['maxbytes'],
			"maxkb" => floor($bandwidth['maxbytes']/8),
			"usedbytes" => $bandwidth['usedbytes'],
			"usedkb" => ceil($bandwidth['usedbytes']/8),
			"remainingbytes" => $bandwidth['remainingbytes'],
			"remainingkb" => floor($bandwidth['remainingbytes']/8),
		);
		
		$bandwidth_element = $doc->createElement("bandwidth");
		foreach($bandwidthInfo as $field => $data)
		{
			$bandwidth_element->appendChild($doc->newTextAttribute($field, $data));
		}
		$root_element->appendChild($bandwidth_element);
		
		$filesize_element = $doc->createElement("filesize");
		$filesize_element->appendChild($doc->newTextAttribute('maxbytes', floor($uploadStatus['filesize']['maxbytes'])));
		$filesize_element->appendChild($doc->newTextAttribute('maxkb', floor($uploadStatus['filesize']['maxbytes']/8)));
		$root_element->appendChild($filesize_element);
		
		$this->_helper->printResponse($doc);
	}
}