<?php

class Ml_Model_Picture
{
    protected $_imageQuality = 80;

    protected $_s3config = null;

    protected $_s3 = null;

    /**
     * @param Zend_Config array $config
     */
    public function __construct(array $config)
    {
        $this->_s3config = $config['services']['S3'];
        $this->_s3 = new Zend_Service_Amazon_S3($this->_s3config['key'], $this->_s3config['secret']);
    }

    protected $_sizes = array(
        "square.jpg" => 75,
        "square@2x.jpg" => 150,
        "thumbnail.jpg" => 100,
        "thumbnail@2x.jpg" => 200,
        "small.jpg" => 320,
        "small@2x.jpg" => 640,
        "medium.jpg" => 800,
        "medium@2x.jpg" => 1600,
        "large.jpg" => 1280,
        "large@2x.jpg" => 2096
    );

    /**
     * @param $source string path to a image
     * @param $id string image id that should be used
     * @return array with picture data in success, -1 if the image could not be loaded, false otherwise
     */
    public function create($source, $id)
    {
        try {
            $originalIm = new Imagick($source);
        } catch (Exception $e) {
            return -1;
        }

        $originalDimension = $originalIm->getimagegeometry();

        if (! $originalDimension) {
            return false;
        }

        $originalIm->setimagecompressionquality($this->_imageQuality);

        $originalIm->unsharpMaskImage(0 , 0.5 , 1 , 0.05);

        $originalIm->setImageFormat('jpeg');

        $files = array();

        foreach ($this->_sizes as $partialPath => $maxDim) {
            $im = $originalIm->getImage();

            $tmpFile = tempnam(sys_get_temp_dir(), 'IMAGE-' . md5(openssl_random_pseudo_bytes(12)));

            if ($partialPath == "square.jpg" || $partialPath == "square@2x.jpg") {
                if ($originalDimension['height'] < $originalDimension['width']) {
                    $size = $originalDimension['height'];
                } else {
                    $size = $originalDimension['width'];
                }
                $im->cropThumbnailImage($maxDim, $maxDim);
            } else if ($originalDimension['width'] > $maxDim && $originalDimension['height'] > $maxDim) {
                if ($originalDimension['width'] > $originalDimension['height']) {
                    $im->resizeimage($maxDim, 0, Imagick::FILTER_LANCZOS, 1);
                } else {
                    $im->resizeimage(0, $maxDim, Imagick::FILTER_LANCZOS, 1);
                }
            }

            $im->writeimage($tmpFile);

            $imGeometry = $im->getimagegeometry();

            $files[$partialPath] = array(
                "path" => $tmpFile,
                "width" => $imGeometry['width'],
                "height" => $imGeometry['height']
            );
        }

        $secret = md5(openssl_random_pseudo_bytes(20) . mt_rand());

        foreach ($files as $partialPath => &$info) {
            $this->_s3->putFile(
                $info["path"],
                $this->getImagePath($id, $secret, $partialPath),
                array(
                    Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                    "Content-Type" => "image/jpeg",
                    "Cache-Control" => "max-age=37580000, public",
                    "Expires" => "Thu, 10 May 2029 00:00:00 GMT"
                )
            );

            unlink($info["path"]);
            unset($info["path"]);
        }

        $this->_s3->putFile(
            $source,
            $this->getImagePath($id, $secret, "original"),
            array(Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PRIVATE)
        );

        return array("id" => $id, "secret" => $secret, "sizes" => $files);
    }

    public function delete($picturesInfo)
    {
        if (! is_array($picturesInfo)) {
            return;
        }

        foreach ($this->_sizes as $sizeInfo) {
            $this->_s3->removeObject($this->getImagePath($picturesInfo['id'], $picturesInfo['secret'], $sizeInfo[1]));
        }

        $this->_s3->removeObject($this->getImagePath($picturesInfo['id'], $picturesInfo['secret'], "original"));
    }

    public function getImagePath($id, $secret, $size)
    {
        $picturePath = $this->_s3config["picturesBucket"] . "/" . $id . "-" . $secret . "-" . $size;

        return $picturePath;
    }

    public function getImageLink($id, $secret, $size)
    {
        $pictureLink = $this->_s3config["picturesBucketAddress"] . $id . "-" . $secret . "-" . $size;

        return $pictureLink;
    }
}