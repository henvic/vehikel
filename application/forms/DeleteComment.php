<?php
class Form_DeleteComment extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        $this->addElementPrefixPath('Ml_Validator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement(Ml_MagicCookies::formElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Delete it!',
        ));
    }
}