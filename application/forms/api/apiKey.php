<?php
class Form_APIkey extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'application_title', array(
            'label'      => 'Application Title:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 80))
                )
        ));
        
        $this->addElement('textarea', 'application_descr', array(
            'label'      => 'Description:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 1024)),
                )
        ));
        
        $this->addElement('textarea', 'application_notes', array(
            'label'      =>
                'Notes (anything our admins should want know about your app):',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 1024)),
                )
        ));
        
        $this->addElement('checkbox', 'application_commercial', array(
            'label'    => 'Commercial'));
        
        $this->addElement('text', 'application_uri', array(
            'label'      => 'Application URI:',
            'required'   => false,
            'filters'    => array('StringTrim', 'UrlFilter'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 255)),
                array('validator' => 'Url')
                )
        ));
        
        $this->addElement('text', 'callback_uri', array(
            'label'      => 'Application Callback:',
            'required'   => false,
            'filters'    => array('StringTrim', 'UrlFilter'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 255)),
                array('validator' => 'Url')
                )
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Submit!',
        ));
        
    }
}
