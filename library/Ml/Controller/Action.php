<?php

/**
 * Ml_Controller_Action helps in making the controllers simpler and more organized
 */
abstract class Ml_Controller_Action extends Zend_Controller_Action
{
    protected $_auth = null;
    protected $_registry = null;
    protected $_router = null;
    protected $_sc = null;
    protected $_config = null;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        $this->_auth = Zend_Auth::getInstance();
        $this->_registry = Zend_Registry::getInstance();
        $this->_router = $this->getFrontController()->getRouter();

        $sc = $this->_registry->get("sc");
        /** @var $sc \Symfony\Component\DependencyInjection\ContainerBuilder() */
        $this->_sc = $sc;

        $this->_config = $this->_registry->get("config");

        return parent::__construct($request, $response, $invokeArgs);
    }

    public function init()
    {
        $this->view->config = $this->_config;
    }
}