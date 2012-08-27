<?php

class Ml_Model_Credential
{
    protected $_dbPrimaryRow = "uid";
    protected $_registry;

    protected $_name = 'credentials';

    protected $_dbTable;
    protected $_dbAdapter;

    public function __construct($config = array())
    {
        if ($this->_registry == null) {
            $this->_registry = Zend_Registry::getInstance();
        }

        $this->_dbTable = new Ml_Model_Db_Table($this->_name, $this->_dbPrimaryRow, $config);
        $this->_dbAdapter = $this->_dbTable->getAdapter();
    }

    private function getPreHash($uid, $password)
    {
        return hash("sha384", $uid . "-" . $password);
    }

    public function setHash($uid, $password)
    {
        $adapter = new \Phpass\Hash\Adapter\Pbkdf2();
        $phpassHash = new \Phpass\Hash($adapter);

        $partHash = $this->getPreHash($uid, $password);

        $hash = $phpassHash->hashPassword($partHash);

        return $hash;
    }

    public function getAuthAdapter($uid, $password)
    {
        $authAdapter = new Ml_Auth_Adapter($this->_registry->get('database'));

        $authAdapter->setTableName($this->_name)
            ->setIdentityColumn($this->_dbPrimaryRow)
            ->setIdentity($uid)
            ->setCredentialColumn("credential")
            ->setCredential($this->getPreHash($uid, $password));

        return $authAdapter;
    }

    public function setCredential($uid, $password)
    {
        $hash = $this->setHash($uid, $password);

        $stmt = $this->_dbAdapter
            ->query('INSERT INTO ' . $this->_dbAdapter->quoteTableAs($this->_name) .
                ' (`uid`, `credential`) VALUES (?, ?) ON DUPLICATE KEY UPDATE credential=VALUES(credential)',
            array($uid, $hash));

        $result = $stmt->rowCount();

        return (bool) $result;
    }

    public function getByUid($uid)
    {
        $select = $this->_dbTable->select()->where("uid = ?", $uid);
        $query = $this->_dbTable->fetchRow($select);

        if (! is_object($query)) {
            return false;
        }

        return $query->toArray();
    }

    /**
     *
     * Checks if there is a link to redirect after sign in ...
     * It has to be a internal link, so it won't accept
     * if it makes the user goes to somewhere else instead
     */
    public function checkLinkToRedirect()
    {
        $config = $this->_registry->get("config");

        $redirectAfterLogin = filter_input(INPUT_GET, "redirect_after_login", FILTER_UNSAFE_RAW);

        if ($redirectAfterLogin && $redirectAfterLogin != null) {
            $testLink = $redirectAfterLogin;
        } else if (isset($_SERVER['HTTP_REFERER'])) {
            $router = Zend_Controller_Front::getInstance()->getRouter();

            if ($router->getCurrentRouteName() == "login") {
                $referer = $_SERVER['HTTP_REFERER'];
                $partialLink = explode("?redirect_after_login=", $referer, 2);

                if (! isset($partialLink[1])) {
                    return false;
                } else {
                    $testLink = $partialLink[1];
                }
            }
        } else {
            return false;
        }

        // the redirection link must start with a '/' and
        // must not end up in the redirector again
        // or be in another host (avoids the use of @)

        $thisPage = explode("?", $_SERVER['REQUEST_URI'], 2);
        $thisPage = $thisPage[0];

        if (mb_substr($testLink, 0, 1) == '/' && !mb_strpos($testLink, "@") && $thisPage != $testLink) {
            // @todo improve HTTPS support
            $redirTo = "http://" . $config['webhost'] . $testLink;

            Zend_Uri::setConfig(array('allow_unwise' => true));

            if (Zend_Uri::check($redirTo)) {
                $testUri = Zend_Uri::factory($redirTo);

                $path = $testUri->getPath();

                Zend_Uri::setConfig(array('allow_unwise' => false));

                return $path;
            }
        }

        return false;
    }
}

