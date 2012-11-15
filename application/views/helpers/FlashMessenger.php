<?php

class Ml_View_Helper_FlashMessenger extends Zend_View_Helper_Abstract
{
    public function flashMessenger()
    {
        $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');

        return $flashMessenger->getMessages();
    }
}
