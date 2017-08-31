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
	
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		
	<script src="js/script.js" type="text/javascript"></script>
	<link href="css/fonts.css" media="all" rel="stylesheet" />
	<link href="css/style.css" media="all" rel="stylesheet" />
	
</head>
<body ng-app="chat-bot" ng-controller="chat" id="chat-bot">
	<div class="wrap">
		<div class="headbg"></div>
		<div class="container">
			<div class="row">
				<div class="header col-xs-12">
					<div class="logo"></div>
					<div class="endChat hidden-xs" ng-click="endChat()"></div>
				</div>
			</div>
			<div id="login-view">
				<div class="row">
					<div class="col-xs-12">
						<h1>Willkommen beim DHL Kundensupport</h1>
						<p>damit wir auch lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
						<p>At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
					    	<label for="nameinput">Name</label>
					    	<input type="text" class="form-control" id="nameinput" ng-model="name">
					  	</div>
					</div>  
				</div>
				<div class="row">	
					<div class="col-xs-12">
						<div class="form-group">
					    	<label for="emailinput">Email</label>
					    	<input type="text" class="form-control" id="emailinput" ng-model="email">
					  	</div>  	
					</div>  	
				</div>
				<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
					    	<label for="phoneinput">Telefonnummer</label>
					    	<input type="text" class="form-control" id="phoneinput" ng-model="phone">
					  	</div> 	
			        </div>
			    </div>	
			   	<div class="row">
					<div class="col-xs-12">
						<div class="form-group">
					    	<button class="openChat btn btn-default" ng-click="open_chat()">Start Chat</button>
					  	</div> 	
			        </div>
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
				<div class="row">
					<div class="col-xs-12">
						<h1>Vielen Dank</h1>
						<p>Bitte bewerten Sie noch kurz unseren neuen Service.</p>
					</div>
				</div>
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
				<div class="row">
					<div class="col-xs-12">
						<h1>Derzeit ist kein Live-Agent verfügbar.</h1>
						<p>Bitte geben Sie nachfolgend Ihre Telefonnummer ein, damit wir Sie zeitnah zurückrufen können.</p>
					</div>
				</div>
				
				<input type="text" name="phone" ng-model="callbackphone" value="" size="30"> <button ng-click="sendCallBackRequest()">Rückrufwunsch senden</button>
			</div>

		</div>
	</div>

</body>
</html>