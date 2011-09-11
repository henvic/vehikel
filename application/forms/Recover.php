<?php
class Form_Recover extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
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
        
        $this->addElement(Ml_AntiAttack::captchaElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'E-mail me!',
        ));
    }
}