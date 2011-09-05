<?php

class TagsController extends Zend_Controller_Action
{
    public function userlistAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $this->_helper->loadApiresource->user();
        
        $userInfo = $registry->get("userInfo");
        
        $Tags = ML_Tags::getInstance();
        
        $taglist = $Tags->getUserTags($userInfo['id']);
        
        $doc = new ML_Dom();
        $doc->formatOutput = true;
        
        $root_element = $doc->createElement("who");
        $root_element->appendChild($doc->newTextAttribute('id', $userInfo['id']));
        $doc->appendChild($root_element);
        
        $tags_element = $doc->createElement("tags");
        
        foreach($taglist as $tag => $count)
        {
            $tag_element = $doc->createElement("tag");
            $tag_element->appendChild($doc->createTextNode($tag));
            $tag_element->appendChild($doc->newTextAttribute('count', $count));
            $tags_element->appendChild($tag_element);
        }
        
        $root_element->appendChild($tags_element);
        
        $this->_helper->printResponse($doc);
    }
}