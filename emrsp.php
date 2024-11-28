<?php
require 'config/config.php';
include("includes/classes/TxtReplace.php");
include("includes/classes/Crypt.php");
include("includes/classes/User.php");
include("includes/classes/Settings.php");
include("includes/classes/Email_Creator.php");

//Mailer setup

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/packages/PHPMailer/src/Exception.php';
require 'includes/packages/PHPMailer/src/PHPMailer.php';
require 'includes/packages/PHPMailer/src/SMTP.php';

$crypt = new Crypt();
$succ_confirmed = 0;

if(isset($_SESSION['username']) && isset($_SESSION['messages_token'])){
	$temp_user = $_SESSION['username'];
	$temp_user_e = $_SESSION['username_e'];
	//$temp_passwrd = $_SESSION['passwrd'];
	$temp_messages_token= $_SESSION['messages_token'];
	
	$stmt = $con->prepare("SELECT * FROM users WHERE username=? AND messages_token=?");
	
	$stmt->bind_param("ss", $temp_user_e, $temp_messages_token);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	
	if(mysqli_num_rows($verification_query) == 1){
		$userLoggedIn = $temp_user; //Retrieves username
		$userLoggedIn_e = $temp_user_e;
		$user_obj = new User($con,$userLoggedIn,$userLoggedIn_e);
		$stmt->close();
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: register.php");
		$stmt->close();
	}
	
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	$txt_rep = new TxtReplace();
	$settings = new Settings($con, $userLoggedIn, $userLoggedIn_e);
	
	//LANGUAGE RETRIEVAL
	
	$lang = $settings->getLang();
	$_SESSION["lang"] = $lang;
	
}


//Authenticate user

elseif(isset($_GET['s']) && isset($_GET['u']) && isset($_GET['d'])){
	$temp_signup_enc_hex= $_GET['s'];
	$temp_username_enc_hex = $_GET['u'];
	$temp_key_enc_hex = $_GET['d'];
	
	//Decrypt key
	$key = $crypt->Decrypt(pack("H*",$temp_key_enc_hex),"--->bla873/|/e#r/|/ap28?iC_!");
	
	$temp_username = $crypt->Decrypt(pack("H*",$temp_username_enc_hex),$key);
	$temp_sign_up = $crypt->Decrypt(pack("H*",$temp_signup_enc_hex),$key);
	
	$stmt = $con->prepare("SELECT `username` FROM users WHERE `signup_date`=?");
	
	$stmt->bind_param("s", $temp_sign_up);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	
	if(mysqli_num_rows($verification_query) == 1){
		
		$verification_arr = mysqli_fetch_assoc($verification_query);
		if($verification_arr['username'] == $temp_username){
			$userLoggedIn_e= $temp_username;
			$userLoggedIn= $crypt->Decrypt($userLoggedIn_e);
			$user_obj = new User($con,$userLoggedIn,$userLoggedIn_e);
			$txt_rep = new TxtReplace();
			$settings = new Settings($con, $userLoggedIn, $userLoggedIn_e);
			$lang = $settings->getLang();
		}
	}
}
else{
	$userLoggedIn = "";
	$userLoggedIn_e = "";
	//session_start();
	session_destroy();
	header("Location: register.php");
	//$stmt->close();
}

//After the user has identified itself, we check if the appointment_id matches him

if(isset($_GET['c']) && isset($_GET['r']) && isset($_GET['d'])){
	if($user_obj->isDoctor()){
		
		$temp_key_enc_hex = $_GET['d'];
		$temp_consult_id_enc_hex = $_GET['c'];
		$temp_response_enc_hex = $_GET['r'];
		
		//Decrypt key
		$key = $crypt->Decrypt(pack("H*",$temp_key_enc_hex),"--->bla873/|/e#r/|/ap28?iC_!");
		
		//Decrypt consult and response
		$temp_consult_id = $crypt->Decrypt(pack("H*",$temp_consult_id_enc_hex),$key);
		$temp_response = $crypt->Decrypt(pack("H*",$temp_response_enc_hex),$key);
		
		$appo_cal = $user_obj->getAppointmentsCalendar();
		
		$stmt = $con->prepare("SELECT * FROM $appo_cal WHERE consult_id=?");
		$stmt->bind_param("s", $temp_consult_id);
		$stmt->execute();
		$verification_query = $stmt->get_result();
		
		if(mysqli_num_rows($verification_query) == 1){
			$verification_arr = mysqli_fetch_assoc($verification_query);
			if($temp_response == 1){
				//Confirm appointment
				$consult_id = $verification_arr['consult_id'];
				
				$stmt = $con->prepare("UPDATE $appo_cal SET confirmed_doc=1 WHERE consult_id=?");
				$stmt->bind_param("s", $consult_id);
				$stmt->execute();
				
			}
			elseif($temp_response == 0){
				//Require reschedule
				$consult_id = $verification_arr['consult_id'];
				
				$appo_cal_dets = $user_obj->getAppointmentsDetails_Doctor();
				$stmt = $con->prepare("UPDATE $appo_cal_dets SET cancelled_by_doc=1 WHERE consult_id=?");
				$stmt->bind_param("s", $consult_id);
				$stmt->execute();
				
				//Retrieve patient for alerting
				
				$stmt = $con->prepare("SELECT * FROM $appo_cal_dets WHERE consult_id=?");
				$stmt->bind_param("s", $consult_id);
				$stmt->execute();
				$appo_dets_query = $stmt->get_result();
				
				$appo_dets_arr = mysqli_fetch_assoc($appo_dets_query);
				$patient_username = $appo_dets_arr['patient_username'];
				$patient_username_e = $crypt->EncryptU($patient_username);
				
				$patient_user_obj = new User($con, $patient_username, $patient_username_e);
				$patient_setts_obj = new Settings($con, $patient_username, $patient_username_e);
				$pat_lang = $patient_setts_obj->getLang();
				$_patient_email = $patient_user_obj->getEmail();
				
				//Configure Patient email
				
				$selected_year = $verification_arr['year'];
				$selected_month = $verification_arr['month'];
				$selected_day = $verification_arr['day'];
				$sel_time_st = $verification_arr['time_start'];
				$sel_time_end = $verification_arr['time_end'];
				
				// Set up calendar info for display
				switch ($pat_lang){
					case("en"):
						$months_lang = "months_eng";
						
						$month_q = mysqli_query($con, "SELECT $months_lang FROM months WHERE id='$selected_month'");
						$month_name = mysqli_fetch_array($month_q)[$months_lang];
						
						$date_Str = $selected_day. " / " . $month_name . " / " . $selected_year;
						$time_Str = $sel_time_st;
						
						break;
						
					case("es"):
						$months_lang = "months_es";
						
						$month_q = mysqli_query($con, "SELECT $months_lang FROM months WHERE id='$selected_month'");
						$month_name = mysqli_fetch_array($month_q)[$months_lang];
						
						$date_Str = $selected_day. " / " . $month_name . " / " . $selected_year;
						$time_Str = $sel_time_st;
						break;
				}
				
				//Send email for patient
				$url = "http://www.confidr.com/patient_appointment_viewer.php?cid=" . $verification_arr['consult_id'];
				
				$to = $_patient_email; // Send email to our user
				switch ($pat_lang){
					
					case("en"):
						$subject = 'Appointment Reschedule'; // Give the email a subject
						$message = '
								
						Unfortunately, your appointment scheduled for ' . $date_Str .' at ' . $time_Str. ', needs to be rescheduled.
						The doctor ' . $$user_obj->getFirstAndLastNameFast(). ' will not be available on the selected date and time. Please click on the following link to go to "Appointment Details" to pick a different time-slot (you should login first).
								
						------------------------
						'. $url.'
						Click on this link to reschedule the appointment.
						------------------------
								
						We appologize for the inconvenience.
						';
						
						
						$email_creator = new Email_Creator();
						
						$title = "Appointment Reschedule";
						$greet = "Unfortunately, your appointment scheduled for " . $date_Str ." at " . $time_Str. ", needs to be rescheduled.";
						$confirmation_message = "The doctor " . $$user_obj->getFirstAndLastNameFast(). " will not be available on the selected date and time. Please click on the following button to go to 'Appointment Details' to pick a different time-slot (you should login first).";
						$button = "Reschedule";
						$confirmation_message_2 = 'We appologize for the inconvenience.';
						
						$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message, $button, $url, $confirmation_message_2);
						break;
						
					case("es"):
						$subject = 'Reprogramación de Cita'; // Give the email a subject
						$message = '
								
						Desafortunadamente tu cita programada para el ' . $date_Str .' a las ' . $date_Str . ', debe ser reprogramada.
						El doctor ' . $user_obj->getFirstAndLastNameFast(). ' no estará disponible en el horario seleccionado. Has click en el siguiente enlace para ir a "Detalles de Cita" para escoger un nuevo horario (debes iniciar sesión primero).
								
						------------------------
						'. $url.'
						Has click en este enlace para reprogramar la cita.
						------------------------
								
						Nos disculpamos por los inconvenientes que esto pueda ocasionar.
						';
						
						
						$email_creator = new Email_Creator();
						
						$title = "Reprogramación de Cita";
						$greet = "Desafortunadamente tu cita programada para el " . $date_Str ." a las " . $date_Str . ", debe ser reprogramada.";
						$confirmation_message = "El doctor " . $user_obj->getFirstAndLastNameFast() ." no estará disponible en el horario seleccionado. Has click en el siguiente botón para ir a 'Detalles de Cita' para escoger un nuevo horario (debes iniciar sesión primero).";
						$button = "Reprogramar";
						$confirmation_message_2 = 'Nos disculpamos por los inconvenientes que esto pueda ocasionar.';
						
						$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message, $button, $url, $confirmation_message_2);
						break;
				}
				
				$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
				
				try {
					//Server settings
					$mail->CharSet = 'UTF-8';
					$mail->SMTPDebug = 0;                                 // Enable verbose debug output
					$mail->isSMTP();                                      // Set mailer to use SMTP
					$mail->Host = 'smtp.1and1.com';                       // Specify main and backup SMTP servers
					$mail->SMTPAuth = true;                               // Enable SMTP authentication
					
					$mail->Username = 'support-confidr@jimenezd.com';                 // SMTP username
					$mail->Password = '/|/euRo#$!Na/pti_C#2017';                           // SMTP password
					
					$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
					$mail->Port = 587;                                    // TCP port to connect to
					
					//Recipients
					$mail->setFrom('support-confidr@jimenezd.com', 'Support-ConfiDr');
					$mail->addAddress($to, $txt_rep->entities($patient_user_obj->getFirstAndLastNameFast()));     // Add a recipient
					$mail->addReplyTo('support-confidr@jimenezd.com', 'Support-ConfiDr');
					
					//Content
					$mail->isHTML(true);                                  // Set email format to HTML
					$mail->Subject = $subject;
					$mail->Body    = $message_html;
					$mail->AltBody = $message;
					
					$mail->send();
					//echo 'Message has been sent';
				} catch (Exception $e) {
					//echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
				}
				$succ_confirmed = 1;
			}
		}
	}
	else{
		//header("Location: index.php");
	}
}


?>


<!DOCTYPE html>
<html>
<head>
	<title>Welcome to ConfiDr.</title>
	<link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="assets/js/register.js"></script>
</head>
<body>
	<div class="wrapper">
		<div class="wrapper_dark">
			<div class="top_register">
				<div class="logo_register" >
					<a href="register.php">
						<img src="assets/images/icons/logo2.png" id="logo_position">
					</a>
				</div>
			</div>
			
			<div class="main_center_left" style=" margin-left: 15%; margin-right: 15%;">
             	<p style=" background-color: rgba(69, 69, 69, 0.5);">
	             	<span class="title_center" style=" letter-spacing: 5px; font-size: 3vw;">
	             	<?php 
	             	switch($lang){
	             	    case("en"):
	             	        echo ($succ_confirmed)?"Success": "This link is incorrect or it has expired. Get in contact with Confidr. at support-confidr@jimenezd.com.";
	                        break;
	             	    case("es"):
	             	        echo ($succ_confirmed)?"Operación Exitosa": "Este enlace es incorrecto o ya expiró. Ponte en contacto con ConfiDr. en support-confidr@jimenezd.com";
	             	        break;
	             	} 
	                ?>
	             	</span>
             	</p>

			</div>
			
		</div>
	</div>
</body>
</html>