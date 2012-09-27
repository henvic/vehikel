<?php
require_once (__DIR__ . "/../../View/Helper/FormUrl.php");

/**
 * Url form element
 */
class Ml_Form_Element_Url extends Zend_Form_Element_Xhtml
{
    /**
     * Default form view helper to use for rendering
     * @var string
     */
    public $helper = 'formUrl';
}
