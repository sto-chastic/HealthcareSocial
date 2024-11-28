<?php

include("../../config/config.php");
include("../classes/User.php");
include("../classes/TxtReplace.php");
include("../classes/Appointments_Master.php");

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
		
		$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
		
		$txt_rep = new TxtReplace();
		
		$lang = $_SESSION['lang'];
		$appointment_obj = new Appointments_Master($con, $userLoggedIn, $userLoggedIn_e);
		
		if($user_obj->isDoctor()){
			echo $appointment_obj->printAppointmentSymptoms($_REQUEST['cid']);
		}
		else{
			$appointment_obj->printAppointmentSymptoms_Patient($_REQUEST['cid']);
		}

	}
	else{
		echo "3";
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
}
else{
	echo "2";
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}
