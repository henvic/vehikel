<?php

class Ml_Form_SignUp extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {
        $url = $this->getView()->url(array(), "join");
        $this->setAction($url);
        $this->setMethod('post');

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'name', array(
            'label'      => 'Nome:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 50))
                )
        ));
        
        $email = $this->addElement('text', 'email', array(
            'label'      => 'Endereço de email:',
            'required'   => true,
            'description' =>
                '<small>Leia a <a href="/privacy" rel="external">'.
                'Política de privacidade</a> antes de continuar</small>',
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(1, 60)),
                array('validator' => 'emailNewUser'),//stringlenght there also
                array('validator' => 'EmailAddress')
                )
        ));
        
        $this->addElement(Ml_Model_AntiAttack::captchaElement());
        
        $this->addElement('submit', 'submit', array(
            'label'    => 'Cadastrar',
            'class'    => 'btn btn-primary btn-large',
        ));

        $this->addDisplayGroup(
            array('submit', 'reset'),
            'actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions')
            )
        );

        $this->setAttrib('class', 'form-stacked');
    }
}
