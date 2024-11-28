<?php
require 'config/config.php';
include("includes/classes/TxtReplace.php");
include("includes/classes/Crypt.php");
$crypt = new Crypt();
if(isset($_SESSION['lang'])){
    $lang = $_SESSION['lang'];
} else $lang = "es";
if(isset($_GET['email']) && isset($_GET['hash'])){
	$temp_email = $_GET['email'];
	$temp_hash = $_GET['hash'];
	
	$stmt = $con->prepare("SELECT `email`, `username` FROM users WHERE `messages_token`=? AND `confirmed_email` != 1");
	
	$stmt->bind_param("s", $temp_hash);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	if(mysqli_num_rows($verification_query) == 1){
		$email_arr = mysqli_fetch_assoc($verification_query);
		$email = md5($email_arr['email']);
		
		$username = $email_arr['username'];
		if($temp_email == $email){
			$stmt = $con->prepare("UPDATE users SET confirmed_email = 1 WHERE username=?");
			$stmt->bind_param("s",$username);
			$stmt->execute();
			
			$succ_confirmed = 1;
			$_SESSION['confirm_email_alert'] = "";
			$_SESSION['no_errors'] = "";
		}
		else{
			$succ_confirmed = 0;
		}
	}
	else{
		$succ_confirmed = 0;
	}
}
else{
	$succ_confirmed = 0;
}


?>


<!DOCTYPE html>
<html>
<head>
	<title>Welcome to ConfiDr.</title>
	<link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="assets/js/register.js"></script>
</head>
<body>
	<div class="wrapper">
		<div class="wrapper_dark">
			<div class="top_register">
				<div class="logo_register" >
					<a href="register.php">
						<img src="assets/images/icons/logo2.png" id="logo_position">
					</a>
				</div>
			</div>
			
			<div class="main_center_left" style=" margin-left: 15%; margin-right: 15%;">
             	<p style=" background-color: rgba(69, 69, 69, 0.5);"><span class="title_center" style=" letter-spacing: 5px; font-size: 3vw;">
             	<?php 
             	switch($lang){
             	    case("en"):
             	        echo ($succ_confirmed)?"Email confirmed successfully, now you can login at ConfiDr. homepage": "This link is incorrect or the email has been already authenticated. Try to login or request a new link to be sent to your email.";
                        break;
             	    case("es"):
             	        echo ($succ_confirmed)?"Dirección de correo electrónico confirmada satisfactoriamente, ya puedes ingresar en la página de inicio de ConfiDr.": "Este enlace es incorrecto o el correo electrónico ya ha sido autenticado. Intenta ingresar o solicita que se envíe un nuevo enlace a tu correo electrónico.";
             	        break;
             	} 
                ?>
             	</span></p> 
			</div>
			
		</div>
	</div>
</body>
</html>