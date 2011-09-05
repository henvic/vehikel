<?php

    //protected $add_counter = false;
//pseudosharesetup:
        /*$action = $request->getUserParam("action");if((!isset($registry['signedUserInfo']) || $registry['signedUserInfo']['id'] != $userInfo['id']))
            {
                if($action == "filepage") {
                    $this->viewsShareCounter($shareInfo);
                    
                    if($this->add_counter) $shareInfo['views']++;
                }
                elseif($action == "filestream") {
                    $this->viewsStreamCounter($userInfo);
                    
                    if($this->add_counter) $userInfo['views']++;
                }
            }*/
    /*
    protected function viewsStreamCounter($userInfo)
    {
        $request = $this->getRequest();
        $streamviewsNamespace = new Zend_Session_Namespace('streamViews');
        
        if(isset($streamviewsNamespace->$userInfo['id'])) {}
        elseif(!isset($_SERVER['HTTP_REFERER'])) $updateCounter = true;
        elseif($request->isGet()) {
            $http_referer = $_SERVER['HTTP_REFERER'];
            if($http_referer[mb_strlen($http_referer)-1]== "/")
                $http_referer = mb_substr($http_referer, 0, -1);
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            $userParams = $request->getUserParams();
            $UserParams['page'] = 1;
            
            $test_refer_addr = "http://".$_SERVER['HTTP_HOST'].$router->assemble($userParams, "filestream_1stpage");
            
            if(mb_substr($http_referer , 0, mb_strlen($test_refer_addr)) != $test_refer_addr) $updateCounter = true;
        }
        if(isset($updateCounter) && $updateCounter)
        {
            ////can't keep performance and do it everytime a page is loaded! Save it to a temp table and do with
            //each ~200 at once later
            $People = ML_People::getInstance();
            $People->getAdapter()->query('UPDATE `people` SET `views` = views + 1 WHERE id = ?', $userInfo['id']);
            //$userInfo['views']++;
            $this->add_counter = true;
            $streamviewsNamespace->setExpirationSeconds(7200, $userInfo['id']);
            $streamviewsNamespace->$userInfo['id'] = time();
        }
    }
    
    protected function viewsShareCounter(&$shareInfo)
    {
        $request = $this->getRequest();
        $shareviewsNamespace = new Zend_Session_Namespace('shareViews');
        
        if(isset($shareviewsNamespace->$shareInfo['id'])) {}
        elseif(!isset($_SERVER['HTTP_REFERER'])) $updateCounter = true;
        elseif($request->isGet()) {
            $http_referer = $_SERVER['HTTP_REFERER'];
            if($http_referer[mb_strlen($http_referer)-1]== "/")
                $http_referer = mb_substr($http_referer, 0, -1);
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            $userParams = $request->getUserParams();
            $UserParams['page'] = 1;
            
            $test_refer_addr = "http://".$_SERVER['HTTP_HOST'].$router->assemble($userParams, "sharepage_1stpage");
            
            if(mb_substr($http_referer , 0, mb_strlen($test_refer_addr)) != $test_refer_addr) $updateCounter = true;
        }
        if(isset($updateCounter) && $updateCounter)
        {
            $Share = ML_Share::getInstance();
            $Share->getAdapter()->query('UPDATE `share` SET `views` = views + 1 WHERE id = ?', $shareInfo['id']);
            //$shareInfo['views']++;
            $this->add_counter = true;
            $shareviewsNamespace->setExpirationSeconds(3600, $shareInfo['id']);
            $shareviewsNamespace->$shareInfo['id'] = time();
        }
    }*/