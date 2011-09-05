<?php
class Form_removeTwitter extends Zend_Form
{    
    public function init()
    {
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'remove', array(
            'label'    => 'Remove Twitter!',
            'required' => true
        ));
        
        $this->addElement(ML_MagicCookies::formElement());
    }
}
