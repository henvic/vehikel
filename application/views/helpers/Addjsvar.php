<?php
class My_View_Helper_addjsvar extends Zend_View_Helper_Abstract
{
	public function addjsvar($key, $value)
	{
		$registry = Zend_Registry::getInstance();
		
		if(!$registry->isRegistered("Layout_JSvars"))
		{
			$layout_vars = array();
		} else $layout_vars = $registry->get("Layout_JSvars");
		
		$layout_vars[$key] = $value;
		
		$registry->set("Layout_JSvars", $layout_vars);
	}
}