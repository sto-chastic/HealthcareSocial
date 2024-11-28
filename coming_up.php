<?php
	require 'config/config.php';
	include('includes/classes/User.php');
	include("includes/classes/Notification.php");
	include("includes/classes/TimeStamp.php");
	include("includes/classes/TxtReplace.php");
	include("includes/classes/Appointments_Calendar_Home.php");
	include("includes/classes/Appointments_Master.php");
?>

<!DOCTYPE html>
<html>
<head>
	<title>ConfiDr / Coming up</title>


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
<body style= "background-color:transparent;">

	<style type="text/css">
		* {
			font-size: 12px;
		    overflow: hidden;
		}
	</style>

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
				$current_day = date("d");
				$current_month = date("m");
				$current_year = date("Y");
				
				$today = date("Y-m-d");
				$tomorrow = date('Y-m-d', strtotime($today. ' + 1 days'));
				
				$tomorrow_day = date('d', strtotime($today. ' + 1 days'));
				$tomorrow_month = date('m', strtotime($today. ' + 1 days'));
				$tomorrow_year = date('Y', strtotime($today. ' + 1 days'));
				
				$lang = $_SESSION['lang'];
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
								
				$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
				$txt_rep = new TxtReplace();
				$time_stamp = new TimeStamp();
				
				$view_type = $user_obj->getUserType();
				
			}
			else{
				echo "err1";
				$userLoggedIn = "";
				session_start();
				session_destroy();
				header("Location: register.php");
			}

		}
		else{
			echo "err2";
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: register.php");
		}

		$str = "";

	?>
	
	<div id="coming_up_title">
		<h3>
		<?php 
			switch ($lang){
					 		
				case("en"):
		?>
					Coming Up
		<?php 
			        break;
				
				case("es"):
		?>
					Próximamente
		<?php 
					break;
			}
		?>
			
		</h3>
	</div>
	
	<div class="coming_up_container" id="coming_up_today">
		<h4>
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						TODAY
			<?php 
				        break;
					
					case("es"):
			?>
						HOY
			<?php 
						break;
				}
			?>
		</h4>
		<div class="coming_up_content  style-2" id="today_content">
		<?php
		try{
			$appointments_calendar_home_TODAY = new Appointments_Calendar_Home($con, $userLoggedIn, $userLoggedIn_e, $current_year, $current_month);
			echo $appointments_calendar_home_TODAY->getDay_Home_Frame($current_day,$view_type);
		}
		catch ( Exception $e ){
			switch ($lang){
				
				case("en"):
					echo "Start ConfiDr. Premium to use this function!
							Start optimizing your appointments, it's <u>free</u> for limited time!
							<a href='premium2.php' target='_top'>
								<div class='suscribe_ad_button'>
									<p class='suscribe_ad_font'>Start now!</p>
								</div>
							</a>";
					break;
					
				case("es"):
					echo "¡Entra a ConfiDr. Premium para usar esta función!
						Empieza a optimizar tus consultas, ¡es <u>gratis</u> por tiempo limitado!
						<a href='premium2.php' target='_top'>
							<div class='suscribe_ad_button'>
								<p class='suscribe_ad_font'>¡Empieza ahora!</p>
							</div>
						</a>";
					break;
			}

		}
		?>
		</div>
	</div>
	
	<div class="coming_up_container" id="coming_up_tomorrow" >
		<h4>
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						TOMORROW
			<?php 
				        break;
					
					case("es"):
			?>
						MAÑANA
			<?php 
						break;
				}
			?>
			
		</h4>
		<div class="coming_up_content style-2" id="tomorrow_content" style="border: 1px solid #add;">
		<?php
		try{
			$appointments_calendar_home_TOMORROW = new Appointments_Calendar_Home($con, $userLoggedIn, $userLoggedIn_e, $tomorrow_year, $tomorrow_month);
			echo $appointments_calendar_home_TOMORROW->getDay_Home_Frame($tomorrow_day,$view_type,FALSE);
		}
		catch ( Exception $e ){
		}
		?>
		</div>
	</div>
	<input type="hidden" name="alerted_5_mins" value="-1">
	<input type="hidden" name="alerted_now" value="-1">

<script>

var SECOND = 1000;

var time = document.getElementById('time');

function detectActiveAppointment(instantTime,isDoctor){
	var intervals_text = $('input[name=intervals]').val();
	var intervals_array = intervals_text.split(",");
	for(var i=0;i<intervals_array.length - 1;i++){
		
		var _temp_array_subdiv = intervals_array[i].split("-");
		var _temp_time_start = _temp_array_subdiv[0];
		var _temp_time_end = _temp_array_subdiv[1];
		var _temp_div_id = _temp_array_subdiv[2];
		var _aid_id = _temp_array_subdiv[3];

		var _temp_time_start_arr = _temp_time_start.split(":");
		var _temp_time_start_hours = parseInt(_temp_time_start_arr[0]);
		var _temp_time_start_minutes = parseInt(_temp_time_start_arr[1]);

		var _temp_time_end_arr = _temp_time_end.split(":");
		var _temp_time_end_hours = parseInt(_temp_time_end_arr[0]);
		var _temp_time_end_minutes = parseInt(_temp_time_end_arr[1]);

		var current_minutes = parseInt(instantTime.getMinutes());
		var current_hours = parseInt(instantTime.getHours());
		
		//Alert 5 minutes before appointment
		if((current_hours == _temp_time_start_hours - 1 && current_minutes == 55) ||
		 (current_hours == _temp_time_start_hours && current_minutes == _temp_time_start_minutes - 5)){
			if($('input[name=alerted_5_mins]').val() != _temp_div_id){
				createNotification('5 Minute Reminder','assets/images/icons/logowhite.gif','You have an appointment in 5 minutes.',isDoctor,_aid_id);
				$('input[name=alerted_5_mins]').val(_temp_div_id);
			}	
		}

		//Alert at time of the appointment
		if((current_hours == _temp_time_start_hours && current_minutes == _temp_time_start_minutes)){
			if($('input[name=alerted_now]').val() != _temp_div_id){
				createNotification('Appointment Reminder','assets/images/icons/logowhite.gif','You have an appointment starting now.',isDoctor,_aid_id);
				$('input[name=alerted_now]').val(_temp_div_id);
			}
		}
		
		if((current_hours >= _temp_time_start_hours &&
			current_minutes >= _temp_time_start_minutes &&
			current_hours == _temp_time_end_hours &&
			current_minutes < _temp_time_end_minutes) || 

			(current_hours >= _temp_time_start_hours &&
			current_minutes >= _temp_time_start_minutes &&
			current_hours < _temp_time_end_hours)){

			$("#list_num_" + _temp_div_id).css({"background-color":"rgba(203, 110, 141, 0.5)"});
			
		}
		else{
			if($("#list_num_" + _temp_div_id).css("background-color") != "#FFF"){
				$("#list_num_" + _temp_div_id).css({"background-color":"#FFF"});
			}

		}
	}
	
}

function notificationsRequestPermision() {
  // Browser supports notifications
  if (!("Notification" in window)) {
  }

  // Notification permissions have already been granted
  else if (Notification.permission === "granted") {
    //var notification = new Notification("Hi there!");
  }

  // Otherwise, we need to ask the user for permission
  else if (Notification.permission !== 'denied') {
    Notification.requestPermission(function (permission) {
      // If the user accepts, Notify it
      if (permission === "granted") {
        var notification = new Notification("Notifications activated.");
      }
    });
  }

}

function createNotification(body,icon,title,isDoctor,aid) {

  var options = {
      body: body,
      icon: icon
  }
  
  var n = new Notification(title,options);

  // Remove the notification from Notification Center when clicked and jump to link
  n.onclick = function () {
      this.close();
      if(isDoctor){
      	jumpAppoDashboard(aid);
      }
      else{
    	  	jumpToProfile(aid);
      }
  };
  
  setTimeout(n.close.bind(n), 5000);
}

function jumpPatAppoViewer(aid){
	top.window.location.href = 'patient_appointment_viewer.php?cid=' + aid;
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

function bindClockTick(callback) {
    function tick() {
        var now = Date.now();
		//var instantTime = new Date;
		var isDoctor = '<?php echo $user_obj->isDoctor(); ?>';
        callback(isDoctor);
        
        setTimeout(tick, SECOND - (now % SECOND)); //when the function shall execute, it is, every second in sync with the system
    }
    
    tick();
}


bindClockTick(function(isDoctor) {
	
	var instantTime = new Date;
    
    detectActiveAppointment(instantTime,isDoctor);
});

notificationsRequestPermision();



</script>
</body>
</html>