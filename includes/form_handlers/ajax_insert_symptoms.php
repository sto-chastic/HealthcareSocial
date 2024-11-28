<?php

include("../../config/config.php");
include("../classes/User.php");
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
		$userLoggedIn = $temp_user;
		$userLoggedIn_e = $temp_user_e;
		$final_mess_token = $temp_messages_token;
		$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
		
		$txt_rep = new TxtReplace();
		
		$lang = $_SESSION['lang'];
		$rejected_symptoms = [];
		
		//for doctors adding symptoms
		
		$doctor_obj = $user_obj;
		
		
	}
	else{
		echo "3";
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
}
else{
	echo "2";
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

if(isset($_REQUEST['cid'])){
	$appoinments_calendar_tab = $doctor_obj->getAppointmentsCalendar();
	$appoinments_details_tab = $user_obj->getAppointmentsDetails_Doctor();
	
	$temp_id = $_REQUEST['cid'];
	
	$stmt = $con->prepare("SELECT * FROM $appoinments_details_tab WHERE consult_id=? AND doctor_username=?");
	$stmt->bind_param("ss", $temp_id, $userLoggedIn);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	$temp_details = mysqli_fetch_array($verification_query);
	
	$stmt = $con->prepare("SELECT * FROM $appoinments_calendar_tab WHERE consult_id=?");
	$stmt->bind_param("s", $temp_id);
	$stmt->execute();
	$verification_query_2 = $stmt->get_result();
	$temp_calendar = mysqli_fetch_array($verification_query_2);
	$stmt->close();
	
	
	if(mysqli_num_rows($verification_query) == 1 && mysqli_num_rows($verification_query_2) == 1){
		$details = $temp_details;
		$appointment_id = $temp_id;
		$patient_username = $details['patient_username'];
		$patient_username_e = $crypt->EncryptU($patient_username);
		$patient_obj = new User($con, $patient_username, $patient_username_e);
		$calendar = $temp_calendar;
		
		$appointment_obj = new Appointments_Master($con, $userLoggedIn, $userLoggedIn_e);
		
		
		$empty = str_replace(' ', '', $_REQUEST['post_symptoms']);
		
		if($empty == "" && isset($_REQUEST['post_text'])){
			$_SESSION['post_symptoms'] = $_REQUEST['post_symptoms'];
			$_SESSION['post_text'] = $_REQUEST['post_text'];
			switch($lang){
			    case("en"):
			        echo "<b id='incorrect'>Symptoms cannot be empty if the description is not empty.<br></b>";
			        break;
			    case("es"):
			        echo "<b id='incorrect'>No puede estar vacía la casilla de síntoma si la descripción no lo está.<br></b>";
			        break;
			}
			
		}
		elseif(isset($_REQUEST['post_symptoms'])){
			$symptoms_table_doc = $doctor_obj->getAppointmentsSymptoms_Doctor();
			$symptoms_table_pat = $patient_obj->getAppointmentsSymptoms_Patient();
			
			$description = htmlspecialchars($_REQUEST['post_text']);
			$description = strip_tags($description);
			$description = preg_replace("/[^\p{Xwd},. ]+/u", "", $description);
			
			$symptoms = htmlspecialchars($_REQUEST['post_symptoms']);
			$symptoms = strip_tags($symptoms);
			$symptoms = preg_replace("/[^\p{Xwd},. ]+/u", "", $symptoms);
			
			$symptoms_array = explode(",", $symptoms);
			$symptoms_array = array_map('ltrim',$symptoms_array);
			$symptoms_array = array_map('rtrim',$symptoms_array);
			$symptoms_array = array_map('strtolower',$symptoms_array);
			$symptoms_array = array_map('ucwords',$symptoms_array);
			
			$stmt = $con->prepare("SELECT title FROM $symptoms_table_doc WHERE consult_id=? ");
			$stmt->bind_param("s", $appointment_id);
			$stmt->execute();
			$query = $stmt->get_result();
			$prev_symptoms_arr = [];
			while($row = mysqli_fetch_array($query)) {
				$prev_symptoms_arr[] = $row['title'];
			}
			
			$stmt = $con->prepare("INSERT INTO $symptoms_table_doc VALUES(?,?,?,'','',?) ");
			
			$sympton_id_arr = [];
			foreach ($symptoms_array as $key => $symptom) {
				if($symptom != "" && !in_array($symptom, $prev_symptoms_arr)){
					$id = $appointment_id . date('His') . $key;
					$sympton_id_arr[$key] = $id;
					$stmt->bind_param("ssss", $appointment_id, $symptom, $description, $id);
					$stmt->execute();
				}
			}
			
			$stmt = $con->prepare("INSERT INTO $symptoms_table_pat VALUES(?,?,?,'','',?) ");
			
			foreach ($symptoms_array as $key => $symptom) {
				$check_bool = 1;
				if(in_array($symptom, $prev_symptoms_arr)){
					$check_bool = 0;
					array_push($rejected_symptoms, $symptom);
				}
				if($symptom != "" && $check_bool){
					$id = $sympton_id_arr[$key];
					$stmt->bind_param("ssss", $appointment_id, $symptom, $description, $id);
					$stmt->execute();
				}
			}
			
			if(!empty($rejected_symptoms)){
				$rej_symp_lin = implode(", ", $rejected_symptoms);
				echo "<b id='incorrect'>The symptom(s): " . $rej_symp_lin . ", was(were) already inserted for this consult and cannot be added twice. Delete the symptom and try again.<br></b>";
			}
			//create symptoms frequency tables
			
			$doctor_symptoms_table = $doctor_obj->getSymptomsFrecTable();
			$query = mysqli_query($con,"SELECT title, cast(100*COUNT(title)/(SELECT Count(*) FROM $symptoms_table_doc) as decimal(6,3)) as prob FROM $symptoms_table_doc GROUP BY title ORDER BY prob DESC");
			
			$query_tr = mysqli_query($con,"TRUNCATE TABLE $doctor_symptoms_table");
			$stmt = $con->prepare("INSERT INTO $doctor_symptoms_table VALUES(?,?,?) ");
			$stmt->bind_param("iss",$key,$title,$prob);
			
			foreach ($query as $key => $value){
				$title = $value['title'];
				$prob = $value['prob'];
				$stmt->execute();
			}
			$stmt->close();
			$_SESSION['post_symptoms'] = "";
			$_SESSION['post_text'] = "";
		}
		
		
		
	}
	else{
		$details = "";
		$appointment_id = "";
		$patient_username = "";
		$patient_obj = NULL;
		$calendar = [];
		
		$appointment_obj = NULL;
		header('Location: ../../index.php');
	}
}
else{
	header('Location: ../../index.php');
}



	

	
