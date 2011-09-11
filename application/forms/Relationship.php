<?php
class Form_Relationship extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('MLValidator', 'ML/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'ML/Filters/', 
        Zend_Form_Element::FILTER);
        
        $contactRelation = $this->addElement('checkbox', 'contact_relation', 
        array('label' => 'Contact'));
         /*
        $this->addElement('checkbox', 'friend_relation', array(
            'label'    => 'Friend'));*/
        
        $this->addElement('submit', 'update_relation', array(
            'label'    => "Change relation",
            'required' => false
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
    }
}
