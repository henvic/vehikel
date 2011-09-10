<?php
// adapted from http://devzone.zend.com/article/14893-Caching-of-Zend-Framework-application-configuration-file
class Ml_Application extends Zend_Application
{
    /**
     *
     * @var Zend_Cache_Core|null
     */
    protected $_configCache;
    protected $_useCache;

    public function __construct($environment, $options, Zend_Cache_Core $configCache, $useCache)
    {
        $this->_configCache = $configCache;
        $this->_useCache = $useCache;
        parent::__construct($environment, $options);
    }
    
    protected function _loadConfig($file)
    {
        if ($this->_useCache == false) {
            return parent::_loadConfig($file);
        }
        
        $configMTime = filemtime($file);
        
        $cacheId = "application_conf_" . md5($file . $this->getEnvironment());
        $cacheLastMTime = $this->_configCache->test($cacheId);
        
        //Valid cache?
        if ($cacheLastMTime !== false &&
         $configMTime <= $cacheLastMTime) {
            return $this->_configCache->load($cacheId, true);
        }
        
        $config = parent::_loadConfig($file);
        $this->_configCache->save($config, $cacheId, array(), null);
        
        return $config;
    }
}