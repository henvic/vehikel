<?php

class UploadController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->verifyIdentity();
        
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $request = $this->getRequest();
        
        if (! $config['upload']['available']) {
            throw new Exception("Not receiving uploads now. Try later.");
        }
        
        $userInfo = $registry->get("authedUserInfo");
        
        $share = Ml_Model_Share::getInstance();
        
        $uploadStatus = $share->getUploadStatus($userInfo['id']);
        
        $registry->set("uploadStatus", $uploadStatus);
        
        $form = $share->apiForm();
        
        if ($request->isPost()) {
            ignore_user_abort(true);
        }
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            // Returns all known internal file information
            $files = $form->file->getFileInfo();

            $fileErrors = array();
            $fileInfo = array();
            
            $file = key($files);
            $info = current($files);
            
            if ($info['error'] != 0 || $info['tmp_name'] == '' ||
             ! is_uploaded_file($info['tmp_name'])) {
                throw new Exception("Upload error. File info problem.");
            }
            
            set_time_limit(100);
            
            $details = array();
            $details['short'] = $form->getValue("short");
            $details['title'] = $form->getValue("title");
            $details['description'] = $form->getValue("description");
            
            $newFileId = $share->addFile($info, $userInfo, false, $details);
            
            if ($newFileId) {
                $shareInfo = $share->getById($newFileId);
                
                $doc = new Ml_Model_Dom();
                $doc->formatOutput = true;
                $fileElement = $doc->createElement("file");
                $idElement = $doc->createElement("id");
                $idElement->appendChild($doc->createTextNode($newFileId));
                $fileElement->appendChild($idElement);
                
                $md5Element = $doc->createElement("md5sum");
                $md5Element
                ->appendChild($doc->createTextNode($shareInfo['md5']));
                
                $fileElement->appendChild($md5Element);
                $doc->appendChild($fileElement);
                $this->_helper->printResponse($doc);
            } else {
                throw new Exception("Upload error.");
            }
        } else {
            throw new Exception("Upload error. Not valid.");
        }
    }
    
    public function statusAction()
    {
        $this->_helper->verifyIdentity();
        
        $registry = Zend_Registry::getInstance();
        
        $userInfo = $registry->get("authedUserInfo");
        
        $share = Ml_Model_Share::getInstance();
        
        $uploadStatus = $share->getUploadStatus($userInfo['id']);
        
        $doc = new Ml_Model_Dom();
        $doc->formatOutput = true;
        
        $rootElement = $doc->createElement("user");
        $doc->appendChild($rootElement);
        
        $rootElement
        ->appendChild($doc->newTextAttribute('id', $userInfo['id']));
        
        $usernameElement = $doc->createElement("username");
        $usernameElement->appendChild($doc->createTextNode($userInfo['alias']));
        $rootElement->appendChild($usernameElement);
        
        $bandwidth = $uploadStatus['bandwidth'];
        $bandwidthInfo = array(
            "maxbytes" => $bandwidth['maxbytes'],
            "maxkb" => floor($bandwidth['maxbytes']/8),
            "usedbytes" => $bandwidth['usedbytes'],
            "usedkb" => ceil($bandwidth['usedbytes']/8),
            "remainingbytes" => $bandwidth['remainingbytes'],
            "remainingkb" => floor($bandwidth['remainingbytes']/8),
        );
        
        $bandwidthElement = $doc->createElement("bandwidth");
        
        foreach ($bandwidthInfo as $field => $data) {
            $bandwidthElement
            ->appendChild($doc->newTextAttribute($field, $data));
        }
        $rootElement->appendChild($bandwidthElement);
        
        $filesizeElement = $doc->createElement("filesize");
        $filesizeElement
        ->appendChild($doc
         ->newTextAttribute('maxbytes',
          floor($uploadStatus['filesize']['maxbytes'])));
        
        $filesizeElement
        ->appendChild($doc
         ->newTextAttribute('maxkb',
          floor($uploadStatus['filesize']['maxbytes']/8)));
        
        $rootElement->appendChild($filesizeElement);
        
        $this->_helper->printResponse($doc);
    }
}