<?php
class Ml_Form_Logout extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'remote_signout', array(
            'label'    => 'Sign out all other sessions',
            'class'    => 'btn',
        ));
        
        $this->addElement('submit', 'signout', array(
            'label'    => 'Sign out!',
            'class'    => 'btn danger',
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->setAttrib('class', 'form-stacked');
    }
}
