<?php
class Ml_Model_SignUp extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "new_users";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    public static function signUpForm()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_SignUp(array(
                'action' => $router->assemble(array(), "join"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public static function newIdentityForm($securityCode)
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_NewIdentity(array(
                'action' =>
                 $router->assemble(array("security_code" => $securityCode),
                 "join_emailconfirm"),
                'method' => 'post',
            ));
        }
        return $form;
    }
    
    public function newUser($name, $email, $inviteCode = false)
    {
        //securitycode is just a random hexnumber
        $securitycode = sha1($name . $email . mt_rand(- 54300, 105000) . microtime());
        
        $this->_dbAdapter->beginTransaction();
        
        $this->_dbAdapter->query('INSERT INTO ' . 
        $this->_dbAdapter->quoteTableAs($this->_dbTable->getTableName()) .
        ' (`email`, `name`, `timestamp`, `securitycode`) SELECT ?, ?, CURRENT_TIMESTAMP, ? FROM DUAL WHERE NOT EXISTS (select * from `people` where people.email = ?) ON DUPLICATE KEY UPDATE name=VALUES(name), timestamp=VALUES(timestamp), securitycode=VALUES(securitycode)',
        array($email, $name, $securitycode, $email));
        
        if (! empty($inviteCode) &&
         ! $this->_registry->isRegistered("inviteCompleteBefore") &&
         ! $this->_registry->isRegistered("inviteMultiple")) {
            $invites = Ml_Model_Invites::getInstance();
            $invites->updateStatus($inviteCode, Ml_Model_Invites::USED);
        }
        
        $this->_dbAdapter->commit();
        
        return array("name" => $name,
        "email" => $email, 
        "securitycode" => $securitycode);
    }
    
    public function delete($id)
    {
        $delete = $this->_dbTable->delete($this->_dbAdapter->quoteInto('id = ?', $id));
        
        if ($delete) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * Get sign up proccess by given email
     * @param string $email
     * @return array of sign up data
     */
    public function getByEmail($email)
    {
        $select = $this->_dbTable->select()->where("binary email = ?", $email);
        
        $data = $this->_dbAdapter->fetchRow($select);
        
        if (is_object($data)) {
            return false;
        }
        
        return $data;
    }
    
    /**
     * 
     * Get sign up proccess by hash
     * @param string $securityCode
     * @return array of sign up data
     */
    public function getByHash($securityCode)
    {
        $select = $this->_dbTable->select();
        
        $select
        ->where('securitycode = ?', $securityCode)
        ->where('timestamp >= ?', date("Y-m-d H:i:s", time()-(48*60*60)));
        
        $confirmationInfo = $this->_dbAdapter->fetchRow($select);
        
        return $confirmationInfo;
    }
    
	/**
     * 
     * Garbage collector
     * @param int $maxAge in seconds
     * @return number of deleted items
     */
    public function gc($maxAge)
    {
        $deleted = $this->_dbAdapter
         ->delete($this->_dbTable->getTableName(), $this->_dbAdapter
         ->quoteInto("timestamp < ?", date("Y-m-d H:i:s", time() - ($maxAge))));
         
         return $deleted;
    }
}
