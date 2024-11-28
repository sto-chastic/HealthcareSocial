<?php
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
		header("Location: register.php");
		$stmt->close();
	}
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: register.php");
	$stmt->close();
}


function validateDate($date, $format = 'Y-m-d'){
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}

function validateDateNoFormat($date){
	try{
		$d = new DateTime($date);
	} catch (Exception $e){
		$d = FALSE;
	}
	return $d !== FALSE;
}

//Error arrays

$women_error_array = [];
$basic_error_array = [];

if(isset($_POST['save_personal_info'])){
	$sex = $_POST['select_sex'];
	$_SESSION['select_sex'] =  $sex;
	$blood_type = $_POST['select_blood_type'];
	$_SESSION['select_blood_type'] = $blood_type;
	
	$birthdate = $_POST['select_birthdate'];
	if(!validateDate($birthdate) && $birthdate!= ''){
		array_push($basic_error_array, "birthdate");
	}
	elseif($birthdate != ''){
		$_SESSION['select_birthdate'] = $birthdate;
	}
	
	if($_POST['select_children'] != ''){
		$children = $_POST['select_children'];
		$_SESSION['select_children'] = $children;
	}
	else
		$children = '';
		
		if($_POST['select_marital_status'] != ''){
			$marital_status = $_POST['select_marital_status'];
			$_SESSION['select_marital_status'] = $marital_status;
		}
		else
			$marital_status = '';
			
			if($_POST['select_education_level'] != ''){
				$education_level = $_POST['select_education_level'];
				$_SESSION['select_education_level'] = $education_level;
			}
			else
				$education_level = '';
				
				if($_POST['select_occupation'] != ''){
					$occupation = $_POST['select_occupation'];
					$_SESSION['select_occupation'] = $occupation;
				}
				else
					$occupation = '';
					
					if($_POST['select_religion'] != ''){
						$religion = $_POST['select_religion'];
						$_SESSION['select_religion'] = $religion;
					}
					else
						$religion = '';
						
						if($_POST['select_languages'] != ''){
							$languages = $_POST['select_languages'];
							$_SESSION['select_languages'] = $languages;
						}
						else
							$languages = '';
							
							$insurance = $_POST['select_insurance_CO'];
							$_SESSION['select_insurance_CO'] = $insurance;
							
							if($_POST['select_laterality'] != ''){
								$laterality = $_POST['select_laterality'];
								$_SESSION['select_laterality'] = $laterality;
							}
							else
								$laterality = '';
								
								if(empty($basic_error_array)){
									$current_update_date = date("Y-m-d H:i:s");
									$stmt = $con->prepare("REPLACE INTO basic_info_patients (username,sex,blood_type,birthdate,marital_status,children,education_level,occupation,religion,languages,insurance,laterality,last_update) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
									$stmt->bind_param("ssissssssssss",$userLoggedIn,$sex,$blood_type,$birthdate,$marital_status,$children,$education_level,$occupation,$religion,$languages,$insurance,$laterality,$current_update_date);
									$stmt->execute();
									
									$_SESSION['select_sex'] = "";
									$_SESSION['select_blood_type'] = "";
									$_SESSION['select_birthdate'] = "";
									$_SESSION['select_children'] = "";
									$_SESSION['select_marital_status'] = "";
									$_SESSION['select_education_level'] = "";
									$_SESSION['select_occupation'] = "";
									$_SESSION['select_religion'] = "";
									$_SESSION['select_languages'] = "";
									$_SESSION['select_insurance_col'] = "";
									$_SESSION['select_laterality'] = "";
								}
}

if(isset($_POST['save_habits_info'])){
	$habits_table = $user_obj->getHabitsTable();
	
	$smoking= $crypt->encryptStringPI($_POST['select_smoking'], $userLoggedIn, $user_obj->user_info['signup_date']);
	
	$alcohol= $crypt->encryptStringPI($_POST['select_alcohol'], $userLoggedIn, $user_obj->user_info['signup_date']);
	
	$diet= $crypt->encryptStringPI($_POST['select_diet'], $userLoggedIn, $user_obj->user_info['signup_date']);
	
	$physical_activity= $crypt->encryptStringPI($_POST['select_physical_activity'], $userLoggedIn, $user_obj->user_info['signup_date']);
	
	$other= $crypt->encryptStringPI($_POST['select_other'], $userLoggedIn, $user_obj->user_info['signup_date']);
	
	$current_update_date = date("Y-m-d H:i:s");
	$stmt = $con->prepare("INSERT INTO $habits_table(id, smoking, alcohol, diet, physical_activity, other, last_update) VALUES ('',?,?,?,?,?,?)");
	$stmt->bind_param("ssssss",$smoking,$alcohol,$diet,$physical_activity,$other,$current_update_date);
	$stmt->execute();
}

if(isset($_POST['save_women_info'])){
	//$women_error_array = [];
	$OBGYN_table = $user_obj->getOBGYNTable();
	
	$menarche= $_POST['select_menarche'];
	$_SESSION['select_menarche'] = $menarche;
	
	$lmp= $_POST['select_lmp'];
	if($lmp == date('Y') . "-"){
		$lmp = '';
	}
	
	if(!validateDate($lmp) && $lmp != ''){
		array_push($women_error_array, "lmp");
	}
	elseif($lmp != ''){
		$_SESSION['select_lmp'] = $lmp;
	}
	
	$cycles= $_POST['select_cycles'];
	$_SESSION['select_cycles'] = $cycles;
	$gestations= $_POST['select_gestations'];
	$_SESSION['select_gestations'] = $gestations;
	$parity= $_POST['select_parity'];
	$_SESSION['select_parity'] = $parity;
	$abortions= $_POST['select_abortions'];
	$_SESSION['select_abortions'] = $abortions;
	$csections= $_POST['select_csections'];
	$_SESSION['select_csections'] = $csections;
	$ectopic= $_POST['select_ectopic'];
	$_SESSION['select_ectopic'] = $ectopic;
	$menopause= $_POST['select_menopause'];
	$_SESSION['select_menopause'] = $menopause;
	$birthcontrol= $_POST['select_birthcontrol'];
	$_SESSION['select_birthcontrol'] = $birthcontrol;
	
	$mammography_date= $_POST['select_mammography_date'];
	if(!validateDate($mammography_date) && $mammography_date != ''){
		$women_error_array[] = "mammography_date";
	}
	else{
		$_SESSION['select_mammography_date'] = $mammography_date;
	}
	
	$mammography_result=$_POST['select_mammography_result'];
	$_SESSION['select_mammography_result'] = $mammography_result;
	
	if(empty($women_error_array)){
		$last_update = date("Y-m-d H:i:s");
		
		if($menarche === ''){
			$menarche = -1;
		}
		if($gestations=== ''){
			$gestations= -1;
		}
		if($parity=== ''){
			$parity= -1;
		}
		if($abortions=== ''){
			$abortions= -1;
		}
		if($csections=== ''){
			$csections= -1;
		}
		if($ectopic=== ''){
			$ectopic= -1;
		}
		if($menopause=== ''){
			$menopause= -1;
		}
		
		$stmt = $con->prepare("INSERT INTO $OBGYN_table (id,menarche,	lmp,	cycles,gestations,parity,abortions,csections,ectopic,menopause,birthcontrol,mammography_date,mammography_result,last_update/*14*/) VALUES ('',?,?,?,?,?,?,?,?,?,?,?,?,?)");
		$stmt->bind_param("issiiiiiissss",$menarche,$lmp,$cycles,$gestations,$parity,$abortions,$csections,$ectopic,$menopause,$birthcontrol,$mammography_date,$mammography_result,$last_update);
		$stmt->execute();
		
		$_SESSION['select_menarche'] = "";
		$_SESSION['select_lmp'] = "";
		$_SESSION['select_cycles'] = "";
		$_SESSION['select_gestations'] = "";
		$_SESSION['select_parity'] = "";
		$_SESSION['select_abortions'] = "";
		$_SESSION['select_csections'] = "";
		$_SESSION['select_ectopic'] = "";
		$_SESSION['select_menopause'] = "";
		$_SESSION['select_birthcontrol'] = "";
		$_SESSION['select_mammography_date'] = "";
		$_SESSION['select_mammography_result'] = "";
	}
	
}

if(isset($_POST['patho_butt'])){
	$desc= $_POST['patho_desc'];
	$approx_date = $_POST['patho_date'];
	if(validateDateNoFormat($approx_date) || validateDate($approx_date)){
		$user_obj->insertPathologiesData($desc, $approx_date);
	}
	else{
		array_push($basic_error_array, "pathologies");
	}
}

if(isset($_POST['surgeries_butt'])){
	$desc = $_POST['surgeries_desc'];
	$approx_date = $_POST['surgeries_date'];
	if(validateDateNoFormat($approx_date) || validateDate($approx_date)){
		$user_obj->insertSurgeriesData($desc, $approx_date);
	}
	else{
		array_push($basic_error_array, "surgeries");
	}
}

if(isset($_POST['hereditary_butt'])){
	$dissease= $_POST['hereditary_diseases_input'];
	$relatives = $_POST['hereditary_relatives'];
	$lang = $_POST['lang'];
	$user_obj->insertHereditariesData($dissease, $relatives);
	
	if($_POST['searched_id_hereditary_diseases'] != ""){
		$percentage_of_change_per_user = 1;
		$id = $_POST['searched_id_hereditary_diseases'];
		
		$query =  mysqli_query($con, "SELECT * FROM hereditary_diseases WHERE $lang <> ''");
		
		$sum = $percentage_of_change_per_user;
		$fetched_array = [];
		
		while($arr = mysqli_fetch_array($query)){
			$fetched_array[] = $arr;
			$sum = $sum + $arr['percent'];
		}
		
		$stmt = $con->prepare("UPDATE hereditary_diseases SET percent=? WHERE id=?");
		$stmt->bind_param("si",$new_percent,$temp_id);
		for($i=0;$i<sizeof($fetched_array);$i++){
			$temp_id = $fetched_array[$i]['id'];
			if($temp_id == $id){
				$temp_percent = $fetched_array[$i]['percent'];
				$new_percent = ($temp_percent + 1)*100/$sum;
			}
			else{
				$temp_percent = $fetched_array[$i]['percent'];
				$new_percent = ($temp_percent)*100/$sum;
			}
			$stmt->execute();
		}
	}
	else{
		$lc_dissease = strtolower($dissease);
		$sql = "INSERT INTO hereditary_diseases (id, ".$lang.", percent) VALUES ('',?,0.00000)";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("s",$lc_dissease);
		$stmt->execute();
	}
	$stmt->close();
}

if(isset($_POST['medicines_butt'])){
	$medicine= $_POST['medicines2dosage_input'];
	$dosage = $_POST['medicines2dosage_dosage_input'];
	$lang = $_POST['lang'];
	$user_obj->insertMedicinesData($medicine, $dosage);
	
	if($_POST['searched_id_medicines2dosage'] != ""){
		$percentage_of_change_per_user = 1;
		$id = $_POST['searched_id_medicines2dosage'];
		
		$query =  mysqli_query($con, "SELECT * FROM medicines WHERE $lang <> ''");
		
		$sum = $percentage_of_change_per_user;
		$fetched_array = [];
		
		while($arr = mysqli_fetch_array($query)){
			$fetched_array[] = $arr;
			$sum = $sum + $arr['percent'];
		}
		
		$stmt = $con->prepare("UPDATE medicines SET percent=? WHERE id=?");
		$stmt->bind_param("si",$new_percent,$temp_id);
		for($i=0;$i<sizeof($fetched_array);$i++){
			$temp_id = $fetched_array[$i]['id'];
			if($temp_id == $id){
				$temp_percent = $fetched_array[$i]['percent'];
				$new_percent = ($temp_percent + 1)*100/$sum;
			}
			else{
				$temp_percent = $fetched_array[$i]['percent'];
				$new_percent = ($temp_percent)*100/$sum;
			}
			$stmt->execute();
		}
	}
	else{
		$lc_medicine = strtolower($medicine);
		$sql = "INSERT INTO medicines (id, ".$lang.", dosage , percent) VALUES ('',?,?,0.00000)";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("ss",$lc_medicine,$dosage);
		$stmt->execute();
	}
	$stmt->close();
}

if(isset($_POST['allergies_butt'])){
	$desc= $_POST['allergies_input'];
	$user_obj->insertAllergiesData($desc);
}
?>