<?php

include("../../config/config.php");
include("../classes/User.php");
include("../classes/TxtReplace.php");

$crypt = new Crypt();

$userLoggedIn="";

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
		$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
		$txt_rep = new TxtReplace();
		
		$lang = $_SESSION['lang'];
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

if($userLoggedIn){
	$elements_to_load = 3;
	$elements_to_load_query = $elements_to_load+1;//so that the display is bounded by the PHP, except if there are no more messages at all, then the query bounds it
	
	$latest_element_id= $_REQUEST['latest_element_id'];
	$doctor= $_REQUEST['doctor']; //1 is searching for doctors, 0 is searching for patients
	
	
	$data = "";
	$connections = $user_obj->getConnections_tab();
	
	if(isset($_REQUEST['search_terms'])){
		//Deprecated, this is no longer in this ajax file, it was moved to ajax_connections_search
		$search_terms= $_REQUEST['search_terms'];	
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
				echo "<div class='request_alert'> You have no connections.</div>";
				break;
				
			case("es"):
				echo "<div class='request_alert'> No tienes conexiones.</div>";
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
			$user_connection_obj = new User($con, $user_connection, $user_connection_e);
	
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
}
else{
	die;
}
?>