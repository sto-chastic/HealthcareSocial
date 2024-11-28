<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Appointments_Master.php");
include("../classes/TxtReplace.php");

$crypt =  new Crypt();
$txtrep = new TxtReplace();

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
		
		$lang = $_SESSION['lang'];
		$column = "";
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

if(isset($_REQUEST['patient_user'])){
    $patient_user_e = pack("H*", $txtrep->entities($_REQUEST['patient_user']));
	$patient_user = $crypt->Decrypt($patient_user_e);
}
else{
	$patient_user = $userLoggedIn;
	$patient_user_e = $userLoggedIn_e;
}

if(isset($_REQUEST['doctor_user'])){
    $doctor_user_e = pack("H*", $txtrep->entities($_REQUEST['doctor_user']));
	$doctor_user = $crypt->Decrypt($doctor_user_e);
}
else{
	$doctor_user = $userLoggedIn;
	$doctor_user_e = $userLoggedIn_e;
}

$correct_doct_bool = FALSE;

if(isset($_REQUEST['aid'])){
	$aid = $_REQUEST['aid'];
	$appointment_obj = new Appointments_Master($con, $patient_user, $patient_user_e);
	$correct_doct = $appointment_obj->getPatient_view_Doctor($aid);
	if($doctor_user == $correct_doct){
		$correct_doct_bool = TRUE;
	}
}

if($correct_doct_bool || $patient_user == $doctor_user){
	$graph = $_REQUEST['table'];
	$add_column = "";
	$patient_obj = new User($con, $patient_user, $patient_user_e);
	
	switch ($lang){
		
		case("en"):
			switch ($graph) {
				case 'height':
					$table = $patient_obj->getHeight();
					$column = $graph;
					$title = ucwords($graph);
					break;
				case 'weight':
					$table = $patient_obj->getWeight();
					$column = $graph;
					$title = ucwords($graph);
					break;
				case 'bmi':
					$table = $patient_obj->getBMI();
					$column = $graph;
					$title = strtoupper($graph);
					break;
				case 'bp':
					$table = $patient_obj->getBloodPressure();
					$column = "BPSys";
					$addi_column = "BPDia";
					$title = strtoupper($graph);
					break;
				default:
					$table = "";
					$column = "";
					$addi_column = "";
					$title = "";
			}
			break;
			
		case("es"):
			switch ($graph) {
				case 'height':
					$table = $patient_obj->getHeight();
					$column = $graph;
					$title = "Altura";
					break;
				case 'weight':
					$table = $patient_obj->getWeight();
					$column = $graph;
					$title = "Peso";
					break;
				case 'bmi':
					$table = $patient_obj->getBMI();
					$column = $graph;
					$title = "IMC";
					break;
				case 'bp':
					$table = $patient_obj->getBloodPressure();
					$column = "BPSys";
					$addi_column = "BPDia";
					$title = "PA";
					break;
				default:
					$table = "";
					$column = "";
					$addi_column = "";
					$title = "";
			}
			break;
	}
	

	$num_points = $_REQUEST['num_points'];
	if($num_points > 10)
		$num_points = 10;
	
	if($graph == 'bp'){
		$stmt = $con->prepare("SELECT $column,$addi_column,date_time FROM $table ORDER BY date_time DESC, PHID DESC LIMIT ?");
	}
	else{
		if($column != ""){
			$stmt = $con->prepare("SELECT $column,date_time FROM $table ORDER BY date_time DESC, PHID DESC LIMIT ?");
		}
	}
	
	if($column != ""){
		$stmt->bind_param("i", $num_points);
		$stmt->execute();
		$query = $stmt->get_result();				
	}
	
	$temp_arr = array();
	$temp_arr2 = array();
	$date_arr = array();
	
	if($column != ""){
		while($row = mysqli_fetch_array($query)) {
			$temp_arr[] = $row[$column];
			if($graph == 'bp'){
				$temp_arr2[] = $row[$addi_column];
			}
			$date_arr[] = $row['date_time'];
		}
	}
	
	if(!empty($temp_arr) && sizeof($temp_arr) > 1){
		$val = sizeof($temp_arr);
		print json_encode(array("data"=>$temp_arr,"data2"=>$temp_arr2,"date_time"=>$date_arr,"title"=>$title));
	}
	else{
		http_response_code(404);
	}
}
else{
	session_start();
	session_destroy();
	header("Location: ../../register.php");
}

?>
