<?php

/**
 * Shares Controller
 *
 * All actions in this controller demand an associated username.
 * And some actions do demand the share_id user param
 *
 * @copyright  2008 Henrique Vicente
 * @version    $Id:$
 * @since      File available since Release 0.1
 */

class SharesController extends Zend_Controller_Action
{
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $request = $this->getRequest();
        
        $this->_helper->loadResource->pseudoshareSetUp();
        
        if ($request->getActionName() == "edit" ||
         $request->getActionName() == "delete") {
            if ($auth->hasIdentity() &&
            $registry->isRegistered("signedUserInfo") &&
             $registry['signedUserInfo']['id'] == $registry['shareInfo']['byUid']) {
            } else {
                 throw new Exception("Can not edit or delete this share.");
            }
        }
    }
    
    public function editAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $request = $this->getRequest();
        
        $share = Ml_Model_Share::getInstance();
        
        $shareInfo = $registry->get("shareInfo");
        
        $form = $share->editForm();
        
        $form->setDefaults($shareInfo);
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $newShareInfo =
            $share->setMeta($signedUserInfo, $shareInfo, $form->getValues(), $form->getErrors());
            
            if ($newShareInfo && ! empty($newShareInfo)) {
                $registry->set("shareInfo", $newShareInfo);
                $form->setDefaults($newShareInfo);
                $shareInfo = $newShareInfo;
            }
            
            $this->_redirect($router->assemble(array("username" =>
            $signedUserInfo['alias'], "share_id" => $shareInfo['id']),
            "sharepage_1stpage"), array("exit"));
        }
        
        $this->view->editForm = $form;
    }
    
    public function deleteAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        $share = Ml_Model_Share::getInstance();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $form = $share->deleteForm();
        if ($request->isPost() && 
            $form->isValid($request->getPost())) {
            $forget = $form->getValue("forget");
            if (! empty($forget)) {
                $this->_redirect($router->assemble(array("username"
                => $signedUserInfo['alias']), 
                "filestream_1stpage"), array("exit"));
            }
            
            $share->deleteShare($shareInfo, $signedUserInfo);
            $this->_redirect($router->assemble(array("username" =>
            $signedUserInfo['alias']),
            "filestream_1stpage") . "?share-erased=true", array("exit"));
        }
         
        $this->view->deleteForm = $form;
    }
    
}
