<?php

class Ml_Model_Coupons extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "coupons";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public static function redeemForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Redeem(array(
                'action' => $router->assemble(array(), "do_order"),
                'method' => 'post',
            ));
        }
        return $form;
    }

    public function getById($id)
    {
        return $this->_dbTable->getById($id);
    }
    
    /**
     * 
     * Get coupon by hash
     * @param $hash coupon code
     * @param bool $onlyActive only get if the coupon is active
     */
    public function get($hash, $onlyActive = false)
    {
        $select = $this->_dbTable->select();
        $select
        ->where("hash = ?", $hash);
        
        if ($onlyActive) {
            $select->where("active = ?", true);
        }
        
        $select->order("active DESC");//to guarantee showing the newest as it's not a unique field
        
        return $this->_dbAdapter->fetchRow($select);
    }
    
    /**
     * 
     * Enable or disable coupon
     * @param $hash
     * @param bool $state true for active, false otherwise
     */
    public function state($hash, $state)
    {
        return $this->_dbTable->update(array("active" => $state), 
            $this->_dbAdapter
                ->quoteInto("hash = ?", $state));
    }
}