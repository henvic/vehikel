<?php
/**
 * Avatar
 * 
 * @author henrique
 *
 */
class Ml_View_Helper_avatarprofile extends Zend_View_Helper_Abstract
{
    public function avatarprofile ($person)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry->get("config");
        
        $picture = Ml_Picture::getInstance();
        
        $uid = $person['id'];
        $alias = $person['alias'];
        $name = $person['name'];
        $avatarInfo = $person['avatarInfo'];
        
        if (isset($avatarInfo))
            $picInfo = unserialize($avatarInfo);
        $sizeInfo = $picture->getSizeInfo("small");
        if (! $picInfo || empty($picInfo)) {
            return false;
        } else {
            $picUri = $config['services']['S3']['headshotsBucketAddress'] .
            $uid .
             '-' . $picInfo['secret'] . $sizeInfo['typeextension'] . '.jpg';
            $dim = (isset($picInfo['sizes'][$sizeInfo['urihelper']]['w']) &&
             isset($picInfo['sizes'][$sizeInfo['urihelper']]['h'])) ?
             ' width="' .
             $picInfo['sizes'][$sizeInfo['urihelper']]['w'] . '" height="' .
             $picInfo['sizes'][$sizeInfo['urihelper']]['h'] . '"' : '';
            $html = '<a href="' .
             $config['services']['S3']['headshotsBucketAddress'] . $uid . '-' .
             $picInfo['secret'] . '-b.jpg' . '" title="' .
             $this->view->escape($name) .
             '\'s picture" class="new-window"><img src="' . $picUri . '"' .
             $dim .
             ' alt="' . $this->view->escape($alias) . "\" class=\"uid-" .
             $uid . "\" /></a>\n";
        }
        return $html;
    }
}
