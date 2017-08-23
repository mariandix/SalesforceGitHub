<?php

include('app/config/config.inc.php');

header ('Content-type: application/json');
$data = json_decode(file_get_contents("php://input")); 

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
			} else {
				
				echo json_encode(array('status' => 'no-session'));
			}	
					
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
			
			$response = $con->sendRequest();
			
			var_dump($response);
			
			break;
	}
	
	
	
} else {
	
	echo json_encode(array('status' => 'empty vars'));
	
}

?>