<?php

if (isset($_GET['lang']) && $_GET['lang'] == 'en') {
	include('app/lang/en_US.php');
} else {
	include('app/lang/de_DE.php');	
}


?>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $html_title; ?></title>
	
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
		
	<?php createJsTranslate(); ?>	
		
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
					<div class="endChat" ng-click="endChat()" style="display:none"></div>
				</div>
			</div>

			<div id="login-view" style="display: block;">
				<div class="row">
					<div class="col-xs-12">
						<h1><?php echo $login_view_welcome; ?></h1>
						<?php echo $login_view_welcome_text; ?>
					</div>
				</div>
				<div class="row mt-20">
					<div class="col-xs-12 col-sm-6">
						<div  style="float: left; width: 80px">
							<div class="form-group radio">
						    	<div class="form-radio">
									<label>
										<input name="salutation" value="Herr" type="radio" ng-model="salutation">
										<span class="input-dummy">
											<span class="icon"></span>
										</span>
										<?php echo $login_view_salutation_mr; ?>
									</label>
								</div>
							</div>
						</div>
						<div style="float: left;">
							<div class="form-group radio">
								<div class="form-radio">
									<label>
										<input name="salutation" value="Frau" type="radio" ng-model="salutation">
										<span class="input-dummy">
											<span class="icon"></span>
										</span>
										<?php echo $login_view_salutation_mrs; ?>
									</label>
								</div>
						  	</div>
					  	</div>
					</div>  
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
					    	<label for="firstnameinput"><?php echo $login_view_firstname; ?></label>
					    	<input type="text" class="form-control" id="firstnameinput" ng-model="firstname">
					  	</div>
					</div>  
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
					    	<label for="nameinput"><?php echo $login_view_lastname; ?></label>
					    	<input type="text" class="form-control" id="nameinput" ng-model="name">
					  	</div>
					</div>
				</div>
				<div class="row">	
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
					    	<label for="emailinput"><?php echo $login_view_email; ?></label>
					    	<input type="text" class="form-control" id="emailinput" ng-model="email">
					  	</div>  	
					</div>  	
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
					    	<label for="phoneinput"><?php echo $login_view_phone; ?></label>
					    	<input type="text" class="form-control" id="phoneinput" ng-model="phone">
					  	</div> 	
			        </div>
			    </div>	
			   	<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
					    	<a class="openChat btn btn-default btn-block" ng-click="open_chat()"><?php echo $login_view_btn_start_chat; ?></a>
					  	</div> 	
			        </div>
			    </div>	            
			</div>
			
			<div id="chat-view" style="display: none;">
				<div id="chat">
					<div id="chat-messages" class="chat-messages">
						<ul>

						</ul>
						<div class="typing"></div>
					</div>
					<div class="typing"></div>
					<div class="row">
						<div class="chat-input">
							<div class="form-group">
								<input class="input form-control" placeholder="Nachricht hier eingeben" type="text" ng-model="message">
								<a class="btn btn-default btn-block chatbutton" ng-click="sendMessage()"><?php echo $chat_view_btn_send; ?></a>
								<a class="btn btn-default btn-block livechatbutton" ng-click="sendLiveMessage()" style="display:none;"><?php echo $chat_view_btn_send; ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div id="survey-view" style="display: none;">
				<div class="row">
					<div class="col-xs-12">
						<h1><?php echo $survey_view_headline; ?></h1>
						<p><?php echo $survey_view_subline; ?></p>
					</div>
				</div>
				
				<div class="row border-yellow">
					<div class="col-xs-12">
						<h2><?php echo $survey_view_rating_headline; ?></h2>
					</div>
					<div class="col-xs-12">
						<ul>
							<li>
								<span><?php echo $survey_view_rating_not_satisfied; ?></span>
							</li>
							<li class="star">
								<span>1</span>
								<div class="form-group radio">
							    	<div class="form-radio">
										<label>
											<input name="stars" value="1" type="radio">
											<span class="input-dummy">
												<span class="icon"></span>
											</span>
										</label>
									</div>
								</div>
							</li>
							<li class="star">
								<span>2</span>
								<div class="form-group radio">
							    	<div class="form-radio">
										<label>
											<input name="stars" value="2" type="radio">
											<span class="input-dummy">
												<span class="icon"></span>
											</span>
										</label>
									</div>
								</div>
							</li>
							<li class="star">
								<span>3</span>
								<div class="form-group radio">
							    	<div class="form-radio">
										<label>
											<input name="stars" value="3" type="radio" checked="checked">
											<span class="input-dummy">
												<span class="icon"></span>
											</span>
										</label>
									</div>
								</div>
							</li>
							<li class="star">
								<span>4</span>
								<div class="form-group radio">
							    	<div class="form-radio">
										<label>
											<input name="stars" value="4" type="radio">
											<span class="input-dummy">
												<span class="icon"></span>
											</span>
										</label>
									</div>
								</div>
							</li>
							<li class="star">
								<span>5</span>
								<div class="form-group radio">
							    	<div class="form-radio">
										<label>
											<input name="stars" value="5" type="radio">
											<span class="input-dummy">
												<span class="icon"></span>
											</span>
										</label>
									</div>
								</div>
							</li>
							<li>
								<span><?php echo $survey_view_rating_satisfied; ?></span>
							</li>
						</ul>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
					    	<a class="sendFeedback btn btn-default btn-block" ><?php echo $survey_view_btn_send_feedback; ?></a>
					  	</div> 
					</div>
				</div>
			</div>
			
			<div id="callback-view" style="display: none;">
				<div class="row">
					<div class="col-xs-12">
						<h1><?php echo $callback_view_headline; ?></h1>
						<p><?php echo $callback_view_subline; ?></p>
					</div>
				</div>
				<div class="row mt-20">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
					    	<label for="phone"><?php echo $callback_view_textarea_headline; ?></label>
					    	<textarea rows="6" type="text" class="form-control" name="phone" id="phone" ng-model="callbackphone"></textarea>
					  	</div>
					</div>  
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<div class="form-group">
					    	<a class="callbackBtn btn btn-default btn-block" ng-click="sendCallBackRequest()"><?php echo $callback_view_btn_callback_request; ?></a>
					  	</div> 	
			        </div>
			    </div>	
			</div>
			
			<div class="bottomClose" style="display:block;">
				<a ng-click="endChat()" class="bottomClose"><?php echo $general_btn_close_window; ?></a>
			</div>
		</div>
	</div>

</body>
</html>