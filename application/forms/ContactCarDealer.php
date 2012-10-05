<?php
class Ml_Form_ContactCarDealer extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = null)
    {
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
        $this->addPrefixPath("Ml_Form", "Ml/Form");

        $this->addElement('text', 'name', array(
            'placeholder'      => 'Nome',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 50))
            ),
            'prepend' => '<i class="icon-user"></i>',
            'class' => 'input-medium'
        ));

        $this->addElement('text', 'email', array(
            'placeholder'      => 'Endereço de email',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower'),
            'prepend' => '<i class="icon-envelope"></i>',
            'class' => 'input-medium'
        ));

        $this->addElement("tel", 'telephone', array(
            'placeholder'      => 'Telefone',
            'required'   => true,
            'filters'    => array('StringTrim', 'TelephoneBr'),
            'prepend' => '☎',
            'class' => 'input-medium'
        ));

        $this->getElement("telephone")
            ->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 16)), true)
            ->addValidator(new Ml_Validate_TelephoneBr());

        $this->getElement('email')->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 60)), true);
        $this->getElement('email')->addValidator(new Zend_Validate_EmailAddress(), true);

        $this->addElement('textarea', 'contact_message', array(
            'placeholder'      => 'Proposta',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(0, 4096)),
            ),
            'class' => 'fix-sized-textarea',
            'rows' => 5
        ));

        $this->addElement('submit', 'submit', array(
            'label'    => 'Enviar proposta',
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
