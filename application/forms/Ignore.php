<?php
class Ml_Form_Ignore extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/', 
        Zend_Form_Element::FILTER);
        
        if (Zend_Registry::getInstance()->isRegistered("is_ignored")) {
            $this->addElement('submit', 'removeignore', array(
                'label'    => 'Remove block!',
            ));
        } else {
            
            $this->addElement('submit', 'ignore', array(
                'label'    => 'Block!',
            ));
        }
        
        $this->addElement('hash', 'no_csrf_foo', 
        array('salt' => '%&UH*0CI7fr5v9', 'timeout' => 600));
    }
}