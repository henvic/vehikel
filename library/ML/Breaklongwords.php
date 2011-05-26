<?php

function breakLongWords($str, $maxLength, $char){//from php.net/wordwrap
    $wordEndChars = array(" ", "\n", "\r", "\f", "\v", "\0");
    $count = 0;
    $newStr = "";
    $openTag = false;
    for($i=0; $i<strlen($str); $i++){
        $newStr .= $str{$i};   
       
        if($str{$i} == "<"){
            $openTag = true;
            continue;
        }
        if(($openTag) && ($str{$i} == ">")){
            $openTag = false;
            continue;
        }
       
        if(!$openTag){
            if(!in_array($str{$i}, $wordEndChars)){//If not word ending char
                $count++;
                if($count==$maxLength){//if current word max length is reached
                    $newStr .= $char;//insert word break char
                    $count = 0;
                }
            }else{//Else char is word ending, reset word char count
                    $count = 0;
            }
        }
       
    }//End for   
    return $newStr;
}
