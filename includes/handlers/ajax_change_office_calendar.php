<?php
include ("../../config/config.php");
include ("../classes/User.php");
include ("../classes/Appointments_Calendar.php");
include ("../classes/Calendar.php");
include ("../classes/SearchNMap.php");
include ("../classes/TxtReplace.php");
$crypt = new Crypt();

$lang = $_SESSION['lang'];

$username = $_REQUEST ['user'];
$username_e = $crypt->EncryptU($username);

$user_obj = new User ( $con, $username, $username_e);
$SnM = new SearchNMap($con);

$date = $_REQUEST ['date'];
$office = $_REQUEST ['office'];
$payment = $_REQUEST ['payment'];
$insurance = $_REQUEST ['insurance'];

$office_arr = $user_obj->getOfficeDetails ();

$adLn1Index = 'ad' . $office . 'ln1';
$adLn2Index = 'ad' . $office . 'ln2';

$adLn1 = $office_arr [$adLn1Index];
$adLn2 = $office_arr [$adLn2Index];


$app_dur_tab = $user_obj->getAppoDurationTable(); // ;

$stmt = $con->prepare ( "SELECT id,appo_type,duration FROM $app_dur_tab WHERE appo_type LIKE ? OR appo_type LIKE ? LIMIT 1" );

$at_en = '_irst__ime%';
$at_es = '_rimera _ez%';
$stmt->bind_param ( "ss", $at_en, $at_es );
$stmt->execute ();
$verification_query = $stmt->get_result ();

if (mysqli_num_rows ( $verification_query ) == 1) {
	$_id_arr = mysqli_fetch_assoc ( $verification_query );
	$appointment_type = $_id_arr ['appo_type'];
	$appo_type_id = $_id_arr ['id'];
	$appointment_duration = $_id_arr ['duration'];
}

$available_days_array = $SnM->closestAppointmentDays ( $date, $username, $username_e, $office, $payment, $appointment_duration);

$days_processed = [ ];
$days_content_arr = [ ];
foreach ( $available_days_array as $day_raw ) {
	
	// Construction of the dates title
	$stmt = $con->prepare ( "SELECT d,dw,m,y FROM calendar_table WHERE dt=?" );
	$stmt->bind_param ( "s", $day_raw );
	$stmt->execute ();
	
	$q = $stmt->get_result ();
	if (mysqli_num_rows ( $q ) == 0) {
		continue;
	}
	$arr = mysqli_fetch_assoc ( $q );
	$_temp_str = "";
	
	switch ($lang) {
		case "en" :
			$week_tab_col = "days_short_eng";
			$months_tab_col = "months_eng";
			break;
		case "es" :
			$week_tab_col = "days_short_es";
			$months_tab_col = "months_es";
			break;
	}
	
	$year = $arr ['y'];
	$month = $arr ['m'];
	
	$stmt = $con->prepare ( "SELECT $week_tab_col FROM days_week WHERE dw=?" );
	$stmt->bind_param ( "i", $arr ['dw'] );
	$stmt->execute ();
	
	$query = $stmt->get_result ();
	$days_arr = mysqli_fetch_assoc ( $query );
	
	$_temp_str .= $days_arr [$week_tab_col] . "<br>";
	
	$stmt = $con->prepare ( "SELECT $months_tab_col FROM months WHERE id=?" );
	$stmt->bind_param ( "i", $arr ['m'] );
	$stmt->execute ();
	
	$query = $stmt->get_result ();
	$month_arr = mysqli_fetch_assoc ( $query );
	
	$_temp_str .= substr ( $month_arr [$months_tab_col], 0, 3 ) . ", " . $arr ['d'];
	
	$days_processed [] = $_temp_str;
	
	// Times content
	

	
	$appointments_calendar = new Appointments_Calendar ( $con, $username, $username_e, $year, $month );
	
	$booking_info_array = array (
			"doc_name" => $user_obj->getFirstAndLastNameFast (),
			"year" => $year,
			"month" => $month,
			"day" => $arr ['d'],
			"week_day" => $days_arr [$week_tab_col],
			"doctor_username" => $username,
			"payment_method" => $payment,
			"appo_type_id" => $appo_type_id,
			"insurance_name" => $insurance,
			"adln1" => $adLn1,
			"adln2" => $adLn2,
			"time_st" => "",
			"time_end" => "",
			"display_time" => "" 
	);
	
	if ($payment == 'insu') {
		$office_arr = array (
				"0" => $office 
		);
		$day_content = $appointments_calendar->getDay_searchResults ( $arr ['d'], $payment, $appointment_duration, $booking_info_array, $office_arr, FALSE );
		$days_content_arr [] = $day_content;
	} elseif ($payment == 'part') {
		$day_content = $appointments_calendar->getDay_searchResults ( $arr ['d'], $payment, $appointment_duration, $booking_info_array, NULL, FALSE );
		$days_content_arr [] =  $day_content;
	}
}

if(!empty($days_processed)){

	echo '
		<div class="ds_ava_banner">
			<div class="ds_ava_banner_element">
				' . ((array_key_exists ( 0, $days_processed )) ? $days_processed [0] : 'NA') . '
			</div>
			<div class="ds_ava_banner_element">
				' . ((array_key_exists ( 1, $days_processed )) ? $days_processed [1] : 'NA') . '
			</div>
			<div class="ds_ava_banner_element">
				' . ((array_key_exists ( 2, $days_processed )) ? $days_processed [2] : 'NA') . '
			</div>
		</div>
		<div class="ds_ava_content">
			<div class="ds_ava_element style-2">
				' . ((array_key_exists ( 0, $days_content_arr )) ? $days_content_arr [0] : '') . '
			</div>
			<div class="ds_ava_element style-2">
				' . ((array_key_exists ( 1, $days_content_arr )) ? $days_content_arr [1] : '') . '
			</div>
			<div class="ds_ava_element style-2">
				' . ((array_key_exists ( 2, $days_content_arr )) ? $days_content_arr [2] : '') . '
			</div>
		</div>';

}
else{
	switch ($lang){
		
		case("en"):
			echo "No Appointments Available.";
			break;
			
		case("es"):
			echo "No hay citas disponibles.";
			break;
	}
	
}