<?php
class Form_Agenda extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        $this->addElementPrefixPath('Ml_Validator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'Agenda', array(
            'label'      => 'Group name:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 250))
                )
        ));
        
        $this->addElement('textarea', 'description', array(
            'label'      => 'Telephone numbers:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 4096)),
                )
        ));
        
        $this->addElement('submit', 'submit', array(
            'label'    => "Save",
            'required' => false
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
    }
}
