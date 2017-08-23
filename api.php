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
				
				echo json_encode(array('status' => 'no-session', 'result' => $response));
				die();
			} else {
				
				echo json_encode(array('status' => 'no-session'));
				die();
			}	
					
			break;
		
		case 'cognesys_talk':
			
			
			
			break;
		
		case 'cognesys_stop':
			
			
			
			
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
			var_dump($params);
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
				      "chatHistory":"",
				      "callbackInfo":"' . $data->callback . '",
				      "chatStatus":"' . $data->status . '",
				      "chatBotSummary":""
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


	}
	
	
	
} else {
	
	echo json_encode(array('status' => 'empty vars'));
	die();
	
}

?>