<?php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);

header('X-Frame-Options: SAMEORIGIN'); 
header("Content-Security-Policy: default-src 'self' maxcdn.bootstrapcdn.com; script-src 'self' 'unsafe-inline' www.google-analytics.com ajax.googleapis.com maxcdn.bootstrapcdn.com cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' maxcdn.bootstrapcdn.com;"); 
header("X-Content-Security-Policy: default-src 'self' maxcdn.bootstrapcdn.com; script-src 'self' 'unsafe-inline' www.google-analytics.com ajax.googleapis.com maxcdn.bootstrapcdn.com cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' maxcdn.bootstrapcdn.com;");

include('app/config/config.inc.php');

header ('Content-type: application/json');
$tmp = (array)json_decode(file_get_contents("php://input")); 
if (count($tmp) > 0) {
	$data = new stdClass();
	foreach($tmp as $k => $v) {
		if ($k == 'email') {
			$v = str_replace(' ','',$v);
		}
		if (is_string($v)) {
			$data->$k = strip_tags($v);
			if ($k == 'name' && $data->$k == '') {
				$data->$k = 'Mustermann';
			} 
		} else {
			$data->$k = $v;
		}
	}
}

session_start();

// when data is sending from the angular post request 
if (isset($data) && count($data) > 0) {
	
	$response = array();
	
	switch ($data->type) {
		
		case 'cognesys_start':
			
			$con = new connector();
			$con->setEndpoint(COGNESYS_URL . '/start');
			$con->setRequestMethod('GET');
			
			$response = $con->sendRequest();
			
			$data = (array)json_decode($response['result']);
					
			$_SESSION['chatbot'] = array();		
					
			if (is_array($data) && isset($data['session-id']) && $data['session-id'] != '') {
				
				echo json_encode(array('status' => 'ok', 'result' => $response));
				die();
			} else {
				
				echo json_encode(array('status' => 'ChatBot Not Available', 'result' => $response));
				die();
			}	
					
			break;
		
		case 'cognesys_talk':
			
			$con = new connector();
			$con->setEndpoint(COGNESYS_URL . '/talk');
			$con->setRequestMethod('POST');
			
			$header = array("Accept: application/json",
				            "Content-Type: application/json");
			$con->setRequestHeader($header);
			
			$params = '{"session-id":"' . $data->session_id . '","text":"' . ($data->text) . '", "sequence-id":"' . $data->sequence . '"}';
			
			$con->setPostfields($params);

			$response = $con->sendRequest();
			
			$_SESSION['chatbot'][] = $response['result'];

			echo json_encode(array('status' => 'ok', 'result' => $response));
			die();

			break;
		
		case 'cognesys_stop':
			
			$con = new connector();
			$con->setEndpoint(COGNESYS_URL . '/stop');
			$con->setRequestMethod('POST');
			
			$header = array("Accept: application/json",
				            "Content-Type: application/json");
			$con->setRequestHeader($header);
			
			$params = '{"session-id":"' . $data->session_id . '"}';
			
			$con->setPostfields($params);

			$response = $con->sendRequest();

			echo json_encode(array('status' => 'ok', 'result' => $response));
			die();
			
			break;
		
		case 'sendCustomerData': 
			
			// init token
			$con = new connector();
			$con->setEndpoint(LOGIN_URI . "/services/oauth2/token");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('POST');
			
			$params = "&grant_type=password"
				    . "&client_id=" . CLIENT_ID
				    . "&client_secret=" . CLIENT_SECRET
				    . "&username=" . USERNAME
				    . "&password=" . PASSWORD;
			
			$con->setPostfields($params);
			
			$response = $con->sendRequest();

			if (isset($response['result'])) {

				$responseData = json_decode($response['result']);
				$_SESSION['token'] = $token = $responseData->access_token;
				$_SESSION['instance_url'] = $instance_url = $responseData->instance_url;
				
				$con = new connector();
				$con->setEndpoint($instance_url . "/services/apexrest/DHL/CustomerInfoAndCallBack/Operation");
				$con->setProxy(PROXY_URI);
				$con->setRequestMethod('POST');

$params = '{  
"requestWrapper":{  
"requestNumber":"' . $data->session_id . '",
"FirstName":"' . $data->firstname . '",
"LastName":"' . $data->name . '",
"Email":"' . $data->email . '",
"PhoneNumber":"' . (isset($data->phone)?$data->phone:"") . '",
"Title":"' . $data->salutation . '",
"chatHistory":' . json_encode($data->chathistory) . ',
"callbackInfo":"' . $data->callback . '",
"chatStatus":"' . $data->status . '",
"chatBotSummary":"' . (isset($data->summary)?addslashes(implode(',',$data->summary)):"") . '",
"tonality": "' . (isset($data->tonality) ? $data->tonality : "") . '",
"chatStartTime": "' . (isset($data->startTime) ? $data->startTime : "2017-09-04T13:20:49.717+02:00") . '",
"chatEndTime": "' . (isset($data->endTime) ? $data->endTime : "2017-09-04T13:25:01.975+02:00") . '", 
"jsonStringBody": "' . (strlen(addslashes(implode(',', $_SESSION['chatbot']))) > 32000 ? "{}" : addslashes(implode(',', $_SESSION['chatbot']))) . '" 
}
}';
				
				$con->setPostfields($params);

				$header = array("Authorization: OAuth $token",
				            "Content-type: application/json");
				$con->setRequestHeader($header);

				$response_save = $con->sendRequest();

				echo json_encode(array('status' => 'ok', 'result' => $response_save));
				die();
			} else {
				
				echo json_encode(array('status' => 'error'));
				die();
			}
			
			break;
			
		case 'liveagent_init':
			
			// init session
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/System/SessionId");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('GET');
			
			$header = array("X-LIVEAGENT-AFFINITY: null",
				            "X-LIVEAGENT-API-VERSION: 40");
			$con->setRequestHeader($header);
			
			$response_session = $con->sendRequest();
			
			$result_session = json_decode($response_session['result']);
			
			$_SESSION[$result_session->id]['affinityToken'] = $result_session->affinityToken;
			$_SESSION[$result_session->id]['key'] = $result_session->key;
			$_SESSION[$result_session->id]['sId'] = $result_session->id;
			
			
			// check live agent availability
			
			//get params
			$params = "?org_id=" . ORG_ID . "&"
   					. "deployment_id=" . DEPLOYMENT_ID . "&Availability.ids=[" . BUTTON_ID . "]";
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/Visitor/Availability" . $params);
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('GET');
			
			$header = array("X-LIVEAGENT-API-VERSION: 40");
			$con->setRequestHeader($header);
			
			$response = $con->sendRequest();
			$result = json_decode($response['result']);
			
			if (isset($result->messages[0]->type) && $result->messages[0]->type == 'Availability' && 
				isset($result->messages[0]->message->results[0]->isAvailable) && $result->messages[0]->message->results[0]->isAvailable == true) {

				$con = new connector();
				$con->setEndpoint(LIVEAGENT_REST_URL . "/Chasitor/ChasitorInit");
				$con->setProxy(PROXY_URI);
				$con->setRequestMethod('POST');
				
				$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$result_session->id]['affinityToken'],
					            "X-LIVEAGENT-API-VERSION: 40",
								"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$result_session->id]['key'],
								"X-LIVEAGENT-SEQUENCE : 1");
				$con->setRequestHeader($header);
				
				$subParams = '';
				
$params = '{
"organizationId": "' . ORG_ID . '", 
"deploymentId": "' . DEPLOYMENT_ID . '", 
"buttonId": "' . BUTTON_ID . '", 
"sessionId": "' . $_SESSION[$result_session->id]['sId'] . '", 
"visitorName": "' . $data->firstname . ' ' . $data->name . '", 
"userAgent": "' . $data->userAgent . '", 
"language": "de", 
"screenResolution": "' . $data->width . 'x' . $data->height . '", 
"prechatDetails": [
	{
		"label":"LastName",
		"value":"' . $data->firstname . ' ' . $data->name . '",
		"entityMaps":[
        	{
           		"entityName":"Contact",
           		"fieldName":"LastName"
        	}
     	],
     	"transcriptFields":[
        	"LastName"
     	],
         "displayToAgent":true
	},
	{
		"label":"Title",
		"value":"' . $data->salutation . '",
		"entityMaps":[
        	{
           		"entityName":"Contact",
           		"fieldName":"Title"
        	}
     	],
     	"transcriptFields":[
        	"Title"
     	],
         "displayToAgent":true
	},
    {
		"label":"Email",
		"value":"' . $data->email . '",
		"entityMaps":[
            {
               "entityName":"Contact",
               "fieldName":"Email"
            }
		],
        "transcriptFields":[
        	"Email"
		],
        "displayToAgent":true
	},
    {
		"label":"Phone",
		"value":"' . (isset($data->phone)?$data->phone:"") . '",
		"entityMaps":[
        	{
            	"entityName":"Contact",
            	"fieldName":"Phone"
            }
		],
        "transcriptFields":[
        	"Phone"
		],
        "displayToAgent":true
	},
	{
		"label":"Tonality",
		"value":"' . (isset($data->tonality)?$data->tonality:"") . '",
		"entityMaps":[
        	{
            	"entityName":"LiveChatTranscript",
            	"fieldName":"ChatBotTonality__c"
            }
		],
        "transcriptFields":[
        	"ChatBotTonality__c"
		],
        "displayToAgent":true
	}
],  
"prechatEntities": [
	{
		"entityName":"Contact",  
		"showOnCreate":true,          
		"saveToTranscript":"Contact",
		"linkToEntityName":null,
		"linkToEntityField":"ContactId",
		"entityFieldsMaps":[
			{
			   	"fieldName":"LastName",
			   	"label":"LastName",
			   	"doFind":false,
			   	"isExactMatch":false,
			   	"doCreate":true
            },
            {
			   	"fieldName":"Title",
			   	"label":"Title",
			   	"doFind":false,
			   	"isExactMatch":false,
			   	"doCreate":true
            },
            {
			   	"fieldName":"Phone",
			   	"label":"Phone",
			   	"doFind":false,
			   	"isExactMatch":false,
			   	"doCreate":true
            },
            {
               	"fieldName":"Email",
               	"label":"Email",
               	"doFind":true,
               	"isExactMatch":true,
               	"doCreate":true
            }           
		]
	}
], 
"receiveQueueUpdates": false, 
"isPost": true 
}';

				$con->setPostfields($params);
				
				$response_chatinit = $con->sendRequest();

  				$result = array('status' => 'ok', 'token' => $_SESSION[$result_session->id]['sId'], 'agent' => true);
				echo json_encode($result);	
			
			} else {
			
				$result = array('status' => 'ok', 'agent' => false);
				echo json_encode($result);	
			}
			
		
			break;

		case 'liveagent_check':
			
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/System/Messages");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('GET');
			
			$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$data->token]['affinityToken'], 
				            "X-LIVEAGENT-API-VERSION: 40",
							"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$data->token]['key']);
			$con->setRequestHeader($header);
			
			$response = $con->sendRequest();
			$result = json_decode($response['result']);
			

			if ($result != '') {
				
				$oResponse = $result;
				
				if (isset($oResponse->messages) && count($oResponse->messages) > 0) {
					
					$resp = array();
					foreach ($oResponse->messages as $key => $value) {
						
						if ($value->type == 'ChatEstablished') {
							
							foreach ($data->history as $key => $value) {
								// send message
								
								$con = new connector();
								$con->setEndpoint(LIVEAGENT_REST_URL . "/Chasitor/ChatMessage");
								$con->setProxy(PROXY_URI);
								$con->setRequestMethod('POST');
								
								$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$data->token]['affinityToken'],
									            "X-LIVEAGENT-API-VERSION: 40",
												"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$data->token]['key']);
								$con->setRequestHeader($header);
							
			
								$params = '{
									"text": "' . (($value->Type == 'Q') ? $data->name:'ChatBot') . ': ' . $value->message . '"
								}'; 
								
								$con->setPostfields($params);
							
								$response = $con->sendRequest();
							}
								
							$result = array('text' => '','chat' => 'established');
							header ('Content-Type: application/json');
							echo json_encode($result);
							die();
							
						}
						if ($value->type == 'ChatRequestFail') {
								
							$result = array('text' => '','chat' => 'requestfail');
							header ('Content-Type: application/json');
							echo json_encode($result);
							die();
							
						}
						if ($value->type == 'ChatRequestSuccess') {
								
							$result = array('text' => '','chat' => 'requestsuccess');
							header ('Content-Type: application/json');
							echo json_encode($result);
							die();
							
						}
						if ($value->type == 'ChatMessage') {
								
							$resp[] = $value->message->text;
							
						}
						if ($value->type == 'ChatEnded') {
							$messageId = '';
							
							if (isset($value->message->attachedRecords) 
								&& count($value->message->attachedRecords) > 0 
								&& isset($value->message->attachedRecords[0]->fieldValue) ) {
								
								$messageId = $value->message->attachedRecords[0]->fieldValue;
								
							}
							
							$result = array('text' => '','chat' => 'stop', 'messageId' => $messageId);
							header ('Content-Type: application/json');
							echo json_encode($result);
							die();
						}
						
					}
					
					$result = array('text' => $resp);
					header ('Content-Type: application/json');
					echo json_encode($result);
					
				} else {
					$result = array('text' => '', 'resp' => 'no-msg');
					header ('Content-Type: application/json');
					echo json_encode($result);
				}
			
			} else {
				
				if (isset($data->cnt) && $data->cnt >= LIVEAGENT_CHECK_COUNT) {
					$result = array('text' => '','chat' => 'requestfail');
					header ('Content-Type: application/json');
					echo json_encode($result);
					die();
				} else {
				
					$result = array('text' => '', 'chat' => 'no-resp');
					header ('Content-Type: application/json');
					echo json_encode($result);
				}
			}

		
			break;

		case 'liveagent_talk' :
			
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/System/Messages");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('GET');
			
			$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$data->token]['affinityToken'], 
				            "X-LIVEAGENT-API-VERSION: 40",
							"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$data->token]['key']);
			$con->setRequestHeader($header);
			
			$response = $con->sendRequest();
			$result = json_decode($response['result']);

			
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/Chasitor/ChatMessage");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('POST');
			
			$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$data->token]['affinityToken'],
				            "X-LIVEAGENT-API-VERSION: 40",
							"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$data->token]['key']);
			$con->setRequestHeader($header);
		

			$params = '{
				"text": "' . $data->text . '"
			}'; 
			
			$con->setPostfields($params);
		
			$response = $con->sendRequest();

			if ($result != '') {
				
				$oResponse = $result;
				
				if (isset($oResponse->messages) && count($oResponse->messages) > 0) {
					
					$resp = array();
					foreach ($oResponse->messages as $key => $value) {
						
						if ($value->type == 'ChatMessage') {
								
							$resp[] = $value->message->text;
							
						}
						if ($value->type == 'ChatEnded') {
							$messageId = '';
							
							if (isset($value->message->attachedRecords) 
								&& count($value->message->attachedRecords) > 0 
								&& isset($value->message->attachedRecords[0]->fieldValue) ) {
								
								$messageId = $value->message->attachedRecords[0]->fieldValue;
								
							}
							
							$result = array('text' => '','chat' => 'stop', 'messageId' => $messageId);
							header ('Content-Type: application/json');
							echo json_encode($result);
							die();
						}
						
					}
					
					$result = array('text' => $resp, 'resp' => $oResponse->messages);
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
			
			
			break;
		
		case 'liveagent_checkandtalk' :
			
			if (isset($data->text) && $data->text != '') {
				$con = new connector();
				$con->setEndpoint(LIVEAGENT_REST_URL . "/Chasitor/ChatMessage");
				$con->setProxy(PROXY_URI);
				$con->setRequestMethod('POST');
				
				$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$data->token]['affinityToken'],
					            "X-LIVEAGENT-API-VERSION: 40",
								"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$data->token]['key']);
				$con->setRequestHeader($header);
			
	
				$params = '{
					"text": "' . $data->text . '"
				}'; 
				
				$con->setPostfields($params);
			
				$response = $con->sendRequest();

			}
			
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/System/Messages");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('GET');
			
			$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$data->token]['affinityToken'], 
				            "X-LIVEAGENT-API-VERSION: 40",
							"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$data->token]['key']);
			$con->setRequestHeader($header);

			$response = $con->sendRequest();
			$result = json_decode($response['result']);
			
			if ($result != '') {
				
				$oResponse = $result;
				
				if (isset($oResponse->messages) && count($oResponse->messages) > 0) {
					
					$resp = array();
					$typing = false;
					foreach ($oResponse->messages as $key => $value) {
						
						if ($value->type == 'ChatMessage') {
								
							$resp[] = $value->message->text;
							
						}
						if ($value->type == 'AgentTyping') {
								
							$typing = true;
							
						}
						if ($value->type == 'AgentNotTyping') {
								
							$typing = false;
							
						}
						if ($value->type == 'ChatEnded') {
							$messageId = '';
							
							if (isset($value->message->attachedRecords) 
								&& count($value->message->attachedRecords) > 0 
								&& isset($value->message->attachedRecords[0]->fieldValue) ) {
								
								$messageId = $value->message->attachedRecords[0]->fieldValue;
								
							}
							
							$result = array('text' => '','chat' => 'stop', 'messageId' => $messageId);
							header ('Content-Type: application/json');
							echo json_encode($result);
							die();
						}
						
						if ($value->type == 'AgentDisconnect') {
								
							$result = array('text' => $resp,'chat' => 'disconnect');
							header ('Content-Type: application/json');
							echo json_encode($result);
							die();
						}
						
					}
					
					$result = array('text' => $resp, 'typing' => $typing, 'resp' => $oResponse->messages);
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
			
			
			break;
		
		case 'liveagent_stop' :
			
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/Chasitor/ChatEnd");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('POST');
			
			$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$data->token]['affinityToken'],
				            "X-LIVEAGENT-API-VERSION: 40",
							"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$data->token]['key']);
			$con->setRequestHeader($header);
			
			$params = '{reason: "client"}'; 
			
			$con->setPostfields($params);
		
			$response = $con->sendRequest();
			
			$result = array('status' => 'ok');
			header ('Content-Type: application/json');
			echo json_encode($result);
			
			break;
			
		case 'sendSurveyRating' :
			
			// init token
			$con = new connector();
			$con->setEndpoint(LOGIN_URI . "/services/oauth2/token");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('POST');
			
			$params = "&grant_type=password"
				    . "&client_id=" . CLIENT_ID
				    . "&client_secret=" . CLIENT_SECRET
				    . "&username=" . USERNAME
				    . "&password=" . PASSWORD;
			
			$con->setPostfields($params);
			
			$response_token = $con->sendRequest();

			if (isset($response_token['result'])) {

				$responseData = json_decode($response_token['result']);
				$_SESSION['token'] = $token = $responseData->access_token;
				$_SESSION['instance_url'] = $instance_url = $responseData->instance_url;
				
				$con = new connector();
				$con->setEndpoint($instance_url . "/services/apexrest/DHL/PostChatSurveyRating/Update");
				$con->setProxy(PROXY_URI);
				$con->setRequestMethod('POST');

if (isset($data->callback) && $data->callback != '') {				
$params = '{  
"requestWrapper":{  
"recordId":"' . (isset($data->callback) ? $data->callback: "") . '",
"surveyRating":"' . $data->rating . '"
}
}';
} else {
$params = '{  
"requestWrapper":{  
"surveyRating":"' . $data->rating . '",
"sessionId":"' . (isset($data->session) ? $data->session: "") . '",
"contactId":"' . (isset($data->messageId) ? $data->messageId: "") . '"
}
}';
}
				
				$con->setPostfields($params);

				$header = array("Authorization: OAuth $token",
				            "Content-type: application/json");
				$con->setRequestHeader($header);

				$response = $con->sendRequest();

				echo json_encode(array('status' => 'ok'));
				die();
			} else {
				
				echo json_encode(array('status' => 'error'));
				die();
			}
			
			break;			
			
	}

// when sending the data over the unload event and the ajax call
} elseif (isset($_POST) && count($_POST) > 0) {
	
	ignore_user_abort(1);
	set_time_limit(0);
	
	switch ($_POST['type']) {
		
		case 'liveagent_stop' :
			
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/Chasitor/ChatEnd");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('POST');
			
			$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION[$_POST['token']]['affinityToken'],
				            "X-LIVEAGENT-API-VERSION: 40",
							"X-LIVEAGENT-SESSION-KEY: ".$_SESSION[$_POST['token']]['key']);
			$con->setRequestHeader($header);
			
			$params = '{reason: "client"}'; 
			
			$con->setPostfields($params);
		
			$response = $con->sendRequest();
			
			$result = array('status' => 'ok');
			header ('Content-Type: application/json');
			echo json_encode($result);

			break;
		
		case 'sendCustomerData': 

			$con = new connector();
			$con->setEndpoint(COGNESYS_URL . '/stop');
			$con->setRequestMethod('POST');
			
			$header = array("Accept: application/json",
				            "Content-Type: application/json");
			$con->setRequestHeader($header);
			
			$params = '{"session-id":"' . $_POST['session_id'] . '"}';
			
			$con->setPostfields($params);

			$responseStop = $con->sendRequest();
			$responseStopData = json_decode($responseStop['result']);
				
			// init token
			$con = new connector();
			$con->setEndpoint(LOGIN_URI . "/services/oauth2/token");
			$con->setProxy(PROXY_URI);
			$con->setRequestMethod('POST');
			
			$params = "&grant_type=password"
				    . "&client_id=" . CLIENT_ID
				    . "&client_secret=" . CLIENT_SECRET
				    . "&username=" . USERNAME
				    . "&password=" . PASSWORD;
			
			$con->setPostfields($params);
			
			$response = $con->sendRequest();

			if (isset($response['result'])) {

				$responseData = json_decode($response['result']);
				$_SESSION['token'] = $token = $responseData->access_token;
				$_SESSION['instance_url'] = $instance_url = $responseData->instance_url;
				
				$con = new connector();
				$con->setEndpoint($instance_url . "/services/apexrest/DHL/CustomerInfoAndCallBack/Operation");
				$con->setProxy(PROXY_URI);
				$con->setRequestMethod('POST');
				
$params = '{  
"requestWrapper":{  
"requestNumber":"' . $_POST['session_id'] . '",
"FirstName":"' . $_POST['firstname'] . '",
"LastName":"' . $_POST['name'] . '",
"Email":"' . str_replace(' ','',$_POST['email']) . '",
"PhoneNumber":"' . (isset($_POST['phone'])?$_POST['phone']:"") . '",
"Title":"' . $_POST['salutation'] . '",
"chatHistory":' . (isset($_POST['chathistory']) ? json_encode($_POST['chathistory']): "") . ',
"callbackInfo":"' . (isset($_POST['callback']) ? $_POST['callback']: "") . '",
"chatStatus":"Aborted",
"chatBotSummary":"' . (isset($_POST['summary']) ? $_POST['summary']: "") . '",
"tonality": "' . (isset($responseStopData->tonality) ? $responseStopData->tonality : "") . '", 
"jsonStringBody": "' . (strlen(addslashes(implode(',', $_SESSION['chatbot']))) > 32000 ? "{}" : addslashes(implode(',', $_SESSION['chatbot']))) . '" 
}
}';
				
					
				$con->setPostfields($params);

				$header = array("Authorization: OAuth $token",
				            "Content-type: application/json");
				$con->setRequestHeader($header);

				$response = $con->sendRequest();

				echo json_encode(array('status' => 'ok'));
				die();
			} else {
				
				echo json_encode(array('status' => 'error'));
				die();
			}
			
			break;		
		default: 
	
		echo json_encode(array('status' => 'default'));
		die();
	}	
	
	echo json_encode(array('status' => 'no vars'));
	die();
	
} else {
	
	echo json_encode(array('status' => 'empty vars'));
	die();
	
}

?>