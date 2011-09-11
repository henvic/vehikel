<?php
/**
 * Tags Controller
 *
 * (see shares controller header notes)
 *
 * @copyright  2009 Henrique Vicente
 * @version    $Id:$
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
        
        $tags = new Ml_TagsChange();
        
        $params = $request->getParams();
        
        if ($auth->getIdentity() == $shareInfo['byUid']) {
            
            $tagsForm = $tags->form();
            
            if ($request->isPost() && $tagsForm->isValid($request->getPost())) {
                $tagsArray = $tags->makeArrayOfTags($tagsForm->getValue('tags'));
                
                //It's not guaranteed to work within the tags limit margin,
                //but it's more than ok
                $oldTags = $tags->getShareTags($shareInfo['id']);
                $tagsCounter = sizeof($oldTags);
                
                foreach ($tagsArray as $n => $tag) {
                    if ($tagsCounter >= $config['tags']['limit']) {
                        break;
                    }
                    
                    try {
                        $add = $tags->getAdapter()
                        ->query("INSERT IGNORE INTO `" . $tags->getTableName() .
                         "` (`share`, `people`, `clean`, `raw`, `timestamp`) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)",
                        array($shareInfo['id'], $shareInfo['byUid'], 
                        $tag['clean'], $tag['raw']));
                        
                        if ($add->rowCount()) {
                            $tagsCounter ++;
                        }
                    } catch (Exception $e) {
                    }
                }
            }
        }
        return $this->_forward("tags");
    }
    
    public function deleteAction()
    {
        $auth = Zend_Auth::getInstance();
        $registry = Zend_Registry::getInstance();
        
        $router = $this->getFrontController()->getRouter();
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        
        $tags = new Ml_TagsChange();
        
        $params = $request->getParams();
        
        if ($auth->getIdentity() == $shareInfo['byUid']) {
            $select = $tags->select()
            ->where("id = ?", $params['deletetag'])
            ->where("share = ?", $shareInfo['id'])
            ->where("people = ?", $shareInfo['byUid']);
            
            $row = $tags->fetchRow($select);
            
            if (is_object($row)) {
                $form = $tags->deleteForm();
                $this->view->tag = $row->toArray();
                $this->view->deleteTagForm = $form;
                
                if ($request->isPost() && $form->isValid($request->getPost())) {
                    $row->delete();
                    $isDeleted = true;
                }
            }
        }
        if ($this->_request->isXmlHttpRequest()) {
            $this->_forward("tags");
        } else if (! is_object($row) || isset($isDeleted)) {
            $this->_redirect($router->assemble($params, "sharepage_1stpage"), array("exit"));
        }
    }
    
    /** this method only displays the tags for a called share or redirects
     * the user to the share's mainpage and that's all */
    public function tagsAction()
    {
        $request = $this->getRequest();
        
        $router = $this->getFrontController()->getRouter();
        
        $params = $request->getParams();
        
        if ($this->_request->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
        } else {
            $this->_redirect($router->assemble($params, "sharepage_1stpage"), array("exit"));
        }
        
        $registry = Zend_Registry::getInstance();
        $shareInfo = $registry->get("shareInfo");
        
        $tags = Ml_Tags::getInstance();
        
        $tagsList = $tags->getShareTags($shareInfo['id']);
        
        $this->view->tagsList = $tagsList;
    }
}