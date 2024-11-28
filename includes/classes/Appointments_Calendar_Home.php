<?php  
include_once("Crypt.php");
	class Appointments_Calendar_Home{
		private $user_obj;
		private $con;
		private $appointments_calendar_patient;
		private $appointments_calendar_doctor;
		private $year;
		private $month;

		public function __construct($con, $user, $user_e, $year, $month){
			try{
				$this->con = $con;
				$this->user_obj = $temp_user_obj = new User($con, $user, $user_e);
			}	
			catch ( Exception $e ){
				$this->con = "";
				$this->$user_obj = "";
				throw new Exception( $e->getMessage() );
			}

			$month_t = date_create("0000-" . $month . "-15");
			$month_corr = date_format($month_t,"m");

			$this->month = $month_corr;
			$this->year = $year;

			//Patients and Doctors calendar selection
				//Doctor:
			if($this->user_obj->isDoctor()){
				$temp_table = $temp_user_obj->getAppointmentsCalendar();
			}
			else{
				$temp_table = $temp_user_obj->getAppointmentsCalendar_Patient();
			}
			//$temp_table = $temp_user_obj->getAppointmentsCalendar();

			
			if (false === ($stmt = $con->prepare("SELECT * FROM $temp_table WHERE year = ? AND month = ? ORDER BY year ASC, month ASC, day ASC, time_start ASC"))){
				throw new Exception( "Non-premium" );
			}
			elseif (!$stmt->bind_param("ss", $year, $month_corr)){
				throw new Exception( "Non-premium" );
			}
			elseif (!$stmt->execute()){
				throw new Exception( "Non-premium" );
			}
			
			$query = $stmt->get_result();
			$this->num_appoints_month = mysqli_num_rows($query);

			$temp_arr = [];
			while($row = mysqli_fetch_array($query)) {
				$temp_arr[] = $row;
			}
			
			if($this->user_obj->isDoctor()){
				$this->appointments_calendar_doctor = $temp_arr;
				$this->appointments_calendar_patient = [];
			}
			else{
				$this->appointments_calendar_patient = $temp_arr;
				$this->appointments_calendar_doctor = [];
			}
			

		}
		
		public function getAppointment_sDoctor($consult_id){
			//This method must be evoqued by the patient user 
			$appointments_details = $this->user_obj->getAppointmentsDetails_Patient();
			$stmt = $con->prepare("SELECT doctor_username FROM $appointments_details WHERE consult_id = ?");
			$stmt->bind_param("s", $consult_id);
			$stmt->execute();
			$query = $stmt->get_result();
			
			$doctor_username = mysqli_fetch_array($query)['doctor_username'];
			return $doctor_username;
		}
		
		public function getAppointment_sPatient($consult_id){
			//This method must be evoqued by the doctor user
			$appointments_details = $this->user_obj->getAppointmentsDetails_Patient();
			$stmt = $con->prepare("SELECT patient_username FROM $appointments_details WHERE consult_id = ?");
			$stmt->bind_param("s", $consult_id);
			$stmt->execute();
			$query = $stmt->get_result();
			
			$patient_username= mysqli_fetch_array($query)['patient_username'];
			return $patient_username;
		}
		
		public function getAppointment_sAppoType($consult_id){
			//This method must be evoqued by the patient user
			$appointments_details = $this->user_obj->getAppointmentsDetails_Patient();
			$stmt = $con->prepare("SELECT appo_type FROM $appointments_details WHERE consult_id = ?");
			$stmt->bind_param("s", $consult_id);
			$stmt->execute();
			$query = $stmt->get_result();
			
			$appo_type= mysqli_fetch_array($query)['appo_type'];
			$appo_type = str_replace('_', ' ', $appo_type);
			return $appo_type;
		}

		public function getAppointmentsCalendar($view_type){
			//Prints the calendar with appointments,
			$month = $this->month;
			$year = $this->year;

			$user = $this->user_obj->getUsername();

			$selected_calendar_view = ($view_type == 1) ? $this->appointments_calendar_doctor : $this->appointments_calendar_patient;

			$selected_calendar_by_day = [];
			foreach ($selected_calendar_view as $key_tt => $element_day){
				$selected_calendar_by_day[] = $element_day['day'];
			}
			//print_r($selected_calendar_by_day);
			$str="";

			$stmt = $this->con->prepare("SELECT * FROM calendar_table WHERE m = ? AND y = ? ORDER BY d ASC");
			$stmt->bind_param("ii", $month, $year);

			$stmt->execute();
			$month_query = $stmt->get_result();
			$month_size = mysqli_num_rows($month_query);



			$prev_month = $month - 1;
			$stmt->bind_param("ii", $prev_month ,$year);
			$stmt->execute();
			$prev_month_query = $stmt->get_result();
			$prev_month_size = mysqli_num_rows($prev_month_query);



			$next_month = $month + 1;
			$stmt->bind_param("ii", $next_month ,$year);
			$stmt->execute();
			$next_month_query = $stmt->get_result();

			$stmt->close();

			$prev_month_once = 0;
			foreach ($month_query as $key => $element) {
				$week_id_year = $element['y'];
				$week_id_week = $element['w'];
				if($key == 0){
					$start_day = $element['dw'];
				}

				if($key == $month_size-1){
					$last_day = $element['dw'];
				}

				$day_week_pointer = $element['dw'];
				$day_t = date_create("0000-" . "05" . "-" . $element['d']);
				$day_pointer = date_format($day_t,"d");

				if($day_week_pointer == 1 && $prev_month_once == 1){
					$str = $str . "<div class='calendar_week_block_no_hov'>";
				}

				if(/*$day_pointer < $start_day &&*/ $prev_month_once == 0){//Only executed once, adds previous month days
					$str = $str . "<div class='calendar_week_block_no_hov'>";

					foreach ($prev_month_query as $key2 => $element2) {
						
						if($element2['d'] > $prev_month_size-($start_day-1)){
							$str = $str . "<div class='calendar_day_block' id='empt'>" /*. $element2['d']*/ . "</div>";
						}
					}
				}//Only executed once


				$prev_month_once = 1;

				if(in_array($day_pointer, $selected_calendar_by_day)){
					$booked_class = "booked_day";
				}
				else{
					$booked_class = "unbooked_day";
				}
				
				if($booked_class == "booked_day"){
					$str = $str . 
						<<<EOL
							<a href="javascript:void(0);" onclick="selectDayHome('$year','$month','$day_pointer','$view_type')" style=" width: calc(100% /7);">
EOL;
				}
				
				$str = $str . "<div class='calendar_day_block " . $booked_class . " not_empt' id='day_" . $year . "_" . $month . "_" . $day_pointer . "'><p>" . $element['d'] . "</p></div>";

				if($booked_class == "booked_day")
					$str = $str . "</a>";

				if($day_week_pointer == 7){
					$str = $str . "</div>";
				}

			}


			foreach ($next_month_query as $key => $element) {
				
				if($element['d'] <= 7-$last_day){
					$str = $str . "<div class='calendar_day_block' id='empt'>" . /*$element['d'] .*/ "</div>";
				}
			}

			if(0 != 7-$last_day){
				$str = $str . "</a></div>";
			}

			return $str . "<input type='hidden' id='selected_day_inp' name='selected_day_inp' value=''>
							<input type='hidden' id='selected_month_inp' name='selected_month_inp' value=''>
							<input type='hidden' id='selected_year_inp' name='selected_year_inp' value=''>
						";
		}
		
		public function getAppointmentsCalendarLinks($view_type){
			//Prints the calendar with appointments, only here every day is clickable.
			$month = $this->month;
			$year = $this->year;
			
			$user = $this->user_obj->getUsername();
			
			$selected_calendar_view = ($view_type == 1) ? $this->appointments_calendar_doctor : $this->appointments_calendar_patient;
			
			$selected_calendar_by_day = [];
			foreach ($selected_calendar_view as $key_tt => $element_day){
				$selected_calendar_by_day[] = $element_day['day'];
			}
			//print_r($selected_calendar_by_day);
			$str="";
			
			$stmt = $this->con->prepare("SELECT * FROM calendar_table WHERE m = ? AND y = ? ORDER BY d ASC");
			$stmt->bind_param("ii", $month, $year);
			
			$stmt->execute();
			$month_query = $stmt->get_result();
			$month_size = mysqli_num_rows($month_query);
			
			
			$prev_month = $month - 1;
			$stmt->bind_param("ii", $prev_month ,$year);
			$stmt->execute();
			$prev_month_query = $stmt->get_result();
			$prev_month_size = mysqli_num_rows($prev_month_query);
			
			
			$next_month = $month + 1;
			$stmt->bind_param("ii", $next_month ,$year);
			$stmt->execute();
			$next_month_query = $stmt->get_result();
			
			$stmt->close();
			
			$prev_month_once = 0;
			foreach ($month_query as $key => $element) {
				$week_id_year = $element['y'];
				$week_id_week = $element['w'];
				if($key == 0){
					$start_day = $element['dw'];
				}
				
				if($key == $month_size-1){
					$last_day = $element['dw'];
				}
				
				$day_week_pointer = $element['dw'];
				$day_t = date_create("0000-" . "05" . "-" . $element['d']);
				$day_pointer = date_format($day_t,"d");
				
				if($day_week_pointer == 1 && $prev_month_once == 1){
					$str = $str . "<div class='calendar_week_block_no_hov'>";
				}
				
				if(/*$day_pointer < $start_day &&*/ $prev_month_once == 0){//Only executed once, adds previous month days
					$str = $str . "<div class='calendar_week_block_no_hov'>";
					
					foreach ($prev_month_query as $key2 => $element2) {
						
						if($element2['d'] > $prev_month_size-($start_day-1)){
							$str = $str . "<div class='calendar_day_block' id='empt'>" /*. $element2['d']*/ . "</div>";
						}
					}
				}//Only executed once
				
				
				$prev_month_once = 1;
				
				if(in_array($day_pointer, $selected_calendar_by_day)){
					$booked_class = "booked_day";
				}
				else{
					$booked_class = "unbooked_day";
				}
				

				$str = $str .
				<<<EOL
						<a href="javascript:void(0);" onclick="selectDayHome('$year','$month','$day_pointer','$view_type')" style=" width: calc(100% /7);">
EOL;
				
				
				$str = $str . "<div style=' width: 100%;' class='calendar_day_block " . $booked_class . " not_empt' id='day_" . $year . "_" . $month . "_" . $day_pointer . "'><p>" . $element['d'] . "</p></div>";
				

				$str = $str . "</a>";
					
				if($day_week_pointer == 7){
					$str = $str . "</div>";
				}
					
			}
			
			
			foreach ($next_month_query as $key => $element) {
				
				if($element['d'] <= 7-$last_day){
					$str = $str . "<div class='calendar_day_block' id='empt'>" . /*$element['d'] .*/ "</div>";
				}
			}
			
			if(0 != 7-$last_day){
				$str = $str . "</a></div>";
			}
			
			return $str . "<input type='hidden' id='selected_day_inp' name='selected_day_inp' value=''>
							<input type='hidden' id='selected_month_inp' name='selected_month_inp' value=''>
							<input type='hidden' id='selected_year_inp' name='selected_year_inp' value=''>
						";
		}
		
		public function getDay_Home($day_p,$view_type){
			
			$lang = $_SESSION['lang'];
			//This function prints the times booked on a particlar selected day.
			//$view_type = 1;
			
			$year = $this->year;
			$month = $this->month;
			
			$today_ = new DateTime();
			$today_day = $today_->format("d");
			$today_month = $today_->format("m");
			$today_year = $today_->format("Y");
			$today_hour = $today_->format("H:i");
			//$today_->add(new DateInterval('PT2H'));
			
			$selected_calendar_view = ($view_type == 1) ? $this->appointments_calendar_doctor : $this->appointments_calendar_patient;
			$txtreplace = new TxtReplace();
			
			$day_t = date_create("0000-" . "05" . "-" . $day_p);
			$day_pointer = date_format($day_t,"d");
			
			$str = "";
			
			foreach($selected_calendar_view as $key => $day_element){
				
				if ($day_element['day'] == $day_pointer){
					$time_start = $day_element['time_start'];
					$time_end = $day_element['time_end'];
					
					$time_start_f = date('g:i a', strtotime($time_start));
					$time_end_f = date('g:i a', strtotime($time_end));
					
					$consult_id = $day_element['consult_id'];
					$crypt = new Crypt();
					$id_e = $crypt->EncryptU($this->user_obj->getUsername());
					$appointments_master = new Appointments_Master($this->con, $this->user_obj->getUsername(),$id_e);
					
					if($view_type == 2){

						$id = $appointments_master->getPatient_view_Doctor($consult_id);
						$id_e = $crypt->EncryptU($id);
						$id_e_h = bin2hex($id_e);
						$id_obj = new User($this->con, $id, $id_e);
						$name_ini = $id_obj->getFirstNameShort(15);
						$last_name_s = $id_obj->getLastNameShort(15);
						$user_str = <<<EOS
									<b>Doctor</b><br> 
									<a href="javascript:void(0);" onclick="jumpToProfile('$id_e_h');">
EOS;
						$user_str .= $txtreplace->entities($name_ini) . " " . $txtreplace->entities($last_name_s) ."</a><br>";
						
						switch ($lang){
							
							case("en"):
								if($appointments_master->patient_view_IsConfirmed($consult_id))
									$confirmed_str = "<span class='text_passive'>Confirmed</span>";
								else{
									$confirmed_str = " <a href='javascript:void(0);' " .
												<<<EOS
													 onclick='jumpPatAppoViewer("$consult_id")'>
                                                    <span class='text_alert'>Requires confirmation!.</span></a>
EOS;
								}
								break;
				
							case("es"):
								if($appointments_master->patient_view_IsConfirmed($consult_id))
									$confirmed_str = "<span class='text_passive'>Confirmado</span>";
								else{
									$confirmed_str = " <a href='javascript:void(0);' " .
												<<<EOS
													 onclick='jumpPatAppoViewer("$consult_id")'>
                                                     <span class='text_alert'>¡Necesitas confirmar!</span></a>
EOS;
								}
								break;
					 	}
					 	
					}
					elseif($view_type == 1){
						try{
							$id = $appointments_master->getDoctor_view_Patient($consult_id);
							$id_e = $crypt->EncryptU($id);
							$id_e_h = bin2hex($id_e);
							$id_obj = new User($this->con, $id, $id_e);
							$name_ini = $id_obj->getFirstNameShort(15);
							$last_name_s = $id_obj->getLastNameShort(15);
							switch ($lang){
								
								case("en"):
									$user_str = "<b>Patient</b><br><a>" . $txtreplace->entities($name_ini) . " " .  $txtreplace->entities($last_name_s) ."</a><br>";
									break;
					
								case("es"):
									$user_str = "<b>Paciente</b><br><a>" . $txtreplace->entities($name_ini) . " " .  $txtreplace->entities($last_name_s) ."</a><br>";
									break;
						 	}
							
						 	switch ($lang){
						 		
						 		case("en"):
						 			if($appointments_master->doctor_view_IsConfirmed($consult_id))
						 				$confirmed_str = "<span class='text_passive'>Confirmed</span>";
						 				else{
						 					$confirmed_str = " <a href='javascript:void(0);' " .
								 					<<<EOS
													 onclick='doctorConfirmAppointment("$consult_id")'>
                                                    <span id='conf_$consult_id' class='text_alert'>Requires confirmation!.</span></a>
EOS;
						 				}
						 				break;
						 				
						 		case("es"):
						 			if($appointments_master->doctor_view_IsConfirmed($consult_id))
						 				$confirmed_str = "<span class='text_passive'>Confirmado</span>";
						 				else{
						 					$confirmed_str = " <a href='javascript:void(0);' " .
								 					<<<EOS
													 onclick='doctorConfirmAppointment("$consult_id")'>
                                                     <span id='conf_$consult_id' class='text_alert'>¡Necesitas confirmar!</span></a>
EOS;
						 				}
						 				break;
						 	}
							$external = 0;
						}
						catch ( Exception $e ){
							$id = $appointments_master->getDoctor_view_Patient($consult_id);
							
							$doctor_user_obj = $this->user_obj;
							$ext_pat_tab = $doctor_user_obj->getExternalPatients_Tab();
							
							$stmt = $this->con->prepare("SELECT `name` FROM $ext_pat_tab WHERE `username` = ?");
							$stmt->bind_param("s",$id);
							$stmt->execute();
							
							$q_ext = $stmt->get_result();
							$arr_ext = mysqli_fetch_assoc($q_ext);
							
							$length = 15;
							
							if(strlen($arr_ext['name']) >= $length)
								$show_first= substr($arr_ext['name'], 0, $length) . "...";
							else
								$show_first = $arr_ext['name'];
							
							$show_first = ucwords($show_first);
							
							switch ($lang){
								
								case("en"):
									$user_str = "<b>Patient (Non-ConfiDr.)</b><br><a>" . $txtreplace->entities($show_first) ."</a><br>";
									break;
									
								case("es"):
									$user_str = "<b>Paciente (No-ConfiDr.)</b><br><a>" . $txtreplace->entities($show_first) ."</a><br>";
									break;
							}
							
							
							$confirmed_str = "";
							$external = 1;
						}
					}
					
					if($view_type == 2){
						
						if(strtotime($today_year."-".$today_month."-".$today_day." ".$today_hour) > strtotime($year."-".$month."-".$day_p." ".$time_end)){
							$isPast = 1;
						}
						else{
							$isPast = 0;
						}
						
						$str .= "<div class='appointment_container_box'>";
						
						switch ($lang){
							
							case("en"):
								$str .=
								<<<EOS
						<a href="javascript:void(0);" onclick="jumpPatAppoViewer('$consult_id');">
							<div class='deep_blue appo_small_butt'>More Details</div>
						</a>
EOS;
								break;
								
							case("es"):
								$str .=
								<<<EOS
						<a href="javascript:void(0);" onclick="jumpPatAppoViewer('$consult_id');">
							<div class='deep_blue appo_small_butt'>Más Detalles</div>
						</a>
EOS;
								break;
						}
						
						
						
						if($isPast){
						    switch ($lang){
						        
						        case("en"):
							$str .=
							<<<EOS
							<a href="javascript:void(0);" onclick="jumpPatAward('$consult_id');">
								<div class='deep_blue award_small_butt'><span class='tip'>Give Award</span></div>
							</a>
EOS;
						    break;
						        case("es"):
						            $str .=
						            <<<EOS
							<a href="javascript:void(0);" onclick="jumpPatAward('$consult_id');">
								<div class='deep_blue award_small_butt'><span class='tip'>Otorgar Premio</span></div>
							</a>
EOS;
						            break;
						    }        
						}
					}
					elseif($view_type == 1){
						
						switch ($lang){
							
							case("en"):
								
								if(!$external){
									
									$str .=
									<<<EOS
							<div class='appointment_container_box'>
								<a href="javascript:void(0);" onclick="jumpAppoDashboard('$consult_id');">
									<div class='deep_blue appo_small_butt'>View Information</div>
								</a>
EOS;
								}
								else{
									
									$str .=
									<<<EOS
							<div class='appointment_container_box'>
								<a href="javascript:void(0);" onclick="viewExtAppoDetails('$consult_id');">
									<div class='deep_blue appo_small_butt'>View Information</div>
								</a>
EOS;
									
								}
								
								break;
								
							case("es"):
								
								if(!$external){
									
									$str .=
									<<<EOS
							<div class='appointment_container_box'>
								<a href="javascript:void(0);" onclick="jumpAppoDashboard('$consult_id');">
									<div  class='deep_blue appo_small_butt'>Ver Información</div>
								</a>
EOS;
								}
								else{
									
									$str .=
									<<<EOS
							<div class='appointment_container_box'>
								<a href="javascript:void(0);" onclick="viewExtAppoDetails('$consult_id');">
									<div class='deep_blue appo_small_butt'>Ver Información</div>
								</a>
EOS;
									
								}
								
								break;
						}
						
					}
					
					$str .= "<div class='appo_profile_pic' >";
					if($view_type == 2){
						$str .= <<<EOS
						<a href="javascript:void(0);" onclick="jumpToProfile('$id_e_h');">
EOS;
					}
					
					if(isset($external)){
						if(!$external){
							$str .= "<img src='" . $txtreplace->entities($id_obj->getProfilePicFast()) . "'>";
						}else{
							$str .= "<img src='assets/images/profile_pics/defaults/default_non_registered.png'>";
						}
					}
					else{
						$str .= "<img src='" . $txtreplace->entities($id_obj->getProfilePicFast()) . "'>";
					}
					if($view_type == 2){
						$str .=	"</a>";
					}
					
					switch ($lang){
						
						case("en"):
							$str .= "	</div><div class='info_doc_schedule'>
    								<div class='patient_data'>
    									<p>" .
    									$user_str .
    									"</p>
    									</div>".
    									$confirmed_str .
    									<<<EOS
    									
    								<div class='profile_schedule_selector_element free_time_element'>
    									<p> Start-End</br>
    									<a id="consult_open" href="javascript:void(0);" onclick="consultAppointment('$consult_id','$view_type')">
    									  $time_start_f - $time_end_f
    									</a></p>
    								</div>
                                </div>
							</div>
EOS;
							break;
			
						case("es"):
							$str .= "	</div><div class='info_doc_schedule'>
    								<div class='patient_data'>
    									<p>" .
    									$user_str .
    									"</p>
    									</div>".
    									$confirmed_str .
    									<<<EOS
    								
    								<div class='profile_schedule_selector_element free_time_element'>
    									<p> Inicio-Fin</br>
    									<a id="consult_open" href="javascript:void(0);" onclick="consultAppointment('$consult_id','$view_type')">
    									  $time_start_f - $time_end_f
    									</a></p>
    								</div>
                                </div>
							</div>
EOS;
							break;
				 	}
					
				}				
			}	
			if($str == ""){
				switch ($lang){
					
					case("en"):
						$str = "<b>No appointments scheduled for today.</b>";
						break;
		
					case("es"):
						$str = "<b>No hay citas agendadas para hoy.</b>";
						break;
			 	}
				
			}
			return $str;
		}
		
		public function getDay_Home_Frame($day_p,$view_type,$today=TRUE){
			
			$lang = $_SESSION['lang'];
			
			//This function prints the times booked on a particlar selected day.
			//$view_type = 1;
			$year = $this->year;
			$month = $this->month;
			$selected_calendar_view = ($view_type == 1) ? $this->appointments_calendar_doctor : $this->appointments_calendar_patient;
			$txtreplace = new TxtReplace();
			
			$day_t = date_create("0000-" . "05" . "-" . $day_p);
			$day_pointer = date_format($day_t,"d");
			
			$str = "";
			$intervals = "";
			$list_num = 0;
			foreach($selected_calendar_view as $key => $day_element){
				
				if ($day_element['day'] == $day_pointer){
					$time_start = $day_element['time_start'];
					$time_end = $day_element['time_end'];
					
					$time_start_f = date('g:i a', strtotime($time_start));
					$time_end_f = date('g:i a', strtotime($time_end));
					
					$consult_id = $day_element['consult_id'];
					
					$appointments_master = new Appointments_Master($this->con, $this->user_obj->getUsername(), $this->user_obj->getUsernameE());
					
					if($view_type == 2){
						$id = $appointments_master->getPatient_view_Doctor($consult_id);
						$crypt = new Crypt();
						$id_e = $crypt->EncryptU($id);
						$id_e_h = bin2hex($id_e);
						$id_obj = new User($this->con, $id, $id_e);
						$name_ini = $id_obj->getFirstNameShort(15);
						$last_name_s = $id_obj->getLastNameShort(15);
						$user_str = <<<EOS
									<b>Doctor</b><br>
									<a style='font-size:12px;' href="javascript:void(0);" onclick="jumpToProfile('$id_e_h');">
EOS;
						$user_str .= $txtreplace->entities($name_ini) . " " . $txtreplace->entities($last_name_s) ."</a><br>";
						
						switch ($lang){
							
							case("en"):
								if($appointments_master->patient_view_IsConfirmed($consult_id))
									$confirmed_str = "<span style='font-size:11px;'class='text_passive'>Confirmed</span>";
									else{
									    $confirmed_str = " <a href='javascript:void(0);' " .
													    <<<EOS
													 onclick='jumpPatAppoViewer("$consult_id")'>
                                                    <span style='width:58%; height: 19px;font-size:11px;right: 42%;' class='text_alert'>Confirm Here!</span></a>
EOS;
									}
									break;
									
							case("es"):
							    if($appointments_master->patient_view_IsConfirmed($consult_id))
							        $confirmed_str = "<span style='font-size:11px;'class='text_passive'>Confirmado</span>";
							        else{
							            $confirmed_str = " <a href='javascript:void(0);' " .
											            <<<EOS
													 onclick='jumpPatAppoViewer("$consult_id")'>
                                                     <span style='width:58%; height: 19px;font-size:11px;right: 42%;' class='text_alert'>Confirmar Aqui</span></a>
EOS;
									}
									break;
						}
						
					}
					elseif($view_type == 1){
						try{
							$id = $appointments_master->getDoctor_view_Patient($consult_id);
							$crypt = new Crypt();
							$id_e = $crypt->EncryptU($id);
							$id_obj = new User($this->con, $id, $id_e);
							$name_ini = $id_obj->getFirstNameShort(15);
							$last_name_s = $id_obj->getLastNameShort(15);
							switch ($lang){
								
								case("en"):
									$user_str = "<b>Patient</b><br><a style='font-size:12px;'>" . $txtreplace->entities($name_ini) . " " .$txtreplace->entities($last_name_s) ."</a><br>";
									break;
								case("es"):
									$user_str = "<b>Paciente</b><br><a style='font-size:12px;'>" . $txtreplace->entities($name_ini) . " " .$txtreplace->entities($last_name_s) ."</a><br>";
									break;
							}
							
							switch ($lang){
								
								case("en"):
									if($appointments_master->doctor_view_IsConfirmed($consult_id))
										$confirmed_str = "<span id='conf_". $consult_id ."'style='right:49%; width:47%; height: 19px;font-size:11px;' class='text_passive'>Confirmed</span>";
										else{
											$confirmed_str = " <a href='javascript:void(0);' " .
													<<<EOS
													 onclick='doctorConfirmAppointment("$consult_id")'>
                                                    <span id='conf_$consult_id' style='right:49%; width:47%; height: 19px;font-size:11px;' class='text_alert'>Confirm Here!</span></a>
EOS;
										}
										break;
										
								case("es"):
									if($appointments_master->doctor_view_IsConfirmed($consult_id))
										$confirmed_str = "<span id='conf_". $consult_id ."'style='right:49%; width:47%; height: 19px;font-size:11px;' class='text_passive'>Confirmado</span>";
										else{
											$confirmed_str = " <a href='javascript:void(0);' " .
													<<<EOS
													 onclick='doctorConfirmAppointment("$consult_id")'>
                                                     <span id='conf_$consult_id' style='right:49%; width:47%; height: 19px;font-size:11px;'  class='text_alert'>Confirmar Aqui</span></a>
EOS;
										}
										break;
							}
							//$confirmed_str = "";
							$external = 0;
						}
						catch ( Exception $e ){
							$id = $appointments_master->getDoctor_view_Patient($consult_id);
							$id_e = $crypt->EncryptU($id);
							$id_e_h = bin2hex($id_e);
							
							$doctor_user_obj = $this->user_obj;
							$ext_pat_tab = $doctor_user_obj->getExternalPatients_Tab();
							
							$stmt = $this->con->prepare("SELECT `name` FROM $ext_pat_tab WHERE `username` = ?");
							$stmt->bind_param("s",$id);
							$stmt->execute();
							
							$q_ext = $stmt->get_result();
							$arr_ext = mysqli_fetch_assoc($q_ext);
							
							$length = 15;
							
							if(strlen($arr_ext['name']) >= $length)
								$show_first= substr($arr_ext['name'], 0, $length) . "...";
								else
									$show_first = $arr_ext['name'];
									
									$show_first = ucwords($show_first);
									
									switch ($lang){
										
										case("en"):
											$user_str = "<b>Patient (Non-ConfiDr.)</b><br><a>" . $txtreplace->entities($show_first) ."</a><br>";
											break;
											
										case("es"):
											$user_str = "<b>Paciente (No-ConfiDr.)</b><br><a>" . $txtreplace->entities($show_first) ."</a><br>";
											break;
									}
									$confirmed_str = "";
									$external = 1;
						}
					}
					
					if($view_type == 2){
						
						switch ($lang){
							
							case("en"):
								$str .=
								<<<EOS
							<div class='appointment_container_box' id='list_num_$list_num'>
								<a href="javascript:void(0);" onclick="jumpPatAppoViewer('$consult_id');">
									<div style='width:34%; height: 19px;font-size:11px;' class='deep_blue appo_small_butt'>More Details</div>
								</a>
EOS;
								break;
								
							case("es"):
								$str .=
								<<<EOS
							<div class='appointment_container_box' id='list_num_$list_num'>
								<a href="javascript:void(0);" onclick="jumpPatAppoViewer('$consult_id');">
									<div style='width:34%; height: 19px;font-size:11px;' class='deep_blue appo_small_butt'>Más Detalles</div>
								</a>
EOS;
								break;
						}
					}
					elseif($view_type == 1){
						if(!$external){
							switch ($lang){
								
								case("en"):
									$str .=
									<<<EOS
							<div class='appointment_container_box_rightbar''>
								<a href="javascript:void(0);" onclick="jumpAppoDashboard('$consult_id');">
									<div style='width:40%;  height: 19px;font-size:11px;' class='deep_blue appo_small_butt'>View Information</div>
								</a>
EOS;
									break;
									
								case("es"):
									$str .=
									<<<EOS
							<div class='appointment_container_box_rightbar'>
								<a href="javascript:void(0);" onclick="jumpAppoDashboard('$consult_id');">
									<div style='width:40%; height: 19px; font-size:11px; ' class='deep_blue appo_small_butt'>Ver Información</div>
								</a>
EOS;
									break;
							}
							
						}
						else{
							
							switch ($lang){
								
								case("en"):
									$str .=
									<<<EOS
							<div class='appointment_container_box_rightbar'>
								<a href="javascript:void(0);" onclick="viewExtAppoDetails('$consult_id');">
									<div style='width:40%;  height: 19px;font-size:11px;' class='deep_blue appo_small_butt'>View Information</div>
								</a>
EOS;
									break;
									
								case("es"):
									$str .=
									<<<EOS
							<div class='appointment_container_box_rightbar'>
								<a href="javascript:void(0);" onclick="viewExtAppoDetails('$consult_id');">
									<div style='width:40%; height: 19px; font-size:11px; ' class='deep_blue appo_small_butt'>Ver Información</div>
								</a>
EOS;
									break;
							}
						}
					}
					
					$str .= "<div class='appo_profile_pic'>";
					if($view_type == 2){
						$str .= <<<EOS
							<a href="javascript:void(0);" onclick="jumpToProfile('$id_e_h');">
EOS;
					}
					
					if(isset($external)){
						if(!$external){
							$str .= "<img style='height: 40px;width: 40px;' src='" . $txtreplace->entities($id_obj->getProfilePicFast()) . "' >";
						}else{
							$str .= "<img style='height: 40px;width: 40px;' src='assets/images/profile_pics/defaults/default_non_registered.png'>";
						}
					}
					else{
						$str .= "<img style='height: 40px;width: 40px;' src='" . $txtreplace->entities($id_obj->getProfilePicFast()) . "'>";
					}
					
					if($view_type == 2){
						$str .=	"</a>";
					}
					
					switch ($lang){
					    
					    case("en"):
					        $str .= "	</div><div class='info_doc_schedule'>
    								<div class='patient_data'>
    									<p style='padding-top:11px; line-height: 12px;'>" .
    									$user_str .
    									"</p>
    									</div>".
    									$confirmed_str .
    									<<<EOS
    									
    								<div style='padding-top:10px;'class='profile_schedule_selector_element free_time_element'>
    									<p> Start-End</br>
    									<a style='font-size:11px;'id="consult_open" href="javascript:void(0);" onclick="consultAppointment('$consult_id','$view_type')">
    									  $time_start_f - $time_end_f
    									</a></p>
    								</div>
                                </div>
							</div>
EOS;
    									  break;
    									  
					    case("es"):
					        $str .= "	</div><div class='info_doc_schedule'>
    								<div class='patient_data'>
    									<p style='padding-top:11px; line-height: 12px;'>" .
    									$user_str . 
    									"</p>
    									</div>".
    									$confirmed_str .
    									<<<EOS
    									
    								<div style='padding-top:10px;'class='profile_schedule_selector_element free_time_element'>
    									<p> Inicio-Fin</br>
    									<a style='font-size:11px;' id="consult_open" href="javascript:void(0);" onclick="consultAppointment('$consult_id','$view_type')">
    									  $time_start_f - $time_end_f
    									</a></p>
    								</div>
                                </div>
							</div>
EOS;
    									  break;
					}
					
					
					$intervals .= $time_start . "-" . $time_end . "-" . $list_num . "-" . $consult_id .",";
					$list_num++;
				}
			}
			
			if($str == ""){
				switch ($lang){
					
					case("en"):
						$str = "<b>No appointments scheduled.</b>";
						break;
		
					case("es"):
					    $str = "<b>No hay citas programadas.</b>";
						break;
			 	}
			}
			
			if($today){
				$str .= "<input type='hidden' name='intervals' value='" . $intervals . "'>";
			}
			return $str;
		}
		
		public function getDay_Home_old($day_p,$view_type){ //DEPRECATED
			
			$lang = $_SESSION['lang'];
			
			//This function prints the times booked on a particlar selected day.
			//$view_type = 1;
			$year = $this->year;
			$month = $this->month;
			$selected_calendar_view = ($view_type == 1) ? $this->appointments_calendar_patient : $this->appointments_calendar_doctor ;
			
			$day_t = date_create("0000-" . "05" . "-" . $day_p);
			$day_pointer = date_format($day_t,"d");
			$print_str = "";
			foreach($selected_calendar_view as $key => $day_element){

				if ($day_element['day'] == $day_pointer){
					$time_start = $day_element['time_start'];
					$time_end = $day_element['time_end'];
					
					$time_start_f = date('g:i a', strtotime($time_start));
					$time_end_f = date('g:i a', strtotime($time_end));
					
					$consult_id = $day_element['consult_id'];
					$print_str = $print_str . '<div class="profile_schedule_selector_element free_time_element" id="hour_' . $year . '_' . $month . '_' . $day_pointer . '_' . $time_start . '">' . 
					<<<EOL
						<a href="javascript:void(0);" onclick="consultAppointment('$consult_id','$view_type')">
							$time_start_f - $time_end_f
						</a>
					</div>
EOL;
				}
			}

			if($print_str == ""){
				switch ($lang){
					
					case("en"):
						$print_str = "No appointments scheduled for today.";
						break;
		
					case("es"):
						$print_str = "No hay citas programadas para hoy.";
						break;
			 	}
				
			}
			return $print_str;
		}
		
	}
?>