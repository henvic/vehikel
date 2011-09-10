<?php
class Ml_View_Helper_addjsvar extends Zend_View_Helper_Abstract
{
    public function addjsvar ($key, $value)
    {
        $registry = Zend_Registry::getInstance();
        
        if (! $registry->isRegistered("Layout_JSvars")) {
            $layoutVars = array();
        } else {
            $layoutVars = $registry->get("Layout_JSvars");
        }
        
        $layoutVars[$key] = $value;
        
        $registry->set("Layout_JSvars", $layoutVars);
    }
}