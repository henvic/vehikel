<?php
class ML_Abuse extends ML_getModel
{	
	protected $_name = "abuse";

	public function form()
	{
		static $form = '';

		if(!is_object($form))
		{
			require_once APPLICATION_PATH . '/forms/Abuse.php';
			 
			$form = new Form_Abuse(array(
				'action' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "report_abuse"),
                'method' => 'post',
			));
		}
		
		return $form;
	}
}