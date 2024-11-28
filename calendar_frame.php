<?php 
require 'config/config.php';
include('includes/classes/User.php');
include('includes/classes/Post.php');
include("includes/classes/Message.php");
include("includes/classes/Notification.php");
include("includes/classes/TimeStamp.php");
include("includes/classes/TxtReplace.php");
include("includes/classes/Calendar.php");
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
				$userLoggedIn_e = $temp_user_e; //Retrieves username
				$userLoggedIn = $temp_user;
				$user = mysqli_fetch_array($verification_query);
				$stmt->close();
			}
			else{
				$userLoggedIn = "";
				session_start();
				session_destroy();
				header("Location: register.php");
			}

			$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
			$txt_rep = new TxtReplace();
			$lang = $_SESSION['lang'];

		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: register.php");
		}


		$var_time = "";

		$calendar = new Calendar($con,$userLoggedIn, $userLoggedIn_e);
		$num_blocks = $calendar->getAvailableCalendarNumItems();
		$message = "";
		$current_day = date("d");
		$current_month = date("m"); 


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
		

		$print_day_week = [];
		$input_elements = [];
		
		
		if(isset($_SESSION['saved_cal'])){
			if($_SESSION['saved_cal'] == 1){
				$show_saved = 1;
			}
			else{
				$show_saved = 0;
			}
		}
		else{
			$show_saved = 0;
		}
		

		//Get id of post
		if(isset($_GET['w']) && isset($_GET['y'])){

			$stmt = $con->prepare("SELECT * FROM calendar_table WHERE w = ? AND y = ?");
			$stmt->bind_param("ii", $temp_w, $temp_y);
			$temp_w = $_GET['w'];
			$temp_y = $_GET['y'];
			$stmt->execute();

			$verification_query = $stmt->get_result();
			$stmt->close();

			if(mysqli_num_rows($verification_query) > 0){
			    $select_week_to_mod = $_GET['w'];
			    $select_year_to_mod = $_GET['y'];
			    
			    foreach ($verification_query as $key => $value) {
			        if($key == 0){
			            switch ($lang){
			                
			                case("en"):
			                    $str = "<div class='week_selected'><h1> Week Selected</h1><p>Year:"." "."<b>" . $value['y'] . "</b> Month:"." "."<b>" . $value['monthName'] . "</b> Days:"." "."<b>" . $value['d'] ."-";
			                    break;
			                    
			                case("es"):
			                    $str = "<div class='week_selected'><h1>Semana seleccionada</h1><p>Año:"." "."<b>" . $value['y'] . "</b> Mes:<b>" . $value['monthName'] . "</b> Días:"." "."<b>" . $value['d'] ."-";
			                    break;
			            }
			            
			        }
			        $print_day_week[$value['dw']] = $value['d'];
			        $last_day = $value['d'];
			    }
			    $str = $str . $value['d'] . "</b></p></div>";
			    echo $str;
			}
			else{
			    header("Location: calendar_frame.php");
			}
			
		}
		else{
		    $select_week_to_mod = 'default';
		    $select_year_to_mod = 'default';
		}


	?>

	<div id="week_container" style=" display: inline-block ;"> 
					<div id="select_office">
						<p><?php 
						switch($lang){
						    case("en"):
						        echo "Select an office to assign an availability schedule.";
						        break;
						    case("es"):
						        echo "Selecciona una oficina para asignar un horario de disponibilidad.";
						        break;
						}
						?></p>
				<div id="office_type">
						<div id= office_arrow>
    						<select id="office_selector">
    						<?php				
    							$stmt = $con->prepare("SELECT * FROM basic_info_doctors WHERE username=?");
    							$stmt->bind_param("s",$userLoggedIn);
    							$stmt->execute();
    							$query = $stmt->get_result();
    							$res = mysqli_fetch_array($query);
    							
    							echo "<option selected='selected' value='1'>" . $res['ad1nick'] . "</option>";
    							
    							if($res['ad2nick'] == ''){
    								if($res['ad3nick'] != ''){
    									echo "<option value='3'>" . $res['ad3nick'] . "</option>";
    								}
    							}
    							else{
    								echo "<option value='2'>" . $res['ad2nick'] . "</option>";
    								
    								if($res['ad3nick'] != ''){
    									echo "<option value='3'>" . $res['ad3nick'] . "</option>";
    								}
    							}
    						?>
    						</select>
    					</div>	
					
							<div id="selected_color" ></div>
				</div>
						<script>
							$("#office_selector").change(function(){
								var selected_office = $("#office_selector").val();
							    if(selected_office == 0){
									$("#selected_color").css({"background-color":"none"});
							    }
							    else if(selected_office == 1){
									$("#selected_color").css({"background-color":"#53a5a5"});
							    }
							    else if(selected_office == 2){
									$("#selected_color").css({"background-color":"#ff8548"});
							    }
							    else if(selected_office == 3){
									$("#selected_color").css({"background-color":"#a559a5"});
							    }
							});
						</script>
						<?php 
						switch($lang){
						    case("en"):
						        echo "
                                    <h2>Conventions</h2>
                                    <b>Insured patient</b><div class='independent_convention'></div>
                                    <b>Uninsured patient</b><div class='insurance_convention'></div>";
						        break;
						    case("es"):
						        echo "
                                    <h2>Convenciones</h2>
                                    <b>Pacientes con seguro/prepagada/EPS</b><div class='independent_convention'></div>
                                    <b>Pacientes particulares</b><div class='insurance_convention'></div>";
						        break;
						}
						?>
						
					</div>
		
		<form action="" method="POST">
			<div id = "calendar_outter_box">
				<div id = "calendar_header">
					<div class="calendar_element day_header" id="hour" ><?php 
					switch($lang){
					    case("en"): echo "Intervals";
					               break;
					    case("es"): echo "Intervalos";
					               break;
					}
					?></div>
					<?php
						if(empty($print_day_week)){
							for($i=1;$i<=8;$i++){
								$print_day_week[] = "";
							}
						}

						$pr_str = "";
						$print_query = mysqli_query($con, "SELECT $days_week_long_lang FROM days_week");
						foreach ($print_query as $key => $value) {
							$week_key = $key + 1;
							$print_day_week[$week_key] = (array_key_exists($week_key,$print_day_week)) ? $print_day_week[$week_key] : "&#8226" ;
							$pr_str .= "<div class='calendar_element day_header'>" . $value[$days_week_long_lang] . "<br>" . $print_day_week[$week_key] . "</div>";
						}
						echo $pr_str;
					?>
					
				</div>
				<div class = "calendar_weekdays" id="style-2">
					<div class="week_day" id="hour">
						<?php
							$block_html = "";
							$times_arr = $calendar->getAvailableCalendarTimes($select_year_to_mod,$select_week_to_mod);

							foreach ($times_arr as $i => $time) {
								$var_time = new DateTime($time['hour']);
								$var_time_end = new DateTime($time['hour_end']);

								$var_time_f = $var_time->format("G:i");
								$var_time_end_f = $var_time_end->format("G:i");
								
								$block_html = "<div class='block' id='time_" . $i ."'>" . $var_time_f . "-" . $var_time_end_f . "</div>";
								echo $block_html;
							}
						?>
					</div>
					<div class="week_day" id="sunday">
						<?php
							if($print_day_week[1] == "&#8226"){
								switch ($lang){
									
									case("en"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>This day is in a different year than selected.</p>';
										break;
										
									case("es"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>Este día está en un año diferente al seleccionado.</p>';
										break;
								}
							}else{
								echo '<div class="insurance_patients">';

								$resArray = [];

								$element = 'sunday_insu';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
						<?php 
							if($print_day_week[1] == "&#8226")
								echo '<div class="independent_patients" style="width:0;">';
							else{
								echo '<div class="independent_patients">';

								$resArray = [];

								$element = 'sunday_part';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
					</div>
					<div class="week_day" id="monday">
						<?php
							if($print_day_week[2] == "&#8226"){
								switch ($lang){
									
									case("en"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>This day is in a different year than selected.</p>';
										break;
										
									case("es"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>Este día está en un año diferente al seleccionado.</p>';
										break;
								}
							}else{
								echo '<div class="insurance_patients">';
								$resArray = [];
								$element = 'monday_insu';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
						<?php 
							if($print_day_week[2] == "&#8226")
								echo '<div class="independent_patients" style="width:0;">';
							else{
								echo '<div class="independent_patients">';
								$resArray = [];
								$element = 'monday_part';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
					</div>
					<div class="week_day" id="tuesday">
						<?php
							if($print_day_week[3] == "&#8226"){
								switch ($lang){
									
									case("en"):
										echo '<div class="insurance_patients" style="width:120px;">
										<p>This day is in a different year than selected.</p>';
										break;
										
									case("es"):
										echo '<div class="insurance_patients" style="width:120px;">
										<p>Este día está en un año diferente al seleccionado.</p>';
										break;
								}
							}
							else{
								echo '<div class="insurance_patients">';
								$resArray = [];
								$element = 'tuesday_insu';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
						<?php 
							if($print_day_week[3] == "&#8226")
								echo '<div class="independent_patients" style="width:0;">';
							else{
								echo '<div class="independent_patients">';
								$resArray = [];
								$element = 'tuesday_part';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
					</div>
					<div class="week_day" id="wednesday">
						<?php
							if($print_day_week[4] == "&#8226"){
								switch ($lang){
									
									case("en"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>This day is in a different year than selected.</p>';
										break;
										
									case("es"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>Este día está en un año diferente al seleccionado.</p>';
										break;
								}
							}
							else{
								echo '<div class="insurance_patients">';
								$resArray = [];
								$element = 'wednesday_insu';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
						<?php 
							if($print_day_week[4] == "&#8226")
								echo '<div class="independent_patients" style="width:0;">';
							else{
								echo '<div class="independent_patients">';
								$resArray = [];
								$element = 'wednesday_part';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
					</div>
					<div class="week_day" id="thursday">
						<?php
							if($print_day_week[5] == "&#8226"){
								switch ($lang){
									
									case("en"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>This day is in a different year than selected.</p>';
										break;
										
									case("es"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>Este día está en un año diferente al seleccionado.</p>';
										break;
								}
							}else{
								echo '<div class="insurance_patients">';
								$resArray = [];
								$element = 'thursday_insu';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
						<?php 
							if($print_day_week[5] == "&#8226")
								echo '<div class="independent_patients" style="width:0;">';
							else{
								echo '<div class="independent_patients">';
								$resArray = [];
								$element = 'thursday_part';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
					</div>
					<div class="week_day" id="friday">
						<?php
							if($print_day_week[6] == "&#8226"){
								switch ($lang){
									
									case("en"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>This day is in a different year than selected.</p>';
										break;
										
									case("es"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>Este día está en un año diferente al seleccionado.</p>';
										break;
								}
							}else{
								echo '<div class="insurance_patients">';
								$resArray = [];
								$element = 'friday_insu';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
						<?php 
							if($print_day_week[6] == "&#8226")
								echo '<div class="independent_patients" style="width:0;">';
							else{
								echo '<div class="independent_patients">';
								$resArray = [];
								$element = 'friday_part';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
					</div>
					<div class="week_day" id="saturday">
						<?php
							if($print_day_week[7] == "&#8226"){
								switch ($lang){
									
									case("en"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>This day is in a different year than selected.</p>';
										break;
										
									case("es"):
										echo '<div class="insurance_patients" style="width:120px;">
											<p>Este día está en un año diferente al seleccionado.</p>';
										break;
								}
							}else{
								echo '<div class="insurance_patients">';
								$resArray = [];
								$element = 'saturday_insu';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
						<?php 
							if($print_day_week[7] == "&#8226")
								echo '<div class="independent_patients" style="width:0;">';
							else{
								echo '<div class="independent_patients">';
								$resArray = [];
								$element = 'saturday_part';//Particular and independent are the same for us
								$resArray = $calendar->dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements);
								echo $resArray[0];
								$input_elements[$element] = $resArray[1];
							}
						?>
						</div>
					</div>
					
				</div>
			</div>
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>	
				<div class="floating_message"> Saved Changes. </div>
				<b>Please, save the schedule changes.</b>
				<input type="submit" name="calendar_submit_button" value="Save" class="save_data_calendar">
			<?php 
					        break;
					
					case("es"):
			?>	
				<div class="floating_message"> Cambios Guardados. </div>
				<b>¿Deseas confirmar cambios hechos al horario?</b>
				<input type="submit" name="calendar_submit_button" value="Guardar" class="save_data_calendar">
			<?php 
						break;
				}
			?>

		</form>
	</div>
	<?php


		if(isset($_POST['calendar_submit_button'])){
			$_SESSION['saved_cal'] = 1;

			if($select_week_to_mod != 'default' && $select_year_to_mod != 'default'){
				$ava_calendar_tab = $userLoggedIn . "__doc_conf_week__" . $select_year_to_mod . "_" . $select_week_to_mod;
			}
			else{
				$ava_calendar_tab = $user_obj->getAvailableCalendar();			
			}
			foreach ($input_elements as $col_element => $column_element_arr) {

				$stmt = $con->prepare("UPDATE $ava_calendar_tab SET $col_element = ? WHERE id = ?");

				foreach ($column_element_arr as $key => $valuepphp) {
					$val_element = $_POST[$valuepphp];

					preg_match('!X(.*?)X!', $valuepphp, $output);
					$id_element = $output[1];

					$stmt->bind_param("ii", $val_element, $id_element);
					$stmt->execute();
							
				}
				$stmt->close();
			}
			header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		}
	?>
	

</body>
</html>


<script>
	$(document).ready(function(){

		val_save = "<?php echo $show_saved?>";
		
		if(val_save == 1){
			$(".floating_message").show(300);
			setTimeout(function () {
				$(".floating_message").hide(300);
			}, 1500);
		}
	});
</script>