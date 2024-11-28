<?php

include("../../config/config.php");
include("../classes/User.php");
include("../classes/TxtReplace.php");

$crypt = new Crypt();
		
$txt_rep = new TxtReplace();
		
$lang = $_SESSION['lang'];
		
//Verify valid doctor user
		
$temp_doctor_e = pack("H*",$_REQUEST['d']);
$temp_doctor = $crypt->Decrypt($temp_doctor_e);
		
$stmt = $con->prepare("SELECT * FROM users WHERE username=?");
		
$stmt->bind_param("s", $temp_doctor_e);
$stmt->execute();
$verification_query = $stmt->get_result();
		
if(mysqli_num_rows($verification_query) == 1){
	$verification_arr = mysqli_fetch_assoc($verification_query);
	$doc_username_e = $verification_arr['username'];
	$doc_username = $temp_doctor;
			
	$doc_user_obj = new User($con, $doc_username, $doc_username_e);
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: register.php");
	$stmt->close();
}

$elements_to_load = 5;
$elements_to_load_query = $elements_to_load+1;//so that the display is bounded by the PHP, except if there are no more messages at all, then the query bounds it

$latest_element_id= $_REQUEST['latest_element_id'];
$doctor= 1; //1 is searching for doctors, 0 is searching for patients


$data = "";
$connections = $doc_user_obj->getConnections_tab();

if(isset($_REQUEST['search_terms'])){
	//Deprecated, this is no longer in this ajax file, it was moved to ajax_connections_search
	//$search_terms= $_REQUEST['search_terms'];	
}
else{
	if($latest_element_id== 0){
		$stmt = $con->prepare("SELECT * FROM $connections WHERE doctor = ? ORDER BY id DESC LIMIT $elements_to_load_query");
		$stmt->bind_param("i",$doctor);
	}
	else{
		$stmt = $con->prepare("SELECT * FROM $connections WHERE id < ? AND doctor = ? ORDER BY id DESC LIMIT $elements_to_load_query");
		$stmt->bind_param("ii", $latest_element_id,$doctor);
	}
	$stmt->execute();
	$verification_query = $stmt->get_result();
}

if(mysqli_num_rows($verification_query) == 0){
	switch ($lang){
		
		case("en"):
			echo "<div class='request_alert'> There is no connections to show.</div>";
			break;
			
		case("es"):
			echo "<div class='request_alert'> No hay conexiones que mostrar.</div>";
			break;
	}
	
	echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='0'>
				<input type='hidden' id='ending_' name='ending_' value='1'>";
}
else{
	$elements_count = 0;
	$ending = 0;
	
	$arr_ordered = [];

	while($val = mysqli_fetch_array($verification_query)){
				
		$user_connection = $val['username_friend'];
		$user_connection_e = $crypt->EncryptU($user_connection);
		
		$user_connection_obj = new User($con, $user_connection,$user_connection_e);

		if($user_connection_obj->isDoctor()){
			$specialization = $user_connection_obj->getSpecializationsTextShort($lang,40);
		}
		else{
			switch ($lang){
				
				case("en"):
					$specialization = "(Patient)";
					break;
					
				case("es"):
					$specialization = "(Paciente)";
					break;
			}
			
		}
		
		echo "<div class='internal_element_small'>
				<a href='". bin2hex($user_connection_e) ."' >
				<img src='" . $txt_rep->entities($user_connection_obj->getProfilePicFast()) . "' >
				<div class='connection_elem_name'>".$txt_rep->entities($user_connection_obj->getFirstAndLastNameShort(35)) . "<br></div>
				<p class='doc_small_spec' style ='text-align:center;width: 100%; margin: 0px;'>".$specialization."</p>";
		
		echo "</a></div>";
		
		$ending = 0;
		if($elements_count>= $elements_to_load){
			$latest_loaded_element_id = $val['id'];
			break;
		}
		$elements_count++;
		$latest_loaded_element_id = $val['id'];
		$ending= 1;
		
	}
	echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='" . $latest_loaded_element_id . "'>
				<input type='hidden' id='ending_' name='ending_' value='" . $ending . "'>";
	
}
	
?>