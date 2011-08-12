<?php
/**
 * ML_Session_SaveHandler_Cache
 *
 * @license         public domain
 * @author          Henrique Vicente <henriquevicente@gmail.com>
 * @version         $Id$
 */

class ML_Session_SaveHandler_Cache implements Zend_Session_SaveHandler_Interface
{
	/**
     * Session lifetime
     *
     * @var int
     */
    protected $_lifetime = false;
    
    /**
     * Whether or not the lifetime of an existing session should be overridden
     *
     * @var boolean
     */
    protected $_overrideLifetime = false;
    
    protected $cache = '';
    
    /**
     * Constructor
     * 
     * @param Zend_Cache_Core $handler
     */
    public function __construct($handler)
    {
    	$this->_cache = $handler;
    }
    
	/**
     * Set session lifetime and optional whether or not the lifetime of an existing session should be overridden
     *
     * $lifetime === false resets lifetime to session.gc_maxlifetime
     *
     * @param int $lifetime
     * @param boolean $overrideLifetime (optional)
     * @return Zend_Session_SaveHandler_DbTable
     */
    public function setLifetime($lifetime, $overrideLifetime = null)
    {
        if ($lifetime < 0) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            // require_once 'Zend/Session/SaveHandler/Exception.php';
            throw new Zend_Session_SaveHandler_Exception();
        } else if (empty($lifetime)) {
            $this->_lifetime = (int) ini_get('session.gc_maxlifetime');
        } else {
            $this->_lifetime = (int) $lifetime;
        }

        if ($overrideLifetime != null) {
            $this->setOverrideLifetime($overrideLifetime);
        }

        return $this;
    }

    /**
     * Retrieve session lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->_lifetime;
    }

    /**
     * Set whether or not the lifetime of an existing session should be overridden
     *
     * @param boolean $overrideLifetime
     * @return Zend_Session_SaveHandler_DbTable
     */
    public function setOverrideLifetime($overrideLifetime)
    {
        $this->_overrideLifetime = (boolean) $overrideLifetime;

        return $this;
    }

    /**
     * Retrieve whether or not the lifetime of an existing session should be overridden
     *
     * @return boolean
     */
    public function getOverrideLifetime()
    {
        return $this->_overrideLifetime;
    }
    
    /**
     * Open Session
     *
     * @param string $save_path
     * @param string $name
     * @return boolean
     */
	public function open($save_path, $name)
    {
        return true;
    }
    
    /**
     * Close session
     *
     * @return boolean
     */
    public function close()
    {
    	return true;
    }
    
    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
    	return ($data = $this->_cache->load($id)) ? $data : '';
    }
    
    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
    	$this->_cache->save($data, $id, array(), $this->_getLifetime($id));
    	return true;
    }
    
    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
    	return ($this->_cache->remove($id)) ? true : false;
    }
    
    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
	public function gc($maxlifetime)
	{
		return true;
	}
	
	/**
     * Retrieve session lifetime considering Zend_Session_SaveHandler_DbTable::OVERRIDE_LIFETIME
     *
     * @param Zend_Db_Table_Row_Abstract $row
     * @return int
     */
    protected function _getLifetime($id)
    {
    	$lifetime = $this->_lifetime;
    	
        if (!$this->_overrideLifetime) {
            $lifetime = (int) $this->_cache->test($id);
        }

        return $lifetime;
    }
}