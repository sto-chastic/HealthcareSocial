<?php
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/Post.php");
	include("../classes/TimeStamp.php");
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
			
			$appo_mast = new Appointments_Master($con, $userLoggedIn, $userLoggedIn_e);
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
	
	$cid = $_REQUEST['cid'];
	$appo_mast->doctor_view_ConfirmAppointment($cid);
	
	$lang = $_SESSION['lang'];
	
	switch ($lang){
		
		case("en"):
			echo "Confirmed";
			break;
		case("es"):
			echo "Confirmado";
			break;
	}