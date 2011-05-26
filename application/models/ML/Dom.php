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
}
