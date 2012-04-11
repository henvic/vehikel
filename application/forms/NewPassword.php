<?php

class Ml_Form_NewPassword extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $auth = Zend_Auth::getInstance();
        
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        if ($auth->hasIdentity()) {
            $this->addElement('password', 'currentpassword', array(
                'filters'    => array('StringTrim'),
                'validators' => array(
                    array('validator' => 'matchPassword') //stringlenght there
                ),
             'autocomplete' => 'off',
                'required'   => true,
                'label'      => 'Current Password:',
                'class'      => 'span3',
            ));
        }
        
        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
            'description' => "Six or more characters required; case-sensitive",
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(6, 20)),
                array('validator' => 'Hardpassword'),
                array('validator' => 'newPassword'), //stringlenght there also
                array('validator' =>
                    'newPasswordRepeat'
                ), //stringlenght there also and in Password.php
            ),
            'autocomplete' => 'off',
            'required'   => true,
            'label'      => 'New Password:',
            'class'      => 'span3',
        ));
        
        $this->addElement('password', 'password_confirm', array(
            'filters'    => array('StringTrim'),
            'required'   => true,
            'label'      => 'Confirm Password:',
            'autocomplete' => 'off',
            'class'      => 'span3',
        ));
        
        if ($registry->isRegistered("changeUserProperPassword")) {
            $this->addElement(Ml_Model_AntiAttack::captchaElement());
        }
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Change it!',
            'class'    => 'btn primary',
        ));
        
        if ($config['ssl']) {
            $this->getElement("submit")->addValidator("Https");
            
            //By default the submit element doesn't display a error decorator
            $this->getElement("submit")->addDecorator("Errors");
        }
        
        if ($auth->hasIdentity()) {
            $this->addElement(Ml_Model_MagicCookies::formElement());
        }
        
        $this->setAttrib('class', 'form-stacked');
    }
}
