<?php
class Ml_Form_AccountSettings extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'name', array(
            'label'      => 'Name:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 50))
                )
        ));
        
        $email = $this->addElement('text', 'email', array(
            'label'      => 'E-mail address:',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 60)),
                array('validator' =>
                    'emailCheckUser'), //stringlenght there also
                array('validator' => 'EmailAddress')
                )
        ));
        
        $emailprivacy = $this->addElement('checkbox', 'private_email', array(
            'label'    => 'Hide e-mail from public search'));
        
        $website = $this->addElement('text', 'website', array(
            'label'      => 'Your homepage:',
            'required'   => false,
            'filters'    => array('StringTrim', 'UrlFilter'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 100)),
                array('validator' => 'Url')
                )
        ));
        
        $location = $this->addElement('text', 'location', array(
            'label'      => 'Location:',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 40)),
                )
        ));
        
        $about = $this->addElement('textarea', 'about', array(
            'label'      => 'About:',
            'description' =>
                '<small><a href="' .
                $this->getView()->staticUrl("/help/html") .
                '" class="new-window">HTML formatting</a></small>',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(0, 4096)),
                )
        ));
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Save!',
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
        
    }
}
