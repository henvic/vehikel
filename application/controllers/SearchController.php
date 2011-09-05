<?php

class SearchController extends Zend_Controller_Action
{
    public function searchAction()
    {
        Zend_Search_Lucene::create("asdasd");
        $doc = new Zend_Search_Lucene_Document();
        
    }
}