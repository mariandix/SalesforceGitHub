<?php
require_once 'conf.inc.php';

session_start();

$token_url = LOGIN_URI . "/services/oauth2/token";

$params = "&grant_type=password"
    . "&client_id=" . CLIENT_ID
    . "&client_secret=" . CLIENT_SECRET
    . "&username=" . USERNAME
    . "&password=" . PASSWORD;

$curl = curl_init($token_url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

$oResponse = json_decode($json_response);

$_SESSION['token'] = $token = $oResponse->access_token;
$_SESSION['instance_url'] = $instance_url = $oResponse->instance_url;

$data = json_decode(file_get_contents("php://input")); 
$contact_url = $instance_url . "/services/apexrest/DHL/Contact/Operation";

$params = '{  
   "requestWrapper":{  
      "requestNumber":"' . $data->session_id . '",
      "FirstName":"FirstName",
      "LastName":"' . $data->name . '",
      "Email":"' . $data->email . '",
      "PhoneNumber":"' . $data->phone . '",
      "Title":"Title"
   }
}';

$curl = curl_init($contact_url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Authorization: OAuth $token",
            "Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

$json_response = curl_exec($curl);
var_dump($json_response);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


?>