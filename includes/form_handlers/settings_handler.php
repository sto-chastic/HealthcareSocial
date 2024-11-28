<?php 

$lang = $_SESSION['lang'];
//Update user details

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
	
	
	if(isset($_POST['update_prof_priv'])){
		
		$privacy_setting = $_POST['prof_privacy'];
		$privacy_setting= mysqli_real_escape_string($con,$privacy_setting);
		$privacy_setting= preg_replace("/[^\p{Xwd} ]+/u", "",$privacy_setting);
		
		switch ($privacy_setting){
			case "private":
				$settings->setSettingsValues_integers("profile_privacy", 1);
				$priv_selected = 1;
				break;
			case "public":
				$settings->setSettingsValues_integers("profile_privacy", 0);
				$priv_selected = 0;
				break;
		}
		
	}

	if(isset($_POST['update_details'])){
		$fname = strip_tags($_POST['first_name']);//Remove html tags
		$fname = mysqli_real_escape_string($con,$fname);
		$fname= preg_replace("/[^\p{Xwd} ]+/u", "",$fname);
		$first_name = $fname;
		/* $first_name = ucwords(strtolower($fname)); */ //Uppercase first letter

		$lname = strip_tags($_POST['last_name']);//Remove html tags
		$lname = mysqli_real_escape_string($con,$lname);
		$lname= preg_replace("/[^\p{Xwd} ]+/u", "",$lname);
		$last_name = ucwords(strtolower($lname)); //Uppercase first letter

		$email = strip_tags($_POST['email']);//Remove html tags
		$email = str_replace(' ', '', $email); //Remove spaces
		$email= preg_replace('/[^a-zA-Z0-9_.@-]/', '', $email);//Remove special characters (even inputed with ascii)
		//$email = preg_replace('/[^a-zA-Z0-9_.@-]/', '', $email);//Remove special characters (even inputed with ascii)


		if((!filter_var($email, FILTER_VALIDATE_EMAIL) === false) && (!preg_match('/[^a-zA-Z0-9_.@-]/', $email))){
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);

			$stmt = $con->prepare("SELECT * FROM users WHERE email=?");
			$stmt->bind_param("s", $email);
			$stmt->execute();
			$email_check = $stmt->get_result();
			
			$num_rows_email = mysqli_num_rows($email_check);
			
			$arrS = mysqli_fetch_array($email_check);
			$matched_user = $arrS['username'];

			if($matched_user == $userLoggedIn_e || $num_rows_email == 0){
				$stmt = $con->prepare("UPDATE users SET first_name=?, last_name=?, email=?, first_name_d=?, last_name_d=? WHERE username = ?");
				$fname_l = strtolower($first_name);
				$lname_l = strtolower($last_name);
				$stmt->bind_param("ssssss", $first_name, $last_name, $email, $fname_l, $lname_l, $userLoggedIn_e);
				$stmt->execute();
				switch($lang){
					case("en"):
						$message = "Details updated. <br><br>";
						break;
					case("es"):
						$message = "Datos actualizados. <br><br>";
						break;
				}
				
			}
			else{
				switch($lang){
					case("en"):
						$message = "That email is already in use. Try a different one. <br><br>";
						break;
					case("es"):
						$message = "El correo ya existe. Prueba otro. <br><br>";
						break;
				}
			}	
			$stmt->close();
		}

	}
	else
		$message = "";

// update password

	if(isset($_POST['update_password'])){

		$old_password = strip_tags($_POST['old_password']);
		$new_password = strip_tags($_POST['new_password']);
		$new_password2 = strip_tags($_POST['new_password2']);


		$stmt = $con->prepare("SELECT password FROM users WHERE username=?");
		$stmt->bind_param("s", $userLoggedIn_e);
		$stmt->execute();
		$password_query = $stmt->get_result();

		$row = mysqli_fetch_array($password_query);
		$db_password = $row['password'];

		if(password_hash($old_password, PASSWORD_BCRYPT) == $db_password){
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
		else{
			switch($lang){
				case("en"):
					$password_message = "Incorrect password.<br><br>";
					break;
				case("es"):
					$password_message = "Contraseña incorrecta.<br><br>";
					break;
			}
		}
			
	}
	else 
		$password_message = "";
	if(isset($_POST['update_language'])){
		    $new_lang = $txt_rep->prepareForSearchNoCommas($_POST['lang_pref_val']);
		    $curr_lang = $settings->getLang();
		    if ($new_lang=="en"){
		        if ($new_lang!=$curr_lang){
		            $settings->setLang("en");
		            $_SESSION['lang'] = "en";
		            $lang = "en";
		        }
		    } elseif ($new_lang=="es"){
		        if ($new_lang!=$curr_lang){
		            $settings->setLang("es");
		            $_SESSION['lang'] = "es";
		            $lang = "es";
		        }
		    }
		    header("Location: settings.php");
		}
	if(isset($_POST['close_account']))
		header("Location: close_account.php");

?>