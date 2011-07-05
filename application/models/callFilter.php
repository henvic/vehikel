<?php

function base58_encode($num) {
	return base_encode($num, "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ");
}

function base58_decode($num) {
	return base_decode($num, "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ");
}

function base_encode($num, $alphabet) {
    $base_count = strlen($alphabet);
    $encoded = '';
    while ($num >= $base_count) {
    $div = $num/$base_count;
    $mod = ($num-($base_count*intval($div)));
    $encoded = $alphabet[$mod] . $encoded;
    $num = intval($div);
    }

    if ($num) $encoded = $alphabet[$num] . $encoded;

    return $encoded;
}

function base_decode($num, $alphabet) {
    $decoded = 0;
    $multi = 1;
    while (strlen($num) > 0) {
    $digit = $num[strlen($num)-1];
    $decoded += $multi * strpos($alphabet, $digit);
    $multi = $multi * strlen($alphabet);
    $num = substr($num, 0, -1);
    }

    return $decoded;
}



function file_size($size, $type_bytes = false)
{
	if(!$type_bytes) $size/=8;
	//http://snipplr.com/view/4633/convert-size-in-kb-mb-gb-/
	$filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
   
   return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';   
}


function is_natural_dbId($val)
{
	if(!is_natural($val)) return false;
	
	return (strval((int)($val)) != (string)($val)) ? false : true;
}

function is_natural($val, $acceptzero = false) {
 $return = ((string)$val === (string)(int)$val);
 if ($acceptzero)
  $base = 0;
 else
  $base = 1;
 if ($return && intval($val) < $base)
  $return = false;
 return $return;
}
