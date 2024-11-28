<?php
include("../../config/config.php");
include("../classes/User.php");

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
	
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

$stat= $_REQUEST['stat'];
$id= $_REQUEST['id'];

switch ($stat) {
	case 'height':
		$table = $user_obj->getHeight();
		break;
	case 'weight':
		$table = $user_obj->getWeight();
		break;
	case 'bmi':
		$table = $user_obj->getBMI();
		break;
	case 'bp':
		$table = $user_obj->getBloodPressure();
		break;
}

$stmt = $con->prepare("DELETE FROM $table WHERE PHID = ?");
$stmt->bind_param("i",$id);
$stmt->execute();

?>