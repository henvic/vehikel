<?php
require_once 'Zend/Validate/Abstract.php';

class MLValidator_Redeem extends Zend_Validate_Abstract
{
    const INVALID_Redeem = 'invalidRedeem';
    const USED_Redeem = 'usedRedeem';
    const YUSED_Redeem = 'yusedRedeem';
    const NOTFOUND_Redeem = 'notfoundRedeem';
    const EMPTY_Redeem = 'emptyRedeem';

    protected $_messageTemplates = array(
        self::INVALID_Redeem   => "Invalid redeem code",
        self::USED_Redeem       => "This redeem code is no longer valid",
        self::YUSED_Redeem       => "You already used this redeem code during this promotion",
        self::NOTFOUND_Redeem  => "Redeem code not found",
        self::EMPTY_Redeem     => "Empty redeem code"
    );

    public function isValid($value, $context = null)
    {
        $registry = Zend_Registry::getInstance();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $valueString = (string) $value;
        $this->_setValue($valueString);
        
        if(mb_strlen($value) > 16)
        {
            $this->_error(self::INVALID_Redeem);
            return false;
        }
        
        if(empty($value))
        {
            $this->_error(self::EMPTY_Redeem);
            return false;
        }
        
        $Coupons = new ML_Coupons();
        
        $select = $Coupons->select()
        ->where("hash = ?", mb_strtolower($value))
        ->order("active DESC")//to garantee showing the newest
        ;
        
        $row = $Coupons->fetchRow($select);
        
        if(!is_object($row))
        {
            $this->_error(self::NOTFOUND_Redeem);
            return false;
        }
        
        $token = $row->toArray();
        
        if(!$token['active']) {
            $this->_error(self::USED_Redeem);
            return false;
        }
        
        $coupon_data = $row->toArray();
        if(!$coupon_data['unique_use'])
        {
            $Credits = ML_Credits::getInstance();
            $is_it_used = $Credits->fetchRow($Credits->select()
                ->where("uid = ?", $signedUserInfo['id'])
                ->where("reason_type = ?", ML_Credits::COUPON_REDEEM)
                ->where("reason_id = ?", $coupon_data['id'])
            );
            
            if(is_object($is_it_used))
            {
                $this->_error(self::YUSED_Redeem);
                return false;
            }
        }
        return true;
    }
}