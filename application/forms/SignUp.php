<?php

class Ml_Form_SignUp extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_config = null;

    protected $_people;

    /**
     * @param null $options
     * @param Zend_Config array $config
     */
    public function __construct($options = null, array $config, Ml_Model_People $people)
    {
        $this->_people = $people;

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

        $url .= $this->getView()->url(array(), "join");
        $this->setAction($url);

        $this->setMethod('post');

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'name', array(
            'label'      => 'Nome',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 50))
                ),
            'prepend' => '<i class="icon-user"></i>',
        ));
        
        $this->addElement('text', 'email', array(
            'label'      => 'Endereço de email',
            'required'   => true,
            'description' =>
                '<small>Leia a <a href="/privacy" rel="external">'.
                'Política de privacidade</a> antes de continuar</small>',
            'filters'    => array('StringTrim', 'StringToLower'),
            'prepend' => '<i class="icon-envelope"></i>',
        ));

        $this->getElement('email')->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 60)), true);
        $this->getElement('email')->addValidator(new Zend_Validate_EmailAddress(), true);
        $this->getElement('email')->addValidator(
            new Ml_Validate_NewEmail(
                $this->_people
            ),
            true
        );
        
        $this->addElement(Ml_Model_AntiAttack::captchaElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Cadastrar',
            'class'    => 'btn btn-primary btn-large',
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
