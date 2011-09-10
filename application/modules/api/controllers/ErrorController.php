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
                if ($registry->isRegistered("notfound")) {
                    $this->getResponse()->setHttpResponseCode(404);
                    $code = 404;
                } else {
                    $this->getResponse()->setHttpResponseCode(500);
                    $code = 500; 
                }
                break; 
        }
        
        $doc = new Ml_Dom();
        
        $doc->formatOutput = true;
        
        $rootElement = $doc->createElement("error");
        
        if (isset($_SERVER['REQUEST_URI'])) {
            $rootElement->appendChild($doc
            ->newTextElement("request", $_SERVER['REQUEST_URI']));
        }
        
        if ($code == 404) {
            $errorMsg = "Not found";
            
        } else {
            $errorMsg = "Bad Request";
        }
        
        $rootElement->appendChild($doc->newTextElement("message", $errorMsg));
        
        if (APPLICATION_ENV == "development") {
            $exceptionElement = $doc->createElement("exception");
            
            $exceptionElement->appendChild($doc->newTextElement("message",
             $errors->exception->getMessage()));
            //$exceptionElement->appendChild($doc->newTextElement("request",
            // print_r($errors->request, true)));
            
            $rootElement->appendChild($exceptionElement);
        }
        
        $doc->appendChild($rootElement);
        
        $this->_helper->printResponse($doc);
    } 
}
