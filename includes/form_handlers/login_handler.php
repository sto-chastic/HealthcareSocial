<?php 

$crypt = new Crypt();

if(isset($_POST['login_button'])){
	$_SESSION['no_errors'] = 0;
    $_SESSION['register_div']="login";
	$email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL); //email correct format

	$_SESSION['log_email'] = $email; //Store email into session
//	$passwrd = md5($_POST['log_passwrd']); //Can have same variable number as the register php does not execute
	
	$passwrd = $_POST['log_passwrd'];
	
// 	$stmt = $con->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
// 	$stmt->bind_param("ss", $email, $passwrd);
// 	$stmt->execute();

	
	$stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	
	
	$result = $stmt->get_result();
	$numrows = mysqli_num_rows($result);

	$arr = mysqli_fetch_array($result);
	
	$confirm_email_alert = 0;
	if(isset($_SESSION['confirm_email_alert'])){
		if($_SESSION['confirm_email_alert'] == 1){
			$confirm_email_alert = 1;
		}
	}
	
	if($numrows == 1 && $arr['confirmed_email'] == 1 && password_verify($passwrd, $arr['password'])){
		
		$username_e = $arr['username'];
		$username = $crypt->Decrypt($username_e);
		$user_closed = $arr['user_closed'];

		if($user_closed == 'yes'){
			$stmt = $con->prepare("UPDATE users SET user_closed='no' WHERE email=?");
			$stmt->bind_param("s", $email);
			$stmt->execute();
			$stmt->close();
			//$reopen_account = mysqli_query($con, "UPDATE users SET user_closed='no' WHERE email='$email'");
		}
		//$query = mysqli_query($con, "INSERT INTO `debug`(`id`, `text`) VALUES ('','Here1!')");
		$salt = "/|/e#rNap56?iC_";
		$messages_token = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
		$messages_token = md5(date("YmdHis") . $salt .md5($messages_token));
		
		$_SESSION['username'] = $username; //Session gets assigned
		$_SESSION['username_e'] = $username_e; //Session gets assigned
		$_SESSION['messages_token'] = $messages_token; //Session gets assigned
		
		$stmt = $con->prepare("UPDATE users SET `messages_token`=? WHERE username = ?");
		$stmt->bind_param("ss", $messages_token,$username_e);
		$stmt->execute();
		
		if(!isset($_POST['search_true'])){
			header("Location: index.php"); //redirects to index if it is logged in successfully.
			exit();
		}
	}
	elseif($numrows == 1 && $arr['confirmed_email'] != 1){
		array_push($error_array, "Email not yet confirmed.<br>");
	}
	else{
		array_push($error_array, "Incorect Email or Password.<br>");
	}
	$stmt->close();

}

?>