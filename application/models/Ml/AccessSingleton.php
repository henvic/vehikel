<?php
/**
 * 
 * This abstract database access singleton class is useful for creating
 * models which use a common standard found on the system
 * @author Henrique Vicente de Oliveira Pinto <henriquevicente@gmail.com>
 *
 */
abstract class Ml_Model_AccessSingleton
{
    protected static $_registry;
    
    protected $_dbAdapter;
    
    protected $_dbTable;
        
    /**
     * Singleton pattern implementation makes "new" unavailable
     *
     * @return void
     */
    protected function __construct($config = array())
    {
        if (self::$_registry == null) {
            self::$_registry = Zend_Registry::getInstance();
        }
        
        if (isset(static::$_dbPrimaryRow)) {
            $dbPrimaryRow = static::$_dbPrimaryRow;
        } else {
            $dbPrimaryRow = 'id';
        }
        
        $this->_dbTable = new Ml_Model_Db_Table(static::$_dbTableName, $dbPrimaryRow, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();
    }
    
    /**
     * Singleton pattern implementation makes "clone" unavailable
     *
     * @return void
     */
    protected function __clone()
    {
    }
    
    public static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new static();
        }
        
        return static::$_instance;
    }
}