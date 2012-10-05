<?php
class Ml_Form_AccountSettings extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_emails = array();

    protected $_people;

    public function __construct($options = null, Ml_Model_People $people, $emails)
    {
        $this->_people = $people;

        $this->_emails = $emails;

        return parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod('post');
        $this->setAction($this->getView()->url(array(), "account"));

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);

        $this->addElement('text', 'name', array(
            'label'      => 'Nome',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 50))
            ),
        ));

        $this->addElement(
            'radio',
            'account_type',
            array(
                'required' => true,
                'label'    => 'Tipo de conta',
                'multiOptions' => array(
                    "private" => "Particular",
                    "retail" => "Loja"
                )
            )
        );

        $this->addElement('text', 'username', array(
            'label'      => 'Seu nome de usuário',
            'required'   => true,
            'class'      => 'input-small',
            'prepend' => '<i class="icon-user"></i>',
            'readonly' => true
        ));

        $this->addElement('text', 'email', array(
            'label'      => 'Endereço de email',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower'),
            'prepend' => '<i class="icon-envelope"></i>',
        ));

        $this->getElement('email')->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 60)), true);
        $this->getElement('email')->addValidator(new Zend_Validate_EmailAddress(), true);
        $this->getElement('email')->addValidator(
            new Ml_Validate_NewEmail(
                $this->_people,
                $this->_emails
            ),
            true
        );

        $this->addElement(
            'checkbox',
            'private_email',
            array(
                'label'    => 'Não mostrar email publicamente',
            )
        );

        $this->addElement('text', 'website', array(
            'label'      => 'Seu site',
            'required'   => false,
            'filters'    => array('StringTrim', 'UrlFilter'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 100)),
                array('validator' => 'Url')
                )
        ));

        $this->addElement('textarea', 'about', array(
            'label'      => 'Sobre',
            'description' =>
                '<small><a href="' .
                $this->getView()->staticUrl("/help/html") .
                '" rel="external">HTML formatting</a></small>',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(0, 4096)),
                ),
            'rows' => 5
        ));

        $this->getElement('about')->getDecorator('description')->setEscape(false);

        $this->addElement('submit', 'submit', array(
            'label'    => 'Salvar',
            'class' => 'btn btn-primary'
        ));

        $this->addElement(Ml_Model_MagicCookies::formElement());

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
