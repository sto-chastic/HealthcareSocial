<?php 		
require 'config/config.php';
include('includes/classes/User.php');
include('includes/classes/Post.php');
include("includes/classes/Message.php");
include("includes/classes/Notification.php");
include("includes/classes/TimeStamp.php");
include("includes/classes/TxtReplace.php");
include("includes/classes/Calendar.php");
include("includes/classes/Appointments_Calendar.php");
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>


	<!-- Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="assets/js/bootstrap.js"></script>
	<script src="assets/js/bootbox.min.js"></script>
	<script src="assets/js/confidr.js"></script>
	<script src="assets/js/jquery.jcrop.js"></script>
	<script src="assets/js/jcrop_bits.js"></script>

	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<link rel="stylesheet" type="text/css" href="assets/css/calendar.css">
	<link rel="stylesheet" type="text/css" href="assets/css/appointment_details.css">
	<link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />

</head>
<body>

	<?php 
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
				header("Location: register.php");
				$stmt->close();
			}

			$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
			$txt_rep = new TxtReplace();
			$time_stamp = new TimeStamp();
			$lang = $_SESSION['lang'];
			$not_logged = FALSE;
		}
		else{
			
			if(isset($_SESSION['lang'])){
				$lang = $_SESSION['lang'];
			}
			else{
				$lang="es";
				$_SESSION['lang'] = "es";
			}
			
			$not_logged = TRUE;
			$txt_rep = new TxtReplace();
			$time_stamp = new TimeStamp();
			$lang = $_SESSION['lang'];
		}
		


		$str = "";

		$str = "";
		
		$current_day = date("d");
		$current_month = date("m"); //Gets current month Year:2017, Month:May, Days:7-13
		$current_year = date("Y");
		switch ($lang){
			
			case("en"):
				$months_row_lang = 'months_eng';
				$days_week_row_lang = 'days_short_eng';
				$days_week_long_lang = 'days_eng';
				break;
				
			case("es"):
				$months_row_lang = 'months_es';
				$days_week_row_lang = 'days_short_es';
				$days_week_long_lang = 'days_es';
				break;
		}

		//Get id of post
		if(isset($_GET['d']) && isset($_GET['m']) && isset($_GET['y'])){

			$stmt = $con->prepare("SELECT * FROM calendar_table WHERE d = ? AND m = ? AND y = ?");
			$stmt->bind_param("iii", $temp_d, $temp_m, $temp_y);
			$temp_d = $_GET['d'];
			$temp_m = $_GET['m'];
			$temp_y = $_GET['y'];
			$stmt->execute();

			$verification_query = $stmt->get_result();
			$stmt->close();

			if(mysqli_num_rows($verification_query) == 1){
				$value = mysqli_fetch_array($verification_query);

				$selected_day = $value['d'];
				$selected_month = $value['m'];
				$selected_year = $value['y'];
				switch($lang){
				    case("en"):
				        $str = "<div id='selected_day' style='text-align:right;'><b>". $value['monthName'] . " " . $value['d'] .", " . $value['y']. "</b> </div>";
				        break;
				    case("es"):
				    default:
				        $month_es = "";
				        switch($value['monthName']){
				            case("January"): $month_es = "enero"; break;
				            case("February"): $month_es = "febrero"; break;
				            case("March"): $month_es = "marzo"; break;
				            case("April"): $month_es = "abril"; break;
				            case("May"): $month_es = "mayo"; break;
				            case("June"): $month_es = "junio"; break;
				            case("July"): $month_es = "julio"; break;
				            case("August"): $month_es = "agosto"; break;
				            case("September"): $month_es = "septiembre"; break;
				            case("October"): $month_es = "octubre"; break;
				            case("November"): $month_es = "noviembre"; break;
				            case("December"): $month_es = "diciembre"; break;
				        }
				        $str = "<div id='selected_day' style='text-align:right;'><b>". $value['d']." / ". $month_es." / " . $value['y']. "</b> </div>";
				        break;
				}
				
				
				echo $str;
			}
			else{
				//echo "1";
				header("Location: 404.php");
			}

			if(isset($_GET['po'])){

				$stmt = $con->prepare("SELECT email FROM users WHERE username=?");
				$po_e = pack("H*", $txt_rep->entities($_GET['po'])); // po_e is encrypted profile owner
				$po = $crypt->Decrypt($po_e);
				$stmt->bind_param("s", $po_e);
				$stmt->execute();
				$verification_query = $stmt->get_result();

				if(mysqli_num_rows($verification_query) == 1){
					$profile_owner = $po;
                    $profile_owner_e = $po_e;
					$appointments_calendar = new Appointments_Calendar($con,$profile_owner, $profile_owner_e,$selected_year,$selected_month);
					$profile_owner_obj = new User($con, $profile_owner, $profile_owner_e);
					$stmt->close();
				}
				else{
					echo "2";
					//header("Location: 404.php");
				}
			}
			
			if(isset($_GET['pm'])){
// 				$stmt = $con->prepare("SELECT * FROM payment_methods WHERE codename=?");

// 				$stmt->bind_param("s", $_GET['pm']);
// 				$stmt->execute();
// 				$verification_query = $stmt->get_result();

// 				if(mysqli_num_rows($verification_query) == 1){
// 					$payment_method = $_GET['pm'];
// 				}

				if($_GET['pm'] == 'part'){
					$payment_method = 'part';
					$selected_insurance = "";
				}
				else{
					$insu_tab = "insurance_" . $user_obj->getCountry();
					$stmt = $con->prepare("SELECT id FROM $insu_tab WHERE id=?");
					
					$stmt->bind_param("s", $_GET['pm']);
					$stmt->execute();
					$verification_query = $stmt->get_result();
					if(mysqli_num_rows($verification_query) == 1){
						$payment_method = 'insu';
						$selected_insurance = $_GET['pm'];
					}
					else{
						echo "3";
						header("Location: 404.php");
					}
				}
				
			}
			else{
				echo "3";
				header("Location: 404.php");
			}


			if(isset($_GET['at'])){
				//echo "appo type " . $_GET['at'] . "<-----";
				$app_dur_tab = $profile_owner_obj->getAppoDurationTable();

				$at = $_GET['at'];
				$stmt = $con->prepare("SELECT id,appo_type,duration FROM $app_dur_tab WHERE id=?");

				$stmt->bind_param("s", $at);
				$stmt->execute();
				$verification_query = $stmt->get_result();

				if(mysqli_num_rows($verification_query) == 1){
					$_id_arr = mysqli_fetch_assoc($verification_query);
					$appointment_type = $_id_arr['appo_type'];
					$appo_type_id = $_id_arr['id'];
					$appointment_duration = $_id_arr['duration'];

				}
				else{
					echo "6";
					header("Location: 404.php");
				}

				$stmt->close();
			}
			else{
				echo "7";
				header("Location: 404.php");
			}
			
			$reschedule = 0;
			
			if(isset($_GET['aid'])){
				$_aid = $_GET['aid'];
				//check id belongs to this doctor
				$_det_doc = $profile_owner_obj->getAppointmentsDetails_Doctor();
				$_doc_usern = $profile_owner_obj->getUsername();
				
				$stmt = $con->prepare("SELECT consult_id FROM $_det_doc WHERE consult_id=? AND doctor_username=? AND patient_username =?");
				$stmt->bind_param("sss", $_aid ,$_doc_usern,$userLoggedIn);
				$stmt->execute();
				$verification_query_aid_doc = $stmt->get_result();
				
				//check id belongs to this patient
				$_det_pat = $user_obj->getAppointmentsDetails_Patient();
				
				$stmt = $con->prepare("SELECT consult_id FROM $_det_pat WHERE consult_id=? AND doctor_username=? AND patient_username =?");
				$stmt->bind_param("sss", $_aid,$_doc_usern,$userLoggedIn);
				$stmt->execute();
				$verification_query_aid_pat = $stmt->get_result();
				
				if(mysqli_num_rows($verification_query_aid_doc) == 1 && mysqli_num_rows($verification_query_aid_pat) == 1){
					$app_id = $_aid;
					$reschedule = 1;
					?>
					<script>
						//alert("this worked");
						function acceptRescheduleSelection(year,month,day,sel_time_st,sel_time_end,profile_owner,appo_type,payment_method,appo_type_id,old_appointment_id = '<?php echo $app_id;?>'){
							
							var ajaxreq = $.ajax({
								url: "includes/handlers/ajax_reschedule_time_selection.php",
								type: "POST",
								data: "profile_owner=" + profile_owner + "&day=" + day + "&month=" + month + "&year=" + year + "&ap_st=" + sel_time_st + "&ap_end=" + sel_time_end + "&payment_method=" + payment_method + "&ap_type=" + appo_type + "&ap_id=" + appo_type_id + "&old_aid=" + old_appointment_id,
								cache: false,
								
								success: function(response){
									//$(".dropdown_confirm_window").html(response);
									top.window.location.href = 'patient_appointment_viewer.php?cid=' + response;
								},
								error: function(jqXHR, exception) {
									if (jqXHR.status === 409) {
										$(".dropdown_confirm_window").html('<?php echo "Failed to add appointment, this slot was already taken by someone else. Try refreshing the page and try again." ?>');
									}
									else if (jqXHR.status === 412) {
										$(".dropdown_confirm_window").html('<?php echo "Failed to add appointment, this slot is already filled in your calendar, select a different time and try again." ?>');
									}
									else if (jqXHR.status === 400) {
										//$(".dropdown_confirm_window").html('<?php echo "Failed to add appointment; bad request, refresh the page and try again." ?>');
										$(".dropdown_confirm_window").html(response);
									}
								}
								
							});
						}
					</script>
					<?php

				}
				else{
					//echo "fuuuuuuu";
					echo "8";
					header("Location: 404.php");
				}
			}

		}
		else{
			echo "9";
			header("Location: 404.php");
		}
	?>

	<div class="dropdown_confirm_window" style="height:0px; border:none; display: inline-block; text-align: center;"></div>
	<div class="dropdown_grey"></div>
	<?php 
		switch ($lang){
				 		
			case("en"):
	?>
				<div class='selected_day_content' id="free_slots" ><p><b> Available Appointments </b></p></div>
	<?php 
		        break;
			
			case("es"):
	?>
				<div class='selected_day_content' id="free_slots" ><p><b> Citas Disponibles </b></p></div>
	<?php 
				break;
		}
	?>
	<div class="day_iframe" id="style-2" >
<!-- 		<div id='day_container_box_profile'> -->
			<b>
			<?php
				if($payment_method == 'insu'){
					$office_arr = $profile_owner_obj->getAvailableOfficesByInsurance($selected_insurance);
					$day_content = $appointments_calendar->getDay($selected_day,$payment_method,$appointment_duration,$appointment_type,$office_arr);
					
					switch ($lang){
						
						case("en"):
							echo ($day_content == '')? "No available appointments this day" : $day_content;
							break;
							
						case("es"):
							echo ($day_content == '')? "No hay citas disponibles para este día" : $day_content;
							break;
					}
					 
				}
				elseif($payment_method == 'part'){
					$day_content = $appointments_calendar->getDay($selected_day,$payment_method,$appointment_duration,$appointment_type);
					
					switch ($lang){
						
						case("en"):
							echo ($day_content == '')? "No available appointments this day" : $day_content;
							break;
							
						case("es"):
							echo ($day_content == '')? "No hay citas disponibles para este día" : $day_content;
							break;
					}
					
					
				}
			?>
<!-- 		</div> -->
			</b>
	</div>
	<?php
	
	if($reschedule && !$not_logged){
			?>
			<script>
				function selectTime4Booking(year,month,day,ap_start,ap_end,profileUsername,payment_method){
					var appointment_duration = '<?php echo $appointment_duration ?>';
					var appointment_type = '<?php echo $appointment_type ?>';
					var aid = '<?php echo $app_id ?>';
					var appo_type_id = '<?php echo $appo_type_id ?>';
					//alert("used");
					var ajaxreq = $.ajax({
						url: "includes/handlers/ajax_confirm_time_selection_window_reschedule.php",
						type: "POST",
						data: "profile_owner=" + profileUsername + "&day=" + day + "&month=" + month + "&year=" + year + "&payment_method=" + payment_method + "&ap_st=" + ap_start + "&ap_end=" + ap_end + "&ap_type=" + appointment_type + "&ap_id=" + appo_type_id + "&aid=" + aid,
						cache: false,
						
						success: function(response){
							$(".dropdown_confirm_window").html(response);
							$(".dropdown_confirm_window").css({"padding": "0px", "height" : "auto" , "border" : "1px solid #DADADA"});
							$(".dropdown_grey").css({"background-color": "#aadddd4d","display":"block"});
						}
					});
				}
			</script>
			<?php
		}
		elseif(!$reschedule && !$not_logged){
			?>
			<script>
		
				function selectTime4Booking(year,month,day,ap_start,ap_end,profileUsername,payment_method){
					var appointment_duration = '<?php echo $appointment_duration ?>';
					var appointment_type = '<?php echo $appointment_type ?>';
					var appo_type_id = '<?php echo $appo_type_id ?>';
					
					var ajaxreq = $.ajax({
						url: "includes/handlers/ajax_confirm_time_selection_window.php",
						type: "POST",
						data: "profile_owner=" + profileUsername + "&day=" + day + "&month=" + month + "&year=" + year + "&payment_method=" + payment_method + "&ap_st=" + ap_start + "&ap_end=" + ap_end + "&ap_type=" + appointment_type + "&ap_id=" + appo_type_id, 
						cache: false,
		
						success: function(response){
							$(".dropdown_confirm_window").html(response);
							$(".dropdown_confirm_window").css({"padding": "0px", "height" : "auto" , "border" : "1px solid #DADADA"});
							$(".dropdown_grey").css({"background-color": "#aadddd4d","display":"block"});
						}
					});
				}
			</script>
			<?php
		}
		else{
		?>
			<script>
		
				function selectTime4Booking(year,month,day,ap_start,ap_end,profileUsername,payment_method){
		            var $body = $(window.frameElement).parents('body'),
	               		dlg = $body.find('#confirm_appo_sele');
	            		dlg.modal('show');
				}
			</script>
		
		<?php 
		}
		
		if(!$not_logged){
		?>
	<script>
		
		function cancelBookingSelection(){
			$(".dropdown_confirm_window").html("");
			$(".dropdown_confirm_window").css({"padding" : "0px", "height" : "0px" , "border" : "none"});
			$(".dropdown_grey").css({"display":"none"});
			
		}

		function acceptBookingSelection(year,month,day,sel_time_st,sel_time_end,profile_owner,appo_type,payment_method,appo_type_id){
			$(".dropdown_confirm_window").html('<center><img id="loading" src="assets/images/icons/logowhite.gif"></center>');
			var ajaxreq = $.ajax({ 
				url: "includes/handlers/ajax_confirm_time_selection.php",
				type: "POST",
				data: "profile_owner=" + profile_owner + "&day=" + day + "&month=" + month + "&year=" + year + "&ap_st=" + sel_time_st + "&ap_end=" + sel_time_end + "&payment_method=" + payment_method + "&ap_type=" + appo_type + "&ap_id=" + appo_type_id,
				cache: false,

				success: function(response){
					//alert(response);
					//var res = response.split("appo");
					//alert(res[1]);
					//$(".dropdown_confirm_window").html(response);
					top.window.location.href = 'patient_appointment_viewer.php?cid=' + response;
				},
				error: function(jqXHR, exception) {
					if (jqXHR.status === 409) {
						$(".dropdown_confirm_window").html('<?php echo "Failed to add appointment, this slot was already taken by someone else. Try refreshing the page and try again." ?>');
					}
					else if (jqXHR.status === 412) {
						$(".dropdown_confirm_window").html('<?php echo "Failed to add appointment, this slot is already filled in your calendar, select a different time and try again." ?>');
					}
					else if (jqXHR.status === 400) {
						$(".dropdown_confirm_window").html('<?php echo "Failed to add appointment; bad request, refresh the page and try again." ?>');
					}
				}

			});
		}
	</script>
		
	<?php }?>

</body>
</html>