
var savedData = [];

var me = {};
me.avatar = "https://freeiconshop.com/wp-content/uploads/edd/person-flat.png";

var you = {};
you.avatar = "https://cdn4.iconfinder.com/data/icons/free-large-android-icons/512/Android.png";

var agentAvatar = "https://cdn4.iconfinder.com/data/icons/user-avatar-flat-icons/512/User_Avatar-26-512.png";

var chatBot = angular.module('chat-bot', ['base64']);

chatBot.controller('chat', function ($scope, $http, $base64) {
	
	$scope.sessionid = '';
	$scope.url = 'https://lhZepTho1M0xnFVznOsT:GRi78QOuyeHpFh0bC5BA@api.cognesys.de:5679';
	$scope.stopChating = false;
	
	$scope.open_chat = function () {

		if ($scope.email != undefined) {
			savedData.email = $scope.email;
		} else {
			savedData.email = Math.random()+'test@test.de';
		} 
		if ($scope.name != undefined) {
			savedData.name = $scope.name;
		} else {
			savedData.name = 'Max_Mustermann';
		}
		if ($scope.phone != undefined) {
			savedData.phone = $scope.phone;
		} else {
			savedData.phone = '03012345678';
		}
		
		savedData.history = [];
		
		$('#login-view').hide();
		$('#chat-view').show();
	
		$scope.start_cognesys_chat();

	}
	
	$scope.start_cognesys_chat = function () {
		
		$http({
			method: 'GET',
			url: 'start.php',
			headers: {
			    'Accept':'application/json'
			}
			}).then(function success(response) {
				$scope.sessionid = response.data['session-id'];
			}, function error(response){
				
			});
					
	}
	
	$scope.stop_cognesys_chat = function () {

		$http({
			method: 'POST',
			url: 'stop.php',
			data: {'session_id': $scope.sessionid, 'text': $scope.message},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				
				$scope.saveCustomerData();

			}, function error(response){
				
			});
		
	}

	$scope.sendMessage = function () {
		
		if ($scope.message != '' && $scope.message != undefined) {
			savedData.history.push($scope.message);
			
			control_customer = '<li style="width:100%">' +
                        '<div class="msj macro">' +
                        '<div class="avatar"><img class="img-circle" style="width:20px;" src="'+ me.avatar +'" /></div>' +
                        '<div class="text text-l">' +
                        '<p>'+ $scope.message +'</p>' +
                        '</div>' +
                        '</div>' +
                        '</li>';
			
			$('.chat-messages').find('ul').append(control_customer);
			
			$http({
				method: 'POST',
				url: 'talk.php',
				data: {'session_id': $scope.sessionid, 'text': $scope.message},
				headers: {
				    'Accept':'application/json',
				    'Content-Type':'application/json'
				}
				}).then(function success(response) {
					console.log(response);
					
					var text = response.data['text'];
					var status = response.data['status'];
					
					if (status == 'handover') {
						
						$('.chatbutton').hide();
						
						$scope.stop_cognesys_chat();
						$scope.connectLiveAgent();
						
					} 
					 
					control_chatbot = '<li style="width:100%;">' +
                        '<div class="msj-rta macro">' +
                        '<div class="text text-r">' +
                        '<p>'+response.data['text']+'</p>' +
                        '</div>' +
                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+you.avatar+'" /></div>' +
                        '</li>';
					
					$('.chat-messages').find('ul').append(control_chatbot);
					savedData.history.push(response.data['text']);
					
					if (status == 'chat-topic-finished') {
						
						$scope.stop_cognesys_chat();
						
						control_chatbot = '<li style="width:100%;">' +
			                        '<div class="msj-rta macro">' +
			                        '<div class="text text-r">' +
			                        '<p>Chat is ended</p>' + 
			                        '</div>' +
			                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+you.avatar+'" /></div>' +
			                        '</li>';
						$('.chat-messages').find('ul').append(control_chatbot);
						
						setTimeout(function(){
							$('#chat-view').hide();
							$('#survey-view').show();
						}, 5000);
						
					}
					
					$('.chat-input .input').val('');
					
				}, function error(response){
					
				});
		}
	}
	
	$scope.sendLiveMessage = function () {
		
		if ($scope.message != '') {
			
			control_customer = '<li style="width:100%">' +
                        '<div class="msj macro">' +
                        '<div class="avatar"><img class="img-circle" style="width:20px;" src="'+ me.avatar +'" /></div>' +
                        '<div class="text text-l">' +
                        '<p>'+ $scope.message +'</p>' +
                        '</div>' +
                        '</div>' +
                        '</li>';
			$('.chat-messages').find('ul').append(control_customer);
			$('.chat-input .input').val('');
			
			$http({
				method: 'POST',
				url: 'liveagent_talk.php',
				data: {'text': $scope.message},
				headers: {
				    'Accept':'application/json',
				    'Content-Type':'application/json'
				}
				}).then(function success(response) {
					
					var text = response.data['text'];
					var chat = response.data['chat'];
					
					console.log(text); 
					if (text != '' && text != undefined) {
						
						$.each(text, function(key, value) {
							
							control_agent = '<li style="width:100%;">' +
			                        '<div class="msj-rta macro">' +
			                        '<div class="text text-r">' +
			                        '<p>' + value + '</p>' + 
			                        '</div>' +
			                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+agentAvatar+'" /></div>' +
			                        '</li>';
							
							$('.chat-messages').find('ul').append(control_agent);
						});
	
					
					}
					if (chat == 'stop') {
						
						$('.livechatbutton').hide();
						
						control_agent = '<li style="width:100%;">' +
			                        '<div class="msj-rta macro">' +
			                        '<div class="text text-r">' +
			                        '<p>Chat is ended</p>' + 
			                        '</div>' +
			                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+agentAvatar+'" /></div>' +
			                        '</li>';
							
						$('.chat-messages').find('ul').append(control_agent);
						
						setTimeout(function(){
							$('#chat-view').hide();
							$('#survey-view').show();
						}, 5000);
	
					}
	
				}, function error(response){
					
				});

			
		}
		
	}	
	
	$scope.connectLiveAgent = function () {
		
		$http({
			method: 'POST',
			url: 'liveagent.php',
			data: {'session_id': $scope.sessionid, 'text': $scope.message, 'history': savedData.history},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				
				var agent = response.data['agent'];
				console.log(agent);
				if (agent) {
					
					control_agent = '<li style="width:100%;">' +
		                        '<div class="msj-rta macro">' +
		                        '<div class="text text-r">' +
		                        '<p>Connect with Live-Agent<br />' + 
		                        'How can I help you</p>' + 
		                        '</div>' +
		                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+agentAvatar+'" /></div>' +
		                        '</li>';
		                        
					$('.chat-messages').find('ul').append(control_agent);
					$('.chatbutton').hide();
					$('.livechatbutton').show();
					
					setTimeout(function() {$scope.readLiveMessage();}, 10000);
					
				} else {
					
					$('#chat-view').hide();
					$('#callback-view').show();
					
				}

			}, function error(response){
				
			});
		
	}
	
	$scope.readLiveMessage = function() {
		
		$http({
			method: 'POST',
			url: 'liveagent_check.php',
			data: {},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				
				var text = response.data['text'];
				var chat = response.data['chat'];
				
				console.log(text); 
				if (text != '' && text != undefined) {
					
					$.each(text, function(key, value) {
						
						control_agent = '<li style="width:100%;">' +
		                        '<div class="msj-rta macro">' +
		                        '<div class="text text-r">' +
		                        '<p>' + value + '</p>' + 
		                        '</div>' +
		                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+agentAvatar+'" /></div>' +
		                        '</li>';
						
						$('.chat-messages').find('ul').append(control_agent);
					});

				
				}
				if (chat == 'stop') {
					
					$('.livechatbutton').hide();
					
					control_agent = '<li style="width:100%;">' +
		                        '<div class="msj-rta macro">' +
		                        '<div class="text text-r">' +
		                        '<p>Chat is ended</p>' + 
		                        '</div>' +
		                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+agentAvatar+'" /></div>' +
		                        '</li>';
						
					$('.chat-messages').find('ul').append(control_agent);
					
					setTimeout(function(){
						$('#chat-view').hide();
						$('#survey-view').show();
					}, 5000);

				}
				
			}, function error(response){
				
			});
		
		setTimeout(function() {$scope.readLiveMessage();}, 10000);
	}

	$scope.saveCustomerData = function () {

		$http({
			method: 'POST',
			url: 'connect.php',
			data: {'session_id': $scope.sessionid, 'phone': savedData.phone, 'email': savedData.email, 'name': savedData.name},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				console.log(response);
				
			}, function error(response){
				
			});
	}
	
	$scope.sendCallBackRequest =  function () {
		
		$http({
			method: 'POST',
			url: 'callback.php',
			data: {'phone': $scope.callbackphone},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				console.log(response);
				
				$('#callback-view').hide();
				$('#survey-view').show();
				
			}, function error(response){
				
			});
	}
	
});
