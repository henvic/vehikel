<?php

class TagsController extends Zend_Controller_Action
{
    public function userlistAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $this->_helper->loadApiresource->user();
        
        $userInfo = $registry->get("userInfo");
        
        $tags = ML_Tags::getInstance();
        
        $taglist = $tags->getUserTags($userInfo['id']);
        
        $doc = new ML_Dom();
        $doc->formatOutput = true;
        
        $rootElement = $doc->createElement("who");
        
        $rootElement
        ->appendChild($doc->newTextAttribute('id', $userInfo['id']));
        
        $doc->appendChild($rootElement);
        
        $tagsElement = $doc->createElement("tags");
        
        foreach ($taglist as $tag => $count) {
            $tagElement = $doc->createElement("tag");
            $tagElement->appendChild($doc->createTextNode($tag));
            $tagElement->appendChild($doc->newTextAttribute('count', $count));
            $tagsElement->appendChild($tagElement);
        }
        
        $rootElement->appendChild($tagsElement);
        
        $this->_helper->printResponse($doc);
    }
}