<?php
class VersionController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $apiConfig = $config['api'];
        
        $doc = new ML_Dom();
        $doc->formatOutput = true;
        
        $apiVersion = $doc->createElement("version");
        
        $apiVersion
        ->appendChild($doc->newTextAttribute("api", $apiConfig['version']));
        
        $doc->appendChild($apiVersion);
        
        $this->_helper->printResponse($doc);
    }
}
