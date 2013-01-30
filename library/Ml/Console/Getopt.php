<?php
/**
 * Extending Zend_Controller_Getopt to allow using unlisted arg values
 * @author VladSun
 * @see http://www.devnetwork.net/viewtopic.php?f=72&t=126334
 */
class Ml_Console_Getopt extends \Zend_Console_Getopt
{
    const CONFIG_PERMIT_UNKNOWN = 'permitUknown';

    public function __construct($rules, $argv = null, $getoptConfig = array())
    {
        parent::__construct($rules, $argv, $getoptConfig);
        $this->_options[self::CONFIG_PERMIT_UNKNOWN] = false;
    }

    public function resetParsing()
    {
        $this->_parsed = false;
    }

    protected function _parseSingleOption($flag, &$argv)
    {
        if ($this->_getoptConfig[self::CONFIG_IGNORECASE]) {
            $flag = strtolower($flag);
        }
        if (!isset($this->_ruleMap[$flag])) {

            /**
             * @hack Do not die on unrecognized options
             */
            if ($this->_getoptConfig[self::CONFIG_PERMIT_UNKNOWN])
                return null;

            require_once 'Zend/Console/Getopt/Exception.php';
            throw new \Zend_Console_Getopt_Exception(
                "Option \"$flag\" is not recognized.",
                $this->getUsageMessage());
        }
        $realFlag = $this->_ruleMap[$flag];
        switch ($this->_rules[$realFlag]['param']) {
            case 'required':
                if (count($argv) > 0) {
                    $param = array_shift($argv);
                    $this->_checkParameterType($realFlag, $param);
                } else {
                    require_once 'Zend/Console/Getopt/Exception.php';
                    throw new \Zend_Console_Getopt_Exception(
                        "Option \"$flag\" requires a parameter.",
                        $this->getUsageMessage());
                }
                break;
            case 'optional':
                if (count($argv) > 0 && substr($argv[0], 0, 1) != '-') {
                    $param = array_shift($argv);
                    $this->_checkParameterType($realFlag, $param);
                } else {
                    $param = true;
                }
                break;
            default:
                $param = true;
        }
        $this->_options[$realFlag] = $param;
    }
}
