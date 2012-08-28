<?php
class Ml_Form_Logout extends Twitter_Bootstrap_Form_Vertical
{
    public function init()
    {
        $url = $this->getView()->url(array(), "logout");
        $this->setAction($url);
        $this->setMethod('post');

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('submit', 'remote_signout', array(
            'label'    => 'Fechar as outras sessões',
            'class'    => 'btn bnt-large',
        ));
        
        $this->addElement('submit', 'signout', array(
            'label'    => 'Sair',
            'class'    => 'btn btn-danger',
        ));
        
        $this->addElement(Ml_Model_MagicCookies::formElement());
    }
}
