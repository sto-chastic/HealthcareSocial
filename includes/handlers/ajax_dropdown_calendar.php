<?php 
	include("../../config/config.php");
	include("../classes/User.php");
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
	
	$calendar = new Calendar($con, $userLoggedIn, $userLoggedIn_e/*$crypt->EncryptU($_REQUEST['user'])*/); //request comes from ajax call
	$month = (int)$_REQUEST['month'];
	$year = (int)$_REQUEST['year'];
	echo $calendar->getDropDownCalendar($month,$year);
?>