<?php
class Ml_Abuse extends Ml_Db
{
    protected $_name = "abuse";

    public function form()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            $form = new Ml_Form_Abuse(array(
                'action' => $router->assemble(array(), "report_abuse"),
                'method' => 'post',
            ));
        }
        
        return $form;
    }
}