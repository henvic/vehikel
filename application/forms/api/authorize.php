<?php
class Form_authorize extends Zend_Form
{	
    public function init()
    {
    	$this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
    	
        $this->setMethod('post');
		
        $this->addElement(ML_MagicCookies::formElement());
		
        $this->addElement('submit', 'allow', array(
            'label'    => 'Yes!',
        ));
        
        $this->addElement('submit', 'deny', array(
            'label'    => 'No!',
        ));
        
    }
}
