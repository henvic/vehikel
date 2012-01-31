<?php

/**
 * 
 * Creates the download link
 * 
 */
class Ml_View_Helper_DownloadLink extends Zend_View_Helper_Abstract
{
    /**
     * 
     * Creates the download link
     * @param shareInfo $shareInfo
     * @param userInfo $userInfo
     * @param bool $escape
     */
    public function downloadLink($shareInfo, $userInfo, $escape = true)
    {
        $registry = Zend_Registry::getInstance();
        
        $config = $registry->get("config");
        
        $s3config = $config['services']['S3'];
        
        $link = $s3config['sharesBucketAddress'] . $userInfo['alias'] . "/" .
        $shareInfo['id'] . "-" .
        $shareInfo['download_secret'] . "/" . $shareInfo['filename'];
        
        if ($escape) {
            return htmlentities($link, ENT_QUOTES, "UTF-8");
        }
        
        return $link;
        
    }
}
