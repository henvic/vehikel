<?php
class Ml_Form_Api_DeleteKey extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Yes, delete it!',
        ));
        /*
        $this->addElement('submit', 'forget', array(
            'label'    => 'No!',
        ));
        */
        $this->addElement('hash', 'no_csrf_foo',
            array('salt' => '%(N*UN(*U00CI7c45v9', 'timeout' => 1200));
    }
}