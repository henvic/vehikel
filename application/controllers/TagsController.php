<?php
/**
 * Tags Controller
 *
 * (see shares controller header notes)
 *
 * @copyright  2009 Henrique Vicente
 * @version    $Id:$
 * @link       http://thinkings.info
 * @since      File available since Release 0.1
 */

class TagsController extends Zend_Controller_Action
{
    public function addAction()
    {
        $this->_helper->layout->disableLayout();
        
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        
        $Tags = new ML_Tagschange();
        
        $params = $request->getParams();
        
        if($auth->getIdentity() == $shareInfo['byUid']) {
            
            $tagsForm = $Tags->_form();
            
            if($request->isPost() && $tagsForm->isValid($request->getPost()))
            {
                $tagsArray = $Tags->makeArrayOfTags($tagsForm->getValue('tags'));
                
                //It's not guaranteed to work within the tags limit margin, but it's more than ok
                $old_tags = $Tags->getShareTags($shareInfo['id']);
                $tagsCounter = sizeof($old_tags);
                
                foreach($tagsArray as $n => $tag)
                {
                    if($tagsCounter >= $config['tags']['limit']) break;
                    try {
                        $add = $Tags->getAdapter()->query("INSERT IGNORE INTO `".$Tags->getTableName()."` (`share`, `people`, `clean`, `raw`, `timestamp`) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)", array($shareInfo['id'], $shareInfo['byUid'], $tag['clean'], $tag['raw']));
                        if($add->rowCount()) $tagsCounter++;
                    } catch(Exception $e) {}
                }
            }
        }
        return $this->_forward("tags");
    }
    
    public function deleteAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        
        $Tags = new ML_Tagschange();
        
        $params = $request->getParams();
        
        if($auth->getIdentity() == $shareInfo['byUid']) {
            $select = $Tags->select()
            ->where("id = ?", $params['deletetag'])
            ->where("share = ?", $shareInfo['id'])
            ->where("people = ?", $shareInfo['byUid']);
            
            $row = $Tags->fetchRow($select);
            
            if(is_object($row)) {
                $form = $Tags->_formDelete();
                $this->view->tag = $row->toArray();
                $this->view->deleteTagForm = $form;
                
                if($request->isPost() && $form->isValid($request->getPost()))
                {
                    $row->delete();
                    $is_deleted = true;
                }
            }
        }
        
        if($this->_request->isXmlHttpRequest())
        {
            $this->_forward("tags");
        } elseif(!is_object($row) || isset($is_deleted))
        {
            $this->_redirect($this->getFrontController()->getRouter()->assemble($params, "sharepage_1stpage"), array("exit"));
        }
    }
    
    /** this method only displays the tags for a called share or redirects
     * the user to the share's mainpage and that's all */
    public function tagsAction()
    {
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
        if($this->_request->isXmlHttpRequest())
        {
            $this->_helper->layout->disableLayout();
        } else {
            $this->_redirect($this->getFrontController()->getRouter()->assemble($params, "sharepage_1stpage"), array("exit"));
        }
        
        $registry = Zend_Registry::getInstance();
        $shareInfo = $registry->get("shareInfo");
        
        $Tags = ML_Tags::getInstance();
        
        $tagsList = $Tags->getShareTags($shareInfo['id']);
        
        $this->view->tagsList = $tagsList;
    }
}