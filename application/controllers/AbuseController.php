<?php
// report_abuse

class AbuseController extends Zend_Controller_Action
{
	
	public function reportAction()
	{
		$auth = Zend_Auth::getInstance();
		
		$request = $this->getRequest();
		
		$Abuse = new ML_Abuse();
		$form = $Abuse->form();
		
		/*
		//@todo reconstruct the url using the router
		//and find out what is the user owner of the page
		//also, limit to report abuse only to share-owned pages
		
		if(isset($_SERVER['HTTP_REFERER'])
		&& strpos(rawurldecode($_SERVER['HTTP_REFERER']), "~"))
		{//@todo outdated
			$form->setDefault("abuse_reference", $_SERVER['HTTP_REFERER']);
		}*/
		
		if($request->isPost() && $form->isValid($request->getPost())) {
			
			$Abuse->insert( array(
				"referer" => $form->getValue("abuse_reference"),
				"description" => $form->getValue("abuse_description"),
				"byUid" => $auth->getIdentity(),
				"byAddr" => $_SERVER['REMOTE_ADDR'],
			));
			
			$this->view->report_done = true;
		}
		
		$this->view->abuseForm = $form;
	}
}
