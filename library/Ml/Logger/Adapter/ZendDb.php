<?php

class Ml_Logger_Adapter_ZendDb implements Ml_Logger_Adapter_Interface
{
    protected $_dbTableName = "log";

    /**
     * @var Zend_Db_Table
     */
    protected $_dbTable;

    /**
     * @param $config
     */
    public function __construct($config) {
        $this->_dbTable = new Zend_Db_Table($this->_dbTableName, $config);
    }

    public function log(array $data)
    {
        if (isset($data['id']) || isset($data['timestamp'])) {
            throw new Exception('key is reserved for the the ZendDb logger adapter');
        }

        $this->_dbTable->insert(["data" => json_encode($data)]);
    }
}
