<?php

/**
 * This proxy is used for the Ajax scripts
 * 
 * so they can consume all the public API methods
 * with the XMLHttpRequest.
 * 
 * @author henrique
 *
 */

class ProxyController extends Zend_Controller_Action
{
	public function apiAction()
	{
		$registry = Zend_Registry::getInstance();
		$config = $registry->get("config");
		
		$request = $this->getRequest();
		//@todo improve security with whitelist approach
		$method = $request->getParam("method");
		if(!$method || mb_strlen($method) > 250 || mb_strstr($method, ".") || mb_strstr($method, "@") || mb_substr($method, 0, 1) != "/") exit(1);
		
		$method = mb_substr($method, 1);
		
		$response_format = $request->getParam("response_format", "xml");
		
		if($response_format == 'json') $content_type = 'application/json';
		else $content_type = 'text/xml';
		
		header("Content-Type: $content_type");
		$url = "http://".$config['apihost'].$config['apiroot']."/".($method)."?".getenv("QUERY_STRING");//&method=bar is being passed, but whatever...
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$result = curl_exec($ch); 
		curl_close($ch);
		echo $result;
		
		exit(0);
	}
}