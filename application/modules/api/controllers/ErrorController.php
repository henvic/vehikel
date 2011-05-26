<?php 

class ErrorController extends Zend_Controller_Action 
{
    public function errorAction() 
    {
    	$registry = Zend_Registry::getInstance();
	    // Grab the error object from the request
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) { 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER: 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION: 
                // 404 error -- controller or action not found 
                $this->getResponse()->setHttpResponseCode(404);
                $code = 404;
                break; 
            default:
                // application error 
                if($registry->isRegistered("notfound")) {
                	$this->getResponse()->setHttpResponseCode(404);
                	$code = 404;
                } else {
                	$this->getResponse()->setHttpResponseCode(500);
                	$code = 500; 
                }
                break; 
        }
        
        $doc = new ML_Dom();
        
		$doc->formatOutput = true;
        
		$root_element = $doc->createElement("error");
		
		if(isset($_SERVER['REQUEST_URI']))
		{
			$root_element->appendChild($doc->newTextElement("request", $_SERVER['REQUEST_URI']));
		}
		
		if($code == 404)
		{
			$error_msg = "Not found";
			
		} else {
			$error_msg = "Bad Request";
		}
		
		$root_element->appendChild($doc->newTextElement("message", $error_msg));
		
        if(APPLICATION_ENV == "development")
        {
        	$exception_element = $doc->createElement("exception");
			
        	$exception_element->appendChild($doc->newTextElement("message", $errors->exception->getMessage()));
        	//$exception_element->appendChild($doc->newTextElement("request", print_r($errors->request, true)));
        	
			$root_element->appendChild($exception_element);
        }
        
        $doc->appendChild($root_element);
        
        $this->_helper->printResponse($doc);
    } 
}
