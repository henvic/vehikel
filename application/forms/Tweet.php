<?php
class Form_Tweet extends Zend_Form
{
    public function init()
    {
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $registry = Zend_Registry::getInstance();
        
        $tweet = $this->addElement('textarea', 'tweet', array(
            'label'      => 'Tell them...',
            'description' => '<b>or anything else...</b>',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 140)),
                )
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
        
        $this->addElement('submit', 'tweetSubmit', array(
            'label'    => 'Tweet!',
        ));
    }
}