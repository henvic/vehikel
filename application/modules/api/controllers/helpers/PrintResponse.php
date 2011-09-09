<?php
class Zend_Controller_Action_Helper_PrintResponse extends
                Zend_Controller_Action_Helper_Abstract
{
    public function direct($xmlDoc)
    {
        $request = $this->getRequest();
        
        $response = new Zend_Controller_Response_Http;
        
        $responseFormat = $request->getParam("response_format", "xml");
        
        $xmlDoc->encoding = "utf-8";
        
        $xml = $xmlDoc->saveXML();
        
        if ($responseFormat == 'json') {
            echo Zend_Json::fromXml($xml, false);
        } else {
            echo $xml;
        }
    }
}