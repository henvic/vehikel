<?php

class Ml_Form_Recover extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'recover', array(
            'label'      => 'Username or e-mail:',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 100)),
                array('validator' => 'accountRecover') //stringlenght there also
                ),
            'autocomplete' => 'off',
        ));
        
        $this->addElement(Ml_Model_AntiAttack::captchaElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'E-mail me!',
            'class'    => 'btn primary',
        ));
        
        $this->setAttrib('class', 'form-stacked');
    }
}