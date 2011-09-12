<?php
class Ml_Model_Dom extends DOMDocument
{
    public function newTextAttribute($name, $text) {
        $rootAttr = $this->createAttribute($name);
        $rootText = $this->createTextNode($text);
        $rootAttr->appendChild($rootText);
        
        return $rootAttr;
    }
    
    public function newTextElement($name, $text) {
        $element = $this->createElement($name);
        $rootText = $this->createTextNode($text);
        $element->appendChild($rootText);
        
        return $element;
    }
    
    public function newTextAttributes($element, $arrayOfAttributes)
    {
        foreach ($arrayOfAttributes as $key => $value) {
            $element->appendChild($this->NewTextAttribute($key, $value));
        }
    }
    
    public function newTextElements($element, $arrayOfElements)
    {
        foreach ($arrayOfElements as $key => $value) {
            $element->appendChild($this->NewTextElement($key, $value));
        }
    }
    
    public function newMultipleTextElements($rootElement, $elementName, $arrayOfMultipleElements) {
        foreach ($arrayOfMultipleElements as $arrayOfElement) {
            $element = $rootElement->appendChild($this->newTextElement($elementName, null));
            $this->newTextElements($element, $arrayOfElement);
        }
    }
    
    public function newMultipleTextAttributes($rootElement, $elementName, $arrayOfMultipleAttributes) {
        foreach ($arrayOfMultipleAttributes as $arrayOfAttributes) {
            $element = $rootElement->appendChild($this->newTextElement($elementName, null));
            $this->newTextAttributes($element, $arrayOfAttributes);
        }
    }
}
