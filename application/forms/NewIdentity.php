<?php
class Form_NewIdentity extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        // beware it can not be ZERO
        $this->addElement('text', 'newusername', array(
            'label'      => 'Choose an username:',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 15)
                ),
                array('validator' =>
                    'usernameNewUser'
                ) //stringlenght there also
                ),
            'autocomplete' => 'off'
        ));
        
        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
            'description' => "Six or more characters required; case-sensitive",
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(6, 20)
                ),
                array('validator' =>
                    'newPasswordRepeat' //stringlenght there also
                ),
                array('validator' => 'Hardpassword')
            ),
            'autocomplete' => 'off',
            'required'   => true,
            'label'      => 'Password:',
        ));
        
        $this->addElement('password', 'password_confirm', array(
            'filters'    => array('StringTrim'),
            'required'   => true,
            'label'      => 'Confirm Password:',
        ));
        
        
        // add the checkbox button for the ToS
        $this->addElement('checkbox', 'tos', array(
            'label'    => 'I agree to the terms of services',
            'description' =>
                '<a href="/tos" class="new-window">terms of services</a> | ' .
                '<a href="/privacy" class="new-window">privacy policy</a>',
            'required' => true,
            'checkedValue'    => 'agree',
            'validators' => array(
            'Alnum', array('StringLength', false, array(5,5))),
        ));
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Create account!',
        ));
        
        if ($config['ssl']) {
            $this->getElement("submit")->addValidator("Https");
            
            //By default the submit element doesn't display a error decorator
            $this->getElement("submit")->addDecorator("Errors");
        }
        
        $this->addElement('hash', 'no_csrf_foo',
            array('salt' => '*UFEWWFfj0ic4w98j', 'timeout' => 6000
        ));
        
        $this->getElement("newusername")->setAttrib('class', 'smallfield');
        $this->getElement("password")->setAttrib('class', 'smallfield');
        $this->getElement("password_confirm")->setAttrib('class', 'smallfield');
        $this->getElement("tos")->setErrorMessages(array(
            'You can only sign up to Plifk if you accept the Terms of Service'
        ));
    }
}