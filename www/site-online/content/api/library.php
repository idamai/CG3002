<?php
function dbconnect()
{
	$con = mysql_connect("localhost", "root", "password");
	mysql_select_db("regional", $con);
	if(!$con)
	{
		die("Connection Failed!");
	}
	else{
		return $con;
	}
}

function dbclose($con)
{
	mysql_close($con);
}

//call to encrpt
function encrypt($sData, $sKey='54h6vhcg4c4gl'){ 
    $sResult = ''; 
    for($i=0;$i<strlen($sData);$i++){ 
        $sChar    = substr($sData, $i, 1); 
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1); 
        $sChar    = chr(ord($sChar) + ord($sKeyChar)); 
        $sResult .= $sChar; 
    } 
    return encode_base64($sResult); 
} 

//call to decrypt. Use same sKey for encrypt and decrypt
function decrypt($sData, $sKey='54h6vhcg4c4gl'){ 
    $sResult = ''; 
    $sData   = decode_base64($sData); 
    for($i=0;$i<strlen($sData);$i++){ 
        $sChar    = substr($sData, $i, 1); 
        $sKeyChar = substr($sKey, ($i % strlen($sKey)) - 1, 1); 
        $sChar    = chr(ord($sChar) - ord($sKeyChar)); 
        $sResult .= $sChar; 
    } 
    return $sResult; 
} 



function encode_base64($sData){ 
    $sBase64 = base64_encode($sData); 
    return strtr($sBase64, '+/', '-_'); 
} 

function decode_base64($sData){ 
    $sBase64 = strtr($sData, '-_', '+/'); 
    return base64_decode($sBase64); 
} 


?>