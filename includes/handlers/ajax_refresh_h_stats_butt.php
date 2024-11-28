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
	$lang = $_SESSION['lang']; 
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

$stat= $_REQUEST['stat'];

switch ($stat) {
	case 'height':
		$table = $user_obj->getHeight();
		$column = $stat;
		$units = "<span class='h_stats_units_title'>m</span>";
		break;
	case 'weight':
		$table = $user_obj->getWeight();
		$column = $stat;
		$units = "<span class='h_stats_units_title'>kg</span>";
		break;
	case 'bmi':
		$table = $user_obj->getBMI();
		$column = $stat;
		$units = "";
		break;
	case 'bp':
		$table = $user_obj->getBloodPressure();
		$column = "BPSys";
		$addi_column = "BPDia";
		$units = "<span class='h_stats_units_title'>mmHg</span>";
		break;
	default:
		$stat = "";
}

$extra = "";

if($stat != "bp"){
	$stmt = $con->prepare("SELECT $column,date_time FROM $table ORDER BY date_time DESC LIMIT 1");
}
else{
	$stmt = $con->prepare("SELECT $column,$addi_column,date_time FROM $table ORDER BY date_time DESC LIMIT 1");
}

$stmt->execute();
$cols_q = $stmt->get_result();
$cols_arr = mysqli_fetch_array($cols_q);
$cols_l = $cols_arr[$column];

if($stat == "bp"){
	$cols_l2 = $cols_arr[$addi_column];
	$extra = " / " . $cols_l2;
}

//BMI scale
switch ($lang){
	
	case("en"):
		if($units == ""){
			if($cols_l< 18.5 && $cols_l !== NULL)
				$units ="<span style='color:rgb(243, 216, 9);'> Underweight </span>";
			elseif ($cols_l>= 18.5 && $cols_l< 24.9)
				$units ="<span style='color:rgb(105, 249, 131);'> Normal </span>";
			elseif ($cols_l>= 25 && $cols_l< 29.9)
				$units ="<span style='color:color:rgb(247, 144, 69);'> Overweight </span>";
			elseif ($cols_l>= 30)
				$units ="<span style='color:red;'> Obese </span>";
			else
				$units ="";
		}
		break;
		
	case("es"):
		if($units == ""){
			if($cols_l< 18.5 && $cols_l !== NULL)
				$units ="<span style='color:rgb(243, 216, 9);'> Infrapeso </span>";
			elseif ($cols_l>= 18.5 && $cols_l< 24.9)
				$units ="<span style='color:rgb(105, 249, 131);'> Normal </span>";
			elseif ($cols_l>= 25 && $cols_l< 29.9)
				$units ="<span style='color:color:rgb(247, 144, 69);'> Sobrepeso </span>";
			elseif ($cols_l>= 30)
				$units ="<span style='color:red;'> Obesidad </span>";
			else
				$units ="";
		}
		break;
}

$data = $cols_l . $extra . $units;

if(mysqli_num_rows($cols_q) > 0){
	$cols_l_date = $cols_arr['date_time'];
	
	$date_time_obj = DateTime::createFromFormat("Y-m-d H:i:s",$cols_l_date);
	switch ($lang){
		
		case("en"):
			$date_time = "on " . $date_time_obj->format("j/n/\'y");
			break;
			
		case("es"):
			$date_time = "el " . $date_time_obj->format("j/n/\'y");
			break;
	}
	
}
else{
	$data = -1;
	$date_time = "";
}

print json_encode(array("data"=>$data,"date_time"=>$date_time));

?>