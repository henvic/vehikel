<?php
class Ml_View_Helper_randomimages extends Zend_View_Helper_Abstract
{
    /**
     * 
     * @param $images array consisting of image arrays
     * @param $defaultSize array (x width, y height) for default sizes
     * image arrays with
     * src, width, height. Optional: alt and absolutepath.
     * The latter tells that the path in src is absolute
     * so it doesn't convert it with staticversion
     * @return HTML img source
     * 
     * @todo weight
     * 
     */
    public function randomimages($images, $defaultSize = false)
    {
        $image = $images[mt_rand(0, sizeof($images)-1)];
        
        if (! isset($image['alt'])) {
            $image['alt'] = '';
        }
        
        if (! isset($image['absolutepath'])) {
            $image['src'] = $this->view->staticversion($image['src']);
        }
        
        $width = (isset($image['width'])) ? $image['width']: $defaultSize[0];
        $height = (isset($image['height'])) ? $image['height']: $defaultSize[1];
        
        return '<img src="' .
        $this->view->escape($image['src']) . '" alt="' .
        $this->view->escape($image['alt']) . '" width="' .
        $this->view->escape($width) . '" height="' .
        $this->view->escape($height) . '" />'."\n";
    }
}
