<?php 

	include("../../config/config.php");
	include("../../includes/classes/User.php");
	include("../classes/TxtReplace.php");
	$crypt = new Crypt();
	if(isset($_SESSION['username'])){
		$temp_user = $_SESSION['username'];
		$temp_user_e = $_SESSION['username_e'];
		//$temp_passwrd = $_SESSION['passwrd'];
		
		$stmt = $con->prepare("SELECT * FROM users WHERE username=?");
		
		$stmt->bind_param("s", $temp_user_e);
		$stmt->execute();
		$verification_query = $stmt->get_result();
		
		if(mysqli_num_rows($verification_query) == 1){
			$userLoggedIn = $temp_user; //Retrieves username
			$userLoggedIn_e = $temp_user_e;
			$user = mysqli_fetch_array($verification_query);
			//$messages_token = $temp_messages_token;
			$stmt->close();
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: ../../register.php");
			$stmt->close();
		}
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}

	$query = $_POST['query'];
	$doctor_e = pack("H*", $_POST['doctor']);
	$doctor = $crypt->Decrypt($doctor_e);
	$doctor_obj = new User($con, $doctor, $doctor_e);

	$search_array = explode(",", $query);

	$end = end($search_array);
	$element = rtrim(ltrim(ucwords(strtolower($end))));

	$prev_elements = "";
	unset($search_array[count($search_array) - 1]);
	$search_array = array_map('strtolower',$search_array);
	$search_array = array_map('ucwords',$search_array);


	if(count($search_array) > 0){
		//$prev_elements_arr = ucwords(strtolower($rest));
		$prev_elements = implode(",", $search_array);		
	}

	$med_symp = $_POST['medsymp'];

	if($med_symp == "symptoms"){
		$table = $doctor_obj->getAppointmentsSymptoms_Doctor();
		$table_element = 'title';
		$_SESSION['post_symptoms'] = $element;
	}
	elseif($med_symp == "medicines"){
		$table = $doctor_obj->getAppointmentsMedicines_Doctor();
		$table_element = 'name';
		$_SESSION['post_medicines'] = $element;
	}

	if($element != ""){

		$stmt = $con->prepare("SELECT DISTINCT $table_element FROM $table WHERE $table_element LIKE ? LIMIT 6");

		$stmt->bind_param("s", $search_term);
		$search_term = '%' . $element . '%';
		$stmt->execute();
		$resultsReturned = $stmt->get_result();

		while($arr = mysqli_fetch_array($resultsReturned)){
			$i_title = $arr[$table_element];

			$trimmed_search_array=array_map('ltrim',$search_array);
			$trimmed_search_array=array_map('rtrim',$trimmed_search_array);

			if(!in_array($i_title, $trimmed_search_array)){
				$txt_rep = new TxtReplace();

				echo <<<EOS
						<a href="javascript:void(0);" onclick="selectSearchResult('$i_title', '$prev_elements', '$med_symp')">
							<div class='resultSympMedDisplay'>
								+ $i_title
							</div>
						</a>
EOS;
			}
		}

	}
?>