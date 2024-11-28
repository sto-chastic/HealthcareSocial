<?php
include_once("Crypt.php");
class SearchNMap{
	private $con;

	public function __construct($con){
		$this->con = $con;
	}
	
	public function appointmentDistanceScore($desired_date_str,$withpoints,$payment_method){
		
		$low_limit = new DateTime();
		$low_limit->add(new DateInterval('PT2H'));
		$low_limit_day = $low_limit->format("d");
		$low_limit_month = $low_limit->format("m");
		$low_limit_year = $low_limit->format("Y");
		
		$desired_date = new DateTime($desired_date_str);
		$selected_day = $desired_date->format("d");
		$selected_month = $desired_date->format("m");
		$selected_year = $desired_date->format("Y");
		
		$stmt = $this->con->prepare("SELECT d FROM calendar_table WHERE y=? AND m=? ORDER BY d DESC LIMIT 1");
		$stmt->bind_param("ii", $selected_year,$selected_month);
		$stmt->execute();
		$tq = $stmt->get_result();
		$selected_month_top_limit_arr = mysqli_fetch_assoc($tq);
		$selected_month_top_limit = $selected_month_top_limit_arr['d'];
		
		if($selected_day >= 15){
			$top = 1;
			$other_month_obj = $desired_date->add(new DateInterval('P14D'));
		}
		else{
			$top = 0;
			$other_month_obj= $desired_date->sub(new DateInterval('P14D'));
		}
		
		$other_month = $other_month_obj->format("m");
		$other_year = $other_month_obj->format("Y");
		
		$stmt = $this->con->prepare("SELECT d FROM calendar_table WHERE y=? AND m=? ORDER BY d DESC LIMIT 1");
		$stmt->bind_param("ii", $other_year,$other_month);
		$stmt->execute();
		$tq = $stmt->get_result();
		$other_month_top_limit_arr = mysqli_fetch_assoc($tq);
		$other_month_top_limit = $other_month_top_limit_arr['d'];
		
		$result_array = [];
		//TODO_bookmark
		$crypt = new Crypt();
		for($i=0;$i<sizeof($withpoints);$i++){
			$temp_username = $withpoints[$i]['username'];
			$temp_office = $withpoints[$i]['office'];
			$temp_username_e = $crypt->EncryptU($temp_username);
			$temp_user_obj = new User($this->con, $temp_username, $temp_username_e);
			
			$appo_dur_tab = $temp_user_obj->getAppoDurationTable();
			$upToDate = $temp_user_obj->isUpToDate();

			if (!$upToDate){
			    continue;
			}
			else{
    			    $stmt = $this->con->prepare("SELECT duration FROM $appo_dur_tab WHERE appo_type LIKE ? OR appo_type LIKE ?");
    			    $stmt->bind_param("ss", $pos_names1,$pos_names2);
    			    
        			$pos_names1 = "_irst__ime";
        			$pos_names2 = "_rimera__ez";
        			
        			$stmt->execute();
        			$appdurq = $stmt->get_result();
        			
        			if(mysqli_num_rows($appdurq) == 1){
        			    $_temp_dur_arr_ = mysqli_fetch_assoc($appdurq);
        			    $temp_appo_dur = $_temp_dur_arr_['duration'];
        			}
        			else{
        			    $temp_appo_dur = 60; //Maximum allowed duration, to make sure the largest possible appointment fits
        			}
    			}
			
			
			$bool_found = 0;
			
			$_office_array = array($temp_office);
			
			$calendar = new Calendar($this->con, $temp_username, $temp_username_e);
			
			//echo "user: " . $temp_username;
			
			$this_appointments_calendar = new Appointments_Calendar($this->con, $temp_username, $temp_username_e, $selected_year, $selected_month);
			$other_appointments_calendar = new Appointments_Calendar($this->con, $temp_username, $temp_username_e, $other_year, $other_month);
			
			if($top){
				$this_availability = $this_appointments_calendar->getDayAvailabilityWithOffice($payment_method, $temp_office, $temp_appo_dur);
				$other_availability = $other_appointments_calendar->getDayAvailabilityWithOffice($payment_method, $temp_office, $temp_appo_dur);
				
				$accumulated = 0;
				for($j=0;$j<=14;$j++){
					
					if($selected_day+$j > $selected_month_top_limit && (strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($other_year."-".$other_month."-".($selected_day+$j - $selected_month_top_limit)))){
						$k = $selected_day+$j - $selected_month_top_limit;
						
						if(array_key_exists($k, $other_availability)){
							if($other_availability[$k] == 1){
								$bool_found = 1;
								break;
							}
						}
						else{
							if($calendar->checkAvailabilityDayWithOffices($payment_method, $k, $other_month, $other_year, $temp_appo_dur,$_office_array)){
								$bool_found = 1;
								break;
							}
						}
						
					}
					elseif($selected_day+$j <= $selected_month_top_limit && strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($selected_year."-".$selected_month."-".($selected_day+$j))){
						if(array_key_exists($selected_day+$j, $this_availability)){
							if($this_availability[$selected_day+$j] == 1){
								$bool_found = 1;
								break;
							}
						}
						else{
							if($calendar->checkAvailabilityDayWithOffices($payment_method, $selected_day+$j, $selected_month, $selected_year, $temp_appo_dur,$_office_array)){
								$bool_found = 1;
								break;
							}
						}
					}
					$rest = $selected_day-$j;
					//echo " array key exists" . $this_availability[$selected_day-$j] . "<--" . $rest . "  ";
					
					if(!(strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($selected_year."-".$selected_month."-".($selected_day-$j)))){
						continue;
					}
					
					if(array_key_exists($selected_day-$j, $this_availability)){
						if($this_availability[$selected_day-$j] == 1){
							$bool_found = 1;
							break;
						}
					}
					else{
						if($calendar->checkAvailabilityDayWithOffices($payment_method, $selected_day-$j, $selected_month, $selected_year, $temp_appo_dur,$_office_array)){
							$bool_found = 1;
							break;
						}
					}
					$accumulated = $accumulated + 1;
					//echo "Day: " . $accumulated;
				}
			}
			else{
				$this_availability = $this_appointments_calendar->getDayAvailabilityWithOffice($payment_method, $temp_office, $temp_appo_dur);
				$other_availability = $other_appointments_calendar->getDayAvailabilityWithOffice($payment_method, $temp_office, $temp_appo_dur);
				
				$accumulated = 0;
				for($j=0;$j<=14;$j++){
					
					if($j >= $selected_day && (strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($other_year."-".$other_month."-".($other_month_top_limit - ($j - $selected_day))))){
						$k = $other_month_top_limit - ($j - $selected_day) ;
						
						if(array_key_exists($k, $other_availability)){
							if($other_availability[$k] == 1){
								$bool_found = 1;
								break;
							}
						}
						else{
							if($calendar->checkAvailabilityDayWithOffices($payment_method, $k, $other_month, $other_year, $temp_appo_dur,$_office_array)){
								$bool_found = 1;
								break;
							}
						}
					}
					elseif($j < $selected_day && strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($selected_year."-".$selected_month."-".($selected_day-$j))){
						if(array_key_exists($selected_day-$j, $this_availability)){
							if($this_availability[$selected_day-$j] == 1){
								$bool_found = 1;
								break;
							}
						}
						else{
							if($calendar->checkAvailabilityDayWithOffices($payment_method, $selected_day-$j, $selected_month, $selected_year, $temp_appo_dur,$_office_array)){
								$bool_found = 1;
								break;
							}
						}
					}
					
					if(!(strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($selected_year."-".$selected_month."-".($selected_day+$j)))){
						continue;
					}
					
					if(array_key_exists($selected_day+$j, $this_availability)){
						if($this_availability[$selected_day+$j] == 1){
							$bool_found = 1;
							break;
						}
					}
					else{
						if($calendar->checkAvailabilityDayWithOffices($payment_method, $selected_day+$j, $selected_month, $selected_year, $temp_appo_dur,$_office_array)){
							$bool_found = 1;
							break;
						}
					}
				$accumulated = $accumulated + 1;
				//echo "Day: " . $accumulated;
				}
			}
// 			echo "<br>user" . $temp_username;
// 			echo "<br>office" . $temp_office;
			
			$score =  $this->distanceScoreFunction($accumulated);
			
// 			echo "<br> accumulated:" . $accumulated;
// 			echo "<br>";
			
// 			echo "<br> score:" . $score;
// 			echo "<br>";
			//$result_array[$temp_username] = $score;
			if($bool_found){
				$withpoints[$i]['ptsCalDist'] = $score;
// 				$result_array[$temp_username] = $score;
//  				$result_array[$temp_username] = $accumulated;
			}
			else{
				$withpoints[$i]['ptsCalDist'] = 0;
//				$result_array[$temp_username] = 0;
			}
		}
		return $withpoints;
	}
	
	private function distanceScoreFunction($accumulated){
		if($accumulated >= 0 && $accumulated<=7){
			$score = -$accumulated*0.7/7 + 1;
		}
		elseif($accumulated > 7 && $accumulated < 14){
			$score = -$accumulated*0.2/7 + 0.5;
		}
		elseif($accumulated >= 14){
			$score = 0;
			//$score = -$accumulated*0.1/13 + 2.7/13;
		}
		else{
			$score = 0;
		}
		
		return $score;
	}
	
	
	public function closestAppointmentDays($desired_date_str,$username, $username_e,$office,$payment_method,$appo_dur){
		//returns the closest day to the desired date
		$closest_days_array = [];
		
		$_office_array = array($office);
		
		$low_limit = new DateTime();
		$low_limit->add(new DateInterval('PT2H'));
		$low_limit_day = $low_limit->format("d");
		$low_limit_month = $low_limit->format("m");
		$low_limit_year = $low_limit->format("Y");
		
		$desired_date = DateTime::createFromFormat("d-m-Y",$desired_date_str);
		$selected_day = $desired_date->format("d");
		$selected_month = $desired_date->format("m");
		$selected_year = $desired_date->format("Y");
		
// 		if($desired_date<$low_limit){
// 			return NULL;
// 		}
		
		$stmt = $this->con->prepare("SELECT d FROM calendar_table WHERE y=? AND m=? ORDER BY d DESC LIMIT 1");
		$stmt->bind_param("ii", $selected_year,$selected_month);
		$stmt->execute();
		$tq = $stmt->get_result();
		$selected_month_top_limit_arr = mysqli_fetch_assoc($tq);
		$selected_month_top_limit = $selected_month_top_limit_arr['d'];
		
		//echo "Selected MONTH TOP:".$selected_month_top_limit;
		
		if($selected_day >= 15){
			$top = 1;
			$other_month_obj = $desired_date->add(new DateInterval('P14D'));
		}
		else{
			$top = 0;
			$other_month_obj= $desired_date->sub(new DateInterval('P14D'));
		}
		
		$other_month = $other_month_obj->format("m");
		$other_year = $other_month_obj->format("Y");
		
		$stmt = $this->con->prepare("SELECT d FROM calendar_table WHERE y=? AND m=? ORDER BY d DESC LIMIT 1");
		$stmt->bind_param("ii", $other_year,$other_month);
		$stmt->execute();
		$tq = $stmt->get_result();
		$other_month_top_limit_arr = mysqli_fetch_assoc($tq);
		$other_month_top_limit = $other_month_top_limit_arr['d'];
		
		$result_array = [];
		
		$temp_username = $username;
		$temp_username_e = $username_e;
		$temp_office = $office;
		$temp_appo_dur = $appo_dur;
		$bool_found = 0;
		
		$calendar = new Calendar($this->con, $temp_username, $username_e);
		
		//echo "user: " . $temp_username;
		
		$this_appointments_calendar = new Appointments_Calendar($this->con, $temp_username, $temp_username_e, $selected_year, $selected_month);
		$other_appointments_calendar = new Appointments_Calendar($this->con, $temp_username, $temp_username_e, $other_year, $other_month);
		
		if($top){
			$this_availability = $this_appointments_calendar->getDayAvailabilityWithOffice($payment_method, $temp_office, $temp_appo_dur);
			$other_availability = $other_appointments_calendar->getDayAvailabilityWithOffice($payment_method, $temp_office, $temp_appo_dur);
			
			//$accumulated = 0;
			for($j=0;$j<=14;$j++){

				if($selected_day+$j > $selected_month_top_limit && (strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($other_year."-".$other_month."-".($selected_day+$j - $selected_month_top_limit)))){
					$k = $selected_day+$j - $selected_month_top_limit;
					
					if(array_key_exists($k, $other_availability)){
						if($other_availability[$k] === 1){							
							$closest_days_array[] = "$other_year-$other_month-$k";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
					else{
						if($calendar->checkAvailabilityDayWithOffices($payment_method,$k,$other_month,$other_year,$temp_appo_dur,$_office_array)){
							$closest_days_array[] = "$other_year-$other_month-$k";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
					
				}
				elseif($selected_day+$j <= $selected_month_top_limit && (strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day)<= strtotime($selected_year."-".$selected_month."-".($selected_day+$j)))){

					if(array_key_exists($selected_day+$j, $this_availability)){
						$day = $selected_day+$j;
						if($this_availability[$day] === 1){
							
							$closest_days_array[] = "$selected_year-$selected_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
					else{
						if($calendar->checkAvailabilityDayWithOffices($payment_method, $selected_day+$j, $selected_month, $selected_year, $temp_appo_dur,$_office_array)){
							$day = $selected_day+$j;
							$closest_days_array[] = "$selected_year-$selected_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
				}
				$rest = $selected_day-$j;
				//echo " array key exists" . $this_availability[$selected_day-$j] . "<--" . $rest . "  ";
				
				if(!(strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($selected_year."-".$selected_month."-".($selected_day-$j)))){
					continue;
				}
				
				if(array_key_exists($selected_day-$j, $this_availability)){
					if($this_availability[$selected_day-$j] == 1){
						$day = $selected_day-$j;
						if(!in_array("$selected_year-$selected_month-$day", $closest_days_array)){
							
							$closest_days_array[] = "$selected_year-$selected_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
				}
				else{
					if($calendar->checkAvailabilityDayWithOffices($payment_method, $selected_day-$j, $selected_month, $selected_year, $temp_appo_dur,$_office_array)){
						$day = $selected_day-$j;
						if(!in_array("$selected_year-$selected_month-$day", $closest_days_array)){
							$closest_days_array[] = "$selected_year-$selected_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
				}
				//$accumulated = $accumulated + 1;
				//echo "Day: " . $accumulated;
			}
		}
		else{
			$this_availability = $this_appointments_calendar->getDayAvailabilityWithOffice($payment_method, $temp_office, $temp_appo_dur);
			$other_availability = $other_appointments_calendar->getDayAvailabilityWithOffice($payment_method, $temp_office, $temp_appo_dur);
			
			//$accumulated = 0;
			for($j=0;$j<=14;$j++){
				
				if($j >= $selected_day && (strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($other_year."-".$other_month."-".($other_month_top_limit - ($j - $selected_day))))){
					$k = $other_month_top_limit - ($j - $selected_day) ;
					
					if(array_key_exists($k, $other_availability)){
						if($other_availability[$k] == 1){
							$day = $k;
							$closest_days_array[] = "$other_year-$other_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
					else{
						if($calendar->checkAvailabilityDayWithOffices($payment_method, $k, $other_month, $other_year, $temp_appo_dur,$_office_array)){
							$day = $k;
							$closest_days_array[] = "$other_year-$other_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
				}
				elseif($j < $selected_day && strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($selected_year."-".$selected_month."-".($selected_day-$j))){
					if(array_key_exists($selected_day-$j, $this_availability)){
						if($this_availability[$selected_day-$j] == 1){
							$day = $selected_day-$j;
							
							$closest_days_array[] = "$selected_year-$selected_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
					else{
						if($calendar->checkAvailabilityDayWithOffices($payment_method, $selected_day-$j, $selected_month, $selected_year, $temp_appo_dur,$_office_array)){
							$day = $selected_day-$j;
// 							echo "now" . strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day);
							
// 							echo "here" . $other_year.$other_month.$day;
							$closest_days_array[] = "$selected_year-$selected_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
				}
				
				if(!(strtotime($low_limit_year."-".$low_limit_month."-".$low_limit_day) <= strtotime($selected_year."-".$selected_month."-".($selected_day+$j)))){
					continue;
				}
				
				if(array_key_exists($selected_day+$j, $this_availability)){
					if($this_availability[$selected_day+$j] == 1){
						$day = $selected_day+$j;
						if(!in_array("$selected_year-$selected_month-$day", $closest_days_array)){
							$closest_days_array[] = "$selected_year-$selected_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
				}
				else{
					if($calendar->checkAvailabilityDayWithOffices($payment_method, $selected_day+$j, $selected_month, $selected_year, $temp_appo_dur,$_office_array)){
						$day = $selected_day+$j;
						if(!in_array("$selected_year-$selected_month-$day", $closest_days_array)){
							$closest_days_array[] = "$selected_year-$selected_month-$day";
							if(sizeof($closest_days_array) >= 3){
								$bool_found = 1;
								break;
							}
						}
					}
				}
			}
		}
		
		return $closest_days_array;
	}
	
	
	public function ptsSymptoms($filteredDocs, $specialty, $symptoms_array, $apriori = 1){
		//TODO_bookmark example
	    $withpoints = $filteredDocs;
	    $doctors_array = array_column($filteredDocs,'username');
	    
	    $missing_symptom_penalty = 1/10;
		$probability_array = [];
		if ($specialty) {
		    foreach ($doctors_array as $key){
		        $probability_array[$key] = 0;
		    }
		} else if(!$specialty){    
        		if($doctors_array!== NULL){
        			$doctors_conditions = [];
        			
        			$doctors_conditions = array_map(
        					function($n){
        						return 'username = ' . $n;
        					},
        					$doctors_array);
        			
        			$sql = "SELECT * FROM symptoms_tables WHERE " . implode(' OR ', $doctors_conditions);
        			$stmt = $this->con->prepare($sql);
        			$stmt->execute();
        			$tables = $stmt->get_result();
        		}
        		
        		if($apriori === NULL){
        			
        			$doctor_apriori = 1/mysqli_num_rows($tables);
        			
        			foreach ($tables as $key => $arr){
        				$doc_username = $arr['username'];
        				$doc_table = $arr['symptoms_table'];
        				
        				$stmt = $this->con->prepare("SELECT * FROM $doc_table");
        				$stmt->execute();
        				$temp_symptoms = $stmt->get_result();
        				
        				$temp_cumulative_prob = 1;
        				$temp_penalty_counter = 0;
        				foreach ($temp_symptoms as $key2 => $arr_symptoms){
        					$temp_symptom = $arr_symptoms['symptoms'];
        					$temp_prob = $arr_symptoms['probability']/100;
        					
        					if(in_array($temp_symptom, $symptoms_array)){
        						$temp_cumulative_prob = $temp_cumulative_prob * $temp_prob;
        						$temp_penalty_counter = $temp_penalty_counter + 1;
        					}
        				}
        				$temp_cumulative_prob = $temp_cumulative_prob * pow($missing_symptom_penalty, (sizeof($symptoms_array) - $temp_penalty_counter));
        				$probability_array[$doc_username] = $temp_cumulative_prob;
        			}
        			
        			$norm_factor = array_sum($probability_array);
        			$probability_array= array_map(
        					function($n) use ($norm_factor){
        						return $n / $norm_factor;
        					},
        					$probability_array);
        			
        		}
            else{
                if($doctors_array!== NULL){
                    	$sql = "SELECT username,pat_seen FROM basic_info_doctors WHERE " . implode(' OR ', $doctors_conditions);
                    	$stmt = $this->con->prepare($sql);
                }
                else{
                    $stmt = $this->con->prepare("SELECT username,pat_seen FROM basic_info_doctors");
                }
                
                $stmt->execute();
                $pat_seen = $stmt->get_result();
                
                $num_pat_norm = 0;
                
                $doctor_apriori_array = [];
                foreach($pat_seen as $key => $value){
                	   $doctor_apriori_array[$value['username']] = $value['pat_seen'];
                	   $num_pat_norm = $num_pat_norm + $value['pat_seen'];
                }
                
               if($num_pat_norm==0){
                    foreach ($tables as $key => $arr){
                        $doc_username = $arr['username'];
                        $probability_array[$doc_username] = 0;
                    }
                } else {
                		foreach ($tables as $key => $arr){
                			$doc_username = $arr['username'];
                			$doc_table = $arr['symptoms_table'];
                			
                			$stmt = $this->con->prepare("SELECT * FROM $doc_table");
                			$stmt->execute();
                			$temp_symptoms = $stmt->get_result();
                			
                			$temp_cumulative_prob = $doctor_apriori_array[$doc_username]/$num_pat_norm;
                			$temp_penalty_counter = 0;
                			foreach ($temp_symptoms as $key2 => $arr_symptoms){
                				$temp_symptom = $arr_symptoms['symptoms'];
                				$temp_prob = $arr_symptoms['probability']/100;
                				
                				if(in_array($temp_symptom, $symptoms_array)){
                					$temp_cumulative_prob = $temp_cumulative_prob * $temp_prob;
                					$temp_penalty_counter = $temp_penalty_counter + 1;
                				}
                			}
                			
                			if(sizeof($symptoms_array) - $temp_penalty_counter == 0){
                			    $temp_cumulative_prob = 0;
                			}
                			else{
                			    $temp_cumulative_prob = $temp_cumulative_prob * pow($missing_symptom_penalty, (sizeof($symptoms_array) - $temp_penalty_counter));
                			}
                			
                			$probability_array[$doc_username] = $temp_cumulative_prob;
                		}
                		
                		$norm_factor = array_sum($probability_array);
                		$probability_array= array_map(
                				function($n) use ($norm_factor){
                					return $n / $norm_factor;
                				},
                				$probability_array);
                }
            }
        }
        	foreach($withpoints as &$key){
		    if(array_key_exists($key['username'], $probability_array)){
                $key['ptsSymptoms'] = $probability_array[$key['username']];
		    } else {
		        $key['ptsSymptoms']=0;
		    }
		}

		//arsort($probability_array);
		return $withpoints;
	}
	
	/**
	 * Establishes a distance between a pair of latitudes and longitudes
	 * @param float $latFrom Initial latitude
	 * @param float $lngFrom Initial longitude
	 * @param float $latTo Secondary latitude
	 * @param float $lngTo Secondary longitude
	 * @author JMZAM
	 * @return double Distance
	 **/
	public function latLngDist($latFrom, $lngFrom, $latTo, $lngTo) {
	    $rad = M_PI / 180;
	    $theta = $lngFrom - $lngTo;
	    $dist = sin($latFrom * $rad) * sin($latTo * $rad) +  cos($latFrom * $rad) * cos($latTo * $rad) * cos($theta * $rad);
	    return acos($dist) / $rad * 60 *  1.852;
	}
	/**
	 * Determines de type of location search according to inputs
	 * @param string $initialCity the name of the city to search in
	 * @param string $initialCityCode the code for the city
	 * @param array $initialPos an array with lat,lng pairs for search
	 * @param int $radius the search radius in km
	 * @return array locSearch <br>
	 *     [0] string $locSearchType Either city or position-radius            <br>
	 *     [1] string $city_name the name of the city                          <br>
	 *     [2] string $city_code the corresponding code for the city           <br>
	 *     [3] Array $coords an array with lat,lng pairs for search            <br>
	 *     [4] int $rad the radius
     * @author JMZAM
	 */
	public function locationSearchType($initialCity, $initialCityCode, $initialPos, $radius){
	    $locSearchType = NULL;
	    
// 	    city if initialcitycode is not empty and position is not an array of empties
// 	     search mode is city
// 	     radius is set to null
// 	     initialpos is set to null
//      searchpos if initialcitycode is empty and position is not an array of empties
//       search mode is pos rad
//       cityname is set to null
//       citycode is set to null
          
          
        if (!empty($initialCityCode) && !$initialPos)  {
	        $locSearchType = "city";
	        $city_name = $initialCity;
	        $city_code = $initialCityCode;
	        $coords = NULL;
	        $rad = NULL;
        } else if (empty($initialCityCode) && $initialPos) {
	        $locSearchType = "position-radius";
	        $city_name = NULL;
	        $city_code = NULL;
	        $coords = $initialPos;
	        $rad = $radius;
	    } else {
	        $locSearchType = "ERROR";
	        $city_name = $initialCity;
	        $city_code = $initialCityCode;
	        $coords = $initialPos;
	        $rad = $radius;
	    }
	    return [$locSearchType, $city_name, $city_code, $coords, $rad];
	}
	/**
	 * Checks and determines the type of search query (specialty or not) using the specializations data
	 * @param string $searchQuery The cleaned search query to look for in specializations database
	 * @return array <br>
	 *     [0] boolean $specialty TRUE if query was found FALSE if not         <br>
	 *     [1] array $spec the query statement for the specializations list    <br>
	 *     [2] string $searchQuery the search query input OR Array $symptoms the search query exploded by commas, up to the first five symptoms
	 * @author JMZAM    
	 */
	public function searchQueryType($searchQuery){
	    $searchQuery = "%".$searchQuery."%";
	    //Look for the searchQueryObject in the specializations database
	    $stmt = $this->con->prepare("SELECT id FROM specializations WHERE en_search LIKE ? OR es_search LIKE ?");
	    $stmt->bind_param("ss",$searchQuery, $searchQuery);
	    $stmt->execute();
	    $query = $stmt->get_result();
                            	    // Just in case you need to map the statement first
                            	    // $spec_list = [];
                            	    // $spec_list = array_map(
                            	    // 	function($n){
                            	    // 	return "specializations LIKE" . $n;
                            	    // 	},
                            	    // $$spec);
	    $spec = [];
	    if ($query->num_rows ===0){
	        $specialty = FALSE;
	    } else if ($query->num_rows >0){
	        $specialty = TRUE;
	        while($arr = mysqli_fetch_row($query)){
	            $spec[] = "specializations LIKE '%\"".$arr['0']."\"%'";
	        }
	    }
	    $specializationsQuery = implode(' OR ', $spec);
	    if($specialty){
	       return [$specialty, $specializationsQuery, substr($searchQuery,1,-1)];
	    } else if(!$specialty) {
	        $symptomsLong = explode(",", substr($searchQuery,1,-1) ,6);
	        $symptoms = array_slice($symptomsLong,0,5);
            return [$specialty, $specializationsQuery, $symptoms];
	    }
	}
	/**
	 * Queries the database to generate a filtered version of doctors to rank
	 * @param string $locSearchType Either city or position-radius
	 * @param boolean $specialty TRUE if query was found FALSE if not
	 * @param string $spec the query statement for the specializations list
	 * @param string $insurance Either "all" or the code of the insurance to search
	 * @param TxtReplace $searchQuery A cleaned version of the input string to query
	 * @param int $initialCityCode The code for the initial city to search in, otherwise NULL
	 * @param array $initialPos An array for the base position to search with lat, lng (optional)
	 * @param double $radius The search radius (optional)
	 * @author JMZAM 
	 * @return array List:
     * <br> &nbsp;&nbsp; array <b> filteredDocs </b> a table with the corresponding columns to use in the search ranking 
	 * <br> &nbsp;&nbsp; string <b> locSearchType </b> a variable with values 'city' or 'position-radius' according to the search type 
	 * <br> &nbsp;&nbsp; boolean <b> specialty </b> a variable that is TRUE if there was a match in the specialties database, FALSE oftherwise
	 **/
	public function genFilteredDocs ($locSearchType,$specialty, $spec, $insurance,$searchQuery_obj,$initialCityCode,$initialPos,$radius){
	    // Prepares the insurance variable for the query
	    if($insurance == "all") {
	        $insuranceQ = "%a:%";
	    } else {
	        $insuranceQ = "%\"".$insurance."\"%";
	    }
	    $initialCity = $initialCityCode;
	    $searchPos = $initialPos;
	    $searchRad = $radius+0;
	    //The second filter will be the city
	    
	    // Do a search query based on location search type and specialty
	    // $sql = "SELECT * FROM(
	    //SELECT username, md_conn, pat_seen, pat_foll, pat_inter, pat_rec, ad1ln1 as line1, ad1ln2 as line2, ad1ln3 as line3, ad1lat as lat, ad1lng as lng, ad1city as city, adcountry from basic_info_doctors
	    //where (" . implode(' OR ', $spec).") AND (insurance_accepted_1 LIKE ?) AND (ad1city = ?) union all
	    //SELECT username, md_conn, pat_seen, pat_foll, pat_inter, pat_rec, ad2ln1 as line1, ad2ln2 as line2, ad2ln3 as line3, ad2lat as lat, ad2lng as lng, ad2city as city, adcountry from basic_info_doctors
	    //where (" . implode(' OR ', $spec).") AND (insurance_accepted_2 LIKE ?) AND (ad2city = ?) union all
	    //SELECT username, md_conn, pat_seen, pat_foll, pat_inter, pat_rec, ad3ln1 as line1, ad3ln2 as line2, ad3ln3 as line3, ad3lat as lat, ad3lng as lng, ad3city as city, adcountry from basic_info_doctors
	        //where (" . implode(' OR ', $spec).") AND (insurance_accepted_3 LIKE ?) AND (ad3city = ?))
	    //derived GROUP BY username, lat, lng LIMIT 500";
	    $filteredDocs=[];
	    switch($locSearchType){
	        case 'city':{
	            switch($specialty){
	                case TRUE:{
	                    $sql = "SELECT * FROM(
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, '1' as office from basic_info_doctors
                            where (" . $spec.") AND (insurance_accepted_1 LIKE ?) AND (ad1city = ?) union all
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, '2' as office from basic_info_doctors
                            where (" . $spec.") AND (insurance_accepted_2 LIKE ?) AND (ad2city = ?) AND ad2nick != '' union all
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, '3' as office from basic_info_doctors
                            where (" . $spec.") AND (insurance_accepted_3 LIKE ?) AND (ad3city = ?) AND ad3nick != '' )
                        derived LIMIT 200";
	                    $stmt = $this->con->prepare($sql);
	                    $stmt->bind_param("ssssss", $insuranceQ, $initialCity, $insuranceQ, $initialCity, $insuranceQ, $initialCity);
	                    $stmt->execute();
	                    $docQuery = $stmt->get_result();
	                    while($arr = mysqli_fetch_assoc($docQuery)){
	                        $filteredDocs[]=$arr;
	                    }
	                    break;
	                }
	                case FALSE:{
	                    $sql = "SELECT * FROM(
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, '1' as office from basic_info_doctors
                            where (insurance_accepted_1 LIKE ?) AND (ad1city = ?) union all
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, '2' as office from basic_info_doctors
                            where (insurance_accepted_2 LIKE ?) AND (ad2city = ?) AND ad2nick != ''  union all
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, '3' as office from basic_info_doctors
                            where (insurance_accepted_3 LIKE ?) AND (ad3city = ?) AND ad3nick != '' )
                        derived LIMIT 200";
	                    $stmt = $this->con->prepare($sql);
	                    $stmt->bind_param("ssssss", $insuranceQ, $initialCity, $insuranceQ, $initialCity, $insuranceQ, $initialCity);
	                    $stmt->execute();
	                    $docQuery = $stmt->get_result();
	                    while($arr = mysqli_fetch_assoc($docQuery)){
	                        $filteredDocs[]=$arr;
	                    }
	                    break;
	                }
	            }
	            break;
	        }
	        case 'position-radius':{
	            switch($specialty){
	                case TRUE:{
	                    $sql = "SELECT * FROM(
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, ad1lat as lat, ad1lng as lng, '1' as office from basic_info_doctors
                            where (" .$spec.") AND (insurance_accepted_1 LIKE ?) union all
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, ad2lat as lat, ad2lng as lng, '2' as office from basic_info_doctors
                            where (" . $spec.") AND (insurance_accepted_2 LIKE ?) AND ad2nick != '' union all
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, ad3lat as lat, ad3lng as lng, '3' as office from basic_info_doctors
                            where (" . $spec.") AND (insurance_accepted_3 LIKE ?) AND ad3nick != '')
                        derived LIMIT 300";
	                    $stmt = $this->con->prepare($sql);
	                    $stmt->bind_param("sss", $insuranceQ, $insuranceQ, $insuranceQ);
	                    $stmt->execute();
	                    $docQuery = $stmt->get_result();
	                    while($arr = mysqli_fetch_assoc($docQuery)){
	                        $arr['distance']=$this->latLngDist($searchPos['lat'], $searchPos['lng'], $arr['lat'], $arr['lng']);
	                        if($arr['distance']<$searchRad){
	                            $filteredDocs[]=$arr;
	                        }//else echo latLngDist($searchPos[0], $searchPos[1], $arr['lat'], $arr['lng'])."<br>";
	                    }
	                    break;
	                }
	                case FALSE:{
	                    $sql = "SELECT * FROM(
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, ad1lat as lat, ad1lng as lng, '1' as office from basic_info_doctors
                            where (insurance_accepted_1 LIKE ?) union all
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, ad2lat as lat, ad2lng as lng, '2' as office from basic_info_doctors
                            where (insurance_accepted_2 LIKE ?) AND ad2nick != '' union all
                        SELECT username, md_conn, pat_seen + pat_foll as pat_conn, coalesce(pat_rec/pat_seen,0) as prop_recur, coalesce(pat_seen/(pat_seen+pat_foll),0) as prop_seen, ad3lat as lat, ad3lng as lng, '3' as office from basic_info_doctors
                            where (insurance_accepted_3 LIKE ?) AND ad3nick != '')
                        derived LIMIT 300";
	                    $stmt = $this->con->prepare($sql);
	                    $stmt->bind_param("sss", $insuranceQ, $insuranceQ, $insuranceQ);
	                    $stmt->execute();
	                    $docQuery = $stmt->get_result();
	                    while($arr = mysqli_fetch_assoc($docQuery)){
	                        $arr['distance']=$this->latLngDist($searchPos['lat'], $searchPos['lng'], $arr['lat'], $arr['lng']);
	                        if($arr['distance']<$searchRad){
	                            $filteredDocs[]=$arr;
	                        }
	                    }
	                    break;
	                }
	            }
	            break;
	        }
	    }
	    
	    $List = array($filteredDocs, $locSearchType, $specialty);
	    return $List;
	}
	
	/**
	 * Verify whether a lat,lng pair is within a radius from a lat,lng reference
	 * @param float $latFrom Initial latitude
	 * @param float $lngFrom Initial longitude
	 * @param float $latTo Secondary latitude
	 * @param float $lngTo Secondary longitude
	 * @param double $radius The search radius
	 * @author JMZAM
	 * @return Within: TRUE if inside the radius, FALSE if not
	 **/
	private function withinRad($latFrom, $lngFrom, $latTo, $lngTo, $radius) {
	    $within = false;
	    if(latLngDist($latFrom, $lngFrom, $latTo, $lngTo)<$radius)
	        $within=true;
        return $within;
	}
	/**
	 * Assign points for a search based on md connections
	 * @param array $filteredDocs The array of doctors with id, location and md_conn variable
	 * @author JMZAM
	 * @return $withpoints An array including the MD connections points assigned, without the md_conn column
	 **/
	private function ptsMDconn ($filteredDocs){
	    $withpoints = $filteredDocs;
	    $maxMDconns = max(array_column($withpoints,'md_conn'));
	    foreach($withpoints as &$key){
	        if ($maxMDconns!=0)
	           $key['ptsMDconn'] = 10*$key['md_conn']/$maxMDconns;
	        else
	            $key['ptsMDconn'] = 0;
	        unset($key['md_conn']);
	    }
	    return $withpoints;
	}
	/**
	 * Assign points for a search based on patient connections
	 * @param array $filteredDocs The array of doctors with id, location and pat_conn variable
	 * @author JMZAM
	 * @return $withpoints An array including the patient connections points assigned, without the pat_conn column
	 **/
	private function ptsPTconn ($filteredDocs){
	    $withpoints = $filteredDocs;
	    $maxPTconns = max(array_column($withpoints,'pat_conn'));
	    foreach($withpoints as &$key){
	        if($maxPTconns!=0)
	           $key['ptsPTconn'] = 10*$key['pat_conn']/$maxPTconns;
	        else 
	           $key['ptsPTconn'] = 0;
	        unset($key['pat_conn']);
	    }
	    return $withpoints;
	}
	/**
	 * Assign points for a search based on proportion of recurring patients
	 * @param array $filteredDocs The array of doctors with id, location and prop_recur variable
	 * @author JMZAM
	 * @return $withpoints An array including the recurring patient proportion points assigned, without the prop_recur column
	 **/
	private function ptsPropRec ($filteredDocs){
	    $withpoints = $filteredDocs;
	    $maxPropRecur = max(array_column($withpoints,'prop_recur'));
	    foreach($withpoints as &$key){
	        if($maxPropRecur != 0)
	           $key['ptsPropRec'] = 10*$key['prop_recur']/$maxPropRecur;
	        else 
	            $key['ptsPropRec'] = 0;
	        unset($key['prop_recur']);
	    }
	    return $withpoints;
	}
	/**
	 * Assign points for a search based on proportion of seen vs following patients
	 * @param array $filteredDocs The array of doctors with id, location and prop_seen variable
	 * @author JMZAM
	 * @return $withpoints An array including the seen patient proportion points assigned, without the prop_seen column
	 **/
	private function ptsPropSeen ($filteredDocs){
	    $withpoints = $filteredDocs;
	    $maxPropSeen = max(array_column($withpoints,'prop_seen'));
	    foreach($withpoints as &$key){
	        if($maxPropSeen !=0)
	           $key['ptsPropSeen'] = 10*$key['prop_seen']/$maxPropSeen;
	        else 
	            $key['ptsPropSeen'] = 0;
	        unset($key['prop_seen']);
	    }
	    return $withpoints;
	}
	/**
	 * Assign points for a search based on distance to an initial location and the search radius
	 * @param array $filteredDocs The array of doctors with id, location and prop_seen variable
	 * @param string $locsearchtype "city" or "position-radius"
	 * @param double $radius The search radius
	 * @author JMZAM
	 * @return $withpoints An array including the seen distance points assigned, without the distance, lat and lng columns
	 **/
	private function ptsDist ($filteredDocs, $locSearchType, $radius=NULL){
	    $withpoints = $filteredDocs;
	    switch($locSearchType){
	        case 'city':{
	            foreach($withpoints as &$key){
	                $key['ptsDist'] = 0;
	            }
	            break;
	        }
	        case 'position-radius':{
	            foreach($withpoints as &$key){
	                $key['ptsDist'] = 10*(1-$key['distance']/$radius);
	                unset($key['distance']);
	                unset($key['lat']);
	                unset($key['lng']);
	            }
	            break;
	        }
	    }
	    return $withpoints;
	}
	/**
	 * Assign points for a search based on the previously created points functions
	 * @param array $filteredDocs The array of doctors returned from genFilteredDocs
	 * @param boolean $specialty did the query hit any specialties
	 * @param array $symptoms the search query or the symptoms array 
	 * @param string $locSearchType either 'city' or 'position-radius'
	 * $param int $radius the search radius 
	 * @author JMZAM
	 * @return $withpoints An array including doctor id, office and several types of points
	 **/
	public function givePoints($filteredDocs, $specialty, $symptoms, $locSearchType,$desired_date_str,$payment_method,$radius=NULL){
	    $withpoints = $filteredDocs;
	    $withpoints = $this->ptsMDconn($withpoints);
	    $withpoints = $this->ptsPTconn($withpoints);
	    $withpoints = $this->ptsPropRec($withpoints);
	    $withpoints = $this->ptsPropSeen($withpoints);
	    $withpoints = $this->ptsDist($withpoints,$locSearchType,$radius);
	    $withpoints = $this->ptsSymptoms($withpoints, $specialty, $symptoms);
	    $withpoints = $this->ptsCalDistScore($withpoints, $desired_date_str, $payment_method);
	    return $withpoints;
	    //TODO_bookmark;
	}
	
	public function ptsCalDistScore($filteredDocs,$desired_date_str,$payment_method){
		$withpoints = $filteredDocs;
		$withpoints = $this->appointmentDistanceScore($desired_date_str,$withpoints,$payment_method);
		return $withpoints;
		//TODO:bookmak
	}
	
	/**
	* Verify whether a doctor appears more than once in the filteredDocs search
	* @param array $filteredDocs the filteredDocs array generated by the genFilteredDocs function or treated by the givePoints function
	* @author JMZAM
	* @return array $docCounts an array with the keys the id for each doc and the value the number of times it appears
	**/
	public function docCount($filteredDocs) {
	    $docCounts = array_count_values(array_column($filteredDocs, 'username'));
	    return $docCounts;
	}
	/**
	 * Rank the doctors based on a set of points
	 * @param array $withpoints the withpoints array generated by givePoints function
	 * @author JMZAM
	 * @return array $docRanks an array with a doctor id, office number and ranking
	 **/
	public function rankDocs ($withpoints){
	    $numCriteria = count($withpoints[0])-2;
	    $weights = array_fill(0,$numCriteria,1/$numCriteria);
	    //$weights = array(0.5,0,0,0,0);
	    //$weights= array(implode(',', array_fill(0,9,1/9)));
	    foreach($withpoints as &$key){
	        if(!isset($key['ptsCalDist'])){
	            $key['ptsCalDist'] = 0;
	        }
	        $weightedPoints =  array_map(function($weights, $points){
	            return $weights * $points;
	        },$weights,array($key['ptsMDconn'],$key['ptsPTconn'], $key['ptsPropRec'], $key['ptsPropSeen'], $key['ptsDist'], $key['ptsCalDist']));
	        
	        $key['rankPoints']=
	        $points[]= array_sum($weightedPoints);
	        unset($key['ptsMDconn'],$key['ptsPTconn'], $key['ptsPropRec'], $key['ptsPropSeen'], $key['ptsDist'],$key['ptsCalDist']);
	    }
	    array_multisort($points, SORT_DESC, $withpoints);
	    return $withpoints;
	}
}
?>