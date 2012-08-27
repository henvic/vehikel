<?php

class Ml_Logger_Adapter_Mongo implements Ml_Logger_Adapter_Interface
{
    protected $_collection = null;

    public function __construct($collection, $db, Mongo $mongo)
    {
        $this->_collection = $mongo->selectDB($db)->selectCollection($collection);
    }

    public function log(array $data)
    {
        if (isset($data['_id']) || isset($data['timestamp'])) {
            throw new Exception('key is reserved for the the Mongo logger adapter');
        }

        $data['timestamp'] = new MongoDate();

        $this->_collection->insert($data);
    }
}
