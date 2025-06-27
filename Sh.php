<?php
$data = '4355473705288423|05|2027|460
4355473705286203|05|2027|460
4355473705284042|05|2027|460';

$data = urlencode($data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://arturo.alwaysdata.net/MultiHilos/peticion2.php?cards=$data");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$response = curl_exec($ch);

echo $response;