<?php

class TwitterController extends Zend_Controller_Action
{
	public function tweetAction()
	{
		require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiCurl.php';
		require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiOAuth.php';
		require_once EXTERNAL_LIBRARY_PATH . '/twitter-async/EpiTwitter.php';
		
		$auth = Zend_Auth::getInstance();
		$registry = Zend_Registry::getInstance();
		
		$config = $registry->get("config");
		
		$userInfo = $registry->get("userInfo");
		$shareInfo = $registry->get("shareInfo");
		
		$request = $this->getRequest();
		$params = $request->getParams();
		
		$Twitter = ML_Twitter::getInstance();
		
		$twitterForm = $Twitter->form();
		
		if($request->isPost())
		{
			if($twitterForm->isValid($request->getPost()))
			{
				ML_MagicCookies::check();
				$msg = $twitterForm->getValue('tweet');
				$response = $Twitter->tweet($msg);
				
				$this->view->tweetResponse = $response;
			} else {
				$errors = $twitterForm->getErrors();
				if(in_array("stringLengthTooLong", $errors['tweet']))
				{
					$this->view->tweetResponse = array("error" => "msg_too_long");
				}
			}
		} else {
		}
		
		if(!$this->_request->isXmlHttpRequest())
		{
			$this->_redirect($this->getFrontController()->getRouter()->assemble($params, "sharepage_1stpage"), array("exit"));
		} else {
			$this->_helper->layout->disableLayout();
		}
	}
}