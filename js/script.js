

var me = {};
me.avatar = "img/icon_user.svg";

var you = {};
you.avatar = "img/icon_chatbot.svg";

var agent = {};
agent.avatar = "img/icon_liveagent.svg";

var REGEX_EMAIL = /^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;    
var savedData = [];	

var chatBot = angular.module('chat-bot', ['base64']);

var messageQueue = '';
var activeChatEndEvent = 'none';
var sendOnUnload = true;
var token = '';
var cnt = 0;
var additional = false;

chatBot.controller('chat', function ($scope, $http, $base64, $q) {

	$scope.sessionid = '';
	$scope.congesysStop = false;
	$scope.chatStop = false;
	$scope.messageCount = 1;
	$scope.timer;
	$scope.inputTimer;
	$scope.fullMessage = '';
	$scope.messageQueue = '';
	
	$scope.open_chat = function () {
		
		$('.openChat').attr('disabled', true);
		
		var error = false;
		var reg = REGEX_EMAIL;
		
		$('input[ng-model="email"]').removeClass('error');
		$('.error_msg').hide();
		
		if ($scope.email == undefined || $scope.email == '') {

        	savedData.email = Math.random()+'test@test.de';
        } else if (!reg.test($scope.email)) {

            error = true;
        } else {
        	
            savedData.email = $scope.email;
        }
		
		if ($scope.name != undefined) {
			savedData.name = $scope.name;
		} else {
			savedData.name = default_lastname;
		}
		if ($scope.firstname != undefined) {
			savedData.firstname = $scope.firstname;
		} else {
			savedData.firstname = default_firstname;
		}
		if ($scope.phone != undefined) {
			savedData.phone = $scope.phone;
		} else {
			savedData.phone = '';
		}
		if ($scope.salutation != undefined) {
			savedData.salutation = $scope.salutation;
		} else {
			savedData.salutation = default_salutation;
		}

		if (!error) {
			savedData.history = [];
			savedData.callback = '';
			savedData.chatstatus = '';
			savedData.summary = [];
			savedData.tonality = '';
			savedData.endTime = '';
			savedData.startTime = '';
			savedData.callbackId = '';
			savedData.messageId = '';
				
			$scope.start_cognesys_chat();
		} else {
			$('.openChat').attr('disabled', false);
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

					$('#login-view').hide();
					$('#chat-view').show();
					$('.endChat').show();
					$('.bottomClose').hide();
					
					$('.chat-messages').find('ul').append($scope.entryChatbot(cognesys_welcome_message));
					$scope.chatScrollDown();
  		
					$('.input').keypress(function(event) {
						
						clearTimeout($scope.inputTimer);
						var oldTimer = ($scope.inputTimer != undefined);
						if (event.which == 13) {
						
							if ($scope.message != '' && $scope.message != undefined) {
								savedData.history.push({'sequenceNumber':$scope.messageCount, 'Type': 'Q', 'message': addslashes($scope.message)});
								
								$scope.fullMessage = $scope.fullMessage + " " + $scope.message.replace("'","&apos;") + "\n";
								
								$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
								$scope.chatScrollDown();
								$('.chat-input .input').val('');
								$scope.message = '';
								
								$scope.inputTimer = setTimeout(function(){
									$scope.sendFullMessage();
								}, 2000);
							}
						} else {
							if (oldTimer) {
								$scope.inputTimer = setTimeout(function(){
									if ($scope.fullMessage != '') {
										$scope.sendFullMessage();
									}
								}, 5000);
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
			data: {'type': 'cognesys_talk', 'session_id': $scope.sessionid, 'text': addslashes($scope.fullMessage), 'sequence': $scope.messageCount },
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
				
				var resp = JSON.parse(response.data['result'].result);
				var text = resp['text'];
				var status = resp['status'];
				var cnt = resp['sequence-id'];
				
				if (resp['summary'] != '') {
					savedData.summary.push(resp['summary']);
				}
				
				savedData.history.push({'sequenceNumber':resp['sequence-id'],'Type': 'A', 'message': resp['text']});
				
				if (status == 'handover') {
					
					savedData.chatstatus = status;
					
					$('.chat-messages').find('ul').append($scope.entryChatbot(text));
					$scope.chatScrollDown();
					$('.chatbutton').hide();
					$('.input').unbind('keypress');
					
					$scope.stop_cognesys_chat(false, true);

				} else if (status == 'chat-topic-finished') {
					
					savedData.chatstatus = 'ChatResolved';
					$scope.chatStop = true;
					
					$('.chatbutton').hide();
					$('.input').unbind('keypress');
					
					$scope.stop_cognesys_chat(true, false);
					
					$('.chat-messages').find('ul').append($scope.entryChatbot(text));
					$scope.chatScrollDown();
					$('.chat-messages').find('ul').append($scope.entryChatbot(cognesys_chat_end_message));
					$scope.chatScrollDown();
					$('.chat-messages').find('ul').append($scope.entryChatbot(cognesys_chat_end_goodbye_message));
					$scope.chatScrollDown();
					
					$('.inside-link-survey').bind('click', function(e) {
						
						$scope.showSurveyPage();
					});
					
				} else {
					
					$('.chat-messages').find('ul').append($scope.entryChatbot(text));
					$scope.chatScrollDown();
				}
				
			}, function error(response){
				
			});
				
		$scope.messageCount++;
		
		$scope.fullMessage = '';
	}
	
	$scope.sendMessage = function () {
		
		if ($scope.message != '' && $scope.message != undefined) {
			
			clearTimeout($scope.inputTimer);
			savedData.history.push({'sequenceNumber':$scope.messageCount, 'Type': 'Q', 'message': addslashes($scope.message)});
			
			$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
			$scope.chatScrollDown();
			$('.chat-input .input').val('');
			var message = $scope.fullMessage + " " + addslashes($scope.message.replace("'","&apos;"));
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
					var text = resp['text'];
					var status = resp['status'];
					var cnt = resp['sequence-id'];
					if (text != undefined) {
					
						if (resp['summary'] != '') {
							savedData.summary.push(resp['summary']);
						}
						if (savedData.chatstatus != 'handover') {
							savedData.history.push({'sequenceNumber':resp['sequence-id'],'Type': 'A', 'message': resp['text']});
						}
						if (status == 'handover') {
							
							if (savedData.chatstatus != 'handover') {
								savedData.chatstatus = status;
								
								$('.chat-messages').find('ul').append($scope.entryChatbot(text));
								$scope.chatScrollDown();
								
								$scope.stop_cognesys_chat(false, true);
								
							}
						} else if (status == 'chat-topic-finished') {
							
							savedData.chatstatus = 'ChatResolved';
							$scope.chatStop = true;
							
							$('.chatbutton').hide();
							$('.input').unbind('keypress');
					
							$scope.stop_cognesys_chat(true, false);
							
							$('.chat-messages').find('ul').append($scope.entryChatbot(text));
							$scope.chatScrollDown();
							$('.chat-messages').find('ul').append($scope.entryChatbot(cognesys_chat_end_message));
							$scope.chatScrollDown();
							$('.chat-messages').find('ul').append($scope.entryChatbot(cognesys_chat_end_goodbye_message));
							$scope.chatScrollDown();
							
							$('.inside-link-survey').bind('click', function(e) {
								
								$scope.showSurveyPage();
							});
							
						} else {
							
							
							$('.chat-messages').find('ul').append($scope.entryChatbot(text));
							$scope.chatScrollDown();
						}
					}
				}, function error(response){
					
				});
				
		$scope.messageCount++;
		$scope.fullMessage = '';
		}
	}	

	$scope.stop_cognesys_chat = function (saveCustomerData, initLiveAgent) {
		
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

				var resp = JSON.parse(response.data['result'].result);

				savedData.tonality = resp.tonality;
				//savedData.summary = resp.summary;
				savedData.endTime = resp.timestamp;
				savedData.startTime = resp.started;
				
				if (initLiveAgent) {
					$scope.connectLiveAgent();
				}
				
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
				'firstname': savedData.firstname,
				'email': savedData.email, 
				'phone': savedData.phone,
				'salutation': savedData.salutation,
				'userAgent': navigator.userAgent,
				'language': navigator.language,
				'width': window.innerWidth,
				'height': window.innerHeight,
				'tonality': savedData.tonality
			},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {

				var agent = response.data['agent'];
				token = response.data['token'];
				savedData.callbackId = '';

				if (agent) {
					
					activeChatEndEvent = 'liveagent_stop';
					
					$('.chat-messages').find('ul').append($scope.entryChatbot(live_agent_connect));
					$scope.chatScrollDown();
					
					$('.chatbutton').hide();
					
					$('.chat-messages').find('ul').append('<li class="connect text"><p><img src="img/CircularProgressAnimationG.gif" width="32"></p><p><a class="inside-link-aborted">' + live_agent_aborted + '</a></p></li>');
					$scope.chatScrollDown();
					
					$scope.timer = setTimeout($scope.checkAgentStatus(), 1000);
					
					$('.inside-link-aborted').bind('click', function(e) {
						
						savedData.chatstatus = 'Live Agent Not Available';
						
						activeChatEndEvent = '';
						clearTimeout($scope.timer);
						$scope.liveagent_stop();
						$scope.saveCustomerData();
						//$scope.showCallbackForm();
						$('.chat-messages').find('ul li.connect').remove();
						$('.chat-messages').find('ul').append($scope.entryAgent(customer_aborted_connect_to_liveagent));
						
						$('.inside-link-close').bind('click', function(e) {
						
							CloseWindow();
						});
					});
					
				} else {
					
					savedData.chatstatus = 'Live Agent Not Available';
					$scope.saveCustomerData();
					
					$('.chat-messages').find('ul').append($scope.entryChatbot(live_agent_not_available));
					$scope.chatScrollDown();
					$('.chatbutton').hide();
					
					$('.input').unbind('keypress');
					
					$('.inside-link').bind('click', function(e) {
						
						$scope.showCallbackForm();
					});
				}
				

			}, function error(response){
				
			});
	
	}
	
	$scope.checkAgentStatus = function() {
		
		var hist = savedData.history;
		if (!additional) {
			
			hist.push({'sequenceNumber':$scope.messageCount++, 'Type': 'A', 'message': addslashes(live_agent_connect)});
			hist.push({'sequenceNumber':$scope.messageCount++, 'Type': 'A', 'message': addslashes(live_agent_connect_with)});
			console.log(savedData.history);
			additional = true;
		}
		$http({
			method: 'POST',
			url: 'api.php',
			data: {
				'type': 'liveagent_check', 
				'history': hist,
				'name' : savedData.name,
				'token' : token,
				'cnt': cnt++
			},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {
	
				var chat = response.data['chat'];

				if (chat == 'requestfail') {
					
					savedData.chatstatus = 'Live Agent Not Available';
					
					$('.connect').remove();
					
					$('.chat-messages').find('ul').append($scope.entryAgent(live_agent_not_available));
					$scope.chatScrollDown();
					
					$scope.liveagent_stop();
					
					$scope.saveCustomerData();
					
					$('.inside-link').bind('click', function(e) {
						
						$scope.showCallbackForm();
					});
				} else if (chat == 'requestsuccess') {	
					
					$scope.timer = setTimeout($scope.checkAgentStatus(), 1000);
				
				
				} else if (chat == 'no-resp') {	
					
					if (activeChatEndEvent != '') {
						$scope.timer = setTimeout($scope.checkAgentStatus(), 1000);
					}
					
				} else if (chat == 'established') {
					
					$('.connect').remove();
					
					$('.chat-messages').find('ul').append($scope.entryAgent(live_agent_connect_with));
					$scope.chatScrollDown();
					
					$('.livechatbutton').show();

					$scope.timer = setTimeout(function() {$scope.sendAndCheckMessages();}, 2000);
					
					$('.input').keypress(function(event) {
						var oldTimer = ($scope.inputTimer != undefined);
						clearTimeout($scope.inputTimer);
						
						if (event.which == 13) {
							
							if ($scope.message != '' && $scope.message != undefined) {

								$scope.fullMessage = $scope.fullMessage + " " + $scope.message + '\n';
					
								$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
								$scope.chatScrollDown();
								$('.chat-input .input').val('');
								$scope.message = '';
								
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
					
				}
				
	
			}, function error(response){
				
				
				
			});
	}
	
	$scope.sendAndCheckMessages = function() {

		var oldMQ = messageQueue;
		$http({
			method: 'POST',
			url: 'api.php',
			data: {'type' : 'liveagent_checkandtalk', 'text': messageQueue, 'token': token},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {

				messageQueue = messageQueue.substr(oldMQ.length);

				var text = response.data['text'];
				var chat = response.data['chat'];
				var typing = response.data['typing'];
				var messageId = response.data['messageId'];

				if (text != '' && text != undefined) {
					
					$.each(text, function(key, value) {

						$('.chat-messages').find('ul').append($scope.entryAgent(value));
						$scope.chatScrollDown();
					});
				}
				
				if (typing != undefined && typing == true) {
					$('.typing').html(live_agent_typing);
					$('#chat-view .chat-messages').css('height','calc(100vh - 285px)');
				} else {
					$('.typing').html(' ');
					$('#chat-view .chat-messages').css('height','calc(100vh - 270px)');
				}
				
				if (chat == 'disconnect') {
					
					savedData.chatstatus = 'Agent Disconnect';
					
					$('.livechatbutton').hide();
					$('.input').unbind('keypress');

					$('.chat-messages').find('ul').append($scope.entryAgent(live_agent_chat_disconnected));
					$scope.chatScrollDown();
					
					$('.inside-link').bind('click', function(e) {
						
						$scope.showCallbackForm();
					});
				
				} else if (chat == 'stop') {
					
					savedData.messageId = messageId;
					$('.livechatbutton').hide();

					$('.input').unbind('keypress');
					
					$('.chat-messages').find('ul').append($scope.entryAgent(live_agent_chat_ended));
					$scope.chatScrollDown();
					$('.chat-messages').find('ul').append($scope.entryAgent(live_agent_chat_goodbye_message));
					$scope.chatScrollDown();
					
					$('.inside-link-survey').bind('click', function(e) {
						
						$scope.showSurveyPage();
					});
					
					$scope.chatStop = true;
					clearTimeout($scope.timer);
					
				} else {
					
					$scope.timer = setTimeout(function() {$scope.sendAndCheckMessages();}, 1000);
				}

			}, function error(response, status, error){

				$scope.timer = setTimeout(function() {$scope.sendAndCheckMessages();}, 1000);
			});
			
	}
	
	$scope.createFullLiveMessage = function(fullMessage) {

		messageQueue = fullMessage;
		$scope.fullMessage = '';
	}
	
	$scope.sendLiveMessage = function () {
		
		if ($scope.message != '') {
		
			$('.chat-messages').find('ul').append($scope.entryCustomer($scope.message));
			$scope.chatScrollDown();
			$('.chat-input .input').val('');
			
			messageQueue = messageQueue + " " + $scope.fullMessage + " " + $scope.message + '\n';
			$scope.message = '';
			$scope.fullMessage = '';
		}

	}	
	
	$scope.liveagent_stop = function() {
		
		$http({
			method: 'POST',
			url: 'api.php',
			data: {
				'type': 'liveagent_stop',
				'token': token
			},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {

				activeChatEndEvent = '';
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
				'firstname': savedData.firstname,
				'phone': savedData.phone, 
				'salutation': savedData.salutation,
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

				savedData.callbackId = JSON.parse(response.data['result'].result).callBackInfoId;
			}, function error(response){

			});
	}
	
	$scope.showCallbackForm = function () {
		
		$('#chat-view').hide();
		$('.endChat').hide();
		$('.bottomClose').show();
		$('#callback-view').show();
	}
	
	$scope.showSurveyPage = function () {
		
		$('#chat-view').hide();
		$('.endChat').hide();
		$('.bottomClose').show();
		$('#survey-view').show();
	}
	
	$scope.sendCallBackRequest =  function () {
		
		sendOnUnload = false;
		
		savedData.callback = $scope.callbackphone;
		
		$scope.saveCustomerData();
		
		$('#callback-view').hide();
		$('#survey-view').show();
		
	}
	
	$scope.sendSurveyRating = function () {
		
		sendOnUnload = false;
		
		$http({ 
			method: 'POST',
			url: 'api.php',
			data: {
				'type': 'sendSurveyRating', 
				'rating': $('input[name="stars"]:checked').val(),
				'callback': savedData.callbackId,
				'session': token,
				'messageId': savedData.messageId
			},
			headers: {
			    'Accept':'application/json',
			    'Content-Type':'application/json'
			}
			}).then(function success(response) {

			}, function error(response){

			});
		
		$('#survey-view').hide();
		$('#survey-success-view').show();
	}
	
	$scope.endChat = function () {
		
		if (sendOnUnload) {
			if (confirm(default_stop_chat)) {
				
				$scope.endChatOnUnload();
				
			}
		}
		
	}
	
	$scope.endChatOnUnload = function () {
		
		if (sendOnUnload) {	
			if ($scope.sessionid == '') {
				// first view - no chat to stop
				// do nothing
			} else if (!$scope.congesysStop) {
				
				savedData.chatstatus = 'Aborted';
				$scope.stop_cognesys_chat(true);
				
			} else if (activeChatEndEvent == '') {
				
				$scope.saveCustomerData();
			} else {
				
				$scope.chatStop = true;
				clearTimeout($scope.timer);
	
				$scope.liveagent_stop();
			}
		} 
		
		setTimeout(function(){
			CloseWindow();
		}, 2000);
		
	}
	
	$scope.entryChatbot = function(text) {
		
		var d = new Date();

		control_chatbot = '<li style="width:100%;position: relative;">' +
	                        '<div class="msj macro">' +
	                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="" style="width:31px;" src="'+you.avatar+'" /></div>' +
	                        '<div class="text text-r">' +
	                        '<p>'+text+'</p>' +
	                        '<p>' + d.toLocaleString('de-DE') + '</p>' +
	                        '</div>' +
	                        '</li>';

		return control_chatbot;
	} 
	
	$scope.entryCustomer = function(text) {
		
		var d = new Date();
		
		var $div = $("<div>", {id: "foo", "class": "a"});
		$div.text(text);

		control_customer = '<li style="width:100%;position: relative;">' +
	                        '<div class="msj-rta macro">' +
	                        '<div class="text text-l">' +
	                        '<p>'+ $div.html() +'</p>' +
	                         '<p>' + d.toLocaleString('de-DE') + '</p>' +
	                        '</div>' +
	                        '<div class="avatar"><img class="" style="width:31px;" src="'+ me.avatar +'" /></div>' +
	                        '</div>' +
	                        '</li>';
		
		return control_customer;
	} 
	
	$scope.entryAgent = function(text) {
		
		var d = new Date();
		
		control_agent = '<li style="width:100%;position: relative;">' +
                        '<div class="msj macro">' +
                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="" style="width:31px;" src="'+agent.avatar+'" /></div>' +
                        '<div class="text text-r">' +
                        '<p>' + text + '</p>' + 
                         '<p>' + d.toLocaleString('de-DE') + '</p>' +
                        '</div>' +
                        '</li>';
		
		return control_agent;
	}	
	
	$scope.chatScrollDown = function() {
		var elem = document.getElementById('chat-messages');
		elem.scrollTop = elem.scrollHeight;
	}	

});


window.onbeforeunload = function (event) {

   $.ajax({
     type: 'POST',
     async: false,
     data: {
		'type': activeChatEndEvent, 
		'session_id': savedData.sessionid, 
		'email': savedData.email, 
		'name': savedData.name, 
		'firstname': savedData.firstname,
		'phone': savedData.phone, 
		'salutation': savedData.salutation,
		'callback': savedData.callback, 
		'status': savedData.chatstatus,
		'tonality': savedData.tonality,
		'chathistory': savedData.history,
		'summary': savedData.summary,
		'endTime': savedData.endTime,
		'startTime': savedData.startTime,
		'token': token
	},
     url: 'api.php'
   }).done(function(data) {

	});
   

};

function CloseWindow() {
  var objWindow = window.open(location.href, "_self");
  objWindow.close();
}

window.onunload = function () {


}

function addslashes( str ) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}