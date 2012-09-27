<?php
require_once (__DIR__ . "/../../View/Helper/FormSearch.php");

/**
 * Search form element
 */
class Ml_Form_Element_Search extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formSearch';
}
