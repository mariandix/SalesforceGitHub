

var me = {};
me.avatar = "https://freeiconshop.com/wp-content/uploads/edd/person-flat.png";

var you = {};
you.avatar = "https://cdn4.iconfinder.com/data/icons/free-large-android-icons/512/Android.png";

var agent = {};
agent.avatar = "https://cdn4.iconfinder.com/data/icons/user-avatar-flat-icons/512/User_Avatar-26-512.png";

var REGEX_EMAIL = /^[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?$/;

var savedData = [];	

var chatBot = angular.module('chat-bot', ['base64']);

chatBot.controller('chat', function ($scope, $http, $base64) {

	$scope.sessionid = '';
	$scope.stopChating = false;
	$scope.messageCount = 0;
	$scope.timer;
	
	$scope.open_chat = function () {
		
		var error = false;
		var reg = REGEX_EMAIL;
		
		$('input[type="email"]').removeClass('error');
		$('.error_msg').hide();
		
		if ($scope.email != undefined && !reg.test($scope.email)) {
            
            error = true;
        } else if ($scope.email == undefined) {
        	
        	savedData.email = Math.random()+'test@test.de';
        } else {
        	
            savedData.email = $scope.email;
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

		if (!error) {
			savedData.history = [];
			savedData.callback = '';
			savedData.chatstatus = '';
			savedData.summary = '';
			savedData.tonality = '';
			savedData.endTime = '';
			savedData.startTime = '';
				
			$scope.start_cognesys_chat();
		} else {
			
			$('input[type="email"]').addClass('error');
			$('.error_msg').show();
		}

	}
	
	// cognesys beginn
	$scope.start_cognesys_chat = function () {
		
		$http({
			method: 'POST',
			url: 'api.php',
			data: {'type': 'cognesys_start'},
			headers: {
			    'Accept':'application/json'
			}
			}).then(function success(response) {
				
				if (response.data['status'] == 'ok') {
					var resp = JSON.parse(response.data['result'].result);
					$scope.sessionid = resp['session-id'];
console.log('start');					
console.log(response.data['result']);
					$('#login-view').hide();
					$('#chat-view').show();
					
					$('.chat-messages').find('ul').append($scope.entryChatbot('Hallo<br>Wie kann ich Ihnen helfen?'));
			
				} else {
					
					$('#login-view').hide();
					$('#callback-view').show();
					
					savedData.chatstatus = response.data['status'];
					
				}
			}, function error(response){
				
				$('#login-view').hide();
				$('#callback-view').show();
				
				$scope.saveCustomerData();
				
			});
					
	}	
	
	$scope.sendMessage = function () {
		
		if ($scope.message != '' && $scope.message != undefined) {
			$scope.messageCount++;
			savedData.history.push({'sequenceNumber':$scope.messageCount, 'Type': 'Q', 'message': $scope.message});
			
			$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
			$('.chat-input .input').val('');
			var message = $scope.message;
			$scope.message = '';
			
			$http({
				method: 'POST',
				url: 'api.php',
				data: {'type': 'cognesys_talk', 'session_id': $scope.sessionid, 'text': message, 'sequence': $scope.messageCount },
				headers: {
				    'Accept':'application/json',
				    'Content-Type':'application/json'
				}
				}).then(function success(response) {
					
					var resp = JSON.parse(response.data['result'].result);
console.log('talk');					
console.log(response.data);					
					var text = resp['text'];
					var status = resp['status'];
					var cnt = resp['sequence-id'];
					savedData.summary = resp['summary'];
					
					savedData.history.push({'sequenceNumber':resp['sequence-id'],'Type': 'A', 'message': resp['text']});
					
					if (status == 'handover') {
						
						savedData.chatstatus = status;
						
						$('.chat-messages').find('ul').append($scope.entryChatbot(text));
						
						$scope.stop_cognesys_chat(false);
						$scope.connectLiveAgent();

					} else if (status == 'chat-topic-finished') {
						
						savedData.chatstatus = status;
						
						$scope.stop_cognesys_chat(true);
						
						$('.chat-messages').find('ul').append($scope.entryChatbot(text));
						$('.chat-messages').find('ul').append($scope.entryChatbot('Chat wurde beendet. Vielen Dank und einen schönen Tag.'));
						
						setTimeout(function(){
							$('#chat-view').hide();
							$('#survey-view').show();
						}, 7500);
						
					} else {
						
						
						$('.chat-messages').find('ul').append($scope.entryChatbot(text));
					}
					
				}, function error(response){
					
				});
		
		}
	}	
	
	$scope.showCustomerMessage = function (text) {
		
		if (text != '' && text != undefined) {
			
			$('.chat-messages').find('ul').append($scope.entryCustomer(text));
			$('.chat-input .input').val('');
			
		
		}
	}	
	
	$scope.stop_cognesys_chat = function (saveCustomerData) {

		$http({
			method: 'POST',
			url: 'api.php',
			data: {'type': 'cognesys_stop', 'session_id': $scope.sessionid},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
console.log('stop');				
console.log(response.data);
				var resp = JSON.parse(response.data['result'].result);
				console.log(resp);
				savedData.tonality = resp.tonality;
				savedData.summary = resp.summary;
				savedData.endTime = resp.timestamp;
				savedData.startTime = resp.started;
				
				if (saveCustomerData) {
					$scope.saveCustomerData();
				}

			}, function error(response){
				
			});
		
	}	


	// cognesys end
	
	// live agent beginn
	
	$scope.connectLiveAgent = function () {
	
		$http({
			method: 'POST',
			url: 'api.php',
			data: {'type': 'liveagent_init', 'history': savedData.history, 'name': savedData.name},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				
				console.log(response);
				
				var agent = response.data['agent'];

				if (agent) {
					
					$('.chat-messages').find('ul').append($scope.entryAgent('Connect with Live-Agent<br />How can I help you'));
					
					$('.chatbutton').hide();
					$('.livechatbutton').show();

					$scope.timer = setTimeout(function() {$scope.readLiveMessage();}, 10000);
					
				} else {
					
					savedData.status = 'Live Agent n/a';
					$scope.saveCustomerData();
					
					$('#chat-view').hide();
					$('#survey-view').show();
					
				}
				

			}, function error(response){
				
			});
	
	}

	$scope.sendLiveMessage = function () {
		
		if ($scope.message != '') {

			$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
			$('.chat-input .input').val('');
			var message = $scope.message;
			$scope.message = '';
			
			$http({
				method: 'POST',
				url: 'api.php',
				data: {'type' : 'liveagent_talk', 'text': message},
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

							$('.chat-messages').find('ul').append($scope.entryAgent(value));
						});
					
					}
					if (chat == 'stop') {
						
						$('.livechatbutton').hide();

						$('.chat-messages').find('ul').append($scope.entryAgent('Chat is ended'));
						
						setTimeout(function(){
							$('#chat-view').hide();
							$('#survey-view').show();
						}, 5000);
	
					}
	
				}, function error(response){
					
				});

			
		}
		
	}

	
	$scope.readLiveMessage = function() {
		
		$http({
			method: 'POST',
			url: 'api.php',
			data: {'type': 'liveagent_check'},
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
						
						$('.chat-messages').find('ul').append($scope.entryAgent(value));
					});
				
				}
				if (chat == 'stop') {
					
					$('.livechatbutton').hide();
						
					$('.chat-messages').find('ul').append($scope.entryAgent('Chat is ended'));
					
					setTimeout(function(){
						$('#chat-view').hide();
						$('#survey-view').show();
					}, 5000);

				}
				
			}, function error(response){
				
			});
		
		setTimeout(function() {$scope.readLiveMessage();}, 10000);
	}	
	
	
	// live agent end
	
	// generall
	$scope.saveCustomerData = function () {

		$http({
			method: 'POST',
			url: 'api.php',
			data: {
				'type': 'sendCustomerData', 
				'session_id': $scope.sessionid, 
				'email': savedData.email, 
				'name': savedData.name, 
				'phone': savedData.phone, 
				'callback': savedData.callback, 
				'status': savedData.chatstatus,
				'tonality': savedData.tonality,
				'chathistory': savedData.history,
				'summary': savedData.summary,
			},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				console.log(response);
			}, function error(response){
				console.log(response);
			});
	}
	
	$scope.sendCallBackRequest =  function () {
		
		savedData.callback = $scope.callbackphone;
		
		$scope.saveCustomerData();
		
		$('#callback-view').hide();
		$('#survey-view').show();
		
	}
	
	$scope.entryChatbot = function(text) {
		
		control_chatbot = '<li style="width:100%;">' +
	                        '<div class="msj-rta macro">' +
	                        '<div class="text text-r">' +
	                        '<p>'+text+'</p>' +
	                        '</div>' +
	                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+you.avatar+'" /></div>' +
	                        '</li>';
		
		return control_chatbot;
	} 
	
	$scope.entryCustomer = function(text) {
		
		control_customer = '<li style="width:100%">' +
	                        '<div class="msj macro">' +
	                        '<div class="avatar"><img class="img-circle" style="width:20px;" src="'+ me.avatar +'" /></div>' +
	                        '<div class="text text-l">' +
	                        '<p>'+ text +'</p>' +
	                        '</div>' +
	                        '</div>' +
	                        '</li>';
		
		return control_customer;
	} 
	
	$scope.entryAgent = function(text) {
		
		control_agent = '<li style="width:100%;">' +
                        '<div class="msj-rta macro">' +
                        '<div class="text text-r">' +
                        '<p>' + text + '</p>' + 
                        '</div>' +
                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+agent.avatar+'" /></div>' +
                        '</li>';
		
		return control_agent;
	}		

});