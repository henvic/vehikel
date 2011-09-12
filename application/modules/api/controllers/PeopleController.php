<?php

class PeopleController extends Zend_Controller_Action
{
    public function infoAction()
    {
        //@todo route: do it the right way!
        $router = new Zend_Controller_Router_Rewrite();
        
        $routeConfig =
         new Zend_Config_Ini(APPLICATION_PATH . '/configs/defaultRoutes.ini');
        
        $router->addConfig($routeConfig, 'routes');
        
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
        $people = Ml_Model_People::getInstance();
        $profile = new Ml_Model_Profile();
        $share = Ml_Model_Share::getInstance();
        
        if (isset($params['username'])) {
            $userInfo = $people->getByUsername($params['username']);
        } else if (isset($params['user_id'])) {
            $userInfo = $people->getById($params['user_id']);
        } else if (isset($params['email'])) {
            $userInfo = $people->getByEmail($params['email']);
            
            if (! empty($userInfo) && $userInfo['private_email'] == true) {
                $registry->set("notfound", true);
                throw new Exception("User not found.");
            }
        } else {
            throw new Exception("No user params were given.");
        }
        
        if (empty($userInfo)) {
            $registry->set("notfound", true);
            throw new Exception("User not found.");
        }
        
        $profileInfo = $profile->getById($userInfo['id']);
        
        $doc = new Ml_Model_Dom();
        $doc->formatOutput = true;
        
        $rootElement = $doc->createElement("person");
        $doc->appendChild($rootElement);
        
        $rootElement
        ->appendChild($doc->newTextAttribute('id', $userInfo['id']));
        
        $avatarInfo = unserialize($userInfo['avatarInfo']);
        
        if (isset($avatarInfo['secret'])) {
            $iconSecret = $avatarInfo['secret'];
        } else {
            $iconSecret = '';
        }
        
        $rootElement
        ->appendChild($doc->newTextAttribute('iconsecret', $iconSecret));
        
        $userData = array(
            "username" => $userInfo['alias'],
            "realname" => $userInfo['name'],
        );
        
        if (! $userInfo['private_email']) {
            $userData["mbox_sha1sum"] = sha1("mailto:".$userInfo['email']);
        }
        
        $userData["location"] = $profileInfo['location'];
        
        $userData["url"] = "http://" . $config['webhost'] .
         $router->assemble(array("username" => $userInfo['alias']),
         "filestream_1stpage");
        
        foreach ($userData as $field => $data) {
            $rootElement->appendChild($doc->newTextElement($field, $data));
        }
        
        $sharesCounter =
        $share->getAdapter()->fetchOne($share->select()
         ->from($share->getTableName(), 'count(*)')
         ->where("byUid = ?", $userInfo['id']));
        
        $sharesElement = $doc->createElement("files");
        $sharesCounterElement = $doc->createElement("count");
        
        $sharesCounterElement
        ->appendChild($doc->createTextNode($sharesCounter));
        
        $sharesElement->appendChild($sharesCounterElement);
        $rootElement->appendChild($sharesElement);
        
        $this->_helper->printResponse($doc);
    }
}