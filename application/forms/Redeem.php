<?php

class Ml_Form_Redeem extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'redeem', array(
                'label'      => 'Type your redeem code:',
                'required'   => true,
                'autoInsertNotEmptyValidator' => false,
                'validators' => array(
                    array('validator' => 'Redeem'),
                    )
            ));
        
        $this->addElement('submit', 'submit_redeem', array(
            'label'    => 'Redeem!',
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
    }
}
