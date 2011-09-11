<?php
class Form_Call extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->addElementPrefixPath('Ml_Validator', 'Ml/Validators/', 
        Zend_Form_Element::VALIDATE);
        $this->addElementPrefixPath('Ml_Filter', 'Ml/Filters/', 
        Zend_Form_Element::FILTER);
        
        $this->addElement('text', 'motive', array(
            'label'      => 'Motive:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 250))
                )
        ));
        
        /** @todo validator for knowing if the record_id really exists **/
        $this->addElement('text', 'record_id', array(
            'label'      => 'Recording ID:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 250))
                )
        ));
        
        $this->addElement('textarea', 'phone_numbers', array(
            'label'      => "Phone numbers",
            'required'   => true,
            'description' => 'One number per line',
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 4096)),
                )
        ));
        
        /** @todo validator for knowing if the group really exists **/
        /*$this->addElement('text', 'group', array(
            'label'      => 'Recipient Group:',
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' =>
                    'StringLength', 'options' => array(1, 250))
                )
        ));*/
        
        $hours = array();
        for ($x = 1; $x <= 12; $x++) {
            $hours[$x] = $x;
        }
        
        $this->addElement('select', 'hour', array(
        'label' => 'Hour:',
        'required' => true,
        'multiOptions' => $hours
        ));
        
        $minutes = array();
        for ($x = 0; $x <= 59; $x++) {
            $minutes[$x] = $x;
        }
        
        $this->addElement('select', 'minutes', array(
        'label' => 'Minutes:',
        'required' => true,
        'multiOptions' => $minutes
        ));
                
        $this->addElement('radio', 'period', array(
        'label' => 'Day Period:',
        'required' => true,
        'multiOptions' => array("am" => "AM", "pm" => "PM")
        ));        
        
        $days = array();
        for ($x = 1; $x <= 31; $x++) {
            $days[$x] = $x;
        }
        
        $this->addElement('select', 'day', array(
        'label' => 'Day:',
        'required' => true,
        'multiOptions' => $days
        ));
        
        $months = array(
            "1" => "January",
            "2" => "February",
            "3" => "March",
            "4" => "April",
            "5" => "May",
            "6" => "June",
            "7" => "July",
            "8" => "August",
            "9" => "September",
            "10" => "October",
            "11" => "November",
            "12" => "December"
        );
        
        $this->addElement('select', 'month', array(
        'label' => 'Month:',
        'required' => true,
        'multiOptions' => $months
        ));
        
        $years = array();
        for ($x = gmdate('Y'); $x <= gmdate('Y') + 5; $x++) {
            $years[$x] = $x;
        }
        
        $this->addElement('select', 'year', array(
        'label' => 'Year:',
        'required' => true,
        'multiOptions' => $years
        ));
        
        $futureDate = new Zend_Date();
        $futureDate->add("24:00:00", Zend_Date::TIMES);
        $futureDateArray = $futureDate->toArray();
        $am = ($futureDateArray['hour'] <= 12) ? true : false;
        
        if ($am) {
            $this->getElement("period")->setValue("am");
            $hourPart = $futureDateArray['hour'];
        } else {
            $this->getElement("period")->setValue("pm");
            $hourPart = $futureDateArray['hour']-12;
        }
        
        $this->getElement("hour")->setValue($hourPart);
        $this->getElement("minutes")->setValue($futureDateArray['minute']);
               
        $this->getElement("day")->setValue($futureDateArray['day']);
        $this->getElement("month")->setValue($futureDateArray['month']);
        
        
        if ($futureDate->get(Zend_Date::TIMEZONE_NAME)) {
            $timezone = array("gmt" => "Greenwich Mean Time (GMT)");
        } else {
            $timezone = array(
            $futureDate->get(Zend_Date::TIMEZONE) =>
            $futureDate->get(Zend_Date::TIMEZONE_NAME));
        }
         $this->addElement('select', 'timezone', array(
        'label' => 'Time Zone:',
        'required' => true,
        'multiOptions' => $timezone
        ));
        
        $this->addElement('submit', 'submit', array(
            'label'    => "Save to call",
            'required' => false
        ));
        
        $this->addElement('submit', 'cancel', array(
            'label'    => "Cancel call",
            'required' => false
        ));
        
        $this->addElement(Ml_MagicCookies::formElement());
    }
}
