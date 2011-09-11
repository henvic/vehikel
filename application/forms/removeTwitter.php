<?php
class Form_removeTwitter extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'remove', array(
            'label'    => 'Remove Twitter!',
            'required' => true
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
    }
}
