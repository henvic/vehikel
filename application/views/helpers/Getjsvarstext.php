<?php
class My_View_Helper_getjsvarstext extends Zend_View_Helper_Abstract
{
	public function getjsvarstext()
	{
		$registry = Zend_Registry::getInstance();
		$script_lines = '';
		if($registry->isRegistered("Layout_JSvars"))
		{
			$layout_vars = $registry->get("Layout_JSvars");
			
			foreach($layout_vars as $key => $value)
			{
				$script_lines .= "var ".$key.' = "'.$value.'"'."\n";
			}
		}
		
		return $script_lines;
	}
}