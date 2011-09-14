<?php

class Ml_Model_Exception extends Zend_Exception
{
    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {
        // just pass ahead if it was throw from the CLI
        if (php_sapi_name() == "cli") {
            return parent::__construct($msg, (int) $code, $previous);
        } else {
            $response = new Zend_Controller_Response_Http();
            
            $response->setHttpResponseCode(500);
            
            ob_get_clean();
            
            ob_start();
            
            require APPLICATION_PATH . "/layout/scripts/exception.phtml";
            
            $outputBuffer = ob_get_clean();
            
            $response->setBody($outputBuffer);
            
            $response->sendResponse();
            
            trigger_error($msg, E_USER_ERROR);
            
            exit;
        }
    }
}