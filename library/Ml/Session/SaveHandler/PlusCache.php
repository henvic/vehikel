<?php
/**
 * Ml_Session_SaveHandler_PlusCache
 *
 * @license         public domain
 * @author          Henrique Vicente <henriquevicente@gmail.com>
 * @version         $Id$
 */

class Ml_Session_SaveHandler_PlusCache extends Ml_Session_SaveHandler_Cache
{
    /**
     * Session prefix
     *
     * @var string
     */
    protected $_lastActivityPrefix = "la_";
    
    /**
     * Constructor
     * 
     * @param Zend_Cache_Core $handler
     * @param string $sessionPrefix session prefix
     * @param string $activityPrefix last activity prefix
     */
    public function __construct($handler, $sessionPrefix = null, $lastActivityPrefix)
    {
        if ($lastActivityPrefix) {
            $this->setLastActivityPrefix($lastActivityPrefix);
        }
        
        return parent::__construct($handler, $sessionPrefix);
    }
    
    /**
     * Set the session prefix
     *
     * @param string $sessionPrefix
     * @return Zend_Session_SaveHandler_Cache
     */
    public function setLastActivityPrefix($lastActivityPrefix)
    {
        $this->_lastActivityPrefix = $lastActivityPrefix;

        return $this;
    }
    
    /**
     * Retrieve activity prefix
     *
     * @return string
     */
    public function getLastActivityPrefix()
    {
        return $this->_lastActivityPrefix;
    }
    
    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        return parent::read($id);
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
        $auth = Zend_Auth::getInstance();
        
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        //if user is identified, save additional information
        if ($auth->hasIdentity()) {
            $frontController = Zend_Controller_Front::getInstance();
            $couchDbConfig = $config['resources']['db']['couchdb']['dsn'];
            
            //if for some reason (which might be or not right) the script
            //was earlier terminated before the creation of the response object
            //don't try to retrieve the response object
            if (is_object($frontController->getResponse())) {
                $responseCode = $frontController->getResponse()->getHttpResponseCode();
            } else {
                $responseCode = '';
            }
            
            $requestInfo = array(
                "http_user_agent" => $_SERVER['HTTP_USER_AGENT'],
                "request_method" => $_SERVER['REQUEST_METHOD'],
                "remote_addr" => $_SERVER['REMOTE_ADDR'],
                "request_time" => (int)$_SERVER['REQUEST_TIME'],
                "request_method" => $_SERVER['REQUEST_METHOD'],
                "request_uri" => $_SERVER['REQUEST_URI'],
                "http_response_code" => $responseCode,
                "session" => $id,
                "uid" => $auth->getIdentity()
            );
            
            $this->_cache->save($requestInfo, 
             $this->_lastActivityPrefix . $id, array(), 
             $this->_getLifetime($id));
            
            $client = new couchClient($couchDbConfig, "web_access_log");
            
            $requestInfo['_id'] =
             $_SERVER['REQUEST_TIME'] . "-" . $auth->getIdentity() . "-" . mt_rand();
            
            try {
                $client->storeDoc((object) ($requestInfo));
            } catch (Exception $e) {
                trigger_error('Failure to store authenticated access log of user id ' .
                $auth->getIdentity(), E_USER_NOTICE);
            }
        }
        
        return parent::write($id, $data);
    }
    
    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {        
        return parent::destroy($id);
    }
}