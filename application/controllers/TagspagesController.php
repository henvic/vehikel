<?php
class TagspagesController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->loadResource->pseudoshareSetUp();
	}
	
	public function tagpageAction()
	{
		$registry = Zend_Registry::getInstance();
		$Tags = ML_Tags::getInstance();
		
		$request = $this->getRequest();
		
		$userInfo = $registry->get('userInfo');
		
		$cleanTag = $request->getUserParam("tag");
		$page = $request->getUserParam("page");
		
		$paginator = $Tags->getTagPage($userInfo['id'], $cleanTag, 25, $page);
		
		//Test if there is enough pages or not
		if((!$paginator->count() && $page != 1) || $paginator->getCurrentPageNumber() != $page) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array("username" => $userInfo['alias'], "tag" => $cleanTag), "tagpage_1stpage"), array("exit"));
		
		if(!$paginator->count())//If not, send 404 error code header
		{
			$this->getResponse()->setHttpResponseCode(404);
		}
		
		$this->view->tagname = $cleanTag;
		$this->view->paginator = $paginator;
	}
	
	public function taglistAction()
	{
		//no pagination for this action because it would interfere with tagpage method
		$registry = Zend_Registry::getInstance();
		
		$userInfo = $registry->get("userInfo");
		
		$Tags = ML_Tags::getInstance();
		
		$this->view->taglist = $Tags->getUserTags($userInfo['id']);
	}
}