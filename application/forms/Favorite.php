<?php
class Ml_Form_Favorite extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->addElement('submit', 'favoriteSubmit', array(
            'label'    => 'Yes',
            'class'    => 'btn primary'
        ));
        
        $this->setAttrib('class', 'form-stacked');
    }
}
