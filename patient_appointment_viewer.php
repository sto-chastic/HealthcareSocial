<?php  
	include('includes/header2.php');
	require 'includes/form_handlers/basic_info_form.php';
	
//Check if it is a legal user and appointment
	if(isset($show_login_modal)){
		if($show_login_modal){
			echo "<script>
					$(document).ready(function(){
						$('#login-modal').modal('show');
						$('#login-modal').on('hidden.bs.modal', function () {
						    window.location = 'register.php';
						});
					});
				</script>";
		}
	}
	if(isset($_GET['cid'])){
		$appoinments_calendar_tab = $user_obj->getAppointmentsCalendar_Patient();
		$appoinments_details_tab = $user_obj->getAppointmentsDetails_Patient();

		$temp_id = $_GET['cid'];
	
		//echo "SELECT * FROM $appoinments_details_tab WHERE consult_id=? AND patient_username=?";
		$stmt = $con->prepare("SELECT * FROM $appoinments_details_tab WHERE consult_id=? AND patient_username=?");
		$stmt->bind_param("ss", $temp_id, $userLoggedIn);
		$stmt->execute();
		$verification_query = $stmt->get_result();
		$temp_details = mysqli_fetch_array($verification_query);
		
		$stmt = $con->prepare("SELECT * FROM $appoinments_calendar_tab WHERE consult_id=?");
		$stmt->bind_param("s", $temp_id);
		$stmt->execute();
		$verification_query_2 = $stmt->get_result();
		$temp_calendar = mysqli_fetch_array($verification_query_2);
		$stmt->close();


		if(mysqli_num_rows($verification_query) == 1 && mysqli_num_rows($verification_query_2) == 1){
			$details = $temp_details;
			$appointment_id = $temp_id;
			$doctor_username = $details['doctor_username'];
			$doctor_username_e = $crypt->EncryptU($doctor_username);
			$doctor_obj = new User($con, $doctor_username, $doctor_username_e);
			$calendar = $temp_calendar;
			$error_array = [];
			$rejected_symptoms = [];
			$rejected_medicines = [];

			//Language
			
			$lang = $_SESSION['lang'];
			
			switch ($lang){
				
				case("en"):
					$months_lang = "months_eng";
					break;
					
				case("es"):
					$months_lang = "months_es";
					break;
			}
			
			
			
			//Previous info load
			
			$stmt = $con->prepare("SELECT * FROM basic_info_patients WHERE username=?");
			$stmt->bind_param("s",$userLoggedIn);
			$stmt->execute();
			$basic_info = mysqli_fetch_array($stmt->get_result());
			
			//Language Tables:
			
			switch ($lang){
				
				case("en"):
					$months_row_lang = 'months_eng';
					$days_week_row_lang = 'days_short_eng';
					break;
					
				case("es"):
					$months_row_lang = 'months_es';
					$days_week_row_lang = 'days_short_es';
					break;
			}

			//reschedule
			
			$current_day = date("d");
			$current_month = date("m"); //Gets current month
			$current_year = date("Y");
			
			$scheduling_calendar = new Appointments_Calendar($con, $doctor_username, $doctor_username_e,$current_year, $current_month);
			
		}
		else{
			$details = [];
			$calendar = [];
			header('Location: index.php');
		}
	}
	else{
	    echo "no";
		//header('Location: index.php');
	}

	if(isset($_POST['add_symptoms_butt'])){

		$empty = str_replace(' ', '', $_POST['post_symptoms']);

		if($empty == "" && isset($_POST['post_text'])){
			$_SESSION['post_symptoms'] = $_POST['post_symptoms'];
			$_SESSION['post_text'] = $_POST['post_text'];
			array_push($error_array, "empty_symptoms"); 
		}
		elseif(isset($_POST['post_symptoms'])){
			$symptoms_table_doc = $doctor_obj->getAppointmentsSymptoms_Doctor();
			$symptoms_table_pat = $user_obj->getAppointmentsSymptoms_Patient();

			$description = htmlspecialchars($_POST['post_text']);
			$description = strip_tags($description);
			$description = preg_replace("/[^\p{Xwd},. ]+/u", "", $description);

			$symptoms = htmlspecialchars($_POST['post_symptoms']);
			$symptoms = strip_tags($symptoms);
			$symptoms = preg_replace("/[^\p{Xwd},. ]+/u", "", $symptoms);

			$symptoms_array = explode(",", $symptoms);
			$symptoms_array = array_map('ltrim',$symptoms_array);
			$symptoms_array = array_map('rtrim',$symptoms_array);
			$symptoms_array = array_map('strtolower',$symptoms_array);
			$symptoms_array = array_map('ucwords',$symptoms_array);

			$stmt = $con->prepare("SELECT title FROM $symptoms_table_doc WHERE consult_id=? ");
			$stmt->bind_param("s", $appointment_id);
			$stmt->execute();
			$query = $stmt->get_result();
			$prev_symptoms_arr = [];
			while($row = mysqli_fetch_array($query)) {
				$prev_symptoms_arr[] = $row['title'];
			}

			$stmt = $con->prepare("INSERT INTO $symptoms_table_doc VALUES(?,?,?,'','',?) ");

			$sympton_id_arr = [];
			foreach ($symptoms_array as $key => $symptom) {
				if($symptom != "" && !in_array($symptom, $prev_symptoms_arr)){
					$id = $appointment_id . date('His') . $key;
					$sympton_id_arr[$key] = $id;
					$stmt->bind_param("ssss", $appointment_id, $symptom, $description, $id);
					$stmt->execute();
				}
			}

			$stmt = $con->prepare("INSERT INTO $symptoms_table_pat VALUES(?,?,?,'','',?) ");

			foreach ($symptoms_array as $key => $symptom) {
				$check_bool = 1;
				if(in_array($symptom, $prev_symptoms_arr)){
					$check_bool = 0;
					array_push($rejected_symptoms, $symptom);
				}
				if($symptom != "" && $check_bool){
					$id = $sympton_id_arr[$key];
					$stmt->bind_param("ssss", $appointment_id, $symptom, $description, $id);
					$stmt->execute();
				}
			}
			
			//create symptoms frequency tables
			
			$doctor_symptoms_table = $doctor_obj->getSymptomsFrecTable();
			$query = mysqli_query($con,"SELECT title, cast(100*COUNT(title)/(SELECT Count(*) FROM $symptoms_table_doc) as decimal(6,3)) as prob FROM $symptoms_table_doc GROUP BY title ORDER BY prob DESC");
			
			$query_tr = mysqli_query($con,"TRUNCATE TABLE $doctor_symptoms_table");
			$stmt = $con->prepare("INSERT INTO $doctor_symptoms_table VALUES(?,?,?) ");
			$stmt->bind_param("iss",$key,$title,$prob);
			
 			foreach ($query as $key => $value){
				$title = $value['title'];
				$prob = $value['prob'];
				$stmt->execute();
			}
			$stmt->close();
			$_SESSION['post_symptoms'] = "";
			$_SESSION['post_text'] = "";
		}

	}

	if(isset($_POST['add_medicines_butt'])){

		$empty = str_replace(' ', '', $_POST['post_medicines']);

		if($empty == "" && isset($_POST['post_text_medicines'])){
			$_SESSION['post_medicines'] = $_POST['post_medicines'];
			$_SESSION['post_text_medicines'] = $_POST['post_text_medicines'];
			array_push($error_array, "empty_medicines"); 
		}
		elseif(isset($_POST['post_medicines'])){
			$medicines_table_doc = $doctor_obj->getAppointmentsMedicines_Doctor();
			$medicines_table_pat = $user_obj->getAppointmentsMedicines_Patient();

			$description = htmlspecialchars($_POST['post_text_medicines']);
			$description = strip_tags($description);
			$description = preg_replace("/[^\p{Xwd},. ]+/u", "", $description);

			$medicines = htmlspecialchars($_POST['post_medicines']);
			$medicines = strip_tags($medicines);
			$medicines = preg_replace("/[^\p{Xwd},. ]+/u", "", $medicines);

			$medicines_array = explode(",", $medicines);
			$medicines_array = array_map('ltrim',$medicines_array);
			$medicines_array = array_map('rtrim',$medicines_array);
			$medicines_array = array_map('strtolower',$medicines_array);
			$medicines_array = array_map('ucwords',$medicines_array);


			$stmt = $con->prepare("SELECT name FROM $medicines_table_doc WHERE consult_id=? ");
			$stmt->bind_param("s", $appointment_id);
			$stmt->execute();
			$query = $stmt->get_result();
			$prev_medicines_arr = [];
			while($row = mysqli_fetch_array($query)) {
				$prev_medicines_arr[] = $row['name'];
			}

			$stmt = $con->prepare("INSERT INTO $medicines_table_doc VALUES(?,?,?,'','',?) ");

			$medicine_id_arr = [];
			foreach ($medicines_array as $key => $medicine) {
				if($medicine != "" && !in_array($medicine, $prev_medicines_arr)){
					$id = $appointment_id . date('His') . $key;
					$medicine_id_arr[$key] = $id;
					$stmt->bind_param("ssss", $appointment_id, $medicine, $description, $id);
					$stmt->execute();
				}
			}

			$stmt = $con->prepare("INSERT INTO $medicines_table_pat VALUES(?,?,?,'','',?) ");
			foreach ($medicines_array as $key => $medicine) {
				$check_bool = 1;
				if(in_array($medicine, $prev_medicines_arr)){
					$check_bool = 0;
					array_push($rejected_medicines, $medicine);
				}
				if($medicine != "" && $check_bool){
					$id = $medicine_id_arr[$key];
					$stmt->bind_param("ssss", $appointment_id, $medicine, $description, $id);
					$stmt->execute();
				}
			}
			$stmt->close();
			$_SESSION['post_medicines'] = "";
			$_SESSION['post_text_medicines'] = "";
		}
	}
	
	if(isset($_POST['patho_butt'])){
		$link = '#personalInfoTabs a[href="#pathologies"]';
		echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
	}
	
	if(isset($_POST['surgeries_butt'])){
		$link = '#personalInfoTabs a[href="#surgical_trauma"]';
		echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
	}
	
	if(isset($_POST['hereditary_butt'])){
		$link = '#personalInfoTabs a[href="#hereditary"]';
		echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
	}
	if(isset($_POST['medicines_butt'])){
		$link = '#personalInfoTabs a[href="#pharmacology"]';
		echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
	}
	if(isset($_POST['allergies_butt'])){
		$link = '#personalInfoTabs a[href="#allergies"]';
		echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
	}
	if(isset($_POST['save_women_info'])){
		$link = '#personalInfoTabs a[href="#OBGYN"]';
		echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
	}
	if(isset($_POST['save_habits_info'])){
		$link = '#personalInfoTabs a[href="#habits"]';
		echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
	}



?>
<!-- CSS -->
<link rel="stylesheet" type="text/css" href="assets/css/appointment_details.css">

<style type="text/css">
	.wrapper{
		
		margin-left: 0px;
		padding-left:0px;
		
	}
	
	
</style>

<div class= "top_banner_title">
	<div class="top_banner_title_text_container">
		<h1><?php 
		switch ($lang){
			
			case("en"):
				echo "Appointment Details";
				break;
				
			case("es"):
				echo "Detalles de Cita";
				break;
		}
		?>
		</h1>
		<h2>
		<?php 
		switch ($lang){
			
			case("en"):
				echo "control your appointment";
				break;
				
			case("es"):
				echo "controla tu cita";
				break;
		}
		?>
		</h2>
	</div>
</div>

<script>
	function selectDay4Booking(year,month,day,payment_method,profileUsername,selected_appo_id){
		var appointment_id = '<?php echo $appointment_id ?>';
		payment_method = $("#payment_type").find(":selected").val();
		$("#day_iframe").attr('src', "day_frame.php?d=" + day + "&m=" + month + "&y=" + year + "&pm=" + payment_method + "&po=" + profileUsername + "&at=" + selected_appo_id + "&aid=" + appointment_id);
	}
</script>

<div class="wrapper">
	<div class="profile_left">
		<h2>
		<?php 
		switch ($lang){
			
			case("en"):
				echo "Appointment <br> Description";
				break;
				
			case("es"):
				echo "Descripción de <br> la cita";
				break;
		}
		?></h2>
		<div id="small_data">
    		<h3>Doctor</h3>
    		<img id="small_square_img" src="<?php echo $txt_rep->entities($doctor_obj->getProfilePicFast()); ?>">
    		
    			<h1><?php echo $txt_rep->entities($doctor_obj->getFirstAndLastNameFast()) ?> </h1>
    			<h2><?php echo $txt_rep->entities($doctor_obj->getSpecializationsText($lang))?> </h2>
    			
		</div>	
		
	<div id="small_data">
		<?php
			$month_numb = $calendar['month'];

			$month_q = mysqli_query($con, "SELECT $months_lang FROM months WHERE id='$month_numb'");
			$month_name = mysqli_fetch_array($month_q)[$months_lang];
			switch ($lang){
			    case("en"):
			        echo "<h3>Date</h3>" ."<h4>". $calendar['day'] . " / " . $month_name . " / " . $calendar['year'] . "</h4>";
			        break;
			        
			    case("es"):
			        echo "<h3>Fecha</h3>" ."<h4>". $calendar['day'] . " / " . $month_name . " / " . $calendar['year'] . "</h4>";
			        break;
			}
			$time_start = new DateTime($calendar['time_start']);
			$time_end = new DateTime($calendar['time_end']);

			$time_start_f = $time_start->format("g:ia");
			$time_end_f = $time_end->format("g:ia");

			echo "<b>". $time_start_f ."-";
			echo $time_end_f ."</b>";
		?>
		
		<input type="submit" class="deep_blue" data-toggle="modal" data-target="#reschedule" value="<?php 
			switch ($lang){				
				case("en"):
					echo "Reschedule";
					break;
				case("es"):
					echo "Reprogramar";
					break;
			}
			?>">

				<hr>
		<h3><?php 
			switch ($lang){
				
				case("en"):
					echo "Address:";
					break;
					
				case("es"):
					echo "Dirección:";
					break;
			}
			?></h3>
			<p><?php 
			$appo_dets =  $doctor_obj->getAppointmentsDetails_Doctor();
			$stmt = $con->prepare("SELECT office FROM $appo_dets WHERE patient_username = ?");
			$stmt->bind_param("s",$userLoggedIn);
			$stmt->execute();
			
			$q_offi_n = $stmt->get_result();
			$arr_offi_n = mysqli_fetch_assoc($q_offi_n);
			//echo $arr_offi_n['office'];
			
			$office_sel = $txt_rep->entities($arr_offi_n['office']);
			$addr_1 = "ad" . $office_sel ."ln1";
			$addr_2 = "ad" . $office_sel ."ln2";
			$addr_3 = "ad" . $office_sel ."ln3";
			$addr_4 = "ad" . $office_sel ."city";
			$addr_5 = "adcountry";
			
			$stmt = $con->prepare("SELECT $addr_1 , $addr_2, $addr_3 , $addr_4, $addr_5 FROM basic_info_doctors WHERE username = ?");
			$stmt->bind_param("s",$doctor_username);
			$stmt->execute();
			
			$q_offi = $stmt->get_result();
			$arr_offi = mysqli_fetch_assoc($q_offi);
			
			$stmt = $con->prepare("SELECT city FROM cities_CO WHERE city_code = ?");
			$stmt->bind_param("s",$arr_offi['ad1city']);
			$stmt->execute();
			$q_offi_city = $stmt->get_result();		
			$arr_offi_city = mysqli_fetch_assoc($q_offi_city);
	
			
			switch ($arr_offi[$addr_5]){
				
				case("CO"):
					$country_disp = "Colombia";
					break;
					
				case("US"):
					$country_disp = "United States";
					break;
			}
			
			echo "<span style='color:#f1769b;'>".$arr_offi[$addr_1] . ",</span> <br> " . $arr_offi[$addr_2] . " " . $arr_offi[$addr_3] . ", <br> " . $arr_offi_city["city"] . ", <br> " . $country_disp;
			?>
		</div>
	</div>

	<div class="right_column column">
		
	
		<h2>
		<?php 
		switch ($lang){
			case("en"):
	            echo "Added Symptoms";
			        break;
				
			case("es"):
					    echo "Síntomas Agregados";
			        break;
		}?>
		</h2>
		<div id="right_data">
		<div class="appo_symp_med_box style-2" id="appo_symp_med_box">
			<?php 
				
				$symptomsTable = $doctor_obj->getAppointmentsSymptoms_Doctor();
				$type = "symptoms";
				$stmt = $con->prepare("SELECT * FROM $symptomsTable WHERE consult_id = ? ORDER BY id");
				$stmt->bind_param("s", $appointment_id);

				$stmt->execute();
				$get_symptoms_query = $stmt->get_result();
				$num_types = mysqli_num_rows($get_symptoms_query);
				$stmt->close();

				$data="";
				if($num_types > 0){

					while($arr = mysqli_fetch_array($get_symptoms_query)){
						$title = $arr['title'];
						$id = $arr['id'];

						$txt_rep = new TxtReplace();
						$title = $txt_rep->entities($title);

						$line = "<div class='table_element' id='translucid_appo_type'>" . $title . "<div class='delete_element' id='del_id_" . $id . "'>x</div></div>";

						$data = $data . $line;

						?>
							<script>
								$(document).ready(function(){
									$('#del_id_<?php echo $id; ?>').on('click',function(){

										$.ajax({
											url: "includes/form_handlers/delete_symp_med.php",
											type: "POST",
											data: "id=<?php echo $id; ?>&aid=<?php echo $appointment_id; ?>&type=<?php echo $type; ?>&u=<?php echo bin2hex($doctor_username_e); ?>",
											cache:false,
											//async: false,

											success: function(data){
											},
											
										});
										
										var cid = '<?php echo $txt_rep->entities($appointment_id); ?>';
										$.ajax({
											url: "includes/handlers/ajax_reload_symptoms.php",
											type: "POST",
											data: "cid=" + cid,
											cache:false,

											success: function(data){
												$('#appo_symp_med_box').html("");
												$('#appo_symp_med_box').html(data);
												var ddd = document.getElementById('appo_symp_med_box');
												ddd.scrollTop = ddd.scrollHeight;
											}
										});
										//location.reload();
									});
								});
							</script>

						<?php

					}
				}	
				else{
					switch ($lang){
						
						case("en"):
							$data = "<div class='table_element' id='translucid_appo_type'> Insert a symptom. <div class='table_element' id='translucid_appo_durat'></div></div>";
							break;
							
						case("es"):
							$data = "<div class='table_element' id='translucid_appo_type'> Agrega un síntoma.<div class='table_element' id='translucid_appo_durat'></div></div> ";
							break;
					}
					
				}

				echo $data;
			?>
			
		</div>
		<script>
			var d = document.getElementById("appo_symp_med_box");// Make load in inverse order.
			d.scrollTop = d.scrollHeight;
		</script>
		<h2 style=" display: none;">
		<?php 
		
		switch ($lang){
			
			case("en"):
				echo "Added Medicines";
				break;
				
			case("es"):
				echo "Medicinas Agregadas";
				break;
		}
		
		?></h2>
		<div class="appo_symp_med_box style-2" id="appo_med_box" style=" display: none;">
			<?php  
				$medicinesTable = $doctor_obj->getAppointmentsMedicines_Doctor();
				$type = "medicines";
				$stmt = $con->prepare("SELECT * FROM $medicinesTable WHERE consult_id = ? ORDER BY id");
				$stmt->bind_param("s", $appointment_id);

				$stmt->execute();
				$get_medicines_query = $stmt->get_result();
				$num_types = mysqli_num_rows($get_medicines_query);
				$stmt->close();

				$data="";
				if($num_types > 0){

					while($arr = mysqli_fetch_array($get_medicines_query)){
						$title = $arr['name'];
						$id = $arr['id'];

						$txt_rep = new TxtReplace();
						$title = $txt_rep->entities($title);

						$line = "<div class='table_element' id='translucid_appo_type'>" . $title . "<div class='delete_element' id='del_id_" . $id . "'>x</div></div>";

						$data = $data . $line;

						?>
							<script>
								$(document).ready(function(){
									$('#del_id_<?php echo $id; ?>').on('click',function(){
										$.post("includes/form_handlers/delete_symp_med.php?id=<?php echo $id; ?>&aid=<?php echo $appointment_id; ?>&type=<?php echo $type; ?>&u=<?php echo bin2hex($doctor_username_e); ?>");
										
										location.reload();
									});
								});
							</script>

						<?php

					}
				}	
				else{
					switch ($lang){
						
						case("en"):
							$data = "<div class='table_element' id='translucid_appo_type'> Insert a medicine name. <div class='table_element' id='translucid_appo_durat'></div></div>";
							break;
							
						case("es"):
							$data = "<div class='table_element' id='translucid_appo_type'> Agrega una medicina. <div class='table_element' id='translucid_appo_durat'></div></div>";
							break;
					}
					
				}

				echo $data;
			?>
		</div>
		<script>
			var d = document.getElementById("appo_med_box");// Make messages load in inverse order.
			d.scrollTop = d.scrollHeight;
		</script>
		<div style="margin-top: 25px">
			<?php 
			switch ($lang){
				
				case("en"):
					?>
					<p> Your entries above are already stored. </p>
					<p> Optionally, you can review your medical information for any recent changes <a href="#review_helath_info_container" id="update_info_from_dashboard"><u>here below</u></a>.</p>
					<?php
					break;
					
				case("es"):
					?>
					<p> Tu información de arriba ya ha sido guardada. </p>
					<p> Opcionalmente, puedes actualizar tu información médica previa <a href="#review_helath_info_container" id="update_info_from_dashboard"><u>aqui</u>.</a></p>
					<?php
					break;
			}
			?>

		</div>
		<?php 
                switch($lang){
                    case("en"):{

        ?>
		<input type="submit" class="appo_confirm" data-toggle="modal" data-target="#confirm_window" value="Confirm Appointment">
		<input type="submit" class="appo_cancel" data-toggle="modal" data-target="#cancel_window" value="Cancel Appointment">
        <?php 
		          break;
		          }
		          case("es"):{
        ?>  
        <input type="submit" class="appo_confirm" data-toggle="modal" data-target="#confirm_window" value="Confirmar Cita">
		<input type="submit" class="appo_cancel" data-toggle="modal" data-target="#cancel_window" value="Cancelar Cita">
		<?php 
	              break;
		          }
                }
		?>
		</div>
		<script>
			$(document).ready(function(){
				$('#finish_button').on('click',function(){
					var appointment_id = '<?php echo $appointment_id; ?>';
					var doctor_username = '<?php echo bin2hex($doctor_username_e); ?>';
					var ajaxst = $.ajax({
						url: "includes/handlers/ajax_finish_patient_appo.php",
						type: "POST",
						data: "aid=" + appointment_id + "&did=" + doctor_username,
						cache: false,
	
						success: function(response){
							//alert(response);
							window.location.href = "index.php";
						}
					});
				});

				$('#cancel_button').on('click',function(){
					var appointment_id = '<?php echo $appointment_id; ?>';
					var doctor_username = '<?php echo bin2hex($doctor_username_e); ?>';
					var ajaxst = $.ajax({
						url: "includes/handlers/ajax_cancel_patient_appo.php",
						type: "POST",
						data: "aid=" + appointment_id + "&did=" + doctor_username,
						cache: false,
	
						success: function(response){
							//alert(response);
							window.location.href = "index.php";
						}
					});
				});

				$('a[href="#review_helath_info_container"]').on('click',function (e) {
				    e.preventDefault();

				    var target = this.hash;
				    var $target = $(target);

				    $('html, body').stop().animate({
				        'scrollTop': $target.offset().top
				    }, 900, 'swing', function () {
				        window.location.hash = target;
				    });
				});
			});
		</script>		
	</div>

	<div class="profile_main_column column">
		<ul class="nav nav-tabs" role="tablist" id="profileTabs">
			<li  role="presentation" class="active"><div class="arrow-down"></div><a href="#prev_div" aria-controls="prev_div" role="tab" data-toggle="tab"><span id="prev_date">hol</span> 
			  <?php switch($lang){
			      case("en"):
			          echo "Before Appointment";     
			          break;
			      case("es"):
			          echo "Antes de la Cita";
			          break;
			  }?></a>
		  	</li>
  		  	<li role="presentation"><div class="arrow-down"></div><a href="#after_div" aria-controls="after_div" role="tab" data-toggle="tab"><span id="post_date">hol</span>
			  <?php switch($lang){
			      case("en"):
			          echo "After Appointment";     
			          break;
			      case("es"):
			          echo "Luego de la Cita";
			          break;
			  }?></a>
		  	</li>
		</ul>
		
		<div class="tab-content">
			
			<div role="tabpanel" class="tab-pane fade in active" id="prev_div">
		
				<form action="" method="POST" name="search_form_symptoms" class="search_form_symptoms">
					<h2>
					<?php 
				        switch ($lang){
				        	
				        	case("en"):
				        		echo "Do you have any symptoms ?";
				        		break;
				        		
				        	case("es"):
				        		echo "¿Tienes algún síntoma?";
				        		break;
				        }
				        
			        ?></h2>
					<p>
					<?php 
				        switch ($lang){
				        	
				        	case("en"):
				        		echo "Type each symptom into the search box separated by commas. No symptoms? Just leave it blank.";
				        		break;
				        		
				        	case("es"):
				        		echo "Escribe cada síntoma separado por comas en el campo de busqueda abajo. ¿No tienes síntomas? Déjala en blanco.";
				        		break;
				        }
				        
			        ?>
			        </p>
					<div>
						<div class="search_appointments">
							<?php $symp = "symptoms"; ?>
							<input type="text" onkeyup="sanitizeSearchSymptomsMed(this.value,'<?php echo bin2hex($txt_rep->entities($doctor_username_e)); ?>', '<?php echo $symp; ?>')" placeholder='<?php 
									        switch ($lang){
									        	
									        	case("en"):
									        		echo "Ex: Stomach Ache, Fever, Vomit, etc...";
									        		break;
									        		
									        	case("es"):
									        		echo "Ej: Dolor de estómago, fiebre, vómito, etc...";
									        		break;
									        }
									        
								        ?>' autocomplete="off" id="search_text_input_symptoms" name="post_symptoms" value="<?php 
									if(isset($_SESSION['post_symptoms'])){
										echo $txt_rep->entities($_SESSION['post_symptoms']);
									}
								?>"
							>
							
		
							<div class="search_symptoms_results">
							</div>
		
						</div>
						<?php 
							if(in_array("empty_symptoms", $error_array)){
								switch ($lang){
									
									case("en"):
										echo "<div class='warning_dashboard'>
												<b>Symptoms cannot be empty if the description is not empty.<br></b>
											</div>
											";
										break;
										
									case("es"):
										echo "<div class='warning_dashboard'>
												<b >Los síntomas no pueden estar vacios si el campo de descripción no está vacio.<br></b>
											</div>
											";
										break;
								}
							}
							elseif(!empty($rejected_symptoms)){
								$rej_symp_lin = implode(", ", $rejected_symptoms);
								switch ($lang){
									
									case("en"):
										echo "<div class='warning_dashboard'>
										<b >The symptom(s): " . $rej_symp_lin . ", was(were) already inserted for this consult and cannot be added twice. To make a change, delete the symptom and add it correctly again.<br></b>
									</div>
									";
										break;
										
									case("es"):
										echo "<div class='warning_dashboard'>
										<b>El (los) síntoma(s): " . $rej_symp_lin . ", agregados para esta consulta y no pueden ser agregados dos veces. Para hacer un cambio, borra el síntoma y agrégalo correctamente de nuevo.<br></b>
									</div>
									";
										break;
								}
		
							}
						?>
						<div style=" display: inline-block ;  margin-top: 50px; padding-top: 30px;  display: inline-block; width: 100%; border-top: 1px solid #ddd;">
							<h3><?php 
							switch ($lang){
								
								case("en"):
									echo "Symptom(s) Description:";
									break;
									
								case("es"):
									echo "Descripción de el(los) síntoma(s):";
									break;
							}
							?></h3>
							<textarea name="post_text" id="post_text" onkeyup="storeVal(this.value, '<?php echo $symp; ?>')" placeholder="<?php 
							switch ($lang){
								
								case("en"):
									echo "Ex: pain on the left side of the head / hurts after eating / it started 2 months ago ...";
									break;
									
								case("es"):
									echo "Ej: dolor en el lado izquierdo de la cabeza / duele al masticar / empezó hace 2 meses ...";
									break;
							}
							?>"><?php 
									if(isset($_SESSION['post_text'])){
										echo $txt_rep->entities($_SESSION['post_text']);
									}
								?></textarea>
						</div>
					
						<input type="submit" name="add_symptoms_butt" value="<?php 
							switch ($lang){
								
								case("en"):
									echo "Add";
									break;
									
								case("es"):
									echo "Agregar";
									break;
							}
							?>" id="post_button">
						
					</div>
				</form>
				
				<?php //TODO: MEdicines deprecated from here. Not translated?>
				<form action="" method="POST" name="search_form_medicines" class="search_form_medicines" style=" display:none;">
					<h2>Are you taking any medicine ?</h2>
					<p>Type each medicine you are taking into the search box separated by commas. Not taking any? Just leave it blank.</p>
					<div>
						<div class="search">
								<?php $symp = "medicines"; ?>
								<input type="text" onkeyup="sanitizeSearchSymptomsMed(this.value,'<?php echo bin2hex($txt_rep->entities($doctor_username_e)); ?>', '<?php echo $symp; ?>')" placeholder="Ex: Ibuprofen, Yasmin, Insulin, etc..." autocomplete="off" id="search_text_input_medicines" name="post_medicines" value="<?php 
										if(isset($_SESSION['post_medicines'])){
											echo $txt_rep->entities($_SESSION['post_medicines']);
										}
									?>"
								>
								<div class="button_holder_appointment">
									<img src="assets/images/icons/search-icon.png">
								</div>
		
								<div class="search_medicines_results">
								</div>
		
						</div>
						<?php 
							if(in_array("empty_medicines", $error_array)){
								echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>Medicines cannot be empty if the description is not empty.<br></p>
									</div>
									";
							}
							elseif(!empty($rejected_medicines)){
								$rej_med_lin = implode(", ", $rejected_medicines);
								echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>The medicine(s): " . $rej_med_lin . ", was(were) already inserted for this consult and cannot be added twice.<br></p>
									</div>
									";
							}
						?>
						<div style="margin-top:80px; display: inline-block ;">
							<p>Symptoms Description:</p>
							<textarea name="post_text_medicines" id="post_text_medicines" onkeyup="storeVal(this.value, '<?php echo $symp; ?>')" placeholder="Ex: I take insulin for my diabetes, 2 times a day. I started taking insulin 1 year ago..."
							><?php 
								if(isset($_SESSION['post_text_medicines'])){
									echo $txt_rep->entities($_SESSION['post_text_medicines']);
								}
							?></textarea>
						</div>
						<br>
						<input type="submit" name="add_medicines_butt" value="Add" style="display: inline-block;">
					</div>
				</form>
			</div>
			
			<div role="tabpanel" class="tab-pane" id="after_div">
				<h2>
					<?php 
				        switch ($lang){
				        	
				        	case("en"):
				        		echo "Doctor's Plan";
				        		break;
				        		
				        	case("es"):
				        		echo "Plan del Doctor";
				        		break;
				        }
				        
			        ?>
				</h2>
				
				<p>
					<?php 
				        switch ($lang){
				        	
				        	case("en"):
				        		echo "Here you can see the doctor's prescriptions, required lab exams, recommended next appointments, etc.";
				        		break;
				        		
				        	case("es"):
				        		echo "Aquí puedes ver las prescripciones del doctor, los exámenes de laboratorios requeridos, proximas visitas recomendadas, etc.";
				        		break;
				        }
				        
			        ?>
		        </p>
		        
		        <div class="plan_display_box_patient">
		        		<?php 
		        			$appo_det_doc = $doctor_obj->getAppointmentsDetails_Doctor();
		        			$stmt = $con->prepare("SELECT plan FROM $appo_det_doc WHERE consult_id=?");
		        			$stmt->bind_param("s",$appointment_id);
		        			$stmt->execute();
		        			$plan_q = $stmt->get_result();
		        			$plan_arr = mysqli_fetch_assoc($plan_q);
		        			$plan = $plan_arr['plan'];
		        			echo $plan;
		        		?>
		        </div>
			</div>
			
		</div>
	</div>
		
		
	<div class="review_helath_info_container" id="review_helath_info_container">
		<h1><?php 
			
			switch ($lang){
				
				case("en"):
					echo "OPTIONAL: Review your information for possible updates.";
					break;
					
				case("es"):
					echo "OPCIONAL: Actualiza tu información médica por posibles actualizaciones.";
					break;
			}
			
		?> </h1>
		<div class="main_column column" id="main_column" style=" width: 100%; margin-left: 0;">
	
				<?php 
		switch($lang){
		    case("en"):
		        echo '
            <ul class="nav nav-tabs" role="tablist" id="personalInfoTabs">
        			<li role="presentation" class="active"><div class="arrow-down"></div><a href="#basic_info" aria-controls="basic_info" role="tab" data-toggle="tab"><span id="basic_tab"></span>Basic Information</a></li>
					<li role="presentation"><div class="arrow-down"></div><a href="#pathologies" aria-controls="pathologies" role="tab" data-toggle="tab"><span id="illness_tab"></span>Illnesses / Hospitalizations</a></li>
					<li role="presentation"><div class="arrow-down"></div><a href="#surgical_trauma" aria-controls="surgical_trauma" role="tab" data-toggle="tab"><span id="surgeries_tab"></span>Surgeries / Traumas</a></li>
					<li role="presentation"><div class="arrow-down"></div><a  href="#hereditary" aria-controls="hereditary" role="tab" data-toggle="tab"><span id="hereditary_tab"></span>Hereditaries</a></li>
					<li role="presentation"><div class="arrow-down"></div><a href="#pharmacology" aria-controls="pharmacology" role="tab" data-toggle="tab"><span id="medicine_tab"></span>Medicines</a></li>
					<li role="presentation"><div class="arrow-down"></div><a  href="#allergies" aria-controls="allergies" role="tab" data-toggle="tab"><span id="allergies_tab"></span>Allergies</a></li>
					<li role="presentation"><div class="arrow-down"></div><a  href="#habits" aria-controls="habits" role="tab" data-toggle="tab"><span id="habits_tab"></span>Habits</a></li>
                        ';
		        $stmt = $con->prepare("SELECT sex FROM basic_info_patients WHERE username = ? AND sex = ?");
		        $stmt->bind_param("ss",$userLoggedIn,$female);
		        $female = "f";
		        $stmt->execute();
		        $sex_query = $stmt->get_result();
		        if(mysqli_num_rows($sex_query) == 1){
		            echo '<div id="sex_selected"><a href="#OBGYN" aria-controls="OBGYN" role="tab" data-toggle="tab">OBGYN</a></div>';
		        }
		      
		        break;
		    case("es"):
		        echo '
            <ul class="nav nav-tabs" role="tablist" id="personalInfoTabs">
                    <li role="presentation" class="active"><div class="arrow-down"></div><a href="#basic_info" aria-controls="basic_info" role="tab" data-toggle="tab"><span id="basic_tab"></span>Información Básica</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a href="#pathologies" aria-controls="pathologies" role="tab" data-toggle="tab"><span id="illness_tab"></span>Enfermedades / Hospitalizaciones</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a href="#surgical_trauma" aria-controls="surgical_trauma" role="tab" data-toggle="tab"><span id="surgeries_tab"></span>Cirugías / Trauma</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a  href="#hereditary" aria-controls="hereditary" role="tab" data-toggle="tab"><span id="hereditary_tab"></span>Hereditario</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a href="#pharmacology" aria-controls="pharmacology" role="tab" data-toggle="tab"><span id="medicine_tab"></span>Medicamentos</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a  href="#allergies" aria-controls="allergies" role="tab" data-toggle="tab"><span id="allergies_tab"></span>Alergias</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a  href="#habits" aria-controls="habits" role="tab" data-toggle="tab"><span id="habits_tab"></span>Hábitos</a></li>
                        ';
		        $stmt = $con->prepare("SELECT sex FROM basic_info_patients WHERE username = ? AND sex = ?");
		        $stmt->bind_param("ss",$userLoggedIn,$female);
		        $female = "f";
		        $stmt->execute();
		        $sex_query = $stmt->get_result();
		        if(mysqli_num_rows($sex_query) == 1){
		            echo '<div id="sex_selected"><a href="#OBGYN" aria-controls="OBGYN" role="tab" data-toggle="tab">Ginecológico/Obstétrico</a></div>';
		        }
		        break;
		}

			?>
		</ul>
			<div class="tab-health_info">
				<div class="tab-content" style=" width: 100%;">
				
					<div role="tabpanel" class="tab-pane fade in active" id="basic_info">
						<h3>
						<?php 
				
							switch ($lang){
								
								case("en"):
									echo "Add your personal informaion";
									break;
									
								case("es"):
									echo "Agrega tu información personal";
									break;
							}
							
						?></h3>
					 	<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $appointment_id ?>" method="POST">
						 	<div class="form_area">
						 		<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "sex";
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Sex *";
									        break;
									    case("es"):
									        echo "Sexo *";
									        break;
									}
									?>
									</p>
									
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> required>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] === $arr['id']){
    														echo "<option value='" . $arr['id']. "' selected='selected' >" . $arr[$lang] . "</option>";
    													}
    													else{
    														echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "blood_type"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Blood Type/Rh *";
									        break;
									    case("es"):
									        echo "Tipo de Sangre/Rh *";
									        break;
									}
									?>
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> required>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] == $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "birthdate"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Birthdate (YYYY-MM-DD) *";
									        break;
									    case("es"):
									        echo "Fecha de Nacimiento (AAAA-MM-DD) *";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: 1978-05-30";
    									        break;
    									    case("es"):
    									        echo "Ej: 1978-05-30";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($basic_info[$current_box] != '' && $basic_info[$current_box] != '0000-00-00'){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>" required
    									>
    									<?php 
    									if(in_array($current_box,$basic_error_array)){
    										echo "<div id='wrong_input'>Incorrect date format, insert as YYYY-MM-DD</div>";
    									}
    									?>
									</div>
								</div>
								
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "children"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Number of Children";
									        break;
									    case("es"):
									        echo "Número de hijos";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: 2";
    									        break;
    									    case("es"):
    									        echo "Ej: 2";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    											if($basic_info[$current_box] != ''){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>">
    								</div>		
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "marital_status"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Marital Status";
									        break;
									    case("es"):
									        echo "Estado Civil";
									        break;
									}
									?>
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?>>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "education_level"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Education Level";
									        break;
									    case("es"):
									        echo "Nivel de educación";
									        break;
									}
									?>
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?>>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "occupation"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Occupation";
									        break;
									    case("es"):
									        echo "Oficio";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: Lawyer";
    									        break;
    									    case("es"):
    									        echo "Ej: Abogado";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    											if($basic_info[$current_box] != ''){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>">
    								</div>		
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "religion"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Religion";
									        break;
									    case("es"):
									        echo "Religión";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: Catholic";
    									        break;
    									    case("es"):
    									        echo "Ej: Católico";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    											if($basic_info[$current_box] != ''){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>">
    								</div>		
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "languages"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Language(s)";
									        break;
									    case("es"):
									        echo "Idioma(s)";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: English, Spanish";
    									        break;
    									    case("es"):
    									        echo "Ej: Español, Inglés";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    											if($basic_info[$current_box] != ''){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>">
    								</div>		
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "insurance_CO"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Insurance *";
									        break;
									    case("es"):
									        echo "Seguro o prepagada *";
									        break;
									}
									?>
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> required>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info['insurance'] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info['insurance'] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "laterality"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Laterality (Hand predominance)";
									        break;
									    case("es"):
									        echo "Lateralidad (Con qué mano predominan sus acciones)";
									        break;
									}
									?>	
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?>>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>		
						 	
						 	</div>
							<div class="three_button_navigation">
								<div class="left_3_button_navigation">
								</div>
								<div class="right_3_button_navigation">
								</div>
								<div class="center_3_button_navigation">
		
									<input type="submit" id="save_data_stats_butt" name="save_personal_info" value="<?php 
									switch($lang){
									    case("en"):
									        echo "Save";
									        break;
									    case("es"):
									        echo "Guardar";
									        break;
									}
									?>">
		
								</div>
							</div>
					 	</form>
					</div>
		
					<div role="tabpanel" class="tab-pane fade" id="pathologies">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Illnesses and/or Hospitalizations";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega Enfermedades y/o Hospitalizaciones";
		        					        break;
		        					}
							?></h3>
					 	<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $appointment_id ?>" method="POST" name="form_pathologies" class="form_pathologies">
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Illnesses / Hospitalizations";
		        					        break;
		        					    case("es"):
		        					        echo "Enfermedades / Hospitalizaciones";
		        					        break;
		        					}
							?></p>
			
								<div class="dashboard_info">
    								<input type="text" name="patho_desc" id="select_illness" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Diabetes";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: Diabetes";
    		        					        break;
    		        					}
    							?>" required>
    							</div>
							</div>
							
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Approximated Date of Diagnosis or Hospitalization (YYYY-MM-DD)";
		        					        break;
		        					    case("es"):
		        					        echo "Fecha aproximada de diagnóstico u hospitalización (AAAA-MM-DD)";
		        					        break;
		        					}
							?></p>
								<div class="dashboard_info">
								<input type="text" name="patho_date" id="select_illness" placeholder="<?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Ex: 2005-07-30";
		        					        break;
		        					    case("es"):
		        					        echo "Ej: 2005-07-30";
		        					        break;
		        					}
							?>" required>
							
							</div> 
							<input type="submit" name="patho_butt" id="save_data_stats_add" value="+">
								<b class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "(You can input just a year, year and month, or the full date.)";
		        					        break;
		        					    case("es"):
		        					        echo "Puedes escribir sólo un año, un mes y un año, o la fecha completa";
		        					        break;
		        					}
							?></b>
						
		
			
								<?php 
								if(in_array("pathologies",$basic_error_array)){
								    switch($lang){
								        case("en"):
								            echo "<div class='warning_dashboard'><b>Incorrect date format, insert as YYYY-MM-DD, YYYY-MM, or YYYY.</b></div>";
								            break;
								        case("es"):
								            echo "<div class='warning_dashboard'><b>Formato de fecha incorrecto, insértalo como AAAA-MM-DD, AAAA-MM, o AAAA.</b></div>";
								            break;
								    }
								}
								?>
								
							</div>
							
					 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia";
		        					        break;
		        					}
							?></h3>
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Illnesses / Hospitalizations";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Enfermedades / Hospitalizaciones";
		                    					        break;
		                    					}
		            					?></p>
								</div>
								<div class="box_right">
									<p><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Approximate Date";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Fecha Aproximada";
		                    					        break;
		                    					}
		            					?></p>
								</div>
							</div>
		
				
							<div class="added_box style-2" id="pathologies_box">
								<?php 
									
									echo $user_obj->getPathologiesData(date("Y-m-d H:i:s"));
								?>
							</div>
							
						</div>
					</div>
					
					
					<div role="tabpanel" class="tab-pane fade" id="surgical_trauma">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Surgeries and/or Traumas";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega cirugías y/o trauma";
		        					        break;
		        					}
							?> </h3>
					 	<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $appointment_id ?>" method="POST" name="form_surgeries" class="form_surgeries">
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Surgeries / Traumas";
		        					        break;
		        					    case("es"):
		        					        echo "Cirugías / Trauma";
		        					        break;
		        					}
							?></p>
							<div class="dashboard_info">	
    								<input type="text" name="surgeries_desc" id="select_surgery"  placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Bone fracture, appendectomy";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: Fractura de hueso, apendicectomía";
    		        					        break;
    		        					}
    							?>" required>
    							</div>
			
							</div>
							
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Approximated Date (YYYY-MM-DD)";
		        					        break;
		        					    case("es"):
		        					        echo "Fecha aproximada (AAAA-MM-DD)";
		        					        break;
		        					}
							?></p>
							
							<div class="dashboard_info">	
    								<input type="text" name="surgeries_date" id="select_surgery" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: 2005-07-30";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: 2005-07-30";
    		        					        break;
    		        					}
    							?>" required>
    						
    						</div>	
    						<input type="submit" name="surgeries_butt" id="save_data_stats_add" value="+">	
							<b class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "(You can input just a year, year and month, or the full date.)";
		        					        break;
		        					    case("es"):
		        					        echo "Puedes escribir sólo un año, un mes y un año, o la fecha completa";
		        					        break;
		        					}
							?></b>
			
								<?php 
								if(in_array("surgeries",$basic_error_array)){
								    switch($lang){
								        case("en"):
								            echo "<div class='warning_dashboard'><b>Incorrect date format, insert as YYYY-MM-DD, YYYY-MM, or YYYY.</b></div>";
								            break;
								        case("es"):
								            echo "<div class='warning_dashboard'><b>Formato de fecha incorrecto, insértalo como AAAA-MM-DD, AAAA-MM, o AAAA.</b></div>";
								            break;
								    }
									
								}
								?>
							</div>
							
								
							
					 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia";
		        					        break;
		        					}
							?></h3>
				
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Surgery / Trauma";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Cirugía / Trauma";
		                    					        break;
		                    					}
		            					?></p>
								</div>
								<div class="box_right">
									<p><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Approximate Date";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Fecha Aproximada";
		                    					        break;
		                    					}
		            					?></p>
								</div>
							</div>
						
				
							<div class="added_box style-2" id="surgical_trauma_box">
								<?php  
									echo $user_obj->getSurgeriesData(date("Y-m-d H:i:s"));
								?>
							</div>
						</div>
					</div>
					
					<div role="tabpanel" class="tab-pane fade" id="hereditary">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Important Diseases in Your Family.";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega enfermedades importantes en tu familia";
		        					        break;
		        					}
							?></h3>
					 	<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $appointment_id ?>" method="POST" name="form_hereditary" class="form_surgeries">
		
		          <div class="dashboard_tag_block" >
					 		<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Disease:";
		        					        break;
		        					    case("es"):
		        					        echo "Enfermedad:";
		        					        break;
		        					}
							?></p>
							<div class="dashboard_info">
    				 			<input type="text" onkeyup="sanitizeSearchHealth(this.value,'<?php echo 'hereditary_diseases'; ?>','<?php echo $lang; ?>')" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Breast Cancer, Heart Disease";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Cáncer de seno, Enfermedad coronaria";
    		        					        break;
    		        					}
    							?>" autocomplete="off" class="search_health_info" name="hereditary_diseases_input" >
    						</div>	
    							<div class="button_holder_search_health">
    								<img src="assets/images/icons/search-icon-pink.png">
    							</div>
    						</div>	
						 <div class="dashboard_tag_block" >	
							<p class="dashboard_tag">
							<?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Relative:";
		        					        break;
		        					    case("es"):
		        					        echo "Familiar:";
		        					        break;
		        					}
							?></p>
							<div class="dashboard_info">
    							<input type="text" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Father, Sister";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: Padre, hermana";
    		        					        break;
    		        					}
    							?>" autocomplete="off" class="search_health_info" name="hereditary_relatives" id="relatives">
   
							
							
							
						</div>	
						<input type="submit" name="hereditary_butt" value="+" id="save_data_stats_add">
		          </div>	
					 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia:";
		        					        break;
		        					}
							?></h3>
				
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Diseases";
		        					        break;
		        					    case("es"):
		        					        echo "Enfermedades";
		        					        break;
		        					}
							?></p>
								</div>
								<div class="box_right">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Relatives";
		        					        break;
		        					    case("es"):
		        					        echo "Familiares";
		        					        break;
		        					}
							?></p>
								</div>
							</div>
							
				
							<div class="added_box style-2" id="hereditary_box">
								<?php  
									echo $user_obj->getHereditariesData();
								?>
							</div>
						</div>
					</div>
					
					
					<div role="tabpanel" class="tab-pane fade" id="pharmacology">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Medicines you Use";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega medicamentos que uses";
		        					        break;
		        					}
							?></h3>
					 	<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $appointment_id ?>" method="POST" name="form_pharmacology" class="form_surgeries">
		
		         <div class="dashboard_tag_block">
		         		
					 		<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Medicine name:";
		        					        break;
		        					    case("es"):
		        					        echo "Nombre del medicamento";
		        					        break;
		        					}
							?></p>
						<div class="dashboard_info">	
							<input type="text" onkeyup="sanitizeSearchHealth(this.value,'<?php echo 'medicines2dosage'; ?>','<?php echo $lang; ?>')" placeholder="<?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Ex: Ibuprofen, Omeprazol";
		        					        break;
		        					    case("es"):
		        					        echo "Ej: Ibuprofeno, Omeprazol";
		        					        break;
		        					}
							?>" autocomplete="off" class="search_health_info" name="medicines2dosage_input" >
							<div class="search_history_results style-2" id="search_medicines2dosage"></div>
							
						</div>
						<div class="button_holder_search_health">
								<img src="assets/images/icons/search-icon-pink.png">
						</div>
				</div>		
		         <div class="dashboard_tag_block">
		          				<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Dosage:";
		        					        break;
		        					    case("es"):
		        					        echo "Dosis:";
		        					        break;
		        					}
							?></p>
	
    				 		<div class="dashboard_info">						
    							<input type="text" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: 50 mg in the morning";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: 50 mg cada mañana";
    		        					        break;
    		        					}
    							?>" autocomplete="off" class="search_health_info" name="medicines2dosage_dosage_input" id="dosage">
    						</div>	
							<input type="submit" name="medicines_butt" value="+"  id="save_data_stats_add">
							
							
							</div>	
							<input type="hidden" name="lang" value=<?php echo '"' . $lang . '"'; ?>>
							<input type="hidden" name="searched_id_medicines2dosage">
		     			
					 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia";
		        					        break;
		        					}
							?></h3>
				
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Medicines:";
		        					        break;
		        					    case("es"):
		        					        echo "Medicamentos:";
		        					        break;
		        					}
							?></p>
								</div>
								<div class="box_right">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Dosage";
		        					        break;
		        					    case("es"):
		        					        echo "Dosis";
		        					        break;
		        					}
							?></p>
								</div>
							</div>
							
				
							<div class="added_box style-2" id="medicines_box">
								<?php  
									echo $user_obj->getMedicinesData();
								?>
							</div>
						</div>
					</div>		
					
					
					<div role="tabpanel" class="tab-pane fade" id="allergies">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Known Allergies";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega alergias conocidas";
		        					        break;
		        					}
							?> </h3>
					 	<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $appointment_id ?>" method="POST" name="form_allergies" class="form_surgeries">
		
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Allergies:";
		        					        break;
		        					    case("es"):
		        					        echo "Alergias:";
		        					        break;
		        					}
							?></p>
								<div class="dashboard_info">
    								<input type="text" name="allergies_input" id="select_allergies" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Penicillin, Peanuts";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ex: Penicilina, Fresas";
    		        					        break;
    		        					}
    							?>"  required>
    							</div>
    		
			
								<input id="save_data_stats_add" type="submit" name="allergies_butt" value="+"  id="save_data_stats_add">
							</div>
									
					 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia";
		        					        break;
		        					}
							?></h3>
				
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Allergies:";
		        					        break;
		        					    case("es"):
		        					        echo "Alergias:";
		        					        break;
		        					}
							?></p>
								</div>
							</div>
							
				
							<div class="added_box style-2" id="allergies_box">
								<?php  
									echo $user_obj->getAllergiesData();
								?>
							</div>
						</div>
					</div>
					
					
					<div role="tabpanel" class="tab-pane fade" id="habits" >
						<h3><?php 
				
							switch ($lang){
								
								case("en"):
									echo "Fill-in the next cells according your habits";
									break;
									
								case("es"):
									echo "Completa los siguientes campos de acuerdo a tus hábitos";
									break;
							}
							
						?></h3>
						<?php
							$habits_table = $user_obj->getHabitsTable();
							$stmt = $con->prepare("SELECT * FROM $habits_table ORDER BY id DESC LIMIT 1");
							$stmt->execute();
							$habits_info = mysqli_fetch_array($stmt->get_result());
						?>
					
					 	<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $appointment_id ?>" method="POST">
						 	<div class="form_area">
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "smoking"; ?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Do you smoke? How much and how often?";
		                    					        break;
		                    					    case("es"):
		                    					        echo "¿Fumas? ¿Qué tanto y qué tan seguido?";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: 3 packs a day.";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ex: 3 packetes diarios";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="32" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
									></div>
								</div>
								
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "alcohol"; ?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Do you drink alcohol? How much and how often?";
		                    					        break;
		                    					    case("es"):
		                    					        echo "¿Bebes alcohol? ¿Qué tanto y qué tan seguido?";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: 2 beers a week";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ex: 2 cervezas semanales";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="30" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
    									></div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "diet"; ?>
									<p class="dashboard_tag"><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Describe your diet briefly";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Describe tu dieta de manera concisa";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: Vegetarian, Low Sodium";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Vegetariana, Baja en sodio";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="30" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
    									></div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "physical_activity"; ?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Are you physically active? How so?";
		                    					        break;
		                    					    case("es"):
		                    					        echo "¿Eres Físicamente activo? Explica";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: 20 minute walk daily";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Camino 20 minutos al día";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="30" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
    									></div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "other"; ?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Others";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Otros";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: Drugs consumption";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Consumo de drogas";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="32" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
    									></div>
								</div>
								
								<div class="three_button_navigation">
									<div class="left_3_button_navigation">
									</div>
									<div class="right_3_button_navigation">
									</div>
									<div class="center_3_button_navigation">
										<input type="submit" id="save_data_stats_butt" name="save_habits_info" value="<?php 
        									switch($lang){
        									    case("en"):
        									        echo "Save";
        									        break;
        									    case("es"):
        									        echo "Guardar";
        									        break;
        									}
        									?>">
									</div>
								</div>
								
							</div>
						</form>
					</div>			
					
					<div role="tabpanel" class="tab-pane fade" id="OBGYN" >
						<?php
							$OBGYN_table = $user_obj->getOBGYNTable();
							$stmt = $con->prepare("SELECT * FROM $OBGYN_table ORDER BY id DESC LIMIT 1");
							$stmt->execute();
							$OBGYN_info = mysqli_fetch_array($stmt->get_result());
						?>
					 	<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $appointment_id ?>" method="POST">
						 	<div class="form_area">
    						 	<h3><?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Add Medicines you Use";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Agrega medicamentos que uses";
    		        					        break;
    		        					}
    							?></h3>
    		
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "menarche"; ?>
									<p class="dashboard_tag">
										<?php 
		                        					switch($lang){
		                        					    case("en"):
		                        					        echo "Age of first menstrual period";
		                        					        break;
		                        					    case("es"):
		                        					        echo "Edad de la primera menstruación";
		                        					        break;
		                        					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											$session_string = "select_" . $current_box;
    											for($i=7;$i<=22;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif($_SESSION[$session_string] !== '' && isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otra </option>";
    											        break;
    											}
    											
    										?>
    									</select>
									</div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "lmp"; 
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                        					switch($lang){
		                        					    case("en"):
		                        					        echo "First day of last menstrual period (YYYY-MM-DD):";
		                        					        break;
		                        					    case("es"):
		                        					        echo "Fecha de primer dia de última regla (AAAA-MM-DD)";
		                        					        break;
		                        					}
		                					?>
									</p>
									<div class="dashboard_info" >
    									<input type="text" placeholder="Ex: <?php echo date('Y-m-d');?>" autocomplete="off" maxlength="10" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($OBGYN_info[$current_box] != '' && $OBGYN_info[$current_box] != '0000-00-00'){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											if($_SESSION[$session_string] !== ''){
    												echo $_SESSION[$session_string];
    											}
    											else{
    												echo date('Y') . '-';
    											}
    										}
    										else{
    											echo date('Y') . '-';
    										}
    										?>"
    									>
    								</div>
    									<?php
    										if(in_array($current_box,$women_error_array)){
    										    switch($lang){
    										        case("en"):
    										            echo "<div class='warning_dashboard'><b>Incorrect date format, insert as YYYY-MM-DD or leave blank</b></div>";
    										            break;
    										        case("es"):
    										            echo "<div class='warning_dashboard'><b>Formato de fecha incorrecto, inserta YYYY-MM-DD o deja en blanco</b></div>";
    										            break;
    										    }
    										}
    									?>
    								</div>
								
								<div class="dashboard_tag_block">
							 		<?php 
							 			$current_box = "cycles";
							 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                        					switch($lang){
		                        					    case("en"):
		                        					        echo "Cycle description:";
		                        					        break;
		                        					    case("es"):
		                        					        echo "Descripción de ciclos:";
		                        					        break;
		                        					}
		                					?>
									</p>
									<div class="dashboard_info" >
    									<input type="text" placeholder="<?php 
    		                        					switch($lang){
    		                        					    case("en"):
    		                        					        echo "Ex: irregular, every 27 days for 3 days, ...";
    		                        					        break;
    		                        					    case("es"):
    		                        					        echo "Ex: irregular, cada 27 días por 3 días, ...";
    		                        					        break;
    		                        					}
    		                					?>" autocomplete="off" maxlength="20" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($OBGYN_info[$current_box] != ''){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											if($_SESSION[$session_string] !== ''){
    												echo $_SESSION[$session_string];
    											}
    										}
    										
    										?>"
    									>
    									</div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "gestations";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 10;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Gestations (total pregnancies):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Gestaciones (número total de embarazos):";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    									</div>
    								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "parity";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 10;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Births (number of children previously born):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Cantidad de nacimientos:";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "abortions";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 10;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Abortions:";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Cantidad de abortos:";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "csections";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 10;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "C-Sections::";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Cantidad de cesáreas:";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>						
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "ectopic";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 3;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Ectopic pregnancies:";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Embarazos ectópicos:";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "menopause";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 30;
						 				$higher_value = 70;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Age of Final Menstruation (Menopause):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Edad de menstruación final (menopausia)";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >	
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='DNA'> Does not apply</option>";
    											        break;
    											    case("es"):
    											        echo "<option value='NA'> No aplica </option>";
    											        break;
    											}
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otra </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "birthcontrol";
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Birth Control (Method and/or Name):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Planificación (Método y/o nombre)";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_info" >	
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: Daily pill, Copper T";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Inyección trimestral, T de cobre";
    		                    					        break;
    		                    					}
    		                					?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> maxlength="30" value="<?php 
    										if($OBGYN_info[$current_box] != ''){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											echo $_SESSION[$session_string];
    										}
    										?>"
    									></div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "mammography_date";
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Last Mammography Date (YYYY-MM-DD):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Fecha de última mamografía (AAAA-MM-DD):";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_info" >	
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: 2017-06-14";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: 2017-06-14";
    		                    					        break;
    		                    					}
    		                					?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> maxlength="10" value="<?php 
    										if($OBGYN_info[$current_box] != '' && $OBGYN_info[$current_box] != '0000-00-00'){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											echo $_SESSION[$session_string];
    										}
    										?>"
    									></div>
									<?php 
										if(in_array($current_box,$women_error_array)){
										    switch($lang){
										        case("en"):
										            echo "<div class='warning_dashboard'><b>Incorrect date format, insert as YYYY-MM-DD</b></div>";
										            break;
										        case("es"):
										            echo "<div class='warning_dashboard'><b>Formato de fecha incorrecto, insertar como AAAA-MM-DD</b></div>";
										            break;
										    }
										}
									?>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "mammography_result";
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Last Mammography Results:";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Resultados de última mamografía:";
		                    					        break;
		                    					}
		                					?>
										
									</p>
									<div class="dashboard_info" >	
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: Negative, Abnormal";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Negativa, Anormal";
    		                    					        break;
    		                    					}
    		                					?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> maxlength="80" value="<?php 
    										if($OBGYN_info[$current_box] != ''){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											echo $_SESSION[$session_string];
    										}
    										?>"
    									></div>
								</div>
								
								<div class="three_button_navigation">
									<div class="left_3_button_navigation">
									</div>
									<div class="right_3_button_navigation">
									</div>
									<div class="center_3_button_navigation">
		
										<input type="submit" id="save_data_stats_butt" name="save_women_info" value="<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Save";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Guardar";
		                    					        break;
		                    					}
		                					?>">
		
									</div>
								</div>
								
							</div>
						</form>
					</div>	
				</div>	
	 		</div> <!-- end of the tabbed area -->
		</div>
	</div>
</div>			
	
	


		<div class="modal fade" id="reschedule" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
		
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"
							aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<div class="modal-title" id="myModalLabel"><h4>
						<?php 
						switch ($lang){
							
							case("en"):
								echo "Reschedule Appointment";
								break;
								
							case("es"):
								echo "Reprogramar Cita";
								break;
						}
						?></h4></div>
					</div>
		
					
		
					<div class="modal-body">
						<div class="modal-body-block">
							
							<div class="profile_calendar_block">
			
								<div class="year_selector">
								
									<select name="year_selected" id="year_selected" style="font-size: 13px;">
									<?php
									$drop_dwn_year = "";
									$year_lim = date ( 'Y', strtotime ( '+2 years' ) );
									
									$years_q = mysqli_query ( $con, "SELECT DISTINCT y FROM calendar_table WHERE y<$year_lim" );
									foreach ( $years_q as $arr ) {
										if ($current_year == $arr ['y']) {
											$drop_dwn_year = $drop_dwn_year . "<option selected='selected' value='" . $arr ['y'] . "'>" . $arr ['y'] . "</option>";
										} else {
											$drop_dwn_year = $drop_dwn_year . "<option value='" . $arr ['y'] . "'>" . $arr ['y'] . "</option>";
										}
									}
									echo $drop_dwn_year;
									?>
									</select>
								</div>
									<?php
									$curr_month_lang_query = mysqli_query ( $con, "SELECT $months_row_lang FROM months WHERE id='$current_month'" );
									$arr = mysqli_fetch_array ( $curr_month_lang_query );
									$curr_month_lang = $arr [$months_row_lang];
									?>
									<div class="month_selector">
    									<select name="month_selected" id="month_selected" style="font-size: 13px;">
    										<?php
    										$drop_dwn_month = "";
    										$query = mysqli_query ( $con, "SELECT $months_row_lang,id FROM months ORDER BY id ASC" );
    										foreach ( $query as $arr ) {
    											if ($curr_month_lang == $arr [$months_row_lang]) {
    												$drop_dwn_month = $drop_dwn_month . "<option selected='selected' value='" . $arr ['id'] . "'>" . $arr [$months_row_lang] . "</option>";
    											} else {
    												$drop_dwn_month = $drop_dwn_month . "<option value='" . $arr ['id'] . "'>" . $arr [$months_row_lang] . "</option>";
    											}
    										}
    										echo $drop_dwn_month;
    										?>
    									</select>
    								</div>			
			
								<div class='calendar_header_container'>
								<?php
								$days_str = "";
								$week_days_lang_query = mysqli_query ( $con, "SELECT $days_week_row_lang FROM days_week ORDER BY dw ASC " );
								
								foreach ( $week_days_lang_query as $value_day ) {
									$days_str = $days_str . "<div class='calendar_day_block' id='header_day'>" . $value_day [$days_week_row_lang] . "</div>";
								}
								echo $days_str;
								?>
							</div>
			
								<div class='calendar_container'>
									<!-- Contains the calendar once is loaded -->
			
								</div>
							</div>
						
			      	
			      		<div class="modal-body-info">
							<p><?php 
							switch ($lang){
								
								case("en"):
									echo "What type of appointment do you need?";
									break;
									
								case("es"):
									echo "¿Qué tipo de cita necesitas?";
									break;
							}
							?></p>
							<div id="appo_arrow">
    							<select name="appo_type" id="appo_type">
    			
    							<?php
    							$appo_duration_tab = $doctor_obj->getAppoDurationTable ();
    							$drop_dwn = "";
    							$query = mysqli_query ( $con, "SELECT appo_type,duration,id FROM $appo_duration_tab WHERE deleted = 0 ORDER BY appo_type ASC" );
    							foreach ( $query as $key => $arr ) {
    								if ($key == 0) {
    									$ini_appo_id = $txt_rep->entities ( $arr ['id'] );
    								}
    								$drop_dwn = $drop_dwn . "<option value='" . $txt_rep->entities ( $arr ['id'] ) . "'>" . $txt_rep->entities ( $arr ['appo_type'] ) . "</option>";
    							}
    							echo $drop_dwn;
    							?>
    			
    							</select>
    						</div>	
						</div>
						<div class="modal-body-info">
							<p><?php 
							switch ($lang){
								
								case("en"):
									echo "Select payment type:";
									break;
									
								case("es"):
									echo "Seleccionar tipo de pago";
									break;
							}
							?></p>
							
							<?php
							$user_insurance = $user_obj->getInsuranceCompany_Patient ();
							
							$insurances_tab = $user_obj->getInsurancesTable ();
							$stmt = $con->prepare ( "SELECT $lang FROM $insurances_tab WHERE id=?" );
							$stmt->bind_param ( "s", $user_insurance );
							$stmt->execute ();
							$_q_ins = $stmt->get_result ();
							$_arr_ins = mysqli_fetch_assoc ( $_q_ins );
							$user_insurance_name = $_arr_ins [$lang];
							
							// print_r($doctor_obj->getAvailableOfficesByInsurance($user_insurance));
							?>
							<div id="payment_arrow">
    							<select name="payment_type" id="payment_type">
    			
    							<?php
    							if ($user_insurance == '') {
    								switch($lang){
    									case "en":
    										echo "<option selected='selected' value='part'>Cash</option>";
    										break;
    									case "es":
    										echo "<option selected='selected' value='part'>Efectivo</option>";
    										break;
    								}
    								$payment_method = 'part';
    							} elseif ($user_insurance == $country . '00') {
    								switch($lang){
    									case "en":
    										echo "<option selected='selected' value='part'>Cash</option>";
    										break;
    									case "es":
    										echo "<option selected='selected' value='part'>Efectivo</option>";
    										break;
    								}
    								$payment_method = 'part';
    							} else {
    								switch($lang){
    									case "en":
    										echo "<option selected='selected' value='part'>Cash</option>";
    										break;
    									case "es":
    										echo "<option selected='selected' value='part'>Efectivo</option>";
    										break;
    								}
    								echo "<option selected='selected' value='" . $user_insurance . "'>" . $user_insurance_name . "</option>";
    								$payment_method = $user_insurance;
    							}
    							?>
    						
    							</select>
    						</div>	
						</div>
							
						<div class="info_icon" style ="top:15px;">
							<?php 
								switch ($lang){
										 		
									case("en"):
							?>
										i<span class="tip"> <u>Cash:</u> Select this if you do not have an insurance or do not intend to use it. You will pay directly to the doctor. <br> <u>Insurance:</u> If you have selected an insurance (selected in <i>Medical Info</i>), and this insurance is accepted by the doctor, this option will be enabled. Notice some insurance have some fees which you should pay directly to the doctor.</span>
							<?php 
								        break;
									
									case("es"):
							?>
										i<span class="tip"> <u>Efectivo:</u> Selecciona esta opción si no tienes seguro o no lo quieres usar. Deberás pagar el valor de la consulta directamente al doctor.<br> <u>Seguro:</u> Si seleccionaste un seguro (se selecciona en <i>Info. Médica</i>), y este seguro es aceptado por este doctor, esta opción será habilitada. Algunos seguros requieren el pago de un deducible que deberá ser pagado directamente al doctor.</span>
							<?php 
										break;
								}
							?>

						</div>
					</div>
						
						<div class="modal-body-block">
			
							<div id="day_container_div_profile">
								
								<?php
								echo " <iframe src='day_frame.php?d=" . $current_day . "&m=" . $current_month . "&y=" . $current_year . "&pm=" . $payment_method . "&po=" . bin2hex($doctor_username_e) . "&at=" . $ini_appo_id . "&aid=" . $appointment_id . "' id='day_iframe' frameborder='0' scrolling='no'>";
								?>								
								</iframe>
							</div>
						</div>
					</div>
		
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal"><?php 
							switch ($lang){
								
								case("en"):
									echo "Dismiss";
									break;
									
								case("es"):
									echo "Cerrar";
									break;
							}
							?></button>
					</div>
				</div>
			</div>
		</div> 
		<div class="modal fade" id="confirm_window" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
	
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">
		        <?php 
		        
		        switch ($lang){
		        	
		        	case("en"):
		        		echo "Confirm Appointment";
		        		break;
		        		
		        	case("es"):
		        		echo "Confirmar Cita";
		        		break;
		        }
		        
		        ?></h4>
		      </div>
	
		      <div class="modal-body">
		      <?php
		      	$pat_cal = $user_obj->getAppointmentsCalendar_Patient();
	
				$stmt = $con->prepare("SELECT * FROM $pat_cal WHERE consult_id = ? AND confirmed_pat = 0");
				$stmt->bind_param("s", $appointment_id);
				$stmt->execute();
				$verification_query_2 = $stmt->get_result();
				$num_aid = mysqli_num_rows($verification_query_2);
				
				if($num_aid == 1){	
				
			?>
		        <p>
        		    <?php 
		        
		        switch ($lang){
		        	
		        	case("en"):
		        		echo "Do you really wish to confirm this appointment?";
		        		break;
		        		
		        	case("es"):
		        		echo "¿Deseas confirmar esta cita?";
		        		break;
		        }
		        
		        ?>
		        
		        </p>
		      </div>
	     
		      <div class="modal-footer">	
		        	<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
		        
		        <a href="index.php">
		        		<button type="button" class="btn btn-primary" id="finish_button">
						<?php 
				        
				        switch ($lang){
				        	
				        	case("en"):
				        		echo "Yes, confirm!";
				        		break;
				        		
				        	case("es"):
				        		echo "¡Sí, confirmar!";
				        		break;
				        }
				        
				        ?>
					</button>
		      	</a>
		      </div>
			<?php 
				}
				else{
			?>
				<p>
					<?php 
			        
			        switch ($lang){
			        	
			        	case("en"):
			        		echo "This appointment has already been confirmed.";
			        		break;
			        		
			        	case("es"):
			        		echo "Esta cita ya fue confirmada.";
			        		break;
			        }
			        
			        ?>
		        
		        </p>
		      </div>
	
		      <div class="modal-footer">	
		        	<button type="button" class="btn btn-default" data-dismiss="modal">
					<?php 
			        
			        switch ($lang){
			        	
			        	case("en"):
			        		echo "Close";
			        		break;
			        		
			        	case("es"):
			        		echo "Cerrar";
			        		break;
			        }
			        
			        ?>
				</button> 
		      </div>
			<?php 
				}
			?>
		    </div>
		  </div>
		</div>
		
		<div class="modal fade" id="cancel_window" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
	
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">
        				<?php 
			        
			        switch ($lang){
			        	
			        	case("en"):
			        		echo "Cancel Appointment";
			        		break;
			        		
			        	case("es"):
			        		echo "Cancelar Cita";
			        		break;
			        }
			        
			        ?>
				</h4>
		      </div>
	
		      <div class="modal-body">
		        <p>
                		<?php 
			        
			        switch ($lang){
			        	
			        	case("en"):
			        		echo "Do you really wish to cancel this appointment? <br> The symptoms you saved for this appointments will be <b>deleted</b> and the time-slot will be cleared on both yours and the doctor's calendar.";
			        		break;
			        		
			        	case("es"):
			        		echo "¿Deseas cancelar esta cita? <br> Los síntomas que guardaste para esta cita serán <b>eliminados</b> y el horario escogido será liberado en tu calendario y el del doctor.";
			        		break;
			        }
			        
			        ?>
		        </p>
		      </div>
		      <div class="modal-footer">
		       
    		        <a href="index.php">
    		        		<button type="button" class="btn btn-default" id="cancel_button">
            		        <?php 
    			        
    			        switch ($lang){
    			        	
    			        	case("en"):
    			        		echo "Yes, cancel";
    			        		break;
    			        		
    			        	case("es"):
    			        		echo "Sí, cancelar";
    			        		break;
    			        }
    			        
    			        ?>
    			        </button>
    		      	</a>
		        	<button type="button" class="btn btn-primary" data-dismiss="modal">No</button>
	
		       
			</div>
		      
		      </div>
		    </div>
		  </div>
			
	</body>
	
	<div class="modal fade" id="login-modal" tabindex="-1"
		role="dialog" aria-labelledby="postModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
	
				<div class="modal-header">
					<h4 class="modal-title" id="myModalLabel">
						<?php
							switch ($lang){
								
								case("en"):
									echo "Login";
									break;
									
								case("es"):
									echo "Iniciar Sesión";
									break;
							}
						?>
	      			</h4>
				</div>
				<div class="modal-body">
					<form action="<?php echo 'patient_appointment_viewer.php?cid=' . $temp_cid .
					"&s=" . $temp_signup_enc_hex . "&u=" . $temp_username_enc_hex .
					"&d=" . $temp_key_enc_hex;?>" method="POST">
				
						<input type="email" name="log_email" placeholder="<?php 
						switch ($lang){
							
							case("en"):
								echo "Email Adress";
								break;
								
							case("es"):
								echo "Correo";
								break;
						}?>"
							value="<?php
								if (isset ( $_SESSION ['log_email'] )) {
									echo $txt_rep->entities ( $_SESSION ['log_email'] );
								}
								?>"
							required style="color:black;"> <input type="password" name="log_passwrd"
							placeholder="<?php 
								switch ($lang){
									
									case("en"):
										echo "Password";
										break;
										
									case("es"):
										echo "Contraseña";
										break;
								}?>" style="color:black;"> <input type="hidden" name="search_true"
								value="1">
							<p id="incorrect" style=" color: red;">
	                       <?php if(in_array("Incorect Email or Password.<br>", $login_error_array)){
			                       	switch ($lang){
			                       		
			                       		case("en"):
			                       			echo "Incorect Email or Password.<br>";
			                       			break;
			                       			
			                       		case("es"):
			                       			echo "Correo o contraseña incorrecta.<br>";
			                       			break;
			                       	}
	                       			 
								}?>
	                    		</p>
							<div style="display: inline-block; width: 100%;">
							<?php 
								switch ($lang){
										 		
									case("en"):
							?>
										<input type="submit" name="login_button" value="Login"
											style="background-color: #f95c8b; border: 1px solid #f95c8b; clear: none; float: left;">
							<?php 
								        break;
									
									case("es"):
							?>
										<input type="submit" name="login_button" value="Iniciar Sesión"
											style="background-color: #f95c8b; border: 1px solid #f95c8b; clear: none; float: left;">
							<?php 
										break;
								}
							?>
	
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	
	<script>
	
	//PROFILEUSERNAME is the doctor
	var profileUsername = '<?php echo bin2hex($txt_rep->entities($doctor_username_e)); ?>'; //profile we are on

	var curr_day = '<?php echo $txt_rep->entities($current_day); ?>';
	var curr_month = '<?php echo $txt_rep->entities($current_month); ?>';
	var curr_year = '<?php echo $txt_rep->entities($current_year); ?>';
	var payment_method = '<?php echo $txt_rep->entities($payment_method); ?>';

	var selected_appo_id = $("#appo_type").find(":selected").val();
	
	$(document).ready(function(){


		$('#profileTabs > li'). css('width', 'calc(100% / 2)');
		$('#personalInfoTabs > li'). css('width', 'calc(100% / 3)');
    	for ( var i=4; i<= $('.nav-tabs > li').length ; i++){
        	$('.nav-tabs > li:nth-child('+i+')'). css('width', 'calc(100% / 4)');
    	}

    

		$("#year_selected").change(function(){
			getProfileCalendar();
		});

		$("#month_selected").change(function(){
			getProfileCalendar();
		});
	
		//Appointment duration change
		$("#appo_type").change(function(){
			appointment_id = '<?php echo $appointment_id; ?>';
			selected_appo_id = $("#appo_type").find(":selected").val();
			payment_method = $("#payment_type").find(":selected").val();
						
			var month = $("#month_selected").find(":selected").val();
			var year = $("#year_selected").find(":selected").val();
			if ($('#selected_day_inp').val() != "")
				var day = $('#selected_day_inp').val();
			else
				var day = curr_day;
	

			//profile we are on
			var ajaxreq = $.ajax({
				url: "includes/handlers/ajax_profile_calendar.php",
				type: "POST",
				data: "profile_user=" + profileUsername + "&month=" + month + "&year=" + year + "&payment_method=" + payment_method + "&appo_type_id=" + selected_appo_id,
				cache: false,
	
				success: function(response){
					$(".calendar_container").html(response);
				}
			});
	
		//Day selection change
	
			$("#day_iframe").attr('src', "day_frame.php?d=" + day + "&m=" + month + "&y=" + year + "&pm=" + payment_method + "&po=" + profileUsername + "&at=" + selected_appo_id + "&aid=" + appointment_id);
	
		});

		//payment type change
		$("#payment_type").change(function(){
			appointment_id = '<?php echo $appointment_id; ?>';
			selected_appo_id = $("#appo_type").find(":selected").val();
			payment_method = $("#payment_type").find(":selected").val();
			
			var month = $("#month_selected").find(":selected").val();
			var year = $("#year_selected").find(":selected").val();
			if ($('#selected_day_inp').val() != "")
				var day = $('#selected_day_inp').val();
			else
				var day = curr_day;

			var ajaxreq = $.ajax({
				url: "includes/handlers/ajax_profile_calendar.php",
				type: "POST",
				data: "profile_user=" + profileUsername + "&month=" + month + "&year=" + year + "&payment_method=" + payment_method + "&appo_type_id=" + selected_appo_id,
				cache: false,

				success: function(response){
					$(".calendar_container").html(response);
				}
			});

		//Day selection change

			$("#day_iframe").attr('src', "day_frame.php?d=" + day + "&m=" + month + "&y=" + year + "&pm=" + payment_method + "&po=" + profileUsername + "&at=" + selected_appo_id + "&aid=" + appointment_id);
				
		 
		   
		});
	
		var ajaxprofcal = $.ajax({
			url: "includes/handlers/ajax_profile_calendar.php",
			type: "POST",
			data: "profile_user=" + profileUsername + "&month=" + curr_month + "&year=" + curr_year + "&payment_method=" + payment_method + "&appo_type_id=" + selected_appo_id,
			cache: false,
	
			success: function(response){
				$(".calendar_container").html(response);
			}
		});
	
	});

</script>
</html>