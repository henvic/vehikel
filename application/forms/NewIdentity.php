<?php
class Ml_Form_NewIdentity extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = null, $securityCode = "")
    {
        $url = $this->getView()->url(
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
            )
        ));

        $email = $this->addElement('text', 'email', array(
            'label'      => 'Endereço de email:',
            'required'   => true,
            "readonly" => true,
            'append' => '<i class="icon-ok"></i>',
        ));

        $this->addElement('text', 'newusername', array(
            'label'      => 'Seu nome de usuário:',
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
            'autocomplete' => 'off',
            'class'      => 'input-small',
            'prepend' => $config['webhost'] . '/',
        ));

        $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
//            'description' => "Six or more characters required; case-sensitive",
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
            'label'      => 'Senha:',
        ));
        
        $this->addElement('password', 'password_confirm', array(
            'filters'    => array('StringTrim'),
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