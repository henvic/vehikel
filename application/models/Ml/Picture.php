<?php

class Ml_Model_Picture
{
    protected $_imageQuality = 70;

    protected $_s3config = null;

    /** @var \Zend_Cloud_StorageService_Adapter() */
    protected $_storage;

    protected $_sizes = array(
        "square.jpg" => 80,
        "square@2x.jpg" => 160,
        "square-big.jpg" => 220,
        "square-big@2x.jpg" => 440,
        "thumbnail.jpg" => 200,
        "thumbnail@2x.jpg" => 400,
        "medium.jpg" => 500,
        "medium@2x.jpg" => 1000,
        "large.jpg" => 1200,
        "large@2x.jpg" => 2400
    );


    /**
     * @param Zend_Config array $config
     */
    public function __construct(array $config)
    {
        $this->_s3config = $config['services']['S3'];

        $storage = new Zend_Cloud_StorageService_Adapter_S3(array(
            Zend_Cloud_StorageService_Factory::STORAGE_ADAPTER_KEY => 'Zend_Cloud_StorageService_Adapter_S3',
            Zend_Cloud_StorageService_Adapter_S3::AWS_ACCESS_KEY => $this->_s3config["key"],
            Zend_Cloud_StorageService_Adapter_S3::AWS_SECRET_KEY => $this->_s3config["secret"],
            Zend_Cloud_StorageService_Adapter_S3::BUCKET_NAME => $this->_s3config["picturesBucket"]
        ));

        $this->_storage = $storage;
    }

    protected function getStorage()
    {
        return $this->_storage;
    }

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

            if (mb_strpos($partialPath, "square") !== false) {
                $im->cropThumbnailImage($maxDim, $maxDim);
            } else if ($originalDimension['width'] > $maxDim && $originalDimension['height'] > $maxDim) {
                if ($originalDimension['width'] > $originalDimension['height']) {
                    $im->resizeimage($maxDim, 0, Imagick::FILTER_LANCZOS, 0);
                } else {
                    $im->resizeimage(0, $maxDim, Imagick::FILTER_LANCZOS, 0);
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

        $secret = mb_substr(md5(openssl_random_pseudo_bytes(20) . mt_rand()), 0, 8);

        foreach ($files as $partialPath => &$info) {
            $fileData = file_get_contents($info["path"]);
            $this->getStorage()->storeItem(
                $this->getImagePath($id, $secret, $partialPath),
                $fileData,
                [
                    Zend_Cloud_StorageService_Adapter_S3::METADATA => [
                        Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PUBLIC_READ,
                        "Content-Type" => "image/jpeg",
                        "Cache-Control" => "max-age=1209600"
                    ]
                ]
            );

            unlink($info["path"]);
            unset($info["path"]);
        }

        $fileData = file_get_contents($source);
        $this->getStorage()->storeItem(
            $this->getImagePath($id, $secret, "original"),
            $fileData,
            [
                Zend_Cloud_StorageService_Adapter_S3::METADATA => [
                    Zend_Service_Amazon_S3::S3_ACL_HEADER => Zend_Service_Amazon_S3::S3_ACL_PRIVATE
                ]
            ]
        );

        return array("id" => $id, "secret" => $secret, "sizes" => $files);
    }

    public function delete($id, $secret)
    {
        foreach ($this->_sizes as $sizeInfo) {
            $this->getStorage()->deleteItem($this->getImagePath($id, $secret, $sizeInfo[1]));
        }

        $this->getStorage()->deleteItem($this->getImagePath($id, $secret, "original"));
    }

    public function getImagePath($id, $secret, $size)
    {
        $picturePath = $id . "-" . $secret . "-" . $size;

        return $picturePath;
    }

    public function getImageLink($id, $secret, $size)
    {
        $pictureLink = $this->_s3config["picturesBucketAddress"] . $id . "-" . $secret . "-" . $size;

        return $pictureLink;
    }
}