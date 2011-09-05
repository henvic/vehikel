<?php
class Form_DeleteTag extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
        
        $this->addElement(ML_MagicCookies::formElement());
        
        $this->addElement('submit', 'deleteTag', array(
            'label'    => 'Delete tag'
        ));
    }
}
