<?php
class ML_Dom extends DOMDocument
{
    public function newTextAttribute($name, $text)
    {
        $root_attr = $this->createAttribute($name);
        $root_text = $this->createTextNode($text);
        $root_attr->appendChild($root_text);
        
        return $root_attr;
    }
    
    public function newTextElement($name, $text)
    {
        $element = $this->createElement($name);
        $root_text = $this->createTextNode($text);
        $element->appendChild($root_text);
        
        return $element;
    }
    
    public function newTextAttributes($element, $array_of_attributes)
    {
        foreach($array_of_attributes as $key => $value)
        {
            $element->appendChild($this->NewTextAttribute($key, $value));
        }
    }
    
    public function newTextElements($element, $array_of_elements)
    {
        foreach($array_of_elements as $key => $value)
        {
            $element->appendChild($this->NewTextElement($key, $value));
        }
    }
    
    public function newMultipleTextElements($root_element, $element_name, $array_of_multiple_elements) {
        foreach($array_of_multiple_elements as $array_of_element)
        {
            $element = $root_element->appendChild($this->newTextElement($element_name, null));
            $this->newTextElements($element, $array_of_element);
        }
    }
    
    public function newMultipleTextAttributes($root_element, $element_name, $array_of_multiple_attributes) {
        foreach($array_of_multiple_attributes as $array_of_attributes)
        {
            $element = $root_element->appendChild($this->newTextElement($element_name, null));
            $this->newTextAttributes($element, $array_of_attributes);
        }
    }
}
