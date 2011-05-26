<?php 
// application/controllers/ErrorController.php
//http://www.survivethedeepend.com/zendframeworkbook/en/1.0/handling.application.errors.gracefully
//see $this->statusCode for view 
/**
 * ErrorController
 */ 

class ErrorController extends Zend_Controller_Action 
{ 
    public function errorAction() 
    {
    	$registry = Zend_Registry::getInstance();
    	
    	$request = $this->getRequest();
    	
    	$params = $request->getParams();
    	
        // Ensure the default view suffix is used so we always return good 
        // content
        $this->_helper->viewRenderer->setViewSuffix('phtml');

        // Grab the error object from the request
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER: 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION: 
                // 404 error -- controller or action not found 
                $this->getResponse()->setHttpResponseCode(404);
                if(isset($params['ajax']) && APPLICATION_ENV != 'development') exit();
                $this->view->statusCode = 404; 
                $this->view->message = 'Page not found'; 
                break; 
            default: 
                // application error
		        if($registry->isRegistered("notfound")) {
                	$this->getResponse()->setHttpResponseCode(404);
                	if(isset($params['ajax']) && APPLICATION_ENV != 'development') exit();
                	$this->view->statusCode = 404;
                	$this->view->message = 'Page not found';
                } else {
                	$this->getResponse()->setHttpResponseCode(500);
                	if(isset($params['ajax']) && APPLICATION_ENV != 'development') exit();
                	$this->view->statusCode = 500;
                	$this->view->message = 'Application error';
                }
                
                break; 
        }
    	
        // pass the actual exception object to the view
        $this->view->exception = $errors->exception; 
        
        // pass the request to the view
        $this->view->request   = $errors->request; 
    } 
}
