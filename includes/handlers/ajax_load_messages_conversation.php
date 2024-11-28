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
	$message_obj = new Message ($con, $userLoggedIn, $userLoggedIn_e);
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

$crypt = new Crypt();

$user_to = $crypt->Decrypt(pack("H*",$_REQUEST['user_to']));
$latest_loaded_message_id = $_REQUEST['latest_load_mess_id'];

echo $message_obj->getMessages($user_to, $latest_loaded_message_id);

?>