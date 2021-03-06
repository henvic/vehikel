<?php

class Ml_View_Helper_AddressMapLink extends Zend_View_Helper_Abstract
{
    public function addressMapLink($address, $onlyQuery = false)
    {
        if ($onlyQuery) {
            return $this->getQuery($address);
        }

        /** @var $userAgent \Zend_View_Helper_UserAgent() */
        $userAgent = $this->view->userAgent()->getUserAgent();

        //try to identify if the request comes from a iOS device and if so
        //uses the native map
        if (strstr($userAgent, "like Mac OS")) {
            $mapProvider = "http://maps.apple.com/maps?q=";
        } else {
            $mapProvider = "http://maps.google.com/maps?q=";
        }

        $mapLink = $mapProvider . urlencode($this->getQuery($address)) . "&amp;t=m";

        return $mapLink;
    }

    public function getQuery($address)
    {
        return $address["street_address"]
            . " " . $address["locality"]
            . " - " . $address["region"]
            . ", " . $address["postal_code"];
    }
}
