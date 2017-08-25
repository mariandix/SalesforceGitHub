<?php





?>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>DHL - ChatBot</title>
	<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/angular.js/1.3.3/angular-route.min.js"></script>  
	<script src="//cdnjs.cloudflare.com/ajax/libs/angular-base64/2.0.5/angular-base64.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>  
	<script src="js/script.js" type="text/javascript"></script>
	<link href="css/style.css" media="all" rel="stylesheet" />
</head>
<body ng-app="chat-bot" ng-controller="chat" id="chat-bot">
	<div class="header">
		<img src="img/1502890870.png" width="200">
	</div>
	
	<div class="wrap">
		<div id="login-view">
	        <div id="login">
	        	<div class="error_msg" style="display:none;">Bitte überprüfen Sie Ihre Eingaben</div>
	        	<label>Name</label>
	            <input type="text" size="30" ng-model="name"><br>
	            <label>Email</label>
	            <input type="email" size="30" ng-model="email"><br>
	            <label>Phone</label>
	            <input type="text" size="30" ng-model="phone"><br>
	            
	            <button ng-click="open_chat()">Start Chat</button><br>
	        </div>
		</div>
		
		<div id="chat-view" style="display: none;">
			<div id="chat">
				<div class="chat-messages">
					<ul>
						
					</ul>
				</div>
				<div class="chat-input">
					<input class="input" type="text" ng-model="message" size="30">
					<button ng-click="sendMessage()" class="chatbutton">Send Message</button>
					<button ng-click="sendLiveMessage()" class="livechatbutton" style="display:none">Send Message</button>
				</div>
				<button ng-click="endChat()" class="endChat">X</button>
			</div>
		</div>
		
		<div id="survey-view" style="display: none;">
			<h1>Vielen Dank</h1>
			
			<h3>Bitte bewerten Sie noch kurz unseren neuen Service.</h3>
			
			<table>
				<tr>
					<td>1</td>
					<td>2</td>
					<td>3</td>
					<td>4</td>
					<td>5</td>
				</tr>
				<tr>
					<td><img src="https://cdn2.iconfinder.com/data/icons/diagona/icon/16/031.png"></td>
					<td><img src="https://cdn2.iconfinder.com/data/icons/diagona/icon/16/031.png"></td>
					<td><img src="https://cdn2.iconfinder.com/data/icons/diagona/icon/16/031.png"></td>
					<td><img src="https://cdn2.iconfinder.com/data/icons/diagona/icon/16/031.png"></td>
					<td><img src="https://cdn2.iconfinder.com/data/icons/diagona/icon/16/031.png"></td>
				</tr>
			</table>
		</div>
		
		<div id="callback-view" style="display: none;">
			<h1>Derzeit ist kein Live-Agent verfügbar.</h1>
			
			<h3>Bitte geben Sie nachfolgend Ihre Telefonnummer ein, damit wir Sie zeitnah zurückrufen können.</h3>
			
			<input type="text" name="phone" ng-model="callbackphone" value="" size="30"> <button ng-click="sendCallBackRequest()">Rückrufwunsch senden</button>
		</div>	
</body>
</html>