<?php
class Ml_Form_DeleteShare extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Yes, delete it!',
            'class'    => 'btn danger',
        ));
        /*
        $this->addElement('submit', 'forget', array(
            'label'    => 'No!',
        ));
        */
        /*$this->addElement('hash', 'no_csrf_foo',
         array('salt' => '%(N*UN(*U00CI7c45v9', 'timeout' => 1200));*/
    }
}