<?php

class UploadController extends Zend_Controller_Action
{
    public function indexAction()
    {
        
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        
        $request = $this->getRequest();
        
        $share = Ml_Model_Share::getInstance();
        
        if (! $auth->hasIdentity()) {
            $this->_redirect($router->assemble(array(), "login"), array("exit"));
        }
        
        if (! $config['upload']['available']) {
            $this->_forward("offline");
        }
        
        $signedUserInfo = $registry->get('signedUserInfo');
        
        $uploadStatus = $share->getUploadStatus($auth->getIdentity());
        
        $registry->set("uploadStatus", $uploadStatus);
        
        $form = $share->form();
        
        if ($request->isPost()) {
            ignore_user_abort(true);
        }
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            // Returns all known internal file information
            $files = $form->file->getFileInfo();

            $fileErrors = array();
            $fileInfo = array();
            
            $num = -1;
            foreach ($files as $file => $info) {
                $num++;
                if ($info['error'] != 0 || $info['tmp_name'] == '' ||
                 ! is_uploaded_file($info['tmp_name'])) {
                    $fileErrors[] = $file;
                    continue;
                }
                $fileInfo[$num] = $info;
            }
            
            $uploaded = array();
            foreach ($fileInfo as $num => $file) {
                set_time_limit(100);
                $newFileId = $share->addFile($file, $signedUserInfo);
                if ($newFileId) {
                    $uploaded[] = $newFileId;
                }
            }

            $upNum = sizeof($uploaded);
            if ($upNum > 1) {
                
                //@todo batch editing. Load like /upload/batchedit/id1/id2/id3...
                $this->_redirect($router->assemble(array("username" =>
                $signedUserInfo['alias']), "filestream_1stpage") . "?uploaded=true", array("exit"));
                
            } else if ($upNum == 1) {
                
                $this->_redirect($router->assemble(array("username" =>
                $signedUserInfo['alias'], "share_id" => $uploaded[0]),
                "editsharepage"), array("exit"));
                
            }
        }
        
        $this->view->uploadForm = $form;
        $this->view->uploadStatus = $uploadStatus;
    }
    
    public function offlineAction()
    {
        //only loads the view script
    }
}