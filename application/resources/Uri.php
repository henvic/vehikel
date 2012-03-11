<?php

class Ml_Resource_Uri extends Zend_Application_Resource_ResourceAbstract
{
    protected $_https = false;
    
    protected $_webhost = '';
    
    protected $_webhostssl = '';
    
    protected $_originalUri = '';
    
    protected function _validHost() {
        if (! isset($_SERVER['HTTP_HOST'])) {
            return true;
        }
        
        if (! $this->_https) {
            if ($_SERVER['HTTP_HOST'] == $this->_webhost) {
                return true;
            }
        } else if ($_SERVER['HTTP_HOST'] == $this->_webhostssl) {
            return true;
        }
        
        return false;
    }
    
    protected function _redirect($newRequestUri, $https)
    {
        if ($https) {
            $newHost = "https://" . $this->_webhostssl;
        } else {
            $newHost = "http://" . $this->_webhost;
        }
        
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: " . $newHost . $newRequestUri);
        header("Cache-Control: max-age=3600, must-revalidate");
        
        //cache-control shall be set: see
        //http://developer.yahoo.net/blog/archives/2007/07/high_performanc_9.html
        
        exit;
    }
    
    public function init()
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") {
            $this->_https = true;
        }
        
        $this->_webhost = $config['webhost'];
        $this->_webhostssl = $config['webhostssl'];
        $this->_webroot = $config['webroot'];
        $this->_forceLowerCase = $config['web_addr']['force_lower_case'];
        $this->_originalUri = $_SERVER['REQUEST_URI'];
        
        $requestUri = $this->_originalUri;
        
        $isValidHost = $this->_validHost();
        
        if ($requestUri == $this->_webroot . "/") {
            if (! $isValidHost) {
                $this->_redirect($this->_webroot . "/", $this->_https);
            }
            return true;
        }
        
        if (isset($_SERVER['QUERY_STRING'])) {
            $queryStringLenght = mb_strlen($_SERVER['QUERY_STRING']);
            $requestUriLenght = mb_strlen($requestUri);
            
            if (empty($_SERVER['QUERY_STRING'])) {
                $offset = $requestUriLenght - $queryStringLenght;
            } else {
                $offset = $requestUriLenght - $queryStringLenght - 1;
            }
            
            $resource = mb_substr($requestUri, 0, $offset);
            
            $queryString = $_SERVER['QUERY_STRING'];
        } else {
            $resource = $requestUri;
            $queryString = '';
        }
        
        // is the case right?
        if ($this->_forceLowerCase) {
            $resourceToLower = mb_strtolower($resource);
            
            if ($resource != $resourceToLower) {
                $resource = $resourceToLower;
            }
        }
        
        // is it the last character in the resource part of the URI a slash?
        if (mb_substr($resource, - 1) == '/') {
            $resource = mb_substr($resource, 0, -1);
        }
        
        if (empty($queryString)) {
            if ($this->_originalUri != $resource) {
                $this->_redirect($resource, $this->_https);
            }
        } else {
            if ($this->_originalUri != $resource . "?" . $queryString) {
                $this->_redirect($resource . $queryString, $this->_https);
            }
        }
        
        if (! $isValidHost) {
            $this->_redirect($this->_originalUri, $this->_https);
        }
        
        return true;
    }
}
