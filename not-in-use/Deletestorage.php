<?php

	/*public function deleteAllFromStorage($userInfo)
	{
		$registry = Zend_Registry::getInstance();
		$config = $registry->get("config");
		
		$s3 = new Zend_Service_Amazon_S3($config->services->S3->key, $config->services->S3->secret);
		
		
		$select = $this->select();
		$select->where("byUid = ?", $userInfo['id'])
		->order("uploadedTime DESC")
		;
		
		$shares = $this->fetchAll($select);
		
		foreach($shares->toArray() as $shareInfo)
		{
			$object_key = $config->services->S3->sharesBucket."/".$userInfo['alias']."/".$shareInfo['id']."_".$shareInfo['download_secret']."/".$shareInfo['filename'];
			$s3->removeObject($object_key);
		}
	}*/
	
?>