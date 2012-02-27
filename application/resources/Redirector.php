<?php
class Ml_Resource_Redirector
{
    public function shortLink()
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $uri = $_SERVER['REQUEST_URI'];
        
        if ($uri == '/') {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: http://".$config['webhost']."/");
            exit();
        }
        
        //clear the first and the last '/'
        if (mb_substr($uri, -1) == '/') {
            $uri = mb_substr($uri, 1, -1);
        } else {
            $uri = mb_substr($uri, 1);
        }
        
        $numbers = new Ml_Model_Numbers();
        
        $id =  $numbers->base58Decode($uri);
        
        if ($id) {
            //Is it a valid share ID?
            $share = Ml_Model_Share::getInstance();
            $people = Ml_Model_People::getInstance();
            
            $shareInfo = $share->getById($id);
            
            if ($shareInfo) {
                $userInfo = $people->getById($shareInfo['byUid']);
                
                $link =
                "http://" . $config['webhost'] . "/" .
                 urlencode($userInfo['alias']) . "/" . $shareInfo['id'];
                
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: " . $link);
                exit(); //nothing more to do
            }
        }
        
        //If nothing matches
        $link = "http://" . $config['webhost'] . "/not-found/" . urlencode(utf8_encode($uri));
        header("Location: " . $link);
        
        //the redirector stops the default bootstrap, always
        exit();
    }
}