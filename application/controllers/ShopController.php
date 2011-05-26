<?php

class ShopController extends Zend_Controller_Action
{
	public function cartAction()
	{
		$cart = array();
		$product1 = array("id" => "2233", "name" => "12-month subscription", "price" => "40", "price_unit" => "USD");
$cart['items'] =
array(
	array("quantity" => "1", "product" => $product1)
);
		$cart['total'] = array("price" => "40", "price_unit" => "USD");
		
		$this->view->cart = $cart;
	}
	
	public function ordersAction()
	{
		//list the past orders
	}
	
	public function orderAction()
	{
		
	}
	
	public function paymentAction()
	{
		
	}
}