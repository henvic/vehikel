<?php

/**
 * Comments Controller
 *
 * (see shares controller header notes)
 *
 * @copyright  2009 Henrique Vicente
 * @version    $Id:$
 * @since      File available since Release 0.1
 * @todo needs to be designed again
 */

class CommentsController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->loadResource->pseudoshareSetUp();
    }
    
    public function editAction()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        
        $request = $this->getRequest();
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $comments = Ml_Model_Comments::getInstance();
        $ignore = Ml_Model_Ignore::getInstance();
        
        $commentId = $request->getUserParam("comment_id");
        
        $comment = $comments->getById($commentId);
        
        if (! $comment) {
            $registry->set("notfound", true);
            throw new Exception("Comment $commentId does not exists.");
        }
        
        if ($comment['uid'] != $auth->getIdentity() ||
        ($auth->getIdentity() != $comment['byUid'] &&
        $ignore->status($userInfo['id'], $auth->getIdentity()))) {
            $registry->set("notfound", true);
            throw new Exception("Can't edit the comment. Either ignored or not the author.");
        }
        
        if ($comment['share'] != $shareInfo['id']) {
            $registry->set("notfound", true);
            throw new Exception("From another share.");
        }
        
        $registry->set("commentInfo", $comment);
        
        $form = $comments->addForm();
        
        $form->setDefault("commentMsg", $comment['comments']);
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $commentMsg = $form->getValue("commentMsg");
            $isSubmit = $form->getValue("commentPost");
            
            if (! empty($isSubmit)) {
                $comments->update($comment['id'], $commentMsg);
                
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
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $request = $this->getRequest();
        
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $comments = Ml_Model_Comments::getInstance();
        
        $commentId = $request->getUserParam("comment_id");
        
        $comment = $comments->getById($commentId);
        
        if (! $comment) {
            $registry->set("notfound", true);
            throw new Exception("Comment $commentId does not exists.");
        }
        
        if ($comment['share'] != $shareInfo['id']) {
            $registry->set("notfound", true);
            throw new Exception("From another share.");
        }
        
        $registry->set("commentInfo", $comment);
        
        $form = $comments->deleteForm($comment['id']);
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $comments->delete($comment['id']);
            
            $this->_redirect($router->assemble(array("username"
            => $userInfo['alias'],
             "share_id" =>
             $shareInfo['id']), "sharepage_1stpage"), array("exit"));
        }
        
        $this->view->form = $form;
        $this->view->comment = $comment;
    }
    
    public function commentpermalinkAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        $userInfo = $registry->get("userInfo");
        $shareInfo = $registry->get("shareInfo");
        
        $request = $this->getRequest();
        
        $commentId = $request->getParam("comment_id");
        //don't use getUserParam
        //because when we have new comments they are dispatched to here
        //and the setParam is used
        
        $comments = Ml_Model_Comments::getInstance();
        
        $position = $comments->getCommentPosition($commentId, 
         $registry['shareInfo']['id'], $config['share']['commentsPerPage']);
        
        if (! is_array($position)) {
            $registry->set("notfound", true);
            throw new Exception("Comment $commentId does not exists.");
        }
        
        if ($position['page'] == 1) {
            $route = 'sharepage_1stpage';
        } else {
            $route = 'sharepage';
        }
        
        $this->_redirect($router
        ->assemble(array("username" => $userInfo['alias'],
        "share_id" => $shareInfo['id'], 
        "page" => $position['page']),
        $route) . '#comment' . $commentId, array("exit"));
    }
}