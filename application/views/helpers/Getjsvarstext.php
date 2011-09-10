<?php
class Ml_View_Helper_getjsvarstext extends Zend_View_Helper_Abstract
{
    public function getjsvarstext ()
    {
        $registry = Zend_Registry::getInstance();
        $scriptLines = '';
        
        if ($registry->isRegistered("Layout_JSvars")) {
            $layoutVars = $registry->get("Layout_JSvars");
            
            foreach ($layoutVars as $key => $value) {
                $scriptLines .= "var " . $key . ' = "' . $value . '"' . "\n";
            }
            
        }
        
        return $scriptLines;
    }
}