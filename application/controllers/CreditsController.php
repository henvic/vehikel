<?php

class CreditsController extends Zend_Controller_Action
{
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        
        if (! $auth->hasIdentity()) {
            Zend_Controller_Front::getInstance()->registerPlugin(new ML_Plugins_LoginRedirect());
        }
    }
    
    public function creditsAction()
    {
        $registry = Zend_Registry::getInstance();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $request = $this->getRequest();
        
        $credits = ML_Credits::getInstance();
        
        $coupons = ML_Coupons::getInstance();
        
        $redeemForm = $coupons->_RedeemForm();
        
        if ($request->isPost()) {
            if ($redeemForm->isValid($request->getPost())) {
                $transactionId =
                $credits->couponTransaction($signedUserInfo['id'], $redeemForm->getValue("redeem"));
                
                if ($transactionId) {
                    $redeemForm->setDefault("redeem", "");
                    $this->view->transactionId = $transactionId;
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
        
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        $config = $registry->get("config");
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $request = $this->getRequest();
        
        $page = $request->getUserParam("page");
        
        $credits = ML_Credits::getInstance();
        $paginator = $credits->history($signedUserInfo['id'], $config['orders']['perPage'], $page);
        
        //Test if there is enough pages or not
        if ((! $paginator->count() && $page != 1) ||
         $paginator->getCurrentPageNumber() != $page) {
            $this->_redirect($router
                ->assemble(array(), "orders_1stpage"), array("exit"));
        }
         
        $this->view->paginator = $paginator;
    }
    
    public function orderAction()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $request = $this->getRequest();
        
        $orderPid = $request->getUserParam("order_pid");
        
        $credits = ML_Credits::getInstance();
        
        $order = $credits->getByPId($orderPid);
        
        if (! $order || $order['uid'] != $signedUserInfo['id']) {
            $registry->set("notfound", true);
            throw new Exception("Order doesn't exists.");
        }
        
        if ($order['reason_type'] == 'redeem') {
            $coupons = ML_Coupons::getInstance();
            $this->view->orderCoupon = $coupons->getById($order['reason_id']);
        }
        
        $this->view->order = $order;
    }
}