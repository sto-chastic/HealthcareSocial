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
$id= $_REQUEST['id'];

switch ($lang){
	
	case("en"):
		switch ($stat) {
			case 'height':
				$table = $user_obj->getHeight();
				$column = $stat;
				$title = ucwords($column);
				break;
			case 'weight':
				$table = $user_obj->getWeight();
				$column = $stat;
				$title = ucwords($column);
				break;
			case 'bmi':
				$table = $user_obj->getBMI();
				$column = $stat;
				$title = ucwords($column);
				break;
			case 'bp':
				$table = $user_obj->getBloodPressure();
				$column = "BPSys";
				$addi_column = "BPDia";
				break;
		}
		break;
		
	case("es"):
		switch ($stat) {
			case 'height':
				$table = $user_obj->getHeight();
				$column = $stat;
				$title = "Altura";
				break;
			case 'weight':
				$table = $user_obj->getWeight();
				$column = $stat;
				$title = "Peso";
				break;
			case 'bmi':
				$table = $user_obj->getBMI();
				$column = $stat;
				$title = "IMC";
				break;
			case 'bp':
				$table = $user_obj->getBloodPressure();
				$column = "BPSys";
				$addi_column = "BPDia";
				break;
		}
		break;
}


$data = $_REQUEST['data'];
$date = $_REQUEST['date'];
$time = $_REQUEST['time'];

if($stat == 'bp'){
	$data2 = $_REQUEST['data2'];
}

$date_time_obj = DateTime::createFromFormat("d/m/y h:ia",$date . ' ' . $time);
$date_time = $date_time_obj->format("Y-m-d H:i") . ":00";

if($id == -1){
	if($stat == 'bp'){
		$stmt = $con->prepare("INSERT INTO $table (`PHID`, $column, $addi_column, `date_time`) VALUES ('',?,?,?)");
		$stmt->bind_param("sss",$data,$data2,$date_time);
	}
	else{
		$stmt = $con->prepare("INSERT INTO $table (`PHID`, $column, `date_time`) VALUES ('',?,?)");
		$stmt->bind_param("ss",$data,$date_time);
	}
}
else{
	if($stat == 'bp'){
		$stmt = $con->prepare("UPDATE $table SET $column = ?, $addi_column = ?, date_time = ? WHERE PHID = ?");
		$stmt->bind_param("ssss",$data,$data2,$date_time,$id);
	}
	else{
		$stmt = $con->prepare("UPDATE $table SET $column = ?, date_time = ? WHERE PHID = ?");
		$stmt->bind_param("sss",$data,$date_time,$id);
	}
}

$stmt->execute();

?>
