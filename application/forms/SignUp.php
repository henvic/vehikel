<?php

class Form_SignUp extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'name', array(
            'label'      => 'Name:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 50))
                )
        ));
        
        $email = $this->addElement('text', 'email', array(
            'label'      => 'E-mail address:',
            'required'   => true,
            'description' =>
                '<small>Read the <a href="/privacy" class="new-window">'.
                'Privacy Policy</a></small> before proceeding',
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 60)),
                array('validator' => 'emailNewUser'),//stringlenght there also
                array('validator' => 'EmailAddress')
                )
        ));
        
        if ($config['signup']['inviteonly']) {
            $this->addElement('text', 'invitecode', array(
                'label'      => 'Invite code:',
                'required'   => true,
                'autoInsertNotEmptyValidator' => false,
                'validators' => array(
                    array('validator' => 'Invite'),
                    )
            ));
            
            $this->getElement("invitecode")->setAttrib('class', 'smallfield');
        }
        
        $this->addElement(Ml_AntiAttack::captchaElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Sign up!',
        ));
        
    }
}
