<?php
class LogoutForm extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'signout', array(
            'label'    => 'Sign out!',
        ));
        
        $this->addElement('submit', 'remote_signout', array(
            'label'    => 'Sign out all other sessions'
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
    }
}
