<?php
class Ml_Form_Address extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {
        $this->addElementPrefixPath('Ml_Validate', 'Ml/Validate/',
            Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filter/',
            Zend_Form_Element::FILTER);
        $this->addPrefixPath("Ml_Form", "Ml/Form");

        $this->addElement('text', 'street_address', array(
            'label'      => 'Logadouro',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 30)),
            )
        ));

        $this->addElement('text', 'neighborhood', array(
            'label'      => 'Bairro',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 30)),
            )
        ));

        $this->addElement('text', 'locality', array(
            'label'      => 'Cidade',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 30)),
            )
        ));

        $states = [
            "AC" => "Acre",
            "AL" => "Alagoas",
            "AM" => "Amazonas",
            "AP" => "Amapá",
            "BA" => "Bahia",
            "CE" => "Ceará",
            "DF" => "Distrito Federal",
            "ES" => "Espírito Santo",
            "GO" => "Goiás",
            "MA" => "Maranhão",
            "MT" => "Mato Grosso",
            "MS" => "Mato Grosso do Sul",
            "MG" => "Minas Gerais",
            "PA" => "Pará",
            "PB" => "Paraíba",
            "PR" => "Paraná",
            "PE" => "Pernambuco",
            "PI" => "Piauí",
            "RJ" => "Rio de Janeiro",
            "RN" => "Rio Grande do Norte",
            "RO" => "Rondônia",
            "RS" => "Rio Grande do Sul",
            "RR" => "Roraima",
            "SC" => "Santa Catarina",
            "SE" => "Sergipe",
            "SP" => "São Paulo",
            "TO" => "Tocantins"
        ];

        // the select element already have the Zend_Validate_InArray by default
        $this->addElement('select', 'region', array(
            'label'      => 'Estado',
            'value' => 'PE',
            'multiOptions' => $states,
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 2)),
            )
        ));

        $this->addElement('text', 'postal_code', array(
            'label'      => 'Código postal (CEP)',
            'required'   => true,
            'filters'    => array('StringTrim', 'Cep'),
            'class'      => "no-spin-button",
            'pattern'    => "[0-9]*"
        ));

        $this->getElement("postal_code")->addValidator(new Ml_Validate_Cep(), true);

        $this->addElement("tel", 'telephone_1', array(
            'label'      => '☎ Telefone 1',
            'required'   => true,
            'filters'    => array('StringTrim', 'TelephoneBr'),
        ));

        $this->getElement("telephone_1")
            ->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 16)), true)
            ->addValidator(new Ml_Validate_TelephoneBr());

        $this->addElement('text', 'telephone_1_name', array(
            'label'      => '☎ 1, falar com',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 40)),
            )
        ));

        $this->addElement('tel', 'telephone_2', array(
            'label'      => '☎ Telefone 2',
            'required'   => false,
            'filters'    => array('StringTrim', 'TelephoneBr'),
        ));

        $this->getElement("telephone_2")
            ->addValidator(new Ml_Validate_StringLength(array("min" => 1, "max" => 16)), true)
            ->addValidator(new Ml_Validate_TelephoneBr());

        $this->addElement('text', 'telephone_2_name', array(
            'label'      => '☎ 2, falar com',
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                'StringLength', 'options' => array(1, 40)),
            )
        ));

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
    }
}
