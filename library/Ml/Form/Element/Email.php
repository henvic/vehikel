<?php
require_once (__DIR__ . "/../../View/Helper/FormEmail.php");

/**
 * Email form element
 */
class Ml_Form_Element_Email extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formEmail';
}
