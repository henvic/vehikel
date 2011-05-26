<?php
/**
 * Flickr-like paginator clone
 * @author henrique
 *
 */
class My_View_Helper_paginator extends Zend_View_Helper_Abstract
{   
 	public function paginator($active_page, $total_pages)
 	{
		$pages = array();
		if($total_pages <= 12) {
			for($page = 1; $page <= $total_pages; $page++) $pages[] = $page;
		} else
		if($active_page >= $total_pages - 8)
		{
			for($page = 1; $page <= 2; $page++) $pages[] = $page;
			
			$left_limit = ($total_pages-$active_page <=2) ? -$total_pages+$active_page+6: 3;
			
			for($page = $active_page - $left_limit; $page <= $total_pages; $page++)
			if($page > 0 && $page <= $total_pages) $pages[] = $page;
		} else
		if($active_page <= 8) {
			
			$right_limit = ($active_page <= 2) ? 7-$active_page : 3;
			
			for($page = 1; $page <= $active_page+$right_limit; $page++) $pages[] = $page;
			
			for($page = $total_pages-1; $page <= $total_pages; $page++) $pages[] = $page;
		} else {
			for($page = 1; $page <= 2; $page++) $pages[] = $page;
			
			for($page = $active_page-3; $page <= $active_page+3; $page++) $pages[] = $page;
			
			for($page = $total_pages-1; $page <= $total_pages; $page++) $pages[] = $page;
		}
		
		return $pages;
	}
}
