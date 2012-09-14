<?php

class Ml_Form_Login extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_auth = null;

    protected $_config = null;

    protected $_people = null;

    protected $_credential = null;

    protected $_userId = null;

    /**
     * @param mixed $options
     * @param Zend_Auth $auth
     * @param Zend_Config array $config
     * @param Ml_Model_People $people
     * @param Ml_Model_Credential $credential
     */
    public function __construct(
        $options = null,
        Zend_Auth $auth,
        array $config,
        Ml_Model_People $people,
        Ml_Model_Credential $credential
    )
    {

        $this->_config = $config;

        $this->_auth = $auth;

        $this->_people = $people;

        $this->_credential = $credential;

        return parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod('post');

        if ($this->_config['ssl']) {
            $url = 'https://' . $this->_config['webhostssl'];
        } else {
            $url = '';
        }

        $url .= $this->getView()->url(array(), "login");
        $this->setAction($url);

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);

        $this->addElement('text', 'username', array(
            'label'      => 'Usuário ou email',
            'required'   => true,
            'autofocus' => 'autofocus',
            'filters'    => array('StringTrim', 'StringToLower')
        ));

        // it accepts up to 60 characters here because it might be the user email instead of the username
        $this->getElement('username')->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 60)), true);

        $usernameValidate = new Ml_Validate_Username($this->_people);

        $this->getElement('username')->addValidator($usernameValidate, true);

        $this->getElement("username")->setAttrib('required', 'required');

        $this->addElement('password', 'password', array(
            'label'      => 'Senha',
            'required'   => true
        ));

        $this->getElement('password')->addValidator(new Ml_Validate_StringLength(array("min" => 6, "max" => 20)), true);
        $this->getElement('password')->addValidator(new Ml_Validate_Password(
            $this->_auth,
            $this->_credential,
            $usernameValidate
            ),
            true
        );

        $this->getElement("password")->setAttrib('required', 'required');

        $this->addElement('checkbox', 'remember_me', array(
            'label'    => 'Relembrar-me'));

        if (Ml_Model_AntiAttack::ensureHuman()) {
            $this->addElement(Ml_Model_AntiAttack::captchaElement());
        }

        $login = $this->addElement('submit', 'login', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Entrar',
            'class'    => 'btn',
        ));


        if ($this->_config['ssl']) {
            $this->getElement("login")->addValidator("Https");

            //By default the submit element doesn't display a error decorator
            $this->getElement("login")->addDecorator("Errors");
        }

        $this->setAttrib('class', 'form-stacked');
    }
}

