<?php

class Ml_Model_Log extends Ml_Model_AccessSingleton
{
    protected static $_dbTableName = "log";
    
    /**
     * Singleton instance
     *
     */
    protected static $_instance = null;
    
    /**
     * 
     * Log the client/server details
     * in the moment of a given important action.
     * 
     * Kind of actions:
     * @todo log in, log out, remote log out, transactions
     * 
     * Kind of information logged:
     * sessions, IP address, browser, etc.
     * 
     * @param $reasonType
     * @param $reasonId
     * @return void
     */
    public function action($reasonType, $reasonId = null, $notes = null)
    {
        $data = array();
        
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $data["remote_addr"] = $_SERVER['REMOTE_ADDR'];
        }
        
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $data['cookies'] = $_SERVER['HTTP_COOKIE'];
        }
        if (self::$_registry->isRegistered("signedUserInfo")) {
            $signedUserInfo = self::$_registry->get("signedUserInfo");
            $data['uid'] = $signedUserInfo['id'];
        }
        if ($notes) {
            $data['notes'] = $notes;
        }
        
        $data['reason_type'] = $reasonType;
        $data['reason_id'] = $reasonId;
        $data['dump'] = var_export($_SERVER, true);
        
        $this->_dbTable->insert($data);
    }
}