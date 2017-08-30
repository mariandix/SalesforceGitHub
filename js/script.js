

var me = {};
me.avatar = "https://freeiconshop.com/wp-content/uploads/edd/person-flat.png";

var you = {};
you.avatar = "https://cdn4.iconfinder.com/data/icons/free-large-android-icons/512/Android.png";

var agent = {};
agent.avatar = "https://cdn4.iconfinder.com/data/icons/user-avatar-flat-icons/512/User_Avatar-26-512.png";

var REGEX_EMAIL = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;    
var savedData = [];	

var chatBot = angular.module('chat-bot', ['base64']);

var messageQueue = '';
var activeChatEndEvent = '';

chatBot.controller('chat', function ($scope, $http, $base64, $q) {

	$scope.sessionid = '';
	$scope.congesysStop = false;
	$scope.chatStop = false;
	$scope.messageCount = 1;
	$scope.timer;
	$scope.inputTimer;
	$scope.fullMessage = '';
	$scope.messageQueue = 'none';
	
	$scope.open_chat = function () {
		
		var error = false;
		var reg = REGEX_EMAIL;
		
		$('input[type="email"]').removeClass('error');
		$('.error_msg').hide();
		
		if ($scope.email == undefined) {

        	savedData.email = Math.random()+'test@test.de';
        } else if (!reg.test($scope.email)) {

            error = true;
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
			
			$('input[ng-model="email"]').addClass('error');
			$('.error_msg').show();
		}

	}
	
	// cognesys beginn
	$scope.start_cognesys_chat = function () {
		
		activeChatEndEvent = 'sendCustomerData';
		
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
					savedData.sessionid = $scope.sessionid = resp['session-id'];
console.log('start');					
console.log(response.data['result']);
					$('#login-view').hide();
					$('#chat-view').show();
					
					$('.chat-messages').find('ul').append($scope.entryChatbot('Hallo<br>Wie kann ich Ihnen helfen?'));

					$('.input').keypress(function(event) {
						var oldTimer = ($scope.inputTimer != undefined);
						clearTimeout($scope.inputTimer);
						
						if (event.which == 13) {
							
							if ($scope.message != '' && $scope.message != undefined) {
								savedData.history.push({'sequenceNumber':$scope.messageCount, 'Type': 'Q', 'message': $scope.message});
								
								$scope.fullMessage = $scope.fullMessage + " " + $scope.message + "\n";
								
								$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
								$('.chat-input .input').val('');
								
								console.log('enter');
								
								$scope.inputTimer = setTimeout(function(){
									$scope.sendFullMessage();
								}, 2000);
							}
						} else {
							
							if (oldTimer) {
								$scope.inputTimer = setTimeout(function(){
									$scope.sendFullMessage();
								}, 3000);
							}
						}
						
					});

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
	
	$scope.sendFullMessage = function () {
		
		clearTimeout($scope.inputTimer);
		$http({
			method: 'POST',
			url: 'api.php',
			data: {'type': 'cognesys_talk', 'session_id': $scope.sessionid, 'text': $scope.fullMessage, 'sequence': $scope.messageCount },
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				
				var resp = JSON.parse(response.data['result'].result);
console.log('talk full');					
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
					$scope.chatStop = true;
					
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
				
		$scope.messageCount++;
		
		$scope.fullMessage = '';
	}
	
	$scope.sendMessage = function () {
		
		if ($scope.message != '' && $scope.message != undefined) {
			
			clearTimeout($scope.inputTimer);
			savedData.history.push({'sequenceNumber':$scope.messageCount, 'Type': 'Q', 'message': $scope.message});
			
			$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
			$('.chat-input .input').val('');
			var message = $scope.fullMessage + " " + $scope.message;
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
						$scope.chatStop = true;
						
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
				
		$scope.messageCount++;
		$scope.fullMessage = '';
		}
	}	

	$scope.stop_cognesys_chat = function (saveCustomerData) {
		
		$scope.congesysStop = true;
		
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
				//savedData.summary = resp.summary;
				savedData.endTime = resp.timestamp;
				savedData.startTime = resp.started;
				
				if (saveCustomerData) {
					$scope.saveCustomerData();
				}
				
				$('.input').unbind('keypress');
				$scope.inputTimer = undefined;
				$scope.fullMessage = '';

			}, function error(response){
				
			});
		
	}	


	// cognesys end
	
	// live agent beginn
	
	$scope.connectLiveAgent = function () {
		
		activeChatEndEvent = 'none';
		
		$http({
			method: 'POST',
			url: 'api.php',
			data: {
				'type': 'liveagent_init', 
				'history': savedData.history, 
				'name': savedData.name, 
				'email': savedData.email, 
				'phone': savedData.phone,
				'userAgent': navigator.userAgent,
				'language': navigator.language,
				'width': window.innerWidth,
				'height': window.innerHeight
			},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				
console.log('connect live agent');				
console.log(response.data);
				
				var agent = response.data['agent'];

				if (agent) {
					
					activeChatEndEvent = 'liveagent_stop';
					
					$('.chat-messages').find('ul').append($scope.entryAgent('Connect with Live-Agent<br />How can I help you'));
					
					$('.chatbutton').hide();
					$('.livechatbutton').show();

					$scope.timer = setTimeout(function() {$scope.sendAndCheckMessages();}, 2000);
					
					$('.input').keypress(function(event) {
						var oldTimer = ($scope.inputTimer != undefined);
						clearTimeout($scope.inputTimer);
						
						if (event.which == 13) {
							
							if ($scope.message != '' && $scope.message != undefined) {

								$scope.fullMessage = $scope.fullMessage + " " + $scope.message + '\n';

								$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
								$('.chat-input .input').val('');
								$scope.message = '';
								
								console.log('enter');
								console.log($scope.fullMessage);
								
								$scope.inputTimer = setTimeout(function(){
									$scope.createFullLiveMessage($scope.fullMessage);
								}, 2000);
							}
						} else {
							
							if (oldTimer) {
								$scope.inputTimer = setTimeout(function(){
									$scope.createFullLiveMessage($scope.fullMessage);
								}, 3000);
							}
						}
						
					});
					
				} else {
					
					savedData.status = 'Live Agent n/a';
					$scope.saveCustomerData();
					
					$('#chat-view').hide();
					$('#callback-view').show();
					
				}
				

			}, function error(response){
				
			});
	
	}
	
	$scope.sendAndCheckMessages = function() {
		
		console.log('Q ' + messageQueue);
		console.log('F ' + $scope.fullMessage);
		console.log('M ' + $scope.message);
		
		$http({
			method: 'POST',
			url: 'api.php',
			data: {'type' : 'liveagent_checkandtalk', 'text': messageQueue},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				messageQueue = '';
				
				var text = response.data['text'];
				var chat = response.data['chat'];
					
console.log('send check and talk');				
console.log(response.data);
				if (text != '' && text != undefined) {
					
					$.each(text, function(key, value) {

						$('.chat-messages').find('ul').append($scope.entryAgent(value));
					});
				
				}
				
				if (chat == 'stop') {

					$('.livechatbutton').hide();

					$('.chat-messages').find('ul').append($scope.entryAgent('Chat is ended'));
					
					$scope.chatStop = true;
					clearTimeout($scope.timer);
					
					setTimeout(function(){
						$('#chat-view').hide();
						$('#survey-view').show();
					}, 5000);
					
				} else {
					
					$scope.timer = setTimeout(function() {$scope.sendAndCheckMessages();}, 2000);
				}

			}, function error(response){
				
			});
			
	}
	
	$scope.createFullLiveMessage = function(fullMessage) {
		console.log('change message to queue');
		messageQueue = fullMessage;
		console.log('cf ' + fullMessage);
		//$scope.fullMessage = '';
	}
	
	$scope.sendLiveMessage = function () {
		
		if ($scope.message != '') {
		
			$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
			$('.chat-input .input').val('');
			
			messageQueue = messageQueue + " " + $scope.fullMessage + " " + $scope.message + '\n';
			//$scope.message = '';
			//$scope.fullMessage = '';
		}
		console.log('slm mq ' + messageQueue);
	}	
	
	$scope.liveagent_stop = function() {
		
		$http({
			method: 'POST',
			url: 'api.php',
			data: {
				'type': 'liveagent_stop'
			},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				
console.log('stop live agent');				
console.log(response.data);
				$scope.chatStop = true;
			}, function error(response){
				
			});
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
				'endTime': savedData.endTime,
				'startTime': savedData.startTime
			},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
console.log('save data');				
console.log(response.data);
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
	
	$scope.endChat = function () {
		
		if (confirm("Chat beenden?")) {
			
			$scope.endChatOnUnload();
			
		}
		
	}
	
	$scope.endChatOnUnload = function () {
			
		if ($scope.sessionid == '') {
			// first view - no chat to stop
			// do nothing
			console.log('end do nothing');
		} else if (!$scope.congesysStop) {
			
			savedData.chatstatus = 'Aborted';
			$scope.stop_cognesys_chat(true);
			
			console.log('end stop cognesys');
		} else {
			
			$scope.chatStop = true;
			clearTimeout($scope.timer);

			$scope.liveagent_stop();
			console.log('end stop live agent');
		}
		
		setTimeout(function(){
			$('#chat-view').hide();
			$('#survey-view').show();
		}, 2000);
		
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


window.onbeforeunload = function (event) {

    console.log('beforeunload2');
	/*$.ajax({
     type: 'POST',
     data: {beforeunload: true},
     url: 'api.php'
   });
    */

};
window.onunload = function () {
	console.log('unload');
	
	$.ajax({
     type: 'POST',
     async: false,
     data: {
		'type': activeChatEndEvent, 
		'session_id': savedData.sessionid, 
		'email': savedData.email, 
		'name': savedData.name, 
		'phone': savedData.phone, 
		'callback': savedData.callback, 
		'status': savedData.chatstatus,
		'tonality': savedData.tonality,
		'chathistory': savedData.history,
		'summary': savedData.summary,
		'endTime': savedData.endTime,
		'startTime': savedData.startTime
	},
     url: 'api.php'
   }).done(function(data) {
	  console.log(data);
	});

}


