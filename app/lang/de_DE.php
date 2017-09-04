<?php

// contents inside index.php
$html_title = 'DHL - ChatBot';

$login_view_welcome = 'Willkommen beim DHL Kundensupport';

// with open <p> tag on start and close </p> tag on end
$login_view_welcome_text = '<p>damit wir auch lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>
						<p>At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>';

$login_view_salutation_mr = 'Herr';
$login_view_salutation_mrs = 'Frau';
$login_view_firstname = 'Vorname';
$login_view_lastname = 'Nachname';
$login_view_email = 'E-Mail';
$login_view_phone = 'Telefonnummer';
$login_view_btn_start_chat = 'Chat starten!';

$chat_view_btn_send = 'Absenden';

$survey_view_headline = 'Vielen Dank, wir freuen uns, wenn wir Ihnen helfen konnten.';
$survey_view_subline = 'Bitte bewerten Sie noch kurz unseren neuen Service.';
$survey_view_rating_headline = 'Wie zufrieden waren Sie mit unserem neuen Service?';
$survey_view_rating_not_satisfied = 'Nicht sehr<br />zufrieden';
$survey_view_rating_satisfied = 'Sehr zufrieden';
$survey_view_btn_send_feedback = 'Feedback senden';

$callback_view_headline = 'Derzeit ist kein Live-Agent verfügbar.';
$callback_view_subline = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';
$callback_view_textarea_headline = 'Bitte geben Sie nachfolgend Ihr Anliegen und Ihre Telefonnummer zur Kontaktaufnahme ein:';
$callback_view_btn_callback_request = 'Rückrufwunsch senden';

$general_btn_close_window = 'Dieses Fenster schließen';

// for js contents
function createJsTranslate() {
	
	$translate = array(
		'default_lastname' => 'Mustermann',
		'default_firstname' => 'Max',
		'default_phone' => '03012345678',
		'default_salutation' => 'Keine Anrede',
		'cognesys_welcome_message' => 'Hallo<br>Wie kann ich Ihnen helfen?',
		'cognesys_chat_end_message' => 'Chat wurde beendet. Vielen Dank und einen schönen Tag.',
		'cognesys_chat_end_goodbye_message' => 'Nachfolgend können Sie noch unseren neuen Service bewerten. Bitte klicken Sie dazu <a class="inside-link-survey">hier</a>',
		'live_agent_connect' => 'Verbinden mit Live-Agent', 
		'live_agent_connect_with' => 'Wie kann ich Ihnen helfen?',
		'live_agent_not_available' => 'Leider sind derzeit alle Live-Agents in Kundengesprächen. Sie können uns einen Rückrufwunsch senden! Bitte klicken Sie dazu <a class="inside-link">hier</a>',
		'live_agent_chat_ended' => 'Der Chat wurde vom Live-Agent beendet!',
		
	
	
	);
	
	
	echo '<script type="text/javascript">';
	
	foreach ($translate as $key => $value) {
		echo 'var ' . $key . '="' . $value . '";';
	}
	
	echo '</script>';
}

?>