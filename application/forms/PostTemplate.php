<?php
class Ml_Form_PostTemplate extends Twitter_Bootstrap_Form_Horizontal
{
    public function __construct($options = null)
    {

        return parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod('post');

        $this->setAttrib("id", "post-template-form");

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);
        $this->addPrefixPath("Ml_Form", "Ml/Form");

        $this->addElement('textarea', 'post_template', array(
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(0, 30000)),
            ),
            'rows' => 5,
            'class' => 'hidden'
        ));

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
