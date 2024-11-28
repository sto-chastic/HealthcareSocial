<?php
require '../../config/config.php';
include('../classes/User.php');
$crypt = new Crypt();
$userLoggedIn = "";


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
		$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../register.php");
	}
	
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../register.php");
}

if(isset($_REQUEST['aid'])) {
	$temp_aid = $_REQUEST['aid'];
	
	$temp_pat_username = $_REQUEST['u'];
	$doctor_details_tab = $user_obj->getAppointmentsDetails_Doctor();
	
	$stmt = $con->prepare("SELECT * FROM $doctor_details_tab WHERE consult_id=? AND patient_username=?");
	$stmt->bind_param("ss", $temp_aid, $temp_pat_username);
	$stmt->execute();
	
	$verification_query = $stmt->get_result();
	$numrows = mysqli_num_rows($verification_query);
	$stmt->close();
	
	
	if($numrows > 0){
		$aid = $temp_aid;
		$id = $_REQUEST['id'];
		
		$doc_username = $userLoggedIn;
		$pat_username = $temp_pat_username;
		$pat_username_e = $crypt->EncryptU($pat_username);
		$pat_obj = new User($con, $pat_username, $pat_username_e);
		$doc_obj = $user_obj;
		
		$doc_sympt_tab = $doc_obj->getAppointmentsSymptoms_Doctor();
		$pat_sympt_tab = $pat_obj->getAppointmentsSymptoms_Patient();
		$doc_medi_tab = $doc_obj->getAppointmentsMedicines_Doctor();
		$pat_medi_tab = $pat_obj->getAppointmentsMedicines_Patient();
		
	}
	else{
		$aid = "";
		$doc_username = "";
		$pat_username = "";
		$doc_obj = NULL;
		$pat_obj = NULL;
		//header("Location: index.php");
	}
	
	if($_REQUEST['type'] == "symptoms"){
		$tab_doc = $doc_sympt_tab;
		$tab_pat = $pat_sympt_tab;
	}
	elseif($_REQUEST['type'] == "medicines"){
		$tab_doc = $doc_medi_tab;
		$tab_pat = $pat_medi_tab;
	}
	
	$stmt = $con->prepare("DELETE FROM $tab_doc WHERE id=? AND consult_id=?");
	
	$stmt->bind_param("ss", $id, $aid);
	
	$stmt->execute();
	
	
	$stmt = $con->prepare("DELETE FROM $tab_pat WHERE id=? AND consult_id=?");
	
	$stmt->bind_param("ss", $id, $aid);
	
	$stmt->execute();
	
	$stmt->close();
	
	
}
?>