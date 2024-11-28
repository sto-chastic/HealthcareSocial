<?php 
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/Appointments_Calendar.php");
	include("../classes/Calendar.php");
	include("../classes/TxtReplace.php");
	include("../classes/TimeStamp.php");
    $crypt = new Crypt();
	//"profile_owner=" + profile_owner + "&day=" + day + "&month=" + month + "&year=" + year + "&ap_st=" + sel_time_st + "&ap_end=" + sel_time_end,
	
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
		}
		else{
// 			echo "3";
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: ../../register.php");
			$stmt->close();
		}
	}
	else{
// 		echo "2";
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}

	$lang = $_SESSION['lang'];
	$selected_year = $_REQUEST['year'];
	$selected_month_t = date_create("0000-" . $_REQUEST['month'] . "-15");
	$selected_month = date_format($selected_month_t,"m");
	$selected_day = $_REQUEST['day'];
	$profile_owner_e = pack("H*", $_REQUEST['profile_owner']);
	$profile_owner = $crypt->Decrypt($profile_owner_e);
	
	$payment_method = $_REQUEST['payment_method'];
	$appo_type_id = $_REQUEST['ap_id'];
	$_old_aid = $_REQUEST['old_aid'];
	$profile_owner_obj = new User($con, $profile_owner, $profile_owner_e);
	$app_dur_tab = $profile_owner_obj->getAppoDurationTable();
	
	$stmt = $con->prepare("SELECT * FROM $app_dur_tab WHERE id=?");
	$stmt->bind_param("i", $appo_type_id);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	
	if(mysqli_num_rows($verification_query) == 1){
		$_id_arr = mysqli_fetch_assoc($verification_query);
		$appo_type_id = $_id_arr['id'];
		$appo_type =  $_id_arr['appo_type'];
		$cost = $_id_arr['cost'];
		$currency = $_id_arr['currency'];
	}
	else{
 		header("Location: 404.php");
	}

	if($profile_owner == $userLoggedIn){
		$external =  1;
	}
	else{
		$external = 0;
	}
	
	//check id belongs to this doctor
	$profile_owner_obj = new User($con,$profile_owner, $profile_owner_e);
	$_det_doc = $profile_owner_obj->getAppointmentsDetails_Doctor();
	$_doc_usern = $profile_owner_obj->getUsername();
	
	if(!$external){
		$stmt = $con->prepare("SELECT consult_id FROM $_det_doc WHERE consult_id=? AND doctor_username=? AND patient_username =?");
		$stmt->bind_param("sss", $_old_aid,$_doc_usern,$userLoggedIn);
	}
	else{
		$stmt = $con->prepare("SELECT consult_id FROM $_det_doc WHERE consult_id=? AND doctor_username=?");
		$stmt->bind_param("ss", $_old_aid,$userLoggedIn);
	}
	
	$stmt->execute();
	$verification_query_aid_doc = $stmt->get_result();
	
	$aid_num_rows = 0;
	
	if(!$external){
		//check id belongs to this patient
		$userLoggedIn_obj = new User($con,$userLoggedIn, $userLoggedIn_e);
		$_det_pat = $userLoggedIn_obj->getAppointmentsDetails_Patient();
		
		$stmt = $con->prepare("SELECT consult_id FROM $_det_pat WHERE consult_id=? AND doctor_username=? AND patient_username =?");
		$stmt->bind_param("sss", $_old_aid,$_doc_usern,$userLoggedIn);
		$stmt->execute();
		$verification_query_aid_pat = $stmt->get_result();
		$aid_num_rows = mysqli_num_rows($verification_query_aid_pat);
	}
	
	if(mysqli_num_rows($verification_query_aid_doc) == 1 && ($aid_num_rows == 1 || $external)){
		if(!$external){
			$aid_arr = mysqli_fetch_assoc($verification_query_aid_pat);
			$aid = $aid_arr['consult_id'];
		}
		else{
			$aid = $_REQUEST['old_aid'];
		}
	}
	else{
		$profile_owner_obj = NULL;
		$userLoggedIn_obj = NULL;
		$_id_arr = "";
		$appo_type_id = "";
		$appo_type =  "";
		$cost = "";
		$currency = "";
		//header("Location: 404.php");
	}

	$time_ini = strtotime($_REQUEST['ap_st']);
	$time_end = strtotime($_REQUEST['ap_end']);

	$sel_time_st = date('H:i', $time_ini);
	$sel_time_end = date('H:i', $time_end);
	$creation_date = date("YmdHis");

	$appointments_table = $profile_owner_obj->getAppointmentsCalendar();
	$appointments_details = $profile_owner_obj->getAppointmentsDetails_Doctor();

	if(!$external){
		$appointments_table_pat = $userLoggedIn_obj->getAppointmentsCalendar_Patient();
		$appointments_details_pat = $userLoggedIn_obj->getAppointmentsDetails_Patient();
	}
	
	//create Id
	$consult_id = $aid;
	//for the event of appointment deletion
	$schedule_id = "appo_sched" . $profile_owner . $selected_year . $selected_month . $selected_day . $time_ini; //schedule ID
	$schedule_id_pat = "appo_sched_pat" . $profile_owner . $selected_year . $selected_month . $selected_day . $time_ini; //schedule ID

	//verify there are no other appointments already scheduled for the doctor

	$err = [];

	$stmt = $con->prepare("SELECT * FROM $appointments_table WHERE time_start < ? AND time_end > ? AND year = ? AND month = ? AND day = ?");
	$stmt->bind_param("sssss", $sel_time_end, $sel_time_st, $selected_year, $selected_month, $selected_day);
	$stmt->execute();
	$query = $stmt->get_result();

	$num_appoints = mysqli_num_rows($query);

	//Verify the doctor has the selected time available for booing, also get the office with available time
	
	$calendar = new Calendar($con, $profile_owner, $profile_owner_e);
	
	$office = $calendar->getAvailabilityIntervalOffice($payment_method, $sel_time_st, $sel_time_end, $selected_day, $selected_month, $selected_year);
	
	if($num_appoints > 0 || $office == 0)
		array_push($err,"ERROR at verification");
	elseif(!$external){
		
		//verify there are no other appointments already scheduled for the patient
				
		$stmt = $con->prepare("SELECT * FROM $appointments_table_pat WHERE time_start < ? AND time_end > ? AND year = ? AND month = ? AND day = ?");
		$stmt->bind_param("sssss", $sel_time_end, $sel_time_st, $selected_year, $selected_month, $selected_day);
		$stmt->execute();
		$query = $stmt->get_result();
		
		$num_appoints = mysqli_num_rows($query);
		
		if($num_appoints > 0)
			array_push($err,"ERROR at verification pat");
		else{
		
			//Doctor Appointment details table
			if (false === ($stmt = $con->prepare("UPDATE $appointments_details SET `payment_info` = ?, `appo_type` = ?, `doctor_username` = ?, `patient_username` = ?, `cancelled_by_pat` = 0, `cancelled_by_doc` = 0, `reescheduled` = 1, `cost` = ?, `currency` = ?, `payed_through_confidr` = '' WHERE `consult_id` = ?"))){
				array_push($err,"ERROR at appointment insertion1");
			}
			elseif (!$stmt->bind_param("sississ", $payment_method, $appo_type_id, $profile_owner, $userLoggedIn, $cost, $currency, $consult_id)){
				array_push($err,"ERROR at appointment insertion2");
			}
			elseif (!$stmt->execute()){
				array_push($err,"ERROR at appointment insertion3");
			}
	
			//Doctor Appointment calendar table
			if (false === ($stmt = $con->prepare("REPLACE INTO $appointments_table (`year`, `month`, `day`, `time_start`, `time_end`, `consult_id`, `confirmed_pat`, `confirmed_doc`, `creation_date_time`) VALUES (?,?,?,?,?,?,1,0,?)"))){
				array_push($err,"ERROR at appointment details insertion");
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','1d')");
			}
			elseif (!$stmt->bind_param("sssssss", $selected_year,$selected_month,$selected_day,$sel_time_st,$sel_time_end,$consult_id,$creation_date)){
				array_push($err,"ERROR at appointment details insertion");
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','2d')");
			}
			elseif (!$stmt->execute()){
				array_push($err,"ERROR at appointment details insertion");
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','3d')");
			}			
			
			//Patient Appointment details table
			
			$specialization = $profile_owner_obj->getSpecializationsCode($lang);
			
			if (false === ($stmt = $con->prepare("UPDATE $appointments_details_pat SET `payment_info` = ?, `appo_type` = ?, `doctor_username` = ?, `patient_username` = ?, `cancelled_by_pat` = 0, `cancelled_by_doc` = 0, `reescheduled` = 1, `cost` = ?, `currency` = ?, `payed_through_confidr` = '', `specializations` = ?, `office` = ? WHERE `consult_id` = ?"))){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','1')");
				array_push($err,"ERROR at appointment details insertion pat1");
			}
			elseif(!$stmt->bind_param("sississis", $payment_method, $appo_type_id, $profile_owner, $userLoggedIn, $cost, $currency ,$specialization, $office, $consult_id)){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','2')");
				array_push($err,"ERROR at appointment details insertion pat2");
			}
			elseif(!$stmt->execute()){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','3')");
				array_push($err,"ERROR at appointment details insertion pat3");
			}
			
			
			//Patient Appointment calendar table
			
			if (false === ($stmt = $con->prepare("REPLACE INTO $appointments_table_pat VALUES (?,?,?,?,?,?,1,0,?)"))){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','1')");
				array_push($err,"ERROR at appointment insertion pat");
			}
			elseif (!$stmt->bind_param("sssssss", $selected_year,$selected_month,$selected_day,$sel_time_st,$sel_time_end,$consult_id,$creation_date)){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','2')");
				array_push($err,"ERROR at appointment insertion pat");
			}
			elseif (!$stmt->execute()){
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','3')");
				array_push($err,"ERROR at appointment insertion pat");
			}
			$stmt->close();
		}
	}
	elseif($external){
			
		//Doctor Appointment details table
		if (false === ($stmt = $con->prepare("UPDATE $appointments_details SET `payment_info` = ?, `appo_type` = ?, `doctor_username` = ?, `patient_username` = ?, `cancelled_by_pat` = 0, `cancelled_by_doc` = 0, `reescheduled` = 1, `cost` = ?, `currency` = ?, `payed_through_confidr` = '' WHERE `consult_id` = ?"))){
			array_push($err,"ERROR at appointment insertion1");
		}
		elseif (!$stmt->bind_param("sississ", $payment_method, $appo_type_id, $userLoggedIn, $_REQUEST['ext_username'], $cost, $currency, $consult_id)){
			array_push($err,"ERROR at appointment insertion2");
		}
		elseif (!$stmt->execute()){
			array_push($err,"ERROR at appointment insertion3");
		}
		
		//Doctor Appointment calendar table
		if (false === ($stmt = $con->prepare("REPLACE INTO $appointments_table (`year`, `month`, `day`, `time_start`, `time_end`, `consult_id`, `confirmed_pat`, `confirmed_doc`, `creation_date_time`) VALUES (?,?,?,?,?,?,1,0,?)"))){
			array_push($err,"ERROR at appointment details insertion");
			//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','1d')");
		}
		elseif (!$stmt->bind_param("sssssss", $selected_year,$selected_month,$selected_day,$sel_time_st,$sel_time_end,$consult_id,$creation_date)){
			array_push($err,"ERROR at appointment details insertion");
			//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','2d')");
		}
		elseif (!$stmt->execute()){
			array_push($err,"ERROR at appointment details insertion");
			//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','3d')");
		}
		
	}

	//response

// 	if(in_array("ERROR at verification",$err)){
// 		http_response_code(409);
// 	}
// 	elseif(in_array("ERROR at verification pat",$err)){
// 		http_response_code(412);
// 	}
// 	elseif(!empty($err)){
// 		//print_r($err);
// 		http_response_code(400);
// 	}
// 	else{

//Schedule deletion after 1 hour

// 		$tab_db = $database_name . "." . $appointments_table;
// 		$tan_db = mysql_real_escape_string($tab_db);
// 		$tab_db_pat = $database_name . "." . $appointments_table_pat;
// 		$tan_db_pat = mysql_real_escape_string($tab_db);

// 		$query = mysqli_query($con,"CREATE EVENT $schedule_id ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 24 HOUR
//     		DO
//       		DELETE FROM $tab_db WHERE consult_id ='$consult_id' AND confirmed=0");
		
// 		$query = mysqli_query($con,"CREATE EVENT $schedule_id_pat ON SCHEDULE AT CURRENT_TIMESTAMP + INTERVAL 24 HOUR
//     		DO
//       		DELETE FROM $tab_db_pat WHERE consult_id ='$consult_id' AND confirmed=0");
		
// 		echo $consult_id;
// 	}

?>