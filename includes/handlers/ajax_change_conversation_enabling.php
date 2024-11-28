<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Message.php");
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
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
	
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	$txtrep = new TxtReplace();
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}
$crypt = new Crypt();

$patient_user = $crypt->Decrypt(pack("H*",$_REQUEST['patient_user']));
$_temp_status = $_REQUEST['status'];

switch($_temp_status){
	case '1':
		$status = 1;
		break;
	case '0':
		$status = 0;
		break;
}

$doctor_messages_status_table = $user_obj->getMessagesStatusTable();

$sql_query = "INSERT INTO $doctor_messages_status_table (`secondary_interlocutor`, `enabled`, `payed`, `date_payed`, `accepted_payment`, `date_activated`, `date_termination`, `score`)
			 VALUES (?,?,0,'',0,'','',-1)
			 ON DUPLICATE KEY
			 UPDATE `enabled`=?";
$stmt = $con->prepare($sql_query);
$stmt->bind_param("sii",$patient_user,$status,$status);
$stmt->execute();

?>