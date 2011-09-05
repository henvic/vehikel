<?php

/**
 * Index Controller
 *
 *
 * @copyright  2008 Henrique Vicente
 * @since      File available since Release 0.1
*/

class IndexController extends Zend_Controller_Action
{
    public function unsignedAction()
    {
        
    }
    
    public function signedAction()
    {
        
    }
    
    public function indexAction()
    {
        if
        (Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_forward("signed");
        }
        else
        {
            $this->_forward("unsigned");
        }
    }
}
