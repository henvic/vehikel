<?php
class Form_Ignore extends Zend_Form
{    
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('MLFilter', 'ML/Filters/', Zend_Form_Element::FILTER);
        
        if(Zend_Registry::getInstance()->isRegistered("is_ignored"))
        {
            $this->addElement('submit', 'removeignore', array(
                'label'    => 'Remove block!',
            ));
        } else {
            
            $this->addElement('submit', 'ignore', array(
                'label'    => 'Block!',
            ));
        }
        
        $this->addElement('hash', 'no_csrf_foo', array('salt' => '%&UH*0CI7fr5v9', 'timeout' => 600));
    }
}