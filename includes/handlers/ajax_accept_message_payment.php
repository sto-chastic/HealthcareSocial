<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Settings.php");
include("../classes/Message.php");
include("../classes/TxtReplace.php");
require '../../vendor/autoload.php';
$crypt = new Crypt();
if(isset($_SESSION['username']) && isset($_SESSION['messages_token'])){
    
	$temp_user = $_SESSION['username'];
	$temp_user_e = $_SESSION['usernam_e'];
	//$temp_passwrd = $_SESSION['passwrd'];
	$temp_messages_token= $_SESSION['messages_token'];
	
	$stmt = $con->prepare("SELECT * FROM users WHERE username=? AND messages_token=?");
	
	$stmt->bind_param("ss", $temp_user_e, $temp_messages_token);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	
	if(mysqli_num_rows($verification_query) == 1){
		$userLoggedIn = $temp_user;
		$userLoggedIn_e = $temp_user_e;
		
		if(isset($_SESSION['payment_in_progress'])){
			if($_SESSION['payment_in_progress'] == 1){
				$blocked = 1;
			}else{
				$blocked = 0;
				$_SESSION['payment_in_progress'] = 1;
			}
		}else{
			$blocked = 0;
			$_SESSION['payment_in_progress'] = 1;
		}
		$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
		$patient_user = $crypt->Decrypt(pack("H*",$_REQUEST['patient_user']));
		$txtrep = new TxtReplace();
		
		//Checks if the doctor decided not to charge the patient(s)
		
		$settings = new Settings($con, $userLoggedIn, $userLoggedIn_e);
		$payed_messages = $settings->getSettingsValues("payed_messages");
		
		$doctor_messages_status_table = $user_obj->getMessagesStatusTable();
		
		$sql_query = "SELECT * FROM $doctor_messages_status_table WHERE secondary_interlocutor=?";
		$stmt = $con->prepare($sql_query);
		$stmt->bind_param("s",$patient_user);
		$stmt->execute();
		$very_q = $stmt->get_result();
		
		$arr_mess_setts = mysqli_fetch_assoc($very_q);
		$free_enabled = $arr_mess_setts['enabled'];
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location:../../register.php");
		$stmt->close();
	}
	
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location:../../register.php");
	$stmt->close();
}

if(!$blocked && !$free_enabled && $payed_messages){
	$payment_accepted = FALSE;
	
	$_num_days = 14;
	$_purchased_days_str = 'P' . $_num_days . 'D';
	$_today = new DateTime();
	$payment_exp_date_obj = $_today->add(new DateInterval($_purchased_days_str));
	
	$temp_today = new DateTime();
	$_today_formated = $temp_today->format('Y-m-d H:i:s');
	$payment_exp_date = $payment_exp_date_obj->format('Y-m-d H:i:s');
	
	$_num_days_before = 2;
	$_num_days_before_str= 'P' . $_num_days_before. 'D';
	$_two_days_ago = new DateTime();
	$_two_days_ago_obj = $_two_days_ago->sub(new DateInterval($_num_days_before_str));
	$_two_days_ago_formated = $_two_days_ago_obj->format("Y-m-d H:i:s");
	
	
	//This bellow checks that the request was indeed submited less than 2 days ago, and that the user did submit the request
	$patient_user_e = $crypt->EncryptU($patient_user);
	$patient_obj = new User($con, $patient_user, $patient_user_e);
	$patient_pay_hist = $patient_obj->getPaymentsHistTab();
	
	$sql_query = "SELECT * FROM $doctor_messages_status_table WHERE secondary_interlocutor=? AND date_payed>? AND `accepted_payment`=0";
	$stmt = $con->prepare($sql_query);
	$stmt->bind_param("ss",$patient_user,$_two_days_ago_formated);
	$stmt->execute();
	$very_q = $stmt->get_result();
	
	if(mysqli_num_rows($very_q) > 0){
		//This bellow checks that the patient did issue the request
		$very_q_arr = mysqli_fetch_assoc($very_q);
		$bill_number = $very_q_arr['bill_number'];
		
		$sql_query_2 = "SELECT * FROM $patient_pay_hist WHERE bill_number=?";
		$stmt = $con->prepare($sql_query_2);
		$stmt->bind_param("s",$bill_number);
		$stmt->execute();
		$very_q_2 = $stmt->get_result();
		
		if(mysqli_num_rows($very_q_2) > 0){
			$very_q_arr_2 = mysqli_fetch_assoc($very_q_2);
			$bill_number = $very_q_arr_2['bill_number'];
			
			$patient_pay = $patient_obj->getPaymentsTab();
			
			$sql_query_3 = "SELECT * FROM $patient_pay WHERE id=1";
			$stmt = $con->prepare($sql_query_3);
			$stmt->execute();
			$very_q_3 = $stmt->get_result();
			
			$payments_info_arr = mysqli_fetch_assoc($very_q_3);		
			
			//print_r($payments_info_arr);
			
			$price = $very_q_arr_2['amount'];
			//Sacar la info de abajo de payments y payments hist del paciente. Añadir el iva al precio antes.
			
			$epayco = new Epayco\Epayco(array(
					"apiKey" => "f83785ced1e42608d315d1a14fc3fe93",
					"privateKey" => "c1e131360d2f3ba9ca9fa49325734364",
					"lenguage" => "ES",
					"test" => true
			));
			
			$token_card = $payments_info_arr['cc_token'];
			$customer_id = $payments_info_arr['client_token'];
			$doc_number = $payments_info_arr['docu_number'];
			$name = $patient_obj->getFirstName();
			$last_name = $patient_obj->getLastName();
			$email = $patient_obj->getEmail();
			$description_patient = $very_q_arr_2['description'];
			$ip = $payments_info_arr['ip'];
			
			$test_Arr= array(
					"public_key" => "f83785ced1e42608d315d1a14fc3fe93",
					"token_card" => (string)$token_card,
					"customer_id" => (string)$customer_id,
					"doc_type" => "CC",
					"doc_number" => (string)$doc_number,
					"name" => (string)$name,
					"last_name" => (string)$last_name,
					"email" => (string)$email,
					"ip" => (string)$ip,
					"bill" => (string)$bill_number,
					"description" => (string)$description_patient,
					"value" => (string)$price,
					"tax" => "0",
					"tax_base" => (string)$price,
					"currency" => "COP",
					"dues" => "1"
			);
			
			//print_r(json_encode($test_Arr));
			//echo json_encode($test_Arr);
			
			$pay = $epayco->charge->create($test_Arr);
			
			//print_r($pay);
			if(property_exists($pay,"success")){
				if($pay->data->estado == "Aceptada"){
			
					$payment_accepted = TRUE;
					$ref_payco = $pay->data->ref_payco;
				
					$response = $epayco->charge->transaction($ref_payco);
					//print_r($response);
					
					//Insert in doctor payment history
					
					$description = "Messaging-Service to be Payed"; 
					$doctor_payment_hist = $user_obj->getPaymentsHistTab();
					
					$doctor_payed_price = $price*0.85; //CONFIDR DISCOUNT
					$_today = new DateTime();
					$_today_formated = $_today->format('Y-m-d H:i:s');
					
					
					$sql_query = "
					INSERT INTO $doctor_payment_hist (`bill_number`, `description`, `amount`, `datetime_issued`, `payed`) VALUES(?,?,?,?,'n')";
					$stmt = $con->prepare($sql_query);
					$stmt->bind_param("ssis",$bill_number,$description,$doctor_payed_price,$_today_formated);
					$stmt->execute();
					
					
					$sql_query_2= "UPDATE $doctor_messages_status_table SET `accepted_payment`=1,`date_activated`=?,`date_termination`=? WHERE secondary_interlocutor=? AND date_payed>?";
					$stmt = $con->prepare($sql_query_2);
					$stmt->bind_param("ssss",$_today_formated,$payment_exp_date,$patient_user,$_two_days_ago_formated);
					$stmt->execute();
				}
			}
		}
	}
	
	if($payment_accepted){
		echo "acc";
	}
	else{
		echo "den";
	}
	
	$_SESSION['payment_in_progress'] = 0;
}
else{
	$_SESSION['payment_in_progress'] = 0;
	echo "ign";
}


?>