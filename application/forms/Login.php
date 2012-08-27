<?php

class Ml_Form_Login extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_config = null;

    /**
     * @param null $options
     * @param Zend_Config array $config
     */
    public function __construct($options = null, array $config)
    {
        $this->_config = $config;

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
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('validator' => 'username'), //stringlenght there
            )
        ));

        $this->getElement("username")->setAttrib('required', 'required');

        $this->addElement('password', 'password', array(
            'label'      => 'Senha',
            'required'   => true,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(5, 20)),
                array('validator' => 'password'),
            ),
        ));

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

