<?php

class Form_NewPassword extends Zend_Form
{
    public function init()
    {
    	$registry = Zend_Registry::getInstance();
    	$config = $registry->get("config");
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
        
        if(Zend_Auth::getInstance()->hasIdentity()) {
	        $this->addElement('password', 'currentpassword', array(
    	        'filters'    => array('StringTrim'),
        	    'validators' => array(
	        		array('validator' => 'matchPassword') //there's stringlenght there
          	  ),
           	 'required'   => true,
           	 'label'      => 'Current Password:',
        	));
        }
        
        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
        	'description' => "Six or more characters required; case-sensitive",
            'validators' => array(
        		array('validator' => 'StringLength', 'options' => array(6, 20)),
        		array('validator' => 'Hardpassword'),
                array('validator' => 'newPassword'), //there's stringlenght there also
                array('validator' => 'newPasswordRepeat'), //there's stringlenght there also and in Password.php
            ),
            'required'   => true,
            'label'      => 'New Password:',
        ));
        
        $this->addElement('password', 'password_confirm', array(
            'filters'    => array('StringTrim'),
            'required'   => true,
            'label'      => 'Confirm Password:',
        ));
        
        if($registry->isRegistered("changeUserProperPassword"))
        {
            $this->addElement(ML_AntiAttack::captchaElement());
        }
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Change it!'
        ));
		
        if($config['ssl'])
        {
            $this->getElement("submit")->addValidator("Https");
            //Note: by default the submit element doesn't display a error decorator
            $this->getElement("submit")->addDecorator("Errors");
        }
        
	    if(Zend_Auth::getInstance()->hasIdentity()) {
        	$this->addElement(ML_MagicCookies::formElement());
        }
        
        //$registry['jsfiles'][$this->getView()->staticversion("/javascript/password.js")] = "prepend";
        
        if(Zend_Auth::getInstance()->hasIdentity()) {
        	$this->getElement("currentpassword")->setAttrib('class', 'smallfield');
        }
        
        $this->getElement("password")->setAttrib('class', 'smallfield');
	    $this->getElement("password_confirm")->setAttrib('class', 'smallfield');
    }
}
