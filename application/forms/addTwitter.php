<?php
class Form_addTwitter extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'connectToTwitter', array(
            'label'    => 'Connect to Twitter!',
            'required' => true
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
    }
}
