<?php

/**
 * Filepage Controller
 *
* (see shares controller header notes)
 *
 * @copyright  2009 Henrique Vicente
 * @version    $Id:$
 * @since      File available since Release 0.1
 */

class FilepageController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->loadResource->pseudoshareSetUp();
    }
    
    public function filepageAction()
    {
        $registry = Zend_Registry::getInstance();
        $auth = Zend_Auth::getInstance();
        $request = $this->getRequest();
        
        $config = $registry->get('config');
        
        $params = $request->getParams();
        
        $keys = array(
            "deletetag" => array("tags" => "delete"),
            "addtags" => array("tags" => "add"),
            "favorite" => array("favorites" => "switch"),
            "unfavorite" => array("favorites" => "switch"),
            "tweet" => array("twitter" => "tweet"),
        );
        
        $this->_helper->loadResource->pseudoshareSetUp();
        foreach ($keys as $key => $where) {
            if (array_key_exists($key, $params)) {
                return $this->_forward(current($where), key($where));
            }
        }
        
        $userInfo = $registry->get('userInfo');
        $shareInfo = $registry->get("shareInfo");
        
        if ($registry->isRegistered("signedUserInfo")) {
            $signedUserInfo = $registry->get("signedUserInfo");
        }
        
        $registry->set("isFilepage", true);//for use by the pagination_control
        $page = $request->getUserParam("page");
        
        $share = ML_Share::getInstance();
        $tags = ML_Tags::getInstance();
        
        $people = ML_People::getInstance();
        $comments = ML_Comments::getInstance();
        $twitter = ML_Twitter::getInstance();
        $ignore = ML_Ignore::getInstance();
        
        
        $paginator = $comments->getCommentsPages($shareInfo['id'], $config['share']['commentsPerPage'], $page);
        
        //Test if there is enough pages or not
        if ((!$paginator->count() && $page != 1) || $paginator->getCurrentPageNumber() != $page) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage"), array("exit"));
        
        $tagsList = $tags->getShareTags($shareInfo['id']);
        
        if ($auth->hasIdentity()) {
            $ignore = ML_Ignore::getInstance();
            
            if ($auth->getIdentity() == $userInfo['id'] ||
            !$ignore->status($userInfo['id'], $auth->getIdentity())) {
                $commentForm = $comments->_addForm();
                
                //should The comment form processing should be in the CommentsController?
                if ($request->isPost() && $commentForm->isValid($request->getPost())) {
                    $newCommentMsg = $commentForm->getValue('commentMsg');
                    $previewFlag = $commentForm->getValue('getCommentPreview');
                    
                    //check if it is a post or preview
                    if (!empty($previewFlag)) {
                        $this->view->commentPreview = $newCommentMsg;
                    } else {
                        $newComment = $comments->add($newCommentMsg, $auth->getIdentity(), $shareInfo);
                        
                        if (! $newComment) {
                            $newComment = "#commentPreview";
                            $this->view->commentPreview = $newCommentMsg;
                        } else {
                            $request->setParam("comment_id", $newComment);
                            return $this->_forward("commentpermalink", "comments");
                        }
                    }
                }
                
                $this->view->commentForm = $commentForm;
                
                if ($twitter->getSignedUserTwitterAccount()) {
                    $this->view->twitterForm = $twitter->form();
                }
            }
        }
        
        $this->view->tagsList = $tagsList;
        $this->view->paginator = $paginator;
    }
}