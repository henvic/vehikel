<?php

class ActivityController extends Zend_Controller_Action
{
    public function recentAction()
    {
        //@todo route: do it the right way!
        $router = new Zend_Controller_Router_Rewrite();
        
        $routeConfig =
         new Zend_Config_Ini(APPLICATION_PATH . '/configs/defaultRoutes.ini');
         
        $router->addConfig($routeConfig, 'routes');
        
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $this->_helper->verifyIdentity();
        $recent = new ML_Recent();
        
        if (! $registry->isRegistered("authedUserInfo")) {
            throw new Exception("Not authenticated.");
        }
        
        $userInfo = $registry->get("authedUserInfo");
        
        $uploads = $recent->contactsUploads($userInfo['id']);
        
        //send response
        $doc = new ML_Dom();
        $doc->formatOutput = true;
        
        $rootElement = $doc->createElement("items");
        $doc->appendChild($rootElement);
        
        foreach ($uploads as $share) {
            $shareElement = $doc->createElement("item");
            
            $avatarInfo = unserialize($share['people.avatarInfo']);
            
            if (isset($avatarInfo['secret'])) {
                $iconSecret = $avatarInfo['secret'];
            } else {
                $iconSecret = '';
            }
            
            $shareData = array(
                "type" => "file",
                "id" => $share['id'],
            );
            
            foreach ($shareData as $name => $field) {
                $shareElement
                 ->appendChild($doc->newTextAttribute($name, $field));
            }
            
            $shareData = array(
                "title" => $share['share.title'],
                "short" => $share['share.short'],
                "url" => "http://".$config['webhost'] .
                 $router->assemble(array("username" => $share['people.alias'],
                  "share_id" => $share['id']), "sharepage_1stpage"));
            
            foreach ($shareData as $name => $field) {
                $shareElement->appendChild($doc->newTextElement($name, $field));
            }
            
            $filesizeElement = $doc->createElement("filesize");
            $filesizeElement->appendChild($doc
             ->newTextAttribute("bits", $share['share.fileSize']));
            
            $filesizeElement->appendChild($doc
             ->newTextAttribute("kbytes",
              ceil($share['share.fileSize']/(1024*8))));
             
            $shareElement->appendChild($filesizeElement);
            
            $ownerElement = $doc->createElement("owner");
            
            $shareData = array(
                "id" => $share['people.id'],
                "alias" => $share['people.alias'],
                "realname" => $share['people.name'],
                "iconsecret" => $iconSecret);
            
            foreach ($shareData as $name => $field) {
                $ownerElement
                 ->appendChild($doc->newTextAttribute($name, $field));
            }
            
            $shareElement->appendChild($ownerElement);
            
            $rootElement->appendChild($shareElement);
        }
        
        $this->_helper->printResponse($doc);
    }
}
