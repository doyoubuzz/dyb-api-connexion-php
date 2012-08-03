<?php

/*
 * TEST FILE FOR DOYOUBOUZZ API CONNECTION using a Partner connection 
 * Just configure the informations below :)
 */
 
 
$key 	= '';
$secret = '';
$user_id = '';

$url = "http://api.doyoubuzz.com/user/?userId=".$user_id."&apiKey=".$key."&apiSecret=".$secret;

$post_string = '';

$header  = "POST HTTP/1.0 \r\n";
$header .= "Content-type: text/xml \r\n";
$header .= "Content-length: ".strlen($post_string)." \r\n";
$header .= "Content-transfer-encoding: text \r\n";
$header .= "Connection: close \r\n\r\n"; 

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 4);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $header);

$data = curl_exec($ch); 

print_r($data);

if(curl_errno($ch))
    print curl_error($ch);
else
    curl_close($ch);

?>