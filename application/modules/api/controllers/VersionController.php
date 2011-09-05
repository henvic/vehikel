<?php
class VersionController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        $doc = new ML_Dom();
        $doc->formatOutput = true;
        
        $api_version = $doc->createElement("version");
        $api_version->appendChild($doc->newTextAttribute("api", $config['api']['version']));
        
        $doc->appendChild($api_version);
        
        $this->_helper->printResponse($doc);
    }
}
