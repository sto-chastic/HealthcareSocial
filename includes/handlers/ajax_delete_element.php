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

$table = $_REQUEST['t'];
$t_id_column = $_REQUEST['idc'];
$id = $_REQUEST['id'];

switch ($t_id_column) {
	case 'id':
		$id_column = $t_id_column;
		break;
	case 'web_page_code':
		$id_column = $t_id_column;
		break;
	default:
		return NULL;
}

switch ($table) {
	case 'education':
		$_tab = $user_obj->getEducation_tab();
		break;
	case 'job':
		$_tab = $user_obj->getJobs_tab();
		break;
	case 'conference':
		$_tab = $user_obj->getConferences_tab();
		break;
	case 'webpage':
		$_tab = $user_obj->getWebpages_tab();
		break;
	case 'publication':
		$_tab = $user_obj->getPublications_tab();
		break;
}

$stmt = $con->prepare("DELETE FROM $_tab WHERE $id_column = ?");
$stmt->bind_param("i",$id);
$stmt->execute();

?>