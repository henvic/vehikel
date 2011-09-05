<?php

/**
 * Comments Controller
 *
 * (see shares controller header notes)
 *
 * @copyright  2009 Henrique Vicente
 * @version    $Id:$
 * @link       http://thinkings.info
 * @since      File available since Release 0.1
 */

class CommentsController extends Zend_Controller_Action
{
    public function _deleteCommentForm($comment_id)
    {
        static $form = '';

        if(!is_object($form))
        {
            $registry = Zend_Registry::getInstance();
            $userInfo = $registry->get("userInfo");
            $shareInfo = $registry->get("shareInfo");
            
            require_once APPLICATION_PATH . '/forms/DeleteComment.php';
            
            $form = new Form_DeleteComment(array(
                'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id'], "comment_id" => $comment_id), "deletecomment"),
                'method' => 'post',
            ));
        }

        $form->setDefault("hash", $registry->get('globalHash'));

        return $form;
    }
    
    public function init()
    {
        $this->_helper->loadResource->pseudoshareSetUp();
    }
    
    public function editAction()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $request = $this->getRequest();
        
        $userInfo = $registry->getInstance();
        
        $Comments = ML_Comments::getInstance();
        $Ignore = ML_Ignore::getInstance();
        
        $comment_id = $request->getUserParam("comment_id");
        
        $comment = $Comments->getById($comment_id);
        
        if(!$comment) throw new Exception("Comment $comment_id does not exists associated with this share.");
        
        if($comment['uid'] != $auth->getIdentity() || ($auth->getIdentity()!=$comment['byUid'] && $Ignore->status($userInfo['id'], $auth->getIdentity())))
        {
            throw new Exception("Can't edit the comment. Either ignored or not the author.");
        }
        
        $registry->set("commentInfo", $comment);
        
        $form = $Comments->_addForm();
        
        $form->setDefault("commentMsg", $comment['comments']);
        
        if($request->isPost() && $form->isValid($request->getPost()))
        {
            $commentMsg = $form->getValue("commentMsg");
            $isSubmit = $form->getValue("commentPost");
            if(!empty($isSubmit))
            {
                $purifier = ML_HtmlPurifier::getInstance();
                
                $comments_filtered = $purifier->purify($commentMsg);
                
                $Comments->update(array("comments" => $commentMsg, "comments_filtered" => $comments_filtered), $Comments->getAdapter()->quoteInto("id = ?", $comment['id']));
                $request->setParam("comment_id", $comment['id']);
                return $this->_forward("commentpermalink");
            }
            
            $this->view->commentPreview = $commentMsg;
        }
        
        $this->view->commentForm = $form;
    }
    
    public function deleteAction()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        $request = $this->getRequest();
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $Comments = ML_Comments::getInstance();
        
        $comment_id = $request->getUserParam("comment_id");
        
        $comment = $Comments->getById($comment_id);
        
        if(!$comment) {
            throw new Exception("Comment $comment_id does not exists associated with this share.");
        }
        
        $registry->set("commentInfo", $comment);
        
        $form = $this->_deleteCommentForm($comment['id']);
        
        if($request->isPost() && $form->isValid($request->getPost()))
        {
            $Comments->delete($Comments->getAdapter()->quoteInto("id = ?", $comment['id']));
            
            $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage"), array("exit"));
        }
        
        $this->view->form = $form;
        $this->view->comment = $comment;
    }
    
    public function commentpermalinkAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        
        $comment_id = $request->getParam("comment_id");
        //don't use getUserParam
        //because when we have new comments they are dispatched to here
        //and the setParam is used
        
        $Comments = ML_Comments::getInstance();
        
        $position = $Comments->getCommentPosition($comment_id, $registry['shareInfo']['id'], $config['share']['commentsPerPage']);
        
        if(!is_array($position)) throw new Exception("Comment $comment_id does not exists associated with this share.");
        
        $route = ($position['page'] == 1) ? 'sharepage_1stpage' : 'sharepage';
        
        $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id'], "page" => $position['page']), $route).'#comment'.$comment_id, array("exit"));
    }
}