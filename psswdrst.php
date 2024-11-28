<?php
require 'config/config.php';
include("includes/classes/TxtReplace.php");
include("includes/classes/Crypt.php");
$crypt = new Crypt();
if(isset($_SESSION['lang'])){
    $lang = $_SESSION['lang'];
} 
else $lang = "es";

$userLoggedIn_e = "";

if(isset($_GET['e']) && isset($_GET['m']) && isset($_GET['h'])){
	$temp_email = $_GET['e'];
	$temp_mess_key = $_GET['m'];
	$temp_hash = $_GET['h'];
	
	$stmt = $con->prepare("SELECT `email`, `username` FROM users WHERE `messages_token`=?");
	
	$stmt->bind_param("s", $temp_mess_key);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	if(mysqli_num_rows($verification_query) == 1){
		$email_arr = mysqli_fetch_assoc($verification_query);
		$md5em = md5($email_arr['email']);
		
		//Verify
		
		$salt = "/|/e#rNap56?iC_";
		
		$hash =  $md5em . "neuRo/|/";
		$hash= md5($salt. md5($hash));
		if($temp_email == $md5em && $temp_hash == $hash){
			$succ_confirmed = 1;
			$userLoggedIn_e = $email_arr['username'];
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

if(isset($_POST['update_password']) && $succ_confirmed == 1){
	
	$new_password = strip_tags($_POST['new_password']);
	$new_password2 = strip_tags($_POST['new_password2']);
	
	
	if($new_password == $new_password2){
		if(strlen($new_password) > 30 || strlen($new_password) < 10){
			switch($lang){
				case("en"):
					$password_message = "Passwords need to be longer than 10 characters and shorter than 30.<br><br>";
					break;
				case("es"):
					$password_message = "Las contraseñas deben tener al menos 10 caracteres y menos de 30.<br><br>";
					break;
			}
		}
		else{
			$new_password_md5 = password_hash($new_password, PASSWORD_BCRYPT);
			
			$stmt = $con->prepare("UPDATE users SET password=? WHERE username=?");
			$stmt->bind_param("ss", $new_password_md5, $userLoggedIn_e);
			$stmt->execute();
			
			switch($lang){
				case("en"):
					$password_message = "Password has been updated.<br><br>";
					break;
				case("es"):
					$password_message = "La contraseña fue actualizada.<br><br>";
					break;
			}
		}
	}
	else {
		switch($lang){
			case("en"):
				$password_message = "Inserted new passwords do not match.<br><br>";
				break;
			case("es"):
				$password_message = "Las contraseñas no coinciden.<br><br>";
				break;
		}
	}
}
else
	$password_message = "";


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
			
			<div class="main_center_left" id="main_psswdrst">
             	<h1>
             	<?php 
             	switch($lang){
             	    case("en"):
             	        echo ($succ_confirmed)?"Please insert a new <br><h2>password </h2>": "This link is incorrect or it has expired.";
                        break;
             	    case("es"):
             	        echo ($succ_confirmed)?"Inserta una nueva <br><h2> contraseña.</h2>": "Este enlace es incorrecto o ya expiró.";
             	        break;
             	} 
                ?>
             	</h1>
             	
             	<?php if($succ_confirmed == 1){?>
             	
				<form style="display: flow-root;" action=<?php echo 'psswdrst.php?e='.$temp_email.'&m='.$temp_mess_key.'&h='.$temp_hash?> method="POST" style=" font-family: Coves-Bold; color: 000; background-color: 000;">
		        		<h3><?php 
		        	       switch($lang){
		                	       case("en"):
		                	           echo "New password";
		                	           break;
		                	       case("es"):
		                	           echo "Contraseña nueva";
		                	           break;
		                }?></h3>
		            <input type="password" name="new_password" value="" id="settings_input" >
		        		<h3> <?php 
		        	       switch($lang){
		                	       case("en"):
		                	           echo "Re-enter new password:";
		                	           break;
		                	       case("es"):
		                	           echo "Confirma la contraseña nueva";
		                	           break;
		                }?></h3>
		                
		            <input type="password" name="new_password2" id="settings_input">
		        
		        		<?php echo $password_message; ?>
		        
		        		<input type="submit" name="update_password" id="save_details" value="<?php 
			        	   switch($lang){
			        	       case("en"):
			        	           echo "Update Password";
			        	           break;
			        	       case("es"):
			        	           echo "Actualizar Contraseña";
			        	           break;
		            }?>" class="info settings_submit_buttons" >
		        	</form>
		        	
		        	<?php }?>
			</div>
			
		</div>
	</div>
</body>
</html>