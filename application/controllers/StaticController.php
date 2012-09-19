<?php

class StaticController extends Ml_Controller_Action
{
    public function docsAction()
    {
        $request = $this->getRequest();
        
        $intReqUri = mb_substr($_SERVER['REQUEST_URI'], mb_strlen($this->_config['webroot']));
        
        // avoid the Null-Byte attack (%00 at the URI) which is the same as 'end of string'
        $safeIntReqUri = str_replace(chr(0), "", $intReqUri);
        
        $uri = explode("?", $safeIntReqUri, 2);
        
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
            return $this->_forward("not-found", "error", "default", array("error" => "page-not-found"));
        }
    }
}