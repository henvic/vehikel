<?php

/**
 * Filepage Controller
 *
* (see shares controller header notes)
 *
 * @copyright  2009 Henrique Vicente
 * @version    $Id:$
 * @link       http://thinkings.info
 * @since      File available since Release 0.1
 */

class FilepageController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->loadResource->pseudoshareSetUp();
	}
	
	public function filepageAction()
	{
		$registry = Zend_Registry::getInstance();
		$auth = Zend_Auth::getInstance();
		$request = $this->getRequest();
		
		$config = $registry->get('config');
		
		$params = $request->getParams();
		
		$keys = array(
			"deletetag" => array("tags" => "delete"),
			"addtags" => array("tags" => "add"),
			"favorite" => array("favorites" => "switch"),
			"unfavorite" => array("favorites" => "switch"),
			"tweet" => array("twitter" => "tweet"),
		);
		
		$this->_helper->loadResource->pseudoshareSetUp();
		
		foreach($keys as $key => $where)
		{
			if(array_key_exists($key, $params)) {
				return $this->_forward(current($where), key($where));
			}
		}
		
		//!!!VERY IMPORTANT!!!
		/*
		 * It is what is loaded if one of the above keys are
		 * not called
		 */
		
		require APPLICATION_PATH."/controllers/Filepage.php";
		
	}
}