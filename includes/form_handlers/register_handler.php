<?php

//Mailer setup

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/packages/PHPMailer/src/Exception.php';
require 'includes/packages/PHPMailer/src/PHPMailer.php';
require 'includes/packages/PHPMailer/src/SMTP.php';

$nnpswd = "<-></\|eUro#/\|apt!C-2017!4!2394%nnY";

//Declaring variables to prevent errors
$fname = ""; //First Name
$lname = ""; //Last Name
$em = ""; //email
$em2 = ""; //email 2
$passwrd = ""; //password
$passwrd2 = ""; //password 2
$date = ""; //Sign up date
$error_array = array(); //Holds error messages


if(isset($_SESSION['lang'])){
	$lang = $_SESSION['lang'];
}
else{
	$lang = "es";
	$_SESSION['lang'] = "es";
}

//doctor calendar set
$blocks_in_an_hour = 2;
$minutes_per_block = 60/$blocks_in_an_hour;
$start_time = "6:00";
$end_time = "21:00";
$dteStart = new DateTime($start_time);
$dteEnd   = new DateTime($end_time);
$hours_per_day = $dteStart->diff($dteEnd)->format("%H");
//$hours_per_day = strtotime($end_time)-strtotime($start_time);
$num_blocks = $hours_per_day*$blocks_in_an_hour;

$var_time = "";
$txt_rep = new TxtReplace();
$crypt = new Crypt();


if(isset($_POST['register_button_doctor'])){
	
    $_SESSION['register_div']="doctor";
	//Registration form values

	//First Name
	$fname = strip_tags($_POST['reg_fname']);//Remove html tags
	$fname = mysqli_real_escape_string($con,$fname);
	$fname= preg_replace("/[^\p{Xwd} ]+/u", "",$fname);
	$fname = ucwords(strtolower($fname)); //Uppercase first letter
	$_SESSION['reg_fname'] = $fname; //Stores first name into session variable

	//Last Name
	$lname = strip_tags($_POST['reg_lname']);//Remove html tags
	$lname = mysqli_real_escape_string($con,$lname);
	$lname= preg_replace("/[^\p{Xwd} ]+/u", "",$lname);
	$lname = ucwords(strtolower($lname)); //Uppercase first letter
	$_SESSION['reg_lname'] = $lname; //Stores last name into session variable

	//Sex
	$sex = strip_tags($_POST['doc_sex_selected']);//Remove html tags
	$sex = mysqli_real_escape_string($con,$sex);
	
	//email
	$em = strip_tags($_POST['reg_email']);//Remove html tags
	$em = str_replace(' ', '', $em); //Remove spaces
	$em = preg_replace('/[^a-zA-Z0-9_.@-]/', '', $em);//Remove special characters (even inputed with ascii)
	$_SESSION['reg_email'] = $em; //Stores email into session variable


	//email2
	$em2 = strip_tags($_POST['reg_email2']);//Remove html tags
	$em2 = str_replace(' ', '', $em2); //Remove spaces THIS WE MIGHT NOT NEED
	$em2 = preg_replace('/[^a-zA-Z0-9_.@-]/', '', $em2);
	$_SESSION['reg_email2'] = $em2; //Stores email 2 into session variable


	//passwrd
	$passwrd = strip_tags($_POST['reg_passwrd']);//Remove html tags

	//passwrd2
	$passwrd2 = strip_tags($_POST['reg_passwrd2']);//Remove html tags

	$date = date("Y-m-d H:i:s"); //Gets current date

	//EMAIL CHECK

	if($em == $em2){
		// Check if email is valid format
		if((!filter_var($em, FILTER_VALIDATE_EMAIL) === false) && (!preg_match('/[^a-zA-Z0-9_.@-]/', $em))){
			$em = filter_var($em, FILTER_VALIDATE_EMAIL);
			//Check if email already exists.

			//select email column from the users table, where email column = variable $em.
			$stmt = $con->prepare("SELECT email FROM users WHERE email = ?");
			$stmt->bind_param("s", $em);
			$stmt->execute();

			$e_check = $stmt->get_result();

			//$e_check = mysqli_query($con,"SELECT email FROM users WHERE email = '$em'"); //select email column from the users table, where email column = variable $em.

			//Count number of rows returned from the query.

			$num_rows = mysqli_num_rows($e_check);

			if($num_rows > 0){
				array_push($error_array, "Email already in use.<br>") ;
			}
		}
		else{
			array_push($error_array, "Email Invalid Format.<br>") ; 
		}

	}
	else {
		array_push($error_array, "Emails don't match.<br>") ;
	}

	//Name check
	if(strlen($fname) > 25 || strlen($fname) < 2){
		array_push($error_array, "Your first name must be between 2 and 25 characters.<br>") ;
	}
	else if(count(explode(" ", $fname)) > 2){
		array_push($error_array, "Your name can only have 1 first name and 1 middle name maximum.<br>") ;
	}
	else if(preg_match('/[^\p{Xwd} ]/u', $fname)){
		array_push($error_array, "Your name must only contain characters and numbers.<br>") ;
	}
	else if(preg_replace("/[\s,]+/","",$fname) == ""){
		array_push($error_array, "Your name cannot be empty.<br>") ;
	}

	if(strlen($lname) > 25 || strlen($lname) < 2){
		array_push($error_array, "Your last name must be between 2 and 25 characters.<br>") ;
	}
	else if(count(explode(" ", $lname)) > 2){
		array_push($error_array, "Your last name can only have your family name and a second family name maximum.<br>") ;
	}
	else if(preg_match('/[^\p{Xwd} ]/u', $lname)){
		array_push($error_array, "Your last name must only contain characters and numbers.<br>") ;
	}
	else if(preg_replace("/[\s,]+/","",$lname) == ""){
		array_push($error_array, "Your last name cannot be empty.<br>") ;
	}

	//Password check

	if($passwrd != $passwrd2){
		array_push($error_array, "Your passwords do not match.<br>");
	}

	if(strlen($passwrd) > 30 || strlen($passwrd) < 10){
		array_push($error_array, "Your password must be between 10 and 30 characters.<br>") ;
	}
	
	//Specialization
	
	$speciali = $_POST['specialization_code'];
	
	$specialization_text = $_POST['search_text_input_specialization'];
	
	
	$specialization_codes_array1 = [];
	
	$specializations_verb = [];
	
	$speciali= rtrim($speciali,',');
	
	$specialiArr = explode(',', $speciali);
	
	
	$stmt = $con->prepare("SELECT id,en,es FROM specializations WHERE id = ?");
	$stmt->bind_param("s",$temp_arr_elem);
	
	$spe_count = 0;
	foreach ($specialiArr as $temp_arr_elem){
		$stmt->execute();
		$q = $stmt->get_result();
		if(mysqli_num_rows($q) == 0 && $specialization_text != ''){
			array_push($error_array, "specialization_not_found");
		}
		elseif($temp_arr_elem != ''){
			$_fetched_array_specializations = mysqli_fetch_assoc($q);
			//$id = mysqli_fetch_array($q)['id'];
			$id = $_fetched_array_specializations['id'];
			$specialization_codes_array1[] = "$id";
			$_deli_ = "\\";
			$_temp_vrb_ = $_fetched_array_specializations[$lang];
			$_temp_exp = explode($_deli_, $_temp_vrb_);
	
			$specializations_verb[] = $_temp_exp[0];
		}
		
		$spe_count++;
		
		if($spe_count >= 3){
			break;
		}
	}
	
	$specialization_id_serialized = serialize($specialization_codes_array1);
	//echo $specialization_id_serialized;
	//Country
	
	$reg_adcountry = strip_tags($_POST['reg_adcountry']);//Remove html tags
	$_SESSION['reg_adcountry'] = $reg_adcountry;
    
	//$str_err_arr = print_r($error_array);
	//$query = mysqli_query($con, "INSERT INTO `debug`(`id`, `text`) VALUES ('',$str_err_arr)");
    if(empty($error_array)){
    		$_SESSION['no_errors'] = 1;
	    	$error_array = array();
	    	$_SESSION['reg_fname'] = "";
	    	$_SESSION['reg_lname'] = "";
	    	$_SESSION['log_email']=$_SESSION['reg_email'];
	    	$_SESSION['reg_email'] = "";
	    	$_SESSION['reg_email2'] = "";
	    	$_SESSION['reg_ad1ln1'] = "";
	    	$_SESSION['reg_ad1ln2'] = "";
	    	$_SESSION['reg_ad1city'] = "";
	    	$_SESSION['reg_ad1adm2'] = "";
	    	$_SESSION['reg_adcountry'] = "";
	    	$_SESSION['searched_insurance1'] = "";
	    	$_SESSION['cost_appo'] = "";
		$_SESSION['register_div']="login";
		

        //$query = mysqli_query($con, "INSERT INTO `debug`(`id`, `text`) VALUES ('','Here!')");
		//$passwrd = md5($passwrd); //Encrypt password before sending to database.
		$passwrd = password_hash($passwrd, PASSWORD_BCRYPT);
		//Generate username.

		$rand_part = date("ymdHis") . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);

		$username = $rand_part; //. strtolower(substr(str_replace(' ', '', $fname), 0, 1) . substr(str_replace(' ', '', $lname), 0, 1));
        
		$username_e = $crypt->EncryptU($username);
		$stmt = $con->prepare("SELECT username FROM users WHERE username=?");
		$stmt->bind_param("s", $username_e);
		$stmt->execute();

		$check_username_query = $stmt->get_result();
		//$check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");

		$i = 0;
		//if username exists add number to username

		while(mysqli_num_rows($check_username_query) != 0){
			 $rand_part = date("ymdHis") . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);

			$usernameT = $rand_part;
			$usernameT_e = $crypt->EncryptU($usernameT, $key);

			//$stmt = $con->prepare("SELECT username FROM users WHERE username=?");
			$stmt->bind_param("s", $usernameT_e);
			$stmt->execute();
			$check_username_query = $stmt->get_result();

			if(mysqli_num_rows($check_username_query) == 0){
				$username = $usernameT;
				$username_e = $usernameT_e;
				break;
			}
		}

		//Profile picture assignment
		
// 		$pth_str = explode("/",$_SERVER['DOCUMENT_ROOT']);
		
// 		$path = "";
// 		for($i=0;$i<= count($pth_str)-2 ;$i++){
// 			$path .= $pth_str[$i] . "/";
// 		}
// 		$path .= "images/profile_pics/";
		$path = "/home/bitnami/images/profile_pics/";
		
		if($sex == "m"){
			$profile_pic = $path . "defaults/doctor_male.png";
		}
		elseif($sex == "f"){
			$profile_pic = $path . "defaults/doctor_female.png";
		}
		else{
			$profile_pic = $path . "defaults/doctor_male.png";
		}
		
		$salt = "jgsad87KJH23JHG235KdBJHh9786723876AFfss";
		$hash = hash_pbkdf2("sha256", $nnpswd . $username, $date, 20000 , 32 , FALSE);
		$thash = hash_pbkdf2("sha256", $nnpswd .$salt. $hash, $salt, 20000 , 32 , FALSE);
		
		//Table Creation
		
		$comments = $hash. "__comments";//
		$connection_requests = $hash. "__connection_requests";//
		$likes = $hash. "__likes";//
		$messages = $hash. "__messages";//
		$messages_status = $hash. "__messages_status";//
		$notifications = $hash. "__notifications";//
		$posts = $hash. "__posts";//
		$connections = $hash. "__connections";//
		$basic_info = $hash. "__basic_info";//
		$appointments_calendar_patient = $hash. "__appointments_calendar_patient";//
		$appointments_pat = $hash. "__appointments_details_pat";//
		$symptoms_pat = $hash. "__symptoms_pat";//
		$medicines_pat = $hash. "__medicines_pat";//
		$settings= $hash. "__settings";
		$payments = $hash. "__payments";
		$payments_hist = $hash. "__payments_hist";

		//Doctor tables
// 		$doc_education = $username . "__doc_education";//
// 		$doc_certificates = $username . "__doc_certificates";
// 		$congresses_private_tab = $username . "__congresses_private_tab";
// 		$appo_duration_tab = $username . "__appo_duration_tab";
// 		$calendar_availability = $username . "__calendar_availability";
// 		$appointments_calendar_doc = $username . "__appointments_calendar_doc";
// 		$appointments_doc = $username . "__appointment_details_doc";
// 		$symptoms_doc = $username . "__symptoms_doc";
// 		$medicines_doc = $username . "__medicines_doc";
		
		
		$rips = $hash. "__rips";
		$education = $hash. "__education";//
		$jobs = $hash. "__jobs";//
		$conferences = $hash. "__conferences";//
		$description = $hash. "__description";//
		$awards = $hash. "__awards";//
		$publications = $hash. "__publications";//
		$webpages = $hash. "__webpages";//
		
		//TODO:Hash particular para los posts y comments. No, mejor encryptar posts y comments
		
		$telephone = $thash . "__telephones";//
		
		//Register user
		$messages_token = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
		$messages_token = md5(date("ymdHis") . md5($messages_token));
		
		$stmt = $con->prepare("INSERT INTO users VALUES (?, ?, ?, ?, ?, ?, ?, ?, '0', '0', 'no', ?, ?,1,?,0)");
		$fname_l = strtolower($fname);
		$lname_l = strtolower($lname);
		$stmt->bind_param("sssssssssss", $fname, $lname, $username_e, $em, $passwrd, $date, $reg_adcountry,$profile_pic, $fname_l, $lname_l,$messages_token);
		$stmt->execute();
		
		$md5email = md5($em);
		
		$to      = $em; // Send email to our user
		switch ($lang){
			
			case("en"):
				$subject = 'ConfiDr. Email Verification'; // Give the email a subject
				$message = '
						
				Welcome to ConfiDr! The first social network joining doctors and patients.
				Your account has been created, you can activate it by clicking in the followng link:
						
				------------------------
				http://www.confidr.com/verify.php?email='.$md5email.'&hash='.$messages_token.'
				Please click this link to activate your account
				------------------------

				';
				
				$url = "http://www.confidr.com/verify.php?email={$md5email}&hash={$messages_token}";
				$email_creator = new Email_Creator();
				
				$title = "Confirmation Email";
				$greet = "Welcome to ConfiDr! The first social network joining doctors and patients.";
				$confirmation_message = "Your account has been created, you can activate it by clicking in the followng link:";
				$button = "Activate Account";
				$confirmation_message_2 = '';
				
				$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
						$button, $url, $confirmation_message_2);
				break;
				
			case("es"):
				$subject = 'ConfiDr. Verificación de Correo'; // Give the email a subject
				$message = '
						
				Bienvenido a ConfiDr! La primera red social que une doctores y pacientes.
				Tu cuenta ha sido creada, ahora puedes ingresar entrando al siguiente link:
						
				------------------------
				http://www.confidr.com/verify.php?email='.$md5email.'&hash='.$messages_token.'
				Has click en este link para activar tu cuenta.
				------------------------
						
				';
				
				$url = "http://www.confidr.com/verify.php?email={$md5email}&hash={$messages_token}";
				$email_creator = new Email_Creator();
				
				$title = "Email Confirmación";
				$greet = "¡Bienvenido a ConfiDr.! La primera red social que une doctores y pacientes.";
				$confirmation_message = "Tu cuenta ha sido creada, ahora sólo debes confirmar tu correo haciendo click aquí:";
				$button = "Confirmar Correo";
				$confirmation_message_2 = '';
				
				$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
						$button, $url, $confirmation_message_2);
				break;
		}
		
		//echo "REMOVE: localhost/confidr/verify.php?email=".$md5email."&hash=".$messages_token;
		
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
			//TODO: Comentar la siguiente linea para permitir que los correos de confirmación lleguen a los usuarios y no a confidr para moderación.
			//$mail->addAddress($to, $txt_rep->entities($fname . " " . $lname));     // Add a recipient
			$mail->addAddress("neuronapticsas@gmail.com", $txt_rep->entities($fname . " " . $lname));     // Add a recipient
			$mail->addAddress("davidrumsjb@icloud.com", $txt_rep->entities($fname . " " . $lname));
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

		$stmt = $con->prepare("INSERT INTO `basic_info_doctors` (`username`,`specializations`,`sex`,`up_to_date`,`adcountry`,`payment_expiration_date`,md_conn,pat_conn,pat_seen,pat_foll,pat_inter,pat_rec) VALUES (?,?,?,0,?,'',0,0,0,0,0,0)");
		$stmt->bind_param("ssss",$username,$specialization_id_serialized,$sex,$reg_adcountry);
		$stmt->execute();
		
		$stmt->close();
		
		//QUERIES
		
		//Comments Tables
		
		$query = mysqli_query($con,
			"CREATE TABLE $telephone (
			id INT(3) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			telephone varchar(100) NOT NULL,
			office_num INT(1)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		//Social Tables
		
		$query = mysqli_query($con,
			"CREATE TABLE $posts (
			body BLOB NOT NULL,
			added_by varchar(100) NOT NULL,
			user_to	varchar(100) NOT NULL,
			date_added	datetime NOT NULL,
			user_closed	varchar(3) NOT NULL,
			deleted	varchar(3) NOT NULL,
			likes int(8) NOT NULL,
			global_id varchar(115) NOT NULL PRIMARY KEY
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $comments (
			id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			comment_global_id CHAR(32),
			post_body TEXT NOT NULL,
			posted_by VARCHAR(100) NOT NULL,
			posted_to VARCHAR(100) NOT NULL,
			date_added datetime NOT NULL,
			removed VARCHAR(3) NOT NULL,
			post_id varchar(115) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $connection_requests (
			id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_to VARCHAR(100) NOT NULL,
			user_from VARCHAR(100) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $likes (
			id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			username VARCHAR(100) NOT NULL,
			post_id varchar(115) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $messages (
			id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_to VARCHAR(100) NOT NULL,
			user_from VARCHAR(100) NOT NULL,
			body BLOB NOT NULL,
			date datetime NOT NULL,
			opened	varchar(3) NOT NULL,
			viewed	varchar(3) NOT NULL,
			deleted	varchar(3) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $messages_status (
			secondary_interlocutor VARCHAR(100) NOT NULL PRIMARY KEY,
			enabled bit(1) NOT NULL,
			payed bit(1),
			payed_messages_cost int(7),
			date_payed datetime,
			accepted_payment bit(1),
			date_activated datetime,
			date_termination datetime,
			bill_number varchar(16),
			message_desc varchar(140),
			score int(1)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $notifications (
			id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			user_to VARCHAR(100) NOT NULL,
			user_from VARCHAR(100) NOT NULL,
			message	BLOB NOT NULL,
			link varchar(100) NOT NULL,
			datetime datetime NOT NULL,
			opened	varchar(3) NOT NULL,
			viewed	varchar(3) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $connections (
			id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			username_friend varchar(100),
			doctor bit(1) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		
	//SETTINGS TABLE
	
		$query = mysqli_query($con,
			"CREATE TABLE $settings (
			id INT(1) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			lang varchar(2),
			payed_messages bit(1),
			payed_messages_cost int(7),
			profile_privacy bit(1),
			last_login datetime
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$stmt = $con->prepare("INSERT INTO $settings (`id`,`lang`,`payed_messages`,`payed_messages_cost`,`profile_privacy`) VALUES ('',?,'','','0')");
		$stmt->bind_param("s",$lang);
		$stmt->execute();
		
	//Payments TABLE
		
		$query = mysqli_query($con,
				"CREATE TABLE $payments (
				id INT(1) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name varchar(32),
				cc_token varchar(32),
				client_token varchar(32),
				cc_4_dig varchar(4),
				email varchar(25),
				docu_number varchar(10),
				rec_pay_email varchar(25),
				phone varchar(10),
				ip varchar(45)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
			);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $payments_hist (
				bill_number varchar(16) PRIMARY KEY,
				cc_token varchar(32),
				description varchar(35),
				amount int(7),
				datetime_issued datetime,
				datetime_payed datetime,
				charged varchar(1), /*n: Pending, y: charged*/
				payed varchar(1) /*n: Pending, y: paid*/
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
	//Doctor tables

// 		$query = mysqli_query($con,
// 			"CREATE TABLE $doc_education (
// 			id int(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
// 			username VARCHAR(100) NOT NULL,
// 			school int(9) NOT NULL /*--references public table*/,
// 			degree int(9) NOT NULL /*references public table*/,
// 			starting_date date,
// 			graduation_date date,
// 			description BLOB,
// 			achievements BLOB,

// 			CONSTRAINT $fk_username_edu FOREIGN KEY(username) REFERENCES users(username),
// 			CONSTRAINT $fk_school_edu FOREIGN KEY(school) REFERENCES schools(id),
// 			CONSTRAINT $fk_degree_edu FOREIGN KEY(degree) REFERENCES degree(id)
// 			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
// 		);

// 		$query = mysqli_query($con,
// 			"CREATE TABLE $doc_certificates (
// 			id int(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
// 			username VARCHAR(100) NOT NULL,
// 			certification int(9) NOT NULL,
// 			issuer int(9) NOT NULL /*--references public table*/,
// 			issue_date date,
// 			description BLOB,

// 			CONSTRAINT $fk_username_cert FOREIGN KEY(username) REFERENCES users(username),
// 			CONSTRAINT $fk_cert_cert FOREIGN KEY(certification) REFERENCES certifications(id),
// 			CONSTRAINT $fk_issuer_cert FOREIGN KEY(issuer) REFERENCES cert_issuer(id)
// 			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
// 		);

// 		$query = mysqli_query($con,
// 			"CREATE TABLE $congresses_private_tab (
// 			id int(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
// 			username VARCHAR(100) NOT NULL,
// 			congress_name VARCHAR(60) NOT NULL,
// 			start_date date,
// 			end_date date,
// 			description BLOB,
// 			congress_username VARCHAR(100),

// 			CONSTRAINT $fk_username_congresses FOREIGN KEY(username) REFERENCES users(username)
// 			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
// 		);	
		
		//About tables
		
		$query = mysqli_query($con,
			"CREATE TABLE $education (
			id int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			title_obtained VARCHAR(70),
			institution VARCHAR(70),
			start_date date,
			end_date date
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);

		$query = mysqli_query($con,
			"CREATE TABLE $jobs(
			id int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			title VARCHAR(70),
			institution VARCHAR(70),
			start_date date,
			end_date date
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $conferences (
			id int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			title VARCHAR(70),
			role VARCHAR(70),
			date date
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $description (
			id int(2) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			description VARCHAR(120)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $awards (
			award_code int(2) NOT NULL PRIMARY KEY,
			votes int(5) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);

		$query = mysqli_query($con,
			"CREATE TABLE $publications (
			id int(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			title varchar(200) NOT NULL,
			main_authors varchar(250) NOT NULL,
			journal varchar(200),
			page_vol varchar(50),
			year int(4)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
			"CREATE TABLE $webpages (
			web_page_code int(1) NOT NULL PRIMARY KEY,
			url varchar(100) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		echo mysqli_error($con);
		
		//array_push($error_array, "<span style='color: #14C800'> You are all set. Please login. </span><br>");

		//Add profile links
		
		//TODO: verbal profile links
		$link_specia = $specializations_verb[0];
		$link_specia = str_replace(" ", "-", $link_specia);
		$link_fname = str_replace(" ", "-", $fname_l);
		$link_lname = str_replace(" ", "-", $lname_l);
		
		$link_verbal_final = $link_specia . "-" . $link_fname . "-" . $link_lname;
		$link_verbal_final = strtolower($link_verbal_final);
		
		$conv_table = array(
				'á'=>'a','é'=>'e','ó'=>'o','í'=>'i','ú'=>'u','ñ'=>'n','ü'=>'u'
		);
		
		$link_verbal_final = strtr($link_verbal_final, $conv_table);
		
		$stmt = $con->prepare("INSERT INTO `profile_register`(`verbal_rich_link`, `custom_link`, `username`) VALUES (?,?,?)");
		$stmt->bind_param("sss", $link_verbal_final,$link_verbal_final,$username_e);
		$stmt->execute();
		
		//Clear session variables
		$_SESSION['reg_fname'] = "";
		$_SESSION['reg_lname'] = "";
		$_SESSION['log_email']=$_SESSION['reg_email'];
		$_SESSION['reg_email'] = "";
		$_SESSION['reg_email2'] = "";
		$_SESSION['reg_ad1ln1'] = "";
		$_SESSION['reg_ad1ln2'] = "";
		$_SESSION['reg_ad1city'] = "";
		$_SESSION['reg_ad1adm2'] = "";
		$_SESSION['reg_adcountry'] = "";
		$_SESSION['searched_insurance1'] = "";
		$_SESSION['cost_appo'] = "";
		
		$error_array = array();
	}
}



if(isset($_POST['register_button_patient'])){
	
    $_SESSION['register_div']="patient";
	//Registration form values
	
	//First Name
	$fname = strip_tags($_POST['reg_fname']);//Remove html tags
	$fname = mysqli_real_escape_string($con,$fname);
	$fname = ucwords(strtolower($fname)); //Uppercase first letter
	$_SESSION['reg_fname'] = $fname; //Stores first name into session variable
	
	//Last Name
	$lname = strip_tags($_POST['reg_lname']);//Remove html tags
	$lname = mysqli_real_escape_string($con,$lname);
	$lname = ucwords(strtolower($lname)); //Uppercase first letter
	$_SESSION['reg_lname'] = $lname; //Stores last name into session variable
	
	
	//email
	$em = strip_tags($_POST['reg_email']);//Remove html tags
	$em = str_replace(' ', '', $em); //Remove spaces
	$em = preg_replace('/[^a-zA-Z0-9_.@-]/', '', $em);//Remove special characters (even inputed with ascii)
	$_SESSION['reg_email'] = $em; //Stores email into session variable
	
	
	//email2
	$em2 = strip_tags($_POST['reg_email2']);//Remove html tags
	$em2 = str_replace(' ', '', $em2); //Remove spaces THIS WE MIGHT NOT NEED
	$em2 = preg_replace('/[^a-zA-Z0-9_.@-]/', '', $em2);
	$_SESSION['reg_email2'] = $em2; //Stores email 2 into session variable
	
	
	//passwrd
	$passwrd = strip_tags($_POST['reg_passwrd']);//Remove html tags
	
	//passwrd2
	$passwrd2 = strip_tags($_POST['reg_passwrd2']);//Remove html tags
	
	//Country
	
	$reg_adcountry = strip_tags($_POST['reg_adcountry']);//Remove html tags
	$_SESSION['reg_adcountry'] = $reg_adcountry;
	
	$date = date("Y-m-d H:i:s"); //Gets current date
	
	
	//EMAIL CHECK
	
	if($em == $em2){
		// Check if email is valid format
		if((!filter_var($em, FILTER_VALIDATE_EMAIL) === false) && (!preg_match('/[^a-zA-Z0-9_.@-]/', $em))){
			$em = filter_var($em, FILTER_VALIDATE_EMAIL);
			//Check if email already exists.
			
			//select email column from the users table, where email column = variable $em.
			$stmt = $con->prepare("SELECT email FROM users WHERE email = ?");
			$stmt->bind_param("s", $em);
			$stmt->execute();
			
			$e_check = $stmt->get_result();
			
			//$e_check = mysqli_query($con,"SELECT email FROM users WHERE email = '$em'"); //select email column from the users table, where email column = variable $em.
			
			//Count number of rows returned from the query.
			
			$num_rows = mysqli_num_rows($e_check);
			//echo "Numrows=" . $num_rows;
			if($num_rows > 0){
				array_push($error_array, "Email already in use.<br>") ;
			}
		}
		else{
			array_push($error_array, "Email Invalid Format.<br>") ;
		}
		
	}
	else {
		array_push($error_array, "Emails don't match.<br>") ;
	}
	
	//Name check
	if(strlen($fname) > 25 || strlen($fname) < 2){
		array_push($error_array, "Your first name must be between 2 and 25 characters.<br>") ;
	}
	else if(count(explode(" ", $fname)) > 2){
		array_push($error_array, "Your name can only have 1 first name and 1 middle name maximum.<br>") ;
	}
	else if(preg_match('/[^\p{Xwd} ]/u', $fname)){
		array_push($error_array, "Your name must only contain characters and numbers.<br>") ;
	}
	else if(preg_replace("/[\s,]+/","",$fname) == ""){
		array_push($error_array, "Your name cannot be empty.<br>") ;
	}
	
	if(strlen($lname) > 25 || strlen($lname) < 2){
		array_push($error_array, "Your last name must be between 2 and 25 characters.<br>") ;
	}
	else if(count(explode(" ", $lname)) > 2){
		array_push($error_array, "Your last name can only have your family name and a second family name maximum.<br>") ;
	}
	else if(preg_match('/[^\p{Xwd} ]/u', $lname)){
		array_push($error_array, "Your last name must only contain characters and numbers.<br>") ;
	}
	else if(preg_replace("/[\s,]+/","",$lname) == ""){
		array_push($error_array, "Your last name cannot be empty.<br>") ;
	}
	
	//Password check
	
	if($passwrd != $passwrd2){
		array_push($error_array, "Your passwords do not match.<br>");
	}
	
	if(strlen($passwrd) > 30 || strlen($passwrd) < 5){
		array_push($error_array, "Your password must be between 5 and 30 characters.<br>") ;
	}
	
	
	
	if(empty($error_array)){
		$_SESSION['no_errors'] = 1;
		$error_array = [];
		$_SESSION['reg_fname'] = "";
		$_SESSION['reg_lname'] = "";
		$_SESSION['log_email']=$_SESSION['reg_email'];
		$_SESSION['reg_email'] = "";
		$_SESSION['reg_email2'] = "";
	    $_SESSION['register_div']="login";
	    
		//$passwrd = md5($passwrd); //Encrypt password before sending to database.
	    $passwrd = password_hash($passwrd, PASSWORD_BCRYPT);
		//Generate username by concatenating first name and last name.
		
		$rand_part = date("ymdHis") . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
		
		$username = $rand_part; //. strtolower(substr(str_replace(' ', '', $fname), 0, 1) . substr(str_replace(' ', '', $lname), 0, 1));
		$username_e = $crypt->EncryptU($username);
		$stmt = $con->prepare("SELECT username FROM users WHERE username=?");
		$stmt->bind_param("s", $username_e);
		$stmt->execute();
		
		$check_username_query = $stmt->get_result();
		//$check_username_query = mysqli_query($con, "SELECT username FROM users WHERE username='$username'");
		
		
		$i = 0;
		//if username exists add number to username
		
		while(mysqli_num_rows($check_username_query) != 0){
			$rand_part = date("ymdHis") . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
			
			$usernameT = $rand_part;
			$usernameT_e = $crypt->EncryptU($usernameT, $key);
			//$stmt = $con->prepare("SELECT username FROM users WHERE username=?");
			$stmt->bind_param("s", $usernameT_e);
			$stmt->execute();
			$check_username_query = $stmt->get_result();
			
			if(mysqli_num_rows($check_username_query) == 0){
				$username = $usernameT;
				break;
			}
		}
		
		//Profile picture assignment
		$rand = rand(1,2); //Rand num between 1 and 2.
		
// 		$pth_str = explode("/",$_SERVER['DOCUMENT_ROOT']);
		
// 		$path = "";
// 		for($i=0;$i<= count($pth_str)-2 ;$i++){
// 			$path .= $pth_str[$i] . "/";
// 		}
// 		$path .= "images/profile_pics/";
        $path = "/home/bitnami/images/profile_pics/";
		
        if($rand == 1){
            $profile_pic = $path . "defaults/doctor_male.png";
        }
		else if ($rand == 2){
			$profile_pic = $path . "defaults/doctor_female.png";
		}
		
		$salt = "som/|/euR@N@p7iC84Ltm(8)";
		$hash =  hash_pbkdf2("sha256", $nnpswd . $username, $date, 20000 , 32 , FALSE);
		$uhash = hash_pbkdf2("sha256", $nnpswd .$salt. $hash, $salt, 20000 , 32 , FALSE);
		//Table Creation
		
		$weight = $uhash. "__weight";//
		$height = $uhash. "__height";//
		$bmi = $uhash. "__bmi";//
		$blood_pressure = $uhash. "__blood_pressure";//
		$blood_sugar = $uhash. "__blood_sugar";//
		//$habits = $username . "__habits";//
		$comments = $hash. "__comments";//
		$connection_requests = $hash. "__connection_requests";//
		$likes = $hash. "__likes";//
		$messages = $hash. "__messages";//
		$notifications = $hash. "__notifications";//
		$posts = $hash. "__posts";//
		$connections = $hash. "__connections";//
		$basic_info = $hash. "__basic_info";//
		$appointments_calendar_patient = $hash. "__appointments_calendar_patient";//
		$appointments_pat = $hash. "__appointments_details_pat";//
		$symptoms_pat = $hash. "__symptoms_pat";//
		$medicines_pat = $hash. "__medicines_pat";//

		$awards = $hash. "__awards_patient";
		$settings= $hash. "__settings";
		$payments = $hash. "__payments";
		$payments_hist = $hash. "__payments_hist";
		
		//Register user
		
		$messages_token = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
		$messages_token = md5(date("ymdHis") . md5($messages_token));
		
		$stmt = $con->prepare("INSERT INTO users VALUES (?, ?, ?, ?, ?, ?, ?, ?, '0', '0', 'no', ?, ?,2,?,0)");
		$fname_l = strtolower($fname);
		$lname_l = strtolower($lname);
		$stmt->bind_param("sssssssssss", $fname, $lname, $username_e, $em, $passwrd, $date, $reg_adcountry, $profile_pic, $fname_l, $lname_l, $messages_token);
		$stmt->execute();		
		
// 		$stmt = $con->prepare("INSERT INTO users_tables VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 0, 0, 0)");
// 		$stmt->bind_param("sssssssssssss", $username, $comments,$connection_requests,$likes,$messages,$notifications,$posts,$connections,$basic_info,$appointments_calendar_patient,$appointments_pat,$symptoms_pat,$medicines_pat);
// 		$stmt->execute();
		
		//QUERIES
		
		//Patient Health Tables
		
		$habits = $uhash. "__habits";
		$query = mysqli_query($con,
				"CREATE TABLE $habits(
				id int(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				smoking varchar(100),
				alcohol varchar(100),
				diet	 varchar(100),
				physical_activity varchar(100),
				other varchar(100),
				last_update DATETIME
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
		);
		
		$OBGYN = $uhash. "__OBGYN";
		$query = mysqli_query($con,
				"CREATE TABLE $OBGYN(
					id int(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					menarche int(2) NULL DEFAULT '-1',
					lmp date,
					cycles varchar(20),
					gestations int(2) NULL DEFAULT '-1',
					parity int(2) NULL DEFAULT '-1',
					abortions int(2) NULL DEFAULT '-1',
					csections int(2) NULL DEFAULT '-1',
					ectopic int(2) NULL DEFAULT '-1',
					menopause int(2) NULL DEFAULT '-1',
					birthcontrol varchar(30),
					mammography_date date,
					mammography_result varchar(80),
					last_update DATETIME
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$pathologies= $uhash. "__pathologies";
		$query = mysqli_query($con,
				"CREATE TABLE $pathologies(
				id int(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				illnesses VARCHAR(100),
				approx_date VARCHAR(10)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$surgical_trauma= $uhash. "__surgical_trauma";
		$query = mysqli_query($con,
				"CREATE TABLE $surgical_trauma(
				id int(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				surgical_trauma VARCHAR(100),
				approx_date VARCHAR(10)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$hereditary= $uhash. "__hereditary";
		$query = mysqli_query($con,
				"CREATE TABLE $hereditary(
				id int(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				diseases VARCHAR(100),
				relatives VARCHAR(100)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$pharmacology= $uhash. "__pharmacology";
		$query = mysqli_query($con,
				"CREATE TABLE $pharmacology(
				id int(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				medicines VARCHAR(100),
				dosage VARCHAR(100)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$allergies = $uhash. "__allergies";
		$query = mysqli_query($con,
				"CREATE TABLE $allergies (
				id int(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				allergies VARCHAR(100)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$query = mysqli_query($con,
				"CREATE TABLE $weight (
				PHID int(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				weight decimal(4,1) NOT NULL,
				date_time datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $height (
				PHID int(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				height decimal(3,2) NOT NULL,
				date_time datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $bmi (
				PHID int(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				BMI decimal(3,1) NOT NULL,
				date_time datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $blood_pressure (
				PHID int(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				BPSys int(3) NOT NULL,
				BPDia int(3) NOT NULL,
				date_time datetime NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
// 		$query = mysqli_query($con,
// 				"CREATE TABLE $blood_sugar (
// 				PHID varchar(18) NOT NULL PRIMARY KEY,
// 				Diabetes bit(1) NOT NULL,
// 				date_time datetime NOT NULL
// 				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
// 				);
		
// 		echo mysqli_error($con);
		
// 		$query = mysqli_query($con,
// 				"CREATE TABLE $habits (
// 				PHID varchar(18) NOT NULL PRIMARY KEY,
// 				smoker varchar(80) NOT NULL,
// 				drinker varchar(80) NOT NULL,
// 				diet varchar(80) NOT NULL,
// 				physical_activity varchar(80) NOT NULL,
// 				date_time datetime NOT NULL
// 				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
// 				);
		
// 		echo mysqli_error($con);
		
		
		//Social Tables
		
		$query = mysqli_query($con,
				"CREATE TABLE $posts (
				body BLOB NOT NULL,
				added_by varchar(100) NOT NULL,
				user_to	varchar(100) NOT NULL,
				date_added	datetime NOT NULL,
				user_closed	varchar(3) NOT NULL,
				deleted	varchar(3) NOT NULL,
				likes int(8) NOT NULL,
				global_id varchar(115) NOT NULL PRIMARY KEY
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$query = mysqli_query($con,
				"CREATE TABLE $comments (
				id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				comment_global_id CHAR(32),
				post_body TEXT NOT NULL,
				posted_by VARCHAR(100) NOT NULL,
				posted_to VARCHAR(100) NOT NULL,
				date_added datetime NOT NULL,
				removed VARCHAR(3) NOT NULL,
				post_id varchar(115) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$query = mysqli_query($con,
				"CREATE TABLE $connection_requests (
				id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_to VARCHAR(100) NOT NULL,
				user_from VARCHAR(100) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$query = mysqli_query($con,
				"CREATE TABLE $likes (
				id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				username VARCHAR(100) NOT NULL,
				post_id varchar(115) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$query = mysqli_query($con,
				"CREATE TABLE $messages (
				id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_to VARCHAR(100) NOT NULL,
				user_from VARCHAR(100) NOT NULL,
				body BLOB NOT NULL,
				date datetime NOT NULL,
				opened	varchar(3) NOT NULL,
				viewed	varchar(3) NOT NULL,
				deleted	varchar(3) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$query = mysqli_query($con,
				"CREATE TABLE $notifications (
				id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				user_to VARCHAR(100) NOT NULL,
				user_from VARCHAR(100) NOT NULL,
				message	BLOB NOT NULL,
				link varchar(100) NOT NULL,
				datetime datetime NOT NULL,
				opened	varchar(3) NOT NULL,
				viewed	varchar(3) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		$query = mysqli_query($con,
				"CREATE TABLE $connections (
				id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				username_friend varchar(100),
				doctor bit(1) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $awards (
				award_code int(2) NOT NULL,
				consult_id varchar(100) NOT NULL PRIMARY KEY
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		//patient appointments tables
		
		$query = mysqli_query($con,
				"CREATE TABLE $appointments_pat (
				consult_id varchar(100) NOT NULL PRIMARY KEY,
				payment_info VARCHAR(30) NOT NULL,
				appo_type int(3) NOT NULL,
				specializations varchar(3) NOT NULL,
				doctor_username VARCHAR(100) NOT NULL,
				patient_username VARCHAR(100) NOT NULL,
				cancelled_by_pat BIT(1) NOT NULL,
				cancelled_by_doc BIT(1) NOT NULL,
				reescheduled BIT(1),
				cost int(8),
				currency varchar (3),
				payed_through_confidr BIT(1),
				
				plan BLOB,
				private_plan BIT(1) NOT NULL,
				closed BIT(1) NOT NULL,
				office INT(1) NOT NULL
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $appointments_calendar_patient (
				year varchar(4) NOT NULL,
				month varchar(4) NOT NULL,
				day varchar(4) NOT NULL,
				time_start varchar(8) NOT NULL,
				time_end varchar(8) NOT NULL,
				consult_id varchar(100) NOT NULL PRIMARY KEY,
				confirmed_pat BIT(1) NOT NULL,
				confirmed_doc BIT(1) NOT NULL,
				creation_date_time datetime NOT NULL
				/*id int(5) NOT NULL AUTO_INCREMENT PRIMARY KEY,*/
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $symptoms_pat (
				consult_id varchar(100) NOT NULL,
				title VARCHAR(30) NOT NULL,
				description varchar(3000),
				start VARCHAR(30),
				frequency VARCHAR(50),
				id varchar(100) NOT NULL PRIMARY KEY
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $medicines_pat (
				consult_id varchar(100) NOT NULL,
				name VARCHAR(30) NOT NULL,
				dosage varchar(50),
				units varchar(30),
				description BLOB,
				start VARCHAR(30),
				frequency VARCHAR(50),
				id varchar(100) NOT NULL PRIMARY KEY
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);

		
		//SETTINGS TABLE
		
		$query = mysqli_query($con,
				"CREATE TABLE $settings (
				id INT(1) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				lang varchar(2),
				last_login datetime
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		$stmt = $con->prepare("INSERT INTO $settings (`id`,`lang`) VALUES ('',?)");
		$stmt->bind_param("s",$lang);
		$stmt->execute();

		
//PAYMENTS
		
		$query = mysqli_query($con,
				"CREATE TABLE $payments (
				id INT(1) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
				name varchar(32),
				cc_token varchar(32),
				client_token varchar(32),
				cc_4_dig varchar(4),
				email varchar(25),
				docu_number varchar(10),
				rec_pay_email varchar(25),
				phone varchar(10),
				ip varchar(45)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		$query = mysqli_query($con,
				"CREATE TABLE $payments_hist (
				bill_number varchar(16) PRIMARY KEY,
				cc_token varchar(32),
				description varchar(35),
				amount int(7),
				datetime_issued datetime,
				datetime_payed datetime,
				charged varchar(1), /*n: Pending, y: charged*/
				payed varchar(1) /*n: Pending, y: payed*/
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		
		echo mysqli_error($con);
		
		//Calendar Appointments table
		
		//array_push($error_array, "<span style='color: #14C800'> You are all set. Please login. </span><br>");
		
		if(isset($_POST['search_true'])){
			$_SESSION['username'] = $username; //Session gets assigned
			$_SESSION['messages_token'] = $messages_token;
			$_SESSION['username_e'] = $username_e;
			
			$_SESSION['confirm_email_alert'] = "1"; 
			$_SESSION['one_appointment_limit'] = "0"; 
			
			$stmt = $con->prepare("UPDATE users SET `messages_token`=? WHERE username = ?");
			$stmt->bind_param("ss", $messages_token,$username_e);
			$stmt->execute();
			
		}
		
		$md5email = md5($em);
		
		$to = $em; // Send email to our user
		switch ($lang){
			
			case("en"):
				$subject = 'ConfiDr. Email Verification'; // Give the email a subject
				$message = '
						
				Welcome to ConfiDr! The first social network joining doctors and patients.
				Your account has been created, you can activate it by clicking in the followng link:
						
				------------------------
				http://www.confidr.com/verify.php?email='.$md5email.'&hash='.$messages_token.'
				Please click this link to activate your account
				------------------------
						
				IMPORTANT: If you scheduled an appointment without having activated your account, you have to:
             		log in, go to calendar, select the appointment, click in "More Details", and then click "Confirm Appointment".<br>
					REMEMBER you only have 2 hours since you schedule the appointment to do this, otherwise your appointment would be
					deleted automatically.
				';
				
				$url = "http://www.confidr.com/verify.php?email={$md5email}&hash={$messages_token}";
				$email_creator = new Email_Creator();
				
				$title = "Confirmation Email";
				$greet = "Welcome to ConfiDr! The first social network joining doctors and patients.";
				$confirmation_message = "Your account has been created, you can activate it by clicking in the followng link:";
				$button = "Activate Account";
				$confirmation_message_2 = 'IMPORTANT: If you scheduled an appointment without having activated your account, you have to:
             		log in, go to calendar, select the appointment, click in "More Details", and then click "Confirm Appointment".<br>
					REMEMBER you only have 2 hours since you schedule the appointment to do this, otherwise your appointment would be
					<b>deleted automatically</b>.';
				
				$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
						$button, $url, $confirmation_message_2);
				break;
				
			case("es"):
				$subject = 'ConfiDr. Verificación de Correo'; // Give the email a subject
				$message = '
						
				Bienvenido a ConfiDr! La primera red social que une doctores y pacientes.
				Tu cuenta ha sido creada, ahora puedes ingresar entrando al siguiente link:
						
				------------------------
				http://www.confidr.com/verify.php?email='.$md5email.'&hash='.$messages_token.'
				Has click en este link para activar tu cuenta.
				------------------------
						
				IMPORTANTE: Si agendaste una cita sin haber verificado tu correo aun,
				ahora debes: iniciar sesión, ir a tu calendario, buscar la cita agendada,
				hacer click en "Información de Cita", y en la siguiente página hacer
				click en "Confirmar".
				RECUERDA que tienes 2 horas para hacer esto desde el momento en que
				reservaste la cita, de lo contrario esta será eliminada automáticamente.
						
				';
				
				$url = "http://www.confidr.com/verify.php?email={$md5email}&hash={$messages_token}";
				$email_creator = new Email_Creator();
				
				$title = "Email Confirmación";
				$greet = "¡Bienvenido a ConfiDr.! La primera red social que une doctores y pacientes.";
				$confirmation_message = "Tu cuenta ha sido creada, ahora sólo debes confirmar tu correo haciendo click aquí:";
				$button = "Confirmar Correo";
				$confirmation_message_2 = 'IMPORTANTE: Si agendaste una cita sin haber verificado tu correo aún, debes:
             		iniciar sesión, ir a tu calendario, ubicar la cita agendada,
					hacer click en "Más Detalles", y en la siguiente página hacer
					click en "Confirmar".<br>
					RECUERDA que tienes 2 horas para hacer esto desde el momento en que
					reservaste la cita, de lo contrario esta será <b>eliminada automáticamente</b>.';
				
				$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
						$button, $url, $confirmation_message_2);
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
			
			$mail->Username = 'support-confidr@jimenezd.com';                 // SMTP username
			$mail->Password = '/|/euRo#$!Na/pti_C#2017';                           // SMTP password
			
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = 587;                                    // TCP port to connect to
			
			//Recipients
			$mail->setFrom('support-confidr@jimenezd.com', 'Support-ConfiDr');
			//TODO: Comentar la siguiente linea para permitir que los correos de confirmación lleguen a los usuarios y no a confidr para moderación.
			//$mail->addAddress($to, $txt_rep->entities($fname . " " . $lname));     // Add a recipient
			$mail->addAddress("neuronapticsas@gmail.com", $txt_rep->entities($fname . " " . $lname));     // Add a recipient
			$mail->addAddress("davidrumsjb@icloud.com", $txt_rep->entities($fname . " " . $lname));
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
		
		//Clear session variables
		$error_array = [];
		$_SESSION['reg_fname'] = "";
		$_SESSION['reg_lname'] = "";
		$_SESSION['log_email']=$_SESSION['reg_email'];
		$_SESSION['reg_email'] = "";
		$_SESSION['reg_email2'] = "";
	}
}
?>