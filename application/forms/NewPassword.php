<?php

class Ml_Form_NewPassword extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_config = null;

    protected $_credential = null;

    protected $_uid = null;

    /**
     * @param null $options
     * @param Zend_Config array $config
     * @param Ml_Model_Credential $credential
     * @param int $uid
     */
    public function __construct($options = null, array $config, Ml_Model_Credential $credential, $uid)
    {
        $this->_config = $config;

        $this->_credential = $credential;

        $this->_uid = $uid;

        $path = $this->getView()->url(array(), "password");

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

        $this->addElement('password', 'current_password', array(
            'autocomplete' => 'off',
            'required'   => true,
            'label'      => 'Senha atual',
        ));

        $this->getElement('current_password')->addValidator(new Ml_Validate_MatchPassword(
                $this->_credential,
                $this->_uid
            ),
            true
        );

        $this->addElement('password', 'password', array(
            'autocomplete' => 'off',
            'required'   => true,
            'label'      => 'Nova senha',
        ));

        $this->getElement('password')->addValidator(new Ml_Validate_StringLength(array("min" => 6, "max" => 20)), true);
        $this->getElement('password')->addValidator(new Ml_Validate_HardPassword(), true);
        $this->getElement('password')->addValidator(new Ml_Validate_NewPassword(
            $this->_credential,
            $this->_uid
        ), true);
        $this->getElement('password')->addValidator(new Ml_Validate_NewPasswordRepeat(), true);

        $this->addElement('password', 'password_confirm', array(
            'required'   => true,
            'label'      => 'Confirme a nova senha',
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
