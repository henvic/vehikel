<?php

class Ml_Logger
{
    protected $_adapter = null;
    protected $_auth = null;

    public function __construct(Ml_Logger_Adapter_Interface $adapter, Zend_Auth $auth = null) {
        $this->_adapter = $adapter;
        $this->_auth = $auth;
    }

    /**
     *
     * Log important actions
     * @param array $data with at least action key
     * @param bool logPost whether to log POST data
     */
    public function log(array $data, $logPost = false)
    {
        if (! isset($data['action'])) {
            throw new Exception("Action key doesn't exists in the data array.");
        }

        if (isset($data['server']) || isset($data['raw_post']) ||
            isset($data['uid'])) {
            throw new Exception("Trying to use reserved log internal key.");
        }

        $data['server'] = filter_input_array(INPUT_SERVER, FILTER_UNSAFE_RAW);

        // add missing data of INPUT_SERVER
        $data['server']['REQUEST_TIME_FLOAT'] = $_SERVER['REQUEST_TIME_FLOAT'];
        $data['server']['REQUEST_TIME'] = $_SERVER['REQUEST_TIME'];

        if ($logPost) {
            $data['raw_post'] = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);
        }

        if (is_object(($this->_auth))) {
            $data['uid'] = $this->_auth->getIdentity();
        }

        $this->_adapter->log($data);
    }
}
