<?php
include("../../config/config.php");
include("../../includes/classes/User.php");
include("../classes/TxtReplace.php");
include("../classes/Appointments_Master.php");
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
		$userLoggedIn = $temp_user; //Retrieves username
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

$usr = $_POST['usr'];
$usr_e = $crypt->EncryptU($usr);
$cie_code = $_POST['cie_code'];
$aid = $_POST['aid'];
$diagnosis_num = $_POST['diagnosis_num'];

$appo_master = new Appointments_Master($con, $usr, $usr_e);
$appo_master->insert_diagnosis_cie_10($diagnosis_num, $aid, $usr, $cie_code);

switch ($diagnosis_num){
    case 1:
        $row = "cod_diag_1";
        break;
    case 2:
        $row = "cod_diag_2";
        break;
    case 3:
        $row = "cod_diag_3";
        break;
    case 4:
        $row = "cod_diag_4";
        break;
}

$user_obj = new User($con, $usr, $usr_e);
$rips_tab = $user_obj->getDoctorsRIPS_tablename();

$stmt = $con->prepare("SELECT $row FROM $rips_tab WHERE consult_id = ?");
$stmt->bind_param("s",$aid);
$stmt->execute();

$quer = $stmt->get_result();
$arr = mysqli_fetch_assoc($quer);

$temp_code = $arr[$row];

$country = $user_obj->getCountry_Doctor();
$stmt = $con->prepare("SELECT `desc` FROM cie_10_" . $country . " WHERE `cie_code` = ?");
$stmt->bind_param("s",$temp_code);
$stmt->execute();
$quer = $stmt->get_result();
$arr = mysqli_fetch_assoc($quer);
$temp_desc= $arr['desc'];

print json_encode(array("description"=>$temp_desc,"code"=>$temp_code));