<?php

class CreditsController extends Zend_Controller_Action
{   
    public function init()
    {
        if(!Zend_Auth::getInstance()->hasIdentity()) {
    	    Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
    	}
    }
    
    public function creditsAction()
    {
    	$registry = Zend_Registry::getInstance();
    	
    	$signedUserInfo = $registry->get("signedUserInfo");
    	
    	$request = $this->getRequest();
    	
    	$Credits = ML_Credits::getInstance();
    	
    	$Coupons = ML_Coupons::getInstance();
    	
    	$redeemForm = $Coupons->_RedeemForm();
    	
    	if($request->isPost()) {
    		if($redeemForm->isValid($request->getPost()))
    		{
    			$transaction_id = $Credits->couponTransaction($signedUserInfo['id'], $redeemForm->getValue("redeem"));
    			
    			if($transaction_id)
    			{
    				$redeemForm->setDefault("redeem", "");
    				$this->view->transaction_id = $transaction_id;
    			}
    		}
    	}
    	
    	$this->view->redeemForm = $redeemForm;
    }
    
    /**
     * 
     * /account/order/history
     */
    public function ordersAction()
    {
    	$registry = Zend_Registry::getInstance();
    	$config = $registry->get("config");
    	
    	$signedUserInfo = $registry->get("signedUserInfo");
    	
    	$request = $this->getRequest();
    	
    	$page = $request->getUserParam("page");
    	
    	$Credits = ML_Credits::getInstance();
    	$paginator = $Credits->history($signedUserInfo['id'], $config['orders']['perPage'], $page);
		
		//Test if there is enough pages or not
		if((!$paginator->count() && $page != 1) || $paginator->getCurrentPageNumber() != $page) $this->_redirect(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), "orders_1stpage"), array("exit"));
		
    	$this->view->paginator = $paginator;
    }
    
    public function orderAction()
    {
    	$registry = Zend_Registry::getInstance();
    	$config = $registry->get("config");
    	
    	$signedUserInfo = $registry->get("signedUserInfo");
    	
    	$request = $this->getRequest();
    	
    	$order_pid = $request->getUserParam("order_pid");
    	
    	$Credits = ML_Credits::getInstance();
    	
    	$order = $Credits->getByPId($order_pid);
    	if(!$order || $order['uid'] != $signedUserInfo['id'])
    	{
    		$registry->set("notfound", true);
    		throw new Exception("Order doesn't exists.");
    	}
    	if($order['reason_type'] == 'redeem')
    	{
    		$Coupons = ML_Coupons::getInstance();
    		$this->view->order_coupon = $Coupons->getById($order['reason_id']);
    	}
    	
    	$this->view->order = $order;
    }
}