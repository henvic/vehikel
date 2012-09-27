<?php
require_once (__DIR__ . "/../../View/Helper/FormRange.php");

/**
 * Range form element
 */
class Ml_Form_Element_Range extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formRange';
}
