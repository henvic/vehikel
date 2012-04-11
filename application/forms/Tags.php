<?php
class Ml_Form_Tags extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->addElement('text', 'tags', array(
            'label'      => 'Add a tag',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 300))
                ),
            'placeholder' => 'Add a tag',
            'class'      => 'span2',
        ));
        
        $this->addElement('submit', 'tagsSubmit', array(
            'label'    => 'Add',
            'required' => false,
            'class'    => 'btn primary'
        ));
        
        $this->setAttrib('class', 'form-stacked');
    }
}
