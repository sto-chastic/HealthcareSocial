<?php 
	include("includes/header2.php");
	
	/*
	$blocks_in_an_hour = 2;
	$minutes_per_block = 60/$blocks_in_an_hour;
	$start_time = "5:00";//:00
	$end_time = "22:00";//:00

	$hours_per_day = $end_time-$start_time;*/
    
	$var_time = "";
	
	$txtrep = new TxtReplace();


	$calendar = new Calendar($con,$userLoggedIn, $userLoggedIn_e);
	$num_blocks = $calendar->getAvailableCalendarNumItems();
	$message = "";
	$current_day = date("d");
	$current_month = date("m"); //Gets current month
	$current_year = date("Y");
	
	
	$_SESSION['saved_cal'] = 0;
	
	
	//Language:
	$lang = $_SESSION['lang'];
	
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

	//error arrays for forms
	$insu_err_arr1 = [];
	$insu_err_arr2 = [];
	$insu_err_arr3 = [];
	
// 	if(isset($_POST['add_appo_type_butt'])){
// 		if(isset($_POST['appo_desc']) && isset($_POST['appo_duration']) && isset($_POST['cost_input'])){
// 			$description = htmlspecialchars($_POST['appo_desc']);
// 			$description = strip_tags($description);
// 			$description = $txtrep->entities($description);
// 			$description = preg_replace('/_/', ' ', $description);
			
// 			$duration = htmlspecialchars($_POST['appo_duration']);
// 			$duration = strip_tags($duration);
// 			$duration = $txtrep->entities($duration);
			
// 			$cost = htmlspecialchars($_POST['cost_input']);
// 			$cost= strip_tags($cost);
// 			$cost= $txtrep->entities($cost);
			
// 			$message = $calendar->setAppoDurationSettings($description,$duration,$cost);
// // 			if ($message==""){
// // 			    header('Location: calendar_settings.php#style-2');
// // 			}
// 		}
// 	}
	
// 	if(isset($_POST['office_submit_button'])){
		
// 		//FULLY DEPRECATED, this post is no longer used
		
// 		if(isset($_POST['stored_tab'])){
// 			$_tab = $_POST['stored_tab'];
// 			$link = '#office_tabs a[href="#' . $txtrep->entities($_tab) . '"]';
// 			echo "<script>
// 				$(function(){
// 					$('" . $link . "').tab('show');
// 				});
// 			</script>";
// 		}
		
// 		$of_name1 = $txtrep->entities($_POST['name_office_1']);
// 		$of_name2 = $txtrep->entities($_POST['name_office_2']);
// 		$of_name3 = $txtrep->entities($_POST['name_office_3']);
		
// 		$of1_addr1 = $txtrep->entities($_POST['address1_office_1']); //Office 1 address 1
// 		$of2_addr1 = $txtrep->entities($_POST['address1_office_2']); //Office 2 address 1
// 		$of3_addr1 = $txtrep->entities($_POST['address1_office_3']); //Office 3 address 1
		
// 		$of1_addr2 = $txtrep->entities($_POST['address2_office_1']); //Office 1 address 2
// 		$of2_addr2 = $txtrep->entities($_POST['address2_office_2']); //Office 2 address 2
// 		$of3_addr2 = $txtrep->entities($_POST['address2_office_3']); //Office 3 address 2
		
// 		$of1_addr3 = $txtrep->entities($_POST['address3_office_1']); //Office 1 address 3
// 		$of2_addr3 = $txtrep->entities($_POST['address3_office_2']); //Office 2 address 3
// 		$of3_addr3 = $txtrep->entities($_POST['address3_office_3']); //Office 3 address 3
		
// 		$of_city1 = $txtrep->entities($_POST['city_office_1']);
// 		$of_city2 = $txtrep->entities($_POST['city_office_2']);
// 		$of_city3 = $txtrep->entities($_POST['city_office_3']);
		
// 		$of_state1 = $txtrep->entities($_POST['state_office_1']);
// 		$of_state2 = $txtrep->entities($_POST['state_office_2']);
// 		$of_state3 = $txtrep->entities($_POST['state_office_3']);
		
// 		//Insurance1 preparation
// 		$insu1 = $txtrep->entities($_POST['searched_insurance1']);
// 		$_SESSION['searched_insurance1'] = $insu1;
		
// 		$insurance_codes_array1 = [];
		
// 		$insu1 = rtrim($insu1,', ');
// 		$insu1 = rtrim($insu1,',');
		
// 		$insu1_no_sp = str_replace(', ', ',', $insu1);
// 		$insu1Arr = explode(',', $insu1_no_sp);
		
// 		$stmt = $con->prepare("SELECT id FROM insurance_CO WHERE $lang = ?");
// 		$stmt->bind_param("s",$temp_arr_elem);
		
// 		foreach ($insu1Arr as $temp_arr_elem){
// 			$stmt->execute();
// 			$q = $stmt->get_result();
// 			if(mysqli_num_rows($q) == 0 && $temp_arr_elem != ''){
// 				array_push($insu_err_arr1, $temp_arr_elem);
// 			}
// 			elseif($temp_arr_elem != ''){
// 				$id = mysqli_fetch_array($q)['id'];
// 				$insurance_codes_array1[] = $id;
// 			}
// 		}
		
// 		if(empty($insurance_codes_array1) && empty($insu_err_arr1)){
// 			array_push($insurance_codes_array1, 'CO00');
// 			array_push($insu_err_arr1, "empty_insurance");
// 		}
		
		
// 		//Insurance2 preparation
// 		$insu2 = $txtrep->entities($_POST['searched_insurance2']);
// 		$_SESSION['searched_insurance2'] = $insu2;
		
// 		$insurance_codes_array2 = [];
		
// 		$insu2 = rtrim($insu2,', ');
// 		$insu2 = rtrim($insu2,',');
		
// 		$insu2Arr = explode(', ', $insu2);
		
// 		$stmt = $con->prepare("SELECT id FROM insurance_CO WHERE $lang = ?");
// 		$stmt->bind_param("s",$temp_arr_elem);
		
// 		foreach ($insu2Arr as $temp_arr_elem){
// 			$stmt->execute();
// 			$q = $stmt->get_result();
// 			if(mysqli_num_rows($q) == 0 && $temp_arr_elem != ''){
// 				array_push($insu_err_arr2, $temp_arr_elem);
// 			}
// 			elseif($temp_arr_elem != ''){
// 				$id = mysqli_fetch_array($q)['id'];
// 				$insurance_codes_array2[] = $id;
// 			}
// 		}
		
// 		if(empty($insurance_codes_array2) && empty($insu_err_arr2)){
// 			array_push($insurance_codes_array2, 'CO00');
// 			array_push($insu_err_arr2, "empty_insurance");
// 		}
		
		
// 		//Insurance3 preparation
// 		$insu3 = $txtrep->entities($_POST['searched_insurance3']);
// 		$_SESSION['searched_insurance3'] = $insu3;
		
// 		$insurance_codes_array3 = [];
		
// 		$insu3 = rtrim($insu3,', ');
// 		$insu3 = rtrim($insu3,',');
		
// 		$insu3Arr = explode(', ', $insu3);
		
// 		$stmt = $con->prepare("SELECT id FROM insurance_CO WHERE $lang = ?");
// 		$stmt->bind_param("s",$temp_arr_elem);
		
// 		foreach ($insu3Arr as $temp_arr_elem){
// 			$stmt->execute();
// 			$q = $stmt->get_result();
// 			if(mysqli_num_rows($q) == 0 && $temp_arr_elem != ''){
// 				array_push($insu_err_arr3, $temp_arr_elem);
// 			}
// 			elseif($temp_arr_elem != ''){
// 				$id = mysqli_fetch_array($q)['id'];
// 				$insurance_codes_array3[] = $id;
// 			}
// 		}
		
// 		if(empty($insurance_codes_array3) && empty($insu_err_arr3)){
// 			array_push($insurance_codes_array3, 'CO00');
// 			array_push($insu_err_arr3, "empty_insurance");
// 		}
		
		
		
// 		$sql = "UPDATE `basic_info_doctors` SET 
// `ad1nick`=?,`ad1ln1`=?,`ad1ln2`=?,`ad1ln3`=?,
// `ad1city`=?,`ad1adm2`=?,
// `ad2nick`=?,`ad2ln1`=?,`ad2ln2`=?,`ad2ln3`=?,
// `ad2city`=?,`ad2adm2`=?,
// `ad3nick`=?,`ad3ln1`=?,`ad3ln2`=?,`ad3ln3`=?,
// `ad3city`=?,`ad3adm2`=?
//  WHERE username = ?";
// 		$stmt = $con->prepare($sql);
// 		$stmt->bind_param("sssssssssssssssssss",
// 				$of_name1,$of1_addr1,$of1_addr2,$of1_addr3,
// 				$of_city1,$of_state1,
// 				$of_name2,$of2_addr1,$of2_addr2,$of2_addr3,
// 				$of_city2,$of_state2,
// 				$of_name3,$of3_addr1,$of3_addr2,$of3_addr3,
// 				$of_city3,$of_state3,$userLoggedIn);
		
// 		$stmt->execute();
		
// 		//Add array 1
// 		if(!empty($insurance_codes_array1)){
// 			$sql = "UPDATE `basic_info_doctors` SET `insurance_accepted_1`=? WHERE username = ?";
// 			$stmt = $con->prepare($sql);
// 			$stmt->bind_param("ss",$insu_arr_ser,$userLoggedIn);
// 			$insu_arr_ser = serialize($insurance_codes_array1);
// 			$stmt->execute();
// 		}
		
// 		//Add array 2
// 		if(!empty($insurance_codes_array2)){
// 			$sql = "UPDATE `basic_info_doctors` SET `insurance_accepted_2`=? WHERE username = ?";
// 			$stmt = $con->prepare($sql);
// 			$stmt->bind_param("ss",$insu_arr_ser,$userLoggedIn);
// 			$insu_arr_ser = serialize($insurance_codes_array2);
// 			$stmt->execute();
// 		}
		
// 		//Add array 3
// 		if(!empty($insurance_codes_array3)){
// 			$sql = "UPDATE `basic_info_doctors` SET `insurance_accepted_3`=? WHERE username = ?";
// 			$stmt = $con->prepare($sql);
// 			$stmt->bind_param("ss",$insu_arr_ser,$userLoggedIn);
// 			$insu_arr_ser = serialize($insurance_codes_array3);
// 			$stmt->execute();
// 		}
// 	}
	
	
	
	
	
	
	//NEW FUNCTIONS
	
	if(isset($_POST['office_submit_button_1'])){
		
		$of_name1 = $txtrep->entities($_POST['name_office_1']);
		$_SESSION['of_name1'] = $of_name1;
		$of1_addr1 = $txtrep->entities($_POST['address1_office_1']); //Office 1 address 1
		$_SESSION['of1_addr1'] = $of1_addr1;
		$of1_addr2 = $txtrep->entities($_POST['address2_office_1']); //Office 1 address 2
		$_SESSION['of1_addr2'] = $of1_addr2;
		$of1_addr3 = $txtrep->entities($_POST['address3_office_1']); //Office 1 address 3
		$_SESSION['of1_addr3'] = $of1_addr3;
		$of_city1 = $txtrep->entities($_POST['cityCode_office_1']);
		$_SESSION['of_city1'] = $of_city1;
		$of_state1 = $txtrep->entities($_POST['state_office_1']);
		$_SESSION['of_state1'] = $of_state1;
		$of1_lat = $txtrep->entities($_POST['lat_ad1']);
		$of1_lng = $txtrep->entities($_POST['lng_ad1']);
		
		//Telephone preparation and insertion
		
		$txtrep = new TxtReplace();
		$description = htmlspecialchars($_POST['telephone1']);
		$description = strip_tags($description);
		$description = $txtrep->entities($description);
		
		$user_obj = new User($con,$userLoggedIn,$userLoggedIn_e);
		$phone_table = $user_obj->getPhoneTable();
		
		$query_str = "INSERT INTO $phone_table (`id`, `telephone`, `office_num`) VALUES (1,?,'') ON DUPLICATE KEY UPDATE telephone=?";
		
		$stmt = $con->prepare($query_str);
		
		$stmt->bind_param("ss", $description, $description);
		$stmt->execute();		
		
		//Insurance1 preparation
		$insu1 = $txtrep->entities($_POST['searched_insurance1']);
		$_SESSION['searched_insurance1'] = $insu1;
		
		$insurance_codes_array1 = [];
		
		$insu1 = rtrim($insu1,', ');
		$insu1 = rtrim($insu1,',');
		
		$insu1_no_sp = str_replace(', ', ',', $insu1);
		$insu1Arr = explode(',', $insu1_no_sp);
		
		$stmt = $con->prepare("SELECT id FROM insurance_CO WHERE $lang = ?");
		$stmt->bind_param("s",$temp_arr_elem);
		
		foreach ($insu1Arr as $temp_arr_elem){
			$stmt->execute();
			$q = $stmt->get_result();
			if(mysqli_num_rows($q) == 0 && $temp_arr_elem != ''){
				array_push($insu_err_arr1, $temp_arr_elem);
			}
			elseif($temp_arr_elem != ''){
				$id = mysqli_fetch_array($q)['id'];
				$insurance_codes_array1[] = $id;
			}
		}
		
		if(empty($insurance_codes_array1) && empty($insu_err_arr1)){
			//array_push($insurance_codes_array1, 'CO00');
			array_push($insu_err_arr1, "empty_insurance");
		}		
		
		$sql = "UPDATE basic_info_doctors SET
				ad1nick=?,ad1ln1=?,ad1ln2=?,ad1ln3=?,
				ad1city=?,ad1adm2=?, ad1lat=?, ad1lng=?
				 WHERE username = ?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("sssssssss",
				$of_name1,$of1_addr1,$of1_addr2,$of1_addr3,
				$of_city1,$of_state1,$of1_lat,$of1_lng, $userLoggedIn);
		$stmt->execute();
		
		//Add array 1
		if(!empty($insurance_codes_array1)){
			$sql = "UPDATE basic_info_doctors SET insurance_accepted_1=? WHERE username = ?";
			$stmt = $con->prepare($sql);
			$stmt->bind_param("ss",$insu_arr_ser,$userLoggedIn);
			$insu_arr_ser = serialize($insurance_codes_array1);
			$stmt->execute();
		}
		
	}
	
	
	if(isset($_POST['office_submit_button_2'])){
		
		$of_name2 = $txtrep->entities($_POST['name_office_2']);
		$_SESSION['of_name2'] = $of_name2;
		$of2_addr1 = $txtrep->entities($_POST['address1_office_2']); //Office 1 address 1
		$_SESSION['of2_addr1'] = $of2_addr1;
		$of2_addr2 = $txtrep->entities($_POST['address2_office_2']); //Office 1 address 2
		$_SESSION['of2_addr2'] = $of2_addr2;
		$of2_addr3 = $txtrep->entities($_POST['address3_office_2']); //Office 1 address 3
		$_SESSION['of2_addr3'] = $of2_addr3;
		$of_city2 = $txtrep->entities($_POST['cityCode_office_2']);
		$_SESSION['of_city2'] = $of_city2;
		$of_state2 = $txtrep->entities($_POST['state_office_2']);
		$_SESSION['of_state2'] = $of_state2;
		$of2_lat = $txtrep->entities($_POST['lat_ad2']);
		$of2_lng = $txtrep->entities($_POST['lng_ad2']);
		
		//Telephone preparation and insertion
		
		$txtrep = new TxtReplace();
		$description = htmlspecialchars($_POST['telephone2']);
		$description = strip_tags($description);
		$description = $txtrep->entities($description);
		
		$user_obj = new User($con,$userLoggedIn,$userLoggedIn_e);
		$phone_table = $user_obj->getPhoneTable();
		
		$query_str = "INSERT INTO $phone_table (`id`, `telephone`, `office_num`) VALUES (2,?,'') ON DUPLICATE KEY UPDATE telephone=?";
		
		$stmt = $con->prepare($query_str);
		
		$stmt->bind_param("ss", $description, $description);
		$stmt->execute();
		
		//Insurance2 preparation
		$insu2 = $txtrep->entities($_POST['searched_insurance2']);
		$_SESSION['searched_insurance2'] = $insu2;

		$insurance_codes_array2 = [];

		$insu2 = rtrim($insu2,', ');
		$insu2 = rtrim($insu2,',');

		$insu2Arr = explode(', ', $insu2);

		$stmt = $con->prepare("SELECT id FROM insurance_CO WHERE $lang = ?");
		$stmt->bind_param("s",$temp_arr_elem);

		foreach ($insu2Arr as $temp_arr_elem){
			$stmt->execute();
			$q = $stmt->get_result();
			if(mysqli_num_rows($q) == 0 && $temp_arr_elem != ''){
				array_push($insu_err_arr2, $temp_arr_elem);
			}
			elseif($temp_arr_elem != ''){
				$id = mysqli_fetch_array($q)['id'];
				$insurance_codes_array2[] = $id;
			}
		}

		if(empty($insurance_codes_array2) && empty($insu_err_arr2)){
			//array_push($insurance_codes_array2, 'CO00');
			array_push($insu_err_arr2, "empty_insurance");
		}	
		
		
		$sql = "UPDATE basic_info_doctors SET
				ad2nick=?,ad2ln1=?,ad2ln2=?,ad2ln3=?,
				ad2city=?,ad2adm2=?,ad2lat=?, ad2lng=?
				 WHERE username = ?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("sssssssss",
				$of_name2,$of2_addr1,$of2_addr2,$of2_addr3,
		    $of_city2,$of_state2,$of2_lat,$of2_lng,$userLoggedIn);
		
		$stmt->execute();
		
		//Add array 2
		if(!empty($insurance_codes_array2)){
			$sql = "UPDATE basic_info_doctors SET insurance_accepted_2=? WHERE username = ?";
			$stmt = $con->prepare($sql);
			$stmt->bind_param("ss",$insu_arr_ser,$userLoggedIn);
			$insu_arr_ser = serialize($insurance_codes_array2);
			$stmt->execute();
		}
		
	}
	

	if(isset($_POST['office_submit_button_3'])){
		
		$of_name3 = $txtrep->entities($_POST['name_office_3']);
		$_SESSION['of_name3'] = $of_name3;
		$of3_addr1 = $txtrep->entities($_POST['address1_office_3']); //Office 1 address 1
		$_SESSION['of3_addr1'] = $of3_addr1;
		$of3_addr2 = $txtrep->entities($_POST['address2_office_3']); //Office 1 address 2
		$_SESSION['of3_addr2'] = $of3_addr2;
		$of3_addr3 = $txtrep->entities($_POST['address3_office_3']); //Office 1 address 3
		$_SESSION['of3_addr3'] = $of3_addr3;
		$of_city3 = $txtrep->entities($_POST['cityCode_office_3']);
		$_SESSION['of_city3'] = $of_city3;
		$of_state3 = $txtrep->entities($_POST['state_office_3']);
		$_SESSION['of_state3'] = $of_state3;
		$of3_lat = $txtrep->entities($_POST['lat_ad3']);
		$of3_lng = $txtrep->entities($_POST['lng_ad3']);
		
		//Telephone preparation and insertion
		
		$txtrep = new TxtReplace();
		$description = htmlspecialchars($_POST['telephone3']);
		$description = strip_tags($description);
		$description = $txtrep->entities($description);
		
		$user_obj = new User($con,$userLoggedIn,$userLoggedIn_e);
		$phone_table = $user_obj->getPhoneTable();
		
		$query_str = "INSERT INTO $phone_table (`id`, `telephone`, `office_num`) VALUES (3,?,'') ON DUPLICATE KEY UPDATE telephone=?";
		
		$stmt = $con->prepare($query_str);
		
		$stmt->bind_param("ss", $description, $description);
		$stmt->execute();
		
		//Insurance3 preparation
		$insu3 = $txtrep->entities($_POST['searched_insurance3']);
		$_SESSION['searched_insurance3'] = $insu3;

		$insurance_codes_array3 = [];

		$insu3 = rtrim($insu3,', ');
		$insu3 = rtrim($insu3,',');

		$insu3Arr = explode(', ', $insu3);

		$stmt = $con->prepare("SELECT id FROM insurance_CO WHERE $lang = ?");
		$stmt->bind_param("s",$temp_arr_elem);

		foreach ($insu3Arr as $temp_arr_elem){
			$stmt->execute();
			$q = $stmt->get_result();
			if(mysqli_num_rows($q) == 0 && $temp_arr_elem != ''){
				array_push($insu_err_arr3, $temp_arr_elem);
			}
			elseif($temp_arr_elem != ''){
				$id = mysqli_fetch_array($q)['id'];
				$insurance_codes_array3[] = $id;
			}
		}

		if(empty($insurance_codes_array3) && empty($insu_err_arr3)){
			//array_push($insurance_codes_array3, 'CO00');
			array_push($insu_err_arr3, "empty_insurance");
		}
		
		
		
		$sql = "UPDATE basic_info_doctors SET
		ad3nick=?,ad3ln1=?,ad3ln2=?,ad3ln3=?,
		ad3city=?,ad3adm2=?,ad3lat=?, ad3lng=?
		 WHERE username = ?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("sssssssss",
                $of_name3,$of3_addr1,$of3_addr2,$of3_addr3,
                $of_city3,$of_state3,$of3_lat,$of3_lng,$userLoggedIn);
		
		$stmt->execute();
		
		//Add array 3
		if(!empty($insurance_codes_array3)){
			$sql = "UPDATE basic_info_doctors SET insurance_accepted_3=? WHERE username = ?";
			$stmt = $con->prepare($sql);
			$stmt->bind_param("ss",$insu_arr_ser,$userLoggedIn);
			$insu_arr_ser = serialize($insurance_codes_array3);
			$stmt->execute();
		}
	}
	
	
	//Initial doctor marker loading
	$stmt = $con->prepare("SELECT * FROM basic_info_doctors WHERE username=?");
	$stmt->bind_param("s",$userLoggedIn);
	$stmt->execute();
	$query = $stmt->get_result();
	$defaultMarkers = mysqli_fetch_assoc($query);
	//$res = mysqli_fetch_array($query);
	$res = $defaultMarkers;
	
	//Getting saved city names
	$initialCityCodes = [1=>"",2=>"",3=>""];
	if($defaultMarkers['ad1city'] != ''){
	    $initialCityCodes[1] =  $defaultMarkers['ad1city'];
	}
	if($defaultMarkers['ad2city'] != ''){
	    $initialCityCodes[2] =  $defaultMarkers['ad2city'];
	}
	if($defaultMarkers['ad3city'] != ''){
	    $initialCityCodes[3] =  $defaultMarkers['ad3city'];
	}
	
	
	$stmt = $con->prepare("SELECT city_code, city, adm2 FROM cities_CO WHERE city_code=? or city_code=? or city_code=?");
	$stmt->bind_param("sss",$initialCityCodes[1],$initialCityCodes[2],$initialCityCodes[3]);
	$stmt->execute();
	$query = $stmt->get_result();
	
	while ($row = mysqli_fetch_assoc($query)){
    	    $cities[$row['city_code']]=$row['city'];
    	    $adm2s[$row['city_code']]=$row['adm2'];
	}
	
	$docMarkers = [ ];
	
	if($defaultMarkers['ad1lat']!== NULL && $defaultMarkers['ad1lng']!== NULL){
 	    $docMarkers [] = array(
 	        'num' => 1,
 	        'nick' => $defaultMarkers['ad1nick'],
 	        'firstNLast' => $user_obj->getFirstAndLastName(),
 	        'adln1' => $defaultMarkers['ad1ln1'],
 	        'adln2' => $defaultMarkers['ad1ln2'],
 	        'adln3' => $defaultMarkers['ad1ln3'],
 	        'adlat' => $defaultMarkers['ad1lat'],
 	        'adlng' => $defaultMarkers['ad1lng'],
 	    );
 	    
 	}
 	if($defaultMarkers['ad2lat']!== NULL && $defaultMarkers['ad2lng']!== NULL){
 	    $docMarkers [] = array(
 	        'num' => 2,
 	        'nick' => $defaultMarkers['ad2nick'],
 	        'firstNLast' => $user_obj->getFirstAndLastName(),
 	        'adln1' => $defaultMarkers['ad2ln1'],
 	        'adln2' => $defaultMarkers['ad2ln2'],
 	        'adln3' => $defaultMarkers['ad2ln3'],
 	        'adlat' => $defaultMarkers['ad2lat'],
 	        'adlng' => $defaultMarkers['ad2lng'],
 	    );
 	    
 	}
 	if($defaultMarkers['ad3lat']!== NULL && $defaultMarkers['ad3lng']!== NULL){
 	    $docMarkers [] = array(
 	        'num' => 3,
 	        'firstNLast' => $user_obj->getFirstAndLastName(),
 	        'nick' => $defaultMarkers['ad3nick'],
 	        'adln1' => $defaultMarkers['ad3ln1'],
 	        'adln2' => $defaultMarkers['ad3ln2'],
 	        'adln3' => $defaultMarkers['ad3ln3'],
 	        'adlat' => $defaultMarkers['ad3lat'],
 	        'adlng' => $defaultMarkers['ad3lng'],
 	    );
 	    //print_r($defaultMarkers['ad3lng']);
 	} 
 	
	$_SESSION['docMarkers'] = $docMarkers;
	//print_r($_SESSION['docMarkers']);

	    

?>

<!-- CSS -->



<link rel="stylesheet" type="text/css" href="assets/css/calendar.css">

<style type="text/css">
	.wrapper{
		margin-left: 0px;
		padding-left:0px;
	}
	.nav-tabs > li {
	    width: calc(100% / 3);
 	}

</style>

<!-- JS -->
<script src="assets/js/confidrcalmap.js"></script>
<script>
	firstNLast = "<?php echo $user_obj->getFirstAndLastName(); ?>";
</script>
<div class= "top_banner_title">
</div>
<div class="top_banner_title_text_container">
		<?php 
			switch ($lang){
					 		
				case("en"):
		?>
					<h1>Calendar Settings</h1>
					<h2>set your schedule, offices, and types of appointments</h2>
		<?php 
			        break;
				
				case("es"):
		?>
					<h1>Configuración de Calendario</h1>
					<h2>establece tu horario, oficinas, y tipos de citas</h2>
		<?php 
					break;
			}
		?>

	
</div>

<div class="wrapper">

<div class="wrapper2">
	

		
		<?php 
			switch ($lang){
					 		
				case("en"):
		?>
			<ul class="nav nav-tabs" role="tablist" id="office_tabs">
				<li role="presentation"  id="office_green" class="active"><a href="#office1_div" onclick="rememberTab('office1_div')" aria-controls="office1_div" role="tab" data-toggle="tab"><span id="office1_tab">hol</span><?php echo $res['ad1nick'];?></a></li>
				<?php 
					if($res['ad2nick'] == ''){
						echo <<<EOS
								<li role="presentation"  id="office_orange"><a href="javascript:void(0);" onclick="newOffice('2')"><span id="office2_tab">hol</span>Add a Second Office</a></li>
EOS;
						if($res['ad3nick'] != ''){
							$temp_titl = $txtrep->entities($res['ad3nick']);
							echo <<<EOS
							<li role="presentation"  id="office_purple"><a href="#office3_div" onclick="rememberTab('office3_div')" aria-controls="office3_div" role="tab" data-toggle="tab"><span id="office3_tab">hol</span> $temp_titl </a></li>
EOS;
						}
					}
					else{
						$temp_titl = $txtrep->entities($res['ad2nick']);
						echo <<<EOS
						<li role="presentation"  id="office_orange"><a href="#office2_div" onclick="rememberTab('office2_div')" aria-controls="office2_div" role="tab" data-toggle="tab"><span id="office2_tab">hol</span> $temp_titl </a></li>
EOS;
						
						if($res['ad3nick'] == ''){
							echo <<<EOS
									<li role="presentation"  id="office_purple"><a href="javascript:void(0);" onclick="newOffice('3')"><span id="office3_tab">hol</span>Add a Third Office</a></li>
EOS;
						}
						else{
							$temp_titl = $txtrep->entities($res['ad3nick']);
							echo <<<EOS
									<li role="presentation"  id="office_purple"><a href="#office3_div" onclick="rememberTab('office3_div')" aria-controls="office3_div" role="tab" data-toggle="tab"><span id="office3_tab">hol</span> $temp_titl  </a></li> 
EOS;
						}
					}
				?>
			</ul>
		<?php 
				        break;
				
				case("es"):
		?>
			<ul class="nav nav-tabs" role="tablist" id="office_tabs">
				<li role="presentation"  id="office_green" class="active"><a href="#office1_div" onclick="rememberTab('office1_div')" aria-controls="office1_div" role="tab" data-toggle="tab"><span id="office1_tab">hol</span><?php echo $res['ad1nick'];?></a></li>
				<?php 
					if($res['ad2nick'] == ''){
						echo <<<EOS
								<li role="presentation"  id="office_orange"><a href="javascript:void(0);" onclick="newOffice('2')"><span id="office2_tab">hol</span>Agregar Segundo Consultorio</a></li>
EOS;
						if($res['ad3nick'] != ''){
							$temp_titl = $txtrep->entities($res['ad3nick']);
							echo <<<EOS
							<li role="presentation"  id="office_purple"><a href="#office3_div" onclick="rememberTab('office3_div')" aria-controls="office3_div" role="tab" data-toggle="tab"><span id="office3_tab">hol</span> $temp_titl </a></li>
EOS;
						}
					}
					else{
						$temp_titl = $txtrep->entities($res['ad2nick']);
						echo <<<EOS
						<li role="presentation"  id="office_orange"><a href="#office2_div" onclick="rememberTab('office2_div')" aria-controls="office2_div" role="tab" data-toggle="tab"><span id="office2_tab">hol</span> $temp_titl </a></li>
EOS;
						
						if($res['ad3nick'] == ''){
							echo <<<EOS
									<li role="presentation"  id="office_purple"><a href="javascript:void(0);" onclick="newOffice('3')"><span id="office3_tab">hol</span>Agregar Tercer Consultorio</a></li>
EOS;
						}
						else{
							$temp_titl = $txtrep->entities($res['ad3nick']);
							echo <<<EOS
									<li role="presentation"  id="office_purple"><a href="#office3_div" onclick="rememberTab('office3_div')" aria-controls="office3_div" role="tab" data-toggle="tab"><span id="office3_tab">hol</span> $temp_titl  </a></li> 
EOS;
						}
					}
				?>
			</ul>
		<?php 
					break;
			}
		?>
		<?php //TODO:?>
	<div class="main_column column" id="office_info">	
	
	
	<div class="tab-settings_calendar">
			<div role="tabpanel" class="tab-pane fade in active" id="office1_div" style=" float: unset;">
				<form action="calendar_settings.php" method="POST">
					<?php $office = "1"?>
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Name (only you see this):";
								break;
								
							case("es"):
								echo "Nombre (sólo tú ves esto):";
								break;
						}
						?></p></div>
						<input type="text" maxlength="20" placeholder="
						<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: My Fifth Av. Office";
								break;
								
							case("es"):
								echo "Ej: Mi Oficina de Chapinero";
								break;
						}
						?>
						" autocomplete="off" id=<?php echo '"name_office_' . $office . '"';?> name=<?php echo '"name_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'nick';
							$nname[1] = $res[$name];
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							elseif(isset($_SESSION['of_name1'])){
								echo $_SESSION['of_name1'];
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Address:";
								break;
								
							case("es"):
								echo "Dirección:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: 350 5th Ave";
								break;
								
							case("es"):
								echo "Ej: Cl. 24 # 122 - 15";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address1_office_' . $office . '"';?> name=<?php echo '"address1_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln1';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							elseif(isset($_SESSION['of1_addr1'])){
								echo $_SESSION['of1_addr1'];
							}
						?>
						 required>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Office #:";
								break;
								
							case("es"):
								echo "No. Oficina:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: Of. 34 / Apt. 23";
								break;
								
							case("es"):
								echo "Ej: Of. 34 / Apt. 23";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address2_office_' . $office . '"';?> name=<?php echo '"address2_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln2';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							elseif(isset($_SESSION['of1_addr2'])){
								echo $_SESSION['of1_addr2'];
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block">
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Building Name:";
								break;
								
							case("es"):
								echo "Nombre Edificio:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: Empire State Building";
								break;
								
							case("es"):
								echo "Ej: Edificio Santafe";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address3_office_' . $office . '"';?> name=<?php echo '"address3_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln3';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							elseif(isset($_SESSION['of1_addr3'])){
								echo $_SESSION['of1_addr3'];
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "City:";
								break;
								
							case("es"):
								echo "Ciudad:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: New York City";
								break;
								
							case("es"):
								echo "Ej: Bogotá";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"city_office_' . $office . '"';?> name=<?php echo '"city_office_' . $office . '"';?> value="<?php 
						
							if($res['ad1city'] != ''){
							    echo $cities[$res['ad1city']];
							}
							elseif(isset($_SESSION['of_city1'])){
								echo $_SESSION['of_city1'];
							}
						?>"
						>
						<div class="style-2" id="cityResults1"></div>
						<input type="hidden" maxlength="7"  autocomplete="off" id="cityCode_office_1" name="cityCode_office_1"
						value="<?php 
        						if ($res['ad1city'] != ''){
        						    echo $res['ad1city'];
        						}
        						elseif(isset($_SESSION['of1_cityC'])){
        						    echo $_SESSION['of1_cityC'];
        						} 
        						?>" readonly
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag" ><p><?php 
						switch ($lang){
							
							case("en"):
								echo "State:";
								break;
								
							case("es"):
								echo "Departamento:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: New York";
								break;
								
							case("es"):
								echo "Ej: Cundinamarca";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"state_office_' . $office . '"';?> name=<?php echo '"state_office_' . $office . '"';?> value=<?php 
							
        						if ($res['ad1adm2'] != ''){
        						    echo $adm2s[$res['ad1city']];
        						}
							elseif(isset($_SESSION['of_state1'])){
								echo $_SESSION['of_state1'];
							}
						?>readonly
						>
						<input type="hidden" id="lat_ad1" name="lat_ad1" value="<?php 
							
        						if ($res['ad1lat'] != ''){
        						    echo $res['ad1lat'];
        						}
							elseif(isset($_SESSION['of_state1'])){
								echo $_SESSION['of_state1'];
							}
						?>" readonly required>
						<input type="hidden" id="lng_ad1" name="lng_ad1" value="<?php 
							
        						if ($res['ad1lng'] != ''){
        						    echo $res['ad1lng'];
        						}
							elseif(isset($_SESSION['of_state1'])){
								echo $_SESSION['of_state1'];
							}
						?>" readonly required>
						<input id="address1" value="" type="hidden" readonly>
						<input id="geocode1" type="button" value="<?php 
                				switch($lang){
                				    case("en"):
                				        echo "Set pin location";
                				        break;
                				    case("es"):
                				        echo "Ubicar pin en el mapa";
                				        break;
                				}
                				?>">
					</div>
					<div class="dashboard_tag_block" >
						<?php 
						$stmt = $con->prepare("SELECT `insurance_accepted_1`, `insurance_accepted_2`, `insurance_accepted_3` FROM `basic_info_doctors` WHERE `username` = ?");
						$stmt->bind_param("s",$userLoggedIn);
						$stmt->execute();
						$ini_insu_q = $stmt->get_result();
						$uns_arr = mysqli_fetch_array($ini_insu_q);
						
						$search_col = "search_" . $lang;
						?>
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Insurance options:";
								break;
								
							case("es"):
								echo "Seguros/Prepagadas/EPS recibidas:";
								break;
						}
						?></p></div>
							<input type="text" onkeyup="sanitizeSearchInsurance(this.value, '<?php echo $lang; ?>', '<?php echo $search_col ;?>', 'search_insurance_results1', 'search_text_input_insurance1')" placeholder="<?php 
							switch ($lang){
								
								case("en"):
									echo "Ex: Medicare";
									break;
									
								case("es"):
									echo "Ej: Colmédica, La Nueva EPS, Allianz";
									break;
							}?>" autocomplete="off" id="search_text_input_insurance1" name="searched_insurance1" value=<?php 
								if($uns_arr['insurance_accepted_1'] != ''){
									
									$arr = unserialize($uns_arr['insurance_accepted_1']);
									$to_print_array = [];
									$stmt = $con->prepare("SELECT $lang FROM insurance_CO WHERE id = ?");
									$stmt->bind_param("s",$val);
									
									$str = '';
									foreach($arr as $key => $val){
										$stmt->execute();
										$t_q = $stmt->get_result();
										$t_a = mysqli_fetch_array($t_q);
										
										if($key < sizeof($arr)-1)
											$str .= $t_a[$lang] . ', ';
										else
											$str .= $t_a[$lang] . '';
									}
									echo '"' . $str . '"';
								}
								elseif(isset($_SESSION['searched_insurance1'])){
									echo '"' . $txt_rep->entities($_SESSION['searched_insurance1']) . '"';
								}
								else{
									echo '""';
								}
							?>
						>
						
						<div id="search_insurance_results1">
						</div>

						<?php 
					 
							if(in_array("empty_insurance", $insu_err_arr1)){
								switch ($lang){
									
									case("en"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>You did not input any isnurance for this office, which means patients are only going to be able to book appointments here if they chose to pay the full consult price without using an insurance.<br></p>
									</div>
									";
										break;
										
									case("es"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>No ingresaste ninguna entidad (Prepagada, Aseguradora, EPS), por lo que solo pacientes particulares podrán hacer cita aquí.<br></p>
									</div>
									";
										break;
								}

							}
							elseif(!empty($insu_err_arr1)){
								$rej_insu = implode(", ", $insu_err_arr1);
								
								switch ($lang){
									
									case("en"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>The insurance(s): " . $rej_insu . ", did not match any of the insurance companies in our system, please contact ConfiDr.<br></p>
									</div>
									";
										break;
										
									case("es"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>El (los) seguro(s): " . $rej_insu . ", no se encontró(aron) en nuestro sistema, ponte en contacto con ConfiDr.<br></p>
									</div>
									";
										break;
								}
								
								
							}
						?>
						
						<?php 
				
						$phone_table = $user_obj->getPhoneTable();
						$query_str = "SELECT `telephone` FROM $phone_table WHERE id=1";
						
						$stmt = $con->prepare($query_str);
						$stmt->execute();
						
						$phone_query = $stmt->get_result();
						$phone_arr = mysqli_fetch_array($phone_query);

							switch ($lang){
									 		
								case("en"):
						?>
									<div class="dashboard_tag" ><p>Telephone</p></div>
									<input type="text" name="telephone1" id = "add_telephone1" placeholder="Ex: 571 555-2355" value="<?php echo ($phone_arr != "")? $phone_arr['telephone']:'';?>" required>
						<?php 
							        break;
								
								case("es"):
						?>
									<div class="dashboard_tag" ><p>Teléfono</p></div>
									<input type="text" name="telephone1" id = "add_telephone1" placeholder="Ej: 571 555-2355" value="<?php echo ($phone_arr != "")? $phone_arr['telephone']:'';?>" required>
						<?php 
									break;
							}
						?>
						
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<input type="submit" name="office_submit_button_1" value="Save" class="save_data_office" >
						<?php 
							        break;
								
								case("es"):
						?>
									<input type="submit" name="office_submit_button_1" value="Guardar" class="save_data_office" >
						<?php 
									break;
							}
						?>							
					</div>
				</form>
				</div>			
				<?php //TODO?>
				
				
				<div role="tabpanel" class="tab-pane fade" id="office2_div" style=" float: unset;">
	  			<form action="calendar_settings.php" method="POST">
					<?php $office = "2"?>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Name:";
								break;
								
							case("es"):
								echo "Nombre:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="20" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: My Fifth Av. Office";
								break;
								
							case("es"):
								echo "Ej: Mi Oficina de Chapinero";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"name_office_' . $office . '"';?> name=<?php echo '"name_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'nick';
							$nname[2] = $res[$name];
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							else{
								echo "''";
							}
							
							$nameField = 'ad' . $office . 'nick';
							if($res[$nameField] != ''){
								echo "required";
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Address:";
								break;
								
							case("es"):
								echo "Dirección:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: 350 5th Ave";
								break;
								
							case("es"):
								echo "Ej: Cl. 24 # 122 - 15";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address1_office_' . $office . '"';?> name=<?php echo '"address1_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln1';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							else{
								echo "''";
							}
							
							$nameField = 'ad' . $office . 'nick';
							if($res[$nameField] != ''){
								echo "required";
							}
						?>
						 >
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Office #:";
								break;
								
							case("es"):
								echo "No. Oficina:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: Of. 34 / Apt. 23";
								break;
								
							case("es"):
								echo "Ej: Of. 34 / Apt. 23";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address2_office_' . $office . '"';?> name=<?php echo '"address2_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln2';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							else{
								echo "''";
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Building Name:";
								break;
								
							case("es"):
								echo "Nombre Edificio:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: Empire State Building";
								break;
								
							case("es"):
								echo "Ej: Edificio Santafe";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address3_office_' . $office . '"';?> name=<?php echo '"address3_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln3';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							else{
								echo "''";
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "City:";
								break;
								
							case("es"):
								echo "Ciudad:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: New York City";
								break;
								
							case("es"):
								echo "Ej: Bogotá";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"city_office_' . $office . '"';?> name=<?php echo '"city_office_' . $office . '"';?> value="<?php 
						
							if($res['ad2city'] != ''){
							    echo $cities[$res['ad2city']];
							}
							elseif(isset($_SESSION['of_city2'])){
								echo $_SESSION['of_city2'];
							}
						?>"
						>
						<div class="style-2" id="cityResults2"></div>
						<input type="hidden" maxlength="7"  autocomplete="off" id="cityCode_office_2" name="cityCode_office_2"
						value="<?php 
        						if ($res['ad2city'] != ''){
        						    echo $res['ad2city'];
        						}
        						elseif(isset($_SESSION['of2_cityC'])){
        						    echo $_SESSION['of2_cityC'];
        						} 
        						?>" readonly
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag" ><p><?php 
						switch ($lang){
							
							case("en"):
								echo "State:";
								break;
								
							case("es"):
								echo "Departamento:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: New York";
								break;
								
							case("es"):
								echo "Ej: Cundinamarca";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"state_office_' . $office . '"';?> name=<?php echo '"state_office_' . $office . '"';?> value=<?php 
							
        						if ($res['ad2adm2'] != ''){
        						    echo $adm2s[$res['ad2city']];
        						}
							elseif(isset($_SESSION['of_state2'])){
								echo $_SESSION['of_state2'];
							}
						?> readonly
						>
						<input type="hidden" id="lat_ad2" name="lat_ad2" value="" readonly required>
						<input type="hidden" id="lng_ad2" name="lng_ad2" value="" readonly required>
						<input id="address2" value="" type="hidden" readonly>
						<input id="geocode2" type="button" value="<?php 
                				switch($lang){
                				    case("en"):
                				        echo "Set pin location";
                				        break;
                				    case("es"):
                				        echo "Ubicar pin en el mapa";
                				        break;
                				}
                				?>">
					</div>
					<div class="dashboard_tag_block" >
						<?php 
						$search_col = "search_" . $lang;
						?>
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Insurance options:";
								break;
								
							case("es"):
								echo "Seguros/Prepagadas/EPS recibidas:";
								break;
						}
						?></p></div>
						<input type="text" onkeyup="sanitizeSearchInsurance(this.value, '<?php echo $lang; ?>', '<?php echo $search_col; ?>', 'search_insurance_results2', 'search_text_input_insurance2')" placeholder="<?php 
							switch ($lang){
								
								case("en"):
									echo "Ex: Medicare";
									break;
									
								case("es"):
									echo "Ej: Colmédica, La Nueva EPS, Allianz";
									break;
							}?>" autocomplete="off" id="search_text_input_insurance2" name="searched_insurance2" value=<?php 
								if($uns_arr['insurance_accepted_2'] != ''){
									
									$arr = unserialize($uns_arr['insurance_accepted_2']);
									$to_print_array = [];
									$stmt = $con->prepare("SELECT $lang FROM insurance_CO WHERE id = ?");
									$stmt->bind_param("s",$val);
									
									$str = '';
									foreach($arr as $key => $val){
										$stmt->execute();
										$t_q = $stmt->get_result();
										$t_a = mysqli_fetch_array($t_q);
										
										if($key < sizeof($arr)-1)
											$str .= $t_a[$lang] . ', ';
										else
											$str .= $t_a[$lang] . '';
									}
									echo '"' . $str . '"';
								}
								elseif(isset($_SESSION['searched_insurance2'])){
									echo '"' . $txt_rep->entities($_SESSION['searched_insurance2']) . '"';
								}
								else{
									echo '""';
								}
							?>
						>

						<div id="search_insurance_results2">
						</div>

						<?php 
						 
							if(in_array("empty_insurance", $insu_err_arr2)){
								switch ($lang){
									
									case("en"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>You did not input any isnurance for this office, which means patients are only going to be able to book appointments here if they chose to pay the full consult price without using an insurance.<br></p>
									</div>
									";
										break;
										
									case("es"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>No ingresaste ninguna entidad (Prepagada, Aseguradora, EPS), por lo que solo pacientes particulares podrán hacer cita aquí.<br></p>
									</div>
									";
										break;
								}
							}
							elseif(!empty($insu_err_arr2)){
								$rej_insu = implode(", ", $insu_err_arr2);
								switch ($lang){
									
									case("en"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>The insurance(s): " . $rej_insu . ", did not match any of the insurance companies in our system, please contact ConfiDr.<br></p>
									</div>
									";
										break;
										
									case("es"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>El (los) seguro(s): " . $rej_insu . ", no se encontró(aron) en nuestro sistema, ponte en contacto con ConfiDr.<br></p>
									</div>
									";
										break;
								}
							}
						?>
						
						<?php 
				
						$phone_table = $user_obj->getPhoneTable();
						
						$query_str = "SELECT `telephone` FROM $phone_table WHERE id=2";
						
						$stmt = $con->prepare($query_str);
						$stmt->execute();
						
						$phone_query = $stmt->get_result();
						$phone_arr = mysqli_fetch_array($phone_query);

							switch ($lang){
									 		
								case("en"):
						?>
									<div class="dashboard_tag" ><p>Telephone</p></div>
									<input type="text" name="telephone2" id = "add_telephone2" placeholder="Ex: 571 555-2355" value="<?php echo ($phone_arr != "")? $phone_arr['telephone']:'';?>" required>
						<?php 
							        break;
								
								case("es"):
						?>
									<div class="dashboard_tag" ><p>Teléfono</p></div>
									<input type="text" name="telephone2" id = "add_telephone2" placeholder="Ej: 571 555-2355" value="<?php echo ($phone_arr != "")? $phone_arr['telephone']:'';?>" required>
						<?php 
									break;
							}
						?>
						
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<input type="submit" name="office_submit_button_2" value="Save" class="save_data_office" >
									<div class="delete_element big_del_butt" data-toggle='modal' data-target=<?php echo "'#del_office_modal" . $office ."'";?> id=<?php echo "'del_office" . $office ."'";?>>		delete</div>
						<?php 
							        break;
								
								case("es"):
						?>
									<input type="submit" name="office_submit_button_2" value="Guardar" class="save_data_office" >
									<div class="delete_element big_del_butt" data-toggle='modal' data-target=<?php echo "'#del_office_modal" . $office ."'";?> id=<?php echo "'del_office" . $office ."'";?>>		eliminar</div>

						<?php 
									break;
							}
						?>

							<?php //TODO:?>
					</div>
				</form>
				</div>
				
				
				<div role="tabpanel" class="tab-pane fade" id="office3_div" style=" float: unset;">
				<form action="calendar_settings.php" method="POST">
					<?php $office = "3"?>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Name:";
								break;
								
							case("es"):
								echo "Nombre:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="20" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: My Fifth Av. Office";
								break;
								
							case("es"):
								echo "Ej: Mi Oficina de Chapinero";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"name_office_' . $office . '"';?> name=<?php echo '"name_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'nick';
							$nname[3] = $res[$name];
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							else{
								echo "''";
							}
							
							$nameField = 'ad' . $office . 'nick';
							if($res[$nameField] != ''){
								echo "required";
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Address:";
								break;
								
							case("es"):
								echo "Dirección:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: 350 5th Ave";
								break;
								
							case("es"):
								echo "Ej: Cl. 24 # 122 - 15";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address1_office_' . $office . '"';?> name=<?php echo '"address1_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln1';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							else{
								echo "''";
							}
							
							$nameField = 'ad' . $office . 'nick';
							if($res[$nameField] != ''){
								echo "required";
							}
						?>
						 >
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Office #:";
								break;
								
							case("es"):
								echo "No. Oficina:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: Of. 34 / Apt. 23";
								break;
								
							case("es"):
								echo "Ej: Of. 34 / Apt. 23";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address2_office_' . $office . '"';?> name=<?php echo '"address2_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln2';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							else{
								echo "''";
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Building Name:";
								break;
								
							case("es"):
								echo "Nombre Edificio:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: Empire State Building";
								break;
								
							case("es"):
								echo "Ej: Edificio Santafe";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"address3_office_' . $office . '"';?> name=<?php echo '"address3_office_' . $office . '"';?> value=<?php 
							$name = 'ad' . $office . 'ln3';
							if($res[$name] != ''){
								echo "'" . $res[$name] . "'";
							}
							else{
								echo "''";
							}
						?>
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "City:";
								break;
								
							case("es"):
								echo "Ciudad:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: New York City";
								break;
								
							case("es"):
								echo "Ej: Bogotá";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"city_office_' . $office . '"';?> name=<?php echo '"city_office_' . $office . '"';?> value="<?php 
						
							if($res['ad3city'] != ''){
							    echo $cities[$res['ad3city']];
							}
							elseif(isset($_SESSION['of_city3'])){
								echo $_SESSION['of_city3'];
							}
						?>"
						>
						<div class="style-2" id="cityResults3"></div>
						<input type="hidden" maxlength="7"  autocomplete="off" id="cityCode_office_3" name="cityCode_office_3"
						value="<?php 
        						if ($res['ad3city'] != ''){
        						    echo $res['ad3city'];
        						}
        						elseif(isset($_SESSION['of3_cityC'])){
        						    echo $_SESSION['of3_cityC'];
        						} 
        						?>" readonly
						>
					</div>
					
					<div class="dashboard_tag_block" >
						<div class="dashboard_tag" ><p><?php 
						switch ($lang){
							
							case("en"):
								echo "State:";
								break;
								
							case("es"):
								echo "Departamento:";
								break;
						}
						?></p></div>
						<input type="text" maxlength="50" placeholder="<?php
						switch ($lang){
							
							case("en"):
								echo "Ex: New York";
								break;
								
							case("es"):
								echo "Ej: Cundinamarca";
								break;
						}
						?>" autocomplete="off" id=<?php echo '"state_office_' . $office . '"';?> name=<?php echo '"state_office_' . $office . '"';?> value=<?php 
							
        						if ($res['ad3adm2'] != ''){
        						    echo $adm2s[$res['ad3city']];
        						}
							elseif(isset($_SESSION['of_state3'])){
								echo $_SESSION['of_state3'];
							}
						?> readonly
						>
						<input type="hidden" id="lat_ad3" name="lat_ad3" value="" readonly required>
						<input type="hidden" id="lng_ad3" name="lng_ad3" value="" readonly required>
						<input id="address3" value="" type="hidden" readonly>
						<input id="geocode3" type="button" value="<?php 
                				switch($lang){
                				    case("en"):
                				        echo "Set pin location";
                				        break;
                				    case("es"):
                				        echo "Ubicar pin en el mapa";
                				        break;
                				}
                				?>">
					</div>
					<div class="dashboard_tag_block" >
						<?php 
						$search_col = "search_" . $lang;
						?>
						<div class="dashboard_tag"><p><?php 
						switch ($lang){
							
							case("en"):
								echo "Insurance options:";
								break;
								
							case("es"):
								echo "Seguros/Prepagadas/EPS recibidas:";
								break;
						}
						?></p></div>
						<input type="text" onkeyup="sanitizeSearchInsurance(this.value, '<?php echo $lang; ?>', '<?php echo $search_col; ?>', 'search_insurance_results3', 'search_text_input_insurance3')" placeholder="<?php 
							switch ($lang){
								
								case("en"):
									echo "Ex: Medicare";
									break;
									
								case("es"):
									echo "Ej: Colmédica, La Nueva EPS, Allianz";
									break;
							}?>" autocomplete="off" id="search_text_input_insurance3" name="searched_insurance3" value=<?php 
								if($uns_arr['insurance_accepted_3'] != ''){
									
									$arr = unserialize($uns_arr['insurance_accepted_3']);
									$to_print_array = [];
									$stmt = $con->prepare("SELECT $lang FROM insurance_CO WHERE id = ?");
									$stmt->bind_param("s",$val);
									
									$str = '';
									foreach($arr as $key => $val){
										$stmt->execute();
										$t_q = $stmt->get_result();
										$t_a = mysqli_fetch_array($t_q);
										
										if($key < sizeof($arr)-1)
											$str .= $t_a[$lang] . ', ';
										else
											$str .= $t_a[$lang] . '';
									}
									echo '"' . $str . '"';
								}
								elseif(isset($_SESSION['searched_insurance3'])){
									echo '"' . $txt_rep->entities($_SESSION['searched_insurance3']) . '"';
								}
								else{
									echo '""';
								}
							?>
						>

						<div id="search_insurance_results3">
						</div>

						<?php 
						
							if(in_array("empty_insurance", $insu_err_arr3)){
								switch ($lang){
									
									case("en"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>You did not input any isnurance for this office, which means patients are only going to be able to book appointments here if they chose to pay the full consult price without using an insurance.<br></p>
									</div>
									";
										break;
										
									case("es"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>No ingresaste ninguna entidad (Prepagada, Aseguradora, EPS), por lo que solo pacientes particulares podrán hacer cita aquí.<br></p>
									</div>
									";
										break;
								}
							}
							elseif(!empty($insu_err_arr3)){
								$rej_insu = implode(", ", $insu_err_arr3);
								switch ($lang){
									
									case("en"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>The insurance(s): " . $rej_insu . ", did not match any of the insurance companies in our system, please contact ConfiDr.<br></p>
									</div>
									";
										break;
										
									case("es"):
										echo "<div style='margin-top:10px; display: inline-block ;'>
										<p id='incorrect'>El (los) seguro(s): " . $rej_insu . ", no se encontró(aron) en nuestro sistema, ponte en contacto con ConfiDr.<br></p>
									</div>
									";
										break;
								}
							}
						?>
						
						<?php 
				
						$phone_table = $user_obj->getPhoneTable();
						
						$query_str = "SELECT `telephone` FROM $phone_table WHERE id=3";
						
						$stmt = $con->prepare($query_str);
						$stmt->execute();
						
						$phone_query = $stmt->get_result();
						$phone_arr = mysqli_fetch_array($phone_query);

							switch ($lang){
									 		
								case("en"):
						?>
									<div class="dashboard_tag" ><p>Telephone</p></div>
									<input type="text" name="telephone3" id = "add_telephone3" placeholder="Ex: 571 555-2355" value="<?php echo ($phone_arr != "")? $phone_arr['telephone']:'';?>" required>
						<?php 
							        break;
								
								case("es"):
						?>
									<div class="dashboard_tag" ><p>Teléfono</p></div>
									<input type="text" name="telephone3" id = "add_telephone3" placeholder="Ej: 571 555-2355" value="<?php echo ($phone_arr != "")? $phone_arr['telephone']:'';?>" required>
						<?php 
									break;
							}
						?>
						
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<input type="submit" name="office_submit_button_3" value="Save" class="save_data_office" >
									<div class="delete_element big_del_butt" data-toggle='modal' data-target=<?php echo "'#del_office_modal" . $office ."'";?> id=<?php echo "'del_office" . $office ."'";?>>delete</div>
						<?php 
							        break;
								
								case("es"):
						?>
									<input type="submit" name="office_submit_button_3" value="Guardar" class="save_data_office" >
									<div class="delete_element big_del_butt" data-toggle='modal' data-target=<?php echo "'#del_office_modal" . $office ."'";?> id=<?php echo "'del_office" . $office ."'";?>>eliminar</div>

						<?php 
									break;
							}
						?>
						
							<?php //TODO:?>
					</div>
				</form>
				</div>
				

		</div>
		<input type="hidden" name="stored_tab" id="stored_tab">
		
	</div>
	<div class="column" id="map_info">
		<div class= "location_map"> 
            	<div class="map_locator">
    				<!--   <div id="floating-panel" style="height:0px; width:0px">  
  				</div>  -->
    				<div id="map" style="height:650px; width:100%;">
    				</div>
    			</div>
    			<script async defer 
        	    				src="https://maps.googleapis.com/maps/api/js?key=&callback=initLocatorMap">
    			</script>
    		</div>
	</div>
</div>
	<div class="main_column column" id="main_column" display:inline-block;>
		<div class = "main_column_element" style=" height: 700px;">
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						<h2>Set Weekly Schedule</h2>
						<b> Configure your appointment settings and availability calendar: </b>
	
			<?php 
				        break;
					
					case("es"):
			?>
						<h2>Configurar Horario Semanal</h2>
						<b> Configura tus citas y disponibilidad en cada consultorio: </b>
			<?php 
						break;
				}
			?>
			<div id="week_selector_buttons">
				<a href="javascript:void(0);" onclick=" closeDropdownCalendar(); getDropdownCalendarIni('<?php echo $txt_rep->entities($current_month); ?>','<?php echo $txt_rep->entities($current_year); ?>')">
					<div class="particular_week">
					<?php 
					switch ($lang){
						
						case("en"):
							echo "Or configure a specific week clicking <b>here</b>";//. &#9654;
							break;
							
						case("es"):

							echo "O configura una semana específica haciendo click <b>aquí</b>";//. &#9654;

							break;
					}
					?>
					</div><br>
				</a>
				<div class="dropdown_calendar_window" id="dropdown_calendar_abs">
					<div id="calendar_window_button_close">
							<a href="javascript:void(0);" onclick="closeDropdownCalendar()">
								<div class="delete_button">
									X
								</div>
							</a>
					</div>
					<div id="calendar_window_header" >
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<h1>Select a week to configure <br> a custom schedule.</h1>
						<?php 
							        break;
								
								case("es"):
						?>
									<h1>Selecciona una semana para crearle un <br> horario especial.</h1>
						<?php 
									break;
							}
						?>
						
					<div id="calendar_window_button_year" >
							<select id="year_selected" name="year_selected" >
								<?php
									for($i = 0; $i <= 2; $i++){
										$_t_y = $current_year + $i;
										if($i == 0){
											echo "<option selected='selected' value='" . $_t_y . "'>" . $_t_y . "</option>";
										}
										else{
											echo "<option value='" . $_t_y . "'>" . $_t_y . "</option>";
										}
									}
								?>
							</select>
					</div>	
					<div id="calendar_window_button_month" >
							
							<?php
								$curr_month_lang_query = mysqli_query($con, "SELECT $months_row_lang FROM months WHERE id='$current_month'");
								$arr = mysqli_fetch_array($curr_month_lang_query);
								$curr_month_lang = $arr[$months_row_lang];
							?>
							<select name="month_selected" id="month_selected">
								<?php
									$drop_dwn_month = "";
									$query = mysqli_query($con, "SELECT $months_row_lang,id FROM months ORDER BY id ASC");
									foreach($query as $arr){
										if($arr['id'] < $current_month){
											continue;
										}
										if($curr_month_lang == $arr[$months_row_lang]){
										    $drop_dwn_month = $drop_dwn_month . "<option selected='selected' value='" . $arr['id'] . "'>" . $arr[$months_row_lang]  . "</option>";
										}
										else{
											$drop_dwn_month = $drop_dwn_month . "<option value='" . $arr['id'] . "'>" . $arr[$months_row_lang] .  "</option>";
										}
									}
									echo $drop_dwn_month;
								?>
							</select>
						</div>
						
					</div>
					<div class='calendar_header_container'>
						<?php
							$days_str = "";
							$week_days_lang_query = mysqli_query($con, "SELECT $days_week_row_lang FROM days_week ORDER BY dw ASC ");
	
							foreach ($week_days_lang_query as $value_day) {
								$days_str = $days_str . "<div class='calendar_day_block' id='header_day'>" . $value_day[$days_week_row_lang] . "</div>";
							}
							echo $days_str;
						?>
					</div>
					
					<script>
					
						function getDropdownCalendar(user){
							if($(".dropdown_calendar_window").css("height") != "0px"){
								var month = $("#month_selected").find(":selected").val();
								var year = $("#year_selected").find(":selected").val();
								var ajaxreq = $.ajax({
									url: "includes/handlers/ajax_dropdown_calendar.php",
									type: "POST",
									data: "user=" + user + "&month=" + month + "&year=" + year,
									cache: false,
	
									success: function(response){
										$(".calendar_container").html(response);
									}
								});
							}
						}
						
					</script>
	
					<div class='calendar_container'>
	
					</div>
				</div>
				
			</div>
    <div style="height: 750px;">
			<div style=" display: inline-block; height: 100%; width: 100%;">

				<iframe src='calendar_frame.php' id='calendar_iframe' frameborder='0' scrolling='no'>
				</iframe>
			</div>
		</div>
	

		<script>
			function rememberTab(tab){
				//alert(tab);
				$('#stored_tab').val(tab);
			}
			
		</script>

	</div>
	
		<div class = "main_column_element">
			<?php 
			
				switch ($lang){
						 		
					case("en"):
			?>
						<h2>Appointment Types, Duration, and Price</h2>
						<b> Manage your appointment types </b>
						
			<?php 
				        break;
					
					case("es"):
			?>
						<h2>Tipos de Cita, Duración, y Precios</h2>
						<b> Gestiona tus tipos de consulta </b>
			<?php 
						break;
				}
			?>
			
	
			<div id="add_appo_type">
					<?php 
						switch ($lang){
								 		
							case("en"):
					?>
								<h3>Add appointment types</h3>
					<?php 
						        break;
							
							case("es"):
					?>
								<h3>Agregar Tipos de Cita</h3>
					<?php 
								break;
						}
					?>
				<form action="" class="appo_type_form" id="appo_type_form">
	
					<div id="appo_type_form_left">
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<p>Type</p>
									<input type="text" name="appo_desc" id = "appo_description" placeholder="Ex: First-Time / Follow Up / ..." required>
						<?php 
							        break;
								
								case("es"):
						?>
									<p>Tipo</p>
									<input type="text" name="appo_desc" id = "appo_description" placeholder="Ej: Primera Vez / Seguimiento / ..." required>
						<?php 
									break;
							}
						?>
						
						<b id="max_appo_types_error"></b>
					</div>
	
					<div class="appo_type_form_middle">
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<p>Duration</p>
						<?php 
							        break;
								
								case("es"):
						?>
									<p>Duración</p>
						<?php 
									break;
							}
						?>
						<div id= "duration_deco">
        					<select name = "appo_duration" id="appo_type_form_duration">
        
        						<?php
        							$drop_dwn = "";
        							$query = mysqli_query($con, "SELECT duration FROM appo_duration ORDER BY duration ASC");
        							foreach($query as $arr){
        								switch ($lang){
        									
        									case("en"):
        										$drop_dwn = $drop_dwn . "<option value='" . $arr['duration'] . "'>" . $arr['duration'] . " minutes </option>";
        										break;
        										
        									case("es"):
        										$drop_dwn = $drop_dwn . "<option value='" . $arr['duration'] . "'>" . $arr['duration'] . " minutos </option>";
        										break;
        								}
        								
        							}
        							echo $drop_dwn;
        						?>
        
        					</select>
        				</div>	
					</div>
					<div class="appo_type_form_middle">
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<p>Price</p>
						<?php 
							        break;
								
								case("es"):
						?>
									<p>Precio</p>
						<?php 
									break;
							}
						?>
						
						
						<input type="number" name="cost_input" id="cost_input" placeholder="(COP)" step="5000" min="10000" max="10000000" required>
						
					</div>
					<div id="appo_type_form_right">
						<?php 
							switch ($lang){
									 		
								case("en"):
						?>
									<input type="submit" name="add_appo_type_butt" id="add_appo_type_butt" value="+ add">
						<?php 
							        break;
								
								case("es"):
						?>
									<input type="submit" name="add_appo_type_butt" id="add_appo_type_butt" value="+ agregar" readonly>
						<?php 
									break;
							}
						?>
						
					</div>
				</form>
	
			</div>
	
			<div id="added_appo_type">
				<?php 
					switch ($lang){
							 		
						case("en"):
				?>
							<h3>Added appointment types</h3>
				<?php 
					        break;
						
						case("es"):
				?>
							<h3>Tipos de citas agregados</h3>
				<?php 
							break;
					}
				?>
				
	
				<div id="appo_type_box_header">
					<?php 
						switch ($lang){
								 		
							case("en"):
					?>
								<div class='table_element' id='translucid_appo_type'><h1> Type </h1> </div><div class='table_element' id='translucid_appo_durat' s><h1> Duration (mins)</h1></div> <div class='table_element' id='translucid_appo_cost' ><h1>Price</h1></div><div class='del_appo_type' ><h1>Del.</h1></div>
					<?php 
						        break;
							
							case("es"):
					?>
								<div class='table_element' id='translucid_appo_type'><h1> Tipo </h1> </div><div class='table_element' id='translucid_appo_durat' s><h1> Duración (mins)</h1></div> <div class='table_element' id='translucid_appo_cost' ><h1>Precio</h1></div><div class='del_appo_type' ><h1>Elim.</h1></div>
					<?php 
								break;
						}
					?>
					
				</div>
				<br>
	
				<div class="added_appo_box" id="style-2" name="added_appo_box">
					<?php  
						echo $calendar->getAppoDurationSettings();
					?>
				</div>
				
				<?php 
					switch ($lang){
							 		
						case("en"):
				?>
							<div id="message_proh_del"> <p><b>Note:</b> &nbsp There must be ONE Appointment Type called <b>"First-Time"</b>,&nbsp as this is used in the ConfiDr Search. Otherwise, you will pe pushed to the end of the search results. </p></div>
				<?php 
					        break;
					        
						case("es"):
				?>
							<div id="message_proh_del"> <p><b>Nota:</b> &nbsp Debe haber una cita llamada <b>"Primera Vez"</b>,&nbsp ya que este es usado en la Búsqueda ConfiDr. De lo contrario, serás mostrado al final de las busquedas.</p> </div>
				<?php 
							break;
					}
				?>	
				
				<div id="message_proh_del"></div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="confirm_custom_week" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel"><?php 
					switch ($lang){
						
						case("en"):
							echo 'Confirm Custom Week';
							break;
							
						case("es"):
							echo 'Confirmar Semana Especial';
							break;
					}
					?></h4>
      </div>

      <div class="modal-body">
        <p>
        <?php 
		switch ($lang){
			
			case("en"):
				echo 'Are you sure you wish to create a custom schedule for this week?<br><br>
						By accepting, you will create a custom availability calendar ONLY for this particular week. This is useful for creating custom calendars during vacations or other special circumstances. <br><br>
                        If a patient has alredy scheduled an appointment during this week, you should inform them and ask him to reeschedule as this is NOT performed automatically.';
				break;
				
			case("es"):
				echo '¿Desea crear un horario especial para esta semana?<br><br>
						Al aceptar, se creará un horario especial de disponibilidad para esta semana específica. Esto es útil para crear horarios especiales durante sus vacaciones u otras circunstancias especiales. <br><br>
                        Si un paciente ya tiene una cita durante esta semana, es su deber informarle del cambio y solicitarle que reprograme su cita, ya que esto NO se hace automaticamente.';
				break;
		}
		?>

        </p>
      </div>

      <div class="modal-footer">
      	<a href="javascript:void(0);" onclick="updateWeek(); closeDropdownCalendar()">
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						<button type="button" class="btn btn-primary" data-dismiss="modal">Create</button>
			<?php 
				        break;
					
					case("es"):
			?>
						<button type="button" class="btn btn-primary" data-dismiss="modal">Crear</button>
			<?php 
						break;
				}
			?>
        		
        </a>
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						<button type="button" class="btn btn-default" id="cancel_button" data-dismiss="modal">Cancel</button>
			<?php 
				        break;
					
					case("es"):
			?>
						<button type="button" class="btn btn-default" id="cancel_button" data-dismiss="modal">Cancel</button>
			<?php 
						break;
				}
			?>
        	
      </div>
    </div>
  </div>
</div>


<script>
$(document).ready(function(){
	$("#month_selected,#year_selected").change(function(){
		getDropdownCalendar('<?php echo $txt_rep->entities($userLoggedIn); ?>');
	});
	$("#year_selected").change(function(){
		var year = $("#year_selected").find(":selected").val();
		var ajaxreq2 = $.ajax({
			url: "includes/handlers/ajax_dropdown_month_based_year.php",
			type: "POST",
			data: "year=" + year,
			cache: false,
	
			success: function(response){
				$("#month_selected").html("");
				$("#month_selected").html(response);
			}
		});
	});

	$("#add_phone_form").submit(function(event){
		event.preventDefault();
		$.post("includes/handlers/ajax_add_telephone.php", 
				{	telephone: $("#add_telephone").val()
					},
				function(data){
						$("#add_telephone_response").html(data);
			}											
		);
	});
});

</script>

<?php 
	
	for($i=2;$i<=3;$i++){
		
		switch ($lang){
			
			case("en"):
				echo <<<EOS
		<div class="modal fade" id="del_office_modal$i" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Cancel"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">Delete office?</h4>
		      </div>
		      
		      <div class="modal-body">
		        <p>
		        		Do you want to delete office "$nname[$i]" and its created schedules?<br>
					<b>Remember</b> to inform patients that have already schedule appointments here, where their appointment is going to be held.
		        </p>
				<br>
				<p>
		        		Please select to which office will the selected available times for "$nname[$i]" in the schedule will be transfered, or select "Delete" if you wish them to be deletead instead.<br>
		        </p>
		        
		        <form class="del_office_form$i" action="" method="POST">
		        	<div class="form_group">
				<p>Transfer to: </p><select id="transfer_selector" name="transfer_selector">
EOS;
				break;
				
			case("es"):
				echo <<<EOS
		<div class="modal fade" id="del_office_modal$i" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		    
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Cancel"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">¿Eliminar Oficina?</h4>
		      </div>
		      
		      <div class="modal-body">
		        <p>
		        		¿Deseas eliminar la oficina "$nname[$i]" y sus horarios seleccionados?<br>
					<b>Recuerda</b> informar a los pacientes que ya tienen cita en esta oficina en dónde va a ser su cita ahora.
		        </p>
				<p>
		        		Selecciona una oficina para transferirle los horarios seleccionados para "$nname[$i]", o selecciona "Eliminar" si deseas que estos se eliminen.<br>
		        </p>
		        
		        <form class="del_office_form$i" action="" method="POST">
		        	<div class="form_group">
				<p>Transferir a: </p><select id="transfer_selector" name="transfer_selector">
EOS;
				break;
		}
		

				
					$stmt = $con->prepare("SELECT * FROM basic_info_doctors WHERE username=?");
					$stmt->bind_param("s",$userLoggedIn);
					$stmt->execute();
					$query = $stmt->get_result();
					$res = mysqli_fetch_array($query);
					
					switch ($lang){
						
						case("en"):
							echo "<option value='0'> Delete </option>";
							break;
							
						case("es"):
							echo "<option value='0'> Eliminar </option>";
							break;
					}
					
					echo "<option selected='selected' value='1'>" . $res['ad1nick'] . "</option>";
					
					if($res['ad2nick'] == '' || $i == 2){
						if($res['ad3nick'] != '' &&  $i != 3){
							echo "<option value='3'>" . $res['ad3nick'] . "</option>";
						}
					}
					else{
						echo "<option value='2'>" . $res['ad2nick'] . "</option>";
						
						if($res['ad3nick'] != '' &&  $i != 3){
							echo "<option value='3'>" . $res['ad3nick'] . "</option>";
						}
					}
					
					switch ($lang){
						
						case("en"):
							echo <<<EOS
					</select>
		        		<input type="hidden" name="office_numb" value=$i>
		        		<input type="hidden" name="user" value=$userLoggedIn>
		        	</div>
		        	
		        </form>
		      </div>
		      
		      
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
		        <button type="button" class="btn btn-primary" name="delete_office_butt$i" id="delete_office_butt$i">Accept</button>
		      </div>
		    </div>
		  </div>
		</div>
EOS;
							break;
							
						case("es"):
							echo <<<EOS
					</select>
		        		<input type="hidden" name="office_numb" value=$i>
		        		<input type="hidden" name="user" value=$userLoggedIn>
		        	</div>
		        	
		        </form>
		      </div>
		      
		      
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
		        <button type="button" class="btn btn-primary" name="delete_office_butt$i" id="delete_office_butt$i">Aceptar</button>
		      </div>
		    </div>
		  </div>
		</div>
EOS;
							break;
					}
					

	}
?>
