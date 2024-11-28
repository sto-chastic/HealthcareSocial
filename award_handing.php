<?php
include ('includes/header.php');

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
		header("Location: register.php");
		$stmt->close();
	}
}
else{
	echo "2";
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: register.php");
	$stmt->close();
}

if(isset($_GET['cid'])){
	
	$today_ = new DateTime();
	$today_hour = $today_->format("H:i");
	$today_day = $today_->format("d");
	$today_month = $today_->format("m");
	$today_year = $today_->format("Y");
	
	$appo_dets = $user_obj->getAppointmentsDetails_Patient();
	$appo_cal = $user_obj->getAppointmentsCalendar_Patient();
	
	$stmt = $con->prepare("SELECT ad.doctor_username FROM $appo_cal AS ac LEFT JOIN
							$appo_dets AS ad
							ON ac.consult_id = ad.consult_id
							WHERE (ac.consult_id = ?
							AND (ac.year < ? OR (ac.year = ? AND ac.month < ?)
							OR (ac.year = ? AND ac.month = ? AND ac.day < ?)
							OR (ac.year = ? AND ac.month = ? AND ac.day = ? AND ac.time_end < ?))
							AND ad.cancelled_by_pat != 1 AND ad.cancelled_by_doc != 1)");
	
	$stmt->bind_param("sssssssssss",$_GET['cid'],$today_year, $today_year, $today_month, $today_year, $today_month, $today_day, $today_year, $today_month, $today_day, $today_hour);
	$stmt->execute();
	$query = $stmt->get_result();
	
	
	$awards_tab = $user_obj->getAwardsPatient_Tab();
	
	$stmt = $con->prepare("SELECT award_code FROM $awards_tab WHERE consult_id = ?");
			
	$stmt->bind_param("s",$_GET['cid']);
	$stmt->execute();
	$query_never_before = $stmt->get_result();
	
	if(mysqli_num_rows($query) > 0 && mysqli_num_rows($query_never_before) == 0){
		$ini_arr = mysqli_fetch_assoc($query);
		$doctor_to_award = $ini_arr['doctor_username'];
		$doctor_to_award_e = $crypt->EncryptU($doctor_to_award);
		$doctor_to_award_obj = new User($con, $doctor_to_award, $doctor_to_award_e);
		
		$active_award = 1;
	}
	else{
		$active_award = 0;
	}
}
else{
	header("Location: index.php");
}

if(isset($_POST['confirm_award'])){
	$i = 0;
	while(true){
		$i++;
		$_t_name = "award_sel_input_" . $i;
		
		if(!isset($_POST[$_t_name])){
			break;
		}
		
		if($_POST[$_t_name] == 1){
			if(isset($doctor_to_award_obj)){
				$doc_awards_tab = $doctor_to_award_obj->getAwards_tab();
			}
			else{
				header("Location: index.php");
			}
			//fetch prev value
			$stmt = $con->prepare("SELECT votes FROM $doc_awards_tab WHERE award_code = ?");
			
			$stmt->bind_param("i",$i);
			$stmt->execute();
			$query_before = $stmt->get_result();
			
			$new_votes = mysqli_fetch_assoc($query_before)['votes'] + 1;
			
			//update new value
			$stmt = $con->prepare("INSERT INTO $doc_awards_tab (award_code, votes) VALUES(?, ?) 
									ON DUPLICATE KEY UPDATE
									votes=?");
			
			$stmt->bind_param("iii",$i,$new_votes,$new_votes);
			$stmt->execute();
			
			//update on patient list that he voted already
			
			$stmt = $con->prepare("INSERT INTO $awards_tab (`award_code`, `consult_id`) VALUES (?,?)");
			
			$stmt->bind_param("is",$i,$_GET['cid']);
			$stmt->execute();
			
			$active_award = 2;
			
		}
	}
}

?>


<div class="main_column column" id="main_column">
	<div class="requests_holder">
	
		<?php 
			switch ($lang){
					 		
				case("en"):
		?>
					<div class="title_tabs">Doctor Awards</div>
    					<div class="main_settings award_div">
    					
    					<?php 
    						if($active_award == 1){
    					?>
    					<div class="title_connection">
    					Award manager   					
    					</div>
    					<input type="text" onkeyup="searchAwards(this.value)" name="awa_q" placeholder="Search an Award" autocomplete="off" class="universal_search_bar" id="search_text_input_awards" >
    					<div class="button_holder_calendar">
    						<img src="assets/images/icons/search-icon-pink.png">
    					</div>
    					
    					<div id="doc_award_select">
    					<p> Select an award from the list below to give to <b>Dr. <?php echo $txt_rep->entities($doctor_to_award_obj->getFirstAndLastNameFast());?>.</b></p>
    					</div>
    					
    					<button class="changer_butt" id="doc_changer_butt_left" style=" background-color: #fff; border: none; height: 220px;">
    						<span class="text-vertical-center">&#10092;</span>
    					</button>
    					
    					<button class="changer_butt" id="doc_changer_butt_right" style=" background-color: #fff; border: none; height: 220px;">
    				        <span class="text-vertical-center">&#10093;</span>
    					</button>
    					
    					<form action="award_handing.php?cid=<?php echo $_GET['cid'];?>"  method="POST">
    						<div class="center_content" style=" height: 220px; margin-bottom: 30px;">
    							<div class="style-2" id="doc_center_content">
    							</div>
    						</div>
    						<input type="submit" name="confirm_award" class="center_horizontally" id="accept_button" style=" width: 40%; margin:0;" value="Confirm">
    					</form>
    					<?php
    						}
    						elseif($active_award == 0){
    					?>
    					<p> You cannot produce an award for this appointment at the moment. This is because the appointment has not been held yet, it was canceled, or you already gave an award.</p>
    					<?php
    						}
    						elseif($active_award == 2){
    					?>
    					<p> Thank you for awarding <b>Dr. <?php echo $txt_rep->entities($doctor_to_award_obj->getFirstAndLastNameFast());?>.</b> Future patients will find your opinion helpful! You can now back to your <a href="index.php">Home</a>. </p>
    					<?php
    						}
    					?>
		<?php 
			        break;
				
				case("es"):
		?>
					<div class="title_tabs">Premios de Doctor</div>
    					<div class="main_settings award_div">
    					<?php 
    						if($active_award == 1){
    					?>
    					<div class="title_connection">
    					Buscador de premios   					
    					</div>
    					<input type="text" onkeyup="searchAwards(this.value)" name="awa_q" placeholder="Busca un premio" autocomplete="off" class="universal_search_bar" id="search_text_input_awards" >
    					<div class="button_holder_calendar">
    						<img src="assets/images/icons/search-icon-pink.png">
    					</div>
    					
    					<div id="doc_award_select">
    					
    					<p> Selecciona un premio de la lista de abajo para otorgar a el <b>Dr. <?php echo $txt_rep->entities($doctor_to_award_obj->getFirstAndLastNameFast());?>.</b></p>
    					
    					</div>
    					<button class="changer_butt" id="doc_changer_butt_left" style=" background-color: #fff; border: none; height: 220px;">
    						<span class="text-vertical-center">&#10092;</span>
    					</button>
    					
    					<button class="changer_butt" id="doc_changer_butt_right" style=" background-color: #fff; border: none; height: 220px;">
    				        <span class="text-vertical-center">&#10093;</span>
    					</button>
    					
    					<form action="award_handing.php?cid=<?php echo $_GET['cid'];?>"  method="POST">
    						<div class="center_content" style=" height: 220px; margin-bottom: 30px;">
    							<div class="style-2" id="doc_center_content">
    							</div>
    						</div>
    						<input type="submit" name="confirm_award" class="center_horizontally" id="accept_button" style=" width: 40%; margin:0;" value="Confirmar">
    					</form>
    					<?php
    						}
    						elseif($active_award == 0){
    					?>
    					<p> En el momento no puedes otorgar un premio por esta consulta. Esto se debe a que esta consulta aún no ha ocurrido, fue cancelada, o ya se otorgó un premio por ella.</p>
    					<?php
    						}
    						elseif($active_award == 2){
    					?>
    					<p> Gracias por otorgar este premio al <b>Dr. <?php echo $txt_rep->entities($doctor_to_award_obj->getFirstAndLastNameFast());?>.</b> ¡Futuros pacientes van a agradecer tu opinión! Ahora ya puedes voler al <a href="index.php">Inicio</a>. </p>
    					<?php
    						}
    					?>
		<?php 
					break;
					
			}
		?>
	

		</div>
	</div>
</div>

<script>
$(document).ready(function(){
	loadConnections(0);

	//buttons
	$('#doc_changer_butt_right').click(function() {
		
		$('#doc_center_content').animate({
			scrollLeft: "+=" + eval("2*" + $(".internal_element_small").outerWidth()) + "px"
		}, "slow");

	});

	$('#doc_changer_butt_left').click(function() {
		
		$('#doc_center_content').animate({
			scrollLeft: "-=" + eval("2*" + $(".internal_element_small").outerWidth()) + "px"
		}, "slow");

	});
	
	$('#doc_center_content').bind('scroll', function(){
	
		var latest_loaded_element_id = $(this).find('#latest_loaded_element_id_').val();
		var ending = $(this).find('#ending_').val();
	
		if($(this).scrollLeft() + $(this).innerWidth() >=  $(this)[0].scrollWidth && ending == 0){
			//scrollHeight:tamaño todo el contenido scrollWidth()
			//scrollTop: numero de pixeles que se han scrolleado scrollLeft()
			//innerHeight: tamaño del div  scrolltop + innerheight = scrollheight innerWidth()
			loadConnections(latest_loaded_element_id);
		}
		return false;
	});
});

function searchAwards(terms){
	if($('#search_text_input_awards').val() != ""){
		$.ajax({
			url: "includes/handlers/ajax_award_load.php",
			type: "POST",
			data: "latest_element_id=0" + "&search_terms=" + terms,
			cache:  false,

			success: function(response){
				$('#doc_center_content').find('#latest_loaded_element_id_').remove();
				$('#doc_center_content').find('#ending_').remove();
	
				$('#doc_center_content').hide().html(response).fadeIn();

			}
		});
	}
	else{
		$('#doc_center_content').scrollLeft(0);
		loadConnections(0);
	}
}

function loadConnections(latest_loaded_element_id){

	if($('#search_text_input_awards').val() != ""){
		var terms =  $('#search_text_input_awards').val();
		$.ajax({
			url: "includes/handlers/ajax_award_load.php",
			type: "POST",
			data: "latest_element_id=" + latest_loaded_element_id + "&search_terms=" + terms,
			cache:  false,

			success: function(response){
				$('#doc_center_content').find('#latest_loaded_element_id_').remove();
				$('#doc_center_content').find('#ending_').remove();
	
				if(latest_loaded_element_id == 0){
					$('#doc_center_content').hide().html(response).fadeIn();
				}
				else{
					$('#doc_center_content').append(response).fadeIn();
				}
			}
		});
	}
	else{
		var terms =  $('#search_text_input_connections_doc').val();
		$.ajax({
			url: "includes/handlers/ajax_award_load.php",
			type: "POST",
			data: "latest_element_id=" + latest_loaded_element_id,
			cache:  false,

			success: function(response){
				$('#doc_center_content').find('#latest_loaded_element_id_').remove();
				$('#doc_center_content').find('#ending_').remove();
	
				if(latest_loaded_element_id == 0){
					$('#doc_center_content').hide().html(response).fadeIn();
				}
				else{
					$('#doc_center_content').append(response).fadeIn();
				}
			}
		});
	}
}
</script>

