<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Calendar.php");
include("../classes/TxtReplace.php");
include("../classes/TimeStamp.php");

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


$year = (int)$_REQUEST['year'];

$current_day = date("d");
$current_month = date("m"); //Gets current month
$current_year = date("Y");
$lang = $_SESSION['lang'];

switch ($lang){
	
	case("en"):
		$months_row_lang = 'months_eng';
		$days_week_row_lang = 'days_short_eng';
		break;
		
	case("es"):
		$months_row_lang = 'months_es';
		$days_week_row_lang = 'days_short_es';
		break;
}

$curr_month_lang_query = mysqli_query($con, "SELECT $months_row_lang FROM months WHERE id='$current_month'");
$arr = mysqli_fetch_array($curr_month_lang_query);
$curr_month_lang = $arr[$months_row_lang];


$drop_dwn_month = "";
$query = mysqli_query($con, "SELECT $months_row_lang,id FROM months ORDER BY id ASC");
foreach($query as $arr){
	if($arr['id'] < $current_month && $year == $current_year){
		continue;
	}
	if($curr_month_lang == $arr[$months_row_lang]){
	    $drop_dwn_month = $drop_dwn_month . "<option selected='selected' value='" . $arr['id'] . "'>" . $arr[$months_row_lang] . "</option>";
	}
	else{
	    $drop_dwn_month = $drop_dwn_month . "<option value='" . $arr['id'] . "'>" . $arr[$months_row_lang] . "</option>";
	}
}

echo $drop_dwn_month;

?>


