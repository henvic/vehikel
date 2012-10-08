<?php

class Ml_Model_ContactSeller
{
    protected $_dbTableName = "contact_seller";
    protected $_dbAdapter;
    protected $_dbTable;

    public function __construct($config)
    {
        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();
    }

    public function saveMessage($recipientUserInfo, $senderName, $senderEmail, $senderPhone, $message)
    {
        $messageData = [
            "sender_name" => $senderName,
            "sender_email" => $senderEmail,
            "sender_phone" => $senderPhone,
            "message" => $message
        ];

        $data = [
            "uid" => $recipientUserInfo["id"],
            "data" => json_encode($messageData)
        ];

        $this->_dbTable->insert($data);

        return $this->_dbAdapter->lastInsertId();
    }
}
