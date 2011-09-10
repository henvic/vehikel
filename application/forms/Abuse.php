<?php
class Form_Abuse extends Zend_Form
{
    public function init()
    {
        $auth = Zend_Auth::getInstance();
        
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'abuse_reference', array(
            'label'      => 'Link to the abuse:',
            'required'   => true,
            'filters'    => array('StringTrim', 'UrlFilter'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 512),
                array('validator' => 'Url')
                ))
        ));
        
        $this->addElement('textarea', 'abuse_description', array(
            'label'      => 'Explain (if necessary):',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 2048)),
                )
        ));
        
        if (! $auth->hasIdentity()) {
            $this->addElement(Ml_AntiAttack::captchaElement());
        }
        
        $this->addElement('submit', 'report_abuse', array(
            'label'    => "Let us know",
            'required' => false
        ));
    }
}
