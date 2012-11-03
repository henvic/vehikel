<?php
class Ml_Form_Hash extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);

        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setMethod('post');

        $this->addElement(Ml_Model_MagicCookies::formElement());
    }
}
