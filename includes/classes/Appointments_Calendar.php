<?php
include_once("Crypt.php");
class Appointments_Calendar{
	private $user_obj;
	private $con;
	private $my_appo_dur_tab;
	private $available_calendar_obj;
	private $appointments_calendar;
	private $num_appoints_month;
	public function __construct($con, $user, $user_e, $year, $month){
		try{
			//THE USER IN THIS CLASS IS THE DOCTOR, THIS IS HIS CALENDARS AND INFO
			$this->con = $con;
			$this->user_obj = $temp_user_obj = new User($con, $user, $user_e);
			$this->available_calendar_obj = new Calendar($con, $user, $user_e);
		}
		catch ( Exception $e ){
			$this->con = "";
			$this->$user_obj = "";
			throw new Exception( $e->getMessage() );
		}
		
		//TODO:COnstructor
		
		$month_t = date_create("0000-" . $month . "-15");
		$month_corr = date_format($month_t,"m");
		$this->month = $month_corr;
		$this->year = $year;
		$temp_table = $temp_user_obj->getAppointmentsCalendar();
		$stmt = $con->prepare("SELECT * FROM $temp_table WHERE year = ? AND month = ? ORDER BY consult_id ASC");
		$stmt->bind_param("ss", $year, $month_corr);
		$stmt->execute();
		$query = $stmt->get_result();
		$this->num_appoints_month = mysqli_num_rows($query);
		$temp_arr = [];
		while($row = mysqli_fetch_array($query)) {
			$temp_arr[] = $row;
		}
		$this->appointments_calendar = $temp_arr;
	}
	public function getAvailabilityCalendar($payment_method,$appo_dur,$appo_type_id){
		//prints the availability calendar for a month. Should be used in conjunction with an echo on the reurn of this function
		$month = $this->month;
		$year = $this->year;
		$available_calendar_obj = $this->available_calendar_obj;
		$user = $this->user_obj->getUsername();
		$user_e = $this->user_obj->getUsernameE();
		$user_e_b = bin2hex($user_e);
		$day_availaility_array = $this->getDayAvailability($payment_method,$appo_dur);
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
			if(array_key_exists($day_pointer, $day_availaility_array)){
				$ava_class = ($day_availaility_array[$day_pointer] 
						&& $available_calendar_obj->checkAvailabilityDay($payment_method,$day_pointer,$month,$year,$appo_dur) 
						&& ($this->getDay($day_pointer, $payment_method, $appo_dur, '') != '')) ? "available_day" : "unavailable_day" ;
			}
			else{
				if($available_calendar_obj->checkAvailabilityDay($payment_method,$day_pointer,$month,$year,$appo_dur))
					$ava_class = "available_day";
				else
					$ava_class = "unavailable_day";
			}
			
			if($ava_class == "available_day"){
				$str = $str .
				<<<EOL
							<a href="javascript:void(0);" onclick="selectDay4Booking('$year','$month','$day_pointer','$payment_method','$user_e_b','$appo_type_id')" style=" width: calc(100% /7);">
EOL;
			}
			
			$str = $str . "<div class='calendar_day_block " . $ava_class . " not_empt' id='day_" . $year . "_" . $month . "_" . $day_pointer . "'><p>" . $element['d'] . "</p></div>";
			if($ava_class == "available_day")
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
	public function getBookedTimes($day,$payment_method){
		$num_appoints_month = $this->num_appoints_month;
		$appointments_calendar = $this->appointments_calendar;
		$bookingArray = [];
		$day_t = date_create("0000-" . "05" . "-" . $day);
		$day_pointer = date_format($day_t,"d");
		for($i=0; $i<=$num_appoints_month-1; $i++){
			if ($appointments_calendar[$i]['day'] == $day_pointer){
				$temp_st = $appointments_calendar[$i]['time_start'];
				$temp_end = $appointments_calendar[$i]['time_end'];
				$bookingArray[$temp_st] = $temp_end;
			}
		}
		return $bookingArray;
	}
	public function getDay($day_p,$payment_method,$appointment_duration,$appointment_type,$office_arr = NULL){
		//This function prints the times available on a particlar selected day.
		
		$lang = $_SESSION['lang'];
		
		$day_t = date_create("0000-" . "05" . "-" . $day_p);
		$day = date_format($day_t,"d");
		$user = $this->user_obj->getUsername();
		$user_e = $this->user_obj->getUsernameE();
		$user_e_b = bin2hex($user_e);
		$month = $this->month;
		$year = $this->year;
		
		$today_ = new DateTime();
		$today_day = $today_->format("d");
		$today_month = $today_->format("m");
		$today_year = $today_->format("Y");
		$today_->add(new DateInterval('PT2H'));
		
		if(strtotime($today_year."-".$today_month."-".$today_day) == strtotime($year."-".$month."-".$day) && $today_->format("d") > strtotime("6:00")){
			
			$frac = 60*20;
			$r = strtotime($today_->format("H:i")) % $frac;
			$new_time = strtotime($today_->format("H:i")) + ($frac-$r);
			$start_time= date('G:i', $new_time);
			//$start_time = $temp_end_obj->format("G:i");
		}
		else{
			$start_time = "6:00";
		}
		
		$available_calendar_obj = $this->available_calendar_obj;
		//$start_time = "6:00";
		$end_time = "21:00";
		$minutes_per_block = 20;
		$blocks_in_an_hour = 60/$minutes_per_block;
		$var_time = strtotime($start_time);
		$dteStart = new DateTime($start_time);
		$dteEnd   = new DateTime($end_time);
		$hours_per_day = $dteStart->diff($dteEnd)->format("%H");
		$num_blocks = $hours_per_day*$blocks_in_an_hour;
		$bookingArray = $this->getBookedTimes($day,$payment_method);
		$print_str = "";
		$ending_time = "";
		for($i = 0; $i <= $num_blocks; $i++){
			$var_time_temp = $var_time + $minutes_per_block*60*$i;//60 seconds per each minute
			$var_time_temp_end = $var_time_temp + 60*$minutes_per_block;//60 seconds per each minute
			$var_time_temp_f = date('H:i', $var_time_temp);//this is the start of the interval
			
			$var_time_temp_end_f = date('H:i', $var_time_temp_end);//this is the end of the interval, currently has NO USE
			$var_time_appo_end = $var_time_temp + $appointment_duration*60;//60 seconds per each minute
			$var_time_appo_end_f = date('H:i', $var_time_appo_end);//this is the end of the appointment if it started at time var_time_temp_f
			$display_time = date("g:ia", $var_time_temp);
			/*if($ending_time != "" && $ending_time != $var_time_temp_f){
			 //$print_str = $print_str . "<div class='profile_schedule_selector_element booked_time'>" . $display_time . "</div>";
			 }
			 else*/
			if($ending_time != "" && $ending_time == $var_time_temp_f){
				$ending_time = "";
			}
			if($ending_time == ""){
				if(array_key_exists($var_time_temp_f,$bookingArray)){
					//$print_str = $print_str . "<div class='profile_schedule_selector_element booked_time'>" . $display_time . "</div>";
					$ending_time = $bookingArray[$var_time_temp_f];
				}
				else{
					$int_st = new DateTime($var_time_temp_f);
					$int_end = new DateTime($var_time_appo_end_f);
					
					switch ($lang){
						
						case("en"):
							if($office_arr == NULL){
								if( $available_calendar_obj->checkAvailabilityInterval($payment_method,$int_st,$int_end,$day,$month,$year) ){
									$print_str = $print_str .
									<<<EOL
							<a href="javascript:void(0);" onclick="selectTime4Booking('$year','$month','$day','$var_time_temp_f','$var_time_appo_end_f','$user_e_b','$payment_method','$appointment_type')">
								<div class="profile_schedule_selector_element free_time_element" id="hour_selected_{$year}_{$month}_{$day}_{$display_time}">
								 		$display_time <b> Avaliable Time</b>
								</div>
							</a>
EOL;
								}
							}
							else{
								if( $available_calendar_obj->checkAvailabilityIntervalWithOffices($payment_method,$int_st,$int_end,$day,$month,$year,$office_arr) ){
									$print_str = $print_str .
									<<<EOL
							<a href="javascript:void(0);" onclick="selectTime4Booking('$year','$month','$day','$var_time_temp_f','$var_time_appo_end_f','$user_e_b','$payment_method','$appointment_type')">
								<div class="profile_schedule_selector_office free_time_element" id="hour_selected_{$year}_{$month}_{$day}_{$display_time}">
								 		$display_time <b> Avaliable Time</b>
								</div>
							</a>
EOL;
								}
							}
							
							break;
							
						case("es"):
							if($office_arr == NULL){
								if( $available_calendar_obj->checkAvailabilityInterval($payment_method,$int_st,$int_end,$day,$month,$year) ){
									$print_str = $print_str .
									<<<EOL
							<a href="javascript:void(0);" onclick="selectTime4Booking('$year','$month','$day','$var_time_temp_f','$var_time_appo_end_f','$user_e_b','$payment_method','$appointment_type')">
								<div class="profile_schedule_selector_element free_time_element" id="hour_selected_{$year}_{$month}_{$day}_{$display_time}">
								 		$display_time <b> Disponible </b>
								</div>
							</a>
EOL;
								}
							}
							else{
								if( $available_calendar_obj->checkAvailabilityIntervalWithOffices($payment_method,$int_st,$int_end,$day,$month,$year,$office_arr) ){
									$print_str = $print_str .
									<<<EOL
							<a href="javascript:void(0);" onclick="selectTime4Booking('$year','$month','$day','$var_time_temp_f','$var_time_appo_end_f','$user_e_b','$payment_method','$appointment_type')">
								<div class="profile_schedule_selector_office free_time_element" id="hour_selected_{$year}_{$month}_{$day}_{$display_time}">
								 		$display_time <b> Disponible </b>
								</div>
							</a>
EOL;
								}
							}
							break;
					}
				}
			}
		}
		return $print_str;
	}
	
	public function getDaySelfBooking($day_p,$payment_method,$appointment_duration,$appointment_type,$office_arr = NULL){
		//This function prints the times available on a particlar selected day.
		
		$lang = $_SESSION['lang'];
		
		switch ($lang){
			case "en":
				$dayoftheweek = "days_short_eng";
				$monthnumbtoname = "months_eng";
				break;
			case "es":
				$dayoftheweek = "days_short_es";
				$monthnumbtoname = "months_es";
				break;
		}
		
		$day_t = date_create("0000-" . "05" . "-" . $day_p);
		$day = date_format($day_t,"d");
		$user = $this->user_obj->getUsername();
		$user_e = $this->user_obj->getUsernameE();
		$user_e_b = bin2hex($user_e);
		$month = $this->month;
		$year = $this->year;
		$available_calendar_obj = $this->available_calendar_obj;
		$start_time = "6:00";
		$end_time = "21:00";
		$minutes_per_block = 20;
		$blocks_in_an_hour = 60/$minutes_per_block;
		$var_time = strtotime($start_time);
		$dteStart = new DateTime($start_time);
		$dteEnd   = new DateTime($end_time);
		$hours_per_day = $dteStart->diff($dteEnd)->format("%H");
		//$hours_per_day = 15;// commented the following for it was causing an error:  $end_time-$start_time;
		$num_blocks = $hours_per_day*$blocks_in_an_hour;
		$bookingArray = $this->getBookedTimes($day,$payment_method);
		$print_str = "";
		$ending_time = "";
		for($i = 0; $i <= $num_blocks; $i++){
			$var_time_temp = $var_time + $minutes_per_block*60*$i;//60 seconds per each minute
			$var_time_temp_end = $var_time_temp + 60*$minutes_per_block;//60 seconds per each minute
			$var_time_temp_f = date('H:i', $var_time_temp);//this is the start of the interval
			$var_time_temp_end_f = date('H:i', $var_time_temp_end);//this is the end of the interval, currently has NO USE
			$var_time_appo_end = $var_time_temp + $appointment_duration*60;//60 seconds per each minute
			$var_time_appo_end_f = date('H:i', $var_time_appo_end);//this is the end of the appointment if it started at time var_time_temp_f
			$display_time = date("g:ia", $var_time_temp);
			/*if($ending_time != "" && $ending_time != $var_time_temp_f){
			 //$print_str = $print_str . "<div class='profile_schedule_selector_element booked_time'>" . $display_time . "</div>";
			 }
			 else*/if($ending_time != "" && $ending_time == $var_time_temp_f){
			 $ending_time = "";
			}
			if($ending_time == ""){
				if(array_key_exists($var_time_temp_f,$bookingArray)){
					//$print_str = $print_str . "<div class='profile_schedule_selector_element booked_time'>" . $display_time . "</div>";
					$ending_time = $bookingArray[$var_time_temp_f];
				}
				else{
					$int_st = new DateTime($var_time_temp_f);
					$int_end = new DateTime($var_time_appo_end_f);
					if($office_arr == NULL){
						//This is never used
						if( $available_calendar_obj->checkAvailabilityInterval($payment_method,$int_st,$int_end,$day,$month,$year) ){
							switch ($lang){
								
								case("en"):
									$print_str = $print_str .
									<<<EOL
									<a href="javascript:void(0);" onclick="selectTime4SelfBooking('$year','$month','$day','$var_time_temp_f','$var_time_appo_end_f','$user_e_b','$payment_method','$appointment_type','$display_time')">
										<div class="profile_schedule_selector_element free_time_element" id="hour_selected_{$year}_{$month}_{$day}_{$display_time}">
										 		$display_time <b> Avaliable Time</b>
										</div>
									</a>
EOL;
									break;
								
								case("es"):
									$print_str = $print_str .
									<<<EOL
									<a href="javascript:void(0);" onclick="selectTime4SelfBooking('$year','$month','$day','$var_time_temp_f','$var_time_appo_end_f','$user_e_b','$payment_method','$appointment_type','$display_time')">
										<div class="profile_schedule_selector_element free_time_element" id="hour_selected_{$year}_{$month}_{$day}_{$display_time}">
										 		$display_time <b> Disponible </b>
										</div>
									</a>
EOL;
									break;
							}
							
						}
					}
					else{
						if( $available_calendar_obj->checkAvailabilityIntervalWithOffices($payment_method,$int_st,$int_end,$day,$month,$year,$office_arr) ){
							$_temp_dt = $year."-".$month."-".$day;
							
							$stmt = $this->con->prepare("
								SELECT t1.d,t2.$dayoftheweek, t3.$monthnumbtoname 
								FROM calendar_table AS t1 LEFT JOIN (days_week AS t2, months as t3) 
								ON (t2.dw = t1.dw AND t3.id = t1.m) 
								WHERE t1.dt = ?
							");
							$stmt->bind_param("s", $_temp_dt);
							$stmt->execute();
							$dt_q = $stmt->get_result();
							$dt_arr= mysqli_fetch_array($dt_q);
							
							$display_date = $dt_arr[$dayoftheweek] .", " . $dt_arr['d'] . " / " . $dt_arr[$monthnumbtoname] . " / " . $year;
							
							switch ($lang){
								
								case("en"):
									$print_str = $print_str .
									<<<EOL
									<a href="javascript:void(0);" onclick="selectTime4SelfBooking('$year','$month','$day','$var_time_temp_f','$var_time_appo_end_f','$user_e_b','$payment_method','$appointment_type','$display_time','$display_date')">
										<div class="profile_schedule_selector_office free_time_element" id="hour_selected_{$year}_{$month}_{$day}_{$display_time}">
										 		$display_time <b> Avaliable Time </b>
										</div>
									</a>
EOL;
									break;
									
								case("es"):
									$print_str = $print_str .
									<<<EOL
									<a href="javascript:void(0);" onclick="selectTime4SelfBooking('$year','$month','$day','$var_time_temp_f','$var_time_appo_end_f','$user_e_b','$payment_method','$appointment_type','$display_time','$display_date')">
										<div class="profile_schedule_selector_office free_time_element" id="hour_selected_{$year}_{$month}_{$day}_{$display_time}">
										 		$display_time <b> Disponible </b>
										</div>
									</a>
EOL;
									break;
							}
						}
					}
				}
			}
		}
		
		return $print_str;
	}
	
	public function getDay_searchResults($day_p,$payment_method,$appointment_duration,$booking_info_array,$office_arr = NULL,$showAvTime = TRUE){
		//This function prints the times available on a particlar selected day on the search results page
		
		$lang = $_SESSION['lang'];
		
		
		$day_t = date_create("0000-" . "05" . "-" . $day_p);
		$day = date_format($day_t,"d");
		$user = $this->user_obj->getUsername();
		$month = $this->month;
		$year = $this->year;
		
		$today_ = new DateTime();
		$today_day = $today_->format("d");
		$today_month = $today_->format("m");
		$today_year = $today_->format("Y");
		$today_->add(new DateInterval('PT2H'));
		
		if(strtotime($today_year."-".$today_month."-".$today_day) == strtotime($year."-".$month."-".$day) && $today_->format("d") > strtotime("6:00")){
			$frac = 60*20;
			$r = strtotime($today_->format("H:i")) % $frac;
			$new_time = strtotime($today_->format("H:i")) + ($frac-$r);
			$start_time= date('G:i', $new_time);
			//$start_time = $temp_end_obj->format("G:i");
		}
		else{
			$start_time = "6:00";
		}
		
// 		echo $day;
// 		echo $month;
// 		echo $year;
		
		$available_calendar_obj = $this->available_calendar_obj;
		$end_time = "21:00";
		$minutes_per_block = 20;
		$blocks_in_an_hour = 60/$minutes_per_block;
		$var_time = strtotime($start_time);
		$dteStart = new DateTime($start_time);
		$dteEnd   = new DateTime($end_time);
		$hours_per_day = $dteStart->diff($dteEnd)->format("%H");
		//$hours_per_day = $end_time-$start_time;
		$num_blocks = $hours_per_day*$blocks_in_an_hour;
		$bookingArray = $this->getBookedTimes($day,$payment_method);
		$print_str = "";
		$ending_time = "";
		for($i = 0; $i <= $num_blocks; $i++){
			$var_time_temp = $var_time + $minutes_per_block*60*$i;//60 seconds per each minute
			$var_time_temp_end = $var_time_temp + 60*$minutes_per_block;//60 seconds per each minute
			$var_time_temp_f = date('H:i', $var_time_temp);//this is the start of the interval
			
			$var_time_temp_end_f = date('H:i', $var_time_temp_end);//this is the end of the interval, currently has NO USE
			$var_time_appo_end = $var_time_temp + $appointment_duration*60;//60 seconds per each minute
			$var_time_appo_end_f = date('H:i', $var_time_appo_end);//this is the end of the appointment if it started at time var_time_temp_f
			$display_time = date("g:ia", $var_time_temp);
			/*if($ending_time != "" && $ending_time != $var_time_temp_f){
			 //$print_str = $print_str . "<div class='profile_schedule_selector_element booked_time'>" . $display_time . "</div>";
			 }
			else*/
			if($ending_time != "" && $ending_time == $var_time_temp_f){
				$ending_time = "";
			}
			if($ending_time == ""){
				if(array_key_exists($var_time_temp_f,$bookingArray)){
					//$print_str = $print_str . "<div class='profile_schedule_selector_element booked_time'>" . $display_time . "</div>";
					$ending_time = $bookingArray[$var_time_temp_f];
				}
				else{
					//print_r($booking_info_array);
					$booking_info_array["time_st"] = $var_time_temp_f;
					$booking_info_array["time_end"] = $var_time_appo_end_f;
					$booking_info_array["display_time"] = $display_time;
					
					$jencoded_booking_info_array = json_encode($booking_info_array);
					
					$int_st = new DateTime($var_time_temp_f);
					$int_end = new DateTime($var_time_appo_end_f);
					if($office_arr == NULL){
						if( $available_calendar_obj->checkAvailabilityInterval($payment_method,$int_st,$int_end,$day,$month,$year) ){
							$print_str = $print_str .
							<<<EOL
								<a href='javascript:void(0);' onclick='selectTime4BookingSearchScreen({$jencoded_booking_info_array})'>
EOL;
							$print_str = $print_str . '<div class="search_sched_sele" id="hour_selected_' . $year . '_' . $month . '_' . $day . '_' . $display_time  . '" data-toggle="modal" data-target="#confirm_appo_sele">' . $display_time ;
							
							switch ($lang){
								
								case("en"):
									$print_str .= ($showAvTime)?"<b> Avaliable Slot</b>":"";
									break;
									
								case("es"):
									$print_str .= ($showAvTime)?"<b> Espacio Disponible </b>":"";
									break;
							
							}
							
							$print_str .= "</div>
										</a>";
							
						}
					}
					else{
						if( $available_calendar_obj->checkAvailabilityIntervalWithOffices($payment_method,$int_st,$int_end,$day,$month,$year,$office_arr) ){
							$print_str = $print_str .
							<<<EOL
								<a href='javascript:void(0);' onclick='selectTime4BookingSearchScreen({$jencoded_booking_info_array})'>
EOL;
							$print_str = $print_str . '<div class="search_sched_sele" id="hour_selected_' . $year . '_' . $month . '_' . $day . '_' . $display_time  . '" data-toggle="modal" data-target="#confirm_appo_sele">' . $display_time ;
								 
							switch ($lang){
								
								case("en"):
									$print_str .= ($showAvTime)?"<b> Avaliable Slot</b>":"";
									break;
								case("es"):
									$print_str .= ($showAvTime)?"<b> Espacio Disponible</b>":"";
									break;
							}
							$print_str .= "</div>
										</a>";
						}
					}
				}
			}
		}
		return $print_str;
	}
	
	public function getDayAvailability($payment_method,$appo_dur){
		//Function for creating a month array with the available days
		
		//NEEDS TO be used together with Calendar::checkAvailabilityDay as this function might not return a day not because it is available, but because it is not available for booking, thus the availability must be checked using the metnioned method
		$num_appoints_month = $this->num_appoints_month;
		// 			echo " User: " . $this->user_obj->getUsername();
		// 			echo " Month: " . $this->month;
		// 			echo " num appo month: " . $num_appoints_month;
		$appointments_calendar = $this->appointments_calendar;
		$available_calendar_obj = $this->available_calendar_obj;
		$month = $this->month;
		$year = $this->year;
		
		$minutes_per_block = 20;
		$blocks_in_an_hour = 60/$minutes_per_block;
		
		$day_availaility_array = [];
		
		$prev_day = 0;
		
		for($i=0; $i<$num_appoints_month; $i++){
			
			$day_obt = $appointments_calendar[$i]['day'];//THis gets each day number associated with an appointment. IMPORTANT: array MUST be sorted by date and time ascending
			
			if(!array_key_exists($day_obt, $day_availaility_array))
				$day_availaility_array[$day_obt] = 0;//not available for booking
				
				if(!$day_availaility_array[$day_obt]){
					
					if($prev_day != $day_obt){ //IDENTIFIES A CHANGE OF DAY, this has to excecute once at the start of a day
						$today_ = new DateTime();
						$today_day = $today_->format("d");
						$today_month = $today_->format("m");
						$today_year = $today_->format("Y");
						$today_->add(new DateInterval('PT2H'));
						
						if(strtotime($today_year."-".$today_month."-".$today_day) == strtotime($year."-".$month."-".$day_obt)){
							if(strtotime($today_->format("H:i")) > strtotime('06:00')){
								//If it's today, and later than 6am
								$frac = 60*20;
								$r = strtotime($today_->format("H:i")) % $frac;
								$new_time = strtotime($today_->format("H:i")) + ($frac-$r);
								$temp_end = date('H:i', $new_time);
								
// 								$temp_end = $temp_end_obj->format("H:i");
								
								if($new_time > strtotime($appointments_calendar[$i]['time_start'])){
									//the current examined appointment is earlier than the current time
									$day_availaility_array[$day_obt] = 0;
//									continue;
								}
								else{
									//the current examined appointment is later than the current time, thus it is plausible that there is an available gap
									$temp_st_next = $appointments_calendar[$i]['time_start']; //start of first appointment of the day
									$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindow($temp_end, $temp_st_next, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
									$prev_day = $day_obt;
								}
							}
							else{
								//If it's today, but earlier than 6am
								$temp_end = '06:00'; //start of the day
								$temp_st_next = $appointments_calendar[$i]['time_start']; //start of first appointment of the day
								$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindow($temp_end, $temp_st_next, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
								$prev_day = $day_obt;
							}
						}
						else{
							//If it's not today
							$temp_end = '06:00'; //start of the day
							$temp_st_next = $appointments_calendar[$i]['time_start']; //start of first appointment of the day
							$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindow($temp_end, $temp_st_next, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
							$prev_day = $day_obt;
						}
					}
					else{
						$temp_end = $appointments_calendar[$i-1]['time_end'];
						$temp_st_next = $appointments_calendar[$i]['time_start'];
						
						$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindow($temp_end, $temp_st_next, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
					}
					
					if(!$day_availaility_array[$day_obt]){
						if(array_key_exists($i+1,$appointments_calendar)){
							if($appointments_calendar[$i+1]['day'] != $day_obt){
								$temp_end = $appointments_calendar[$i]['time_end'];
								$temp_st_next = '21:30';
								$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindow($temp_end, $temp_st_next, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
								//$prev_day = $day_obt;
							}
						}
						else{
							$temp_end = $appointments_calendar[$i]['time_end'];
							$temp_st_next = '21:30';
							$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindow($temp_end, $temp_st_next, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
							//$prev_day = $day_obt;
						}
					}
				}
		}
		//echo "RESULTING ARRAY: ";
		return $day_availaility_array; //1 = available, not in list = available, 0 = not available
	}
	
	public function getAvailabilityCalendarWithOffices($payment_method,$appo_dur,$appo_type_id,$office_num){
		//prints the availability calendar for a month. Should be used in conjunction with an echo on the reurn of this function
		$month = $this->month;
		$year = $this->year;
		$available_calendar_obj = $this->available_calendar_obj;
		$user = $this->user_obj->getUsername();
		$user_e = $this->user_obj->getUsernameE();
		$user_e_b = bin2hex($user_e);
		$day_availaility_array = $this->getDayAvailabilityWithOffices($payment_method,$office_num,$appo_dur);
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
			if(array_key_exists($day_pointer, $day_availaility_array)){
				$ava_class = ($day_availaility_array[$day_pointer]
						&& $available_calendar_obj->checkAvailabilityDayWithOffices($payment_method,$day_pointer,$month,$year,$appo_dur,$office_num)
						&& ($this->getDay($day_pointer, $payment_method, $appo_dur, '') != '')) ? "available_day" : "unavailable_day" ;
			}
			else{
				if($available_calendar_obj->checkAvailabilityDayWithOffices($payment_method,$day_pointer,$month,$year,$appo_dur,$office_num))
					$ava_class = "available_day";
					else
						$ava_class = "unavailable_day";
			}
			
			if($ava_class == "available_day"){
				$str = $str .
				<<<EOL
					<a href="javascript:void(0);" onclick="selectDay4Booking('$year','$month','$day_pointer','$payment_method','$user_e_b','$appo_type_id')" style=" width: calc(100% /7);">
EOL;
			}
			
			$str = $str . "<div class='calendar_day_block " . $ava_class . " not_empt' id='day_" . $year . "_" . $month . "_" . $day_pointer . "'><p>" . $element['d'] . "</p></div>";
			if($ava_class == "available_day")
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
	
	public function getDayAvailabilityWithOffices($payment_method,/*this is an array of office numbers*/$office_num,$appo_dur){
		//Function for creating a month array with the available days
		
		//NEEDS TO be used together with Calendar::checkAvailabilityDay as this function might not return a day not because it is available, but because it is not available for booking, thus the availability must be checked using the metnioned method
		$num_appoints_month = $this->num_appoints_month;
		// 			echo " User: " . $this->user_obj->getUsername();
		// 			echo " Month: " . $this->month;
		// 			echo " num appo month: " . $num_appoints_month;
		$appointments_calendar = $this->appointments_calendar;
		$available_calendar_obj = $this->available_calendar_obj;
		$month = $this->month;
		$year = $this->year;
		
		$minutes_per_block = 20;
		$blocks_in_an_hour = 60/$minutes_per_block;
		
		$day_availaility_array = [];
		
		$prev_day = 0;
		
		for($i=0; $i<$num_appoints_month; $i++){
			
			$day_obt = $appointments_calendar[$i]['day'];//THis gets each day number associated with an appointment. IMPORTANT: array MUST be sorted by date and time ascending
			//echo "THE DAY: " . $day_obt . "\n";
			if(!array_key_exists($day_obt, $day_availaility_array))
				$day_availaility_array[$day_obt] = 0;//not available for booking
				
				if(!$day_availaility_array[$day_obt]){
					
					if($prev_day != $day_obt){ //IDENTIFIES A CHANGE OF DAY, this has to excecute once at the start of a day
						$temp_end = '06:00'; //start of the day
						$temp_st_next = $appointments_calendar[$i]['time_start']; //start of first appointment of the day
						$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNums($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
						$prev_day = $day_obt;
					}
					else{
						$temp_end = $appointments_calendar[$i-1]['time_end'];
						$temp_st_next = $appointments_calendar[$i]['time_start'];
						$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNums($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
					}
					
					if(!$day_availaility_array[$day_obt]){
						if(array_key_exists($i+1,$appointments_calendar)){
							if($appointments_calendar[$i+1]['day'] != $day_obt){
								$temp_end = $appointments_calendar[$i]['time_end'];
								$temp_st_next = '21:30';
								$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNums($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
								//$prev_day = $day_obt;
							}
						}
						else{
							$temp_end = $appointments_calendar[$i]['time_end'];
							$temp_st_next = '21:30';
							$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNums($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
							//$prev_day = $day_obt;
						}
					}
					
				}
				//echo "result: " . $day_availaility_array[$day_obt];
		}
		//echo "RESULTING ARRAY: ";
		return $day_availaility_array; //1 = available, not in list = available, 0 = not available
	}
	
	public function getDayAvailabilityWithOffice($payment_method,$office_num,$appo_dur){
		//Function for creating a month array with the available days
		
		//NEEDS TO be used together with Calendar::checkAvailabilityDay as this function might not return a day not because it is available, but because it is not available for booking, thus the availability must be checked using the metnioned method
		$num_appoints_month = $this->num_appoints_month;
		// 			echo " User: " . $this->user_obj->getUsername();
		// 			echo " Month: " . $this->month;
		// 			echo " num appo month: " . $num_appoints_month;
		$appointments_calendar = $this->appointments_calendar;
		$available_calendar_obj = $this->available_calendar_obj;
		$month = $this->month;
		$year = $this->year;

		$minutes_per_block = 20;
		$blocks_in_an_hour = 60/$minutes_per_block;
		
		$day_availaility_array = [];
		
		$prev_day = 0;
		for($i=0; $i<$num_appoints_month; $i++){
			
			$day_obt = $appointments_calendar[$i]['day'];//THis gets each day number associated with an appointment. IMPORTANT: array MUST be sorted by date and time ascending
			//echo "THE DAY: " . $day_obt . "\n";
			if(!array_key_exists($day_obt, $day_availaility_array))
				$day_availaility_array[$day_obt] = 0;//not available for booking
				
			if(!$day_availaility_array[$day_obt]){
				
				if($prev_day != $day_obt){ //IDENTIFIES A CHANGE OF DAY, this has to excecute once at the start of a day
					$today_ = new DateTime();
					$today_day = $today_->format("d");
					$today_month = $today_->format("m");
					$today_year = $today_->format("Y");
					$today_->add(new DateInterval('PT2H'));
					
					if(strtotime($today_year."-".$today_month."-".$today_day) == strtotime($year."-".$month."-".$day_obt)){
						if(strtotime($today_->format("H:i")) > strtotime('06:00')){
							//If it's today, and later than 6am
							$frac = 60*20;
							$r = strtotime($today_->format("H:i")) % $frac;
							$new_time = strtotime($today_->format("H:i")) + ($frac-$r);
							$temp_end= date('H:i', $new_time);
// 							echo $temp_end . ",";
// 							echo $appointments_calendar[$i]['time_start'] . "//";
							if($new_time > strtotime($appointments_calendar[$i]['time_start'])){
								
								//the current examined appointment is earlier than the current time
								$day_availaility_array[$day_obt] = 0;
//								continue;
							}
							else{
								//the current examined appointment is later than the current time, thus it is plausible that there is an available gap
								$temp_st_next = $appointments_calendar[$i]['time_start']; //start of first appointment of the day
								$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNum($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
								$prev_day = $day_obt;
							}
						}
						else{
							//If it's today, but earlier than 6am
							$temp_end = '06:00'; //start of the day
							$temp_st_next = $appointments_calendar[$i]['time_start']; //start of first appointment of the day
							$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNum($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
							$prev_day = $day_obt;
						}
					}
					else{
						//If it's not today
						$temp_end = '06:00'; //start of the day
						$temp_st_next = $appointments_calendar[$i]['time_start']; //start of first appointment of the day
						$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNum($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
						$prev_day = $day_obt;
					}
				}
				else{
					$temp_end = $appointments_calendar[$i-1]['time_end'];
					$temp_st_next = $appointments_calendar[$i]['time_start'];
					
					$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNum($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);

				}
				
				if(!$day_availaility_array[$day_obt]){
					
					if(array_key_exists($i+1,$appointments_calendar)){
						if($appointments_calendar[$i+1]['day'] != $day_obt){
							$temp_end = $appointments_calendar[$i]['time_end'];
							$temp_st_next = '21:30';
							$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNum($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
						}
					}
					else{
						$temp_end = $appointments_calendar[$i]['time_end'];
						$temp_st_next = '21:30';
						$day_availaility_array[$day_obt] = $this->subProcessMoveTimeWindowWithOfficeNum($temp_end, $temp_st_next,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day_obt,$month,$year,$payment_method);
					}
				}
				
			}
			//echo "result: " . $day_availaility_array[$day_obt];
		}
		//echo "RESULTING ARRAY: ";
		return $day_availaility_array; //1 = available, not in list = available, 0 = not available
	}
	
	private function subProcessMoveTimeWindow($temp_end/*time previous appointment ends*/, $temp_st_next /*time next appointment starts*/, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day,$month,$year,$payment_method){
		//This function checks if in the time window provided, there are slots made available by the doctor, of sufficient size, to match the requirements. Basically checks if in the  time window exists any of such slots of availability.
		//echo " Start: " . $temp_end;
		//echo " End : " . $temp_st_next;
		$temp_end_DT = new DateTime($temp_end);
		$temp_st_DT = new DateTime($temp_st_next);
		$interval_diff = $temp_end_DT->diff($temp_st_DT);
		
		$interval_check = $interval_diff->format("%i") + $interval_diff->format("%H")*60;
		$available_calendar_obj = $this->available_calendar_obj;
		
		$return_val = 0;
		if($interval_check >= $appo_dur){
			$start_time_a = $temp_end_DT;
			$end_time_b = $temp_st_DT;
			$interval = $start_time_a->diff($end_time_b);
			
			$hours = $interval->format("%H");
			$mins = $interval->format("%i");
			
			$num_blocks = $hours*$blocks_in_an_hour + ($mins/$minutes_per_block);
			
			for($k = 0; $k < $num_blocks; $k++){
				
				$var_time_temp = strtotime($temp_end) + $minutes_per_block*60*$k;//60 seconds per each minute
				$var_time_temp_end = $var_time_temp + $appo_dur*60;//60 seconds per each minute
				
				$st_time_wind = new DateTime(date('H:i',$var_time_temp));
				$end_time_wind = new DateTime(date('H:i',$var_time_temp_end));
				
				if($available_calendar_obj->checkAvailabilityInterval($payment_method,$st_time_wind/*have to be a DateTime object*/,$end_time_wind/*have to be a DateTime object*/,$day,$month,$year)){
					
					$return_val = 1; //available for booking
					break;
				}
			}
		}
		//echo " Result from interval : " . $return_val;
		return $return_val;
	}
	
	private function subProcessMoveTimeWindowWithOfficeNum($temp_end/*time previous appointment ends*/, $temp_st_next /*time next appointment starts*/,$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day,$month,$year,$payment_method){
		//This function checks if in the time window provided, there are slots made available by the doctor, of sufficient size, to match the requirements. Basically checks if in the  time window exists any of such slots of availability.
		//echo " Start: " . $temp_end;
		//echo " End : " . $temp_st_next;
		$temp_end_DT = new DateTime($temp_end);
		$temp_st_DT = new DateTime($temp_st_next);
		$interval_diff = $temp_end_DT->diff($temp_st_DT);
		
		$interval_check = $interval_diff->format("%i") + $interval_diff->format("%H")*60;
		$available_calendar_obj = $this->available_calendar_obj;
		
		$return_val = 0;
		if($interval_check >= $appo_dur){
			$start_time_a = $temp_end_DT;
			$end_time_b = $temp_st_DT;
			$interval = $start_time_a->diff($end_time_b);
			
			$hours = $interval->format("%H");
			$mins = $interval->format("%i");
			
			$num_blocks = $hours*$blocks_in_an_hour + ($mins/$minutes_per_block);
			
			for($k = 0; $k < $num_blocks; $k++){
				
				$var_time_temp = strtotime($temp_end) + $minutes_per_block*60*$k;//60 seconds per each minute
				$var_time_temp_end = $var_time_temp + $appo_dur*60;//60 seconds per each minute
				
				$st_time_wind = new DateTime(date('H:i',$var_time_temp));
				$end_time_wind = new DateTime(date('H:i',$var_time_temp_end));
				
				if($available_calendar_obj->checkAvailabilityIntervalwithOffice($payment_method,$office_num,$st_time_wind/*have to be a DateTime object*/,$end_time_wind/*have to be a DateTime object*/,$day,$month,$year)){
					
					$return_val = 1; //available for booking
					break;
				}
			}
		}
		//echo " Result from interval : " . $return_val;
		return $return_val;
	}
	
	
	private function subProcessMoveTimeWindowWithOfficeNums($temp_end/*time previous appointment ends*/, $temp_st_next /*time next appointment starts*/, /*this is an array of office numbers*/$office_num, $appo_dur, $blocks_in_an_hour, $minutes_per_block,$day,$month,$year,$payment_method){
		//This function checks if in the time window provided, there are slots made available by the doctor, of sufficient size, to match the requirements. Basically checks if in the  time window exists any of such slots of availability.
		//echo " Start: " . $temp_end;
		//echo " End : " . $temp_st_next;
		$temp_end_DT = new DateTime($temp_end);
		$temp_st_DT = new DateTime($temp_st_next);
		$interval_diff = $temp_end_DT->diff($temp_st_DT);
		
		$interval_check = $interval_diff->format("%i") + $interval_diff->format("%H")*60;
		$available_calendar_obj = $this->available_calendar_obj;
		
		$return_val = 0;
		if($interval_check >= $appo_dur){
			$start_time_a = $temp_end_DT;
			$end_time_b = $temp_st_DT;
			$interval = $start_time_a->diff($end_time_b);
			
			$hours = $interval->format("%H");
			$mins = $interval->format("%i");
			
			$num_blocks = $hours*$blocks_in_an_hour + ($mins/$minutes_per_block);
			
			for($k = 0; $k < $num_blocks; $k++){
				
				$var_time_temp = strtotime($temp_end) + $minutes_per_block*60*$k;//60 seconds per each minute
				$var_time_temp_end = $var_time_temp + $appo_dur*60;//60 seconds per each minute
				
				$st_time_wind = new DateTime(date('H:i',$var_time_temp));
				$end_time_wind = new DateTime(date('H:i',$var_time_temp_end));
				
				foreach ($office_num as $key => $office){
					if($available_calendar_obj->checkAvailabilityIntervalwithOffice($payment_method,$office,$st_time_wind/*have to be a DateTime object*/,$end_time_wind/*have to be a DateTime object*/,$day,$month,$year)){
						$return_val = 1; //available for booking
						break;
					}
				}
				if($return_val){
					break;
				}
			}
		}
		//echo " Result from interval : " . $return_val;
		return $return_val;
	}
	
	public function checkAvailabilityNotBookedDay($day_p,$appo_dur,$payment_method){
		$day_t = date_create("0000-" . "05" . "-" . $day_p);
		$day = date_format($day_t,"d");
		$month = $this->month;
		$year = $this->year;
		$available_calendar_obj = $this->available_calendar_obj;
		$minutes_per_block = 20;
		$blocks_in_an_hour = 60/$minutes_per_block;
		$temp_end_DT = new DateTime('06:00');
		$temp_st_DT = new DateTime('21:30');
		$start_time_a = $temp_end_DT;
		$end_time_b = $temp_st_DT;
		$interval = $start_time_a->diff($end_time_b);
		$hours = $interval->format("%H");
		$mins = $interval->format("%i");
		$num_blocks = $hours*$blocks_in_an_hour + ($mins/$minutes_per_block);
		$bool_var = 0;
		for($i = 0; $i <= $num_blocks; $i++){
			$var_time_temp = strtotime('06:00') + $minutes_per_block*60*$i;//60 seconds per each minute
			$var_time_temp_end = $var_time_temp + 60*$appo_dur;//60 seconds per each minute
			$st_time_wind = new DateTime(date('H:i',$var_time_temp));
			$end_time_wind = new DateTime(date('H:i',$var_time_temp_end));
			if($available_calendar_obj->checkAvailabilityInterval($payment_method,$st_time_wind/*have to be a DateTime object*/,$end_time_wind/*have to be a DateTime object*/,$day,$month,$year)){
				$bool_var = 1; //available for booking
			}
		}
		return $bool_var;
	}
}
?>