<?php
class Ml_Form_DeleteComment extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Delete it!',
            'class'    => 'btn danger',
        ));
        
        $this->setAttrib('class', 'form-stacked');
    }
}