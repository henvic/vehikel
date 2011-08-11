<?php
/**
 * === WARNING === WARNING === WARNING === WARNING === WARNING === WARNING ===
 * ============================= unsual behavior =============================
 * This filter changes whatever value for the hash is set and puts a valid one
 * Care MUST be taken as this makes the value passed to the validator always validate,
 * meaning the value should be taken there by another means such as by the use
 * of the PHP new function filter_input (or the depreceated(?) $_POST)
 * 
 * @author henvic
 *
 */
class MLFilter_MagicCookies implements Zend_Filter_Interface
{
    public function filter($value)
    {
    	return ML_MagicCookies::getLast();
    }
}
