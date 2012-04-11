<?php

class Ml_Form_Login extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'username', array(
            'label'      => 'Username or e-mail:',
            'required'   => true,
            'autofocus' => 'autofocus',
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('validator' => 'username'), //stringlenght there
                )
        ));
        
        $this->getElement("username")->setAttrib('required', 'required');
        
        $this->addElement('password', 'password', array(
            'label'      => 'Password:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
        array('validator' => 'StringLength', 'options' => array(5, 20)),
                array('validator' => 'password'),
            ),
        ));
        
        $this->getElement("password")->setAttrib('required', 'required');
        
        $this->addElement('checkbox', 'remember_me', array(
            'label'    => 'Remember me'));
        
        if (Ml_Model_AntiAttack::ensureHuman()) {
            $this->addElement(Ml_Model_AntiAttack::captchaElement());
        }
        
        $login = $this->addElement('submit', 'login', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Sign in',
            'class'    => 'btn primary',
        ));
        
        
        if ($config['ssl']) {
            $this->getElement("login")->addValidator("Https");
            
            //By default the submit element doesn't display a error decorator
            $this->getElement("login")->addDecorator("Errors");
        }
        
        $this->getElement("username")->setAttrib('class', 'span3');
        $this->getElement("password")->setAttrib('class', 'span3');
        
        $this->setAttrib('class', 'form-stacked');
    }
}
 
