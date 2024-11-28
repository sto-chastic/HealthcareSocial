<?php

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
		$userLoggedIn_e = $temp_user_e;//Retrieves username
		
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
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	$txt_rep = new TxtReplace();
	$settings = new Settings($con, $userLoggedIn, $userLoggedIn_e);
	
	$comments = $user_obj->getCommentsTable();
	$connection_requests = $user_obj->getRequestsTable();
	$likes = $user_obj->getLikesTable();
	$messages = $user_obj->getMessagesTable();
	$notifications = $user_obj->getNotificationsTable();
	$posts = $user_obj->getPostsTable();
	
	
	
	//LANGUAGE RETRIEVAL
	
	$lang = $settings->getLang();
	$_SESSION["lang"] = $lang;
	
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: register.php");
	$stmt->close();
}


//Declaring variables to prevent errors
$fname = ""; //First Name
$lname = ""; //Last Name
$em = ""; //email
$em2 = ""; //email 2
$date = ""; //Sign up date
$error_array = array(); //Holds error messages
$insu_err_arr1 = array();

//doctor calendar set
$blocks_in_an_hour = 2;
$minutes_per_block = 60/$blocks_in_an_hour;
$start_time = "6:00";
$end_time = "21:00";
$dteStart = new DateTime($start_time);
$dteEnd   = new DateTime($end_time);
$hours_per_day = $dteStart->diff($dteEnd)->format("%H");
$num_blocks = $hours_per_day*$blocks_in_an_hour;

$var_time = "";
$txt_rep = new TxtReplace();

if(isset($_POST['premium2_button_doctor'])){
    
	$username = $userLoggedIn;
	
	// Get the encripted username
	$posts = $user_obj->getPostsTable();
	$pieces = explode("_", $posts);
	$hash = $pieces[0];
    
    //Address 1
    
    $reg_ad1ln1 = strip_tags($_POST['prem_ad1ln1']);//Remove html tags
    $reg_ad1ln1= mysqli_real_escape_string($con,$reg_ad1ln1);
    $reg_ad1ln1= ucwords(strtolower($reg_ad1ln1)); //Uppercase first letter
    $_SESSION['prem_ad1ln1'] = $reg_ad1ln1;
    
    //Address 2
    
    $reg_ad1ln2 = strip_tags($_POST['prem_ad1ln2']);//Remove html tags
    $reg_ad1ln2= mysqli_real_escape_string($con,$reg_ad1ln2);
    $reg_ad1ln2= ucwords(strtolower($reg_ad1ln2)); //Uppercase first letter
    $_SESSION['prem_ad1ln2'] = $reg_ad1ln2;
    
    //Address 3
    
    $reg_ad1ln3 = strip_tags($_POST['prem_ad1ln3']);//Remove html tags
    $reg_ad1ln3= mysqli_real_escape_string($con,$reg_ad1ln3);
    $reg_ad1ln3= ucwords(strtolower($reg_ad1ln3)); //Uppercase first letter
    $_SESSION['prem_ad1ln3'] = $reg_ad1ln3;
    
    //City
    
    $reg_ad1city = strip_tags($_POST['prem_ad1cityCode']);//Remove html tags
    $reg_ad1city= mysqli_real_escape_string($con,$reg_ad1city);
    $_SESSION['prem_ad1cityCode'] = $reg_ad1city;
    
    $_SESSION['prem_ad1city'] = $_POST['prem_ad1city'];
    $reg_ad1adm2 = strip_tags($_POST['prem_ad1adm2']);//Remove html tags
    $reg_ad1adm2= mysqli_real_escape_string($con,$reg_ad1adm2);
    $_SESSION['prem_ad1adm2'] = $_POST['prem_ad1adm2'];
    
    //Country
    
    //This is done in the register
//     $reg_adcountry = strip_tags($_POST['reg_adcountry']);//Remove html tags
//     $_SESSION['reg_adcountry'] = $reg_adcountry;

    $reg_adcountry = 'CO';
    
    //Coordinates
    
    $prem_lat = preg_replace('/[^0-9\.-]/', '', $_POST['prem_lat']);
    $prem_lng= preg_replace('/[^0-9\.-]/', '', $_POST['prem_lng']);
    
    //Insurance
    
    $insu1 = $_POST['searched_insurance1'];
    $_SESSION['searched_insurance1'] = $insu1;
    
    $insurance_codes_array1 = [];
    
    $insu1 = rtrim($insu1,', ');
    $insu1 = rtrim($insu1,',');
    
    $insu1_no_sp = str_replace(', ', ',', $insu1);
    $insu1Arr = explode(',', $insu1_no_sp);
    
    //Appointment Cost
    
    $cost_appo = strip_tags($_POST['cost_appo']);
    $cost_appo = mysqli_real_escape_string($con,$cost_appo);
    $_SESSION['cost_appo'] = $cost_appo;
    
    $lang = $_SESSION['lang']; 
    
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
        array_push($insurance_codes_array1, 'CO00');
    }
    
    //Specialization
    
    //ONLY REGISTER, NOT HERE
    
//     $speciali = $_POST['specialization_code'];
// //     $_SESSION['specialization_code'] = $speciali;
    
//     $specialization_text = $_POST['search_text_input_specialization'];
    
    
//     $specialization_codes_array1 = [];
    
//     $speciali= rtrim($speciali,',');
    
//     $specialiArr = explode(',', $speciali);
    
   
//     $stmt = $con->prepare("SELECT id FROM specializations WHERE id = ?");
//     $stmt->bind_param("s",$temp_arr_elem);
    
//     foreach ($specialiArr as $temp_arr_elem){
// 	    	$stmt->execute();
// 	    	$q = $stmt->get_result();
// 	    	if(mysqli_num_rows($q) == 0 && $specialization_text != ''){
// 	    		array_push($error_array, "specialization_not_found");
// 	    	}
// 	    	elseif($temp_arr_elem != ''){
// 	    		$id = mysqli_fetch_array($q)['id'];
// 	    		$specialization_codes_array1[] = $id;
// 	    	}
//     }
    
    
//     $specialization_name = $_POST['search_text_input_specialization'];
//     $element = $txt_rep->prepareForSearch($specialization_name);
//     $temp_specialization_id = $_POST['specialization_code'];
//     $search_spec_col = $lang . "_search";
    
//     $stmt = $con->prepare("SELECT id FROM specializations WHERE $search_spec_col = ? OR id = ?");
    
//     $stmt->bind_param("ss", $search_term, $temp_specialization_id);
//     $search_term = $element;
//     $stmt->execute();
//     $resultsReturned = $stmt->get_result();
    
//     $num_rows_sp = mysqli_num_rows($resultsReturned);

    
    if(empty($error_array) && !empty($insurance_codes_array1)){
         
        //Table Creation
        
        $comments = $hash . "__comments";//
        $connection_requests = $hash . "__connection_requests";//
        $likes = $hash . "__likes";//
        $messages = $hash . "__messages";//
        $notifications = $hash . "__notifications";//
        $posts = $hash . "__posts";//
        $connections = $hash . "__connections";//
        $patients_recurrent = $hash . "__patients_recurrent";//not included in users_tables, as in the future will all be
        $patients_seen = $hash . "__patients_seen";//not included in users_tables, as in the future will all be
        $basic_info = $hash . "__basic_info";//
        $appointments_calendar_patient = $hash . "__appointments_calendar_patient";//
        $appointments_pat = $hash . "__appointments_details_pat";//
        $symptoms_pat = $hash . "__symptoms_pat";//
        $medicines_pat = $hash . "__medicines_pat";//
        
        
        //Doctor tables
        $doc_education = $hash . "__doc_education";//
        $doc_certificates = $hash . "__doc_certificates";
        $congresses_private_tab = $hash . "__congresses_private_tab";
        $appo_duration_tab = $hash . "__appo_duration_tab";
        $calendar_availability = $hash . "__calendar_availability";
        $appointments_calendar_doc = $hash . "__appointments_calendar_doc";
        $appointments_doc = $hash . "__appointment_details_doc";
        $symptoms_doc = $hash . "__symptoms_doc";
        $medicines_doc = $hash . "__medicines_doc";
        $external_patients = $hash . "__external_patients";
        
        
        $rips = $hash . "__rips";
        $education = $hash . "__education";//
        $jobs = $hash . "__jobs";//
        $conferences = $hash . "__conferences";//
        $description = $hash . "__description";//
        $awards = $hash . "__awards";//
        $publications = $hash . "__publications";//
        $webpages = $hash . "__webpages";//
        
        
        //Constraint creation
        $fk_username_by_comm = $hash . "_fk_username_by_comm";
        $fk_username_to_comm = $hash . "_fk_username_to_comm";
        $fk_post_id_comm = $hash . "_fk_post_id_comm";
        $fk_username_to_con_req = $hash . "_fk_username_to_con_req";
        $fk_username_from_con_req = $hash . "_fk_username_from_con_req";
        $fk_username_from_lik = $hash . "_fk_username_from_lik";
        $fk_post_id_lik = $hash . "_fk_post_id_lik";
        $fk_username_to_mess = $hash . "_fk_username_to_mess";
        $fk_username_from_mess = $hash . "_fk_username_from_mess";
        $fk_username_to_not = $hash . "_fk_username_to_not";
        $fk_username_from_not = $hash . "_fk_username_from_not";
        $fk_username_posts = $hash . "_fk_username_posts";
        $fk_username_conn= $hash . "_fk_username_conn";
        $fk_pat_recurrent= $hash . "_fk_pat_recurrent";
        $fk_pat_seen= $hash . "_fk_pat_seen";
        $fk_appo_patient_user_doc = $hash . "_fk_appo_patient_user_doc";
        $fk_appo_patient_user_pat = $hash . "_fk_appo_patient_user_pat";
        $fk_appo_pat_symptoms_id = $hash . "_fk_appo_pat_symptoms_id";
        $fk_appo_pat_medicines_id = $hash . "_fk_appo_pat_medicines_id";
        $fk_appointment_cons_id_pat = $hash . "_fk_appointment_cons_id_pat";
        $fk_rips_consultid = $hash . "_rips_consultid";
        
        
        
        //Doctor constraints
        $fk_username_edu = $hash . "_fk_username_edu";
        $fk_school_edu = $hash . "_fk_school_edu";
        $fk_degree_edu = $hash . "_fk_degree_edu";
        $fk_username_cert = $hash . "_fk_username_cert";
        $fk_cert_cert = $hash . "_fk_cert_cert";
        $fk_issuer_cert = $hash . "_fk_issuer_cert";
        $fk_username_congresses = $hash . "_fk_username_congresses";
        $fk_user_appo_dur = $hash . "_fk_user_appo_dur";
        $fk_dura_appo_dur = $hash . "_fk_dura_appo_dur";
        $fk_appointment_user_doc = $hash . "_fk_appointment_user_doc";
        $fk_appointment_user_pat = $hash . "_fk_appointment_user_pat";
        $fk_appo_doc_symptoms_id = $hash . "_fk_appo_doc_symptoms_id";
        $fk_appo_doc_medicines_id = $hash . "_fk_appo_doc_medicines_id";
        $fk_appointment_cons_id = $hash . "_fk_appointment_cons_id";
      
        //Insert into basic_info_doctors

        
        $lat = $prem_lat;
        $long = $prem_lng;
        $insu_arr_ser = serialize($insurance_codes_array1);
//        $spec_arr_ser = serialize($specialization_codes_array1);
        
        //TODO: SELECT THE FREE PERIOD DURATION HERE!!!
        $num_free_months = 1;
        $_num_free_days = $num_free_months*30;
        $_free_months_str = 'P' . $_num_free_days . 'D';
        $_today = new DateTime();
        $payment_exp_date_obj = $_today->add(new DateInterval($_free_months_str));
        $payment_exp_date = $payment_exp_date_obj->format('Y-m-d');
        
//         $sql_string = "
// 		INSERT INTO `basic_info_doctors`
// 		(`username`, `specializations`, `sex`, `insurance_accepted_1`, `insurance_accepted_2`,
// 		 `insurance_accepted_3`, `md_conn`, `pat_seen`, `pat_foll`, `pat_inter`, `pat_rec`,
// 		 `ad1nick`, `ad1ln1`, `ad1ln2`, `ad1ln3`, `ad1city`, `ad1adm2`, `adcountry`, `ad1lat`,
// 		 `ad1lng`, `ad2nick`, `ad2ln1`, `ad2ln2`, `ad2ln3`, `ad2city`, `ad2adm2`, `ad2lat`,
// 		 `ad2lng`, `ad3nick`, `ad3ln1`, `ad3ln2`, `ad3ln3`, `ad3city`, `ad3adm2`, `ad3lat`,
// 		 `ad3lng`, `payment_expiration_date`, `up_to_date`)
// 		 VALUES (?,?,?,?,
// 		 '','',0,0,0,0,0,
// 		 'Office 1',?/*ad1ln*/,?/*ad1ln1*/, ?/*ad1ln2*/, ?/*ad1ln3*/, ?/*ad1city*/, ?/*ad1adm2*/, ?/*adcountry*/, ?/*ad1lat*/,?/*ad1lng*/
// 		 '','','','','','','','','','','','','','','','',?,1)
// 		 ON DUPLICATE KEY UPDATE
// 		 `specializations` = ?, `insurance_accepted_1` = ?, 
// 		 `ad1nick` = ?, `ad1ln1` = ?, `ad1ln2` = ?, `ad1ln3` = ?, `ad1city` = ?, `ad1adm2` = ?, `adcountry` = ?, `ad1lat` = ?, `ad1lng` = ?,
// 		 `payment_expiration_date` = ?, `up_to_date` = ?
// 		";
        switch($lang){
            case("en"):
                $sql_string = "
                		UPDATE `basic_info_doctors`
                		SET
                		`insurance_accepted_1` = ?,
                		`ad1nick` = 'Office 1', `ad1ln1` = ?, `ad1ln2` = ?, `ad1ln3` = ?, `ad1city` = ?, `ad1adm2` = ?, `ad1lat` = ?, `ad1lng` = ?,
                		`payment_expiration_date` = ?, `up_to_date` = 1
                		WHERE `username` = ?
                		";
                break;
            case("es"):
            default:
                $sql_string = "
                		UPDATE `basic_info_doctors`
                		SET
                		`insurance_accepted_1` = ?,
                		`ad1nick` = 'Consultorio 1', `ad1ln1` = ?, `ad1ln2` = ?, `ad1ln3` = ?, `ad1city` = ?, `ad1adm2` = ?, `ad1lat` = ?, `ad1lng` = ?,
                		`payment_expiration_date` = ?, `up_to_date` = 1
                		WHERE `username` = ?
                		";
                break;
        }
        
        
        $stmt = $con->prepare($sql_string);
        $stmt->bind_param("ssssssddss",$insu_arr_ser,$reg_ad1ln1,$reg_ad1ln2,$reg_ad1ln3,$reg_ad1city,$reg_ad1adm2,$lat,$long,$payment_exp_date,$username);
        $stmt->execute();
        
        //QUERIES
        
        // 		$query = mysqli_query($con,
        // 			"CREATE TABLE $doc_certificates (
        // 			id int(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        // 			username VARCHAR(100) NOT NULL,
        // 			certification int(9) NOT NULL,
        // 			issuer int(9) NOT NULL /*--references public table*/,
        // 			issue_date date,
        // 			description BLOB,
        
        // 			CONSTRAINT $fk_username_cert FOREIGN KEY(username) REFERENCES users(username),
        // 			CONSTRAINT $fk_cert_cert FOREIGN KEY(certification) REFERENCES certifications(id),
        // 			CONSTRAINT $fk_issuer_cert FOREIGN KEY(issuer) REFERENCES cert_issuer(id)
        // 			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
        // 		);
        
        // 		$query = mysqli_query($con,
        // 			"CREATE TABLE $congresses_private_tab (
        // 			id int(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        // 			username VARCHAR(100) NOT NULL,
        // 			congress_name VARCHAR(60) NOT NULL,
        // 			start_date date,
        // 			end_date date,
        // 			description BLOB,
        // 			congress_username VARCHAR(100),
        
        // 			CONSTRAINT $fk_username_congresses FOREIGN KEY(username) REFERENCES users(username)
        // 			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
        // 		);
        
        //Appointment Settings
        $query = mysqli_query($con,
            "CREATE TABLE $appo_duration_tab (
			id int(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			appo_type VARCHAR(40) NOT NULL,
			duration int(2) NOT NULL,
			cost int(8),
			currency varchar(3),
			deleted bit(1),
            
			CONSTRAINT $fk_dura_appo_dur FOREIGN KEY(duration) REFERENCES appo_duration(duration)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
            );
        
        if($reg_adcountry == 'CO'){
            $currency ='COP';
        }
        else{
            $currency ='';
        }
        
        $query = mysqli_query($con,
            "CREATE TABLE $calendar_availability (
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
        
        $querr = mysqli_query($con, "SELECT * FROM $calendar_availability WHERE 1");
        $num_rows_check_prev = mysqli_num_rows($querr);
        
        if($num_rows_check_prev== 0){
	        	switch ($lang){
	        		
	        		case("en"):
	        			$stmt = $con->prepare("INSERT INTO $appo_duration_tab (id,appo_type,duration,cost,currency,deleted) VALUES('','First-Time',20,?,?,0)");
	        			break;
	        			
	        		case("es"):
	        			$stmt = $con->prepare("INSERT INTO $appo_duration_tab (id,appo_type,duration,cost,currency,deleted) VALUES('','Primera Vez',20,?,?,0)");
	        			break;
	        	}
	        	
	        	$stmt->bind_param("ss",$cost_appo,$currency);
	        	$stmt->execute();
        }
        
        $var_time = strtotime($start_time);
        
        if($num_rows_check_prev== 0){
	        $stmt = $con->prepare("INSERT INTO $calendar_availability VALUES ('',?,?,0,0,0,0,0,0,0,0,0,0,0,0,0,0)");
	        $stmt->bind_param("ss", $hour_var, $hour_var_end);
	        
	        for($i = 0; $i <= $num_blocks; $i++){
	            $var_time_temp = $var_time + $minutes_per_block*60*$i;//60 seconds per each minute
	            $var_time_temp_end = $var_time_temp + 60*$minutes_per_block;//60 seconds per each minute
	            
	            $hour_var_end = date('H:i', $var_time_temp_end);
	            $hour_var = date('H:i', $var_time_temp);
	            $stmt->execute();
	        }
        }
        //Doctor appointments tables
        
        $query = mysqli_query($con,
            "CREATE TABLE $appointments_doc (
			consult_id varchar(100) NOT NULL PRIMARY KEY,
			payment_info VARCHAR(30) NOT NULL,
			appo_type int(3) NOT NULL,
			doctor_username VARCHAR(100) NOT NULL,
			patient_username VARCHAR(100) NOT NULL,
			cancelled_by_pat BIT(1) NOT NULL,
			cancelled_by_doc BIT(1) NOT NULL,
			reescheduled BIT(1),
			cost int(8),
			currency varchar (3),
			payed_through_confidr BIT(1),
            
			plan BLOB,
			closed BIT(1) NOT NULL,
			notes BLOB,
			
			external_patient bit(1),
			office INT(1) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
            );
        
        echo mysqli_error($con);
        
        $query = mysqli_query($con,
            "CREATE TABLE $appointments_calendar_doc (
			year varchar(4) NOT NULL,
			month varchar(4) NOT NULL,
			day varchar(4) NOT NULL,
			time_start varchar(8) NOT NULL,
			time_end varchar(8) NOT NULL,
			consult_id varchar(100) NOT NULL PRIMARY KEY,
			confirmed_pat BIT(1) NOT NULL,
			confirmed_doc BIT(1) NOT NULL,
			creation_date_time datetime NOT NULL,
			/*id int(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,*/
			CONSTRAINT $fk_appointment_cons_id FOREIGN KEY(consult_id) REFERENCES $appointments_doc(consult_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
            );
        
        echo mysqli_error($con);
        
        $query = mysqli_query($con,
            "CREATE TABLE $symptoms_doc (
			consult_id varchar(100) NOT NULL,
			title VARCHAR(30) NOT NULL,
			description BLOB,
			start VARCHAR(30),
			frequency VARCHAR(50),
			id varchar(100) NOT NULL PRIMARY KEY,
			CONSTRAINT $fk_appo_doc_symptoms_id FOREIGN KEY(consult_id) REFERENCES $appointments_doc(consult_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
            );
        
        echo mysqli_error($con);
        
        $query = mysqli_query($con,
        		"CREATE TABLE $external_patients(
			username varchar(100) NOT NULL PRIMARY KEY,
        		name varchar(50),
			contact_info varchar(100),
        		insurance VARCHAR(30),
        		notes BLOB
        		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
        	);
        
        echo mysqli_error($con);
        
        $query = mysqli_query($con,
            "CREATE TABLE $medicines_doc (
			consult_id varchar(100) NOT NULL,
			name VARCHAR(30) NOT NULL,
			dosage varchar(50),
			units varchar(30),
			description BLOB,
			start VARCHAR(30),
			frequency VARCHAR(50),
			id varchar(100) NOT NULL PRIMARY KEY,
			CONSTRAINT $fk_appo_doc_medicines_id FOREIGN KEY(consult_id) REFERENCES $appointments_doc(consult_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
        );
        
        echo mysqli_error($con);
        
        $query = mysqli_query($con,
            "CREATE TABLE $rips (
			consult_id varchar(100) NOT NULL PRIMARY KEY,
            numero_factura int(6),
            tipo_identificación varchar(2),
            numero_identificacion varchar(12),
            fecha_consulta date,
            num_autorizacion_1 varchar(20),
            cod_consulta varchar(8),
            finalidad_consulta varchar(2),
            num_autorizacion_2 varchar(20),
            cod_diag_1 varchar(4),
            cod_diag_2 varchar(4),
            cod_diag_3 varchar(4),
            cod_diag_4 varchar(4),
            valor_copago int(15),
            valor_consulta int(15),
            valor_neto int(15),
            
			CONSTRAINT $fk_rips_consultid FOREIGN KEY(consult_id) REFERENCES $appointments_doc(consult_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
            );
        
        echo mysqli_error($con);
        
        array_push($error_array, "<span style='color: #14C800'> You are all set. Please log in. </span><br>");
        
        //Clear session variables
        $_SESSION['prem_ad1ln1'] = "";
        $_SESSION['prem_ad1ln2'] = "";
        $_SESSION['prem_ad1ln3'] = "";
        $_SESSION['prem_ad1cityCode'] = "";
        $_SESSION['prem_ad1city'] = "";
        $_SESSION['prem_ad1adm2'] = "";
        $_SESSION['reg_adcountry'] = "";
        $_SESSION['searched_insurance1'] = "";
        $_SESSION['cost_appo'] = "";
        $_SESSION['specialization_code'] = "";
        header("Location: calendar_settings.php");
    }
}
?>