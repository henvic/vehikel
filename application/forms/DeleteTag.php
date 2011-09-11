<?php
class Form_DeleteTag extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement(Ml_MagicCookies::formElement());
        
        $this->addElement('submit', 'deleteTag', array(
            'label'    => 'Delete tag'
        ));
    }
}
