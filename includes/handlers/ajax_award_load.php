<?php

include("../../config/config.php");
include("../classes/User.php");
include("../classes/TxtReplace.php");

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
		header("Location:../../register.php");
		$stmt->close();
	}
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location:../../register.php");
	$stmt->close();
}

$elements_to_load = 14;
$lang = $_SESSION['lang'];

$latest_element_id= $_REQUEST['latest_element_id'];

switch ($lang){
	case "en":
		$lang = "en";
		break;
	case "es":
		$lang = "es";
		break;
	default:
		$lang = "es";
}

if((isset($_REQUEST['search_terms']) ? $_REQUEST['search_terms'] : "") != ""){
	
	
	$search_terms_caps = ucwords($_REQUEST['search_terms']);
	$search_terms= $_REQUEST['search_terms'];
	
	$search_term_arr = explode(" ",$search_terms);
	$search_t = "%" . implode("%", $search_term_arr) . "%";
	
	$search_term_arr_caps = explode(" ",$search_terms_caps);
	$search_t_caps = "%" . implode("%", $search_term_arr_caps) . "%";
	
	$name = "name_$lang";
	$desc = "description_$lang";
	$sql = "SELECT id, $name, $desc FROM awards WHERE $name LIKE ? OR $name LIKE ? OR $desc LIKE ? ORDER BY id ASC";
	
	$stmt = $con->prepare($sql);
	$stmt->bind_param("sss",$search_t_caps,$search_t,$search_t);
	$stmt->execute();
	$query = $stmt->get_result();
	
	$elements_count = 0;
	$elements_displayed = 0;
	
	$size_results = mysqli_num_rows($query);
	
	foreach ($query as $id_minus_1 => $val){
		$elements_count++;
		if($elements_count <= $latest_element_id && $latest_element_id != 0){
			continue;
		}
		$elements_displayed++;
		
		echo "<div class='internal_element_small unsel_award' id='selected_award_".$val['id']."'  style=' height: 220px;cursor: pointer; width: unset;'>
				<img src='assets/images/awards/" . $val['id'] . ".png' >
				<div class='connection_elem_name'>". $val[$name]. "<br></div>
				<p class='doc_small_spec'>".$val[$desc]."</p>
				<input type='hidden' name='award_sel_input_".$val['id']."' id='award_sel_input_".$val['id']."' value=0>";
		
		echo "</div>
			</div>";
		
		
		?>
			<script>

			$('#selected_award_' + "<?php echo $val['id']; ?>").click(function() {
				$('.sel_award').toggleClass('unsel_award');
				$('.sel_award').toggleClass('sel_award');

				$('#selected_award_' + "<?php echo $val['id']; ?>").toggleClass('unsel_award');
				$('#selected_award_' + "<?php echo $val['id']; ?>").toggleClass('sel_award');

				$("[id^=award_sel_input_]").val(0);
				$("#award_sel_input_" + "<?php echo $val['id']; ?>").val(1);
				
			});
			
			</script>
			
		<?php
		
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
	        
	        case("en"):{
	            echo "<div class='request_alert'> There are no awards at this time.</div>";
	            break;
	        }
	        case("es"):{
	            echo "<div class='request_alert'> No hay premios disponibles ahora.</div>";
	            break;
	        }
	    }
	
		echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='0'>
			<input type='hidden' id='ending_' name='ending_' value='1'>";
	}
	else{
		echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='" . $latest_loaded_element_id . "'>
					<input type='hidden' id='ending_' name='ending_' value='" . $ending . "'>";
	}

}
else{
	$name = "name_$lang";
	$desc = "description_$lang";
	$sql = "SELECT id, $name, $desc FROM awards ORDER BY id ASC";
	$stmt = $con->prepare($sql);
	$stmt->execute();
	$query = $stmt->get_result();
	
	$elements_count = 0;
	$elements_displayed = 0;
	
	$size_results = mysqli_num_rows($query);
	
	foreach ($query as $id_minus_1 => $val){
		$elements_count++;
		if($elements_count <= $latest_element_id && $latest_element_id != 0){
			continue;
		}
		$elements_displayed++;
		
		echo "<div class='internal_element_small unsel_award' id='selected_award_".$val['id']."''>
				<img src='assets/images/awards/" . $val['id'] . ".png' >
				<div class='connection_elem_name'>". $val[$name]. "<br></div>
				<p class='doc_small_spec'>".$val[$desc]."</p>
				<input type='hidden' name='award_sel_input_".$val['id']."' id='award_sel_input_".$val['id']."' value=0>";
		
		echo "</div>
			</div>";
		
		
		?>
			<script>

			$('#selected_award_' + "<?php echo $val['id']; ?>").click(function() {
				$('.sel_award').toggleClass('unsel_award');
				$('.sel_award').toggleClass('sel_award');

				$('#selected_award_' + "<?php echo $val['id']; ?>").toggleClass('unsel_award');
				$('#selected_award_' + "<?php echo $val['id']; ?>").toggleClass('sel_award');

				$("[id^=award_sel_input_]").val(0);
				$("#award_sel_input_" + "<?php echo $val['id']; ?>").val(1);
				
			});
			
			</script>
			
		<?php
		
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
	        
	        case("en"):{
	            echo "<div class='request_alert'> There are no awards at this time.</div>";
	            break;
	        }
	        case("es"):{
	            echo "<div class='request_alert'> No hay premios disponibles ahora.</div>";
	            break;
	        }
	    }
		echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='0'>
			<input type='hidden' id='ending_' name='ending_' value='1'>";
	}
	else{
		echo "<input type='hidden' id='latest_loaded_element_id_' name='latest_loaded_element_id_' value='" . $latest_loaded_element_id . "'>
					<input type='hidden' id='ending_' name='ending_' value='" . $ending . "'>";
	}
}
