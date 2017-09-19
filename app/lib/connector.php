<?php


class connector {
	
	public $endpoint;
	
	public $method;
	
	public $postfields;
	
	public $requestHeader;
	
    public function __construct() {
       
	}	
	
	public function setEndpoint ($endpoint) {
		
		$this->endpoint = $endpoint;
		
		return $this;
	}
	
	public function getEndpoint () {
		
		return $this->endpoint;
	}
	
	public function setRequestMethod($method = 'POST') {
		
		$this->method = $method;
		
		return $this;
	}
	
	public function getRequestMethod () {
		
		return $this->method;
	}
	
	public function setPostfields ($fields = '') {
		
		$this->postfields = $fields;
		
		return $this;
	}
	
	public function getPostfields() {
		
		return $this->postfields;
	}
	
	public function setRequestHeader ($header = array()) {
		
		$this->requestHeader = $header;
		return $this;
	}
	
	public function getRequestHeader () {
		
		return $this->requestHeader;
	}
	
	public function sendRequest () {
		
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $this->getEndpoint());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		
		
		if ($this->getRequestHeader() != '') {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeader());
		}
		
		if ($this->method == 'POST') {
			
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getPostfields());
		} elseif ($this->method == 'GET') {
			
			curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		} else {
			return false;
		}
		
		$result = curl_exec($ch);
		$info = curl_getinfo($ch);

		$error = curl_error($ch);
		curl_close($ch);
		
		return array('result' => $result, 'error' => $error);
	}
}

?>