<?php 
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/Appointments_Calendar_Home.php");
	include("../classes/Calendar.php");
	include("../classes/TxtReplace.php");
	include("../classes/TimeStamp.php");
	
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
	
	$appo_calendar = new Appointments_Calendar_Home($con, $userLoggedIn, $userLoggedIn_e, $_REQUEST['year'], $_REQUEST['month']); //request comes from ajax call
	
	$view_type = $_REQUEST['view_type'];
	if($_REQUEST['isdoc'] == 1 && !isset($_REQUEST['4reesched'])){
		echo $appo_calendar->getAppointmentsCalendarLinks($view_type);
	}
	elseif($_REQUEST['isdoc'] == 1 && isset($_REQUEST['4reesched'])){
		echo $appo_calendar->getAppointmentsCalendar(2);
	}
	else{
		echo $appo_calendar->getAppointmentsCalendar($view_type);
	}
?>