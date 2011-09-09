<?php

class StaticController extends Zend_Controller_Action
{
    public function docsAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $request = $this->getRequest();
        
        $intReqUri = mb_substr($_SERVER['REQUEST_URI'], mb_strlen($config['webroot']));
        
        $uri = explode("?", $intReqUri, 2);
        
        if (mb_substr($uri[0], -1) != '/') {
            $findPath = mb_substr($uri[0], 1);
        } else {
            $findPath = mb_substr($uri[0], 1, -1);;
        }
        
        if (! mb_strpos($findPath, ".") && ! mb_strpos($findPath, "\\")) {
            $getFsPath = APPLICATION_PATH . "/views/scripts/static/" . $findPath . ".phtml";
            
            //security: check if exists, etc
            $findPathRealpath = realpath($getFsPath);
            
            //security / consistency: avoids '../' in the path
            if ($findPathRealpath && $findPathRealpath == $getFsPath) {
                $found = true;
                //instead of docs.phtml...
                $this->_helper->viewRenderer->setScriptAction($findPath);
            }
        }
        
        //If not found dispatch to a method which doesn't exists
        if (! isset($found)) {
            $this->_forward("notstatic");
        }
    }
}