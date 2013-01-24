<?php
class Ml_Model_Numbers
{
    const base58 = "123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ";

    /**
     * baseSimple (base58 - uppercase -a -e -u)
     */
    const baseSimple = "123456789BCDFGHJKLMNPQRSTWXYZ";
    
    public function base58Encode($num)
    {
        return self::baseEncode($num, self::base58);
    }
    
    public function base58Decode($num)
    {
        return self::baseDecode($num, self::base58);
    }
    
    public function baseEncode($num, $alphabet)
    {
        $num = (string) $num;
        
        $baseCount = strlen($alphabet);
        $encoded = '';
        
        while (bccomp($num, $baseCount) > -1) {
            $div = bcdiv($num, $baseCount, 0);
            
            $mod = bcsub($num, bcmul($baseCount, $div, 0), 0);
            $encoded = $alphabet[$mod] . $encoded;
            $num = $div;
        }
        if (bccomp($num, "1", 0) >= 0) {
            $encoded = $alphabet[$num] . $encoded;
        }
        return $encoded;
    }
    
    public function baseDecode($num, $alphabet)
    {
        $num = (string) $num;
        
        $decoded = 0;
        $multi = 1;
        
        while (strlen($num) > 0) {
            $digit = $num[strlen($num) - 1];
            
            $decoded = bcadd($decoded, (bcmul($multi, strpos($alphabet, $digit))));
            $multi = bcmul($multi, strlen($alphabet));
            $num = substr($num, 0, - 1);
        }
        return $decoded;
    }
}