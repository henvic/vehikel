<?php

class Ml_View_Helper_AddJsParam extends Zend_View_Helper_Abstract
{
    public function addJsParam($key, $value)
    {
        $jsParams = $this->view->jsParams;

        $jsParams[$key] = $value;

        $this->view->jsParams = $jsParams;
    }
}
