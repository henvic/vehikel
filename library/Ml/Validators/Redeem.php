<?php
//require_once 'Zend/Validate/Abstract.php';

class Ml_Validate_Redeem extends Zend_Validate_Abstract
{
    const INVALID_Redeem = 'invalidRedeem';
    const USED_Redeem = 'usedRedeem';
    const YUSED_Redeem = 'yusedRedeem';
    const NOTFOUND_Redeem = 'notfoundRedeem';
    const EMPTY_Redeem = 'emptyRedeem';

    protected $_messageTemplates = array(
        self::INVALID_Redeem   => "Invalid redeem code",
        self::USED_Redeem       => "This redeem code is no longer valid",
        self::YUSED_Redeem       =>
            "You already used this redeem code during this promotion",
        self::NOTFOUND_Redeem  => "Redeem code not found",
        self::EMPTY_Redeem     => "Empty redeem code"
    );

    public function isValid($value, $context = null)
    {
        $registry = Zend_Registry::getInstance();
        
        $signedUserInfo = $registry->get("signedUserInfo");
        
        $valueString = (string) $value;
        $this->_setValue($valueString);
        
        if (mb_strlen($value) > 16) {
            $this->_error(self::INVALID_Redeem);
            return false;
        }
        
        if (empty($value)) {
            $this->_error(self::EMPTY_Redeem);
            return false;
        }
        
        $coupons = Ml_Model_Coupons::getInstance();
        
        $token = $coupons->get($value);
        
        if (! $token) {
            $this->_error(self::NOTFOUND_Redeem);
            return false;
        }
        
        
        if (! $token['active']) {
            $this->_error(self::USED_Redeem);
            return false;
        }
        
        if (! $token['unique_use']) {
            $credits = Ml_Model_Credits::getInstance();
            
            $isItUsed = $credits->getCouponRedeemed($signedUserInfo['id'], $token['id']);
            
            if ($isItUsed) {
                $this->_error(self::YUSED_Redeem);
                return false;
            }
        }
        return true;
    }
}