<?php

class Ml_Form_Recover extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_people;

    /**
     * @param null $options
     * @param Ml_Model_People $people
     */
    public function __construct($options = null, Ml_Model_People $people)
    {
        $this->_people = $people;

        return parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod('post');
        $url = $this->getView()->url(array(), "recover");
        $this->setAction($url);

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'recover', array(
            'label'      => 'Usuário ou email',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower'),
            'autocomplete' => 'off',
        ));

        // it accepts up to 60 characters here because it might be the user email instead of the username
        $this->getElement('recover')->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 60)), true);
        $this->getElement('recover')->addValidator(new Ml_Validate_Username($this->_people), true);

        $this->addElement(Ml_Model_AntiAttack::captchaElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Enviar',
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