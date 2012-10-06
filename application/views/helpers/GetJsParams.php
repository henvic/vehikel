<?php
class Ml_View_Helper_GetJsParams extends Zend_View_Helper_Abstract
{
    public function getJsParams ()
    {
        return 'var AppParams = ' . json_encode($this->view->jsParams) . ';';
    }
}