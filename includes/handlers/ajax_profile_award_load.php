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
		
		$txtrep = new TxtReplace();
		
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

$txtrep = new TxtReplace();
$elements_to_load = 5;
$lang = $_SESSION['lang'];

$latest_element_id= $_REQUEST['latest_element_id'];
$profile_user_e = pack("H*", $txtrep->entities($_REQUEST['profile_user']));
$profile_user = $crypt->Decrypt($profile_user_e);
$profile_user_obj = new User($con, $profile_user, $profile_user_e);

$doc_aw_tab = $profile_user_obj->getAwards_tab();

switch ($lang){
	
	case("en"):
		$lang = "en";
		break;
		
	case("es"):
		$lang = "es";
		break;
	default:
		$lang = "es";
}

$name = "name_$lang";
$desc = "description_$lang";

$sql = "SELECT aw.id, aw.$name, aw.$desc, docaw.votes FROM $doc_aw_tab AS docaw LEFT JOIN
 		awards AS aw
		ON docaw.award_code = aw.id ORDER BY docaw.votes DESC";

//echo $sql;
$stmt = $con->prepare($sql);
$stmt->execute();
$query = $stmt->get_result();

$elements_count = 0;
$elements_displayed = 0;

$size_results = mysqli_num_rows($query);

foreach ($query as $key => $val){
	$elements_count++;
	if($elements_count <= $latest_element_id && $latest_element_id != 0){
		continue;
	}
	$elements_displayed++;
	
	
	//Metal Status calculated here
	
	$_votes = $val['votes'];
	
	
	switch ($lang){
		
		case("en"):
			if($_votes < 100){
				$status = "bronze";
			}
			elseif ($_votes >= 100 && $_votes < 1000){
				$status = "silver";
			}
			elseif ($_votes >= 1000 && $_votes < 10000){
				$status = "gold";
			}
			elseif ($_votes >= 10000 && $_votes < 1000000){
				$status = "master";
			}
			else{
				$status = "supreme";
			}
			break;
			
		case("es"):
			if($_votes < 100){
				$status = "bronce";
			}
			elseif ($_votes >= 100 && $_votes < 1000){
				$status = "plata";
			}
			elseif ($_votes >= 1000 && $_votes < 10000){
				$status = "oro";
			}
			elseif ($_votes >= 10000 && $_votes < 1000000){
				$status = "maestro";
			}
			else{
				$status = "supremo";
			}
			break;
	}

	
	
	
	switch ($lang){
		
		case("en"):
			$times_str = ($val['votes'] == 1)?" time.":" times.";
			echo "<div class='internal_element_small' style='  width: 220px; height: 200px;'>
			<img src='assets/images/awards/" . $val['id'] . ".png' >
			<div class='connection_elem_name'>". $val[$name]. " <div class='info_icon' style=' float: unset;'>i<span class='tip_middle'>" . $val[$desc] . "</span></div></div>
			<p class='doc_small_spec profile_awards_text'> Received ".$val['votes'] . $times_str . " </p>
			<span class='".$status."'>" . ucwords($status) ."</span>" ;
			break;
			
		case("es"):
			$times_str = ($val['votes'] == 1)?" vez.":" veces.";
			echo "<div class='internal_element_small' style='  width: 220px; height: 200px;'>
			<img src='assets/images/awards/" . $val['id'] . ".png' >
			<div class='connection_elem_name'>". $val[$name]. " <div class='info_icon' style=' float: unset;'>i<span class='tip_middle'>" . $val[$desc] . "</span></div></div>
			<p class='doc_small_spec profile_awards_text'> Recibido ".$val['votes'] . $times_str . " </p>
			<span class='".$status."'>" . ucwords($status) ."</span>" ;
			break;
	}
	

	
	echo "</div>
		</div>";

	
	$ending = 0;
	if($elements_displayed>= $elements_to_load){
		$latest_loaded_element_id = $elements_count;
		break;
	}		
}

if($size_results== $elements_count){
	$latest_loaded_element_id = $elements_count;
	$ending= 1;
}
else{
	$ending= 0;
}

if($size_results == 0){
	switch ($lang){
		
	    case("en"):
	        echo "<div class='request_info' id='awards_request'> There are no awards to display at this time.</div>";
	        break;
	        
	    case("es"):
	        echo "<div class='request_info' id='awards_request'> No hay premios en este momento.</div>";
	        break;
	}
	
	echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='0'>
		<input type='hidden' id='ending_' name='ending_' value='1'>";
}
else{
	echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='" . $latest_loaded_element_id . "'>
				<input type='hidden' id='ending_' name='ending_' value='" . $ending . "'>";
}

