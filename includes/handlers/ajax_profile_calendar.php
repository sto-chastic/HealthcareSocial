<?php 
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/Appointments_Calendar.php");
	include("../classes/Calendar.php");
	include("../classes/TxtReplace.php");
	include("../classes/TimeStamp.php");
	$crypt = new Crypt();
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

	$profile_user_e = $txtrep->entities($_REQUEST['profile_user']);
	$profile_user_e =  pack("H*", $profile_user_e);
	$profile_user = $crypt->Decrypt($profile_user_e);

	$appo_calendar = new Appointments_Calendar($con, $profile_user, $profile_user_e, $_REQUEST['year'], $_REQUEST['month']); //request comes from ajax call

	$_temp_appo_type_id = $_REQUEST['appo_type_id'];
	
	$doctor_obj = new User($con, $profile_user, $profile_user_e);
	$appo_types_tab = $doctor_obj->getAppoDurationTable();
	$stmt = $con->prepare("SELECT * FROM $appo_types_tab WHERE id=?");
	$stmt->bind_param("s",$_temp_appo_type_id);
	$stmt->execute();
	$q = $stmt->get_result();
	$arr = mysqli_fetch_assoc($q);
	$appo_type_id = $arr['id'];
	$duration= $arr['duration'];
	
	if($_REQUEST['payment_method'] == 'part' || $_REQUEST['payment_method'] == 'insu'){
		echo $appo_calendar->getAvailabilityCalendar($_REQUEST['payment_method'],$duration,$appo_type_id);
	}
	else{
		$office_arr = $doctor_obj->getAvailableOfficesByInsurance($_REQUEST['payment_method']);
		echo $appo_calendar->getAvailabilityCalendarWithOffices('insu',$duration,$appo_type_id,$office_arr);
	}
?>