<?php

class LoginForm extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', 
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
        
        if (Ml_AntiAttack::ensureHuman()) {
            $this->addElement(Ml_AntiAttack::captchaElement());
        }
        
        $login = $this->addElement('submit', 'login', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Sign in'
        ));
        
        
        if ($config['ssl']) {
            $this->getElement("login")->addValidator("Https");
            
            //By default the submit element doesn't display a error decorator
            $this->getElement("login")->addDecorator("Errors");
        }
        
        $this->getElement("username")->setAttrib('class', 'smallfield');
        $this->getElement("password")->setAttrib('class', 'smallfield');
    }
}
 
