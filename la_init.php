<?php

include('app/config/config.inc.php');

session_start();


// init session
$con = new connector();
$con->setEndpoint(LIVEAGENT_REST_URL . "/System/SessionId");
$con->setRequestMethod('GET');

$header = array("X-LIVEAGENT-AFFINITY: null",
	            "X-LIVEAGENT-API-VERSION: 40");
$con->setRequestHeader($header);

$response = $con->sendRequest();

$result = json_decode($response['result']);

$_SESSION['affinityToken'] = $result->affinityToken;
$_SESSION['key'] = $result->key;
$_SESSION['sId'] = $result->id;
			
			
// check live agent availability

//get params
$params = "?org_id=" . ORG_ID . "&"
		. "deployment_id=" . DEPLOYMENT_ID . "&Availability.ids=[" . BUTTON_ID . "]";
$con = new connector();
$con->setEndpoint(LIVEAGENT_REST_URL . "/Visitor/Availability" . $params);
$con->setRequestMethod('GET');

$header = array("X-LIVEAGENT-API-VERSION: 40");
$con->setRequestHeader($header);

$response = $con->sendRequest();
$result = json_decode($response['result']);
	
echo "<pre>";
echo "Availabilty Check Response\n";
var_dump($response);	
			
if (isset($result->messages[0]->type) && $result->messages[0]->type == 'Availability' && 
	isset($result->messages[0]->message->results[0]->isAvailable) && $result->messages[0]->message->results[0]->isAvailable == true) {
	
	$con = new connector();
	$con->setEndpoint(LIVEAGENT_REST_URL . "/Chasitor/ChasitorInit");
	$con->setRequestMethod('POST');
	
	$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION['affinityToken'],
		            "X-LIVEAGENT-API-VERSION: 40",
					"X-LIVEAGENT-SESSION-KEY: ".$_SESSION['key'],
					"X-LIVEAGENT-SEQUENCE : 1");
	$con->setRequestHeader($header);
				
				$subParams = '';
$rand = rand(1, 1000);
								
$params = '{
"organizationId": "' . ORG_ID . '", 
"deploymentId": "' . DEPLOYMENT_ID . '", 
"buttonId": "' . BUTTON_ID . '", 
"sessionId": "' . $_SESSION['sId'] . '", 
"visitorName": "FirstLastname ' . $rand . '", 
"userAgent": "Firefox", 
"language": "de_DE", 
"screenResolution": "1024x768", 
"prechatDetails": [{
				"label":"LastName",
				"value":"FirstLastname ' . $rand . '",
				"entityMaps":[{
		           		"entityName":"Contact",
		           		"fieldName":"LastName"
	        	}],
				"transcriptFields":["LastName"],
 				"displayToAgent":true
			},
		    {
				"label":"Email",
				"value":"' . $rand . '_testemail@email.de",
				"entityMaps":[{
		               "entityName":"Contact",
		               "fieldName":"Email"
		            }],
		        "transcriptFields":["Email"],
		        "displayToAgent":true
			},
		    {
				"label":"Phone",
				"value":"' . $rand . '_030-12345678",
				"entityMaps":[{
		            	"entityName":"Contact",
		            	"fieldName":"Phone"
		            }],
		        "transcriptFields":["Phone"],
		        "displayToAgent":true
			}],  
"prechatEntities": [{
	"entityName":"Contact", 
	"showOnCreate":true,        
	"saveToTranscript":"contact",
	"linkToEntityName":"Contact",
	"linkToEntityField":"ContactId",
	"entityFieldsMaps":[{
		   	"fieldName":"LastName",
		   	"label":"LastName",
		   	"doFind":false,
		   	"isExactMatch":false,
		   	"doCreate":false
	    },
	    {
		   	"fieldName":"Phone",
		   	"label":"Phone",
		   	"doFind":false,
		   	"isExactMatch":false,
		   	"doCreate":false
	    },
	    {
	       	"fieldName":"Email",
	       	"label":"Email",
	       	"doFind":true,
	       	"isExactMatch":true,
	       	"doCreate":true
    }]
}], 
"receiveQueueUpdates": true, 
"isPost": true 
}';

	$con->setPostfields($params);
	
	$response = $con->sendRequest();
echo "<pre>";
echo "Header\n";
var_dump($header);

echo "\nPOST Params: \n";
var_dump($params);		
	
echo "\nResponse: \n";
var_dump($response);	
	
}
				


?>