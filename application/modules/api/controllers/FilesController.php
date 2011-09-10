<?php

class FilesController extends Zend_Controller_Action
{
    public function listAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $request = $this->getRequest();
        
        $this->_helper->loadApiresource->user();
        
        $userInfo = $registry->get("userInfo");
        
        $params = $request->getParams();
        
        $share = Ml_Share::getInstance();
        
        $perPage = $request->getParam("per_page", 20);
        if ($perPage > 100) {
            $perPage = 100;
        } else if ($perPage < 1 || $perPage > 100) {
            $perPage = 20;
        }
        
        $page = $request->getParam("page", 1);
        
        if (! is_natural($page)) {
            $page = 1;
        }
        
        $paginator = $share->getPages($userInfo['id'], $perPage, $page);
        
        if ($paginator->count() < $page) {
            $page = $paginator->count();
        }
        
        $doc = new Ml_Dom();
        $doc->formatOutput = true;
        
        $rootElement = $doc->createElement("files");
        
        $doc->appendChild($rootElement);
        
        $shareData = array(
            "page" => $page,
            "pages" => $paginator->count(),
            "per_page" => $perPage,
            "total" => $paginator->getTotalItemCount(),
        );
        
        foreach ($shareData as $field => $data) {
            $rootElement->appendChild($doc->newTextAttribute($field, $data));
        }
    
        foreach ($paginator->getCurrentItems()->toArray() as $share) {
            $shareElement = $doc->createElement("file");
            
            $shareElement
            ->appendChild($doc->newTextAttribute("id", $share['id']));
            
            $shareElement
            ->appendChild($doc
             ->newTextAttribute("posted", $share['uploadedTime']));
            
            $shareData = array("title" => $share['title'], 
            "filename" => $share['filename'], "filetype" => $share['type'], 
            "short" => $share['short']);
            
            foreach ($shareData as $field => $data) {
                $shareElement->appendChild($doc->newTextElement($field, $data));
            }
            
            $filesizeElement = $doc->createElement("filesize");
            $filesizeElement
            ->appendChild($doc->newTextAttribute("bits", $share['fileSize']));
            
            $filesizeElement
            ->appendChild($doc
            ->newTextAttribute("kbytes", ceil($share['fileSize']/(1024*8))));
            
            $shareElement->appendChild($filesizeElement);
            
            $rootElement->appendChild($shareElement);
        }
    
        $this->_helper->printResponse($doc);
    }
    
    public function infoAction()
    {
        //@todo route: do it the right way!
        $router = new Zend_Controller_Router_Rewrite();
        
        $routeConfig =
        new Zend_Config_Ini(APPLICATION_PATH . '/configs/defaultRoutes.ini');
        
        $router->$routeConfig($config, 'routes');
        
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $request = $this->getRequest();
        
        $params = $request->getParams();
        
        $people = Ml_People::getInstance();
        $favorites = Ml_Favorites::getInstance();
        $comments = Ml_Comments::getInstance();
        $tags = Ml_Tags::getInstance();
        
        $this->_helper->loadApiresource->share();
        $shareInfo = $registry->get("shareInfo");
        
        $userInfo = $people->getById($shareInfo['byUid']);
        
        $tagsList = $tags->getShareTags($shareInfo['id']);
        $countFavs = $favorites->count($shareInfo['id']);
        $countComments = $comments->count($shareInfo['id']);
        
        
        //begin of response
        $doc = new Ml_Dom();
        $doc->formatOutput = true;
        
        $rootElement = $doc->createElement("file");
        $doc->appendChild($rootElement);
        
        $rootElement
        ->appendChild($doc->newTextAttribute('id', $shareInfo['id']));
        
        $rootElement
        ->appendChild($doc->newTextAttribute('secret', $shareInfo['secret']));
        
        $rootElement
        ->appendChild($doc
         ->newTextAttribute('download_secret', $shareInfo['download_secret']));
        
        $ownerElement = $doc->createElement("owner");
        
        $ownerData = array(
            "id" => $userInfo['id'],
            "username" => $userInfo['alias'],
            "realname" => $userInfo['name'],
        );
        
        foreach ($ownerData as $field => $data) {
            $ownerElement->appendChild($doc->newTextAttribute($field, $data));
        }
        
        $rootElement->appendChild($ownerElement);
        
        $shareData = array(
            "title" => $shareInfo['title'],
            "filename" => $shareInfo['filename'],
            "filetype" => $shareInfo['type'],
            "short" => $shareInfo['short'],
            "description" => $shareInfo['description_filtered'],
            "url" => "http://".$config['webhost'] .
             $router->assemble(array("username" => $userInfo['alias'],
              "share_id" => $shareInfo['id']),
              "sharepage_1stpage"),
            "dataurl" => $config['services']['S3']['sharesBucketAddress'] .
              $userInfo['alias'] . "/" . $shareInfo['id'] . "-" .
              $shareInfo['download_secret'] . "/" . $shareInfo['filename'],
            "shorturl" => $config['URLshortening']['addr'] .
               base58_encode($shareInfo['id']),
            "comments" => $countComments,
            "favorites" => $countFavs
        );
        
        foreach ($shareData as $field => $data) {
            $rootElement->appendChild($doc->newTextElement($field, $data));
        }
        
        $filesizeElement = $doc->createElement("filesize");
        
        $filesizeElement
        ->appendChild($doc->newTextAttribute("bits", $shareInfo['fileSize']));
        
        $filesizeElement
        ->appendChild($doc
         ->newTextAttribute("kbytes", ceil($shareInfo['fileSize']/(1024*8))));
        
        $rootElement->appendChild($filesizeElement);
        
        $checksumElement = $doc->createElement("checksum");
        
        $checksumElement
        ->appendChild($doc->newTextAttribute("hash", "md5"));
        
        $checksumElement
        ->appendChild($doc->newTextAttribute("value", $shareInfo['md5']));
        
        $rootElement->appendChild($checksumElement);
        
        $visibilityElement = $doc->createElement("visibility");
        
        $visibilityElement
        ->appendChild($doc->newTextAttribute("ispublic", "1"));
        
        $rootElement->appendChild($visibilityElement);
        
        $datesData = array("posted" => $shareInfo['uploadedTime'],
            "lastupdate" => $shareInfo['lastChange']);
        
        $datesElement = $doc->createElement("dates");
        
        foreach ($datesData as $field => $data) {
            $datesElement
            ->appendChild($doc->newTextAttribute($field, $data));
        }
        
        $rootElement->appendChild($datesElement);
        
        
        $tagsElement = $doc->createElement("tags");
        foreach ($tagsList as $tag) {
            $tagElement = $doc->createElement("tag");
            
            $tagElement
            ->appendChild($doc->newTextAttribute("id", $tag['id']));
            
            $tagElement
            ->appendChild($doc->newTextAttribute("raw", $tag['raw']));
            
            $tagElement
            ->appendChild($doc->createTextNode($tag['clean']));
            
            $tagsElement
            ->appendChild($tagElement);
        }
        
        $rootElement->appendChild($tagsElement);
        
        $this->_helper->printResponse($doc);
    }
    
    public function setmetaAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $request = $this->getRequest();
        
        $this->_helper->verifyIdentity();
        
        $this->_helper->loadApiresource->share();
        
        $share = new Ml_Upload();
        
        $form = $share->_apiSetMetaForm();
        
        if ($request->isPost()) {//@todo should work with PUT also
            if ($form->isValid($request->getPost())) {
                $shareInfo = $registry->get("shareInfo");
                $authedUserInfo = $registry->get("authedUserInfo");
                
                $share->setMeta($authedUserInfo,
                 $shareInfo,
                 $form->getValues(),
                 $form->getErrors());
                
            } else {
                throw new Exception("Invalid post.");
            }
        } else {
            throw new Exception("Not POST HTTP call.");
        }
    }
}