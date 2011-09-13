<?php

class Ml_Resource_Uri extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if ($config['web_addr']['force_lower_case'] &&
         $_SERVER['REQUEST_URI'] != $config['webroot'] . "/") {
            $lowerRequestUri = mb_strtolower($_SERVER['REQUEST_URI']);
            $qmarkPos = mb_strpos($lowerRequestUri, "?");
            if ($_SERVER['REQUEST_URI'] != $lowerRequestUri) {
                if ($qmarkPos) {
                    $befQm = mb_substr($_SERVER['REQUEST_URI'], 0, $qmarkPos);
                    $afterQm = mb_substr($_SERVER['REQUEST_URI'], $qmarkPos);
                    
                    $befQmTolower = mb_strtolower($befQm);
                    
                    if ($befQm != $befQmTolower) {
                        $sendAddr = $befQmTolower.$afterQm;
                    }
                    
                } else {
                    $sendAddr = $lowerRequestUri;
                }
            }
            
            if (! isset($sendAddr)) {
                $sendAddr = $_SERVER['REQUEST_URI'];
            }
            
            if ($qmarkPos) {
                $befQm = mb_substr($sendAddr, 0, $qmarkPos);
                $afterQm = mb_substr($sendAddr, $qmarkPos);
                
                if ($befQm != '/' && mb_substr($befQm, - 1) == '/') {
                    $befQm = mb_substr($befQm, 0, -1);
                    $sendAddr = $befQm . $afterQm;
                }
            } else if (mb_substr($sendAddr, -1) == '/') {
                $sendAddr = mb_substr($sendAddr, 0, -1);
            }
            
            //backwards compatibility
            if (isset($sendAddr[2]) && $sendAddr[1] == "~" &&
             $sendAddr[2] != "~") {
                $sendAddr = $sendAddr[0] . mb_substr($sendAddr, 2);
            }
            
            if ($sendAddr != $_SERVER['REQUEST_URI']) {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: ".$sendAddr);
                header("Cache-Control: max-age=86400, must-revalidate");
                
                //cache-control shall be set: see
                //http://developer.yahoo.net/blog/archives/2007/07/high_performanc_9.html
                
                exit;
            }
        }
    }
}
