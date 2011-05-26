<?php
class ML_Service
{
	public function putString($string)
	{
		fwrite(STDOUT, $string);
	}
	
	public function getInput($what = '', $chars = 100)
	{
		if($what) fwrite(STDOUT, "$what: ");
		$stdin = fopen('php://stdin', 'r');
		$data  = mb_substr(fgets($stdin, 100), 0, -1);
		fclose($stdin);
		
		return $data;
	}
	
	public function requestConfirmAction($what)
	{
		$are_you_sure = $this->getInput("$what (yes/no)?");
		
		if($are_you_sure != 'yes')
		{
			die("Operation canceled.\n");
		}
	}
}