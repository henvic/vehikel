<?php
// from http://devzone.zend.com/article/14893-Caching-of-Zend-Framework-application-configuration-file
class ML_Application extends Zend_Application
{
    /**
     *
     * @var Zend_Cache_Core|null
     */
    protected $_configCache;
    protected $_useCache;

    public function __construct($environment, $options = null, $useCache)
    {
        $this->_useCache = $useCache;
        parent::__construct($environment, $options);
    }

    protected function _cacheId($file)
    {
        return md5($file . '_' . $this->getEnvironment());
    }

    //Override
    protected function _loadConfig($file)
    {
        if ($this->_useCache == false || strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'php')
        {
            return parent::_loadConfig($file);
        }
        
        if(ctype_alnum(APPLICATION_ENV)) {
            $cached_config_file_path = CACHE_PATH . "/config." . APPLICATION_ENV . ".php";
        } else {
            throw new Exception("Application environment not valid.");
        }
        
        $config_filemtime = filemtime($file);
        
        if(file_exists($cached_config_file_path))
        {
            $cached_config = parent::_loadConfig($cached_config_file_path);
            
            if(isset($cached_config['config_cached_time']) && $cached_config['config_cached_time'] == $config_filemtime)
            {
                return $cached_config;
            }
        }
        
        $config = parent::_loadConfig($file);
        
        $config['config_cached_time'] = $config_filemtime;
        
        file_put_contents($cached_config_file_path, "<?php " . PHP_EOL . "return " . var_export($config, true) . ";");
        
        return $config;
    }
}