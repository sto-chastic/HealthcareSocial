<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Appointments_Master.php");
include("../classes/TxtReplace.php");
$crypt = new Crypt();
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
		$userLoggedIn = $temp_user;//Retrieves username
		$userLoggedIn_e = $temp_user_e; 
		$user = mysqli_fetch_array($verification_query);
		//$messages_token = $temp_messages_token;
		$stmt->close();
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

$limit = 4;

$txtrep = new TxtReplace();
$patient_user_e = pack("H*", $txtrep->entities($_REQUEST['pat_user']));
$patient_user = $crypt->Decrypt($patient_user_e);

$am = new Appointments_Master($con, $patient_user, $patient_user_e);
$patient_obj =  new User($con, $patient_user, $patient_user_e);
$category_temp = $_REQUEST['search_crit'];
$search_term = $txtrep->entities($_REQUEST['search']);
$count_prev = $_REQUEST['count_prev'];
if(isset($_REQUEST['doc_usr'])){
    $doc_usr_e = pack("H*", $txtrep->entities($_REQUEST['doc_usr']));
    $doc_usr = $crypt->Decrypt($doc_usr_e);
	$str = $am->printPreviousAppointments($patient_obj, $category_temp, $search_term, $limit, $count_prev, $doc_usr);
}
else{
	$str = $am->printPreviousAppointments($patient_obj, $category_temp, $search_term, $limit, $count_prev);
}

echo $str;
// $posts->loadPostsFriends($_REQUEST, $limit);

?>