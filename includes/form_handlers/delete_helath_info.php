<?php
	require '../../config/config.php';
	include('../classes/User.php');
	include('../classes/TimeStamp.php');
	
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
			$userLoggedIn_e = $temp_user_e; //Retrieves username
			$users_details_query = $verification_query;
			$user = mysqli_fetch_array($users_details_query);
			$stmt->close();
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: ../../register.php");
		}

	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
	}

// 	$var1 = false;
// 	$var2 = false;

// 	if($userLoggedIn != "" && isset($_GET['id']) && isset($_GET['u']) && isset($_GET['t'])){
// 		$id = $_GET['id'];
// 		$user = "";

// 		$stmt = $con->prepare("SELECT username FROM users WHERE username=?");
// 		$stmt->bind_param("s", $temp_user_e);

// 		$temp_user = $_GET['u'];
// 		$stmt->execute();
// 		$stmt->store_result();
// 		$numrows = $stmt->num_rows;

// 		if($numrows > 0){
// 			$var2 = true;
// 			$user = $_GET['u'];
// 		}

// 		if($user == $userLoggedIn){
// 			$var1 = true;
// 		}
// 		$type = $_GET['t'];
// 	}
// 	$stmt->close();

	if(isset($_GET['id']) && isset($_GET['t'])){
		$id = $_GET['id'];
		$type = $_GET['t'];
		
		$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
		
		switch($type){
			case 'pathologies':
				$table = $user_obj->getPathologiesTable();
				break;
			case 'surgical_trauma':
				$table = $user_obj->getSurgeriesTable();
				break;
			case 'hereditary':
				$table = $user_obj->getHereditariesTable();
				break;
			case 'medicines':
				$table = $user_obj->getMedicinesTable();
				break;
			case 'allergies':
				$table = $user_obj->getAllergiesTable();
				break;
			default:
				$table = "";
				$type = "";
		}
		
		$stmt = $con->prepare("DELETE FROM $table WHERE id=? ");
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$stmt->close();
		
		switch($type){
			case 'pathologies':
				$str = $user_obj->getPathologiesData(date("Y-m-d H:i:s"));
				break;
			case 'surgical_trauma':
				$str = $user_obj->getSurgeriesData(date("Y-m-d H:i:s"));
				break;
			case 'hereditary':
				$str = $user_obj->getHereditariesData();
				break;
			case 'medicines':
				$str = $user_obj->getMedicinesData();
				break;
			case 'allergies':
				$str = $user_obj->getAllergiesData();
				break;
			default:
				$str = "";
		}
		echo $str;
	}
?>