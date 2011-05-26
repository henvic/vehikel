<?php

/**
 * Makes it easy to make links listings
 * 
 * @author henrique
 *
 */

class My_View_Helper_linksList extends Zend_View_Helper_Abstract
 	{
 		 /**
 	* Generate a URL List
 	*
    *
    * @param array   $linklist Array with the link elements of the list
    * @param boolean $ordered Specifies ordered/unordered list; default unordered
    * @param array   $attribs Attributes for the ol/ul tag.
    * @return string The list XHTML.
 	*/
 	public function linksList($linklist, $ordered = false, $attribs = false, $escape = false)
 	{
 		$li = Array();
 		if(is_array($linklist))
 		foreach($linklist as $key => $value)
 		{
 			$li[] = '<a href="'.$this->view->url(array('controller'=>$value), 'default').'">'."$key</a>"; 
	    }
	    else
	    foreach($linklist as $key)
	    {
	    	$li[] = '<a href="'.$this->view->url(array('controller'=>$key), 'default').'">'."$key</a>";
	    }
	    
	    $printlist = $this->view->htmlList($li, $ordered, $attribs, $escape);
	    
	    return $printlist;
 	}
 	}
 