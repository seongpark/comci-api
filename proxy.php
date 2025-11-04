<?php
$url = "http://comci.net:4082/36179?";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["User-Agent: Mozilla/5.0"]);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
