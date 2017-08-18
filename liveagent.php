<?php
require_once 'conf.inc.php';

session_start();

$data = json_decode(file_get_contents("php://input")); 

// create Session
$contact_url = "https://d.la1-c2-frf.salesforceliveagent.com/chat/rest/System/SessionId";

$curl = curl_init($contact_url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("X-LIVEAGENT-AFFINITY: null",
            "X-LIVEAGENT-API-VERSION: 40"));
curl_setopt($curl, CURLOPT_POST, false);
//curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

$json_response = curl_exec($curl);
$oResponse = json_decode($json_response);
$_SESSION['affinityToken'] = $oResponse->affinityToken;
$_SESSION['key'] = $oResponse->key;
$_SESSION['sId'] = $oResponse->id;

// check availibilty
$params = "?org_id=00D0Y000002iLiq&"
    . "deployment_id=5720Y000000H1cx&Availability.ids=[5730Y000000Gyfr]";

$contact_url = "https://d.la1-c2-frf.salesforceliveagent.com/chat/rest/Visitor/Availability" . $params;

$curl2 = curl_init($contact_url);
curl_setopt($curl2, CURLOPT_HEADER, false);
curl_setopt($curl2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl2, CURLOPT_HTTPHEADER,
        array("X-LIVEAGENT-API-VERSION: 40"));
curl_setopt($curl2, CURLOPT_POST, false);
//curl_setopt($curl2, 'CURLINFO_HEADER_OUT', true);

$json_response = curl_exec($curl2);
//var_dump($json_response);
$oResponseAvailablity = json_decode($json_response);
//var_dump($oResponseAvailablity);

if (isset($oResponseAvailablity->messages[0]->type) && $oResponseAvailablity->messages[0]->type == 'Availability' && 
		isset($oResponseAvailablity->messages[0]->message->results[0]->isAvailable) && $oResponseAvailablity->messages[0]->message->results[0]->isAvailable == true) {
			
			
	$contact_url = "https://d.la1-c2-frf.salesforceliveagent.com/chat/rest/Chasitor/ChasitorInit";
	$curl = curl_init($contact_url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
	        array("X-LIVEAGENT-AFFINITY: ".$_SESSION['affinityToken'],
	            "X-LIVEAGENT-API-VERSION: 40",
				"X-LIVEAGENT-SESSION-KEY: ".$_SESSION['key'],
				"X-LIVEAGENT-SEQUENCE : 1"));
	curl_setopt($curl, CURLOPT_POST, true);
	$params = '{
	"organizationId": "00D0Y000002iLiq", 
	"deploymentId": "5720Y000000H1cx", 
	"buttonId": "5730Y000000Gyfr", 
	"sessionId": "' . $_SESSION['sId'] . '", 
	"userAgent": "Lynx/2.8.8", 
	"language": "de-DE", 
	"screenResolution": "1900x1080", 
	"visitorName": "Max Mustermann", 
	"prechatDetails": [{label: "chathistory", value: "' . implode(';', $data->history) . '", displayToAgent: true, transcriptFields: ["XXX"] }],  
	"prechatEntities": [], 
	"receiveQueueUpdates": true, 
	"isPost": true 
	}';
	//var_dump($params);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
	
	$json_response = curl_exec($curl);	
		

	
	$contact_url = "https://d.la1-c2-frf.salesforceliveagent.com/chat/rest/System/Messages";
	$curl = curl_init($contact_url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
	        array("X-LIVEAGENT-AFFINITY: ".$_SESSION['affinityToken'], 
	            "X-LIVEAGENT-API-VERSION: 40",
				"X-LIVEAGENT-SESSION-KEY: ".$_SESSION['key']));
	curl_setopt($curl, CURLOPT_POST, false);
	curl_setopt($curl, 'CURLINFO_HEADER_OUT', true);
	
	$json_response = curl_exec($curl);
	
	foreach ($data->history as $key => $value) {
		// send message
		$contact_url = "https://d.la1-c2-frf.salesforceliveagent.com/chat/rest/Chasitor/ChatMessage";
		$curl = curl_init($contact_url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER,
		        array("X-LIVEAGENT-AFFINITY: ".$_SESSION['affinityToken'],
		            "X-LIVEAGENT-API-VERSION: 40",
					"X-LIVEAGENT-SESSION-KEY: ".$_SESSION['key']));
		curl_setopt($curl, CURLOPT_POST, true);
		$params = '{
		"text": "' . $value . '"
		}'; 
		
		curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		
		$chat_json_response = curl_exec($curl);
	}
	
		//var_dump($json_response);	 
	$result = array('agent' => true);
	header ('Content-Type: application/json');
	echo json_encode($result);	
		
			
} else {
	
	$result = array('agent' => false);
	header ('Content-Type: application/json');
	echo json_encode($result);
}

?>