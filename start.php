<?php 


$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://lhZepTho1M0xnFVznOsT:GRi78QOuyeHpFh0bC5BA@api.cognesys.de:5679/prototype/start");
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, 0);		
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

$result = curl_exec($ch);
$info = curl_getinfo($ch);
//echo "<br>";
echo curl_error($ch);
curl_close($ch);

header ('Content-Type: application/json');
echo ($result);

?>

