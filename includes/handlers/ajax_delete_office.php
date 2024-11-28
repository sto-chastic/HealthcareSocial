<?php 
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/Post.php");
	include("../classes/TimeStamp.php");
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
	
	$numb = $_POST['office_numb'];
	$username = $_POST['user'];
	switch($numb){
		case "1":
			$name = "ad1nick";
			$transfered_from = 1;
			$adln1 = "ad1ln1";
			$adln2 = "ad1ln2";
			$adln3 = "ad1ln3";
			$adcity = "ad1city";
			$adadm2 = "ad1adm2";
			$adlat = "ad1lat";
			$adlng = "ad1lng";
			$insurance = "insurance_accepted_1";
			break;
		case "2":
			$name = "ad2nick";
			$transfered_from = 2;
			$adln1 = "ad2ln1";
			$adln2 = "ad2ln2";
			$adln3 = "ad2ln3";
			$adcity = "ad2city";
			$adadm2 = "ad2adm2";
			$adlat = "ad2lat";
			$adlng = "ad2lng";
			$insurance = "insurance_accepted_2";
			break;
		case "3":
			$name = "ad3nick";
			$transfered_from = 3;
			$adln1 = "ad3ln1";
			$adln2 = "ad3ln2";
			$adln3 = "ad3ln3";
			$adcity = "ad3city";
			$adadm2 = "ad3adm2";
			$adlat = "ad3lat";
			$adlng = "ad3lng";
			$insurance = "insurance_accepted_3";
			break;
	}
	
	$transfer_selector = $_POST['transfer_selector'];
	switch($transfer_selector){
		case "1":
			$transfer_selected = "1";
			break;
		case "2":
			$transfer_selected = "2";
			break;
		case "3":
			$transfer_selected = "3";
			break;
	}
	//DELETE OFFICE
	$stmt = $con->prepare("UPDATE basic_info_doctors SET $name = NULL, $insurance = NULL, $adln1 = NULL, $adln2 = NULL, $adln3=NULL, $adcity = NULL, $adadm2 = NULL, $adlat=NULL, $adlng=NULL WHERE username =?");
	$stmt->bind_param("s",$username);
	$stmt->execute();
	
	//CHANGE SELECTED TIMES FOR OFFICE FROM DEFAULT CALENDAR
	$user_obj = new User($con, $username, $crypt->EncryptU($username));
	$calendar_availability = $user_obj->getAvailableCalendar();
	
	$stmt = $con->prepare("show fields from $calendar_availability");
	//echo "err1: " . $stmt->error;
	$stmt->execute();
	$q = $stmt->get_result();

	$array_of_tab_fields = [];
	foreach($q as $key => $val){
		$field = $val['Field'];
		$array_of_tab_fields[] = $field;
		//echo "field" . $field;
		$stmt = $con->prepare("UPDATE $calendar_availability SET $field = CASE WHEN $field = ? THEN ? ELSE $field END WHERE 1");
		$stmt->bind_param("ii",$transfered_from,$transfer_selected);
		$stmt->execute();
	}
	
	//CHANGE TIMES FROM CUSTUM CALENDARS
	
	$week_id = $username. "__doc_conf_week__%";
	$calendar_availability = $week_id;

	$existance = mysqli_query($con, "SHOW TABLES LIKE '$calendar_availability'");
	$num_existant_tabs = mysqli_num_rows($existance);
	
	while($arr = mysqli_fetch_row($existance)){
		$current_cust_table = $arr['0'];
		foreach ($array_of_tab_fields as $key => $field){
			
			$stmt = $con->prepare("UPDATE $current_cust_table SET $field = CASE WHEN $field = ? THEN ? ELSE $field END WHERE 1");
			$stmt->bind_param("ii",$transfered_from,$transfer_selected);
			$stmt->execute();
		}
	}

 ?>

