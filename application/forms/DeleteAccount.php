<?php
class Ml_Form_DeleteAccount extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_credential;

    protected $_uid;

    /**
     * @param mixed $options
     * @param Ml_Model_Credential $credential
     * @param int $uid
     */
    public function __construct($options = null, Ml_Model_Credential $credential, $uid)
    {
        $this->_credential = $credential;

        $this->_uid = $uid;

        return parent::__construct($options);
    }

    public function init()
    {
        $this->setMethod('post');
        $this->setAction($this->getView()->url(array(), "account_delete"));

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);

        $this->addElement('password', 'password', array(
            'required'   => true,
            'label'      => 'Senha',
        ));

        $this->getElement('password')->addValidator(
            new Ml_Validate_StringLength(array("min" => 6, "max" => 20)),
            true
        );
        $this->getElement('password')->addValidator(new Ml_Validate_MatchPassword(
                $this->_credential,
                $this->_uid
            ),
            true
        );

        $this->addElement('hash', 'no_csrf_foo',
            array('salt' => 'K*#%JQk74#$*%Ĉ#%R*b', 'timeout' => 600));

        $this->addElement(Ml_Model_AntiAttack::captchaElement());

        $this->addElement('submit', 'submit', array(
            'label'    => 'Desativar conta',
            'class'    => 'btn btn-danger',
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