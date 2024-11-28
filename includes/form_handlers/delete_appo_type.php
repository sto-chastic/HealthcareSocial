<?php
	require '../../config/config.php';
	include('../classes/User.php');
	include('../classes/Calendar.php');
	include_once('../classes/TxtReplace.php');

	$userLoggedIn = "";
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
			$stmt->close();
			
			$lang = $_SESSION['lang'];
			
		}
		else{
			$is1 = $_SESSION['username'];
			$is3 = mysqli_num_rows($verification_query);
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: register.php");
		}

	}
	else{
		$is1 = $_SESSION['username'];
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: register.php");
	}

// 	$var1 = false;
// 	$var2 = false;

// 	if($userLoggedIn != "" && isset($_GET['id']) ){
// 		$id = $_GET['id'];
// 		$user = "";

// 		$stmt = $con->prepare("SELECT username FROM users WHERE username=?");
// 		$stmt->bind_param("s", $temp_user_e);

// 		$temp_user = $_GET['u'];
// 		$stmt->execute();
// 		$stmt->store_result();
// 		$numrows = $stmt->num_rows;

// 		if($numrows > 0 && $userLoggedIn == $temp_user){
// 			$var2 = true;
// 			$user = $userLoggedIn;
// 		}

// 		if($user == $userLoggedIn){
// 			$var1 = true;
// 		}

// 	}

// 	$stmt->close();

	if(isset($_GET['id'])){
		$id = $_GET['id'];
		$id = mysqli_escape_string($con, $id);
		
		$calendar = new Calendar($con, $userLoggedIn, $userLoggedIn_e);
		$num_rows = $calendar->getAppoDurationRowsNum();
		
		switch ($lang){
			
			case("en"):
				if($num_rows > 1){
					$calendar->removeAppoType($id);
					$html_to_echo = $calendar->getAppoDurationSettings();
					echo $html_to_echo;
				}
				else
					echo "Error. Cannot delete this appointment type because there must always be at least 1 type of appointment. Add another type in order to delete this one.";
				break;
				
			case("es"):
				if($num_rows > 1){
				    $calendar->removeAppoType($id);
				    $html_to_echo = $calendar->getAppoDurationSettings();
				    echo $html_to_echo;
					//echo "success";
				}
				else
					echo "Error. No se pudo borrar este tipo de cita porque siempre debe haber al menos 1 tipo de cita. Adiciona otro tipo de cita para eliminar esta.";
				break;
		}
	}
?>