<?php

class Ml_View_Helper_FormEmail extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'email' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function formEmail($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable

        // build the element
        $disabled = '';
        if ($disable) {
            // disabled
            $disabled = ' disabled="disabled"';
        }

        $xhtml = '<input type="email"'
            . ' name="' . $this->view->escape($name) . '"'
            . ' id="' . $this->view->escape($id) . '"'
            . ' value="' . $this->view->escape($value) . '"'
            . $disabled
            . $this->_htmlAttribs($attribs)
            . $this->getClosingBracket();

        return $xhtml;
    }
}
