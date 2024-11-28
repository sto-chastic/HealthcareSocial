<?php 
	include("../../config/config.php");
	include("../classes/TxtReplace.php");

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
	
	$info = $_REQUEST['info'];
	$type = $_REQUEST['type'];
	
	$txt_replace =  new TxtReplace();

	if($type == "symptoms"){
		$_SESSION['post_text'] = $txt_replace->entities($info);
		echo $_SESSION['post_text'];
	}
	elseif ($type == "medicines") {
		$_SESSION['post_text_medicines'] = $txt_replace->entities($info);
		echo $_SESSION['post_text_medicines'];
	}

?>