<?php
class TestController extends Zend_Controller_Action
{
    public function echoAction()
    {
        $doc = new Ml_Dom();
        $doc->formatOutput = true;
        
        $element = $doc->newTextElement("echo", $_SERVER['REQUEST_URI']);
        $doc->appendChild($element);
        
        $this->_helper->printResponse($doc);
    }
}