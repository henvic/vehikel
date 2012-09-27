<?php
require_once (__DIR__ . "/../../View/Helper/FormTel.php");

/**
 * Tel form element
 */
class Ml_Form_Element_Tel extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formTel';
}
