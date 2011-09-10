<?php

class Form_Redeem extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', 
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
        
        $this->addElement(Ml_MagicCookies::formElement());
        
    }
}
