<?php
class Myview extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        Zend_View_Helper_PaginationControl::setDefaultViewPartial('my_pagination_control.phtml');
        
        // Initialize view
        $view = new Zend_View;
        
        $view->setEncoding('UTF-8');
        
        // Add it to the ViewRenderer
        $viewRenderer =
        Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        
        $viewRenderer->setView($view);
        
        $view->addHelperPath(APPLICATION_PATH.'/views/helpers', 'Ml_View_Helper');
        
        $view->headTitle()->append($config['applicationname']);
        
        // Return it, so that it can be stored by the bootstrap
        return $view;
    }
}
