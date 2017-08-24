<?php

include('app/config/config.inc.php');

header ('Content-type: application/json');
$data = json_decode(file_get_contents("php://input")); 

session_start();

if (isset($data) && count($data) > 0) {
	
	$response = array();
	
	switch ($data->type) {
		
		case 'cognesys_start':
			
			$con = new connector();
			$con->setEndpoint(COGNESYS_URL . '/start');
			$con->setRequestMethod('GET');
			
			$response = $con->sendRequest();
			
			$data = (array)json_decode($response['result']);
					
			if (is_array($data) && isset($data['session-id']) && $data['session-id'] != '') {
				
				echo json_encode(array('status' => 'ok', 'result' => $response));
				die();
			} else {
				
				echo json_encode(array('status' => 'ChatBot Not Available'));
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
			
			$params = '{"session-id":"' . $data->session_id . '","text":"' . $data->text . '", "sequence-id":"' . $data->sequence . '"}';
			
			$con->setPostfields($params);

			$response = $con->sendRequest();
			
			echo json_encode(array('status' => 'ok', 'params' => $params, 'result' => $response));
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

			echo json_encode(array('status' => 'ok', 'params' => $params, 'result' => $response));
			die();
			
			break;
		
		case 'sendCustomerData': 
			
			// init token
			$con = new connector();
			$con->setEndpoint(LOGIN_URI . "/services/oauth2/token");
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
				$con->setRequestMethod('POST');
				
$params = '{  
"requestWrapper":{  
"requestNumber":"' . $data->session_id . '",
"FirstName":"FirstName",
"LastName":"' . $data->name . '",
"Email":"' . $data->email . '",
"PhoneNumber":"' . $data->phone . '",
"Title":"Title",
"chatHistory":' . json_encode($data->chathistory) . ',
"callbackInfo":"' . $data->callback . '",
"chatStatus":"' . $data->status . '",
"chatBotSummary":"' . (isset($data->summary) ? $data->summary: "") . '",
"tonality": "' . (isset($data->tonality) ? $data->tonality : "") . '",
"endTime":"' . $data->endTime . '",
"startTime":"' . $data->startTime . '"
}
}';
				
			/*	$params = '{
"requestWrapper":{
  
   "tonality":"chattonality",
   "Title":"mr",
   "requestNumber":"requestNumber",
   "PhoneNumber":"121344444",
   "LastName":"lastName",
   "FirstName":"Firsname",
   "Email":"testa@madeup.co",
   "chatStatus":"HandOver",
   "chatHistory":[  
      {  
         "Type":"Q",
         "sequenceNumber":"1",
         "message":"What is my name "
      }
   ],
   "chatBotSummary":"chatbotSummary",
   "callbackInfo":"callbackInfo"
}
}';*/
				
				$con->setPostfields($params);

				$header = array("Authorization: OAuth $token",
				            "Content-type: application/json");
				$con->setRequestHeader($header);

				$response = $con->sendRequest();

				echo json_encode(array('status' => 'ok', 'params' => $params, 'result' => $response));
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
				
				$params = '{
				"organizationId": "' . ORG_ID . '", 
				"deploymentId": "' . DEPLOYMENT_ID . '", 
				"buttonId": "' . BUTTON_ID . '", 
				"sessionId": "' . $_SESSION['sId'] . '", 
				"visitorName": "' . $data->name . '", 
				"prechatDetails": [{label: "chathistory", value: "", displayToAgent: true, transcriptFields: ["XXX"] }],  
				"prechatEntities": [], 
				"receiveQueueUpdates": true, 
				"isPost": true 
				}';
				
				//' . implode(';', $data->history) . '
				
				$con->setPostfields($params);
				
				$response = $con->sendRequest();
			
/*					
				$con = new connector();
				$con->setEndpoint(LIVEAGENT_REST_URL . "/System/Messages");
				$con->setRequestMethod('GET');
				
				$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION['affinityToken'], 
					            "X-LIVEAGENT-API-VERSION: 40",
								"X-LIVEAGENT-SESSION-KEY: ".$_SESSION['key']);
				$con->setRequestHeader($header);
				
				$response = $con->sendRequest();
*/				
/*
				foreach ($data->history as $key => $value) {
					// send message
					
					$con = new connector();
					$con->setEndpoint(LIVEAGENT_REST_URL . "/Chasitor/ChatMessage");
					$con->setRequestMethod('POST');
					
					$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION['affinityToken'],
						            "X-LIVEAGENT-API-VERSION: 40",
									"X-LIVEAGENT-SESSION-KEY: ".$_SESSION['key']);
					$con->setRequestHeader($header);
				

					$params = '{
						"text": "' . $value . '"
					}'; 
					
					$con->setPostfields($params);
				
					$response = $con->sendRequest();
					
					
					
				}
*/
  				$result = array('status' => 'ok', 'response' => $response, 'agent' => true);
				echo json_encode($result);	
			
			} else {
			
				$result = array('status' => 'ok', 'response' => $response, 'agent' => false);
				echo json_encode($result);	
			}
			
		
			break;

		case 'liveagent_check':
			
			$con = new connector();
			$con->setEndpoint(LIVEAGENT_REST_URL . "/System/Messages");
			$con->setRequestMethod('GET');
			
			$header = array("X-LIVEAGENT-AFFINITY: ".$_SESSION['affinityToken'], 
				            "X-LIVEAGENT-API-VERSION: 40",
							"X-LIVEAGENT-SESSION-KEY: ".$_SESSION['key']);
			$con->setRequestHeader($header);
			
			$response = $con->sendRequest();
			$result = json_decode($response['result']);
			

			if ($result != '') {
				
				$oResponse = $result;
				
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

		
			break;

	}
	
	
	
} else {
	
	echo json_encode(array('status' => 'empty vars'));
	die();
	
}

?>