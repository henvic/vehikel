<?php
class Ml_Form_Filepage extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'title', array(
            'label'      => 'Title:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 100))
                )
        ));
        
        $this->addElement('text', 'filename', array(
            'label'      => 'Filename:',
            'required'   => true,
            'description' =>
                "Don't forget the extension (i.e., .txt, .jpg, .pdf)",
            'filters'    => array('StringTrim', 'StringToLower', 'Filename'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 60)),
                array('validator' => 'filename')
                )
        ));
        
        
        $this->addElement('text', 'short', array(
            'label'      => 'Short description:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 120)),
                )
        ));
        
        $this->addElement('textarea', 'description', array(
            'label'      => 'Description:',
            'required'   => false,
            'description' =>
                '<small><a href="/help/html" class="new-window">'.
                'HTML formatting</a></small>',
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 4096)),
                )
        ));
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Save!',
            'class'    => 'btn primary'
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
        
        $this->setAttrib('class', 'form-stacked');
    }
}
