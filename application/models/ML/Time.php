<?php

class ML_Time
{
	public static function ago($date, $format = null)
	{
		$zend_date = new Zend_Date();
		
		if(!$format) $format = Zend_Date::ISO_8601;
		$zend_date->set($date, $format);
		
		$ago = time()-$zend_date->getTimestamp();
		
		if($ago < 120) return "less than $ago seconds ago";
		
		if($ago < 60*120) return ceil($ago/60)." minutes ago";
		
		if($ago < 60*60*48) return "about " . floor($ago/(60*60))." hours ago";
		
		if($ago < 60*60*24*60) return floor($ago/(60*60*24))." days ago";
		
		//if($ago < 60*60*24*7*12)
		return floor($ago/(60*60*24*7))." weeks ago";
	}
}
