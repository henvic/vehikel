<?php
class Ml_Form_AddTwitter extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'connectToTwitter', array(
            'label'    => 'Connect to Twitter!',
            'required' => true
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
    }
}
