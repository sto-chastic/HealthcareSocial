<?php 
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/Appointments_Calendar.php");
	include("../classes/Appointments_Master.php");
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
	
	$txtrep =  new TxtReplace();
	$lang = $_SESSION['lang'];
	
	$year = $_REQUEST['year'];
	$month = $_REQUEST['month'];
	$day = $_REQUEST['day'];
	$profile_owner_e = pack("H*",$_REQUEST['profile_owner']);
	$profile_owner = $crypt->Decrypt($profile_owner_e);
	
	$profile_owner_obj = new User($con,$profile_owner, $profile_owner_e);
	$payment_method = $_REQUEST['payment_method'];
	$appo_type = $_REQUEST['ap_type'];
	$ap_id = $_REQUEST['ap_id'];

	switch ($lang){
		
		case("en"):
			$months_column = 'months_eng';
			break;
			
		case("es"):
			$months_column = 'months_es';
			break;
	}
	
	$stmt = $con->prepare("SELECT $months_column FROM months WHERE id = ?");
	$stmt->bind_param("i", $_REQUEST['month']);
	$stmt->execute();

	$month_name_q = $stmt->get_result();
	$arr = mysqli_fetch_array($month_name_q);
	$month_name = $arr[$months_column];

	$ap_start = $_REQUEST['ap_st'];
	$ap_start_obj = new DateTime($ap_start);
	
	$ap_end = $_REQUEST['ap_end'];
	$ap_end_obj = new DateTime($ap_end);
	
	$calendar_availability = new Calendar($con, $profile_owner, $crypt->EncryptU($profile_owner));
	$office_num = $calendar_availability->getIntervalOffice($payment_method,$ap_start_obj,$ap_end_obj,$day,$month,$year);
	
	$appointment_master = new Appointments_Master($con, $profile_owner, $crypt->EncryptU($profile_owner));
	$office_num_arr = $appointment_master->getOfficeData($profile_owner,$payment_method,$ap_start_obj,$ap_end_obj,$day,$month,$year);
	
	$usr_bin_e = bin2hex($profile_owner_e);
	
	$_add1 = "ad" . $office_num . "ln1";
	$address1 = $office_num_arr[$_add1];
	$_add2 = "ad" . $office_num . "ln2";
	$address2 = $office_num_arr[$_add2];
	$_add3 = "ad" . $office_num . "ln3";
	$address3 = $office_num_arr[$_add3];
	
	$_city = "ad" . $office_num . "city";
	$city = $office_num_arr[$_city];
	$_adm2 = "ad" . $office_num . "adm2";
	$adm2 = $office_num_arr[$_adm2];
	
	$country = $office_num_arr['adcountry'];
	
	$_lat = "ad" . $office_num. "lat";
	$lat = $office_num_arr[$_lat];
	$_lng = "ad" . $office_num. "lng";
	$lng = $office_num_arr[$_lng];
	
	switch ($lang){
		
		case("es"):
			$str = "<div id='confirm_booking'>
				<p> ¿Quieres agendar la cita para: <b>" . $ap_start . "</b> a <b>" . $ap_end . "</b>, el <b>" . $month_name . " " . $_REQUEST['day'] . "</b>, " . $_REQUEST['year'] . ",<br> con: <b>Dr. " . $txtrep->entities($profile_owner_obj->getLastName()) ."</b>?
				</p>
			</div>";
			$str .= "<div id='confirm_address'><p>Esta cita será programada en: </p><b>" . $txtrep->entities($address1) . ", " . $txtrep->entities($address2) . ", " . $txtrep->entities($address3) . "</b></div>";
			$str .=
			<<<EOS
            <div id='confirm_buttons' style='  float: none;'>
    				<a href="javascript:void(0);" onclick="cancelBookingSelection();">
    					<div class='small_butt danger'>Cancelar</div>
    				</a>
    				<a href="javascript:void(0);" onclick="acceptBookingSelection('$year','$month','$day','$ap_start','$ap_end','$usr_bin_e','$appo_type','$payment_method','$ap_id');">
    					<div class='small_butt deep_blue'>Aceptar</div>
    				</a>
            </div>
EOS;
			break;
			
		case("en"):
		    $str = "<div id='confirm_booking'>
				<p> Do you really want to book an appointment from <b>" . $ap_start . "</b> to <b>" . $ap_end . "</b>, on <b>" . $month_name . " " . $_REQUEST['day'] . "</b>, " . $_REQUEST['year'] . ",<br> with <b>Dr. " . $txtrep->entities($profile_owner_obj->getLastName()) ."</b>?
				</p>
			</div>";
		    $str .= "<div id='confirm_address'><p>This appointment will be held at: </p><b>" . $txtrep->entities($address1) . ", " . $txtrep->entities($address2) . ", " . $txtrep->entities($address3) . "</b></div>";
		    $str .=
		    <<<EOS
                <div id='confirm_buttons' style=' float: none;'>
    				<a href="javascript:void(0);" onclick="cancelBookingSelection();">
    					<div class='small_butt danger'>Cancel</div>
    				</a>
    				<a href="javascript:void(0);" onclick="acceptBookingSelection('$year','$month','$day','$ap_start','$ap_end','$usr_bin_e','$appo_type','$payment_method','$ap_id');">
    					<div class=' small_butt deep_blue'>Accept</div>
    				</a>
                </div>
EOS;
		    
		    
		    break;
	}
	

	
//	$str .= "<p>" . $txtrep->entities($city) . ", " . $txtrep->entities($adm2) . ", " . $txtrep->entities($country) . "</p><br>";
	//TODO: Adequate the codes to display real names
	
	echo $str;
?>