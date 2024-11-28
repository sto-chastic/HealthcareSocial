<?php

if($argv[1] !== 'neuronaptic2017'){
	//TODO: program an email alert if tried to be accessed incorrectly
	exit("Incorrect password, unathorized access.");
}

require_once("../classes/Crypt.php");
require_once("../classes/TxtReplace.php");
require_once("../classes/User.php");
require_once("../classes/Settings.php");
require_once("../classes/Email_Creator.php");


//Mailer setup

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../packages/PHPMailer/src/Exception.php';
require '../packages/PHPMailer/src/PHPMailer.php';
require '../packages/PHPMailer/src/SMTP.php';

$database_name='confidrV5';
$timezone = date_default_timezone_set("America/Bogota");
//NOTE, you need to specify the address of the local host and the port to the database due to the use of different protocols in webbased php and unix
//TODO: Comment the following line for deplyment, or uncomment it for testing

$con = mysqli_connect("127.0.0.1:3308","root","",$database_name); //connection variable

// $database_name='confidrV5';
// $db_username= 'basic_users';
// $db_passwrd = '<-></|/euro#Naptic-20!7Bas!c_Usr<-';
// $con = mysqli_connect("localhost",$db_username,$db_passwrd,$database_name); //connection variable

if(mysqli_connect_errno()){
	echo "Failed to connect:" . mysqli_connect_errno(); // dot is add to string echo is print on screen.
	exit("FAILED TO CONNECT");
}

$crypt = new Crypt();
$email_creator = new Email_Creator();
$txt_rep = new TxtReplace();

function reescheduleUnconfirmedAppointments() {
	//TO be deprecated, maybe useful as guide in the future
	$sql = mysqli_query($con,
			"SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME like '%appointments_calendar%' and TABLE_SCHEMA = '$database_name'");
	
	foreach ($sql as $key => $value) {
		$table = $value['TABLE_NAME'];
		
	}
	
	return;
}

function scheduler_delete_sendEmail_unconfirmed_doc($con, $crypt, $email_creator, $txt_rep){
	//$current_time = date('Y-m-d H:i:s');
	$date = new DateTime();
	
	//echo $date->format("Y-m-d H:i:s");
	$current_time = $date->format("Y-m-d H:i:s");
	
	//echo "SELECT * FROM scheduler WHERE STR_TO_DATE(execution_time, '%Y-%m-%d %H:%i:%s') < '$current_time' AND type = 1";
	$sql = mysqli_query($con,"SELECT * FROM scheduler WHERE STR_TO_DATE(execution_time, '%Y-%m-%d %H:%i:%s') < '$current_time' AND type = 1"); //Type 1 is for email send for unconfirmed appointments
	
	foreach ($sql as $key => $value) {
		$_query = $value['query'];
		$_query2 = $value['query2'];
		//$_query3 = $value['query3'];
		
		$_id = $value['id'];
		$_sql = mysqli_query($con,$_query);
		
		if(mysqli_num_rows($_sql) > 0){ //If query 1 produced a result, run query 2
			
			// Query 2 retrieves appointment's details
			$_sql2 = mysqli_query($con,$_query2);
			$_arr2 = mysqli_fetch_assoc($_sql2);
			
			$_doctor_username= $_arr2['doctor_username'];
		 	$_patient_username= $_arr2['patient_username'];
		 	
		 	$_doctor_username_e= $crypt->EncryptU($_doctor_username);
		 	$_patient_username_e= $crypt->EncryptU($_patient_username);
		 	
		 	$_doctor_user_obj = new User($con, $_doctor_username, $_doctor_username_e);
		 	
		 	$_patient_user_obj = new User($con, $_patient_username, $_patient_username_e);
		 	$_patient_email = $_patient_user_obj->getEmail();
		 	
		 	$_patient_settings_obj = new Settings($con, $_patient_username, $_patient_username_e);
		 	$pat_lang = $_patient_settings_obj->getLang();
		 	
		 	// Query 1 retrieves appointment's calendar info
		 	
		 	$arr = mysqli_fetch_assoc($_sql);
		 	
		 	$consult_id = $arr['consult_id'];
		 	
		 	$selected_year = $arr['year'];
		 	$selected_month = $arr['month'];
		 	$selected_day = $arr['day'];
		 	$sel_time_st = $arr['time_start'];
		 	$sel_time_end = $arr['time_end'];
		 	
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
		 	$url = "http://www.confidr.com/patient_appointment_viewer.php?cid=" . $arr['consult_id'];
		 	
		 	$to = $_patient_email; // Send email to our user
		 	switch ($pat_lang){
		 		
		 		case("en"):
		 			$subject = 'Appointment Reschedule'; // Give the email a subject
		 			$message = '
		 					
					Unfortunately, your appointment scheduled for ' . $date_Str .' at ' . $time_Str. ', needs to be rescheduled.
					The doctor ' . $_doctor_user_obj->getFirstAndLastNameFast(). ' will not be available on the selected date and time. Please click on the following link to go to "Appointment Details" to pick a different time-slot (you should login first).
							
					------------------------
					'. $url.'
					Click on this link to reschedule the appointment.
					------------------------
							
					We appologize for the inconvenience.
					';
		 			
		 			
		 			$email_creator = new Email_Creator();
		 			
		 			$title = "Appointment Reschedule";
		 			$greet = "Unfortunately, your appointment scheduled for " . $date_Str ." at " . $time_Str. ", needs to be rescheduled.";
		 			$confirmation_message = "The doctor " . $_doctor_user_obj->getFirstAndLastNameFast(). " will not be available on the selected date and time. Please click on the following button to go to 'Appointment Details' to pick a different time-slot (you should login first).";
		 			$button = "Reschedule";
		 			$confirmation_message_2 = 'We appologize for the inconvenience.';
		 			
		 			$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message, $button, $url, $confirmation_message_2);
		 			break;
		 			
		 		case("es"):
		 			$subject = 'Reprogramación de Cita'; // Give the email a subject
		 			$message = '
		 					
					Desafortunadamente tu cita programada para el ' . $date_Str .' a las ' . $date_Str . ', debe ser reprogramada.
					El doctor ' . $_doctor_user_obj->getFirstAndLastNameFast(). ' no estará disponible en el horario seleccionado. Has click en el siguiente enlace para ir a "Detalles de Cita" para escoger un nuevo horario (debes iniciar sesión primero).
							
					------------------------
					'. $url.'
					Has click en este enlace para reprogramar la cita.
					------------------------

					Nos disculpamos por los inconvenientes que esto pueda ocasionar.
					';
		 			
		 			
		 			$email_creator = new Email_Creator();
		 			
		 			$title = "Reprogramación de Cita";
		 			$greet = "Desafortunadamente tu cita programada para el " . $date_Str ." a las " . $date_Str . ", debe ser reprogramada.";
		 			$confirmation_message = "El doctor " . $_doctor_user_obj->getFirstAndLastNameFast() ." no estará disponible en el horario seleccionado. Has click en el siguiente botón para ir a 'Detalles de Cita' para escoger un nuevo horario (debes iniciar sesión primero).";
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
		 		$mail->addAddress($to, $txt_rep->entities($_patient_user_obj->getFirstAndLastNameFast()));     // Add a recipient
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
		 	
		 	$appo_cal_dets = $_doctor_user_obj->getAppointmentsDetails_Doctor();
		 	$stmt = $con->prepare("UPDATE $appo_cal_dets SET cancelled_by_doc=1 WHERE consult_id=?");
		 	$stmt->bind_param("s", $consult_id);
		 	$stmt->execute();
		 	
		 	$appo_cal_dets = $_patient_user_obj->getAppointmentsDetails_Patient();
		 	$stmt = $con->prepare("UPDATE $appo_cal_dets SET cancelled_by_doc=1 WHERE consult_id=?");
		 	$stmt->bind_param("s", $consult_id);
		 	$stmt->execute();
		 	
		 	$scheduled_event_id = $value['id'];
		 	$del_sql = mysqli_query($con,"DELETE FROM `scheduler` WHERE `id` = '$scheduled_event_id'"); //Delete the event
		}
	}
}

while (true) {
	//All tasks to be executed go here
	scheduler_delete_sendEmail_unconfirmed_doc($con, $crypt, $email_creator, $txt_rep);
	sleep(10);//Seconds to wait for next execution
}