

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
	
	$scope.open_chat = function () {
		
		var error = false;
		var reg = REGEX_EMAIL;
		
		$('input[type="email"]').removeClass('error');
		$('.error_msg').hide();
		
		if (!reg.test($scope.email)) {
            
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
					$scope.sessionid = response.data['session-id'];
					
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
	


	// cognesys end
	
	// live agent beginn
	
	
	
	
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
				'status': savedData.chatstatus
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
                        '<div class="avatar" style="padding:0px 0px 0px 10px !important"><img class="img-circle" style="width:20px;" src="'+agentAvatar+'" /></div>' +
                        '</li>';
		
		return control_agent;
	}		

});