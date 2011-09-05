<?php

/**
 * Shares Controller
 *
 * All actions in this controller demand an associated username.
 * And some actions do demand the share_id user param
 *
 * @copyright  2008 Henrique Vicente
 * @version    $Id:$
 * @link       http://thinkings.info
 * @since      File available since Release 0.1
 */

class SharesController extends Zend_Controller_Action
{
    public function _deleteForm()
    {
        static $form = '';

        if(!is_object($form))
        {
            $registry = Zend_Registry::getInstance();
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
            
            require_once APPLICATION_PATH . '/forms/DeleteShare.php';
             
            $form = new Form_DeleteShare(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "deleteshare"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public function _editForm()
    {
        static $form = '';

        if(!is_object($form))
        {
            $registry = Zend_Registry::getInstance();
            $shareInfo = $registry->get('shareInfo');
            $userInfo = $registry->get('userInfo');
             
            require_once APPLICATION_PATH . '/forms/Filepage.php';
             
            $form = new Form_Filepage(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "editsharepage"),
                'method' => 'post',
            ));
        }
        
        $form->setDefault("hash", $registry->get('globalHash'));
        
        return $form;
    }
    
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $request = $this->getRequest();
        
        $this->_helper->loadResource->pseudoshareSetUp();
        
        if($request->getActionName() == "edit" || $request->getActionName() == "delete")
        {
            if($auth->hasIdentity() && $registry->isRegistered("signedUserInfo") && $registry['signedUserInfo']['id'] == $registry['shareInfo']['byUid'])
            {} else throw new Exception("Can not edit or delete this share.");
        }
    }
    
    public function editAction()
    {
        $registry = Zend_Registry::getInstance();
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $request = $this->getRequest();
        
        $Share = new ML_Upload();
        
        $shareInfo = $registry->get("shareInfo");
        
        $form = $this->_editForm();
        
        $form->setDefaults($shareInfo);
        
        if($request->isPost() && $form->isValid($request->getPost()))
        {
            $new_shareInfo = $Share->setMeta($signedUserInfo, $shareInfo, $form->getValues(), $form->getErrors());
            
            if($new_shareInfo && !empty($new_shareInfo))
            {
                $registry->set("shareInfo", $new_shareInfo);
                $form->setDefaults($new_shareInfo);
                $shareInfo = $new_shareInfo;
            }
            
            $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $signedUserInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage"), array("exit"));
        }
        
        $this->view->editForm = $form;
    }
    
    public function deleteAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $request = $this->getRequest();
        $Share = new ML_Upload();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $form = $this->_deleteForm();
        if($request->isPost() && 
            $form->isValid($request->getPost())
        )
        {
            $forget = $form->getValue("forget");
            if(!empty($forget)) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $signedUserInfo['alias']), "filestream_1stpage"), array("exit"));
            
            $Share->deleteShare($shareInfo, $signedUserInfo);
            $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $signedUserInfo['alias']), "filestream_1stpage") . "?share-erased=true", array("exit"));
        }
         
        $this->view->deleteForm = $form;
    }
    
}
