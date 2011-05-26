<?php

/** Zend_View_Helper_Abstract.php */
require_once 'Zend/View/Helper/Abstract.php';

/**
 * Helper for making easy links for static pages
 *
 */
class Zend_View_Helper_StaticUrl extends Zend_View_Helper_Abstract
{
    /**
     * Appends the baseurl to a path
     *
     * @access public
     * @param  string $path the path
     * @return string baseurl/$path
     */
    public function StaticUrl($path)
    {
        $frontController = Zend_Controller_Front::getInstance();
        //front controller
        $baseUrl = $frontController->getBaseUrl();
        
        return htmlentities($baseUrl . $path);
        
    }
}
