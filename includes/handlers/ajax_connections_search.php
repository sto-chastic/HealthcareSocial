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
	session_d("Location: ../../register.php");
	$stmt->close();
}

if($userLoggedIn != ""){

	$latest_element_id= $_REQUEST['latest_element_id'];
	$doctor= $_REQUEST['doctor']; //1 is searching for doctors, 0 is searching for patients
	
	$elements_to_load = 6;
	
	if(isset($_REQUEST['search_terms'])){
		
		$search_terms= $_REQUEST['search_terms'];
		
		$search_term_arr = explode(" ",$search_terms);
		
		$data = "";
		$connections = $user_obj->getConnections_tab();
		
		$stmt = $con->prepare("SELECT conn.*, u.username, u.first_name_d, u.last_name_d FROM $connections AS conn INNER JOIN users AS u ON conn.username_friend = u.username WHERE conn.doctor = ?");
		$stmt->bind_param("i",$doctor);
		$stmt->execute();
		$query = $stmt->get_result();
		
		$results_arr = [];
		
		if(mysqli_num_rows($query) > 0){
			foreach($query as $key => $arr){
				
				foreach($search_term_arr as $search_term){
					//echo "st: " . $search_term;
					$user_connection_obj= new User($con, $arr['username'], $crypt->EncryptU($arr['username']));
					$name =  $arr['first_name_d'] . " " . $arr['last_name_d'];
					$specialization = $user_connection_obj->getSpecializationsText($lang);
					
					$heystack_spec = $txt_rep->entities($specialization);
					$heystack_name = $txt_rep->entities($name);
					
					$search_term_wild = "*" . strtolower($search_term) . "*";
					
					if(!fnmatch($search_term_wild, strtolower($heystack_spec)) && !fnmatch($search_term_wild, strtolower($heystack_name))){
	
						if( array_key_exists($arr['username'], $results_arr)){
							unset($results_arr[$arr['username']] );
						}
						continue 2;
					}
					else{
						if(!in_array($arr['username'], $results_arr)){
							$results_arr[$arr['username']] = 1;
						}
						else{
							$results_arr[$arr['username']] = $results_arr[$arr['username']]++;
						}
					}
				}
			}
			
			asort($results_arr);
			
			$elements_count = 0;
			$elements_displayed = 0;
			
			foreach ($results_arr as $_username => $val){
				//$user_connection = $val[$_username];
				$elements_count++;
				if($elements_count <= $latest_element_id && $latest_element_id != 0){
					continue;	
				}
				
				$user_connection_obj = new User($con, $_username, $crypt->EncryptU($_username));
				$elements_displayed++;
				
				
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
					<a href='". bin2hex($crypt->EncryptU($_username)) ."' >
					<img src='" . $txt_rep->entities($user_connection_obj->getProfilePicFast()) . "' >
					<div class='connection_elem_name'>".$txt_rep->entities($user_connection_obj->getFirstAndLastNameShort(35)) . "<br></div>
					<p class='doc_small_spec'style ='text-align:center;width: 100%; margin: 0px;'>".$specialization."</p></div>";
				
				echo "</a></div>";
				
				$ending = 0;
				if($elements_displayed>= $elements_to_load){
					$latest_loaded_element_id = $elements_count;
					break;
				}
				
				
			}
			
			if(sizeof($results_arr) == $elements_count){
				$latest_loaded_element_id = $elements_count;
				$ending= 1;
			}
			else{
				$ending= 0;
			}
			
			if(empty($results_arr)){
				switch ($lang){
					
					case("en"):
						echo "<div class='request_alert'> You have no connections matching this search criteria.</div>";
						break;
						
					case("es"):
						echo "<div class='request_alert'> No tienes conexiones que satisfagan tus criterios de búsqueda.</div>";
						break;
				}
				
				echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='0'>
				<input type='hidden' id='ending_' name='ending_' value='1'>";
			}
			else{
				echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='" . $latest_loaded_element_id . "'>
						<input type='hidden' id='ending_' name='ending_' value='" . $ending . "'>";
			}
		}
	
	}
	else{
	
	}
	
}
else{
	die;
}
