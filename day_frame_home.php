<?php 

require 'config/config.php';
include('includes/classes/User.php');
include("includes/classes/TxtReplace.php");
include("includes/classes/Appointments_Calendar_Home.php");
include("includes/classes/Appointments_Master.php");

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
	<link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />

</head>
<body>

	<?php 

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

			//LANGUAGE RETRIEVAL
			
			$lang = $_SESSION['lang'];
			
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: register.php");
			$stmt->close();
		}

		$str = "";

		$current_day = date("d");
		$current_month = date("m"); //Gets current month 
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
				$str = "<div id='selected_day' ><p><b> " .$value['d'] ." ". $value['monthName'] ." ". $value['y'] . "</b></p></div>";
				echo $str;
			}
			else{
				header("Location: 404.php");
			}
		}
		
		if(isset($_GET['vt'])){
			if($_GET['vt'] == 1 || $_GET['vt'] == 2){
				$userLoggedIn_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
// 				if(!$userLoggedIn_obj->isDoctor()){
// 					header("Location: 404.php");
// 					$view_type = "";
// 				}
				$view_type = $_GET['vt'];
			}
			else{
				header("Location: 404.php");
				$view_type = "";
			}
		}
		else{
			header("Location: 404.php");
		}
	?>
	<?php 
		switch ($lang){
				 		
			case("en"):
	?>
				<div class='selected_day_content' ><p><b> Appointments </b></p></div>
	<?php 
		        break;
			
			case("es"):
	?>
				<div class='selected_day_content' ><p><b> Citas </b></p></div>
	<?php 
				break;
		}
	?>
	
	<div class="day_iframe style-2" >
		<?php
			$appointments_calendar_home = new Appointments_Calendar_Home($con, $userLoggedIn, $userLoggedIn_e, $selected_year, $selected_month);
			echo $appointments_calendar_home->getDay_Home($selected_day,$view_type);
		?>
	</div>

	<script>
		function consultAppointment(aid,view_type){
			var ajaxreq = $.ajax({
				url: "includes/handlers/ajax_consult_appointment_window.php",
				type: "POST",
				data: "aid=" + aid + "&vt=" + view_type,
				cache: false,

				success: function(response){
					$(".dropdown_confirm_window").html(response);
					$(".dropdown_confirm_window").css({"padding": "0px", "height" : "200px" , "border" : "none"});
				}
			});
		}

		function cancelConsultAppointment(){
			$(".dropdown_confirm_window").html("");
			$(".dropdown_confirm_window").css({"padding" : "0px", "height" : "0px" , "border" : "none"});
		}

		function jumpPatAppoViewer(aid){
			top.window.location.href = 'patient_appointment_viewer.php?cid=' + aid;
		}

		function jumpPatAward(aid){
			top.window.location.href = 'award_handing.php?cid=' + aid;
		}

		function jumpAppoDashboard(aid){
			top.window.location.href = 'doctor_appointment_viewer.php?cid=' + aid;
		}

		function jumpToProfile(profile){
			top.window.location.href = profile;
		}

		function viewExtAppoDetails(aid){
			top.window.location.href = 'ext_pat_appo_viewer.php?cid=' + aid;
		}
		
	</script>


</body>
</html>