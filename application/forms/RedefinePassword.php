<?php

class Ml_Form_RedefinePassword extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_config = null;

    public function __construct($options = null, array $config, $uid = null, $securityCode = null)
    {
        $this->_config = $config;

        $path = $this->getView()->url(array("confirm_uid" => $uid,
                "security_code" => $securityCode),
            "password_recovering");

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
        $config = $this->_config;

        $this->setMethod('post');

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);

        $this->addElement('text', 'username', array(
            'label'      => 'Seu nome de usuário',
            'required'   => true,
            'class'      => 'input-small',
            'prepend' => '<i class="icon-user"></i>',
            'readonly' => true
        ));

        $this->addElement('password', 'password', array(
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
            'label'      => 'Senha',
        ));

        $this->addElement('password', 'password_confirm', array(
            'required'   => true,
            'label'      => 'Repita a senha',
        ));

        $this->addElement('submit', 'submit', array(
            'label'    => 'Modificar',
            'class'    => 'btn btn-primary',
        ));

        if ($this->_config['ssl']) {
            $this->getElement("submit")->addValidator("Https");

            //By default the submit element doesn't display a error decorator
            $this->getElement("submit")->addDecorator("Errors");
        }

        $this->addDisplayGroup(
            array('submit', 'reset'),
            'actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions')
            )
        );

        $this->setAttrib('class', 'form-stacked');
    }
}
