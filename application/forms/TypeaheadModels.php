<?php

class Ml_Form_TypeaheadModels extends Zend_Form
{
    public function init()
    {
        $this->setMethod('get');

        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);

        $this->addElement('text', 'make', array(
            'label'      => 'Make',
            'required'   => true,
            'filters'    => array('StringTrim', 'StringToLower')
        ));

        $this->getElement('make')
            ->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 30)), true);

        $this->addElement(
            'radio',
            'type',
            array(
                'required' => false,
                'label'    => 'Type',
                'filters' => array('StringToLower'),
                'multiOptions' => array(
                    "car" => "car",
                    "boat" => "boat",
                    "motorcycle" => "motorcycle"
                )
            )
        );

        $this->addElement('text', 'word', array(
            'label'      => 'Word',
            'filters'    => array('StringTrim', 'StringToLower')
        ));

        $this->getElement('word')->addValidator(new Ml_Validate_StringLength(array("min" => 0, "max" => 30)), true);
    }
}

