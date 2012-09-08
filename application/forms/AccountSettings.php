<?php
class Ml_Form_AccountSettings extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {
        $this->setMethod('post');
        $this->setAction($this->getView()->url(array(), "account"));

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);

        $this->addElement('text', 'name', array(
            'label'      => 'Nome',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 50))
            ),
        ));

        $this->addElement('text', 'username', array(
            'label'      => 'Seu nome de usuário',
            'required'   => true,
            'class'      => 'input-small',
            'prepend' => '<i class="icon-user"></i>',
            'readonly' => true
        ));

        $email = $this->addElement('text', 'email', array(
            'label'      => 'Endereço de email',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 60)),
                array('validator' =>
                    'emailCheckUser'), //stringlenght there also
                array('validator' => 'EmailAddress')
                ),
            'prepend' => '<i class="icon-envelope"></i>',
        ));

        $emailprivacy = $this->addElement(
            'checkbox',
            'private_email',
            array(
                'label'    => 'Não mostrar email publicamente',
            )
        );

        $website = $this->addElement('text', 'website', array(
            'label'      => 'Seu site',
            'required'   => false,
            'filters'    => array('StringTrim', 'UrlFilter'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 100)),
                array('validator' => 'Url')
                )
        ));

        $location = $this->addElement('text', 'location', array(
            'label'      => 'Localidade',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 40)),
                )
        ));

        $about = $this->addElement('textarea', 'about', array(
            'label'      => 'Sobre',
            'description' =>
                '<small><a href="' .
                $this->getView()->staticUrl("/help/html") .
                '" rel="external">HTML formatting</a></small>',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(0, 4096)),
                ),
            'rows' => 5
        ));

        $this->getElement('about')->getDecorator('description')->setEscape(false);

        $this->addElement('submit', 'submit', array(
            'label'    => 'Salvar',
            'class' => 'btn btn-primary'
        ));

        $this->addElement(Ml_Model_MagicCookies::formElement());

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
