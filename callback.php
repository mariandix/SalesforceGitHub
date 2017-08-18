<?php
require_once 'conf.inc.php';

session_start();

$data = json_decode(file_get_contents("php://input")); 
$contact_url = $_SESSION['instance_url'] . "/services/apexrest/DHL/ContactUS/Operation";

$params = '{  
   "requestWrapper":{  
      "inputParam":"' . $data->phone . '"
   }
}';

$curl = curl_init($contact_url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Authorization: OAuth ".$_SESSION['token'],
            "Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

$json_response = curl_exec($curl);
var_dump($json_response);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


?>