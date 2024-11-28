<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/Appointments_Master.php");
include("../classes/TxtReplace.php");

$crypt = new Crypt();
//DEPRECATED, no longer used
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

$aid = $_REQUEST['aid'];
$view_type = $_REQUEST['vt'];


$temp_user = $_SESSION['username'];
$temp_user_e = $_SESSION['username_e'];
$stmt = $con->prepare("SELECT email FROM users WHERE username=?");

$stmt->bind_param("s", $temp_user_e);
$stmt->execute();
$verification_query = $stmt->get_result();

if(mysqli_num_rows($verification_query) == 1){
	$userLoggedIn = $temp_user;
	$userLoggedIn_e = $temp_user_e;//Retrieves username
	$txtreplace = new TxtReplace();
	$stmt->close();
}
else{
	$userLoggedIn = "";
	echo "User not found.";
	$stmt->close();
}

$appointments_master = new Appointments_Master($con, $userLoggedIn, $userLoggedIn_e);

if($view_type == 1){
	$id = $appointments_master->getPatient_view_Doctor($aid);
	$id_obj = new User($con, $id, $crypt->EncryptU($id));
	$name_ini = $id_obj->getFirstNameShort(15);
	$last_name_s = $id_obj->getLastNameShort(15);
	$user_str = "<br> <a href='" . $id . "'>" . $txtreplace->entities($name_ini) . "<br>" . $txtreplace->entities($last_name_s) ."</a><br><b>Doctor</b>"; // cambios victor ------- se cambio el orden de Doctor
	if($appointments_master->patient_view_IsConfirmed($aid))
		$confirmed_str = " <span class='text_passive'>Confirmed</span>";
	else{
		$confirmed_str = " <span class='text_alert'>Requires additional info <a href='javascript:void(0);' " . 
<<<EOS
		 onclick='jumpPatAppoViewer("$aid")'>here</a>.</span>
EOS;
	}  
}
elseif($view_type == 2){
	$id = $appointments_master->getDoctor_view_Patient($aid);
	$id_obj = new User($con, $id, $crypt->EncryptU($id));
	$name_ini = $id_obj->getFirstNameShort(15);
	$last_name_s = $id_obj->getLastNameShort(15);
	$user_str = " <a href='" . $id . "'>" . $txtreplace->entities($name_ini) . " " . $txtreplace->entities($last_name_s) ."</a><br><b>Patient</b>"; // cambios victor ------- se cambio el orden de Patients
	$confirmed_str = "";
}
else
	header("Location: 404.php"); 

// cambios victor ---- en el siguiente $str se eleminaron las clases de los siguientes divs
$str = " 
		<div> 				
			<a href='javascript:void(0);' onclick='cancelConsultAppointment();'>
				<div class='delete_element' id='del_id_2' >X</div>
			</a>
			
			<div class='appointment_window_view_top'>
				<div class='post_profile_pic'  >
					<a href='" . $id . "'>
						<img src='" . $txtreplace->entities($id_obj->getProfilePicFast()) . "' style='width:50px; height:50px;'> <!--cambios victor-------se elminaron elementos de estilo en la imagen-->
					</a>
				</div>
				<div class='patient_data'>
					<p>" . 
						$user_str .
					$confirmed_str .
					"</p>
				</div>
			</div>	
		</div>";
		// cambios victor ------- todos los elementos del los if quedan con clase deep_pink
if($view_type == 2){
	$str .=
			<<<EOS
				<a href="javascript:void(0);" onclick="jumpPatAppoViewer('$aid');">
					<div class='deep_pink small_butt'>More Details</div>
				</a>
EOS;
}
elseif($view_type == 1){
	$str .=
			<<<EOS
				<a href="javascript:void(0);" onclick="jumpAppoDashboard('$aid');">
					<div class='deep_pink small_butt'>Dashboard!</div>
				</a>
EOS;
}

echo $str;
?>