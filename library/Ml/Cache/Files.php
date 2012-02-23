<?php

/**
 * 
 * This class provides as a intelligent caching for config files
 * 
 * @author Henrique Vicente
 *
 */
class Ml_Cache_Files
{
    protected $_cache = '';
    
    public function __construct(Zend_Cache_Core $handler)
    {
        $this->_cache = $handler;
    }
    
    public function getConfigIni($file)
    {
        $configMTime = filemtime($file);
        
        $cacheId = "cachedIni_" . md5($file);
        
        $cacheLastMTime = $this->_cache->test($cacheId);
        
        if ($cacheLastMTime !== false && $configMTime <= $cacheLastMTime) {
            return $this->_cache->load($cacheId, true);
        }
        
        $config = new Zend_Config_Ini($file);
        
        $this->_cache->save($config, $cacheId, array(), null);
        
        return $config;
    }
}