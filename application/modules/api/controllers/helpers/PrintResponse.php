<?php
class Zend_Controller_Action_Helper_PrintResponse extends
                Zend_Controller_Action_Helper_Abstract
{
    public function direct($xml_doc)
    {
        $request = $this->getRequest();
        
        $response = new Zend_Controller_Response_Http;
        
        $response_format = $request->getParam("response_format", "xml");
        
        $xml_doc->encoding = "utf-8";
        
        $xml = $xml_doc->saveXML();
        
        echo ($response_format == 'json') ? Zend_Json::fromXml($xml, false) : $xml;
    }
}