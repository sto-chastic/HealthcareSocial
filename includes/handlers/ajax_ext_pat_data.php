<?php

include("../../config/config.php");
include("../classes/User.php");
include("../classes/TxtReplace.php");

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
		
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

$doctor_user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
$ext_pat_tab = $doctor_user_obj->getExternalPatients_Tab();
$appo_dets = $doctor_user_obj->getAppointmentsDetails_Doctor();
$stmt = $con->prepare("SELECT patient_username FROM $appo_dets WHERE consult_id=?");
$stmt->bind_param("s",$_POST['cid']);
$stmt->execute();

$username_q = $stmt->get_result();
$username_arr = mysqli_fetch_assoc($username_q);

//echo "asjfa_ " . $_POST['pat_name'];


$stmt = $con->prepare("UPDATE $ext_pat_tab SET `name`=?,`contact_info`=?,`insurance`=?,`notes`=? WHERE username=?");
$stmt->bind_param("sssss",$_POST['pat_name'],$_POST['pat_contact'],$_POST['pat_insurance'],$_POST['patient_notes'],$username_arr['patient_username']);
$stmt->execute();
