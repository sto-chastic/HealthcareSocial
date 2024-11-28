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
		$userLoggedIn_e = $temp_user_e;
		$final_mess_token = $temp_messages_token;
		$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
		
		$txt_rep = new TxtReplace();
		
		$lang = $_SESSION['lang'];
	}
	else{
		 			echo "3";
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
}
else{
	 		echo "2";
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

$elements_to_load = 3;
$elements_to_load_query = $elements_to_load+1;//so that the display is bounded by the PHP, except if there are no more messages at all, then the query bounds it

$latest_element_id= $_REQUEST['latest_element_id'];
	

$data = "";	
$connection_requests = $user_obj->getRequestsTable();


if($latest_element_id== 0){
	$stmt = $con->prepare("SELECT * FROM $connection_requests ORDER BY id DESC LIMIT $elements_to_load_query");
}
else{
	$stmt = $con->prepare("SELECT * FROM $connection_requests WHERE id < ? ORDER BY id DESC LIMIT $elements_to_load_query");
	$stmt->bind_param("i", $latest_element_id);
}
$stmt->execute();
$verification_query = $stmt->get_result();


if(mysqli_num_rows($verification_query) == 0){
    switch ($lang){
        
        case("en"):
            echo "<div class='request_alert'> You have no connection requests at this time.</div>";
            break;
            
        case("es"):
            echo "<div class='request_alert'> No tienes solicitudes de conexión pendientes.</div>";
            break;
    }

	echo "<input type='hidden' id='latest_loaded_element_id_req' name='latest_loaded_element_id_req' value='0'>
				<input type='hidden' id='ending_req' name='ending_req' value='1'>";
}
else{
	$elements_count = 0;
	$ending = 0;
	
	$arr_ordered = [];
	$i = 0;
	while($val = mysqli_fetch_array($verification_query)){
		
// 		$arr_ordered[$i] = $arr;
// 		$i++;
// 	}
	
// 	for($j=$i-1;$j>=0;$j--){
// 		$val = $arr_ordered[$j];
		
// 		if($j==$i-1){
// 			$latest_loaded_element_id = $val['id'];
// 		}
		
		
		$user_from_temp = $val['user_from'];
		
		
		//validation of the users
		try{
			$user_from_obj = new User($con, $user_from_temp, $crypt->EncryptU($user_from_temp)); //whitelisted, implement try catch
			$user_from = $user_from_temp;
			$user_from_e = $crypt->EncryptU($user_from_temp);
			if($user_from_obj->isDoctor()){
				$specialization = $user_from_obj->getSpecializationsTextShort($lang,25);
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
			
		}
		catch ( Exception $e ){
			continue;
		}
		
		
		echo "<div class='internal_element'>
                <img src='" . $txt_rep->entities($user_from_obj->getProfilePicFast()) . "' >
				<div class='request_info'>".$txt_rep->entities( $user_from_obj->getFirstAndLastNameShort(35)) . "</div>
				<p class='req_small_spec'>".$specialization."</p>";
		
		?>
			<form action="requests.php" method="POST" style=" display: table-caption; width: 190px; padding-left: 5px;">
				
				<input type="submit" name="accept_request<?php echo bin2hex($user_from_e); ?>" id="accept_button" style=" width: 40%;" 
				value="<?php switch($lang){
				    case("en"): echo "Accept"; break;
				    case("es"): echo "Aceptar"; break;
				}?>">
				<input type="submit" name="ignore_request<?php echo bin2hex($user_from_e);?>" id="ignore_button" style=" width: 40%;" 
				value="<?php switch($lang){
				    case("en"): echo "Ignore"; break;
				    case("es"): echo "Ignorar"; break;
				}?>">
			</form>
		<?php
		
		echo "</div>";
		
		$ending = 0;
		if($elements_count>= $elements_to_load){
			$latest_loaded_element_id = $val['id'];
			break;
		}
		$elements_count++;
		$latest_loaded_element_id = $val['id'];
		$ending= 1;
		
	}
	echo "<input type='hidden' id='latest_loaded_element_id_req' name='latest_loaded_element_id_req' value='" . $latest_loaded_element_id . "'>
				<input type='hidden' id='ending_req' name='ending_req' value='" . $ending . "'>";
	
}
	
?>