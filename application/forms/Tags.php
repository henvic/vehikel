<?php
class Form_Tags extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement(ML_MagicCookies::formElement());
        
        $this->addElement('text', 'tags', array(
            'label'      => 'Add a tag',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 300))
                )
        ));
        
        $this->addElement('submit', 'tagsSubmit', array(
            'label'    => 'Add',
            'required' => false
        ));
        
        $this->getElement("tags")->setAttrib('class', 'smallfield');
    }
}
