<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/TxtReplace.php");
$crypt = new Crypt();
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
		$userLoggedIn = $temp_user;
		$userLoggedIn_e = $temp_user_e;
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
	
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	$txtrep = new TxtReplace();
	$lang = $_SESSION['lang'];
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

//$results_array[$_t_cid] = array("matches" => 1,"content"=> array($category=> array($_highlited_string)), "date"=>$arr['day'] . '/' . $arr['month'] . '/' . $arr['year'], "name" => $name);

function quick_sort_w_repeated_items($my_array){
	$loe = $gt = array();
	
	$count = count($my_array);
	if($count < 2)
	{
		return $my_array;
	}
	
	$pivot_key = key($my_array);
	$pivot = array_shift($my_array);
	
	$all_same = TRUE;
	
	foreach($my_array as $val){
		if(isset($prev)){
			if($prev != $val){
				$all_same = FALSE;
			}
		}
		
		if($count - 1 < 3){
			$all_same = FALSE;
		}
		
		if($val <= $pivot)
		{
			$loe[] = $val;
		}elseif ($val > $pivot)
		{
			$gt[] = $val;
		}
		
		$prev = $val;
	}
	
	if($all_same){
		
		if($val < $pivot){
			return array_merge($my_array,array($pivot_key=>$pivot));
		}
		else{
			return array_merge(array($pivot_key=>$pivot),$my_array);
		}
	}
	else{
		return array_merge(quick_sort_w_repeated_items($loe),array($pivot_key=>$pivot),quick_sort_w_repeated_items($gt));
	}
}


$search_term = trim($txtrep->entities($_REQUEST['search']));
$isDoctor = $_REQUEST['isDoctor'];
	
if($search_term != ""){
	if($isDoctor){
		$appo_details = $user_obj->getAppointmentsDetails_Doctor();
		$appo_dates = $user_obj->getAppointmentsCalendar();
		$categories = array("name","symptoms","plan","notes");
		
		$sql = "SELECT A.consult_id, A.patient_username, A.plan, A.notes, B.year, B.month, B.day
						FROM " . $appo_details . " AS A LEFT JOIN " . $appo_dates . " AS B
						ON A.consult_id = B.consult_id
						ORDER BY B.year DESC, B.month DESC, B.day DESC, B.time_start DESC";
		
		$subject = 'patient_username';
		$appo_symptoms = $user_obj->getAppointmentsSymptoms_Doctor();
	}
	else{
		$appo_details = $user_obj->getAppointmentsDetails_Patient();
		$appo_dates = $user_obj->getAppointmentsCalendar_Patient();
		$categories = array("name","specialty","symptoms","plan");
		
		$sql = "SELECT A.consult_id, A.doctor_username, A.specializations, A.plan, A.private_plan, B.year, B.month, B.day
						FROM " . $appo_details . " AS A LEFT JOIN " . $appo_dates . " AS B
						ON A.consult_id = B.consult_id
						ORDER BY B.year DESC, B.month DESC, B.day DESC, B.time_start DESC";
		
		$subject = 'doctor_username';
		$appo_symptoms = $user_obj->getAppointmentsSymptoms_Patient();
	}
	$stmt = $con->prepare($sql);
	
	$stmt->execute();
	$query = $stmt->get_result();
	
	//fnmatch("*gr[ae]y", $color)
	
	$query_num_res = mysqli_num_rows($query);
	
	$max_num_results = 20;
	
	$partial_str = '';
	
	$search_term_arr = explode(" ",$search_term);
	
	if(sizeof($search_term_arr) < 2){
	
		foreach($categories as $cat_key => $category){
			
			switch ($lang){
				
				case("en"):
					switch($category){
						case "specialty":
							$cat_title = "Specialty";
							break;
						case "name":
							$cat_title= "Name";
							break;
						case "plan":
							$cat_title= "Plan";
							break;
						case "symptoms":
							$cat_title= "Symptoms";
							break;
						case "notes":
							$cat_title= "Notes";
							break;
					}
					break;
					
				case("es"):
					switch($category){
						case "specialty":
							$cat_title = "Especialidad";
							break;
						case "name":
							$cat_title= "Nombre";
							break;
						case "plan":
							$cat_title= "Plan";
							break;
						case "symptoms":
							$cat_title= "Síntomas";
							break;
						case "notes":
							$cat_title= "Notas";
							break;
					}
					break;
			}
			

			
			if(isset($res_counter)){
				if($res_counter == 0){
					switch ($lang){
						
						case("en"):
							$partial_str .='<div class="searchResultRow_noResults">No results.</div>';
							break;
							
						case("es"):
							$partial_str .='<div class="searchResultRow_noResults">No hay resultados.</div>';
							break;
					}
					
				}
			}
			
			$res_counter = 0;
			//echo "res counter: " . $res_counter;
			$partial_str .= "<div class='category_division'>
						<div class='category_division_title'> <p>" . $cat_title . "</p>
					</div>";
			foreach($query as $key => $arr){
				
				$txt_rep = new TxtReplace();
				$specialist_username = $arr[$subject];
				$specialist_username_e = $crypt->EncryptU($specialist_username);
				$plan = $arr['plan'];
				$specialist_obj = new User($con, $specialist_username, $specialist_username_e);
				$name = $specialist_obj->getFirstAndLastName();
				$specialization = $specialist_obj->getSpecializationsText($lang);
				
				if($category == "specialty" || $category == "name" || $category == "plan" || $category == "notes"){
					
					switch($category){
						case "specialty":
							$heystack = $txt_rep->entities($specialization);
							break;
						case "name":
							$heystack = $txt_rep->entities($specialist_obj->getFirstAndLastNameFast());
							break;
						case "plan":
							$heystack = $txt_rep->entities($plan);
							if(array_key_exists('private_plan', $arr)){
								if($arr['private_plan'] == 1){
									continue 2;
								}
							}
							break;
						case "notes":
							$heystack = $txt_rep->entities($arr['notes']);
							break;
					}
					
					$search_term_wild = "*" . strtolower($search_term) . "*";
					if($category == "plan" || $category == "notes"){
						if(strpos(strtolower($heystack), strtolower($search_term)) === false){
							continue;
						}
					}
					else{
						if(!fnmatch($search_term_wild, strtolower($heystack))){
							continue;
						}
					}
					
					$arr_spl = explode(strtolower($search_term),strtolower($heystack));
					
					$words = [];
					foreach($arr_spl as $key2 => $splits){
						$words[$key2] = explode(' ',trim($splits));
					}
					
					$resu_arr = "";
					
					
					$limit = 4;
					
					for($i=0;$i<sizeof($words);$i++){
						$_sub_arr = $words[$i];
						if($i == 0){
							if($limit < sizeof($_sub_arr)){
								$resu_arr .= "...";
								for($j=sizeof($_sub_arr)-$limit;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " " ;
								}
							}
							else{
								for($j=0;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
							}
							$resu_arr .= "<span class='highlighted_text'>" . strtoupper($search_term) . "</span>";
						}
						elseif($i == sizeof($words) - 1){
							if($limit < sizeof($_sub_arr)){
								for($j=0;$j<$limit;$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
								$resu_arr .= "...";
							}
							else{
								for($j=0;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
							}
						}
						else{
							if($limit*2 < sizeof($_sub_arr)){
								for($j=0;$j<$limit;$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
								$resu_arr .= "...";
								for($j=sizeof($_sub_arr)-$limit;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
							}
							else{
								for($j=0;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
							}
							$resu_arr .= "<span class='highlighted_text'>" . strtoupper($search_term) . "</span>";
						}
					}
					
					$_highlited_string = $resu_arr;				
					
				}
				
				$profile_pic = $specialist_obj->getProfilePicFast();
				
				$sympts_str = "";
				
				if($category == "symptoms"){
					
					$temp_consult_id = $arr['consult_id'];
					
					$sql = "SELECT title FROM " . $appo_symptoms . " WHERE consult_id = ?";
					$stmt = $con->prepare($sql);
					$stmt->bind_param("s",$temp_consult_id);
					$stmt->execute();
					
					$query_sympts = $stmt->get_result();
					$query_sympts_num = mysqli_num_rows($query_sympts);
					
					foreach ($query_sympts as $key2 => $arr2){
						if($key2 + 1 < $query_sympts_num){
							$sympts_str .= $arr2['title'] . ", ";
						}
						else{
							$sympts_str .= $arr2['title'] . ".";
						}
					}
					
					$heystack = $sympts_str;
					if(strpos(strtolower($heystack), strtolower($search_term)) === false){
						continue;
					}
	
					$arr_spl= explode(strtolower($search_term),strtolower($heystack));
					
					$words = [];
					foreach($arr_spl as $key2 => $splits){
						$words[$key2] = explode(' ',trim($splits));
					}
					
					$resu_arr = "";
					
					
					$limit = 4;
					
					for($i=0;$i<sizeof($words);$i++){
						$_sub_arr = $words[$i];
						if($i == 0){
							if($limit < sizeof($_sub_arr)){
								$resu_arr .= "...";
								for($j=sizeof($_sub_arr)-$limit;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " " ;
								}
							}
							else{
								for($j=0;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
							}
							$resu_arr .= "<span class='highlighted_text'>" . strtoupper($search_term) . "</span>";
						}
						elseif($i == sizeof($words) - 1){
							if($limit < sizeof($_sub_arr)){
								for($j=0;$j<$limit;$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
								$resu_arr .= "...";
							}
							else{
								for($j=0;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
							}
						}
						else{
							if($limit*2 < sizeof($_sub_arr)){
								for($j=0;$j<$limit;$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
								$resu_arr .= "...";
								for($j=sizeof($_sub_arr)-$limit;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
							}
							else{
								for($j=0;$j<sizeof($_sub_arr);$j++){
									$resu_arr .= $_sub_arr[$j] . " ";
								}
							}
							$resu_arr .= "<span class='highlighted_text'>" . strtoupper($search_term) . "</span>";
						}
					}
					
					$_highlited_string = $resu_arr;
				}
				
				$date = $arr['day'] . '/' . $arr['month'] . '/' . $arr['year'];
				
				if($isDoctor){
					$partial_str .= "<a href='doctor_appointment_viewer.php?cid=" . $arr['consult_id'] . "'>";
				}
				else{
					$partial_str .= "<a href='patient_appointment_viewer.php?cid=" . $arr['consult_id'] . "'>";
				}
				
				$partial_str .= '<div class="searchResultRow">
								<img style=" display: inline-block; left: 0; top:0;" src="' . $txt_rep->entities($profile_pic) . '"> 
									<div class="calendar_search_name">' . $name . '</div>';
				if($category != "name"){
					$partial_str .= '<div class="found_search_term">' . $cat_title . ': '. ucwords($_highlited_string) . '</div>';
				}
				switch ($lang){
					
					case("en"):
						$partial_str .= '<div class="dashboard_tag"><b><span style=" font-size: 9px; font-weight: 800 ;"> Date: </span>'. $date . '</b></div>';
						break;
						
					case("es"):
						$partial_str .= '<div class="dashboard_tag"><b><span style=" font-size: 9px; font-weight: 800 ;"> Fecha: </span>'. $date . '</b></div>';
						break;
				}
				
				$partial_str .= '</div>
								</a>';
				
				$res_counter ++;
				
				if($res_counter >= $max_num_results){
					break;
				}
			}
		}
		if($res_counter == 0){
			switch ($lang){
				
				case("en"):
					$partial_str .='<div class="searchResultRow_noResults">No results.</div>';
					break;
					
				case("es"):
					$partial_str .='<div class="searchResultRow_noResults">No hay resultados.</div>';
					break;
			}
			
		}
		echo $partial_str;
	}
	
//MULTIPLE SEARCH TERMS ARE DEALT WITH BELOW HERE	
	
	else{
		
		$results_array = array();
		
		foreach($search_term_arr as $search_term){
		
			foreach($categories as $cat_key => $category){
				
				foreach($query as $key => $arr){
					
					$txt_rep = new TxtReplace();
					$specialist_username = $arr[$subject];
					$specialist_username_e = $crypt->EncryptU($specialist_username);
					
					$plan = $arr['plan'];
					$specialist_obj = new User($con, $specialist_username, $specialist_username_e);
					$name = $specialist_obj->getFirstAndLastName();
					$specialization = $specialist_obj->getSpecializationsText($lang);
					$_t_cid = $arr['consult_id'];
					
					
					if($category == "specialty" || $category == "name" || $category == "plan" || $category == "notes"){
						
						switch($category){
							case "specialty":
								$heystack = $txt_rep->entities($specialization);
								break;
							case "name":
								$heystack = $txt_rep->entities($specialist_obj->getFirstAndLastNameFast());
								break;
							case "plan":
								$heystack = $txt_rep->entities($plan);
								if(array_key_exists('private_plan', $arr)){
									if($arr['private_plan'] == 1){
										continue 2;
									}
								}
								break;
							case "notes":
								$heystack = $txt_rep->entities($arr['notes']);
								break;
						}
						
						$search_term_wild = "*" . strtolower($search_term) . "*";
						if($category == "plan" || $category == "notes"){
							if(strpos(strtolower($heystack), strtolower($search_term)) === false){
								continue;
							}
						}
						else{
							if(!fnmatch($search_term_wild, strtolower($heystack))){
								continue;
							}
						}
						
						$arr_spl = explode(strtolower($search_term),strtolower($heystack));
						
						$words = [];
						foreach($arr_spl as $key2 => $splits){
							$words[$key2] = explode(' ',trim($splits));
						}
						
						$resu_arr = "";
						
						
						$limit = 4;
						
						for($i=0;$i<sizeof($words);$i++){
							$_sub_arr = $words[$i];
							if($i == 0){
								if($limit < sizeof($_sub_arr)){
									$resu_arr .= "...";
									for($j=sizeof($_sub_arr)-$limit;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " " ;
									}
								}
								else{
									for($j=0;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
								}
								$resu_arr .= "<span class='highlighted_text'>" . strtoupper($search_term) . "</span> ";
							}
							elseif($i == sizeof($words) - 1){
								if($limit < sizeof($_sub_arr)){
									for($j=0;$j<$limit;$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
									$resu_arr .= "...";
								}
								else{
									for($j=0;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
								}
							}
							else{
								if($limit*2 < sizeof($_sub_arr)){
									for($j=0;$j<$limit;$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
									$resu_arr .= "...";
									for($j=sizeof($_sub_arr)-$limit;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
								}
								else{
									for($j=0;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
								}
								$resu_arr .= "<span class='highlighted_text'>" . strtoupper($search_term) . "</span> ";
							}
						}
						
						$_highlited_string = $resu_arr;
						
					}
					
					$profile_pic = $specialist_obj->getProfilePicFast();
					
					$sympts_str = "";
					
					if($category == "symptoms"){
						
						$temp_consult_id = $arr['consult_id'];
						
						$sql = "SELECT title FROM " . $appo_symptoms . " WHERE consult_id = ?";
						$stmt = $con->prepare($sql);
						$stmt->bind_param("s",$temp_consult_id);
						$stmt->execute();
						
						$query_sympts = $stmt->get_result();
						$query_sympts_num = mysqli_num_rows($query_sympts);
						
						foreach ($query_sympts as $key2 => $arr2){
							if($key2 + 1 < $query_sympts_num){
								$sympts_str .= $arr2['title'] . ", ";
							}
							else{
								$sympts_str .= $arr2['title'] . ".";
							}
						}
						
						$heystack = $sympts_str;
						if(strpos(strtolower($heystack), strtolower($search_term)) === false){
							continue;
						}
						
						$arr_spl= explode(strtolower($search_term),strtolower($heystack));
						
						$words = [];
						foreach($arr_spl as $key2 => $splits){
							$words[$key2] = explode(' ',trim($splits));
						}
						
						$resu_arr = "";
						
						
						$limit = 2;
						
						for($i=0;$i<sizeof($words);$i++){
							$_sub_arr = $words[$i];
							if($i == 0){
								if($limit < sizeof($_sub_arr)){
									$resu_arr .= "...";
									for($j=sizeof($_sub_arr)-$limit;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " " ;
									}
								}
								else{
									for($j=0;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
								}
								$resu_arr .= "<span class='highlighted_text'>" . strtoupper($search_term) . "</span> ";
							}
							elseif($i == sizeof($words) - 1){
								if($limit < sizeof($_sub_arr)){
									for($j=0;$j<$limit;$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
									$resu_arr .= "...";
								}
								else{
									for($j=0;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
								}
							}
							else{
								if($limit*2 < sizeof($_sub_arr)){
									for($j=0;$j<$limit;$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
									$resu_arr .= "...";
									for($j=sizeof($_sub_arr)-$limit;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
								}
								else{
									for($j=0;$j<sizeof($_sub_arr);$j++){
										$resu_arr .= $_sub_arr[$j] . " ";
									}
								}
								$resu_arr .= "<span class='highlighted_text'>" . strtoupper($search_term) . "</span> ";
							}
						}
						
						$_highlited_string = $resu_arr;
					}
					
					if(!array_key_exists($_t_cid, $results_array)){
						$results_array[$_t_cid] = array("matches" => 1,"content"=> array($category=> array($_highlited_string)), "date"=>$arr['day'] . '/' . $arr['month'] . '/' . $arr['year'], "name" => $name);
					}
					else	{
						$results_array[$_t_cid]["matches"] = $results_array[$_t_cid]["matches"] + 1;
						
						if(!array_key_exists($category, $results_array[$_t_cid]["content"])){
							$results_array[$_t_cid]["content"][$category] = array($_highlited_string);
						}else{
							array_push($results_array[$_t_cid]["content"][$category], $_highlited_string);
						}
					}
				}
			}
		}
		
		//This part is for sorting the number of matches
		$matches_array = array();
		foreach ($results_array as $key => $val){
			//restrict number of results here, currently unrestricted
			
			//if($val["matches"] > 1){
				array_push($matches_array,$val["matches"]);
			//}
		}
		$unique_sorted_results = array_values(array_unique(quick_sort_w_repeated_items($matches_array)));
		//print_r($unique_sorted_results);
		$num_diff_keys = count($unique_sorted_results);
		
		//This part is for printing the results sorted by the number of matches
		
		switch ($lang){
			
			case("en"):
				$partial_str = "<div class='category_division'>
						<div class='category_division_title'><p> Multiple Search Terms</p></div>
					</div>";
				break;
				
			case("es"):
				$partial_str = "<div class='category_division'>
						<div class='category_division_title'><p> Busqueda con Múltiples Términos</p></div>
					</div>";
				break;
		}

		
		$res_counter = 0;
		
		for($i=$num_diff_keys-1;$i>=0;$i--){
			$_temp_matches = $unique_sorted_results[$i];
			foreach ($results_array as $cid => $val){
				if($val["matches"] == $_temp_matches){
					
					if($isDoctor){
						$partial_str .= "<a href='doctor_appointment_viewer.php?cid=" . $cid . "'>";
					}
					else{
						$partial_str .= "<a href='patient_appointment_viewer.php?cid=" . $cid . "'>";
					}
					
					$partial_str .= '<div class="searchResultRow">
									<img  src="' . $txt_rep->entities($profile_pic) . '"> 
									<p class="calendar_search_name">' . $name . '</p>
									<div class="multiple_search_matches">';
					
					foreach ($val["content"] as $cat_key => $cat_val){
						//print_r($results_array[$_t_cid]["content"]);
						switch ($lang){
							
							case("en"):
								switch($cat_key){
									case "specialty":
										$cat_title = "Specialty";
										break;
									case "name":
										$cat_title= "Name";
										break;
									case "plan":
										$cat_title= "Plan";
										break;
									case "symptoms":
										$cat_title= "Symptoms";
										break;
									case "notes":
										$cat_title= "Notes";
										break;
								}
								break;
								
							case("es"):
								switch($cat_key){
									case "specialty":
										$cat_title = "Especialidad";
										break;
									case "name":
										$cat_title= "Nombre";
										break;
									case "plan":
										$cat_title= "Plan";
										break;
									case "symptoms":
										$cat_title= "Síntomas";
										break;
									case "notes":
										$cat_title= "Notas";
										break;
								}
								break;
						}

						
						$partial_str .= '<p class="found_search_term"><b>' . $cat_title . ':</b> ['. implode("]  ,  [",$cat_val) . ']</p>';
					}
					$partial_str .= '</div>';
					
					$date = $val["date"];
					switch ($lang){
						
						case("en"):
							$partial_str .= '<p class="dashboard_tag"><span style=" font-size: 8px; font-weight: 800 ;"> Date: </span>' . $date . '</p>';
							break;
							
						case("es"):
							$partial_str .= '<p class="dashboard_tag"><span style=" font-size: 8px; font-weight: 800 ;"> Fecha: </span>' . $date . '</p>';
							break;
					}
					
					$partial_str .= '</div>
								</a>';
					
					unset($results_array[$cid]);
					
					$res_counter++;
					
					if($res_counter >= $max_num_results){
						break;
					}
				}
			}
			if($res_counter >= $max_num_results){
				break;
			}
		}
		if($res_counter == 0){
			switch ($lang){
				
				case("en"):
					$partial_str .='<div class="searchResultRow_noResults">No results.</div>';
					break;
					
				case("es"):
					$partial_str .='<div class="searchResultRow_noResults">No hay resultados.</div>';
					break;
			}
			
		}
		echo $partial_str;
	}
}

?>	

