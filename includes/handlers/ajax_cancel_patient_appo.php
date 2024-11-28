<?php
include("../../config/config.php");
include("../classes/TxtReplace.php");
include("../classes/User.php");
$crypt = new Crypt();
$txtrep = new TxtReplace();
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

$aid = $_REQUEST['aid'];
$did_e = pack("H*", $txtrep->entities($_REQUEST['did']));
$did = $crypt->Decrypt($did_e);
$pid = $userLoggedIn;

$doctor_obj = new User($con, $did, $did_e);
$patient_obj = new User($con, $pid, $userLoggedIn_e);

$doc_cal = $doctor_obj->getAppointmentsCalendar();
$doc_appo_dets = $doctor_obj->getAppointmentsDetails_Doctor();

$pat_cal = $patient_obj->getAppointmentsCalendar_Patient();
$pat_appo_dets = $patient_obj->getAppointmentsDetails_Patient();

$stmt = $con->prepare("DELETE FROM $doc_cal WHERE consult_id = ?");
$stmt->bind_param("s", $aid);
$stmt->execute();

$stmt = $con->prepare("UPDATE $doc_appo_dets SET cancelled_by_pat = 1 WHERE consult_id = ?");
$stmt->bind_param("s", $aid);
$stmt->execute();

//Check if we need to change docs nums
$stmt = $con->prepare("SELECT * FROM $pat_cal WHERE consult_id = ? AND confirmed_pat = 1");
$stmt->bind_param("s", $aid);
$stmt->execute();
$verification_query_2 = $stmt->get_result();
$num_aid = mysqli_num_rows($verification_query_2);

if($num_aid == 1){
	//Current information from basic_info_doctors
	
	$stmt = $con->prepare("SELECT `pat_seen`, `pat_inter`, `pat_rec` FROM basic_info_doctors WHERE username = ?");
	$stmt->bind_param("s", $did);
	$stmt->execute();
	$basic_info_doctors_q = $stmt->get_result();
	$basic_info_doctors_arr = mysqli_fetch_assoc($basic_info_doctors_q);
	
	$pat_rec = $basic_info_doctors_arr['pat_rec'];
	$pat_seen = $basic_info_doctors_arr['pat_seen'];
	$pat_inter = $basic_info_doctors_arr['pat_inter'];
	
	//Patients seen calculation
	
	$doc_appo_dets = $doctor_obj->getAppointmentsDetails_Doctor();
	
	$stmt = $con->prepare("SELECT * FROM $doc_appo_dets WHERE patient_username = ?");
	$stmt->bind_param("s", $pid);
	$stmt->execute();
	$doc_appo_dets_query = $stmt->get_result();
	
	if(mysqli_num_rows($doc_appo_dets_query) > 0){
		$pat_rec--;
	}
	else{
		$pat_seen--;
	}
	$pat_inter--;
	
	$stmt = $con->prepare("UPDATE basic_info_doctors SET pat_rec = ?, pat_seen = ?, pat_inter = ? WHERE username = ?");
	$stmt->bind_param("iiis",$pat_rec,$pat_seen,$pat_inter,$did);
	$stmt->execute();
}

$stmt = $con->prepare("DELETE FROM $pat_cal WHERE consult_id = ?");
$stmt->bind_param("s", $aid);
$stmt->execute();

$stmt = $con->prepare("UPDATE $pat_appo_dets SET cancelled_by_pat = 1 WHERE consult_id = ?");
$stmt->bind_param("s", $aid);
$stmt->execute();



?>