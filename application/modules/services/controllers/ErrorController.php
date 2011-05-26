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
                //if(APPLICATION_ENV == "development") print_r($this->_getParam('error_handler'));
                echo "Error Controller: There was an error with this application.\n";
                
                echo "Exception: ".$errors->exception->getMessage()."\n";
                
                /*echo "\nRequest:\n";
                
                print_r($errors->request);
                
                echo "\n";*/
                 
                break; 
        }
		exit(1);
	}
}