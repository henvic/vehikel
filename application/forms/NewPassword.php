<?php

class Ml_Form_NewPassword extends Zend_Form
{
    protected $_config = null;
    protected $_auth = null;

    /**
     * @param null $options
     * @param Zend_Config array $config
     */
    public function __construct($options = null, array $config, Zend_Auth $auth, $uid = null, $securityCode = null)
    {
        $this->_auth = $auth;
        $this->_config = $config;
        $this->_uid = null;


        if (! $uid) {
            $path = $this->getView()->url(array(), "password");
        } else {
            $this->_uid = $uid;
            $path = $this->getView()->url(array("confirm_uid" => $uid,
                    "security_code" => $securityCode),
                "password_unsigned");
        }

        if ($this->_config['ssl']) {
            $action = 'https://' . $this->_config['webhostssl'] . $this->_config['webroot'] . $path;
        } else {
            $action = $this->_config['webroot'] . $path;
        }

        $this->setAction($action);

        return parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod('post');

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        if ($this->_auth->hasIdentity() && ($this->_uid == null)) {
            $this->addElement('password', 'currentpassword', array(
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
            'required'   => true,
            'label'      => 'Confirm Password:',
            'autocomplete' => 'off',
            'class'      => 'span3',
        ));

        if (! $this->_auth->hasIdentity()) {
            $this->addElement(Ml_Model_AntiAttack::captchaElement());
        }

        $this->addElement('submit', 'submit', array(
            'label'    => 'Change it!',
            'class'    => 'btn primary',
        ));
        
        if ($this->_config['ssl']) {
            $this->getElement("submit")->addValidator("Https");
            
            //By default the submit element doesn't display a error decorator
            $this->getElement("submit")->addDecorator("Errors");
        }
        
        if ($this->_auth->hasIdentity()) {
            $this->addElement(Ml_Model_MagicCookies::formElement());
        }
        
        $this->setAttrib('class', 'form-stacked');
    }
}
