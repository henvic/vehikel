<?php
class Ml_Form_AddTwitter extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'connectToTwitter', array(
            'label'    => 'Connect to Twitter!',
            'required' => true,
            'class' => 'btn primary'
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->setAttrib('class', 'form-stacked');
    }
}
