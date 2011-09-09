<?php
class ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER: 
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION: 
                echo "Action or controller not found.\n";
                break;
            default: 
                // application error
                echo "This application threw an exception.\n";
                
                //@todo log exceptions using CouchDB
                if (APPLICATION_ENV == "development") {
                    //print_r($this->_getParam('error_handler'));
                    echo "Exception: ".$errors->exception->getMessage()."\n";
                }
                 
                break; 
        }
        exit(1);
    }
}