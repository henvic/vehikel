<?php
/**
 * This is used for checking for the age of an action
 * 
 * Useful for the service module
 * 
 * To avoid doing bad stuff
 * 
 * In other words:
 * taking too long to do an action and messing with it
 * by inputing wrong data because of lack of attention
 */
 
class Ml_Timecheck
{
    public function reset()
    {
        $registry = Zend_Registry::getInstance();
        $registry->set("timecheck", time());
    }
    
    public function get()
    {
        $registry = Zend_Registry::getInstance();
        
        if ($registry->isRegistered("timecheck")) {
            return $registry->get("timecheck");
        }
        
        return false;
    }
    
    public function erase()
    {
        $registry = Zend_Registry::getInstance();
        
        if ($registry->isRegistered("timecheck")) {
            $registry->set("timecheck", null);
        }
    }
    
    /**
     * 
     * @param $allow seconds of age allowed or kill the process
     * @return true
     */
    public function check($allow)
    {
        $registry = Zend_Registry::getInstance();
        
        if ($registry->isRegistered("timecheck")) {
            if ($registry->get("timecheck") < time() - $allow) {
                throw new Exception("Process killed by the Timecheck: no input actiontook longer than allowed (i.e., to input data).");
            } else {
                return true;
            }
        } else {
            throw new Exception("Timecheck not set and check was called.");
        }
    }
}