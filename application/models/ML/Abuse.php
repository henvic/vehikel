<?php
class ML_Abuse extends ML_Db
{
    protected $_name = "abuse";

    public function form()
    {
        static $form = '';
        
        if (! is_object($form)) {
            $router = Zend_Controller_Front::getInstance()->getRouter();
            
            require APPLICATION_PATH . '/forms/Abuse.php';
             
            $form = new Form_Abuse(array(
                'action' => $router->assemble(array(), "report_abuse"),
                'method' => 'post',
            ));
        }
        
        return $form;
    }
}