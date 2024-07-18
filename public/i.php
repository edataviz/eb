<?php
$ch = curl_init('https://www.howsmyssl.com/a/check'); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
$data = curl_exec($ch); 
echo $data;
$json = json_decode($data); 
echo "<pre>TLS version: " . $json->tls_version . "</pre>\n";
if(curl_errno($ch)){
    echo 'Curl error: ' . curl_error($ch);
}
curl_close($ch); 
phpinfo();