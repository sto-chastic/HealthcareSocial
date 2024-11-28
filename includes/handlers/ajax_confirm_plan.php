<?php 
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/TxtReplace.php");

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
			$userLoggedIn_e = $temp_user_e;//Retrieves username
			
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
	
	$plan = $_POST['post_plan'];
	$doctor_username_e= pack("H*", $_POST['doctor_username']);
	$doctor_username = $crypt->Decrypt($doctor_username_e);
	$patient_username_e= pack("H*", $_POST['patient_username']);
	$patient_username = $crypt->Decrypt($patient_username_e);
	$aid= $_POST['aid'];
	$privacy= $_POST['plan_restriction'];
	
	$doctor_obj =  new User($con,$doctor_username, $doctor_username_e);
	$patient_obj	 = new User($con, $patient_username, $patient_username_e);
	
	//insert appointment details into the doctor database
	
	$appo_det_doc = $doctor_obj->getAppointmentsDetails_Doctor();
	
	$stmt = $con->prepare("UPDATE $appo_det_doc SET plan = ?, closed = 1 WHERE consult_id=?"); //AND closed=0");
	$stmt->bind_param("ss",$plan,$aid);
	$stmt->execute();
	
	//insert appointment details into the patient database
	
	$appo_det_pat = $patient_obj->getAppointmentsDetails_Patient();
	
	$stmt = $con->prepare("UPDATE $appo_det_pat SET plan = ?, closed = 1, private_plan=? WHERE consult_id=?");// AND closed=0");
	$stmt->bind_param("sis",$plan,$privacy,$aid);
	$stmt->execute();
?>