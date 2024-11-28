<?php  
	class TimeStamp{

		public function __construct(){
		}

		public function getTimeStamp($interval){
			//DO NOT USE ANYMORE use next function with objects getTimeStampFromDates
			$lang = $_SESSION['lang'];
			
			switch ($lang){
				
				case("en"):
					if($interval->y >= 1){
						if($interval->y == 1){
							$time_message = $interval->y . " year ago."; // 1 year ago.
						}
						else{
							$time_message = $interval->y . " years ago."; // eg 2 years ago.
						}
					}
					else if($interval->m >= 1){
						if($interval->d == 0){
							$days = " ago.";
						}
						else if($interval->d == 1){
							$days = $interval->d . " day ago.";
						}
						else{
							$days = $interval->d . " days ago.";
						}
						
						if($interval->m == 1){
							$time_message = $interval->m . " month, " . $days;
						}
						else{
							$time_message = $interval->m . " months, " . $days;
						}
					}
					else if($interval->d >= 1){
						if($interval->d == 1){
							$time_message = " Yesterday.";
						}
						else{
							$time_message = $interval->d . " days ago.";
						}
					}
					else if($interval->h >= 1){
						if($interval->h == 1){
							$time_message = $interval->h . " hour ago.";
						}
						else{
							$time_message = $interval->h . " hours ago.";
						}
					}
					else if($interval->i >= 1){
						if($interval->i == 1){
							$time_message = $interval->i . " minute ago.";
						}
						else{
							$time_message = $interval->i . " minutes ago.";
						}
					}
					else{
						if($interval->s < 30){
							$time_message = " Just now.";
						}
						else{
							$time_message = " Seconds ago.";
						}
					}
					break;
					
				case("es"):
					if($interval->y >= 1){
						if($interval->y == 1){
							$time_message = " Hace ". $interval->y . " año."; // 1 year ago.
						}
						else{
							$time_message = " Hace ". $interval->y . " años."; // eg 2 years ago.
						}
					}
					else if($interval->m >= 1){
						if($interval->d == 0){
							$days = "";
						}
						else if($interval->d == 1){
							$days = $interval->d . " día.";
						}
						else{
							$days = $interval->d . " días.";
						}
						
						if($interval->m == 1){
							$time_message = " Hace ". $interval->m . " mes y " . $days;
						}
						else{
							$time_message = $interval->m . " meses y " . $days;
						}
					}
					else if($interval->d >= 1){
						if($interval->d == 1){
							$time_message = " Ayer.";
						}
						else{
							$time_message = " Hace ".$interval->d . " días.";
						}
					}
					else if($interval->h >= 1){
						if($interval->h == 1){
							$time_message = " Hace ".$interval->h . " hora.";
						}
						else{
							$time_message = " Hace ".$interval->h . " horas.";
						}
					}
					else if($interval->i >= 1){
						if($interval->i == 1){
							$time_message = " Hace ".$interval->i . " minuto.";
						}
						else{
							$time_message = " Hace ".$interval->i . " minutos.";
						}
					}
					else{
						if($interval->s < 30){
							$time_message = " Ahora.";
						}
						else{
							$time_message = " Hace unos segundos.";
						}
					}
					break;
			}
			

			return $time_message;
		}
		
		public function getTimeStampFromDates($date_ini, $date_fin){
			//example input date("Y-m-d H:i:s") for current date and time
			
			$lang = $_SESSION['lang'];
			
			if(strlen($date_ini) == 4){
				$start_date = date_create_from_format('Y',$date_ini);
			}
			else{
				$start_date = new DateTime($date_ini); //Time of post
			}
			
			$end_date = new DateTime($date_fin); //Current time
			$interval = $start_date->diff($end_date); //Difference between dates
			
			switch ($lang){
				
				case("en"):
					if($interval->y >= 1){
						if($interval->y == 1){
							$time_message = $interval->y . " year ago."; // 1 year ago.
						}
						else{
							$time_message = $interval->y . " years ago."; // eg 2 years ago.
						}
					}
					else if($interval->m >= 1){
						if($interval->d == 0){
							$days = " ago.";
						}
						else if($interval->d == 1){
							$days = $interval->d . " day ago.";
						}
						else{
							$days = $interval->d . " days ago.";
						}
						
						if($interval->m == 1){
							$time_message = $interval->m . " month, " . $days;
						}
						else{
							$time_message = $interval->m . " months, " . $days;
						}
					}
					else if($interval->d >= 1){
						$start_hours = $start_date->format("H");
						$start_mins = $start_date->format("i");
						$hours_til_mid = 24 - $start_hours;
						$mins_til_change = 59 - $start_mins;
						
						$interval_h = $interval->h;
						$interval_m = $interval->m;
						
						$diff_hours = $hours_til_mid-$interval_h;
						$diff_mins = $mins_til_change-$interval_m;
						
						if($diff_hours< 1 || ($diff_hours== 1 && $diff_mins< 1)){
							$days_interval = $interval->d + 1;
						}
						else{
							$days_interval = $interval->d;
						}
						
						if($days_interval == 1){
							$time_message = " Yesterday.";
						}
						else{
							$time_message = $days_interval . " days ago.";
						}
					}
					else{
						$start_hours = $start_date->format("H");
						$start_mins = $start_date->format("i");
						$hours_til_mid = 24 - $start_hours;
						$mins_til_change = 59 - $start_mins;
						
						$interval_h = $interval->h;
						$interval_m = $interval->m;
						
						$diff_hours = $hours_til_mid-$interval_h;
						$diff_mins = $mins_til_change-$interval_m;
						
						if($diff_hours< 1 || ($diff_hours== 1 && $diff_mins< 1)){
							$time_message = " Yesterday.";
						}
						else{
							if($interval->h >= 1){
								if($interval->h == 1){
									$time_message = $interval->h . " hour ago.";
								}
								else{
									$time_message = $interval->h . " hours ago.";
								}
							}
							else if($interval->i >= 1){
								if($interval->i == 1){
									$time_message = $interval->i . " minute ago.";
								}
								else{
									$time_message = $interval->i . " minutes ago.";
								}
							}
							else{
								if($interval->s < 30){
									$time_message = " Just now.";
								}
								else{
									$time_message = " Seconds ago.";
								}
							}
						}
					}
					break;
					
				case("es"):
					if($interval->y >= 1){
						if($interval->y == 1){
							$time_message = " Hace ".$interval->y . " año."; // 1 year ago.
						}
						else{
							$time_message = " Hace ".$interval->y . " años."; // eg 2 years ago.
						}
					}
					else if($interval->m >= 1){
						if($interval->d == 0){
							$days = "";
						}
						else if($interval->d == 1){
							$days = $interval->d . " día.";
						}
						else{
							$days = $interval->d . " días.";
						}
						
						if($interval->m == 1){
							$time_message = " Hace ".$interval->m . " mes y " . $days;
						}
						else{
							$time_message = " Hace ".$interval->m . " meses y " . $days;
						}
					}
					else if($interval->d >= 1){
						$start_hours = $start_date->format("H");
						$start_mins = $start_date->format("i");
						$hours_til_mid = 24 - $start_hours;
						$mins_til_change = 59 - $start_mins;
						
						$interval_h = $interval->h;
						$interval_m = $interval->m;
						
						$diff_hours = $hours_til_mid-$interval_h;
						$diff_mins = $mins_til_change-$interval_m;
						
						if($diff_hours< 1 || ($diff_hours== 1 && $diff_mins< 1)){
							$days_interval = $interval->d + 1;
						}
						else{
							$days_interval = $interval->d;
						}
						
						if($days_interval == 1){
							$time_message = " Ayer.";
						}
						else{
							$time_message = " Hace " . $days_interval . " días.";
						}
					}
					else{
						$start_hours = $start_date->format("H");
						$start_mins = $start_date->format("i");
						$hours_til_mid = 24 - $start_hours;
						$mins_til_change = 59 - $start_mins;
						
						$interval_h = $interval->h;
						$interval_m = $interval->m;
						
						$diff_hours = $hours_til_mid-$interval_h;
						$diff_mins = $mins_til_change-$interval_m;
						
						if($diff_hours< 1 || ($diff_hours== 1 && $diff_mins< 1)){
							$time_message = " Ayer.";
						}
						else{
							if($interval->h >= 1){
								if($interval->h == 1){
									$time_message = " Hace " . $interval->h . " hora.";
								}
								else{
									$time_message = " Hace " . $interval->h . " horas.";
								}
							}
							else if($interval->i >= 1){
								if($interval->i == 1){
									$time_message = " Hace " . $interval->i . " minutos.";
								}
								else{
									$time_message = " Hace " . $interval->i . " minutes ago.";
								}
							}
							else{
								if($interval->s < 30){
									$time_message = " Ahora.";
								}
								else{
									$time_message = " Hace unos segundos.";
								}
							}
						}
					}
					break;
			}			
			return $time_message;
		}
		
		public function getTimeStampFromDates_noSmallIntervals($date_ini/*time of event*/, $date_fin/*curent time*/){
			//For intervals smaller than a day, the hour is printed instead
			//example input date("Y-m-d H:i:s") for current date and time
			
			$lang = $_SESSION['lang']; 
			
			if(strlen($date_ini) == 4){
				$start_date = date_create_from_format('Y',$date_ini);
			}
			else{
				$start_date = new DateTime($date_ini); //Time of post
			}
			
			$end_date = new DateTime($date_fin); //Current time
			$interval = $start_date->diff($end_date); //Difference between dates
			
			switch ($lang){
				
				case("en"):
					if($interval->y >= 1){
						if($interval->y == 1){
							$time_message = $interval->y . " year ago."; // 1 year ago.
						}
						else{
							$time_message = $interval->y . " years ago."; // eg 2 years ago.
						}
					}
					else if($interval->m >= 1){
						if($interval->d == 0){
							$days = " ago.";
						}
						else if($interval->d == 1){
							$days = $interval->d . " day ago.";
						}
						else{
							$days = $interval->d . " days ago.";
						}
						
						if($interval->m == 1){
							$time_message = $interval->m . " month " . $days;
						}
						else{
							$time_message = $interval->m . " months " . $days;
						}
					}
					else if($interval->d >= 1){
						
						$start_hours = $start_date->format("H");
						$start_mins = $start_date->format("i");
						$hours_til_mid = 24 - $start_hours;
						$mins_til_change = 59 - $start_mins;
						
						$interval_h = $interval->h;
						$interval_m = $interval->m;
						
						$diff_hours = $hours_til_mid-$interval_h;
						$diff_mins = $mins_til_change-$interval_m;
						
						if($diff_hours< 1 || ($diff_hours== 1 && $diff_mins< 1)){
							$days_interval = $interval->d + 1;
						}
						else{
							$days_interval = $interval->d;
						}
						
						if($days_interval == 1){
							$time_message = " Yesterday.";
						}
						else{
							$time_message = $days_interval . " days ago.";
						}
					}
					else{
						$start_hours = $start_date->format("H");
						$start_mins = $start_date->format("i");
						$hours_til_mid = 24 - $start_hours;
						$mins_til_change = 59 - $start_mins;
						
						$interval_h = $interval->h;
						$interval_m = $interval->m;
						
						$diff_hours = $hours_til_mid-$interval_h;
						$diff_mins = $mins_til_change-$interval_m;
						
						if($diff_hours< 1 || ($diff_hours== 1 && $diff_mins< 1)){
							$time_message = " Yesterday.";
						}
						else{
							$time_message = $start_date->format("g:ia");
						}
					}
					break;
					
				case("es"):
					if($interval->y >= 1){
						if($interval->y == 1){
							$time_message = " Hace " . $interval->y . " año."; // 1 year ago.
						}
						else{
							$time_message = " Hace " .$interval->y . " años."; // eg 2 years ago.
						}
					}
					else if($interval->m >= 1){
						if($interval->d == 0){
							$days = "";
						}
						else if($interval->d == 1){
							$days = $interval->d . " día.";
						}
						else{
							$days = $interval->d . " días.";
						}
						
						if($interval->m == 1){
							$time_message = " Hace " .$interval->m . " mes y " . $days;
						}
						else{
							$time_message = " Hace " .$interval->m . " meses y " . $days;
						}
					}
					else if($interval->d >= 1){
						
						$start_hours = $start_date->format("H");
						$start_mins = $start_date->format("i");
						$hours_til_mid = 24 - $start_hours;
						$mins_til_change = 59 - $start_mins;
						
						$interval_h = $interval->h;
						$interval_m = $interval->m;
						
						$diff_hours = $hours_til_mid-$interval_h;
						$diff_mins = $mins_til_change-$interval_m;
						
						if($diff_hours< 1 || ($diff_hours== 1 && $diff_mins< 1)){
							$days_interval = $interval->d + 1;
						}
						else{
							$days_interval = $interval->d;
						}
						
						if($days_interval == 1){
							$time_message = " Ayer.";
						}
						else{
							$time_message = " Hace " .$days_interval . " días.";
						}
					}
					else{
						$start_hours = $start_date->format("H");
						$start_mins = $start_date->format("i");
						$hours_til_mid = 24 - $start_hours;
						$mins_til_change = 59 - $start_mins;
						
						$interval_h = $interval->h;
						$interval_m = $interval->m;
						
						$diff_hours = $hours_til_mid-$interval_h;
						$diff_mins = $mins_til_change-$interval_m;
						
						if($diff_hours< 1 || ($diff_hours== 1 && $diff_mins< 1)){
							$time_message = " Ayer.";
						}
						else{
							$time_message = $start_date->format("g:ia");
						}
					}
					break;
			}
			

			return $time_message;
		}
		
	}

?>