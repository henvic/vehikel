<?php

class Ml_Form_SignUp extends Zend_Form
{
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
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
                'Privacy Policy</a> before proceeding</small>',
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
            
            $this->getElement("invitecode")->setAttrib('class', 'span3');
        }
        
        $this->addElement(Ml_Model_AntiAttack::captchaElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Sign up!',
            'class'    => 'btn primary',
        ));
        
        $this->setAttrib('class', 'form-stacked');
    }
}
