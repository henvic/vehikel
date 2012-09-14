<?php

class Ml_Model_Account
{
    protected $_people;

    protected $_gearmanClient;

    public function __construct(
        Ml_Model_People $people,
        GearmanClient $gearmanClient
    )
    {
        $this->_people = $people;

        $this->_gearmanClient = $gearmanClient;
    }

    /**
     * @param $uid
     * @return true in success, false otherwise
     */
    public function deactive($uid)
    {
        $result = $this->_people->update($uid, array("email" => null, "active" => false));

        if (! $result) {
            return false;
        }

        $this->_gearmanClient->doBackground("makeS3UserAssetsPrivate", json_encode(array("uid" => $uid)));

        return true;
    }
}
