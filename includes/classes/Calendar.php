<?php
class Calendar{
	private $user_obj;
	private $con;
	private $my_appo_dur_tab;
	private $calendar_availability; //the calendar defined by the doctor for his availability
	public function __construct($con, $user, $user_e/*The user in this class is the doctor, owner of the calendar*/){
		try{
			$this->con = $con;
			$this->user_obj = $temp_user_obj = new User($con, $user, $user_e);
			$this->my_appo_dur_tab = $temp_user_obj->getAppoDurationTable();
			$this->calendar_availability = $temp_user_obj->getAvailableCalendar();
		}
		catch ( Exception $e ){
			$this->con = "";
			$this->$user_obj = "";
			$this->my_appo_dur_tab = "";
			throw new Exception( $e->getMessage() );
		}
	}
	
	public function getIntervalOffice($payment_method,$time_start/*have to be a DateTime object*/,$time_end/*have to be a DateTime object*/,$day,$month,$year){
		
		//This function checks if the interval of time defined by $time_start and $time_end is set as available by the doctor for booking
		
		//the week is correct, there is no week with two numbers
		
		$time_start_f = $time_start->format("H:i");
		$time_end_f = $time_end->format("H:i");
		$user = $this->user_obj->getUsername();
		
		$stmt = $this->con->prepare("SELECT w,dayName FROM calendar_table WHERE m = ? AND y = ? AND d = ?");
		$stmt->bind_param("iii", $month, $year, $day);
		
		$stmt->execute();
		$res = $stmt->get_result();
		$res_arr = mysqli_fetch_array($res);
		
		$week = $res_arr['w'];
		$dayName = $res_arr['dayName'];
		
		$day_and_method = strtolower($dayName) . "_" . $payment_method;
		
		$week_id = $user . "__doc_conf_week__" . $year . "_" . $week;
		
		$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$week_id'");
		$existance_rows = mysqli_num_rows($existance);
		
		if($existance_rows == 0){
			$checking_week = $this->calendar_availability;
		}
		else{
			$checking_week = $week_id;
		}
		
		$stmt = $this->con->prepare("SELECT $day_and_method FROM $checking_week WHERE STR_TO_DATE(hour, '%H:%i')<? AND STR_TO_DATE(hour_end, '%H:%i') > ?");
		$stmt->bind_param("ss", $time_end_f, $time_start_f);
		
		$stmt->execute();
		$time_slot = $stmt->get_result();
		
		$checking_bool = 1;
		
		foreach ($time_slot as $key => $value) {
			if($key == 0){
				$old_val = $value[$day_and_method];
			}
			
			$bool_var = 0;
			
			if($value[$day_and_method] > 0){
				$bool_var = 1;
			}
			
			if($old_val != $value[$day_and_method]){
				$bool_var = 0;
			}
			
			$checking_bool = $bool_var * $checking_bool;
			$old_val = $value[$day_and_method];
		}
		
		if($checking_bool && mysqli_num_rows($time_slot) > 0){
			$return_value = $value[$day_and_method];
		}
		else{
			$return_value = 0;
		}
		
		return $return_value; //1 means there is availability
	}
	public function checkAvailabilityInterval($payment_method,$time_start/*have to be a DateTime object*/,$time_end/*have to be a DateTime object*/,$day,$month,$year){
		//This function checks if the interval of time defined by $time_start and $time_end is set as available by the doctor for booking
		//(the week is correct, there is no week with two numbers)
		$time_start_f = $time_start->format("H:i");
		$time_end_f = $time_end->format("H:i");
		$user = $this->user_obj->getUsername();
		$stmt = $this->con->prepare("SELECT w,dayName FROM calendar_table WHERE m = ? AND y = ? AND d = ?");
		$stmt->bind_param("iii", $month, $year, $day);
		$stmt->execute();
		$res = $stmt->get_result();
		$res_arr = mysqli_fetch_array($res);
		$week = $res_arr['w'];
		$dayName = $res_arr['dayName'];
		$day_and_method = strtolower($dayName) . "_" . $payment_method;
		$week_id = $user . "__doc_conf_week__" . $year . "_" . $week;
		$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$week_id'");
		$existance_rows = mysqli_num_rows($existance);
		if($existance_rows == 0){
			$checking_week = $this->calendar_availability;
		}
		else{
			$checking_week = $week_id;
		}
		$stmt = $this->con->prepare("SELECT $day_and_method FROM $checking_week WHERE STR_TO_DATE(hour, '%H:%i')<? AND STR_TO_DATE(hour_end, '%H:%i') > ?");
		$stmt->bind_param("ss", $time_end_f, $time_start_f);
		$stmt->execute();
		$time_slot = $stmt->get_result();
		$checking_bool = 1;
		
		foreach ($time_slot as $key => $value) {
			if($key == 0){
				$old_val = $value[$day_and_method];
			}
			
			$bool_var = 0;
			
			if($value[$day_and_method] > 0){
				$bool_var = 1;
			}
			
			if($old_val != $value[$day_and_method]){
				$bool_var = 0;
			}
			
			$checking_bool = $bool_var * $checking_bool;
			$old_val = $value[$day_and_method];
		}
		return $checking_bool; //1 means there is availability
	}
	
	public function getAvailabilityIntervalOffice($payment_method,$time_start,$time_end,$day,$month,$year){
		//This function checks if the interval of time defined by $time_start and $time_end is set as available by the doctor for booking AND RETURNS THE AVAILABLE OFFICE, 0 otherwise
		//(the week is correct, there is no week with two numbers)
		$time_start_f = $time_start;
		$time_end_f = $time_end;
		$user = $this->user_obj->getUsername();
		$stmt = $this->con->prepare("SELECT w,dayName FROM calendar_table WHERE m = ? AND y = ? AND d = ?");
		$stmt->bind_param("iii", $month, $year, $day);
		$stmt->execute();
		$res = $stmt->get_result();
		$res_arr = mysqli_fetch_array($res);
		$week = $res_arr['w'];
		$dayName = $res_arr['dayName'];
		$day_and_method = strtolower($dayName) . "_" . $payment_method;
		$week_id = $user . "__doc_conf_week__" . $year . "_" . $week;
		$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$week_id'");
		$existance_rows = mysqli_num_rows($existance);
		if($existance_rows == 0){
			$checking_week = $this->calendar_availability;
		}
		else{
			$checking_week = $week_id;
		}
		$stmt = $this->con->prepare("SELECT $day_and_method FROM $checking_week WHERE STR_TO_DATE(hour, '%H:%i')<? AND STR_TO_DATE(hour_end, '%H:%i') > ?");
		$stmt->bind_param("ss", $time_end_f, $time_start_f);
		$stmt->execute();
		$time_slot = $stmt->get_result();
		$checking_bool = 1;
		
		$current_office = 0;
		
		foreach ($time_slot as $key => $value) {
			if($key == 0){
				$old_val = $value[$day_and_method];
			}
			
			$bool_var = 0;
			
			if($value[$day_and_method] > 0){
				$bool_var = 1;
				$current_office = $value[$day_and_method];
			}
			
			if($old_val != $value[$day_and_method]){
				$bool_var = 0;
			}
			
			$checking_bool = $bool_var * $checking_bool;
			$old_val = $value[$day_and_method];
		}
		
		if($checking_bool){
			return $current_office;
		}
		else{
			return 0;
		}
	}
	
	public function checkAvailabilityIntervalWithOffices($payment_method,$time_start/*have to be a DateTime object*/,$time_end/*have to be a DateTime object*/,$day,$month,$year,$offices_arr){
		//This function checks if the interval of time defined by $time_start and $time_end is set as available by the doctor for booking
		//(the week is correct, there is no week with two numbers)
		$time_start_f = $time_start->format("H:i");
		$time_end_f = $time_end->format("H:i");
		$user = $this->user_obj->getUsername();
		$stmt = $this->con->prepare("SELECT w,dayName FROM calendar_table WHERE m = ? AND y = ? AND d = ?");
		$stmt->bind_param("iii", $month, $year, $day);
		$stmt->execute();
		$res = $stmt->get_result();
		$res_arr = mysqli_fetch_array($res);
		$week = $res_arr['w'];
		$dayName = $res_arr['dayName'];
		$day_and_method = strtolower($dayName) . "_" . $payment_method;
		$week_id = $user . "__doc_conf_week__" . $year . "_" . $week;
		$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$week_id'");
		$existance_rows = mysqli_num_rows($existance);
		if($existance_rows == 0){
			$checking_week = $this->calendar_availability;
		}
		else{
			$checking_week = $week_id;
		}
		$stmt = $this->con->prepare("SELECT $day_and_method,hour,hour_end,id FROM $checking_week WHERE STR_TO_DATE(hour, '%H:%i') < ? AND STR_TO_DATE(hour_end, '%H:%i') > ?");
		$stmt->bind_param("ss", $time_end_f, $time_start_f);
		$stmt->execute();
		$time_slot = $stmt->get_result();
		$checking_bool = 1;
		
		$f_time = 1;
		
		foreach ($time_slot as $key => $value) {

			if($f_time == 1){
				$old_val = $value[$day_and_method];
				$f_time = 0;
			}
			
			$bool_var = 0;
			if(in_array($value[$day_and_method], $offices_arr)){
				$bool_var = 1;
			}
			
			if($old_val != $value[$day_and_method]){
				$bool_var = 0;
			}
			
			$checking_bool = $bool_var * $checking_bool;
			$old_val = $value[$day_and_method];
			
		}
		return $checking_bool; //1 means there is availability
	}
	
	public function checkAvailabilityDay($payment_method,$day,$month,$year,$appo_dur){
		//This function checks if the $day is set as available by the doctor for booking, even one slot
		//the week is correct, there is no week with two numbers
		
// 		echo "day : " . $day. "<br>";
// 		echo "month : " . $month. "<br>";
// 		echo "year : " . $year. "<br>";

		$today_ = new DateTime();
		$today_day = $today_->format("d");
		$today_month = $today_->format("m");
		$today_year = $today_->format("Y");
		$today_->add(new DateInterval('PT2H'));
		
		if(strtotime($today_year."-".$today_month."-".$today_day) == strtotime($year."-".$month."-".$day)){
			$isToday = 1;
			$isPast = 0;
		}
		elseif(strtotime($today_year."-".$today_month."-".$today_day) > strtotime($year."-".$month."-".$day)){
			$isToday = 0;
			$isPast = 1;
		}
		else{
			$isToday = 0;
			$isPast = 0;
		}
		
		$user = $this->user_obj->getUsername();
		$stmt = $this->con->prepare("SELECT w,dayName FROM calendar_table WHERE m = ? AND y = ? AND d = ?");
		$stmt->bind_param("iii", $month, $year, $day);
		$stmt->execute();
		$res = $stmt->get_result();
		$res_arr = mysqli_fetch_array($res);
		$week = $res_arr['w'];
		$dayName = $res_arr['dayName'];
		$day_and_method = strtolower($dayName) . "_" . $payment_method;
		$week_id = $user . "__doc_conf_week__" . $year . "_" . $week;
		$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$week_id'");
		$existance_rows = mysqli_num_rows($existance);
		
		if($existance_rows == 0){
			$checking_week = $this->calendar_availability;
		}
		else{
			$checking_week = $week_id;
		}
		
// 		echo "day_and_method: " . $day_and_method . "<br>";
// 		echo "checking week: " . $checking_week . "<br>";
		if($isToday){
			$low_bound = $today_->format("H:i");
			$stmt = $this->con->prepare("SELECT hour,hour_end,$day_and_method FROM $checking_week WHERE STR_TO_DATE(hour, '%H:%i') >= ?");
			$stmt->bind_param("s", $low_bound);
		}
		else{
			$stmt = $this->con->prepare("SELECT hour,hour_end,$day_and_method FROM $checking_week");
		}
		
		$stmt->execute();
		$time_slot = $stmt->get_result();
		
		$time_slots_arr = array();
		foreach ($time_slot as $key => $value) {
			$time_slots_arr[$key] = $value[$day_and_method];
		}

		$num_repeated = $appo_dur/20;
		$checking_bool= 0;
		
		if(!$isPast){
			for($i = 0;$i<sizeof($time_slots_arr)-$num_repeated;$i++){
				
				$start_val = $time_slots_arr[$i];
				
				if($start_val != 0){
					$checking_bool= 1;
					
					for($j = 1; $j < $num_repeated ;$j++){
						if($start_val != $time_slots_arr[$i+$j]){
							$checking_bool = 0;
						}
					}
					if($checking_bool){
						break;
					}
				}
			}
		}
		return $checking_bool; //1 means there is availability
	}
	
	public function checkAvailabilityDayWithOffices($payment_method,$day,$month,$year,$appo_dur,$office_arr){
		//This function checks if the $day is set as available by the doctor for booking, even one slot
		//the week is correct, there is no week with two numbers
		
		$today_ = new DateTime();
		$today_day = $today_->format("d");
		$today_month = $today_->format("m");
		$today_year = $today_->format("Y");
		$today_->add(new DateInterval('PT2H'));
		
		if(strtotime($today_year."-".$today_month."-".$today_day) == strtotime($year."-".$month."-".$day)){
			$isToday = 1;
			$isPast = 0;
		}
		elseif(strtotime($today_year."-".$today_month."-".$today_day) > strtotime($year."-".$month."-".$day)){
			$isToday = 0;
			$isPast = 1;
		}
		else{
			$isToday = 0;
			$isPast = 0;
		}
		
		$user = $this->user_obj->getUsername();
		$stmt = $this->con->prepare("SELECT w,dayName FROM calendar_table WHERE m = ? AND y = ? AND d = ?");
		$stmt->bind_param("iii", $month, $year, $day);
		$stmt->execute();
		$res = $stmt->get_result();
		$res_arr = mysqli_fetch_array($res);
		$week = $res_arr['w'];
		$dayName = $res_arr['dayName'];
		$day_and_method = strtolower($dayName) . "_" . $payment_method;
		$week_id = $user . "__doc_conf_week__" . $year . "_" . $week;
		$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$week_id'");
		$existance_rows = mysqli_num_rows($existance);
		
		if($existance_rows == 0){
			$checking_week = $this->calendar_availability;
		}
		else{
			$checking_week = $week_id;
		}
		
		if($isToday){
			$low_bound = $today_->format("H:i");
			$stmt = $this->con->prepare("SELECT hour,hour_end,$day_and_method FROM $checking_week WHERE STR_TO_DATE(hour, '%H:%i') >= ?");
			$stmt->bind_param("s", $low_bound);
		}
		else{
			$stmt = $this->con->prepare("SELECT hour,hour_end,$day_and_method FROM $checking_week");
		}
		
		$stmt->execute();
		$time_slot = $stmt->get_result();
		
		$time_slots_arr = array();
		foreach ($time_slot as $key => $value) {
			$time_slots_arr[$key] = $value[$day_and_method];
		}
		
		$num_repeated = $appo_dur/20;
		$checking_bool= 0;
		
		if(!$isPast){
			for($i = 0;$i<sizeof($time_slots_arr)-$num_repeated;$i++){
				
				$start_val = $time_slots_arr[$i];
				
				if(in_array($start_val, $office_arr)){
					$checking_bool= 1;
					
					for($j = 1; $j < $num_repeated ;$j++){
						if($start_val != $time_slots_arr[$i+$j]){
							$checking_bool = 0;
						}
					}
					if($checking_bool){
						break;
					}
				}
			}
		}
		return $checking_bool; //1 means there is availability
	}
	
	public function getDropDownCalendar($month,$year){
		$lang = $_SESSION['lang'];
		
		$userLoggedIn = $this->user_obj->getUsername();
		//$str = "<a href='#'><div class='calendar_week_block'>";
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
			$day_pointer = $element['d'];
			if($day_week_pointer == 1 && $prev_month_once == 1){
				$str = $str .
				<<<EOL
									<a href="javascript:void(0);" onclick="changeWeek('$week_id_year','$week_id_week')">
										<div class='calendar_week_block'
EOL;
				$str = $str . " id=calendar_week_block_id_" . $week_id_year . $week_id_week . ">";
			}
			if(/*$day_pointer < $start_day &&*/ $prev_month_once == 0){
				$str = $str .
				<<<EOL
									<a href="javascript:void(0);" onclick="changeWeek('$week_id_year','$week_id_week')">
										<div class='calendar_week_block'
EOL;
				$str = $str . " id=calendar_week_block_id_" . $week_id_year . $week_id_week . ">";
				foreach ($prev_month_query as $key2 => $element2) {
					
					if($element2['d'] > $prev_month_size-($start_day-1)){
						$str = $str . "<div class='calendar_day_block' id='empt'>" . $element2['d'] . "</div>";
					}
				}
			}
			$prev_month_once = 1;
			$str = $str . "<div class='calendar_day_block' id='not_empt'><p>" . $element['d'] . "</p></div>";
			if($day_week_pointer == 7){
				$str = $str . "</a></div>";
			}
		}
		foreach ($next_month_query as $key => $element) {
			
			if($element['d'] <= 7-$last_day){
				$str = $str . "<div class='calendar_day_block' id='empt'>" . $element['d'] . "</div>";
			}
		}
		if(0 != 7-$last_day){
			$str = $str . "</a></div>";
		}
		
		switch ($lang){
			
			case("en"):
				$str = $str . "<input type='hidden' id='selected_week_week' name='selected_week_week' value=''>
						    <input type='hidden' id='selected_week_year' name='selected_week_year' value=''>
							<input type='submit' class='select_week_office' data-toggle='modal' data-target='#confirm_custom_week' id='select_week_button' value='Set Selected Week'>
						";
				break;
				
			case("es"):
				$str = $str . "<input type='hidden' id='selected_week_week' name='selected_week_week' value=''>
						    <input type='hidden' id='selected_week_year' name='selected_week_year' value=''>
							<input type='submit' class='select_week_office' data-toggle='modal' data-target='#confirm_custom_week' id='select_week_button' value='Configurar Semana'>
						";
				break;
		}
		
		
		
// 		<a href='javascript:void(0);' onclick='updateWeek(); closeDropdownCalendar()'>
// 		<div class='div_button'>
// 		Change Week
// 		</div>
// 		</a>
		
		return $str;
	}
	public function newAvailableCalendar($week_id){
		//doctor calendar set
		$blocks_in_an_hour = 2;
		$minutes_per_block = 60/$blocks_in_an_hour;
		$start_time = "6:00";
		$end_time = "21:00";
		$dteStart = new DateTime($start_time);
		$dteEnd   = new DateTime($end_time);
		$hours_per_day = $dteStart->diff($dteEnd)->format("%H");
		//$hours_per_day = $end_time-$start_time;
		$num_blocks = $hours_per_day*$blocks_in_an_hour;
		$var_time = "";
		$query = mysqli_query($this->con,
				"CREATE TABLE $week_id (
				id int(4) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				hour varchar(8) not null,
				hour_end varchar(8) not null,
				sunday_part TINYINT(1) not null,
				sunday_insu TINYINT(1) not null,
				monday_part TINYINT(1) not null,
				monday_insu TINYINT(1) not null,
				tuesday_part TINYINT(1) not null,
				tuesday_insu TINYINT(1) not null,
				wednesday_part TINYINT(1) not null,
				wednesday_insu TINYINT(1) not null,
				thursday_part TINYINT(1) not null,
				thursday_insu TINYINT(1) not null,
				friday_part TINYINT(1) not null,
				friday_insu TINYINT(1) not null,
				saturday_part TINYINT(1) not null,
				saturday_insu TINYINT(1) not null
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
				);
		$var_time = strtotime($start_time);
		$stmt = $this->con->prepare("INSERT INTO $week_id VALUES ('',?,?,0,0,0,0,0,0,0,0,0,0,0,0,0,0)");
		$stmt->bind_param("ss", $hour_var, $hour_var_end);
		for($i = 0; $i <= $num_blocks; $i++){
			$var_time_temp = $var_time + $minutes_per_block*60*$i;//60 seconds per each minute
			$var_time_temp_end = $var_time_temp + 60*$minutes_per_block;//60 seconds per each minute
			$hour_var_end = date('H:i', $var_time_temp_end);
			$hour_var = date('H:i', $var_time_temp);
			$stmt->execute();
		}
	}
	public function getAvailableCalendarTimes($year,$week){
		$userLoggedIn = $this->user_obj->getUsername();
		$year = htmlspecialchars($year);
		$year = strip_tags($year);
		$year = mysqli_real_escape_string($this->con,$year);
		$week = htmlspecialchars($week);
		$week = strip_tags($week);
		$week = mysqli_real_escape_string($this->con,$week);
		if($year == 'default' || $week == 'default')
			$calendar_availability = $this->calendar_availability;
			else{
				$week_id = $userLoggedIn . "__doc_conf_week__" . $year . "_" . $week;
				$calendar_availability = $week_id;
			}
			$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$calendar_availability'");
			$existance_rows = mysqli_num_rows($existance);
			if($existance_rows == 0){
				$this->newAvailableCalendar($week_id);
			}
			$query = mysqli_query($this->con,"SELECT hour, hour_end FROM $calendar_availability");
			return $query;
	}
	public function dayFill($element,$select_year_to_mod,$select_week_to_mod,$input_elements){
		$block_html = "";
		$final_str = "";
		$times_arr = $this->getAvailableCalendarByDay($select_year_to_mod,$select_week_to_mod,$element);
		
		foreach ($times_arr as $i => $val) {
			$active = $val[$element];
			$id = $val['id'];
			$input_elements[] = 'inp-' . $element . '-X' . $id .'X';
			if($active != 0){
				$selection = "<div class='block cal_active" . $active . "'";
				$inp = "<input type='hidden' id='inp_" . $element . $id . "' name='inp-" . $element . "-X" . $id . "X' value='" . $active . "'>";
				$sel_text = "&#10003;";
			}
			else{
				$selection = "<div class='block cal_inactive'";
				$inp = "<input type='hidden' id='inp_" . $element . $id . "' name='inp-" . $element . "-X" . $id . "X' value='0'>";
				$sel_text = "-";
			}
			$block_html = $selection . "id='div_" . $element . $id ."'>" . $sel_text . "</div>";
			$final_str = $final_str . $block_html . $inp;
			$final_str = $final_str .
			"
							<script>
								$(document).ready(function(){
									$('#div_" . $element . $id . "').on('click', function(){
										var active = $('#inp_" . $element . $id . "').val();
										if(active != 0){
											var selector = $('#office_selector').val();
											if(selector == active){
												$('#inp_" . $element . $id . "').val('0');
												$('#div_" . $element . $id . "').toggleClass('cal_inactive');
												$('#div_" . $element . $id . "').toggleClass('cal_active' + selector);
												$('#div_" . $element . $id . "').html('-');
											}
											else{
												$('#inp_" . $element . $id . "').val(selector);
												$('#div_" . $element . $id . "').toggleClass('cal_active' + active);
												$('#div_" . $element . $id . "').toggleClass('cal_active' + selector);
												$('#div_" . $element . $id . "').html('&#10003;');
											}
										}
										else{
											var selector = $('#office_selector').val();
											var active = $('#inp_" . $element . $id . "').val();
											$('#inp_" . $element . $id . "').val(selector);
											$('#div_" . $element . $id . "').toggleClass('cal_active' + selector);
											$('#div_" . $element . $id . "').toggleClass('cal_inactive');
											$('#div_" . $element . $id . "').html('&#10003;');
										}
									});
								});
							</script>";
		}
		return array($final_str,$input_elements);
	}
	public function getAvailableCalendarByDay($year,$week,$column){
		$userLoggedIn = $this->user_obj->getUsername();
		$year = htmlspecialchars($year);
		$year = strip_tags($year);
		$year = mysqli_real_escape_string($this->con,$year);
		$week = htmlspecialchars($week);
		$week = strip_tags($week);
		$week = mysqli_real_escape_string($this->con,$week);
		if($year == 'default' || $week == 'default')
			$calendar_availability = $this->calendar_availability;
			else{
				$week_id = $userLoggedIn . "__doc_conf_week__" . $year . "_" . $week;
				$calendar_availability = $week_id;
			}
			$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$calendar_availability'");
			$existance_rows = mysqli_num_rows($existance);
			if($existance_rows == 0){
				$this->newAvailableCalendar($week_id);
			}
			$query = mysqli_query($this->con,"SELECT id,$column FROM $calendar_availability");
			return $query;
	}
	public function getAvailableCalendarNumItems(){
		$calendar_availability = $this->calendar_availability;
		//echo $calendar_availability;
		$query = mysqli_query($this->con,"SELECT hour FROM $calendar_availability");
		$num = mysqli_num_rows($query);
		return $num;
	}
	
	public function getAppoDurationRowsNum(){
		$userLoggedIn = $this->user_obj->getUsername();
		$my_appo_dur_tab = $this->my_appo_dur_tab;
		
		$stmt = $this->con->prepare("SELECT * FROM $my_appo_dur_tab WHERE deleted = 0 ORDER BY id DESC");
		//$stmt->bind_param("s", $userLoggedIn);
		
		$stmt->execute();
		$get_messages_query = $stmt->get_result();
		$num_types = mysqli_num_rows($get_messages_query);
		$stmt->close();
		
		return $num_types;
	}
	public function getAppoDurationSettings(){
		$userLoggedIn = $this->user_obj->getUsername();
		$my_appo_dur_tab = $this->my_appo_dur_tab;
		$stmt = $this->con->prepare("SELECT * FROM $my_appo_dur_tab WHERE deleted = 0 ORDER BY id DESC");
		//$stmt->bind_param("s", $userLoggedIn);
		$stmt->execute();
		$get_messages_query = $stmt->get_result();
		$num_types = mysqli_num_rows($get_messages_query);
		$stmt->close();
		$data="";
		if($num_types > 0){
			while($arr = mysqli_fetch_array($get_messages_query)){
				$appo_type = $arr['appo_type'];
				$duration = $arr['duration'];
				$cost = $arr['cost'];
				$currency = $arr['currency'];
				$id = $arr['id'];
				$txt_rep = new TxtReplace();
				$appo_type = $txt_rep->entities($appo_type);
				$line = "<div class='table_element' id='translucid_appo_type'>" . $txt_rep->entities($appo_type) . "</div><div class='table_element' id='translucid_appo_durat'>" . $txt_rep->entities($duration) . "</div> <div class='table_element' id='translucid_appo_cost'>" . $txt_rep->entities($cost) . "<span style=' font-size: 10px;'>" . $txt_rep->entities($currency) . "</span></div><div class='delete_element del_appo_type' id='del_appo_type_id_" . $id . "'>x</div><br>";
				$data = $data . $line;
				?>
						<script>
							$(document).ready(function(){
								$('#del_appo_type_id_<?php echo $id; ?>').on('click',function(){
									$.post("includes/form_handlers/delete_appo_type.php?id=<?php echo $id; ?>", function(data){
										//alert(data.substring(0,5));
										if(data.substring(0,6) == "Error."){
											$("#message_proh_del" ).html(data.substring(7));
										}
										else{
											$('[name="added_appo_box"]').html(data);
										}											
									});
									
									
								});
							});
						</script>

					<?php
				}
			}	
			else{
				$data = "<div class='table_element' id='translucid_appo_type'> Insert an appointment type.</div> <div class='table_element' id='translucid_appo_durat'></div><br>";
			}
			return $data;
		}
		public function setAppoDurationSettings($description,$duration,$cost,$currency = 'COP'){
			$lang = $_SESSION['lang'];
			$userLoggedIn = $this->user_obj->getUsername();
			$my_appo_dur_tab = $this->my_appo_dur_tab;
			$message = "";
			$query = mysqli_query($this->con, "SELECT id FROM $my_appo_dur_tab where deleted ='0'");
			if(mysqli_num_rows($query) < 6){//the pointer is moved, it starts at 0 rows for 1 item.
				$stmt = $this->con->prepare("INSERT INTO $my_appo_dur_tab (`id`, `appo_type`, `duration`, `cost`, `currency`, `deleted`) VALUES ('', ?, ?, ?, ?, 0) ");
				$stmt->bind_param("siis", $description, $duration,$cost,$currency);
				$stmt->execute();
				$stmt->close();
			}
			else{
				switch ($lang){
					
					case("en"):
						$message = "Error. Could not add this appointment type: Maximum 6 types";
						break;
						
					case("es"):
						$message = "Error. No se pudo agregar este tipo de cita: Máximo 6 tipos";
						break;
				}
				
			}
			return $message;
		}
		public function removeAppoType($id){
			$userLoggedIn = $this->user_obj->getUsername();
			$my_appo_dur_tab = $this->my_appo_dur_tab;
			$stmt = $this->con->prepare("UPDATE $my_appo_dur_tab SET deleted = 1 WHERE id=?");
			$stmt->bind_param("i", $id);
			$stmt->execute();
			$stmt->close();
		}
		
		public function checkAvailabilityIntervalwithOffice($payment_method,$office_num,$time_start/*have to be a DateTime object*/,$time_end/*have to be a DateTime object*/,$day,$month,$year){
			
			//This function checks if the interval of time defined by $time_start and $time_end is set as available by the doctor for booking
			
			//the week is correct, there is no week with two numbers
			
			$time_start_f = $time_start->format("H:i");
			$time_end_f = $time_end->format("H:i");
			$user = $this->user_obj->getUsername();
			
			$stmt = $this->con->prepare("SELECT w,dayName FROM calendar_table WHERE m = ? AND y = ? AND d = ?");
			$stmt->bind_param("iii", $month, $year, $day);
			
			$stmt->execute();
			$res = $stmt->get_result();
			$res_arr = mysqli_fetch_array($res);
			
			$week = $res_arr['w'];
			$dayName = $res_arr['dayName'];
			
			$day_and_method = strtolower($dayName) . "_" . $payment_method;
			
			$week_id = $user . "__doc_conf_week__" . $year . "_" . $week;
			
			$existance = mysqli_query($this->con, "SHOW TABLES LIKE '$week_id'");
			$existance_rows = mysqli_num_rows($existance);
			
			if($existance_rows == 0){
				$checking_week = $this->calendar_availability;
			}
			else{
				$checking_week = $week_id;
			}
						
			$stmt = $this->con->prepare("SELECT $day_and_method FROM $checking_week WHERE STR_TO_DATE(hour, '%H:%i')<? AND STR_TO_DATE(hour_end, '%H:%i') > ?");
			$stmt->bind_param("ss", $time_end_f, $time_start_f);
			
			$stmt->execute();
			$time_slot = $stmt->get_result();
			
			$checking_bool = 1;
			
			foreach ($time_slot as $key => $value) {
				
				if($key == 0){
					$old_val = $value[$day_and_method];
					if($office_num != $old_val){
						$checking_bool = 0;
					}						
				}
				
				$bool_var = 0;
				
				if($value[$day_and_method] > 0){
					$bool_var = 1;
				}
				
				if($old_val != $value[$day_and_method]){
					$bool_var = 0;
				}
				
				$checking_bool = $bool_var * $checking_bool;
				$old_val = $value[$day_and_method];
			}
			
			return $checking_bool; //1 means there is availability
		}
	}
?>