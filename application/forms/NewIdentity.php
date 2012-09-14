<?php
class Ml_Form_NewIdentity extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_people;

    /**
     * @param null $options
     * @param string $securityCode
     * @param Zend_Config array $config
     */
    public function __construct($options = null, $securityCode = "", array $config, Ml_Model_People $people)
    {
        $this->_people = $people;

        if ($this->_config['ssl']) {
            $url = 'https://' . $this->_config['webhostssl'];
        } else {
            $url = '';
        }

        $url .= $this->getView()->url(
            array(
                "security_code" => $securityCode),
            "join_emailconfirm"
        );
        $this->setAction($url);

        return parent::__construct($options);
    }

    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");

        $this->setMethod('post');

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);

        $this->addElement('text', 'name', array(
            'label'      => 'Nome:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 50))
            ),
            'prepend' => '<i class="icon-user"></i>',
        ));

        $email = $this->addElement('text', 'email', array(
            'label'      => 'Endereço de email:',
            'required'   => true,
            "readonly" => true,
            'prepend' => '<i class="icon-envelope"></i>',
            'append' => '<i class="icon-ok"></i>',
        ));

        $this->addElement('text', 'newusername', array(
            'label'      => 'Seu nome de usuário:',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower'),
            'autocomplete' => 'off',
            'class'      => 'input-small',
            'prepend' => $config['webhost'] . '/',
        ));

        $this->getElement('newusername')->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 15)), true);
        $this->getElement('newusername')->addValidator(
            new Ml_Validate_UsernameNewUser(
                $this->_people,
                APPLICATION_PATH . "/configs/reserved-usernames.json"
            ),
            true
        );

        $this->addElement('password', 'password', array(
            'autocomplete' => 'off',
            'required'   => true,
            'label'      => 'Senha:',
        ));

        $this->getElement('password')->addValidator(new Ml_Validate_StringLength(array("min" => 6, "max" => 20)), true);
        $this->getElement('password')->addValidator(new Ml_Validate_HardPassword(), true);
        $this->getElement('password')->addValidator(new Ml_Validate_NewPasswordRepeat(), true);

        $this->addElement('password', 'password_confirm', array(
            'required'   => true,
            'label'      => 'Repita a senha:',
        ));
        
        // add the checkbox button for the ToS
        $this->addElement('checkbox', 'tos', array(
            'label'    => 'Eu concordo com os termos de serviço',
            'required' => true,
            'checkedValue'    => 'agree',
            'validators' => array(
            'Alnum', array('StringLength', false, array(5,5))),
        ));

        $this->addElement('submit', 'submit', array(
            'label'    => 'Criar conta',
            'class'    => 'btn',
        ));

        if ($config['ssl']) {
            $this->getElement("submit")->addValidator("Https");
            
            //By default the submit element doesn't display a error decorator
            $this->getElement("submit")->addDecorator("Errors");
        }
        
        $this->addElement('hash', 'no_csrf_foo',
            array('salt' => '*UFEWWFfj0ic4w98j', 'timeout' => 7200
        ));
        
        $this->getElement("tos")->setErrorMessages(array(
            'Você deve concordar com os Termos de Serviço para poder continuar.'
        ));

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