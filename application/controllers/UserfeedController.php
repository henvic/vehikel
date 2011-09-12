<?php

class UserfeedController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_helper->loadResource->pseudoshareSetUp();
    }
    
    public function userfeedAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $s3config = $config['services']['S3'];
        
        $request = $this->getRequest();
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $userInfo = $registry->get("userInfo");
        
        $share = Ml_Model_Share::getInstance();
        
        $paginator = $share->getPages($userInfo['id'], 20, 1);
        
        $currentItems = $paginator->getCurrentItems()->toArray();
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $doc = new Ml_Model_Dom();
        $doc->formatOutput = true;
        $doc->encoding = "utf-8";
        
        $rootElement = $doc->createElement("rss");
        
        $rootElement
        ->appendChild($doc->newTextAttribute("version", "2.0"));
        
        $rootElement
        ->appendChild($doc->newTextAttribute("xmlns:atom", "http://www.w3.org/2005/Atom"));
        
        $doc->appendChild($rootElement);
        
        $channelElement = $doc->createElement("channel");
        
        $channelElement
        ->appendChild($doc
         ->newTextElement("description", "Recent uploads to " . $config['applicationname'] . "."));
        
        $rootElement->appendChild($channelElement);
        
        $userLink = "http://" . $config['webhost'] .
         $router->assemble(array("username" =>
          $userInfo['alias']),
         "filestream_1stpage");
        
        /*Instead of...
         * $firstElement = current($currentItems);
        if(!is_array($firstElement)) $x = 1;
        else $x =2;
        should see the last changed or published of the items...
        */
        
        $userData = array(
            "title" => "Shared files from ".$userInfo['name'],
            "link" => $userLink,
            //"description" => //$profileInfo['about_filtered'], //=> it's doesn't render as expected!
            "generator" => "http://".$config['webhost'],
        //http://validator.w3.org/feed/docs/rss2.html
            "docs" => "http://blogs.law.harvard.edu/tech/rss",
            "ttl" => "180",
        );
        
        if (empty($config['webroot'])) {
            $userData['generator'] .= '/';
        } else {
            $userData['generator'] .= "/" . $config['webroot'] . "/";
        }
        
        foreach ($userData as $field => $value) {
                $channelElement->appendChild($doc->newTextElement($field, $value));
        }
        
        $avatarInfo = unserialize($userInfo['avatarInfo']);
        
        if (isset($avatarInfo['secret'])) {
            $iconSecret = $avatarInfo['secret'];
        } else {
            $iconSecret = '';
        }
        
        if (! empty($iconSecret)) {
            $imageElement = $doc->createElement("image");
            $picUri = $s3config['headshotsBucketAddress'] . $userInfo['id'] . '-' . $iconSecret . '-s.jpg';
            $imageElement->appendChild($doc->newTextElement("url", $picUri));
            $imageElement->appendChild($doc->newTextElement("title", "Shares from " . $userInfo['name']));
            $imageElement->appendChild($doc->newTextElement("link", $userLink));
            
            $channelElement->appendChild($imageElement);
        }
        
        $atomLink = $doc->createElement("atom:link");
        
        $atomLink->appendChild($doc->newTextAttribute("href",
         "http://" . $config['webhost'] .
         $router->assemble(array("username" =>
          $userInfo['alias']), "userfeed")));
         
        $atomLink->appendChild($doc->newTextAttribute("rel", "self"));
        $atomLink->appendChild($doc->newTextAttribute("type", "application/rss+xml"));
        $channelElement->appendChild($atomLink);
        
        foreach ($currentItems as $share) {
            $shareElement = $doc->createElement("item");
            
            if (empty($share['description_filtered'])) {
                $description = $this->view->escape($share['short']);
            } else {
                $description = $share['description_filtered'];
            }
            
            $link = "http://" . $config['webhost'] .
             $router->assemble(array("username" => $userInfo['alias'],
              "share_id" => $share['id']), "sharepage_1stpage");
            
            $shareDate = new Zend_Date($share['uploadedTime'], Zend_Date::ISO_8601);
            
            $shareData = array("title" => $share['title'], "link" => $link, 
            "description" => $description, 
            "pubDate" => $shareDate->get(Zend_Date::RSS));
            
            foreach ($shareData as $field => $data) {
                $shareElement->appendChild($doc->newTextElement($field, $data));
            }
            
            $shareElement->appendChild($doc->newTextElement("guid", $link));
            
            
            $enclosureElement = $doc->createElement("enclosure");
            
            $enclosureElement
            ->appendChild($doc->newTextAttribute("url",
             $this->view->escape($s3config['sharesBucketAddress'] .
              $userInfo['alias'] . "/" . $share['id'] . "-" .
               $share['download_secret'] . "/" . $share['filename'])));
            
            $enclosureElement
            ->appendChild($doc->newTextAttribute("length", $share['fileSize']));
            
            $enclosureElement
            ->appendChild($doc->newTextAttribute("type", $share['type']));
            
            $shareElement->appendChild($enclosureElement);
            
            $channelElement->appendChild($shareElement);
        }
        
        $request = $this->getRequest();
        
        $response = new Zend_Controller_Response_Http;
        
        //I'm not serving as rss+xml for browsers because they get it wrong
        if (isset($_SERVER['HTTP_USER_AGENT']) &&
         strstr($_SERVER['HTTP_USER_AGENT'], "Mozilla")) {
            $contenttype = 'text/xml'; 
        } else {
            $contenttype = 'application/rss+xml';
        }
        
        header('Content-Type: '.$contenttype.'; charset=utf-8');
        echo $doc->saveXML();
        exit;
    }
}