<?php
include('includes/header2.php');

//Check if it is a legal DOCTOR user and appointment

if(isset($_GET['cid'])){
	$appoinments_calendar_tab = $user_obj->getAppointmentsCalendar();
	$appoinments_details_tab = $user_obj->getAppointmentsDetails_Doctor();
	
	$temp_id = $_GET['cid'];
	
	$stmt = $con->prepare("SELECT * FROM $appoinments_details_tab WHERE consult_id=? AND doctor_username=?");
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
		$patient_username = $details['patient_username'];
		$patient_username_e = $crypt->EncryptU($patient_username);
		$patient_obj = new User($con, $patient_username, $patient_username_e);
		$calendar = $temp_calendar;
		
		$appointment_obj = new Appointments_Master($con, $userLoggedIn, $userLoggedIn_e);
		
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
		//Graph settings
		$num_points = 10;
		
	}
	else{
		$details = "";
		$appointment_id = "";
		$patient_username = "";
		$patient_obj = NULL;
		$calendar = [];
		
		$appointment_obj = NULL;
		header('Location: index.php');
	}
}
else{
	header('Location: index.php');
}


if(isset($_POST['notes'])){
	$notes_to_save = $_POST['notes'];
	$appo_det_doc = $user_obj->getAppointmentsDetails_Doctor();
	$stmt = $con->prepare("UPDATE $appo_det_doc SET notes = ? WHERE consult_id=?");
	$stmt->bind_param("ss",$notes_to_save,$appointment_id);
	$stmt->execute();
	exit;
}

if(isset($_POST['perform_change_type'])){
	$appo_type_id = $_POST['appo_type'];
	$appointment_obj->changeAppoType($appointment_id,$user_obj,$patient_obj,$appo_type_id);
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
</div>
<div class="top_banner_title_text_container">
	<?php 
		switch ($lang){
				 		
			case("en"):
	?>
				<h1>Appointment Dashboard</h1>
	<?php 
		        break;
			
			case("es"):
	?>
				<h1>Registro de Consulta</h1>
	<?php 
				break;
		}
	?>
		<h2>
	<?php 
	switch ($lang){
		
		case("en"):
			echo "useful data for your next appointment  ";
			break;
			
		case("es"):
			echo "información útil previa a la consulta ";
			break;
	}
	?>
	</h2>
	
</div>

<div class="wrapper">
	<div class="profile_left" style="width: 20%;">
		
			<h2 style="padding-top:15px;">
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						Appointment Select
			<?php 
				        break;
					
					case("es"):
			?>
						Consultas
			<?php 
						break;
				}
			?>
			</h2>
		<div class="appointment_changer_window">
			<div class="prev_next_appointment_changer_window" id="prev_next_left_window">
		<!-- 				<div class="appo_changer_butt" id="appo_changer_butt_left">&#10092;<br><span style="font-size:10px;">Prev.</span></div> -->
		<!-- 				<div class="pic_name_container"> -->
				<?php 
					//Prev appointment info extraction
					$prev_next = $appointment_obj->getDoctorViewPrevNextAppo_id_usern($appointment_id);
					if($prev_next['aid'][0] != 404){
						$prev_aid = $prev_next['aid'][0];
						$prev_id = $prev_next['id'][0];
						//$prev_appo_obj = new Appointments_Master($con, $prev_id);
						
						try{
						    $prev_id_e = $crypt->EncryptU($prev_id);
							$prev_patient_obj = new User($con, $prev_id, $prev_id_e);
							$prof_pic = $txt_rep->entities($prev_patient_obj->getProfilePicFast());
							$link = 'doctor_appointment_viewer.php?cid=' . $prev_aid;
						}
						catch ( Exception $e ){
							$prof_pic = $txt_rep->entities("assets/images/profile_pics/defaults/default_non_registered.png");
							$link = 'ext_pat_appo_viewer.php?cid=' . $prev_aid;
						}
						
						$prev_appo_date_time_arr = $appointment_obj->getAppointmentTimeDate($prev_aid, 2);
						$stmt = $con->prepare("SELECT $months_lang FROM months WHERE id=?");
						$stmt->bind_param("s",$prev_appo_date_time_arr['month']);
						$stmt->execute();
						$month_correct = mysqli_fetch_array($stmt->get_result())[$months_lang];
						$prev_appo_date = $prev_appo_date_time_arr['day'] . "/" . substr($month_correct,0,3) . "/" . substr($prev_appo_date_time_arr['year'],2) ;
						
						//$time_arr = $prev_appo_obj->getAppointmentTimeDate($prev_aid, 1);
						$time_p = new DateTime($prev_appo_date_time_arr['time_start']);
						echo '<a href="' .$link. '"><div class="appo_changer_butt" id="appo_changer_butt_left">&#10092;<br>
                        <!-- <span style="font-size:10px;">Prev.</span> -->
                        </div></a>
						<a href="' .$link. '"><div class="pic_name_container">';
						echo '<img class="prev_next_square_img" src="' . $prof_pic . '"></a>';
						echo '<p style="text-align: center; line-height: 10px;">' . $time_p->format("g:ia") . '<br>
							<span style="font-size:8px;"> ' . $prev_appo_date . ' </span></p>';
					}
					else{
						echo '<div class="pic_name_container">';
					}
				?>
				</div>
			</div>
			<div class="prev_next_appointment_changer_window" id="prev_next_right_window">
					<?php 
						//Prev appointment info extraction
						//$prev_next = $appointment_obj->getDoctorViewPrevNextAppo_id_usern($appointment_id);
						
						if($prev_next['aid'][1] != 404){
							$next_aid = $prev_next['aid'][1];
							$next_id = $prev_next['id'][1];
							
							try{
							    $next_id_e = $crypt->EncryptU($next_id);
								$next_patient_obj = new User($con, $next_id, $next_id_e);
								$prof_pic = $txt_rep->entities($next_patient_obj->getProfilePicFast());
								$link = 'doctor_appointment_viewer.php?cid=' . $next_aid;
							}
							catch ( Exception $e ){
								$prof_pic = $txt_rep->entities("assets/images/profile_pics/defaults/default_non_registered.png");
								$link = 'ext_pat_appo_viewer.php?cid=' . $next_aid;
							}

							$next_appo_date_time_arr = $appointment_obj->getAppointmentTimeDate($next_aid, 2);
							$stmt = $con->prepare("SELECT $months_lang FROM months WHERE id=?");
							$stmt->bind_param("s",$next_appo_date_time_arr['month']);
							$stmt->execute();
							$month_correct = mysqli_fetch_array($stmt->get_result())[$months_lang];
							$prev_appo_date = $next_appo_date_time_arr['day'] . "/" . substr($month_correct,0,3) . "/" . substr($next_appo_date_time_arr['year'],2) ;
							
							//$time_arr = $next_appo_obj->getAppointmentTimeDate($next_aid, 1);
							$time_p = new DateTime($next_appo_date_time_arr['time_start']);
							echo '<div class="pic_name_container">';
							echo '<a href="' .$link. '"><img class="prev_next_square_img" src="' . $prof_pic. '"></a>';
							//echo '<p style="text-align: center;">' . $time_p->format("g:ia") . '</p>';
							echo '<p style="text-align: center; line-height: 10px;">' . $time_p->format("g:ia") . '<br>
							<span style="font-size:8px;"> ' . $prev_appo_date . ' </span></p>';
							echo '</div>
								<a href="' .$link. '">
									<div class="appo_changer_butt" id="appo_changer_butt_right">&#10093;<br>
                                <!-- <span style="font-size:10px;">Next</span> -->
                                </div>
								</a>';
						}
						else{
						    echo '<div class="pic_name_container">';
						    echo '</div>';
						}
					?>
<!-- 				</div> -->
<!-- 				<div class="appo_changer_butt" id="appo_changer_butt_left">&#10093;<br><span style="font-size:10px;">Next</span></div> -->
			</div>
			<div class="current_appointment_changer_window">
				<?php
					//Current Appointment Info Extraction:
					echo '<img class="prev_next_curr_square_img" src="' . $txt_rep->entities($patient_obj->getProfilePicFast()) . '">';
					$appo_date_time_arr = $appointment_obj->getAppointmentTimeDate($appointment_id, 2);
					$curr_appo_time	= new DateTime($appo_date_time_arr['time_start']);
					echo '<p style="text-align: center; font-size: 20px;">' . $curr_appo_time->format("g:ia") . '<br></p>';
				?>
			</div>
			<div class="date_appointment_changer_window">			
        		<?php
        			$stmt = $con->prepare("SELECT $months_lang FROM months WHERE id=?");
        			$stmt->bind_param("s",$appo_date_time_arr['month']);
        			$stmt->execute();
        			$month_correct = mysqli_fetch_array($stmt->get_result())[$months_lang];
        			echo $appo_date_time_arr['day'] . " / " . $month_correct . " / " . $appo_date_time_arr['year'];
        		?>
		</div>
		</div>
		
		<div class="date_appointment_changer_name">
			<?php 
				echo "<p>" . $txt_rep->entities($patient_obj->getFirstAndLastNameFast()) . "<br>";
				
				$stmt = $con->prepare("SELECT * FROM basic_info_patients WHERE username=?");
				$stmt->bind_param("s",$patient_username);
				$stmt->execute();
				$basic_info = mysqli_fetch_array($stmt->get_result());
				
				if($basic_info['phone_numbers'] != NULL)
					echo "<span style='font-size:10px'>" . $txt_rep->entities($basic_info['phone_numbers']) . "</span></p>";
			?>
		</div>
		<div class="dashboard_left_health">
    		<div class="dashboard_container_1" style=" height: auto;">
    			<h2>
    			<?php 
    				switch ($lang){
    						 		
    					case("en"):
    			?>
    						Appointment Type:
    			<?php 
    				        break;
    					
    					case("es"):
    			?>
    						Tipo de Cita:
    			<?php 
    						break;
    				}
    			?>
    			</h2>
    			<form class="reg_form" action="<?php echo 'doctor_appointment_viewer.php?cid=' . $appointment_id ; ?>" method="POST">
        				<select name = "appo_type" id = "appo_type">
        	
        					<?php
        						$selected_appo_type_id = $appointment_obj->viewAppoTypeId_Doctor($appointment_id);
        						
        						$appo_duration_tab = $user_obj->getAppoDurationTable();
        						$drop_dwn = "";
        						$query = mysqli_query($con, "SELECT id,appo_type,duration FROM $appo_duration_tab WHERE deleted = 0 ORDER BY appo_type ASC");
        						foreach($query as $key => $arr){
        							if($selected_appo_type_id == $arr['id']){
        								$drop_dwn = $drop_dwn . "<option value='" . $arr['id'] . "' selected>" . $txt_rep->entities($arr['appo_type']) . "</option>";
        							}
        							else{
        								$drop_dwn = $drop_dwn . "<option value='" . $arr['id'] . "'>" . $txt_rep->entities($arr['appo_type']) . "</option>";
        							}
        						}
        						echo $drop_dwn;
        					?>
        	
        				</select>
        				<?php 
        					switch ($lang){
        							 		
        						case("en"):
        				?>
        							<input type="submit" name="perform_change_type" id="perform_change_type" value="Confirm" >
        				<?php 
        					        break;
        						
        						case("es"):
        				?>
        							<input type="submit" name="perform_change_type" id="perform_change_type" value="Confirmar">
        				<?php 
        							break;
        					}
        				?>
        	
    				
    			</form>	
    			<a id="edit_appo_butt" href="" data-toggle="modal" data-target="#confirm_appo_change_window">
    				
    				<?php 
    					switch ($lang){
    							 		
    						case("en"):
    				?>
    							Edit
    				<?php 
    					        break;
    						
    						case("es"):
    				?>
    							Editar
    				<?php 
    							break;
    					}
    				?>
    			</a>
    			<hr style="margin-top:10px; margin-bottom:10px;">
    		</div>
    		<div class="dashboard_container_1">
        			<div class="dashboard_block_1 dashboard_left">
        			<?php				
        				$stmt = $con->prepare("SELECT $lang FROM sex WHERE id=?");
        				$sex_raw = $basic_info['sex'];
        				$stmt->bind_param("s",$sex_raw);
        				$stmt->execute();
        				$sex = mysqli_fetch_array($stmt->get_result())[$lang];
        				switch ($lang){
        					
        					case("en"):
        						echo "Sex: " . $sex;
        						break;
        						
        					case("es"):
        						echo "Sexo: " . $sex;
        						break;
        				}
        				
        			?>
        			</div>
        			<div class="dashboard_block_1 dashboard_right" >
        			<?php 
        				$bdate = new DateTime($basic_info['birthdate']);
        				$today = new DateTime();
        				$interval = $bdate->diff($today);
        				switch ($lang){
        					
        					case("en"):
        						echo "Age: " . $interval->format("%y");
        						break;
        						
        					case("es"):
        						echo "Edad: " . $interval->format("%y");
        						break;
        				}
        				
        			?>
        			</div>
        		</div>
        		<div class="dashboard_container_1">
        			<div class="dashboard_block_1 dashboard_left">
        				<?php
        
        					switch ($lang){
        						
        						case("en"):
        							if($basic_info['birthplace'] != NULL)
        								echo "Birthplace: <br>" . $txt_rep->entities($basic_info['birthplace']);
        							else
        								echo "No listed birthplace.";
        							break;
        							
        						case("es"):
        							if($basic_info['birthplace'] != NULL)
        								echo "Lugar de Nacimiento: <br>" . $txt_rep->entities($basic_info['birthplace']);
        							else
        								echo "Lugar de nacimiento no registrado.";
        							break;
        					}
        				?>
        			</div>
        			<div class="dashboard_block_1 dashboard_right">
        				<?php
        					switch ($lang){
        						
        						case("es"):
        							if($basic_info['current_residence'] != NULL)
        								echo "Lugar Residencia: <br>" . $txt_rep->entities($basic_info['current_residence']);
        							else
        								echo "Lugar residencia no registrado.";
        							break;
        									
        						case("en"):
        							if($basic_info['current_residence'] != NULL)
        								echo "Place of Residence: <br>" . $txt_rep->entities($basic_info['current_residence']);
        							else
        								echo "No listed place of residence.";
        							break;
        					}
        				?>
        			</div>
        		</div>
        		<div id="dashboard_left_column_health_info">
        			<div class="dashboard_tag_block">
        				<p class="dashboard_tag">
        					
        					<?php 
        						switch ($lang){
        								 		
        							case("en"):
        					?>
        								Marital Status
        					<?php 
        						        break;
        							
        							case("es"):
        					?>
        								Estado Civil
        					<?php 
        								break;
        						}
        					?>
        				</p>
        				<div class="dashboard_block_2">
        					<?php
        						if($basic_info['marital_status'] != NULL){
        							$stmt = $con->prepare("SELECT $lang FROM marital_status WHERE id=?");
        							$raw = $basic_info['marital_status'];
        							$stmt->bind_param("s",$raw);
        							$stmt->execute();
        							$processed = mysqli_fetch_array($stmt->get_result())[$lang];
        							echo $processed;
        						}
        						else{
        							switch ($lang){
        								
        								case("en"):
        									echo "NA";
        									break;
        									
        								case("es"):
        									echo "NR";
        									break;
        							}
        						}
        					?>
        				</div>
        			</div>
        			
        			<div class="dashboard_tag_block">
        				<p class="dashboard_tag">
        					
        					<?php 
        						switch ($lang){
        								 		
        							case("en"):
        					?>
        								Children
        					<?php 
        						        break;
        							
        							case("es"):
        					?>
        								Hijos
        					<?php 
        								break;
        						}
        					?>
        				</p>
        				<div class="dashboard_block_2">
        					<?php
        						if($basic_info['children'] !== NULL)
        							echo $txt_rep->entities($basic_info['children']);
        						else{
        							switch ($lang){
        								
        								case("en"):
        									echo "NA";
        									break;
        									
        								case("es"):
        									echo "NR";
        									break;
        							}
        						}
        							
        					?>
        				</div>
        			</div>
        			
        			<div class="dashboard_tag_block">
        				<p class="dashboard_tag">
        					<?php 
        						switch ($lang){
        								 		
        							case("en"):
        					?>
        								Education Level
        					<?php 
        						        break;
        							
        							case("es"):
        					?>
        								Nivel de Educación
        					<?php 
        								break;
        						}
        					?>
        					
        				</p>
        				<div class="dashboard_block_2">
        					<?php
        						if($basic_info['education_level'] != NULL){
        							$stmt = $con->prepare("SELECT $lang FROM education_level WHERE id=?");
        							$raw = $basic_info['education_level'];
        							$stmt->bind_param("s",$raw);
        							$stmt->execute();
        							$processed = mysqli_fetch_array($stmt->get_result())[$lang];
        							echo $processed;
        						}
        						else{
        							switch ($lang){
        								
        								case("en"):
        									echo "NA";
        									break;
        									
        								case("es"):
        									echo "NR";
        									break;
        							}
        						}
        					?>
        				</div>
        			</div>
        			
        			<div class="dashboard_tag_block">
        				<p class="dashboard_tag">
        					<?php 
        						switch ($lang){
        								 		
        							case("en"):
        					?>
        								Occupation
        					<?php 
        						        break;
        							
        							case("es"):
        					?>
        								Ocupación
        					<?php 
        								break;
        						}
        					?>
        					
        				</p>
        				<div class="dashboard_block_2">
        					<?php
        						if($basic_info['occupation'] != NULL)
        							echo $txt_rep->entities($basic_info['occupation']);
        						else{
        							switch ($lang){
        								
        								case("en"):
        									echo "NA";
        									break;
        									
        								case("es"):
        									echo "NR";
        									break;
        							}
        						}
        					?>
        				</div>
        			</div>
        			
        			<div class="dashboard_tag_block">
        				<p class="dashboard_tag">
        					<?php 
        						switch ($lang){
        								 		
        							case("en"):
        					?>
        								Religion
        					<?php 
        						        break;
        							
        							case("es"):
        					?>
        								Religión
        					<?php 
        								break;
        						}
        					?>
        					
        				</p>
        				<div class="dashboard_block_2">
        					<?php
        						if($basic_info['religion'] != NULL)
        							echo $txt_rep->entities($basic_info['religion']);
        						else{
        							switch ($lang){
        								
        								case("en"):
        									echo "NA";
        									break;
        									
        								case("es"):
        									echo "NR";
        									break;
        							}
        						}
        					?>
        				</div>
        			</div>
        			
        			<div class="dashboard_tag_block">
        				<p class="dashboard_tag">
        					<?php 
        						switch ($lang){
        								 		
        							case("en"):
        					?>
        								Languages
        					<?php 
        						        break;
        							
        							case("es"):
        					?>
        								Idiomas
        					<?php 
        								break;
        						}
        					?>
        					
        				</p>
        				<div class="dashboard_block_2">
        					<?php
        						if($basic_info['languages'] != NULL)
        							echo $txt_rep->entities($basic_info['languages']);
        						else{
        							switch ($lang){
        								
        								case("en"):
        									echo "NA";
        									break;
        									
        								case("es"):
        									echo "NR";
        									break;
        							}
        						}
        					?>
        				</div>
        			</div>
        			
        			<div class="dashboard_tag_block">
        				<p class="dashboard_tag">
        					<?php 
        						switch ($lang){
        								 		
        							case("en"):
        					?>
        								Laterality
        					<?php 
        						        break;
        							
        							case("es"):
        					?>
        								Lateralidad
        					<?php 
        								break;
        						}
        					?>
        					
        				</p>
        				<div class="dashboard_block_2">
        					<?php
        						if($basic_info['laterality'] != NULL){
        							$stmt = $con->prepare("SELECT $lang FROM laterality WHERE id=?");
        							$raw = $basic_info['laterality'];
        							$stmt->bind_param("s",$raw);
        							$stmt->execute();
        							$processed = mysqli_fetch_array($stmt->get_result())[$lang];
        							echo $processed;
        						}
        						else{
        							switch ($lang){
        								
        								case("en"):
        									echo "NA";
        									break;
        									
        								case("es"):
        									echo "NR";
        									break;
        							}
        						}
        					?>
        				</div>
        			</div>
        			
        			<div class="dashboard_tag_block">
        				<p class="dashboard_tag" style="font-size: 10px;">
        					<?php 
        						switch ($lang){
        								 		
        							case("en"):
        					?>
        								Insurance
        					<?php 
        						        break;
        							
        							case("es"):
        					?>
        								Prepagada / Seguro / EPS
        					<?php 
        								break;
        						}
        					?>
        
        				</p>
        				<div class="dashboard_block_2">
        					<?php
        						if($basic_info['insurance'] != NULL){
        							$stmt = $con->prepare("SELECT $lang FROM insurance_CO WHERE id=?");
        							$raw = $basic_info['insurance'];
        							$stmt->bind_param("s",$raw);
        							$stmt->execute();
        							$processed = mysqli_fetch_array($stmt->get_result())[$lang];
        							echo $processed;
        						}
        						else{
        							switch ($lang){
        								
        								case("en"):
        									echo "NA";
        									break;
        									
        								case("es"):
        									echo "NR";
        									break;
        							}
        						}
        					?>
        				</div>
            			</div>
    			
    		</div>
    	</div>
    </div>	

	<div class="right_column column">
		<?php 
			switch ($lang){
					 		
				case("en"):
		?>
					<h2 style="margin-top: 0px;">Current Consult</h2>		
					
		<?php 
			        break;
				
				case("es"):
		?>
					<h2 style="margin-top: 0px;">Consulta Actual</h2>		
					
		<?php 
					break;
			}
		?>

		<form class="plan_form" action="" method="POST">
		<?php 
			switch ($lang){
					 		
				case("en"):
		?>
							
					<h3 style=" display: inline-block; width: 45%; float: left;">Plan</h3>
					<p id="plan_not_saved">(Not Saved Yet)</p>
		<?php 
			        break;
				
				case("es"):
		?>
					
					<h3 style=" display: inline-block; width: 45%; float: left;">Plan</h3>
					<p id="plan_not_saved">(Sin Guardar)</p>
		<?php 
					break;
			}
		?>
			<?php 
				$appo_det_doc = $user_obj->getAppointmentsDetails_Doctor();
				$stmt = $con->prepare("SELECT plan,closed,notes FROM $appo_det_doc WHERE consult_id=?");
				$stmt->bind_param("s",$appointment_id);
				$stmt->execute();
				$plan_q = $stmt->get_result();
				$plan_arr = mysqli_fetch_assoc($plan_q);
				$plan = $plan_arr['plan'];
				$plan_issued = $plan_arr['closed'];
				//TODO:THE PLAN IS EDITABLE, if you do not want this, uncomment the next line and errase the 1.
				$closed_plan = 0;//$plan_arr['closed'];<---- This makes the plan uneditable after issued. Also uncomment in file includes/handlers/ajax_confirm_plan.php the line 19 and 27
			?>
			<input type="hidden" id="doctor_username" name="doctor_username" value="<?php echo bin2hex($userLoggedIn_e); ?>">
			<input type="hidden" id="patient_username" name="patient_username" value="<?php echo bin2hex($patient_username_e); ?>">
			<input type="hidden" id="aid" name="aid" value="<?php echo $appointment_id; ?>">
			<textarea name="post_plan" id="post_plan" onkeyup="unsavedPlan()" placeholder="<?php 
			switch ($lang){
				
				case("en"):
					echo "Write for the patient any requested lab exams, prescriptions, recomend a future visit, etc. The patient will be able to see this after the appointment.";
					break;
					
				case("es"):
					echo "Escribe para el paciente las recetas, exámenes de laboratorio, próximas visitas, etc. El paciente lo podrá ver después de su cita.";;
					break;
			}
			?>" <?php if($closed_plan){echo 'disabled="disabled"';} ?>><?php 
				echo $plan;
			?></textarea>
			<?php 
				$patient_det_tab = $patient_obj->getAppointmentsDetails_Patient();
				$stmt = $con->prepare("SELECT private_plan FROM $patient_det_tab WHERE consult_id=?");
				$stmt->bind_param("s",$appointment_id);
				$stmt->execute();
				$privacy_q = $stmt->get_result();
				$privacy_arr = mysqli_fetch_assoc($privacy_q);
				$privacy = $privacy_arr['private_plan'];
				
				if($privacy){
					echo '<input type="hidden" id="plan_restriction" name="plan_restriction" value="1">';
					switch ($lang){
						
						case("en"):
							echo '<p style=" display: inline-block;"><b>Privacy:</b> ';
							break;
							
						case("es"):
							echo '<p style=" display: inline-block;"><b>Privacidad:</b> ';
							break;
					}
					
					if(!$closed_plan){
						echo '<a href="javascript:void(0);" onclick="changePrivacyPlan()">'; 
					}
					
					switch ($lang){
						
						case("en"):
							echo '<span id="restriction_p" style=" color: red;">restricted</span>';
							break;
							
						case("es"):
							echo '<span id="restriction_p" style=" color: red;">restringido</span>';
							break;
					}
					
					if(!$closed_plan){
						echo '</a>';
					}
					
					switch ($lang){
						
						case("en"):
							echo '</p> <div class="info_icon">i<span class="tip"> <u>Unrestricted:</u> Plan will be visible ONLY by other doctors that treat this patient, by the patient, and by you. <br> <u>Restricted:</u> Plan will ONLY be visible by the patient and by you.</span></div>';
							break;
							
						case("es"):
							echo '</p> <div class="info_icon">i<span class="tip"> <u>Abierto:</u> El plan será visible SÓLO por otros doctores que traten al paciente, por el paciente y por ti. <br> <u>Restringido:</u> El plan será visible SÓlO por el paciente y por ti.</span></div>';
							break;
					}
					
				}
				else{
					echo '<input type="hidden" id="plan_restriction" name="plan_restriction" value="0">';
					switch ($lang){
						
						case("en"):
							echo '<p style=" display: inline-block;"><b>Privacy:</b> ';
							break;
							
						case("es"):
							echo '<p style=" display: inline-block;"><b>Privacidad:</b> ';
							break;
					}
					
					if(!$closed_plan){
						echo '<a href="javascript:void(0);" onclick="changePrivacyPlan()">';
					}
					
					switch ($lang){
						
						case("en"):
							echo '<span id="restriction_p" style=" color: blue;">unrestricted</span>';
							break;
							
						case("es"):
							echo '<span id="restriction_p" style=" color: blue;">abierto</span>';
							break;
					}
					
					
					if(!$closed_plan){
						echo '</a>';
					}
					
					switch ($lang){
						
						case("en"):
							echo '</p> <div class="info_icon">i<span class="tip"> <u>Unrestricted:</u> Plan will be visible ONLY by other doctors that treat this patient, by the patient, and by you. <br> <u>Restricted:</u> Plan will ONLY be visible by the patient and by you.</span></div>';
							break;
							
						case("es"):
							echo '</p> <div class="info_icon">i<span class="tip"> <u>Abierto:</u> El plan será visible SÓLO por otros doctores que traten al paciente, por el paciente y por ti. <br> <u>Restringido:</u> El plan será visible SÓlO por el paciente y por ti.</span></div>';
							break;
					}
					
					
				}
			?>
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						<script>
							function changePrivacyPlan(){
								var restricted = $("#plan_restriction").val();
								if(restricted == 1){
									$("#plan_restriction").val("0");
									$("#restriction_p").html("unrestricted");
									$("#restriction_p").css({"color":"blue"});
								}
								else if(restricted == 0){
									$("#plan_restriction").val("1");
									$("#restriction_p").html("restricted");
									$("#restriction_p").css({"color":"red"});
								}
							}
						</script>
			<?php 
				        break;
					
					case("es"):
			?>
						<script>
							function changePrivacyPlan(){
								var restricted = $("#plan_restriction").val();
								if(restricted == 1){
									$("#plan_restriction").val("0");
									$("#restriction_p").html("abierto");
									$("#restriction_p").css({"color":"blue"});
								}
								else if(restricted == 0){
									$("#plan_restriction").val("1");
									$("#restriction_p").html("restringido");
									$("#restriction_p").css({"color":"red"});
								}
							}
						</script>
			<?php 
						break;
				}
			?>

			
			<?php 
			if(!$closed_plan){
				switch ($lang){
					
					case("en"):
						echo '<a href="javascript:void(0);" onclick="popUpPlanConfirm()">
								<div class="div_button" data-toggle="modal" data-target="#confirm_plan" id="save_plan_butt">Save</div>
							</a>';
						break;
						
					case("es"):
						echo '<a href="javascript:void(0);" onclick="popUpPlanConfirm()">
								<div class="div_button" data-toggle="modal" data-target="#confirm_plan" id="save_plan_butt">Guardar</div>
							</a>';
						break;
				}

			}
			?>
		</form>
		
	

<!-- 		<p class='dashboard_tag'> -->
<!-- 			This is YOUR personal notes. It will NOT be shared with the patient, NOR with other doctors. -->
<!-- 		</p> -->
		<form class="notes_post" action="" method="POST">
    		<?php 
    			switch ($lang){
    					 		
    				case("en"):
    		?>
    					<h3>Notes</h3>
    					<p id="notes_not_saved">(Not Saved Yet)</p>
    		<?php 
    			        break;
    				
    				case("es"):
    		?>
    					<h3>Notas</h3>
    					<p id="notes_not_saved">(Sin Guardar)</p>
    		<?php 
    					break;
    			}
    		?>
			<textarea name="notes" id="notes" onkeyup="unsavedNotes()" placeholder="<?php 
			switch ($lang){
				
				case("en"):
					echo "Write your personal notes regarding the current patient. This are YOUR personal notes, and they will NOT be shared with the patient, NOR with other doctors.";
					break;
					
				case("es"):
					echo "Escribe tus notas personales de la consulta. Estas son TUS notas personales, y NO serán compartidas con el paciente NI con otros doctores.";
					break;
			}
			?>"><?php 
				echo $plan_arr['notes']; //TODO
			?></textarea>
			<div class="div_button" id="save_notes_butt">
				<?php 
					switch ($lang){
							 		
						case("en"):
				?>
							Save
				<?php 
					        break;
						
						case("es"):
				?>
							Guardar
				<?php 
							break;
					}
				?>
				
			</div>
		</form>
	</div>

	<div class="profile_main_column column" style="width: 60%;  margin-left: 20%;">
		<ul class="nav nav-tabs" role="tablist" id="profileTabs">
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>			
						<li role="presentation" class="active"><span id="basic_tab"></span><a href="#current_sympts" aria-controls="current_sympts" role="tab" data-toggle="tab"><span id="illness_tab"></span>Current Symptoms</a></li>
						<li role="presentation"><span id="basic_tab"></span><a href="#pmh" aria-controls="pmh" role="tab" data-toggle="tab"><span id="medhist_tab"></span>Medical History</a></li>
						<li role="presentation"><span id="basic_tab"></span><a href="#previous_appo" aria-controls="previous_appo" role="tab" data-toggle="tab"><span id="prev_appo_tab"></span>Previous Appointments</a></li>
			<?php 
				        break;
					
					case("es"):
			?>
						<li role="presentation" class="active"><span id="basic_tab"></span><a href="#current_sympts" aria-controls="current_sympts" role="tab" data-toggle="tab"><span id="illness_tab"></span>Síntomas</a></li>
						<li role="presentation"><span id="basic_tab"></span><a href="#pmh" aria-controls="pmh" role="tab" data-toggle="tab"><span id="medhist_tab"></span>Historia Clínica</a></li>
						<li role="presentation"><span id="basic_tab"></span><a href="#previous_appo" aria-controls="previous_appo" role="tab" data-toggle="tab"><span id="prev_appo_tab"></span>Consultas Previas</a></li>

			<?php 
						break;
				}
			?>

			<!-- <li role="presentation"><a href="#diagnoses" aria-controls="diagnoses" role="tab" data-toggle="tab">Diagnoses</a></li> -->
		</ul>

		<div class="tab-content">

			<div role="tabpanel" class="tab-pane fade in active" id="current_sympts">
				<!--   <div id="dashboard_mainc_top">-->
					<div class="dashboard_full_block">
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h3>Provided List of Symptoms</h3>
						<?php 
							        break;
								
								case("es"):
						?>
									<h3>Lista de Síntomas</h3>
						<?php 
									break;
							}
						?>
						
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<p>Signs and Symptoms described by the patient</p>
						<?php 
							        break;
								
								case("es"):
						?>
									<p>Síntomas registrados por el paciente </p>
						<?php 
									break;
							}
						?>
						<div id="dashboard_symptoms_cont_box" class="style-2">
							<?php
								echo $appointment_obj->printAppointmentSymptoms($appointment_id);
							?>
						</div>
					</div>
				<!--</div>-->
				<hr>
				<!--<div id="dashboard_mainc_top">-->
					<div class="dashboard_full_block">
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h2>Add Symptoms</h2>
						<?php 
							        break;
								
								case("es"):
						?>
									<h2>Añadir Síntomas</h2>
						<?php 
									break;
							}
						?>
				
						
						<div class="search_appointments">
							<?php $symp = "symptoms"; ?>
							<input type="text" onkeyup="sanitizeSearchSymptomsMed(this.value,'<?php echo bin2hex($txt_rep->entities($userLoggedIn_e)); ?>', '<?php echo $symp; ?>')" placeholder="<?php 
							switch ($lang){
								
								case("en"):
									echo "(Symptoms separated by a comma) Ex: Stomach Ache, Fever, Vomit, etc.";
									break;
									
								case("es"):
									echo "(Síntomas separados por coma) Ej: Dolor de Estómago, Fiebre, Vómito, etc.";
									break;
							}
							?>" autocomplete="off" id="search_text_input_symptoms" name="post_symptoms" value="<?php 
									if(isset($_SESSION['post_symptoms'])){
										echo $txt_rep->entities($_SESSION['post_symptoms']);
									}
								?>"
							>
							<div class="button_holder_appointment">
								<img src="assets/images/icons/search-icon-pink.png">
							</div>
	
							<div class="search_symptoms_results">
							</div>
						</div>
						<div style='display: inline-block ;' id="error_display_space">
						</div>

						<div style="margin-top:35px; display: inline-block ; width: 100% ;">
							<?php 
								switch ($lang){
										 		
									case("en"):
							?>
										<h2>Symptom(s) Description / Notes:</h2>
							<?php 
								        break;
									
									case("es"):
							?>
										<h2>Descripción de Síntoma(s) / Notas:</h2>
							<?php 
										break;
								}
							?>
							
							
							<textarea name="post_text" id="post_text" onkeyup="storeVal(this.value, '<?php echo $symp; ?>')" placeholder="<?php 
							switch ($lang){
								
								case("en"):
									echo "Ex: pain on the left side / hurts after eating / it started 2 months ago ...";
									break;
									
								case("es"):
									echo "Ej: dolor en el lado izquierdo / duele al comer / empezó hace 2 meses ...";
									break;
							}
							?>"><?php 
									if(isset($_SESSION['post_text'])){
										echo $txt_rep->entities($_SESSION['post_text']);
									}
								?></textarea>
						</div>
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<input type="submit" name="add_symptoms_butt" value="Add" id="post_button">
						<?php 
							        break;
								
								case("es"):
						?>
									<input type="submit" name="add_symptoms_butt" value="Adicionar" id="post_button">
						<?php 
									break;
							}
						?>
						
						    
					</div>
				<!--</div>-->
			</div>
			
			<div role="tabpanel" class="tab-pane fade" id="pmh">
			 	<div id="dashboard_mainc_top">
			 		<div class="graph_window" style="height: 0px; border: none; display: inline-block; text-align: center; padding: 0px;">
 						<a href="javascript:void(0);" onclick="closeGraphWindow();">
							<div class="delete_element" id="del_id_2" style=" display: inline-block; margin:5px">x</div>
						</a>
						<div id="canvas_graph_div" width="500px" height="260px">
	 						<canvas id="canvas_graph" width="500px" height="260px"> <!-- Size must be set here as well, otherwise a magnification or scaling occurs -->
							    Your browser does not support the canvas element, for which the graphs could not be displayed. Please update to the latest version of Chrome, Firefox or Safari.
							</canvas>
						</div>
			 		</div>
			 		<div class="dashboard_big_block" style="border-right:1px rgb(227, 227, 222) solid;">
			 			<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h3>Health Stats</h3>
						<?php 
							        break;
								
								case("es"):
						?>
									<h3>Estadísticas</h3>
						<?php 
									break;
							}
						?>
				 		
				 		<div class="dashboard_tag_block_main">
					 		<?php
					 			$health_tab = $patient_obj->getHeight();
						 		$stmt = $con->prepare("SELECT height FROM $health_tab ORDER BY date_time DESC LIMIT 1");
						 		$stmt->execute();
						 		$target = mysqli_fetch_array($stmt->get_result())['height'];
					 		?>
							<p class="dashboard_tag">
					 			<?php 
									switch ($lang){
											 		
										case("en"):
								?>
											Height
								<?php 
									        break;
										
										case("es"):
								?>
											Altura
								<?php 
											break;
									}
								?>
								
							</p>
							<div class="dashboard_block_graph">
								<div class="dashboard_block_2 graph_sized">
									<?php
									$current_type = 'height';
									if($target != "")
										echo $target;
									else{
										switch ($lang){
											
											case("en"):
												echo "NA";
												break;
												
											case("es"):
												echo "NR";
												break;
										}
									}
									?>
								</div>
								<a href="javascript:void(0);" id="<?php echo $current_type . '_g_toggle'; ?>" onclick="drawGraph('<?php echo bin2hex($patient_username_e); ?>' ,'<?php echo bin2hex($userLoggedIn_e); ?>' ,'<?php echo $appointment_id; ?>', '<?php echo $current_type; ?>', '<?php echo $num_points; ?>')">
									<div class="graph_button" id="<?php echo $current_type . '_g_button'; ?>"></div>
								</a>
							</div>
						</div>
						<div class="dashboard_tag_block_main">
					 		<?php
					 			$health_tab = $patient_obj->getWeight();
						 		$stmt = $con->prepare("SELECT weight FROM $health_tab ORDER BY date_time DESC LIMIT 1");
						 		$stmt->execute();
						 		$target = mysqli_fetch_array($stmt->get_result())['weight'];
					 		?>
							<p class="dashboard_tag">
							<?php 
								switch ($lang){
										 		
									case("en"):
							?>
										Weight
							<?php 
								        break;
									
									case("es"):
							?>
										Peso
							<?php 
										break;
								}
							?>
								
							</p>
							<div class="dashboard_block_graph">
								<div class="dashboard_block_2 graph_sized">
									<?php
									$current_type = 'weight';
									if($target != "")
										echo $target;
									else{
										switch ($lang){
											
											case("en"):
												echo "NA";
												break;
												
											case("es"):
												echo "NR";
												break;
										}
									}
									?>
								</div>
								<a href="javascript:void(0);" id="<?php echo $current_type . '_g_toggle'; ?>" onclick="drawGraph('<?php echo bin2hex($patient_username_e); ?>' ,'<?php echo bin2hex($userLoggedIn_e); ?>' ,'<?php echo $appointment_id; ?>', '<?php echo $current_type; ?>', '<?php echo $num_points; ?>')">
									<div class="graph_button" id="<?php echo $current_type . '_g_button'; ?>"></div>
								</a>
							</div>
						</div>
						<div class="dashboard_tag_block_main">
					 		<?php
					 			$health_tab = $patient_obj->getBMI();
						 		$stmt = $con->prepare("SELECT BMI FROM $health_tab ORDER BY date_time DESC LIMIT 1");
						 		$stmt->execute();
						 		$target = mysqli_fetch_array($stmt->get_result())['BMI'];
					 		?>
							<p class="dashboard_tag">
								<?php
									switch ($lang){
										
										case("en"):
											if($target < 18.5 && $target !== NULL)
												echo "BMI - <span style='color:yellow;'> Underweight </span>";
											elseif ($target >= 18.5 && $target < 24.9)
												echo "BMI - <span style='color:blue;'> Normal </span>";
											elseif ($target >= 25 && $target < 29.9)
												echo "BMI - <span style='color:red;'> Overweight </span>";
											elseif ($target >= 30)
												echo "BMI - <span style='color:red;'> Obese </span>";
											else
												echo "BMI";
											break;
											
										case("es"):
											if($target < 18.5 && $target !== NULL)
												echo "IMC - <span style='color:yellow;'> Infrapeso </span>";
											elseif ($target >= 18.5 && $target < 24.9)
												echo "IMC - <span style='color:blue;'> Normal </span>";
											elseif ($target >= 25 && $target < 29.9)
												echo "IMC - <span style='color:red;'> Sobrepeso </span>";
											elseif ($target >= 30)
												echo "IMC - <span style='color:red;'> Obesidad </span>";
											else
												echo "IMC";
											break;
									}

								?>
							</p>
							<div class="dashboard_block_graph">
								<div class="dashboard_block_2 graph_sized">
									<?php
									$current_type = 'bmi';
									if($target != "")
										echo $target;
									else{
										switch ($lang){
											
											case("en"):
												echo "NA";
												break;
												
											case("es"):
												echo "NR";
												break;
										}
									}
									?>
								</div>
								<a href="javascript:void(0);" id="<?php echo $current_type . '_g_toggle'; ?>" onclick="drawGraph('<?php echo bin2hex($patient_username_e); ?>' ,'<?php echo bin2hex($userLoggedIn_e); ?>' ,'<?php echo $appointment_id; ?>', '<?php echo $current_type; ?>', '<?php echo $num_points; ?>')">
									<div class="graph_button" id="<?php echo $current_type . '_g_button'; ?>"></div>
								</a>
							</div>
						</div>
						<div class="dashboard_tag_block_main">
					 		<?php
					 			$health_tab = $patient_obj->getBloodPressure();
						 		$stmt = $con->prepare("SELECT BPSys,BPDia FROM $health_tab ORDER BY date_time DESC LIMIT 1");
						 		$stmt->execute();
						 		$array = mysqli_fetch_array($stmt->get_result());
						 		$target1 = $array['BPSys'];
						 		$target2 = $array['BPDia'];
					 		?>
							<p class="dashboard_tag">
								<?php 
									switch ($lang){
											 		
										case("en"):
								?>
											Blood Pressure - sys/dia
								<?php 
									        break;
										
										case("es"):
								?>
											Presión Arterial - sis/dia
								<?php 
											break;
									}
								?>
								
							</p>
							<div class="dashboard_block_graph">
								<div class="dashboard_block_2 graph_sized">
									<?php
									$current_type = 'bp';
									if($target1 != "")
										echo $target1 . " / " . $target2;
									else{
										switch ($lang){
											
											case("en"):
												echo "NA";
												break;
												
											case("es"):
												echo "NR";
												break;
										}
									}
									?>
								</div>
								<a href="javascript:void(0);" id="<?php echo $current_type . '_g_toggle'; ?>" onclick="drawGraph('<?php echo bin2hex($patient_username_e); ?>' ,'<?php echo bin2hex($userLoggedIn_e); ?>' ,'<?php echo $appointment_id; ?>', '<?php echo $current_type; ?>', '<?php echo $num_points; ?>')">
									<div class="graph_button" id="<?php echo $current_type . '_g_button'; ?>"></div>
								</a>
							</div>
						</div>
			 		</div>
 					<div class="dashboard_big_block">
 							<?php 
								switch ($lang){
										 		
									case("en"):
							?>
										<h3>Habits</h3>
							<?php 
								        break;
									
									case("es"):
							?>
										<h3>Habitos</h3>
							<?php 
										break;
								}
							?>
 						
 							<?php
					 			$health_tab = $patient_obj->getHabitsTable();
						 		$stmt = $con->prepare("SELECT * FROM $health_tab ORDER BY last_update DESC LIMIT 1");
						 		$stmt->execute();
						 		$arr = mysqli_fetch_array($stmt->get_result());
					 		?>
				 		<div class="dashboard_tag_block_main">
							<p class="dashboard_tag">
	 							<?php 
									switch ($lang){
											 		
										case("en"):
								?>
											Smoking Habits
								<?php 
									        break;
										
										case("es"):
								?>
											Consumo de Tabaco
								<?php 
											break;
									}
								?>
								
							</p>
							<div class="dashboard_block_2">
								<?php 
								if($arr['smoking'] != "")
									echo $txt_rep->entities($crypt->decryptStringPI($arr['smoking'], $patient_username, $patient_obj->user_info['signup_date']));
								else{
									switch ($lang){
										
										case("en"):
											echo "NA";
											break;
											
										case("es"):
											echo "NR";
											break;
									}
								}
								?>
							</div>
						</div>
				 		<div class="dashboard_tag_block_main">
							<p class="dashboard_tag">
	 							<?php 
									switch ($lang){
											 		
										case("en"):
								?>
											Alcohol Consumption
								<?php 
									        break;
										
										case("es"):
								?>
											Consumo de Alcohol
								<?php 
											break;
									}
								?>
								
							</p>
							<div class="dashboard_block_2">
								<?php 
								if($arr['alcohol'] != "")
									echo $txt_rep->entities($crypt->decryptStringPI($arr['alcohol'], $patient_username, $patient_obj->user_info['signup_date']));
								else{
									switch ($lang){
										
										case("en"):
											echo "NA";
											break;
											
										case("es"):
											echo "NR";
											break;
									}
								}
								?>
							</div>
						</div>
				 		<div class="dashboard_tag_block_main">
							<p class="dashboard_tag">
	 							<?php 
									switch ($lang){
											 		
										case("en"):
								?>
											Diet
								<?php 
									        break;
										
										case("es"):
								?>
											Dieta
								<?php 
											break;
									}
								?>
								
							</p>
							<div class="dashboard_block_2">
								<?php 
								if($arr['diet'] != "")
									echo $txt_rep->entities($crypt->decryptStringPI($arr['diet'], $patient_username, $patient_obj->user_info['signup_date']));
								else{
									switch ($lang){
										
										case("en"):
											echo "NA";
											break;
											
										case("es"):
											echo "NR";
											break;
									}
								}
								?>
							</div>
						</div>
				 		<div class="dashboard_tag_block_main">
							<p class="dashboard_tag">
	 							<?php 
									switch ($lang){
											 		
										case("en"):
								?>
											Physical Activity
								<?php 
									        break;
										
										case("es"):
								?>
											Actividad Física
								<?php 
											break;
									}
								?>
								
							</p>
							<div class="dashboard_block_2">
								<?php 
								if($arr['physical_activity'] != "")
									echo $txt_rep->entities($crypt->decryptStringPI($arr['physical_activity'], $patient_username, $patient_obj->user_info['signup_date']));
								else{
									switch ($lang){
										
										case("en"):
											echo "NA";
											break;
											
										case("es"):
											echo "NR";
											break;
									}
								}
								?>
							</div>
						</div>
						<div class="dashboard_tag_block_main">
							<p class="dashboard_tag">
	 							<?php 
									switch ($lang){
											 		
										case("en"):
								?>
											Others
								<?php 
									        break;
										
										case("es"):
								?>
											Otros
								<?php 
											break;
									}
								?>

							</p>
							<div class="dashboard_block_2">
								<?php 
								if($arr['other'] != "")
									echo $txt_rep->entities($crypt->decryptStringPI($arr['other'], $patient_username, $patient_obj->user_info['signup_date']));
								else{
									switch ($lang){
										
										case("en"):
											echo "NA";
											break;
											
										case("es"):
											echo "NR";
											break;
									}
								}
								?>
							</div>
						</div>
				 		
			 		</div>
			 		
		 			<hr style="margin-top: 55px;">
			 		<?php 
			 			if($basic_info['sex'] == 'f'){
			 				$patient_obj->getOBGYNTable();
			 				
			 				$obgyn_tab = $patient_obj->getOBGYNTable();
			 				//echo $obgyn_tab;
			 				$stmt = $con->prepare("SELECT * FROM $obgyn_tab ORDER BY last_update DESC LIMIT 1");
			 				$stmt->execute();
			 				$arr = mysqli_fetch_assoc($stmt->get_result());
			 				
			 				switch ($lang){
			 					
			 					case("en"):
			 						$obgyn_str ='<div class="dashboard_big_block" style="width: 100%;">
										   <h3>OB-GYN</h3>';
			 						break;
			 						
			 					case("es"):
			 						$obgyn_str ='<div class="dashboard_big_block" style="width: 100%;">
										   <h3>Ginecológico</h3>';
			 						break;
			 				}
			 				
			 				
			 				//MENARCHE
			 				
			 				switch ($lang){
			 					
			 					case("en"):
			 						$obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Menarche
										   </p>
										   <div class="dashboard_block_2">';
			 						break;
			 						
			 					case("es"):
			 						$obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Menarquía
										   </p>
										   <div class="dashboard_block_2">';
			 						break;
			 				}
			 				
			 				
			 				if($arr['menarche'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['menarche']);
			 				}
			 				else{
			 					switch ($lang){
			 						
			 						case("en"):
			 							$obgyn_str .= 'NA';
			 							break;
			 							
			 						case("es"):
			 							$obgyn_str .= 'NR';
			 							break;
			 					}
			 					
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				//LMP
			 				
			 				switch ($lang){
			 					
			 					case("en"):
			 						$obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Last Menstrual Period (YYYY-MM-DD)
										   </p>
										   <div class="dashboard_block_2">';
			 						break;
			 						
			 					case("es"):
			 						$obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Fecha Última Regla (YYYY-MM-DD)
										   </p>
										   <div class="dashboard_block_2">';
			 						break;
			 				}
			 				
			 				if($arr['lmp'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['lmp']);
			 				}
			 				else{
			 					switch ($lang){
			 						
			 						case("en"):
			 							$obgyn_str .= 'NA';
			 							break;
			 							
			 						case("es"):
			 							$obgyn_str .= 'NR';
			 							break;
			 					}
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 			
			 				//Cycles
			 				
			 				switch ($lang){
			 					
			 					case("en"):
			 						$obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Cycle Duration
										   </p>
										   <div class="dashboard_block_2">';
			 						break;
			 						
			 					case("es"):
			 						$obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Duración Ciclos
										   </p>
										   <div class="dashboard_block_2">';
			 						break;
			 				}
			 				
			 				$obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Cycles
										   </p>
										   <div class="dashboard_block_2">';
			 				
			 				if($arr['cycles'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['cycles']);
			 				}
			 				else{
			 					switch ($lang){
			 						
			 						case("en"):
			 							$obgyn_str .= 'NA';
			 							break;
			 							
			 						case("es"):
			 							$obgyn_str .= 'NR';
			 							break;
			 					}
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				//birthcontrol
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Birthcontrol
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Anticonceptivos
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['birthcontrol'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['birthcontrol']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				//Separador
			 				
			 				$obgyn_str .= '<div class="dashboard_tag_block_main" id="dashboard_separator"></div>';
			 				
			 				//menopause
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Menopause
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Menopausia
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['menopause'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['menopause']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				//Separador
			 				
			 				$obgyn_str .= '<br>';
			 				
			 				//gestations
			 				
			 				
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Gestations
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Gestaciones
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['gestations'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['gestations']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				//Parity
			 				
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Parity
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Embarazos
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['parity'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['parity']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				
			 				//abortions
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Abortions
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Abortos
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['abortions'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['abortions']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				//csections
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											C-Sections
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Cesáreas
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['csections'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['csections']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				//ectopic
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Ectopic Pregnancies
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
											Embarazos Ectópicos
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['ectopic'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['ectopic']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				//Separador
			 				
			 				$obgyn_str .= '<div class="dashboard_tag_block_main" id="dashboard_separator"></div>';
			 				
			 				//mammography_date
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
												Last Mammography Date
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
												Fecha Última Mamografía
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['mammography_date'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['mammography_date']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				//mammography_result
			 				switch ($lang){
			 				    
			 				    case("en"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
												Last Mammography Results
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				        
			 				    case("es"):
			 				        $obgyn_str .= '<div class="dashboard_tag_block_main">
										   <p class="dashboard_tag">
												Resultados Última Mamografía
										   </p>
										   <div class="dashboard_block_2">';
			 				        break;
			 				}
			 				
			 				
			 				if($arr['mammography_result'] > 0){
			 					$obgyn_str .= $txt_rep->entities($arr['mammography_result']);
			 				}
			 				else{
			 				    switch ($lang){
			 				        
			 				        case("en"):
			 				            $obgyn_str .= 'NA';
			 				            break;
			 				            
			 				        case("es"):
			 				            $obgyn_str .= 'NR';
			 				            break;
			 				    }
			 				}
			 				
			 				$obgyn_str .= '</div>
										</div>';
			 				
			 				$obgyn_str .= '</div>';
			 				echo $obgyn_str;
			 			}
			 			?>
			 				
			 		<hr style="margin-top: 55px;" >		 		
			 		
			 		<div class="dashboard_big_block" style="border-right:1px rgb(227, 227, 222) solid;">
			 			<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h2>Illnesses / Hospitalizations</h2>
						<?php 
							        break;
								
								case("es"):
						?>
									<h2>Enfermedades / Hospitalizaciones</h2>
						<?php 
									break;
							}
						?>
 						
 						<div class="box_div_container">
							<div class="added_box_header">
								<div class="box_left" style="width:70%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Illn./Hosp.</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Enf./Hosp.</p>
									<?php 
												break;
										}
									?>
									
								</div>
								<div class="box_right" style="width:30%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Approx. Time Ago</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Hace</p>
									<?php 
												break;
										}
									?>
									
								</div>
							</div>
						
				
							<div class="added_box style-2" id="pathologies_box">
								<?php 
									
								echo $patient_obj->getPathologiesData(date("Y-m-d H:i:s"),False);
								?>
							</div>
						</div>
					</div>
					
			 		<div class="dashboard_big_block">
			 			<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h2>Surgeries / Traumas</h2>
						<?php 
							        break;
								
								case("es"):
						?>
									<h2>Cirugías / Traumas</h2>
						<?php 
									break;
							}
						?>
 						
 						<div class="box_div_container">
							<div class="added_box_header">
								<div class="box_left" style="width:70%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Surg./Traum.</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Ciru./Traum.</p>
									<?php 
												break;
										}
									?>
									
								</div>
								<div class="box_right" style="width:30%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Approx. Time Ago</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Hace</p>
									<?php 
												break;
										}
									?>
								</div>
							</div>
						
				
							<div class="added_box style-2" id="pathologies_box">
								<?php 
									echo $patient_obj->getSurgeriesData(date("Y-m-d H:i:s"),False);
								?>
							</div>
						</div>
					</div>
				
					
					<hr style="width:100%; border-top: none;">
					
					<div class="dashboard_big_block" style="border-right:1px rgb(227, 227, 222) solid;">
			 			<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h2>Hereditary Diseases</h2>
						<?php 
							        break;
								
								case("es"):
						?>
									<h2>Enfermedades Hereditarias</h2>
						<?php 
									break;
							}
						?>
 						
 						<div class="box_div_container">
							<div class="added_box_header">
								<div class="box_left" style="width:70%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Diseases</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Enfermedades</p>
									<?php 
												break;
										}
									?>
									
								</div>
								<div class="box_right" style="width:30%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Relative</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Pariente</p>
									<?php 
												break;
										}
									?>
									
								</div>
							</div>
						
				
							<div class="added_box style-2" id="pathologies_box">
								<?php 
									
									echo $patient_obj->getHereditariesData(False);
								?>
							</div>
						</div>
					</div>
					
			 		<div class="dashboard_big_block">
			 			<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h2>Medicines</h2>
						<?php 
							        break;
								
								case("es"):
						?>
									<h2>Medicinas</h2>
						<?php 
									break;
							}
						?>
 						
 						<div class="box_div_container">
							<div class="added_box_header">
								<div class="box_left" style="width:70%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Medicines</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Medicinas</p>
									<?php 
												break;
										}
									?>
									
								</div>
								<div class="box_right" style="width:30%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Dosage</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Dosis</p>
									<?php 
												break;
										}
									?>
									
								</div>
							</div>
							
				
							<div class="added_box style-2" id="pathologies_box">
								<?php 
									
								echo $patient_obj->getMedicinesData(False);
								?>
							</div>
						</div>
					</div>
					
					<hr style="width:100%; border-top: none;">
					
					<div class="dashboard_big_block" style="border-right:1px rgb(227, 227, 222) solid;">
			 			<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h2>Allergies</h2>
						<?php 
							        break;
								
								case("es"):
						?>
									<h2>Alergias</h2>
						<?php 
									break;
							}
						?>
 						
 						<div class="box_div_container">
							<div class="added_box_header">
								<div class="box_left" style="width:70%;">
						 			<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<p>Allergies</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<p>Alergias</p>
									<?php 
												break;
										}
									?>
								</div>
							</div>
						
				
							<div class="added_box style-2" id="pathologies_box">
								<?php 
									echo $patient_obj->getAllergiesData(False);
								?>
							</div>
						</div>
					</div>
			 		
			 	</div>
			</div>
			
			<!-- <div role="tabpanel" class="tab-pane fade" id="about_div"></div> -->
			
			<div role="tabpanel" class="tab-pane fade" id="previous_appo">
				<div id="dashboard_mainc_top" style=" width: 100%;">
					<div class="dashboard_prev_appo_header">
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h3>Patient previous appointments</h3>
						<?php 
							        break;
								
								case("es"):
						?>
									<h3>Citas previas del paciente</h3>
						<?php 
									break;
							}
						?>
						
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<p>Check the patient's history in the medical community CONFIDR</p>
						<?php 
							        break;
								
								case("es"):
						?>
									<p>Consulta el historial del paciente en la comunidad medica CONFIDR</p>
						<?php 
									break;
							}
						?>
						
			  			<input type="checkbox" id="checkbox_all_docs" checked>
			  			<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<p style=" display: inline-block; margin-right: 7px;">All Doctors</p>
						<?php 
							        break;
								
								case("es"):
						?>
									<p style=" display: inline-block; margin-right: 7px;">Todos los Doctores</p>
						<?php 
									break;
							}
						?>
						
						<input type="checkbox" id="checkbox_me">
						
		  				<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<p style=" display: inline-block; margin-right: 29px;">Me</p>
						<?php 
							        break;
								
								case("es"):
						?>
									<p style=" display: inline-block; margin-right: 29px;">Yo</p>
						<?php 
									break;
							}
						?>
						
						
						<br>
						
						
		  				<?php 
							switch ($lang){
									 		
								case("en"):
						?>		<div style="display: block;">
									<p style=" display: inline-block; margin-bottom: 2px; width: 100%;">Search by:</p>
									<div class="dashboard_arrow">
									<select id="select_search_criteria">
										<option value="specialty">Specialty</option>
										<option value="name">Doctor's Name</option>
										<option value="plan">Plan</option>
										<option value="symptoms">Symptoms</option>
			<!-- 							<option value="date">Date</option> -->
									</select>
									</div>
								<div class="button_holder_appointment dashboard_search_icon">	
									<input type="text" onkeyup="searchDashboard(this.value, '<?php echo bin2hex($patient_username_e); ?>')" placeholder="Search History" autocomplete="off" id="search_history" name="search_history" value="">

								
						<?php 
							        break;
								
								case("es"):
						?>		<div style="display: block;">
									<p style=" display: inline-block; margin-bottom: 2px; width: 100%;">Buscar por:</p>
    								<div class="dashboard_arrow">	
    									<select id="select_search_criteria">
    										<option value="specialty">Especialidad</option>
    										<option value="name">Nombre del Doctor</option>
    										<option value="plan">Plan</option>
    										<option value="symptoms">Síntomas</option>
    			<!-- 							<option value="date">Date</option> -->
    									</select>
    									</div>
									<div class="button_holder_appointment dashboard_search_icon">
    									<input type="text" onkeyup="searchDashboard(this.value, '<?php echo bin2hex($patient_username_e); ?>')" placeholder="Busca en el historial" autocomplete="off" id="search_history" name="search_history" value="">
            					
    						
    									
									
						<?php         
									break;
							}
						?>
    							<img src="assets/images/icons/search-icon.png">
            						</div>
        						</div>
        					</div>
        						
						
            							
						<div class="row_container_dashboard title_search_dash" >
						
						
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<div class="row_element_title dash_search_name style-2 " style=" overflow-y: unset;">
										<p> Name / Specialty / Date :</p>
									</div>
									<div class="row_element_title dash_search_symptoms" style=" overflow-y: unset;">
										<p> Symptoms: </p>
									</div>
									<div class="row_element_dashboard dash_search_plan" style=" overflow-y: unset;">
										<p > Plan: </p>
									</div>
						<?php 
							        break;
								
								case("es"):
						?>
									<div class="row_element_title dash_search_name  " style=" overflow-y: unset;">
										<p> Nombre / Especialidad / Fecha : </p>
									</div>
									<div class="row_element_title dash_search_symptoms" style=" overflow-y: unset;">
										<p> Síntomas: </p>
									</div>
									<div class="row_element_title dash_search_plan" style=" overflow-y: unset;">
										<p> Plan: </p>
									</div>
						<?php 
									break;
							}
						?>

						

						
					</div>
					<div class="dashboard_prev_appo_container style-2">
						<?php 
						//echo $appointment_obj->printPreviousAppointments($patient_obj,"symptoms","fev");
						echo $appointment_obj->printPreviousAppointments($patient_obj);
						?>
					</div>
				</div>
			</div>
			
<script>
	var pat_user = '<?php echo bin2hex($patient_username_e); //TODO:?>';
	var cid = '<?php echo $txt_rep->entities($appointment_id); ?>';
	
	$(document).ready(function(){
		
		$('.nav-tabs > li'). css('width', 'calc(100% / 3)');
		$('#save_notes_butt').on("click",function(){
			$.ajax({
				type: "POST",
				url: "doctor_appointment_viewer.php?cid=" + cid,
				data: $('form.notes_post').serialize(), //What we send!
				success: function(msg) {
					//alert(msg);
					selfHideAlert("Saved",3000,"notes");
					$('#notes_not_saved').css({"display":"none"});
					$('#notes').css({"border":"1px solid #ccc"});
				},
				error: function() {
					alert("Could not be saved at this time, please try again later.");
				}
			});
		});

		
		$("input[name='add_symptoms_butt']").click(function(){
			var sympts_add = $('#search_text_input_symptoms').val();
			var sympts_text_add = $('#post_text').val();
			$.ajax({
				url: "includes/form_handlers/ajax_insert_symptoms.php",
				type: "POST",
				data: "cid=" + cid + "&post_symptoms=" + sympts_add + "&post_text=" + sympts_text_add,
				cache:false,

				success: function(data){
					$('#error_display_space').html(data);
					$('#search_text_input_symptoms').val("");
					$('#post_text').val("");
				}
			});
			$.ajax({
				url: "includes/handlers/ajax_reload_symptoms.php",
				type: "POST",
				data: "cid=" + cid,
				cache:false,

				success: function(data){
					$('#dashboard_symptoms_cont_box').html("");
					$('#dashboard_symptoms_cont_box').html(data);
					var ddd = document.getElementById('dashboard_symptoms_cont_box');
					ddd.scrollTop = ddd.scrollHeight;
				}
			});
	    });

		
	    $('#checkbox_me').change(function() {		    
		    if($('#checkbox_me').is(":checked") && $('#checkbox_all_docs').is(":checked")){
	        		$('#checkbox_all_docs').prop('checked', 0);
		    }
		    else{
		    		$('#checkbox_all_docs').prop('checked', 1);
		    }

			var search = $('#search_history').val();
			var patient_username = '<?php echo bin2hex($patient_username_e);?>';
		    searchDashboard(search,patient_username);
	    });

	    $('#checkbox_all_docs').change(function() {		    
		    if($('#checkbox_me').is(":checked") && $('#checkbox_all_docs').is(":checked")){
	        		$('#checkbox_me').prop('checked', 0);
		    }
		    else{
		    		$('#checkbox_me').prop('checked', 1);
		    }

			var search = $('#search_history').val();
			var patient_username = '<?php echo bin2hex($patient_username_e);?>';
		    searchDashboard(search,patient_username);
	    });

	    $('#select_search_criteria').change(function() {
			var search = $('#search_history').val();
			var patient_username = '<?php echo bin2hex($patient_username_e);?>';
		    searchDashboard(search,patient_username);
	    });
		
		$('#loading').show();

		$('.dashboard_prev_appo_container').bind('scroll', function(){
			var search = $('#search_history').val();
			var patient_username = '<?php echo bin2hex($patient_username_e);?>';
			var count_prev = $('.dashboard_prev_appo_container').find('.count_search_dash').val();
			var ending = $('.dashboard_prev_appo_container').find('.end_search_dash').val();
			if($(this).scrollTop() + $(this).innerHeight()>=$(this)[0].scrollHeight && ending == 0){
				//alert(ending);
				searchDashboard(search,patient_username,count_prev);
            }
		});

		$('#confirm_edit_button').on('click', function(){
			$('#appo_type').removeAttr('disabled');
			$("#perform_change_type").css({"display":"inline-block"});
			$("#confirm_appo_change_window").modal('hide');
		});

	});
</script>

<script>
function unsavedPlan(){
	$('#plan_not_saved').css({"display":"inline-block"});
	$('#post_plan').css({"border":"2px solid #f38ead"});
}

function unsavedNotes(){
	$('#notes_not_saved').css({"display":"inline-block"});
	$('#notes').css({"border":"2px solid #f38ead"});
}

function selfHideAlert(msg,duration,refElementId){
	var p = $( "#" + refElementId );
	var position = p.position();
	var outterHeight = p.outerHeight();
	var top = outterHeight + position.top;

    var element = document.createElement("div");
    element.setAttribute("style","position:absolute;top:" + top +"px;left:" + position.left + "px;background-color:rgb(214,119,153);");
    element.innerHTML = msg;
    setTimeout(function(){
    	element.parentNode.removeChild(element);
    },duration);
    document.body.appendChild(element);
}

function searchDashboard(search,patient_username,count_prev = 0){
	
	var select_search_criteria = $("#select_search_criteria").find(":selected").val();
	if($('#checkbox_me').is(":checked")){
		var doc_usr = '<?php echo bin2hex($userLoggedIn_e); ?>';
		$.ajax({
			url: "includes/handlers/ajax_search_dashboard_appos.php",
			type: "POST",
			data: "pat_user=" + patient_username + "&search_crit=" + select_search_criteria + "&search=" + search +"&doc_usr=" + doc_usr +"&count_prev=" + count_prev,
			cache:  false,
	
			success: function(data){
				//alert(count_prev);
				if(count_prev != 0){
					var ending = $('.dashboard_prev_appo_container').find('.end_search_dash').val();
					$('.dashboard_prev_appo_container').find('.count_search_dash').remove();
					if(ending == 0){
						$('.dashboard_prev_appo_container').find('.end_search_dash').remove();
						$('.dashboard_prev_appo_container').append(data);
					}
				}
				else{
					$('.dashboard_prev_appo_container').html(data);
				}
			}
		});
	}
	
	if($('#checkbox_all_docs').is(":checked")){
		$.ajax({
			url: "includes/handlers/ajax_search_dashboard_appos.php",
			type: "POST",
			data: "pat_user=" + patient_username + "&search_crit=" + select_search_criteria + "&search=" + search +"&count_prev=" + count_prev,
			cache:  false,
	
			success: function(data){
				//alert(count_prev);
				if(count_prev != 0){
					var ending = $('.dashboard_prev_appo_container').find('.end_search_dash').val();
					$('.dashboard_prev_appo_container').find('.count_search_dash').remove();
					if(ending == 0){
						$('.dashboard_prev_appo_container').find('.end_search_dash').remove();
						$('.dashboard_prev_appo_container').append(data);
					}
// 					if(ending == 0){
// 						$('.dashboard_prev_appo_container').append(data);
// 					}
				}
				else{
					$('.dashboard_prev_appo_container').html(data);
				}
			}
		});
	}
}
</script>
			
<!-- 			<div role="tabpanel" class="tab-pane fade" id="diagnoses"> -->
<!-- 				<div id="dashboard_mainc_top" style=" width: 100%;">
<!-- 					<h3>Diagnoses</h3> -->
<!--     					<div class="dashboard_big_block" style="border-right:1px rgb(227, 227, 222) solid;">
        					
<!--         					<h4>Main Diagnosis</h4> -->
                    		<?php //$country = $user_obj->getCountry_Doctor();?>
<!--                     		<input type="text" style=" float: left;" onkeyup="searchDiagnosisCIE(this.value, '<?php //echo bin2hex($userLoggedIn_e); ?>', '<?php //echo $appointment_id; ?>', '1')" placeholder="Search Diagnosis" autocomplete="off" id="search_diagnosis_input" name="search_diagnosis_input" required>
<!--                     		<div class="button_holder"> -->
<!-- 							<img src="assets/images/icons/search-icon.png"> -->
<!-- 						</div> -->
<!--                     		<div class="search_diagnosis_dropdown" id="search_diagnosis_dropdown_1" style=" display: inline-block ;"></div>
<!--                     		<p class="dashboard_tag left"> Description: </p> -->
<!--                     		<p id="diag_desc_1" style=" display: inline-block; clear: both; width: 100%;">
                    		<?php
//                     			$rips_tab = $user_obj->getDoctorsRIPS_tablename();
//                     			$row = "cod_diag_1";
//                     			$sql = "SELECT ". $row . " FROM " . $rips_tab . " WHERE consult_id = ?";
//                     			$stmt = $con->prepare($sql);
// 	                    		$stmt->bind_param("s",$appointment_id);
// 	                    		$stmt->execute();
// 	                    		$quer = $stmt->get_result();
// 	                    		$arr = mysqli_fetch_assoc($quer);
// 	                    		$code = $arr[$row];
	                    		
// 	                    		$stmt = $con->prepare("SELECT `desc` FROM cie_10_" . $country . " WHERE `cie_code` = ?");
// 	                    		$stmt->bind_param("s",$code);
// 	                    		$stmt->execute();
// 	                    		$quer = $stmt->get_result();
// 	                    		$arr = mysqli_fetch_assoc($quer);
// 	                    		$desc= $arr['desc'];
// 	                    		echo $desc;
//                     		?>
<!--                     		</p> -->
<!--                     		<p class="dashboard_tag left"> Code: </p> -->
<!--                     		<p id="diag_code_1" style=" display: inline-block; clear: both; width: 100%;">
                    		<?php 
//                     			echo $code;
//                     		?>
<!--                     		</p> -->
<!--                     	</div> -->
<!--                     	<div class="dashboard_big_block"> -->
<!--         					<h4>Secondary Diagnosis</h4> -->
                    		<?php //$country = $user_obj->getCountry_Doctor();?>
<!--                     		<input type="text" style=" float: left;" onkeyup="searchDiagnosisCIE(this.value, '<?php //echo bin2hex($userLoggedIn_e); ?>', '<?php //echo $appointment_id; ?>', '2')" placeholder="Search Diagnosis" autocomplete="off" id="search_diagnosis_input" name="search_diagnosis_input" required>
<!--                     		<div class="button_holder"> -->
<!-- 							<img src="assets/images/icons/search-icon.png"> -->
<!-- 						</div> -->
<!--                     		<div class="search_diagnosis_dropdown" id="search_diagnosis_dropdown_2" style=" display: inline-block ;"></div>
<!--                     		<p class="dashboard_tag left"> Description: </p> -->
<!--                    		<p id="diag_desc_2" style=" display: inline-block; clear: both; width: 100%;">
                    		<?php
//                     			$rips_tab = $user_obj->getDoctorsRIPS_tablename();
//                     			$row = "cod_diag_2";
//                     			$sql = "SELECT ". $row . " FROM " . $rips_tab . " WHERE consult_id = ?";
//                     			$stmt = $con->prepare($sql);
// 	                    		$stmt->bind_param("s",$appointment_id);
// 	                    		$stmt->execute();
// 	                    		$quer = $stmt->get_result();
// 	                    		$arr = mysqli_fetch_assoc($quer);
// 	                    		$code = $arr[$row];
	                    		
// 	                    		$stmt = $con->prepare("SELECT `desc` FROM cie_10_" . $country . " WHERE `cie_code` = ?");
// 	                    		$stmt->bind_param("s",$code);
// 	                    		$stmt->execute();
// 	                    		$quer = $stmt->get_result();
// 	                    		$arr = mysqli_fetch_assoc($quer);
// 	                    		$desc= $arr['desc'];
// 	                    		echo $desc;
//                     		?>
<!--                     		</p> -->
<!--                     		<p class="dashboard_tag left"> Code: </p> -->
<!--                     		<p id="diag_code_2" style=" display: inline-block; clear: both; width: 100%;">
                    		<?php 
//                     			echo $code;
//                     		?>
<!--                     		</p> -->
<!--                     	</div> -->
<!--                     	<div class="dashboard_big_block" style="border-right:1px rgb(227, 227, 222) solid;">
<!--         					<h4>Terciary Diagnosis</h4> -->
                    		<?php //$country = $user_obj->getCountry_Doctor();?>
<!--                     		<input type="text" style=" float: left;" onkeyup="searchDiagnosisCIE(this.value, '<?php //echo bin2hex($userLoggedIn_e); ?>', '<?php //echo $appointment_id; ?>', '3')" placeholder="Search Diagnosis" autocomplete="off" id="search_diagnosis_input" name="search_diagnosis_input" required>
<!--                     		<div class="button_holder"> -->
<!-- 							<img src="assets/images/icons/search-icon.png"> -->
<!-- 						</div> -->
<!--                     		<div class="search_diagnosis_dropdown" id="search_diagnosis_dropdown_3" style=" display: inline-block ;"></div>
<!--                     		<p class="dashboard_tag left"> Description: </p> -->
<!--                     		<p id="diag_desc_3" style=" display: inline-block; clear: both; width: 100%;">
                    		<?php
//                     			$rips_tab = $user_obj->getDoctorsRIPS_tablename();
//                     			$row = "cod_diag_3";
//                     			$sql = "SELECT ". $row . " FROM " . $rips_tab . " WHERE consult_id = ?";
//                     			$stmt = $con->prepare($sql);
// 	                    		$stmt->bind_param("s",$appointment_id);
// 	                    		$stmt->execute();
// 	                    		$quer = $stmt->get_result();
// 	                    		$arr = mysqli_fetch_assoc($quer);
// 	                    		$code = $arr[$row];
	                    		
// 	                    		$stmt = $con->prepare("SELECT `desc` FROM cie_10_" . $country . " WHERE `cie_code` = ?");
// 	                    		$stmt->bind_param("s",$code);
// 	                    		$stmt->execute();
// 	                    		$quer = $stmt->get_result();
// 	                    		$arr = mysqli_fetch_assoc($quer);
// 	                    		$desc= $arr['desc'];
// 	                    		echo $desc;
//                     		?>
<!--                     		</p> -->
<!--                     		<p class="dashboard_tag left"> Code: </p> -->
<!--                     		<p id="diag_code_3" style=" display: inline-block; clear: both; width: 100%;">
                    		<?php 
//                     			echo $code;
//                     		?>
<!--                     		</p> -->
<!--                     	</div> -->
                    	
<!--                     	<div class="dashboard_big_block"> -->
<!--         					<h4>Quaternary Diagnosis</h4> -->
                    		<?php //$country = $user_obj->getCountry_Doctor();?>
<!--                     		<input type="text" style=" float: left;" onkeyup="searchDiagnosisCIE(this.value, '<?php //echo bin2hex($userLoggedIn_e); ?>', '<?php //echo $appointment_id; ?>', '4')" placeholder="Search Diagnosis" autocomplete="off" id="search_diagnosis_input" name="search_diagnosis_input" required>
<!--                     		<div class="button_holder"> -->
<!-- 							<img src="assets/images/icons/search-icon.png"> -->
<!-- 						</div> -->
<!--                     		<div class="search_diagnosis_dropdown" id="search_diagnosis_dropdown_4" style=" display: inline-block ;"></div>
<!--                     		<p class="dashboard_tag left"> Description: </p> -->
<!--                     		<p id="diag_desc_4" style=" display: inline-block; clear: both; width: 100%;">
                    		<?php
//                     			$rips_tab = $user_obj->getDoctorsRIPS_tablename();
//                     			$row = "cod_diag_4";
//                     			$sql = "SELECT ". $row . " FROM " . $rips_tab . " WHERE consult_id = ?";
//                     			$stmt = $con->prepare($sql);
// 	                    		$stmt->bind_param("s",$appointment_id);
// 	                    		$stmt->execute();
// 	                    		$quer = $stmt->get_result();
// 	                    		$arr = mysqli_fetch_assoc($quer);
// 	                    		$code = $arr[$row];
	                    		
// 	                    		$stmt = $con->prepare("SELECT `desc` FROM cie_10_" . $country . " WHERE `cie_code` = ?");
// 	                    		$stmt->bind_param("s",$code);
// 	                    		$stmt->execute();
// 	                    		$quer = $stmt->get_result();
// 	                    		$arr = mysqli_fetch_assoc($quer);
// 	                    		$desc= $arr['desc'];
// 	                    		echo $desc;
//                     		?>
<!--                     		</p> -->
<!--                     		<p class="dashboard_tag left"> Code: </p> -->
<!--                     		<p id="diag_code_4" style=" display: inline-block; clear: both; width: 100%;">
                    		<?php 
//                     			echo $code;
//                     		?>
<!--                     		</p> -->
<!--                     	</div> -->
                    	
<!-- 				</div>                     -->
<!-- 			</div> -->
			
		</div>
	</div>
</div>

<div class="modal fade" id="confirm_appo_change_window" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        
        		<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						<h4 class="modal-title" id="myModalLabel">Change Appointment Type</h4>
			<?php 
				        break;
					
					case("es"):
			?>
						<h4 class="modal-title" id="myModalLabel">Cambiar Tipo de Cita</h4>
			<?php 
						break;
				}
			?>
			      </div>
		
			      <div class="modal-body">
			        <p>
                		<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						Do you really wish to edit the appointment type? This might cause your patient to pay a consult price difference between these types.
			<?php 
				        break;
					
					case("es"):
			?>
						¿Desea cambiar el tipo de cita? Esto puede causar que tu paciente tenga que pagar la diferencia de precios entre los dos tipos de cita.
			<?php 
						break;
				}
			?>
	        
	        </p>
	      </div>

	      <div class="modal-footer">
	      <?php 
				switch ($lang){
						 		
					case("en"):
			?>
				        	<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
	       			 	<button type="button" class="btn btn-primary" id="confirm_edit_button">Continue</button>
			<?php 
				        break;
					
					case("es"):
			?>
				        	<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
	       			 	<button type="button" class="btn btn-primary" id="confirm_edit_button">Continuar</button>
			<?php 
						break;
				}
			?>

      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirm_plan" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Cancel"><span aria-hidden="true">&times;</span></button>
        		<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						<h4 class="modal-title" id="myModalLabel">Confirm the plan?</h4>
			<?php 
				        break;
					
					case("es"):
			?>
						<h4 class="modal-title" id="myModalLabel">¿Confirmar plan?</h4>
			<?php 
						break;
				}
			?>
	        
	      </div>
	
	      <div class="modal-body">
	        <?php 
				switch ($lang){
						 		
					case("en"):
			?>
						<p>Current plan:</p>
			<?php 
				        break;
					
					case("es"):
			?>
						<p>Plan actual:</p>
			<?php 
						break;
				}
			?>
	      	
	      	<div id="plan_preview">
	      	</div>
	      	<br>
	        <p>
		        <?php 
					switch ($lang){
							 		
						case("en"):
				?>
							Are you sure you wish to save this plan?<br>
				<?php 
					        break;
						
						case("es"):
				?>
							¿Desea guardar este plan? <br>
				<?php 
							break;
					}
				?>
	        		
	        </p>
	      </div>
	
	      <div class="modal-footer">
	        <?php 
				switch ($lang){
						 		
					case("en"):
			?>
			        		<button type="button" class="btn btn-default" data-dismiss="modal" name="cancel_plan" id="cancel_plan">Cancel</button>
	        				<button type="button" class="btn btn-primary" name="confirm_plan" id="confirm_plan">Save</button>
			<?php 
				        break;
					
					case("es"):
			?>
			        		<button type="button" class="btn btn-default" data-dismiss="modal" name="cancel_plan" id="cancel_plan">Cancelar</button>
	        				<button type="button" class="btn btn-primary" name="confirm_plan" id="confirm_plan">Guardar</button>
			
			<?php 
						break;
				}
			?>

      </div>
    </div>
  </div>
</div>

</body>
</html>