<?php

require_once 'conf.inc.php';

session_start();

$data = json_decode(file_get_contents("php://input")); 
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
//var_dump(($json_response));

if ($json_response != '') {
	
	$oResponse = json_decode($json_response);
	
	if (isset($oResponse->messages) && count($oResponse->messages) > 0) {
		
		$resp = array();
		foreach ($oResponse->messages as $key => $value) {
			
			if ($value->type == 'ChatMessage') {
					
				$resp[] = $value->message->text;
				
			}
			if ($value->type == 'ChatEnded') {
					
				$result = array('text' => '','chat' => 'stop');
				header ('Content-Type: application/json');
				echo json_encode($result);
				die();
			}
			
		}
		
		$result = array('text' => $resp);
		header ('Content-Type: application/json');
		echo json_encode($result);
		
	} else {
		$result = array('text' => '');
		header ('Content-Type: application/json');
		echo json_encode($result);
	}

} else {
	$result = array('text' => '');
	header ('Content-Type: application/json');
	echo json_encode($result);
}

?>