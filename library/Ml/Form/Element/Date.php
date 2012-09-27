<?php
require_once (__DIR__ . "/../../View/Helper/FormDate.php");

/**
 * Date form element
 */
class Ml_Form_Element_Date extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formDate';
}
