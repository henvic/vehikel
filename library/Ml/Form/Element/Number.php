<?php
require_once (__DIR__ . "/../../View/Helper/FormNumber.php");

/**
 * Number form element
 */
class Ml_Form_Element_Number extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formNumber';
}
