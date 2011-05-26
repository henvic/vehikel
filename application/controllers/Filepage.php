<?php
/***
 * This is not the usual file in here (application/controller)
 * This is just to optimize (avoid loading unnecessary data sometimes)
 * see FilepageController.php to understand
*/

		$userInfo = $registry->get('userInfo');
		$shareInfo = $registry->get("shareInfo");
		if($registry->isRegistered("signedUserInfo")) $signedUserInfo = $registry->get("signedUserInfo");
		$registry->set("isFilepage", true);//for use by the pagination_control
		$page = $request->getUserParam("page");
		
		$Share = ML_Share::getInstance();
		$Tags = ML_Tags::getInstance();
		
		$People = ML_People::getInstance();
		$Comments = ML_Comments::getInstance();
		$Twitter = ML_Twitter::getInstance();
		$Ignore = ML_Ignore::getInstance();
		
		
		$paginator = $Comments->getCommentsPages($shareInfo['id'], $config->share->commentsPerPage, $page);
		
		//Test if there is enough pages or not
		if((!$paginator->count() && $page != 1) || $paginator->getCurrentPageNumber() != $page) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "share_id" => $shareInfo['id']), "sharepage_1stpage"), array("exit"));
		
		$tagsList = $Tags->getShareTags($shareInfo['id']);
		
		if($auth->hasIdentity())
		{
			$Ignore = ML_Ignore::getInstance();
			
			if($auth->getIdentity() == $userInfo['id'] 
			|| !$Ignore->status($userInfo['id'], $auth->getIdentity())
			)
			{
				$commentForm = $Comments->_addForm();
				
				//should The comment form processing should be in the CommentsController?
				if($request->isPost() && $commentForm->isValid($request->getPost()))
				{
					$newCommentMsg = $commentForm->getValue('commentMsg');
					$previewFlag = $commentForm->getValue('getCommentPreview');
					
					if(!empty($previewFlag)) {//check if it is a post or preview
						$this->view->commentPreview = $newCommentMsg;
					}
					else
					{
						$newComment = $Comments->add($newCommentMsg, $auth->getIdentity(), $shareInfo);
						
						if(!$newComment) {
							$newComment = "#commentPreview";
							$this->view->commentPreview = $newCommentMsg;
						} else {
							$request->setParam("comment_id", $newComment);
							return $this->_forward("commentpermalink", "comments");
						}
					}
				}
				
				$this->view->commentForm = $commentForm;
				
				if($Twitter->getSignedUserTwitterAccount())
				 $this->view->twitterForm = $Twitter->form();
			}
		}
		
		$this->view->tagsList = $tagsList;
		$this->view->paginator = $paginator;
		