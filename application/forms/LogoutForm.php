<?php
class LogoutForm extends Zend_Form
{	
    public function init()
    {
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'signout', array(
            'label'    => 'Sign out!',
        ));
        
        $this->addElement('submit', 'remote_signout', array(
            'label'    => 'Sign out all other sessions'
        ));
        
        $this->addElement(ML_MagicCookies::formElement());
    }
}
