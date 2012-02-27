<?php

/**
 * 
 * Creates the short link
 * 
 */
class Ml_View_Helper_ShortLink extends Zend_View_Helper_Abstract
{
    /**
     * 
     * Creates the short link
     * @param big int $shareId
     * @param bool $escape
     */
    public function shortLink($shareId)
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $numbers = new Ml_Model_Numbers();
        
        $base58Id = $numbers->base58Encode($shareId);
        
        $link = $config['URLshortening']['addr'] . $base58Id;
        
        return $link;
        
    }
}
