<?php 
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/Appointments_Calendar.php");
	include("../classes/Calendar.php");
	include("../classes/TxtReplace.php");
	include("../classes/TimeStamp.php");
	include("../classes/Email_Creator.php");
	include("../classes/Settings.php");
	
	//Mailer setup
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	
	require '../packages/PHPMailer/src/Exception.php';
	require '../packages/PHPMailer/src/PHPMailer.php';
	require '../packages/PHPMailer/src/SMTP.php';
	
	$crypt =  new Crypt();

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
			$userLoggedIn = $temp_user;
			$userLoggedIn_e = $temp_user_e;
			$final_mess_token = $temp_messages_token;
			$txt_rep = new TxtReplace();
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: ../../register.php");
			$stmt->close();
		}
	}
	elseif(isset($_SESSION['username']) && isset($_SESSION['confirm_email_alert'])){
		if($_SESSION['confirm_email_alert']){
			$temp_user = $_SESSION['username'];
			$temp_user_e = $_SESSION['username_e'];
			
			$stmt = $con->prepare("SELECT * FROM users WHERE username=?");
			
			$stmt->bind_param("s", $temp_user_e);
			$stmt->execute();
			$verification_query = $stmt->get_result();
			
			if(mysqli_num_rows($verification_query) == 1){
				$userLoggedIn = $temp_user;
				$userLoggedIn_e = $temp_user_e;
				
				$mt_arr = mysqli_fetch_assoc($verification_query);
				$final_mess_token  = $mt_arr['messages_token'];
			}
			else{
				$userLoggedIn = "";
				session_start();
				session_destroy();
				header("Location: ../../register.php");
				$stmt->close();
			}
		}
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}

	$lang = $_SESSION['lang']; 
	$selected_year = $_REQUEST['year'];
	$selected_month_t = date_create("0000-" . $_REQUEST['month'] . "-15");
	$selected_month = date_format($selected_month_t,"m");
	$selected_day = $_REQUEST['day'];
	$profile_owner_e = pack("H*", $_REQUEST['profile_owner']);
	$profile_owner = $crypt->Decrypt($profile_owner_e);
	//$userLoggedIn = $userLoggedIn;
	$payment_method = $_REQUEST['payment_method'];
	$appo_type_id = $_REQUEST['ap_id'];
	$profile_owner_obj = new User($con, $profile_owner, $profile_owner_e);
	$app_dur_tab = $profile_owner_obj->getAppoDurationTable();
	
	$stmt = $con->prepare("SELECT * FROM $app_dur_tab WHERE id=?");
	$stmt->bind_param("i", $appo_type_id);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	
	if(mysqli_num_rows($verification_query) == 1){
		$_id_arr = mysqli_fetch_assoc($verification_query);
		$appo_type_id = $_id_arr['id'];
		$appo_type =  $_id_arr['appo_type'];
		$cost = $_id_arr['cost'];
		$currency = $_id_arr['currency'];
	}
	else{
		header("Location: 404.php");
	}
	
// 	if(isset($_REQUEST['external'])){
// 		if($_REQUEST['external'] == 1){
// 			$external =  1;
// 		}
// 		else{
// 			$external = 0;
// 		}
// 	}		
// 	else{
// 		$external = 0;
// 	}
	
	if($profile_owner == $userLoggedIn){
		$external = 1;
	}
	else{
		$external = 0;
	}

	$time_ini = strtotime($_REQUEST['ap_st']);
	$time_end = strtotime($_REQUEST['ap_end']);

	$sel_time_st = date('H:i', $time_ini);
	$sel_time_end = date('H:i', $time_end);
	$creation_date = date("ymdHis");

	$profile_owner_obj = new User($con,$profile_owner, $crypt->EncryptU($profile_owner));
	$appointments_table = $profile_owner_obj->getAppointmentsCalendar();
	$appointments_details = $profile_owner_obj->getAppointmentsDetails_Doctor();

	if(!$external){
		$userLoggedIn_obj = new User($con,$userLoggedIn, $userLoggedIn_e);
		$appointments_table_pat = $userLoggedIn_obj->getAppointmentsCalendar_Patient();
		$appointments_details_pat = $userLoggedIn_obj->getAppointmentsDetails_Patient();
	}
	//create Id
	
	$crypt_usrnames_xtract = bin2hex($crypt->Encrypt(substr($profile_owner, -4),"-%->blaP873/|/e#r/|/ap28?iC_P0z7!"));
	//$consult_id = "appo_" . $profile_owner . $selected_year . $selected_month . $selected_day . $time_ini; //consult unique ID
	$consult_id = "appo_" . $creation_date . $selected_year . $selected_month . $selected_day . $time_ini . $crypt_usrnames_xtract; //consult unique ID
	
	//for the event of appointment deletion
	$schedule_id = "appo_sched" . $crypt_usrnames_xtract. $selected_year . $selected_month . $selected_day . $time_ini; //schedule ID
	$schedule_id_pat = "appo_sched_pat" . $crypt_usrnames_xtract. $selected_year . $selected_month . $selected_day . $time_ini; //schedule ID
	
	//verify there are no other appointments already scheduled for the doctor

	$err = [];

	$stmt = $con->prepare("SELECT * FROM $appointments_table WHERE time_start < ? AND time_end > ? AND year = ? AND month = ? AND day = ?");
	$stmt->bind_param("sssss", $sel_time_end, $sel_time_st, $selected_year, $selected_month, $selected_day);
	$stmt->execute();
	$query = $stmt->get_result();

	$num_appoints = mysqli_num_rows($query);
	
	//Verify the doctor has the selected time available for booing, also get the office with available time
	
	$calendar = new Calendar($con, $profile_owner, $crypt->EncryptU($profile_owner));
	
	$office = $calendar->getAvailabilityIntervalOffice($payment_method, $sel_time_st, $sel_time_end, $selected_day, $selected_month, $selected_year);

	if($num_appoints > 0 || $office == 0)
		array_push($err,"ERROR at verification");
	elseif(!$external){
		//verify there are no other appointments already scheduled for the patient
		
		$stmt = $con->prepare("SELECT * FROM $appointments_table_pat WHERE time_start < ? AND time_end > ? AND year = ? AND month = ? AND day = ?");
		$stmt->bind_param("sssss", $sel_time_end, $sel_time_st, $selected_year, $selected_month, $selected_day);
		$stmt->execute();
		$query = $stmt->get_result();
		
		$num_appoints = mysqli_num_rows($query);
		
		if($num_appoints > 0)
			array_push($err,"ERROR at verification pat");
		else{
		
			//Doctor Appointment details table
			if (false === ($stmt = $con->prepare("INSERT INTO $appointments_details (`consult_id`, `payment_info`, `appo_type`, `doctor_username`, `patient_username`, `cancelled_by_pat`, `cancelled_by_doc`, `reescheduled` , `cost`, `currency`, `payed_through_confidr`, `plan`, `closed`, `notes`, `external_patient`, `office`) VALUES (?, ?, ?, ?, ?, 0, 0, 0, ?, ?, '', '', 0, '', 0, ?)"))){
				array_push($err,"ERROR at appointment insertion");
			}
			elseif (!$stmt->bind_param("ssissisi", $consult_id, $payment_method, $appo_type_id, $profile_owner, $userLoggedIn, $cost, $currency, $office)){
				array_push($err,"ERROR at appointment insertion");
			}
			elseif (!$stmt->execute()){
				array_push($err,"ERROR at appointment insertion");
			}
	
			//Doctor Appointment calendar table
			if (false === ($stmt = $con->prepare("INSERT INTO $appointments_table (`year`, `month`, `day`, `time_start`, `time_end`, `consult_id`, `confirmed_pat` , `confirmed_doc`, `creation_date_time`) VALUES (?,?,?,?,?,?,0,0,?)"))){
				array_push($err,"ERROR at appointment details insertion");
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','1d')");
			}
			elseif (!$stmt->bind_param("sssssss", $selected_year,$selected_month,$selected_day,$sel_time_st,$sel_time_end,$consult_id,$creation_date)){
				array_push($err,"ERROR at appointment details insertion");
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','2d')");
			}
			elseif (!$stmt->execute()){
				array_push($err,"ERROR at appointment details insertion");
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','3d')");
			}
			
			
			
			
			
			//Patient Appointment details table
			
			$specialization = $profile_owner_obj->getSpecializationsCode($lang);
			
			if (false === ($stmt = $con->prepare("INSERT INTO $appointments_details_pat (`consult_id`, `payment_info`, `appo_type`, `doctor_username`, `patient_username`, `cancelled_by_pat`, `cancelled_by_doc`, `reescheduled`, `cost`, `currency`, `payed_through_confidr`, `plan`, `private_plan`, `closed`, `specializations`, `office`) VALUES (?, ?, ?, ?, ?, 0, 0, 0, ?, ?, '','', 0, 0,?,?)"))){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','1')");
				array_push($err,"ERROR at appointment details insertion pat");
			}
			elseif(!$stmt->bind_param("ssississi", $consult_id, $payment_method, $appo_type_id, $profile_owner, $userLoggedIn, $cost, $currency ,$specialization,$office)){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','2')");
				array_push($err,"ERROR at appointment details insertion pat");
			}
			elseif(!$stmt->execute()){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','3')");
				array_push($err,"ERROR at appointment insertion pat");
			}
			
			
			//Patient Appointment calendar table
			
			if (false === ($stmt = $con->prepare("INSERT INTO $appointments_table_pat VALUES (?,?,?,?,?,?,0,0,?)"))){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','1')");
				array_push($err,"ERROR at appointment insertion pat");
			}
			elseif (!$stmt->bind_param("sssssss", $selected_year,$selected_month,$selected_day,$sel_time_st,$sel_time_end,$consult_id,$creation_date)){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','2')");
				array_push($err,"ERROR at appointment insertion pat");
			}
			elseif (!$stmt->execute()){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','3')");
				array_push($err,"ERROR at appointment insertion pat");
			}
			$stmt->close();
		}
	}
	elseif($external){
		
		$external_id = date("ymdHis") . mt_rand(0,9);
		
		$pat_name=$_REQUEST['pat_name'];
		$pat_contact=$_REQUEST['pat_contact'];
		$pat_insurance=$_REQUEST['pat_insurance'];
		$patient_notes=$_REQUEST['patient_notes'];
		$ext_pat_tab = $profile_owner_obj->getExternalPatients_Tab();
		
		$stmt = $con->prepare("INSERT INTO $ext_pat_tab (`username`, `name`, `contact_info`, `insurance`, `notes`) VALUES (?,?,?,?,?)");
		$stmt->bind_param("sssss",$external_id,$pat_name,$pat_contact,$pat_insurance,$patient_notes);
		$stmt->execute();
		 
		//Doctor Appointment details table
		if (false === ($stmt = $con->prepare("INSERT INTO $appointments_details (`consult_id`, `payment_info`, `appo_type`, `doctor_username`, `patient_username`, `cancelled_by_pat`, `cancelled_by_doc`, `reescheduled` , `cost`, `currency`, `payed_through_confidr`, `plan`, `closed`, `notes`, `external_patient`, `office`) VALUES (?, ?, ?, ?, ?, 0, 0, 0, ?, ?, '', '', 0, '', 0, ?)"))){
			array_push($err,"ERROR at appointment insertion");
		}
		elseif (!$stmt->bind_param("ssissisi", $consult_id, $payment_method, $appo_type_id, $profile_owner, $external_id, $cost, $currency, $office)){
			array_push($err,"ERROR at appointment insertion");
		}
		elseif (!$stmt->execute()){
			array_push($err,"ERROR at appointment insertion");
		}
		
		//Doctor Appointment calendar table
		if (false === ($stmt = $con->prepare("INSERT INTO $appointments_table (`year`, `month`, `day`, `time_start`, `time_end`, `consult_id`, `confirmed_pat`, `confirmed_doc`, `creation_date_time`) VALUES (?,?,?,?,?,?,0,0,?)"))){
			array_push($err,"ERROR at appointment details insertion");
			//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','1d')");
		}
		elseif (!$stmt->bind_param("sssssss", $selected_year,$selected_month,$selected_day,$sel_time_st,$sel_time_end,$consult_id,$creation_date)){
			array_push($err,"ERROR at appointment details insertion");
			//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','2d')");
		}
		elseif (!$stmt->execute()){
			array_push($err,"ERROR at appointment details insertion");
			//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','3d')");
		}
	}

	//response

	if(in_array("ERROR at verification",$err)){
		http_response_code(409);
	}
	elseif(in_array("ERROR at verification pat",$err)){
		http_response_code(412);
	}
// 	elseif(!empty($err)){
// 		http_response_code(400);
// 	}
	elseif(!$external){
		
		$hours = 24;
		
		
//Send Email for doctor!!!
		if(!$external){
			//Set up authentication for the links
			$confirm_appointment_salt = "jgvasjh7347db7%a-!hbd12";
			$username_e = $profile_owner_obj->username_e;
			$username = $profile_owner_obj->username;
			
			$doc_setts = new Settings($con, $username, $username_e);
			$doc_lang = $doc_setts->getLang();
			
			$username_key = $crypt->Encrypt(rand(0,9). rand(0,9) . $confirm_appointment_salt . rand(0,9).  rand(0,9).  rand(0,9). rand(0,9),"/|/e#rNap28?iC_!"); //Generate a random key 
			$username_key_enc_hex = bin2hex($crypt->Encrypt($username_key,"--->bla873/|/e#r/|/ap28?iC_!")); //Encrypt it for sending
			
			$signup_date_enc =  $crypt->Encrypt($profile_owner_obj->user_info['signup_date'],$username_key);
			$username_enc =  $crypt->Encrypt($username_e,$username_key);
			$consult_id_enc = $crypt->Encrypt($consult_id,$username_key);
			
			$signup_date_enc_hex =  bin2hex($signup_date_enc);
			$username_enc_hex = bin2hex($username_enc);
			$consult_id_enc_hex =  bin2hex($consult_id_enc);
			
			//Confirm URL
			$one = $crypt->Encrypt("1",$username_key);
			$one_hex = bin2hex($one);
			$confirm_url = "http://www.confidr.com/emrsp.php?s=" . $signup_date_enc_hex. "&u=" . $username_enc_hex
						. "&d=" . $username_key_enc_hex. "&c=" . $consult_id_enc_hex . "&r=" . $one_hex;
			
			//Reject URL
			$zero = $crypt->Encrypt("0",$username_key);
			$zero_hex = bin2hex($zero);
			$reject_url = "http://www.confidr.com/emrsp.php?s=" . $signup_date_enc_hex. "&u=" . $username_enc_hex
						. "&d=" . $username_key_enc_hex . "&c=" . $consult_id_enc_hex . "&r=" . $zero_hex;
			
			// Set up calendar info for display
			switch ($doc_lang){
				case("en"):
					$months_lang = "months_eng";
					
					$month_q = mysqli_query($con, "SELECT $months_lang FROM months WHERE id='$selected_month'");
					$month_name = mysqli_fetch_array($month_q)[$months_lang];
					
					$date_Str = $selected_day. " / " . $month_name . " / " . $selected_year;
					$time_Str = " from " . $sel_time_st . " to " . $sel_time_end;
					
					break;
					
				case("es"):
					$months_lang = "months_es";
					
					$month_q = mysqli_query($con, "SELECT $months_lang FROM months WHERE id='$selected_month'");
					$month_name = mysqli_fetch_array($month_q)[$months_lang];
					
					$date_Str = $selected_day. " / " . $month_name . " / " . $selected_year;
					$time_Str = " de " .$sel_time_st . " a " . $sel_time_end;
					break;
			}
			
			
		//Send email to the doctor
			
			$to = $profile_owner_obj->getEmail(); // Send email to our user
			switch ($doc_lang){
				
				case("en"):
					$subject = 'New Appointment ConfiDr.'; // Give the email a subject
					$message = '
							
					You have a new appointment with patient ' . $userLoggedIn_obj->getFirstAndLastNameFast() .'
					The appointment has been scheduled for ' . $date_Str . ', ' . $time_Str . '. You need to click on the following link to CONFIRM the appointment within ' . $hours .' hours, otherwise the appointment will be CANCELED.
							
					------------------------
					'. $confirm_url.'
					Click this link to confirm the appointment.
					------------------------
							
					If you cannot make it to this appointment, click the following link to request the patient to pick a different available time-slot. Doing this repeatedly can result in penalties, if you cannot honor an available slot, you should mark it as not available in your calendar configuration before any patient books it.
							
					------------------------
					'. $reject_url .'
					Click this link to request a reeschedule.
					------------------------
					';
					
					
					$email_creator = new Email_Creator();
					
					$title = "New Appointment ConfiDr.";
					$greet = 'You have a new appointment with patient ' . $userLoggedIn_obj->getFirstAndLastNameFast() .'.';
					$confirmation_message = 'The appointment has been scheduled for ' . $date_Str . ', ' . $time_Str . '. You need to click on the following button to CONFIRM the appointment within ' . $hours .' hours, otherwise the appointment will be CANCELED.';
					$button = "Confirm Appointment";
					$confirmation_message_2 = 'If you cannot make it to this appointment, click the following button to request the patient to pick a different available time-slot. Doing this repeatedly can result in penalties, if you cannot honor an available slot, you should mark it as not available in your calendar configuration before any patient books it.';
					$button_2 = "Request Reeschedule";
					$url = $confirm_url;
					$url_2 = $reject_url;
					
					$message_html = $email_creator->doc_appointment_email_2_buttons($title, $greet, $confirmation_message,
							$button, $url, $confirmation_message_2,$button_2, $url_2);
					break;
					
				case("es"):
					$subject = 'Nueva Cita ConfiDr.'; // Give the email a subject
					$message = '
							
					Tienes una nueva cita con el paciente ' . $userLoggedIn_obj->getFirstAndLastNameFast() .'
					La cita ha sido programada para el ' . $date_Str . ', ' . $time_Str . '. Debes hacer click en el siguiente enlace para CONFIRMAR la cita dentro de ' . $hours .' horas, de lo contrario la cita será CANCELADA.
					
					------------------------
					'. $confirm_url.'
					Has click en este enlace para confirmar la cita.
					------------------------

					Si no puedes asistir a esta cita, has click en el siguiente enlace para solicitar al paciente que escoja otro espacio disponible. Hacer esto en repetidas ocaciones puede ocasionarte penalizaciones, si no puedes cumplir un horario, debes marcarlo en tu confiduración de calendario antes de que algún paciente reserve.
					
					------------------------
					'. $reject_url .'
					Has click en este enlace para solicitar reprogramación.
					------------------------
					';
					
					
					$email_creator = new Email_Creator();
					
					$title = "Nueva Cita ConfiDr.";
					$greet = "Tienes una nueva cita con el paciente " . $userLoggedIn_obj->getFirstAndLastNameFast() .".";
					$confirmation_message = "La cita ha sido programada para el " . $date_Str . ", " . $time_Str . ". Debes hacer click en el siguiente boton para CONFIRMAR la cita dentro de " . $hours ." horas, de lo contrario la cita será CANCELADA.";
					$button = "Confirmar Cita";
					$confirmation_message_2 = 'Si no puedes asistir a esta cita, has click en el siguiente boton para solicitar al paciente que escoja otro espacio disponible. Hacer esto en repetidas ocaciones puede ocasionarte penalizaciones, si no puedes cumplir un horario, debes marcarlo en tu configuración de calendario antes de que algún paciente reserve.';
					$button_2 = "Solicitar Reprogramación";
					$url = $confirm_url;
					$url_2 = $reject_url;
					
					$message_html = $email_creator->doc_appointment_email_2_buttons($title, $greet, $confirmation_message,
							$button, $url, $confirmation_message_2,$button_2, $url_2);
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
				$mail->addAddress($to, $txt_rep->entities($profile_owner_obj->getFirstAndLastNameFast()));     // Add a recipient
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
			
		//Send email to the patient
			
			$to = $userLoggedIn_obj->getEmail(); // Send email to our user
			
			//Confirm URL
			
			//Set up authentication for the links for the patient
			$confirm_appointment_salt = "jgvasjh7347db7%a-!hbd12";
			$username_e = $userLoggedIn_obj->username_e;
			
			$username_key = $crypt->Encrypt( rand(0,9). rand(0,9) . $confirm_appointment_salt . rand(0,9).  rand(0,9).  rand(0,9). rand(0,9),"/|/e#rNap28?iC_!"); //Generate a random key
			$username_key_enc_hex = bin2hex($crypt->Encrypt($username_key,"--->bla873/|/e#r/|/ap28?iC_!")); //Encrypt it for sending
			
			$signup_date_enc =  $crypt->Encrypt($userLoggedIn_obj->user_info['signup_date'],$username_key);
			$username_enc =  $crypt->Encrypt($username_e,$username_key);
			
			$signup_date_enc_hex =  bin2hex($signup_date_enc);
			$username_enc_hex = bin2hex($username_enc);
			
			switch ($lang){
				case("en"):
					$months_lang = "months_eng";
					
					$month_q = mysqli_query($con, "SELECT $months_lang FROM months WHERE id='$selected_month'");
					$month_name = mysqli_fetch_array($month_q)[$months_lang];
					
					$date_Str = $selected_day. " / " . $month_name . " / " . $selected_year;
					$time_Str = " from " . $sel_time_st . " to " . $sel_time_end;
					
					break;
					
				case("es"):
					$months_lang = "months_es";
					
					$month_q = mysqli_query($con, "SELECT $months_lang FROM months WHERE id='$selected_month'");
					$month_name = mysqli_fetch_array($month_q)[$months_lang];
					
					$date_Str = $selected_day. " / " . $month_name . " / " . $selected_year;
					$time_Str = " de " .$sel_time_st . " a " . $sel_time_end;
					break;
			}
			
			$confirm_url = "http://www.confidr.com/patient_appointment_viewer.php?s=" . $signup_date_enc_hex. "&u=" . $username_enc_hex
			. "&d=" . $username_key_enc_hex. "&cid=" . $consult_id ;
			switch ($lang){
				
				case("en"):
					$subject = 'New Appointment ConfiDr.'; // Give the email a subject
					$message = '
							
					You have scheduled an appointment with doctor ' . $profile_owner_obj->getFirstAndLastNameFast() .'
					The appointment has been scheduled for ' . $date_Str . ', ' . $time_Str . '. You need to click on the following link and CONFIRM the appointment within ' . $hours .' hours, otherwise the appointment will be CANCELED.
							
					------------------------
					'. $confirm_url.'
					Click this link to jump to appointment.
					------------------------

					In this link, you can also insert relevant information for your consult.
					';
					
					
					$email_creator = new Email_Creator();
					
					$title = "New Appointment ConfiDr.";
					$greet = 'You have scheduled an appointment with doctor ' . $profile_owner_obj->getFirstAndLastNameFast() .'.';
					$confirmation_message = 'The appointment has been scheduled for ' . $date_Str . ', ' . $time_Str . '. You need to click on the following button to CONFIRM the appointment within ' . $hours .' hours, otherwise the appointment will be CANCELED.';
					$button = "Appointment Details";
					$url = $confirm_url;
					$confirmation_message_2 = 'In this link, you can also insert relevant information for your consult.';
					
					$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
							$button, $url, $confirmation_message_2);
					break;
					
				case("es"):
					$subject = 'Nueva Cita ConfiDr.'; // Give the email a subject
					$message = '
							
					Tienes una nueva cita con el doctor ' . $profile_owner_obj->getFirstAndLastNameFast() .'
					La cita ha sido programada para el ' . $date_Str . ', ' . $time_Str . '. Debes hacer click en el siguiente enlace para CONFIRMAR la cita dentro de ' . $hours .' horas, de lo contrario la cita será CANCELADA.
							
					------------------------
					'. $confirm_url.'
					Has click en este enlace para ir a la cita.
					------------------------
							
					En este enlace también puedes agregar información relevante para tu doctor.
					';
					
					
					$email_creator = new Email_Creator();
					
					$title = "Nueva Cita ConfiDr.";
					$greet = 'Tienes una nueva cita con el doctor ' . $profile_owner_obj->getFirstAndLastNameFast() .'.';
					$confirmation_message = "La cita ha sido programada para el " . $date_Str . ", " . $time_Str . ". Debes hacer click en el siguiente boton para CONFIRMAR la cita dentro de " . $hours ." horas, de lo contrario la cita será CANCELADA.";
					$button = "Detalles de Cita";
					$confirmation_message_2 = 'En este enlace también puedes agregar información relevante para tu doctor.';
					$url = $confirm_url;
					
					$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
							$button, $url, $confirmation_message_2);
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
				$mail->addAddress($to, $txt_rep->entities($profile_owner_obj->getFirstAndLastNameFast()));     // Add a recipient
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
			
		}
		
		
		
		
		
		//If the appointment was scheduled without a confirmed email
		
		if(isset($_SESSION['confirm_email_alert'])){
			if($_SESSION['confirm_email_alert']){
				
				$em = $userLoggedIn_obj->getEmail();
				$md5email = md5($em);
				
				$to      = $em; // Send email to our user
				switch ($lang){
					
					case("en"):
						$subject = 'ConfiDr. Email Verification'; // Give the email a subject
						$message = '
								
						Welcome to ConfiDr! The first social network joining doctors and patients.
						You have scheduled an appointment with your newly created account, however you should verify your email withtin 2 hours since you schedule it or it will be automatically CANCELED.
								
						Fear not! To keep your appointment, please click on the link below and login.
								
						------------------------
						http://www.confidr.com/confidr/verify.php?email='.$md5email.'&hash='.$final_mess_token.'
						Please click this link to activate your account.
						------------------------
								
								
						THEN, TO CONFIRM YOUR APPOINTMENT, please click on the following link AFTER you have confirmed your email (above link) and logged in at confidr.com .
						------------------------
						http://www.confidr.com/confidr/patient_appointment_viewer.php?cid='.$consult_id.'
						Please click this link to see your appointment.
						------------------------
						In there, click on the button "Confirm Appointment".
								
						';
						
						$url = "http://www.confidr.com/verify.php?email={$md5email}&hash={$messages_token}";
						$email_creator = new Email_Creator();
						
						$title = "Confirmation Email";
						$greet = "Welcome to ConfiDr! The first social network joining doctors and patients.";
						$confirmation_message = 'You have scheduled an appointment with your newly created account, however you should verify your email withtin 2 hours since you schedule it or it will be automatically CANCELED.
								
						Fear not! To keep your appointment, please click on "Activate Account" below and login to your account.';
						$button = "Activate Account";
						$confirmation_message_2 = 'THEN, TO CONFIRM YOUR APPOINTMENT, please click on "Go to Appointment" AFTER you are logged in. There, click on "Confirm Appointment"';
						$button_2 = "Go to Appointment";
						$url_2 = "http://www.confidr.com/patient_appointment_viewer.php?cid='.$consult_id.'";
						$confirmation_message_3 = "That's it! Don't forget your appointment.";
						
						$message_html = $email_creator->confirmation_email_2_buttons($title, $greet,
								$confirmation_message, $button, $url, $confirmation_message_2,
								$button_2, $url_2, $confirmation_message_3);
						break;
						
					case("es"):
						$subject = 'ConfiDr. Verificación de Correo'; // Give the email a subject
						$message = '
								
						Bienvenido a ConfiDr! La primera red social que une doctores y pacientes.
						Acabas de hacer una cita con tu recientemente creada cuenta, sin embargo
						aún debes verificar tu correo dentro de máximo 2 horas desde que hiciste
						la cita, de lo contrario esta será automaticamente CANCELADA.
								
						¡Nada que temer! Para que esto no ocurra, solo has click en el link
						acontinuación e inicia sesión.
								
						------------------------
						http://www.confidr.com/verify.php?email='.$md5email.'&hash='.$final_mess_token.'
						Has click en este link para activar tu cuenta.
						------------------------
								
								
						LUEGO, PARA CONFIRMAR TU CITA, has click en el siguiente link, una vez ya
						hayas verificado tu cuenta (link de arriba) e iniciado sesión en confidr.com .
						------------------------
						http://www.confidr.com/patient_appointment_viewer.php?cid='.$consult_id.'
						Has click en este link para ver tu cita.
						------------------------
						Una vez allí, has click en "Confirmar Cita".
								
						';
						
						$url = "http://www.confidr.com/verify.php?email={$md5email}&hash={$messages_token}";
						$email_creator = new Email_Creator();
						
						$title = "Email Confirmación";
						$greet = "¡Bienvenido a ConfiDr.! La primera red social que une doctores y pacientes.";
						$confirmation_message = 'Acabas de hacer una cita con tu recientemente creada cuenta, sin embargo
						aún debes verificar tu correo dentro de máximo 2 horas desde que hiciste
						la cita, de lo contrario esta SERÁ automaticamente CANCELADA. <br>
						Para que esto NO OCURRA, solo has click en "Confirmar Correo" y luego inicia sesión.
						';
						$button = "Confirmar Correo";
						$confirmation_message_2 = 'LUEGO, PARA CONFIRMAR TU CITA, has click en "Ir a Cita", una vez hayas iniciado sesión, y allí has click en "Confirmar Cita".';
						$button_2 = "Ir a Cita";
						$url_2 = "http://www.confidr.com/patient_appointment_viewer.php?cid='.$consult_id.'";
						$confirmation_message_3 = '¡Eso es todo! No olvides tu cita.';
						
						$message_html = $email_creator->confirmation_email_2_buttons($title, $greet,
								$confirmation_message, $button, $url, $confirmation_message_2,
								$button_2, $url_2, $confirmation_message_3);
						break;
				}
				
				//TODO:Comment for deployment, uncomment for test
				//echo "REMOVE: localhost/confidr/verify.php?email=".$md5email."&hash=".$messages_token;
				
				$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
				
				try {
					//Server settings
					$mail->CharSet = 'UTF-8';
					$mail->SMTPDebug = 0;                                 // Enable verbose debug output
					$mail->isSMTP();                                      // Set mailer to use SMTP
					$mail->Host = 'smtp.1and1.com';                       // Specify main and backup SMTP servers
					$mail->SMTPAuth = true;                               // Enable SMTP authentication
					
					$mail->Username = 'no-reply.confidr@jimenezd.com';                 // SMTP username
					$mail->Password = '/|/euRo#$!Na/pti_C#2017';                           // SMTP password
					
					$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
					$mail->Port = 587;                                    // TCP port to connect to
					
					//Recipients
					$mail->setFrom('no-reply.confidr@jimenezd.com', 'Support-ConfiDr');
					$mail->addAddress($to, $txt_rep->entities($fname . " " . $lname));     // Add a recipient
					$mail->addReplyTo('no-reply.confidr@jimenezd.com', 'Support-ConfiDr');
					
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
				
				$hours = 2;
			}
		}

		//Schedule deletion after 24 hour

		$tab_db = $database_name . "." . $appointments_table;
		$tan_db = mysqli_real_escape_string($con,$tab_db);
		$tab_db_pat = $database_name . "." . $appointments_table_pat;
		$tan_db_pat = mysqli_real_escape_string($con,$tab_db);

		$query = mysqli_query($con,"CREATE EVENT $schedule_id ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL $hours HOUR
    		DO
      		DELETE FROM $tab_db WHERE consult_id ='$consult_id' AND confirmed_pat=0");
		
		$query = mysqli_query($con,"CREATE EVENT $schedule_id_pat ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL $hours HOUR
    		DO
      		DELETE FROM $tab_db_pat WHERE consult_id ='$consult_id' AND confirmed_pat=0");
		
		
		//Schedule send reeschedule email
		$_added_secs = 60*60*$hours;
		
		$sql_query = "SELECT * FROM $appointments_table WHERE consult_id ='$consult_id' AND confirmed_doc=0";
		$sql_query2 = "SELECT * FROM $appointments_details WHERE consult_id ='$consult_id'";
		//$sql_query3 = "DELETE FROM $appointments_table_pat WHERE consult_id ='$consult_id'"; //Delete from patient's table (NOT DONE)
		//$sql_query4 = "DELETE FROM $appointments_table WHERE consult_id ='$consult_id'";
		
		$exec_time = date('ymdHis', time()+$_added_secs);
		$scheduler_id = strtotime(date('Y-m-d H:i:s')) . rand(0,9) . rand(0,9);
		$scheduler_id_int = (String)$scheduler_id;
		
		$stmt = $con->prepare("INSERT INTO `scheduler` (`id`, `type`, `execution_time`, `query`, `query2`, `query3`, `query4`) VALUES (?,'1',?,?,?,'','')");
		$stmt->bind_param("ssss",$scheduler_id_int,$exec_time,$sql_query,$sql_query2);
		$stmt->execute();
		
		$_SESSION['one_appointment_limit'] = 1;
		
		echo $consult_id;
	}

?>