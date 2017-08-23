

var me = {};
me.avatar = "https://freeiconshop.com/wp-content/uploads/edd/person-flat.png";

var you = {};
you.avatar = "https://cdn4.iconfinder.com/data/icons/free-large-android-icons/512/Android.png";

var agent = {};
agent.avatar = "https://cdn4.iconfinder.com/data/icons/user-avatar-flat-icons/512/User_Avatar-26-512.png";

var REGEX_EMAIL = /^[\w!#$%&'*+/=?^_`{|}~-]+(?:\.[\w!#$%&'*+/=?^_`{|}~-]+)*@(?:[\w](?:[\w-]*[\w])?\.)+[\w](?:[\w-]*[\w])?$/;


var chatBot = angular.module('chat-bot', ['base64']);

chatBot.controller('chat', function ($scope, $http, $base64) {

	$scope.savedData = [];	
	$scope.sessionid = '';
	$scope.stopChating = false;
	
	$scope.open_chat = function () {
		
		var error = false;
		var reg = REGEX_EMAIL;
		
		$('input[type="email"]').removeClass('error');
		$('.error_msg').hide();
		
        if ($scope.email == "" || $scope.email == undefined) {
            
            error = true;
        } else if (!reg.test($scope.email)) {
            
            error = true;
        } else {
            $scope.savedData.email = $scope.email;
        }
		
		if ($scope.name != undefined) {
			$scope.savedData.name = $scope.name;
		} else {
			$scope.savedData.name = 'Max_Mustermann';
		}
		if ($scope.phone != undefined) {
			$scope.savedData.phone = $scope.phone;
		} else {
			$scope.savedData.phone = '03012345678';
		}
		
		if (!error) {
			$scope.savedData.history = [];
				
			$scope.start_cognesys_chat();
		} else {
			
			$('input[type="email"]').addClass('error');
			$('.error_msg').show();
		}

	}
	
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
			
				} else {
					
					$('#login-view').hide();
					$('#callback-view').show();
					
					$scope.saveCustomerData();
					
				}
			}, function error(response){
				
				$('#login-view').hide();
				$('#callback-view').show();
				
				$scope.saveCustomerData();
				
			});
					
	}	
	





	$scope.saveCustomerData = function () {

		$http({
			method: 'POST',
			url: 'api.php',
			data: {'type': 'sendCustomerData', 'session_id': $scope.sessionid, 'cData': $scope.savedData},
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

});