<?php

class Uri extends Zend_Application_Resource_ResourceAbstract
{
	public function init()
	{
	    $registry = Zend_Registry::getInstance();
	    $config = $registry->get("config");
	    
		if($config['web_addr']['force_lower_case'] && $_SERVER['REQUEST_URI'] != $config['webroot'] . "/")
		{
			$lower_request_uri = mb_strtolower($_SERVER['REQUEST_URI']);
			$questionmark_pos = mb_strpos($lower_request_uri, "?");
    		if($_SERVER['REQUEST_URI'] != $lower_request_uri)
	    	{
	        	if($questionmark_pos)
    	    	{
        			$before_qm = mb_substr($_SERVER['REQUEST_URI'], 0, $questionmark_pos);
        			$after_qm = mb_substr($_SERVER['REQUEST_URI'], $questionmark_pos);
        			
 	    	   		$before_qm_tolower = mb_strtolower($before_qm);
    	    		if($before_qm != $before_qm_tolower)
        			$send_addr = $before_qm_tolower.$after_qm;
        		} else $send_addr = $lower_request_uri;
			}
			
			if(!isset($send_addr)) $send_addr = $_SERVER['REQUEST_URI'];
			
			if($questionmark_pos)
			{
				$before_qm = mb_substr($send_addr, 0, $questionmark_pos);
        		$after_qm = mb_substr($send_addr, $questionmark_pos);
        		
        		if($before_qm != '/' && mb_substr($before_qm, -1) == '/')
        		{
        			$before_qm = mb_substr($before_qm, 0, -1);
        			$send_addr = $before_qm . $after_qm;
        		}
			} elseif(mb_substr($send_addr, -1) == '/') {
				$send_addr = mb_substr($send_addr, 0, -1);
			}
			
			//backwards compatibility
			if(isset($send_addr[2]) && $send_addr[1] == "~" && $send_addr[2] != "~") {
				$send_addr = $send_addr[0] . mb_substr($send_addr, 2);
			}
			
			if($send_addr != $_SERVER['REQUEST_URI'])
			{
				header("HTTP/1.1 301 Moved Permanently");
				header("Location: ".$send_addr);
				header("Cache-Control: max-age=86400, must-revalidate");
				
				//cache-control shall be set, see
				//http://developer.yahoo.net/blog/archives/2007/07/high_performanc_9.html
				
				exit;
			}
		}
	}
}
