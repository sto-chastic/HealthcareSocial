<?php
require 'config/config.php';
include ("includes/classes/TxtReplace.php");
include("includes/classes/Crypt.php");
include ("includes/classes/Email_Creator.php");
include ("includes/classes/SearchNMap.php");

require 'includes/form_handlers/register_handler.php';
require 'includes/form_handlers/login_handler.php';
require 'includes/form_handlers/docsearch_handler.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

//setting langs

if(isset($_SESSION['lang'])){
	$lang = $_SESSION['lang'];	
}
else{
    $known_langs = array('en','es');
    $user_pref_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $found_lang = false;
    foreach($user_pref_langs as $idx => $u_lang) {
        $u_lang = substr($u_lang, 0, 2);
        if (in_array($u_lang, $known_langs)) {
            $lang = $u_lang;
            $_SESSION['lang']=$lang;
            $found_lang = true;
            break;
        }
    }
    if(!$found_lang){
        $lang = "es";
        $_SESSION['lang'] = "es";
    }
}

// if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
// 	if ($_SERVER['PHP_AUTH_USER'] != 'beta_tester' ||
// 			$_SERVER['PHP_AUTH_PW'] != 'neuronaptic2017') {

// 				header('WWW-Authenticate: Basic realm="Protected area"');
// 				header('HTTP/1.0 401 Unauthorized');

// 				die('Login failed!');
// 			}
// }


// $valid_passwords = array ("beta_tester_n" => "/|/e#uroNaptic2017man/#");
// $valid_users = array_keys($valid_passwords);

// //TODO: Uncomment following line for deployment (ONLY ON SOME SERVERS, NOT AWS)
// //list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':' , base64_decode(substr($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 6)));

// $user = $_SERVER['PHP_AUTH_USER'];
// $pass = $_SERVER['PHP_AUTH_PW'];

// $validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

// if (!$validated) {
// 	header('WWW-Authenticate: Basic realm="My Realm"');
// 	header('HTTP/1.0 401 Unauthorized');
// 	die ("Not authorized");
// }

if(isset($_POST['reset_psswrd_button'])){
	$temp_email = $_SESSION ['log_email'];
	
	if(!preg_match('/[^a-zA-Z0-9_.@-]/', $temp_email)){
		$stmt = $con->prepare("SELECT messages_token,email FROM users WHERE email = ?");
		$stmt->bind_param("s",$temp_email);
		$stmt->execute();
		
		$result = $stmt->get_result();
		
		if(mysqli_num_rows($result) == 1){
			$res=mysqli_fetch_assoc($result);
			$salt = "/|/e#rNap56?iC_";
			$em = $res['email'];
			$md5em =  md5($res['email']);
			
			$hash =  $md5em . "neuRo/|/";
			$hash= md5($salt. md5($hash));
			
			$messages_token = $res['messages_token'];
			
			$to = $temp_email; // Send email to our user
			switch ($lang){
				
				case("en"):
					$subject = 'ConfiDr. Password Reset'; // Give the email a subject
					$message = '
							
				It seems you are having trouble logging in. Please click the link below to reset your password. If you did not require a password change, then ignore this email.
							
				------------------------
				http://www.confidr.com/psswdrst.php?e='.$md5em.'&m='.$messages_token.'&h='.$hash.'
				Please click this link to change your password.
				------------------------
				';
					
					$url = "http://www.confidr.com/psswdrst.php?e={$md5em}&m={$messages_token}&h={$hash}";
					$email_creator = new Email_Creator();
					
					$title = "ConfiDr. Password Reset";
					$greet = "It seems you are having trouble logging in.";
					$confirmation_message = "Please click the button below to reset your password.";
					$button = "Reset Password";
					$confirmation_message_2 = 'If you did not require a password change, then ignore this email';
					
					$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
							$button, $url, $confirmation_message_2);
					break;
					
				case("es"):
					$subject = 'ConfiDr. Reestablecer Contraseña'; // Give the email a subject
					$message = '
							
				Parece que tienes problemas para acceder a tu cuenta. Has click en el siguiente link para reestablecer tu contraseña. Si tu no pediste un cambio de contraseña, ignora este correo.
							
				------------------------
				http://www.confidr.com/psswdrst.php?e='.$md5em.'&m='.$messages_token.'&h='.$hash.'
				Has click en este link para reestablecer tu constraseña.
				------------------------
				';
					
					$url = "http://www.confidr.com/psswdrst.php?e={$md5em}&m={$messages_token}&h={$hash}";
					$email_creator = new Email_Creator();
					
					$title = "ConfiDr. Reestablecer Contraseña";
					$greet = "Parece que tienes problemas para acceder a tu cuenta.";
					$confirmation_message = "Has click en el siguiente botón para reestablecer tu contraseña";
					$button = "Reestablecer Contraseña";
					$confirmation_message_2 = 'Si tu no pediste un cambio de contraseña, ignora este correo.';
					
					$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
							$button, $url, $confirmation_message_2);
					break;
			}
			
			//TODO:Comment for deployment, uncomment for test
			//echo "http://www.confidr.com/psswdrst.php?e={$md5em}&m={$messages_token}&h={$hash}";
			
			$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
			
			try {
				//Server settings
				$mail->CharSet = 'UTF-8';
				$mail->SMTPDebug = 0;                                 // Enable verbose debug output
				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = 'smtp.1and1.com';                       // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				
				$mail->Username = 'support-confidr@jimenezd.com';                 // SMTP username
				$mail->Password = '/|/euRo#$!Na/pti_C#2017';                           // SMTP password
				
				$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
				$mail->Port = 587;                                    // TCP port to connect to
				
				//Recipients
				$mail->setFrom('support-confidr@jimenezd.com', 'Support-ConfiDr');
				//TODO: Comentar la siguiente linea para permitir que los correos de confirmación lleguen a los usuarios y no a confidr para moderación.
				//$mail->addAddress($to, $txt_rep->entities($fname . " " . $lname));     // Add a recipient
				$mail->addAddress("neuronapticsas@gmail.com", $txt_rep->entities($fname . " " . $lname));     // Add a recipient
				$mail->addReplyTo('support-confidr@jimenezd.com', 'Support-ConfiDr');
				
				//Content
				$mail->isHTML(true);                                  // Set email format to HTML
				$mail->Subject = $subject;
				$mail->Body    = $message_html;
				$mail->AltBody = $message;
				
				$mail->send();
				//echo 'Message has been sent';
			} catch (Exception $e) {
				//echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
			}
			
// 			switch ($lang){
// 				case "en":
// 					echo "<div class='fading_message'>An email was sent to you. It shall soon arrive to your <b>'Inbox'</b>, or sometimes to your <b>'Junk'</b> (Spam) folder.</div>";
// 					break;
// 				case "es":
// 					echo "<div class='fading_message'>Un mensaje de confirmación fue enviado a tu correo. Este debe llegar pronto a tu <b>'Bandeja de Entrada'</b> (Inbox), o a veces a tu carpeta de <b>'Correo No Deseado'</b> (Spam).</div>";
// 					break;
// 			}

			switch ($lang){
			    case "en":
			        echo "<div class='fading_message'>ConfiDr. will contact you soon. An email will soon arrive to your <b>'Inbox'</b>, or sometimes to your <b>'Junk'</b> (Spam) folder.
                            <form action='register.php' method='POST'>
                                <input  id='but_fading_message' type='submit' name='accept_banner_button' class='accept_banner_button' value='Accept' style='cursor: pointer;'>
                            </form>
                        </div>";
			        break;
			    case "es":
			        echo "<div class='fading_message'>ConfiDr. se pondrá en contacto contigo a través de tu correo. Este debe llegar pronto a tu <b>'Bandeja de Entrada'</b> (Inbox), o a veces a tu carpeta de <b>'Correo No Deseado'</b> (Spam).
                            <form action='register.php' method='POST'>
                                <input id='but_fading_message' type='submit' name='accept_banner_button' class='accept_banner_button' value='Aceptar' style='cursor: pointer;'>
                            </form>
                        </div>";
			        break;
			}
			//echo $url;
			//TODO: change message
		}
	}
}

if(isset($_POST['accept_banner_button'])){
    $_SESSION['no_errors'] = "";
    $_SESSION['log_email'] = "";
}

if(isset($_POST['send_link_button'])){
	$temp_email = $_SESSION ['log_email'];
	$salt = "/|/e#rNap56?iC_";
	$messages_token = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);
	$messages_token = md5(date("YmdHis") . $salt. md5($messages_token));
	
	$stmt = $con->prepare("UPDATE users SET messages_token = ? WHERE email = ?");
	$stmt->bind_param("ss",$messages_token,$temp_email);
	$stmt->execute();
	
	$md5email = md5($temp_email);
	
	$to      = $temp_email; // Send email to our user
	switch ($lang){
		
		case("en"):
			$subject = 'ConfiDr. Email Verification'; // Give the email a subject
			$message = '
					
				Welcome to ConfiDr! The first social network joining doctors and patients.
				Your account has been created, you can activate it by clicking in the followng link:
					
				------------------------
				http://www.confidr.com/verify.php?email='.$md5email.'&hash='.$messages_token.'
				Please click this link to activate your account
				------------------------
					
				IMPORTANT: If you scheduled an appointment without having activated your account, you have to:
             		log in, go to calendar, select the appointment, click in "More Details", and then click "Confirm Appointment".<br>
					REMEMBER you only have 2 hours since you schedule the appointment to do this, otherwise your appointment would be
					deleted automatically.	
				';
			
			$url = "http://www.confidr.com/verify.php?email={$md5email}&hash={$messages_token}";
			$email_creator = new Email_Creator();
			
			$title = "Confirmation Email";
			$greet = "Welcome to ConfiDr! The first social network joining doctors and patients.";
			$confirmation_message = "Your account has been created, you can activate it by clicking in the followng link:";
			$button = "Activate Account";
			$confirmation_message_2 = 'IMPORTANT: If you scheduled an appointment without having activated your account, you have to:
             		log in, go to calendar, select the appointment, click in "More Details", and then click "Confirm Appointment".<br>
					REMEMBER you only have 2 hours since you schedule the appointment to do this, otherwise your appointment would be
					<b>deleted automatically</b>.';
			
			$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
					$button, $url, $confirmation_message_2);
			break;
			
		case("es"):
			$subject = 'ConfiDr. Verificación de Correo'; // Give the email a subject
			$message = '
					
				Bienvenido a ConfiDr! La primera red social que une doctores y pacientes.
				Tu cuenta ha sido creada, ahora puedes ingresar entrando al siguiente link:
					
				------------------------
				http://www.confidr.com/verify.php?email='.$md5email.'&hash='.$messages_token.'
				Has click en este link para activar tu cuenta.
				------------------------
						
				IMPORTANTE: Si agendaste una cita sin haber verificado tu correo aun,
				ahora debes: iniciar sesión, ir a tu calendario, buscar la cita agendada,
				hacer click en "Información de Cita", y en la siguiente página hacer
				click en "Confirmar".
				RECUERDA que tienes 2 horas para hacer esto desde el momento en que
				reservaste la cita, de lo contrario esta será eliminada automáticamente.
						
				';
			
			$url = "http://www.confidr.com/verify.php?email={$md5email}&hash={$messages_token}";
			$email_creator = new Email_Creator();
			
			$title = "Email Confirmación";
			$greet = "¡Bienvenido a ConfiDr.! La primera red social que une doctores y pacientes.";
			$confirmation_message = "Tu cuenta ha sido creada, ahora sólo debes confirmar tu correo haciendo click aquí:";
			$button = "Confirmar Correo";
			$confirmation_message_2 = 'IMPORTANTE: Si agendaste una cita sin haber verificado tu correo aún, debes:
             		iniciar sesión, ir a tu calendario, ubicar la cita agendada,
					hacer click en "Más Detalles", y en la siguiente página hacer 
					click en "Confirmar".<br> 
					RECUERDA que tienes 2 horas para hacer esto desde el momento en que
					reservaste la cita, de lo contrario esta será <b>eliminada automáticamente</b>.';
			
			$message_html = $email_creator->confirmation_email($title, $greet, $confirmation_message,
					$button, $url, $confirmation_message_2);
			break;
	}
	
	//TODO:Comment for deployment, uncomment for test
	//echo "REMOVE: localhost/confidr/verify.php?email=".$md5email."&hash=".$messages_token;
	
	$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
	
	try {
		//Server settings
		$mail->CharSet = 'UTF-8';
		$mail->SMTPDebug = 0;                                 // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.1and1.com';                       // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		
		$mail->Username = 'support-confidr@jimenezd.com';                 // SMTP username
		$mail->Password = '/|/euRo#$!Na/pti_C#2017';                           // SMTP password
		
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to
		
		//Recipients
		$mail->setFrom('support-confidr@jimenezd.com', 'Support-ConfiDr');
		$mail->addAddress($to, $txt_rep->entities($fname . " " . $lname));     // Add a recipient
		$mail->addReplyTo('support-confidr@jimenezd.com', 'Support-ConfiDr');

		//Content
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $message_html;
		$mail->AltBody = $message;
		
		$mail->send();
		//echo 'Message has been sent';
	} catch (Exception $e) {
		//echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
	}
	//TODO: Change message
// 	switch ($lang){
// 		case "en":
// 			echo "<div class='fading_message'>A confirmation email was sent to you. It shall arrive soon to your <b>'Inbox'</b>, or sometimes to your <b>'Junk'</b> (Spam) folder.</div>";
// 			break;
// 		case "es":
// 			echo "<div class='fading_message'>Un mensaje de confirmación fue enviado a tu correo. Este debe llegar pronto a tu <b>'Bandeja de Entrada'</b> (Inbox), o a veces a tu carpeta de <b>'Correo No Deseado'</b> (Spam).</div>";
// 			break;
// 	}

	switch ($lang){
	    case "en":
	        echo "<div class='fading_message'>ConfiDr. will contact you soon. An email will soon arrive to your <b>'Inbox'</b>, or sometimes to your <b>'Junk'</b> (Spam) folder.
                            <form action='register.php' method='POST'>
                                <input id='but_fading_message'type='submit' name='accept_banner_button' class='accept_banner_button' value='Accept' style='cursor: pointer;'>
                            </form>
                        </div>";
	        break;
	    case "es":
	        echo "<div class='fading_message'>ConfiDr. se pondrá en contacto contigo a través de tu correo. Este debe llegar pronto a tu <b>'Bandeja de Entrada'</b> (Inbox), o a veces a tu carpeta de <b>'Correo No Deseado'</b> (Spam).
                            <form action='register.php' method='POST'>
                                <input id='but_fading_message' type='submit' name='accept_banner_button' class='accept_banner_button' value='Aceptar' style='cursor: pointer;'>
                            </form>
                        </div>";
	        break;
	}
	
}

if(isset($_POST['lang'])) {
	$temp_lang = $_POST['lang'];
	switch ($temp_lang){
		
		case("en"):
			$_SESSION['lang'] = "en";
			break;
			
		case("es"):
			$_SESSION['lang'] = "es";
			break;
		default:
			$_SESSION['lang'] = "es";
	}
	$lang = $_SESSION['lang'];
	exit();
}

if(!isset($_SESSION['register_div'])){
    $_SESSION['register_div'] = "home";
}

if(isset($_SESSION['no_errors'])){
	if($_SESSION['no_errors'] == 1){				                    			switch ($lang){
// 			case "en":
// 				echo "<div class='fading_message'>A confirmation email was sent to you. It shall soon arrive to your <b>'Inbox'</b>, or sometimes to your <b>'Junk'</b> (Spam) folder.</div>";
// 				break;
// 			case "es":
// 				echo "<div class='fading_message'>Un mensaje de confirmación fue enviado a tu correo. Este debe llegar pronto a tu <b>'Bandeja de Entrada'</b> (Inbox), o a veces a tu carpeta de <b>'Correo No Deseado'</b> (Spam).</div>";
// 				break;
	    case "en":
	        echo "<div class='fading_message'>ConfiDr. will contact you soon. An email will soon arrive to your <b>'Inbox'</b>, or sometimes to your <b>'Junk'</b> (Spam) folder.
                            <form action='register.php' method='POST'>
                                <input id='but_fading_message' type='submit' name='accept_banner_button' class='accept_banner_button' value='Accept' style='cursor: pointer;'>
                            </form>
                        </div>";
	        break;
	    case "es":
	        echo "<div class='fading_message'>ConfiDr. se pondrá en contacto contigo a través de tu correo. Este debe llegar pronto a tu <b>'Bandeja de Entrada'</b> (Inbox), o a veces a tu carpeta de <b>'Correo No Deseado'</b> (Spam).
                            <form action='register.php' method='POST'>
                                <input id='but_fading_message' type='submit' name='accept_banner_button' class='accept_banner_button' value='Aceptar' style='cursor: pointer;'>
                            </form>
                        </div>";
	        break;
	    //TODO change message
		}
		

	}
}
?>
<script>
	let currentDiv = '<?php echo $_SESSION['register_div']?>';
</script>

<!DOCTYPE html>
<html>
<head>
<title>
	<?php
	
	switch ($lang){
	    
	    case("en"):
	        echo "Welcome to ConfiDr!";
	        break;
	        
	    case("es"):
	        echo "Bienvenido a ConfiDr!";
	        break;
	}

    ?>
</title>
<meta name = "description" content="
<?php 
switch($lang){
    case("en"):
        echo "ConfiDr. is the first social network for doctors and patients in the world. Here you will find information about your doctors, can request medical appointments online, write to them via messaging among other functions. Join now!";
        break;
    case("es"):
        echo "ConfiDr. es la primera red social para medicos y pacientes en el mundo. Aqui encontrarás información sobre tus médicos, podrás pedir citas médicas, escribirles vía mensaje entre otras funciones. Únete ya!";
}
?>">
<link rel="stylesheet" type="text/css" href="assets/css/register_style.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="assets/js/register.js"></script>


 <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">

</head>
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-118239678-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-118239678-1');
</script>

<body class="style-2">


	<?php
	$txt_rep = new TxtReplace ();
	$insu_err_arr1 = [ ];
	?>
    <div class="wrapper" >
		<div class="wrapper_dark">
			<div class="top_register">
				<div class="logo_register">
					<div class="logo_register">
						<img src="assets/images/icons/logo2.png" id="logo_position">
					</div>
				</div>
				<div class="logo_register_right">
					
					<ul class="login_register">
						<li><a href="javascript:void(0);" id="top_home"> 
						<?php
                            	
                            	switch ($lang){
                            	    
                            	    case("en"):
                            	        echo "Home";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "Inicio";
                            	        break;
                            	}
                        ?>
						</a></li>
						<li><a href="javascript:void(0);" id="top_register_patients">
						<?php
                            	
                            	switch ($lang){
                            	    
                            	    case("en"):
                            	        echo "For Patients";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "Para Pacientes";
                            	        break;
                            	}
                        ?>
						</a></li>
						<li><a href="javascript:void(0);" id="top_register_doctors">
						<?php
                            	
                            	switch ($lang){
                            	    
                            	    case("en"):
                            	        echo "For Doctors";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "Para Doctores";
                            	        break;
                            	}
                        ?> 
						</a></li>
						<li>
							<div class="login_button" href="javascript:void(0);"
								id="top_login">
								<a> 
								<?php
                                    	
                                    	switch ($lang){
                                    	    
                                    	    case("en"):
                                    	        echo "Login";
                                    	        break;
                                    	        
                                    	    case("es"):
                                    	        echo "Iniciar Sesión";
                                    	        break;
                                    	}
                                ?>	
								<img src="assets/images/icons/login.png"
									id="logo_login">
								</a>
							</div>
						</li>
						<li>
						<div class= "lang_deco">
							<select name="lang_select" id="lang_select"> 
								<?php 
									if(isset($_SESSION['lang'])){
									
										switch ($_SESSION['lang']){
											
											case("en"):
												echo '			
													<option value="en" selected>EN</option>
													<option value="es">ES </option>';
												break;
											case("es"):
												echo '
													<option value="en">EN</option>
													<option value="es" selected>ES </option>';
												break;
										}
									}
									else {
									    if(isset($lang)){
									        switch($lang){
									            case("en"):
									                echo '
													<option value="en" selected>EN</option>
													<option value="es">ES </option>';
									                break;
									            case("es"):
									                echo '
													<option value="en">EN</option>
													<option value="es" selected>ES </option>';
									                break;
									        }
									    }
									}
// 									else{
// 										$_SESSION['lang'] = "es";
// 										echo '
// 											<option value="en">EN</option>
// 											<option value="es" selected>ES</option>';
// 									}
								?>
							</select>
						</div>		
						</li>
					</ul>
					
					
					<button onclick="menuRegister()" class="login_settings"> </button>
					<ul class="login_display" >
						<li><a href="javascript:void(0);" id="top_home_mobile"> 
						<?php
                            	
                            	switch ($lang){
                            	    
                            	    case("en"):
                            	        echo "Home";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "Inicio";
                            	        break;
                            	}
                        ?>
						</a></li>
						<li><a href="javascript:void(0);" id="top_register_patients_mobile">
						<?php
                            	
                            	switch ($lang){
                            	    
                            	    case("en"):
                            	        echo "For Patients";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "Para Pacientes";
                            	        break;
                            	}
                        ?>
						</a></li>
						<li><a href="javascript:void(0);" id="top_register_doctors_mobile">
						<?php
                            	
                            	switch ($lang){
                            	    
                            	    case("en"):
                            	        echo "For Doctors";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "Para Doctores";
                            	        break;
                            	}
                        ?> 
						</a></li>
						<li>
							<div class="login_button" href="javascript:void(0);"
								id="top_login_mobile">
								<a> 
								<?php
                                    	
                                    	switch ($lang){
                                    	    
                                    	    case("en"):
                                    	        echo "Login";
                                    	        break;
                                    	        
                                    	    case("es"):
                                    	        echo "Iniciar Sesión";
                                    	        break;
                                    	}
                                ?>	
								<img src="assets/images/icons/loginmobile.png"
									id="logo_login_mobile">
								</a>
							</div>
						</li>
					</ul>
				</div>

				<script>
                	$(document).ready(function(){
						$('ul li a').click(function(){
							$('li a').removeClass("active");
						    $(this).addClass("active");
							});
						});
			    </script>
			</div>
			<div class="top_register_line"></div>
			<div class="register_background">
				<div class="decoration_pink"></div>

				<div class="main_center">
					<div class="main_center_left">
						<p><?php
                            	
                            	switch ($lang){
                            	    case("en"):
                            	        echo " &nbspWelcome to <br>  <span class='title_center'>Confidr</span>";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo " &nbspBienvenido a <br> <span class='title_center'> Confidr</span>";
                            	        break;
                            	}
                        ?>
						</p>
					</div>
					<div class="main_center_right">
						<h2>
						<?php
                            	
                            	switch ($lang){
                            	    case("en"):
                            	        echo "The first Social Network for patients and doctors in the world";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "La primera red social para pacientes y doctores en el mundo";
                            	        break;
                            	}
                        ?>
						</h2>
						<div class="search_register">
							
							<input type="button" name="search_4a_doctor"
								id="search_4a_doctor" value="<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Search for a doctor";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Busca un doctor";
                                                    	        break;
                                                    	}
                                                ?>"
								href="javascript:void(0);">
								
						</div>
					</div>
				</div>
				<div class="decoration_blue"></div>
			</div>
			<div class="bottom_register">
				<div class="bottom_register_left">
					<ul>
						<li class="first">Confidr</li>
						<li class="second">
						<?php
                            	
                            	switch ($lang){
                            	    case("en"):
                            	        echo "developed by Neuronaptic Technologies";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "desarrollado por Neuronaptic Technologies";
                            	        break;
                            	}
                        ?>
                        </li>
						<li class="third">Bogota</li>
						<li class="quarter">Colombia</li>
						<li class="fifth">TEL:+(57)312 412 57 15</li>
					</ul>
				</div>
			</div>
			<div class="for_search">
				<div class=search>
					<div class="title_search">
						<p>
							<?php
                                	
                                	switch ($lang){
                                	    case("en"):
                                	        echo "<span class='caps_confidr'> Find </span>available <br>
							                     doctors near your location";
                                	        break;
                                	        
                                	    case("es"):
                                	        echo "<span class='caps_confidr'> Encuentra </span> doctores <br>
							                     disponibles cerca de ti";
                                	        break;
                                	}
                            ?>
						</p>
					</div>
					<div class="search_doctor">
						<form action="register.php" method="POST">
							<ul class="list_search">
								<li><ul class="list_search_sub">
										<li><p>
											<?php
                                                	
                                                	switch ($lang){
                                                	    case("en"):
                                                	        echo "Type of doctor <br> or symptom";
                                                	        break;
                                                	        
                                                	    case("es"):
                                                	        echo "Tipo de doctor <br> o síntoma";
                                                	        break;
                                                	}
                                            ?>
												
											</p></li>
										<li><input type="text" name="query" id="search_specialist"
											placeholder="<?php
                                                        	
                                                        	switch ($lang){
                                                        	    case("en"):
                                                        	        echo "ex:gastroenterology";
                                                        	        break;
                                                        	        
                                                        	    case("es"):
                                                        	        echo "ej:gastroenterología";
                                                        	        break;
                                                        	}
                                                    ?>" required autocomplete="OFF"></li>
									</ul></li>
								<li>
									<ul class="list_search_sub">
										<li><p>
												<br>
												<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Date";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Fecha";
                                                    	        break;
                                                    	}
                                                ?>
											</p></li>
										<li><input type="text" id="datepicker" name="date_query"
											readonly="true" required></li>
									</ul>
								</li>
								<li>
									<ul class="list_search_sub">
										<li><p>
												<br>
												<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Location";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Lugar";
                                                    	        break;
                                                    	}
                                                ?>
											</p></li>
										<li><input type="text" id="search_location"
											name="search_location_name" value="Bogotá, D.C."
											autocomplete="off" required>
											<div class="style-2" id="docsearch_location_reg"></div> <input type="hidden"
											id="ds_city_code" name="ds_city_code" value="CO001"> <input
											type="hidden" id="ds_lat" name="pos[lat]"> <input
											type="hidden" id="ds_lng" name="pos[lng]"></li>
									</ul>
								</li>
								<li>
									<ul class="list_search_sub">
										<li><p>
												<br>
												<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Insurance";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Seguro";
                                                    	        break;
                                                    	}
                                                ?>
											</p></li>
										<li>
										<input type="text" id="search_insurance"
											name="searched_insurance1" placeholder="<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Ex: Uninsured";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Ej: Particular";
                                                    	        break;
                                                    	}
                                                ?>"
											autocomplete="off" required>
											<div class="style-2" id="docsearch_insurance_reg"></div>
											<input type="hidden" id="ds_ins_code" name="ds_ins_code" >
										</li>
									</ul>
								</li>
								<li>
									<ul class="list_search_sub" id="ul_radius">
										<li><p>
												<br>
												<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Radius";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Radio";
                                                    	        break;
                                                    	}
                                                ?>
											</p></li>
										<li style=" height: 8vh;"><input type="range" id="search_radius" name="radius"
											min="0" max="50" step="10"> <!--         		                 	<div class="search_radius_info" id="search_radius_info"> -->
											<span class="current_search_radius"
											id="current_search_radius"></span> <!--         		                 	</div> -->
										</li>
										<li><span class="caps_meter">0Km<span
											 style="float:right;">50Km </span></span>
										
										</li>
									
									</ul>
								</li>
								<li style="width: 3.2vw;"><input type="submit" id="go" name="go_search" value="<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Go";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Ir";
                                                    	        break;
                                                    	}
                                ?>"></li>
							</ul>
						</form>
					</div>
				</div>
			</div>
			<div class="for_doctor">
				<div class="bullets_info">

					<div class="bullets_title">
						<h2><?php
                                	
                                	switch ($lang){
                                	    case("en"):
                                	        echo "Get</h2><br>
						                      <h1>
							                  more patients and <b>optimize</b> your consults.
                                              </h1>";
                                	        break;
                                	        
                                	    case("es"):
                                	        echo "Atiende</h2><br>
						                      <h1>
							                  más pacientes y <b>optimiza</b> tus consultas.
                                              </h1>";
                                	        break;
                                	}
                            ?>
					</div>

					<div class="bullets_doctor">
						<ul class="list_doctor">
							<li><img src="assets/images/icons/bullet1.png">
							<p>
								<?php
                                    	
                                    	switch ($lang){
                                    	    case("en"):
                                    	        echo "Feature your practice online in the first social network for doctors and patients";
                                    	        break;
                                    	        
                                    	    case("es"):
                                    	        echo "Promueve tu práctica en línea en la primera red social para médicos y pacientes";
                                    	        break;
                                    	}
                                ?>		
							</p></li>
							<li><img src="assets/images/icons/bullet2.png">
							<p>
								<?php
                                    	
                                    	switch ($lang){
                                    	    case("en"):
                                    	        echo "Patients can book appointments with you online at up to three different locations";
                                    	        break;
                                    	        
                                    	    case("es"):
                                    	        echo "Los pacientes pueden agendar citas contigo en línea en hasta tres consultorios distintos ";
                                    	        break;
                                    	}
                                ?>
							</p></li>
							<li><img src="assets/images/icons/bullet3.png">
							<p>
								<?php
                                    	
                                    	switch ($lang){
                                    	    case("en"):
                                    	        echo "Communicate with patients outside of the office using our messaging system";
                                    	        break;
                                    	        
                                    	    case("es"):
                                    	        echo "Comunícate con pacientes fuera del consultorio usando nuestro sistema de mensajería";
                                    	        break;
                                    	}
                                ?>
							</p></li>
						</ul>
					</div>
					<div class="login_box_reg">

						<div class="login_header">
    							<?php
                                	
                                	switch ($lang){
                                	    case("en"):
                                	        echo "<h1>SignUp</h1>
							                  <h2 style='color: rgb(94, 158, 214);'>&nbsp doctor</h2>";
                                	        break;
                                	        
                                	    case("es"):
                                	        echo "<h1>Registro</h1>
							                  <h2 style='color: rgb(94, 158, 214);'>&nbsp doctores</h2>";
                                	        break;
                                	}
                            ?>
						</div>
						<form action="register.php" method="POST">
							<div class="signup" id="animation_container">
								<div id="inner">
									<div id="signup_pg1">
										<input type="text" name="reg_fname" placeholder="<?php
                                            	
                                            	switch ($lang){
                                            	    case("en"):
                                            	        echo "First Name";
                                            	        break;
                                            	        
                                            	    case("es"):
                                            	        echo "Nombres";
                                            	        break;
                                            	}
                                        ?>"
											value="<?php
											if (isset ( $_SESSION ['reg_fname'] )) {
												echo $txt_rep->entities ( $_SESSION ['reg_fname'] );
											}
											?>"
											required>
				                    
					                    <?php
					                    
					                    if(isset($_SESSION['no_errors'])){
					                    		if($_SESSION['no_errors'] == 1){
					                    			$error_array = array();
					                    		}
					                    }
					                    
										if (in_array ( "Your first name must be between 2 and 25 characters.<br>", $error_array )){
										    switch ($lang){
										        case("en"):
										            echo "<p id='incorrect'>Your first name must be between 2 and 25 characters.<br></p>";
										            break;
										        case("es"):
										            echo "<p id='incorrect'>Tu nombre debe tener entre 2 y 25 caracteres<br></p>";
										            break;
										      }}									
										else if (in_array ( "Your name can only have 1 first name and 1 middle name maximum.<br>", $error_array )){
										    switch ($lang){
										        case("en"):
										            echo "<p id='incorrect'>Your name can only have 1 first name and 1 middle name maximum.<br></p>";
										            break;
										        case("es"):
										            echo "<p id='incorrect'>Sólo puedes tener máximo 2 nombres<br></p>";
										            break;
										}}	
										?>
		
				                    <input type="text" name="reg_lname"
											placeholder="<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Last Name";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Apellidos";
                                                    	        break;
                                                    	}
                                                ?>"
											value="<?php
											if (isset ( $_SESSION ['reg_lname'] )) {
												echo $txt_rep->entities ( $_SESSION ['reg_lname'] );
											}
											?>"
											required>
				                    
				                    <?php
									if (in_array ( "Your last name must be between 2 and 25 characters.<br>", $error_array )){
									    switch ($lang){
									        case("en"):
									            echo "<p id='incorrect'>Your last name must be between 2 and 25 characters.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'>Tu apellido debe tener entre 2 y 25 caracteres<br></p>";
									            break;
									    }
									}
									else if (in_array ( "Your last name can only have your family name and a second family name maximum.<br>", $error_array )){
									    switch ($lang){
									        case("en"):
									            echo "<p id='incorrect'>You may only have two last names.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'>Sólo puedes tener 2 apellidos máximo.<br></p>";
									            break;
									    }
									}
										
									?>
		
				                    <input type="email" name="reg_email"
											placeholder="Email"
											value="<?php
											if (isset ( $_SESSION ['reg_email'] )) {
												echo $txt_rep->entities ( $_SESSION ['reg_email'] );
											}
											?>"
											required><input type="email" name="reg_email2"
											placeholder="<?php 
    											    switch ($lang){
            									        case("en"):
            									            echo "Confirm Email";
            									            break;
            									        case("es"):
            									            echo "Confirma tu Email";
            									            break;
            									    }
        									    ?>"
											value="<?php
											if (isset ( $_SESSION ['reg_email2'] )) {
												echo $txt_rep->entities ( $_SESSION ['reg_email2'] );
											}
											?>"
											required>
				                    
				                    <?php
									if (in_array ( "Email already in use.<br>", $error_array )){
									    switch($lang){
									        case("en"):
									            echo "<p id='incorrect'>Email already in use.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'>Email ya está en uso.<br></p>";
									            break;
									   }}
									else if (in_array ( "Email Invalid Format.<br>", $error_array )){
									    switch($lang){
									        case("en"):
									            echo "<p id='incorrect'>Invalid Email Format.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'>Formato inválido de Email.<br></p>";
									            break;
									   }}
								   else if (in_array ( "Emails don't match.<br>", $error_array )){
									       switch($lang){
									           case("en"):
									               echo "<p id='incorrect'>Emails don't match.<br></p>";
									               break;
									           case("es"):
									               echo "<p id='incorrect'>No concuerdan los Emails.<br></p>";
									               break;
									       }}
										
									?>
		
				                    <input type="password" name="reg_passwrd"
											placeholder="<?php
        											switch($lang){
        											    case("en"):
        											        echo "Password";
        											        break;
        											    case("es"):
        											        echo "Contraseña";
        											        break;
        											}
											?>" required><input type="password"
											name="reg_passwrd2" placeholder="<?php
        											switch($lang){
        											    case("en"):
        											        echo "Confirm Password";
        											        break;
        											    case("es"):
        											        echo "Confirma tu contraseña";
        											        break;
        											}
											?>" required>
				                      
				                    <?php
									if (in_array ( "Your passwords do not match.<br>", $error_array )){
    											switch($lang){
    											    case("en"):
    											        echo " <p id='incorrect'> Your passwords do not match.<br></p>";
    											        break;
    											    case("es"):
    											        echo " <p id='incorrect'> No concuerdan tus contraseñas.<br></p>";
    											        break;
    											}
									}
									else if (in_array ( "Your password must only contain characters and numbers.<br>", $error_array )){
									    switch($lang){
									        case("en"):
									            echo "<p id='incorrect'> Your password must only contain characters and numbers.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'> Tu contraseña sólo debe contener caracteres y números<br></p>";
									            break;
									    }
									}
									else if (in_array ( "Your password must be between 10 and 30 characters.<br>", $error_array )){
									    switch($lang){
									        case("en"):
									            echo "<p id='incorrect'> Your password must be between 10 and 30 characters.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'> Tu contraseña debe tener entre 10 y 30 caracteres.<br></p>";
									            break;
									    }
									}
										
									?>
				                    
				                    	
				                    	<?php
									$search_col = $lang . "_search";
									?>
																
				            			<input type="text"
											onkeyup="sanitizeSearchSpecialization(this.value, '<?php echo $lang; ?>', '<?php echo $search_col ;?>', 'search_specialization_reg', 'search_text_input_specialization','specialization_code')"
											placeholder="<?php 
											switch($lang) {
											    case("en"):
											        echo "Specializations (max. 4)";
											        break;
											    case("es"):
											        echo "Especializaciones (máx. 4)";
											        break;
                                            }?>" autocomplete="off"
											id="search_text_input_specialization"
											name="search_text_input_specialization" required>
				            			<?php
									if (in_array ( "specialization_not_found", $error_array )){
									    switch($lang){
									        case("en"):
									           echo "<p id='incorrect'>No specialization inserted, please make sure you type and <b>SELECT a specialization from the dropdown list</b>.<br></p>";
									           break;
									        case("es"):
									            echo "<p id='incorrect'>No ha insertado una especialización, asegúrese de <b>SELECCIONAR una especialización de la lista desplegable</b>.<br></p>";
									            break;
									    }
									}
										
									?>
									<div class= "style-2" id="search_specialization_reg"></div>
										<input type="hidden"
											name="search_text_input_specialization_holder"
											id="search_text_input_specialization_holder"> <input
											type="hidden" name="specialization_code"
											id="specialization_code"> 
											<div id="search_specialization_sex">
    											<input type="radio"
    											name="doc_sex_selected" value="m" checked> 
    											<?php 
    											     switch($lang){
    											         case("en"):
    											             echo "Male";
    											             break;
    											         case("es"):
    											             echo "Masculino";
    											             break;
    											     }
    											?>
    											<input
    											type="radio" name="doc_sex_selected" value="f">
    											<?php 
    											     switch($lang){
    											         case("en"):
    											             echo "Female";
    											             break;
    											         case("es"):
    											             echo "Femenino";
    											             break;
    											     }
    											?>
    											<input
    											type="radio" name="doc_sex_selected" value="o"> 
    											<?php 
    											     switch($lang){
    											         case("en"):
    											             echo "Other";
    											             break;
    											         case("es"):
    											             echo "Otro";
    											             break;
    											     }
    											?>
											</div>
				                      <?php
        							if (in_array ( "<div class='confirm_login'> You are all set. Please login. </div><br>", $error_array ))
        								
        							    switch($lang){
        								    case("en"):
        								        echo "<div class='confirm_login'> You are all set. Please log in. </div><br>";
        								        break;
        								    case("es"):
        								        echo "<div class='confirm_login'> Todo listo. Por favor inicia sesión. </div><br>";
        								        break;
        							    }
        							    
        							?>
									<select name="reg_adcountry" id="reg_adcountry">
											<option selected='selected' value='CO'>Colombia</option>
											<option value='US'>United States</option>
										</select>
				                    
				                    <input type="submit"
											name="register_button_doctor" value="<?php 
											     switch($lang){
											         case("en"):
											             echo "Register";
											             break;
											         case("es"):
											             echo "Registrarse";
											             break;
											     }
											?>"
											id="register_button_doctor" style="cursor: pointer;">

									</div>

								</div>
							</div>
						</form>
					</div>
					<div class="decoration_doctor"></div>
				</div>
			</div>
			<div class="for_patient">
				<div class="bullets_info">
					<div class="bullets_title">
						<?php 
						     switch($lang){
						         case("en"):
						             echo "<h2>Find</h2><br>
						                  <h1>the best specialist near your location</h1>";
						             break;
						         case("es"):
						             echo "<h2>Encuentra</h2><br>
						                  <h1>al mejor especialista cerca de ti</h1>";
						             break;
						     }
						?>
					</div>

					<div class="bullets_doctor">
						<ul class="list_doctor">
							<li><img src="assets/images/icons/bullet1.png">
							<?php 
						     switch($lang){
						         case("en"):
						             echo "<p>
                									Connect with your new and existing doctors in the first social network for doctors and patients
                								</p></li>
                							<li><img src='assets/images/icons/bullet2.png'>
                							<p>
                									Book appointments online with our health professionals
                								</p></li>
                							<li><img src='assets/images/icons/bullet3.png'>
                							<p>
                									Contact your doctor outside of the office using our messaging system
                								</p></li>";
						             break;
						         case("es"):
						             echo "<p>
                									Conéctate con tus médicos actuales y nuevos en la primera red social para doctores y pacientes
                								</p></li>
                							<li><img src='assets/images/icons/bullet2.png'>
                							<p>
                									Agenda citas en línea con nuestros profesionales de la salud
                								</p></li>
                							<li><img src='assets/images/icons/bullet3.png'>
                							<p>
                									Contacta a tu médico fuera de tu consultorio usando nuestro sistema de mensajería
                								</p></li>";
						             break;
						     }
						    ?>
							
						</ul>
					</div>
					<div class="login_box_reg">

						<div class="login_header">
    						<?php
                            	
                            	switch ($lang){
                            	    case("en"):
                            	        echo "<h1>SignUp</h1>
						                  <h2 style='color: #f1769b;'>&nbsp patient</h2>";
                            	        break;
                            	        
                            	    case("es"):
                            	        echo "<h1>Registro</h1>
						                  <h2 style='color: #f1769b;'>&nbsp pacientes</h2>";
                            	        break;
                            	}
                        ?>
						</div>
						<div class="signup">
							<form action="register.php" method="POST">
								<input type="text" name="reg_fname" placeholder="<?php
                                            	
                                            	switch ($lang){
                                            	    case("en"):
                                            	        echo "First Name";
                                            	        break;
                                            	        
                                            	    case("es"):
                                            	        echo "Nombres";
                                            	        break;
                                            	}
                                        ?>"
									value="<?php
									if (isset ( $_SESSION ['reg_fname'] )) {
										echo $txt_rep->entities ( $_SESSION ['reg_fname'] );
									}
									?>"
									required>
									<?php 
										if(isset($_SESSION['no_errors'])){
					                    		if($_SESSION['no_errors'] == 1){
					                    			$error_array = array();
					                    		}
					                    }
					               ?>
		                    <?php
		                    if (in_array ( "Your first name must be between 2 and 25 characters.<br>", $error_array )){
		                        switch ($lang){
		                            case("en"):
		                                echo "<p id='incorrect'>Your first name must be between 2 and 25 characters.<br></p>";
		                                break;
		                            case("es"):
		                                echo "<p id='incorrect'>Tu nombre debe tener entre 2 y 25 caracteres<br></p>";
		                                break;
		                        }}
		                        else if (in_array ( "Your name can only have 1 first name and 1 middle name maximum.<br>", $error_array )){
		                            switch ($lang){
		                                case("en"):
		                                    echo "<p id='incorrect'>Your name can only have 1 first name and 1 middle name maximum.<br></p>";
		                                    break;
		                                case("es"):
		                                    echo "<p id='incorrect'>Sólo puedes tener máximo 2 nombres<br></p>";
		                                    break;
		                            }}	
							?>

		                    <input type="text" name="reg_lname"
									placeholder="<?php
                                                    	
                                                    	switch ($lang){
                                                    	    case("en"):
                                                    	        echo "Last Name";
                                                    	        break;
                                                    	        
                                                    	    case("es"):
                                                    	        echo "Apellidos";
                                                    	        break;
                                                    	}
                                                ?>"
									value="<?php
									if (isset ( $_SESSION ['reg_lname'] )) {
										echo $txt_rep->entities ( $_SESSION ['reg_lname'] );
									}
									?>"
									required>
		                    
		                    <?php
									if (in_array ( "Your last name must be between 2 and 25 characters.<br>", $error_array )){
									    switch ($lang){
									        case("en"):
									            echo "<p id='incorrect'>Your last name must be between 2 and 25 characters.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'>Tu apellido debe tener entre 2 y 25 caracteres<br></p>";
									            break;
									    }
									}
									else if (in_array ( "Your last name can only have your family name and a second family name maximum.<br>", $error_array )){
									    switch ($lang){
									        case("en"):
									            echo "<p id='incorrect'>You may only have two last names.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'>Sólo puedes tener 2 apellidos máximo.<br></p>";
									            break;
									    }
									}
										
							?>

		                    <input type="email" name="reg_email"
									placeholder="Email"
									value="<?php
									if (isset ( $_SESSION ['reg_email'] )) {
										echo $txt_rep->entities ( $_SESSION ['reg_email'] );
									}
									?>"
									required><input type="email" name="reg_email2"
									placeholder="<?php 
    											    switch ($lang){
            									        case("en"):
            									            echo "Confirm Email";
            									            break;
            									        case("es"):
            									            echo "Confirma tu Email";
            									            break;
            									    }
        									    ?>"
									value="<?php
									if (isset ( $_SESSION ['reg_email2'] )) {
										echo $txt_rep->entities ( $_SESSION ['reg_email2'] );
									}
									?>"
									required>
		                    
		                     <?php
									if (in_array ( "Email already in use.<br>", $error_array )){
									    switch($lang){
									        case("en"):
									            echo "<p id='incorrect'>Email already in use.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'>Email ya está en uso.<br></p>";
									            break;
									   }}
									else if (in_array ( "Email Invalid Format.<br>", $error_array )){
									    switch($lang){
									        case("en"):
									            echo "<p id='incorrect'>Invalid Email Format.<br></p>";
									            break;
									        case("es"):
									            echo "<p id='incorrect'>Formato inválido de Email.<br></p>";
									            break;
									   }}
								   else if (in_array ( "Emails don't match.<br>", $error_array )){
									       switch($lang){
									           case("en"):
									               echo "<p id='incorrect'>Emails don't match.<br></p>";
									               break;
									           case("es"):
									               echo "<p id='incorrect'>No concuerdan los Emails.<br></p>";
									               break;
									       }}
										
									?>

		                    <input type="password" name="reg_passwrd"
									placeholder="<?php
        											switch($lang){
        											    case("en"):
        											        echo "Password";
        											        break;
        											    case("es"):
        											        echo "Contraseña";
        											        break;
        											}
											?>" required><input type="password"
									name="reg_passwrd2" placeholder="<?php
        											switch($lang){
        											    case("en"):
        											        echo "Confirm Password";
        											        break;
        											    case("es"):
        											        echo "Confirma tu contraseña";
        											        break;
        											}
											?>" required>
		                      
		                    <?php
		                    if (in_array ( "Your passwords do not match.<br>", $error_array )){
		                        switch($lang){
		                            case("en"):
		                                echo " <p id='incorrect'> Your passwords do not match.<br></p>";
		                                break;
		                            case("es"):
		                                echo " <p id='incorrect'> No concuerdan tus contraseñas.<br></p>";
		                                break;
		                        }
		                    }
		                    else if (in_array ( "Your password must only contain characters and numbers.<br>", $error_array )){
		                        switch($lang){
		                            case("en"):
		                                echo "<p id='incorrect'> Your password must only contain characters and numbers.<br></p>";
		                                break;
		                            case("es"):
		                                echo "<p id='incorrect'> Tu contraseña sólo debe contener caracteres y números<br></p>";
		                                break;
		                        }
		                    }
		                    else if (in_array ( "Your password must be between 5 and 30 characters.<br>", $error_array )){
		                        switch($lang){
		                            case("en"):
		                                echo "<p id='incorrect'> Your password must be between 5 and 30 characters.<br></p>";
		                                break;
		                            case("es"):
		                                echo "<p id='incorrect'> Tu contraseña debe tener entre 5 y 30 caracteres.<br></p>";
		                                break;
		                        }
		                    }
							?>
		                    
		                    <select name="reg_adcountry" id="reg_adcountry">
									<option selected='selected' value='CO'>Colombia</option>
									<option value='US'>United States</option>
								</select> <input type="submit" name="register_button_patient"
									value="<?php 
											     switch($lang){
											         case("en"):
											             echo "Register";
											             break;
											         case("es"):
											             echo "Registrarse";
											             break;
											     }
											?>"
									style="background-color: #f95c8b; border: 1px solid #f95c8b; cursor: pointer;"
									id="register_button_patient">
		                				<br>
		                     <?php
        							if (in_array ( "<div class='confirm_login'> You are all set. Please login. </div><br>", $error_array ))
        								switch($lang){
        								    case("en"):
        								        echo "<div class='confirm_login'> You are all set. Please login. </div><br>";
        								        break;
        								    case("es"):
        								        echo "<div class='confirm_login'> Todo listo. Por favor inicia sesión </div><br>";
        								        break;
        							    }
        							    
        							?>

		                </form>
						</div>
					</div>
					<div class="decoration_patient"></div>
				</div>
			</div>
			<div class="login_box">
				
				<div class="login_header">
					<?php 
					   switch($lang){
					       case("en"):
					           echo "<h1>Login to</h1>";
				                break;
					       case("es"):
					           echo "<h1>Inicia sesión en</h1>";
					           break;
					   }
					?>
					<h2>&nbsp;Confidr</h2>
				</div>
				<div class="signin">
					<form action="register.php" method="POST">
						<input type="email" name="log_email" placeholder="Email"
							value="<?php
							if (isset ( $_SESSION ['log_email'] )) {
								echo $txt_rep->entities ( $_SESSION ['log_email'] );
							}
							?>"
							required> 
						<input type="password" name="log_passwrd"
							placeholder="<?php
        											switch($lang){
        											    case("en"):
        											        echo "Password";
        											        break;
        											    case("es"):
        											        echo "Contraseña";
        											        break;
        											}
											?>">
						<p id="incorrect">
		                    <?php
							if (in_array ( "Incorect Email or Password.<br>", $error_array )) {
							    switch($lang){
							        case("en"):
							            echo "Incorect Email or Password.<br>";
							            echo '<form action="register.php" method="POST">
        										   <!-- cambios victor-->
        										  <input type="submit" name="reset_psswrd_button" id="send_link" value="Forgot Password">
        									   </form>
                                               <!-- cambios victor-->';
							            break;
							        case("es"):
							            echo "Email o contraseña incorrecta.<br>";
							            echo '<form action="register.php" method="POST">
                                              <!-- cambios victor-->
        										  <input type="submit" name="reset_psswrd_button" id="send_link" value="Olvidé Contraseña">
        									  </form>
                                              <!-- cambios victor--> ';
							            break;
							    }
								
							}
							elseif(in_array ( "Email not yet confirmed.<br>", $error_array )){
							    switch($lang){
							        case("en"):
							             echo 'Your email has not been confirmed yet, click on "Send Link" to send a new link to your email<br>
        									   <form action="register.php" method="POST">
        										<!-- cambios victor-->
        										  <input type="submit" name="send_link_button" id="send_link" value="Send Link">
                                              <!-- cambios victor-->
        									   </form>     
        								        ';
							             break;
							        case("es"):
							            echo 'Su email no ha sido confirmado, haz click en "Enviar Link" para enviar un nuevo link a tu email<br>
        									   <form action="register.php" method="POST">
                                                <!-- cambios victor-->
        										  <input type="submit" name="send_link_button" id="send_link" value="Enviar Link">
                                                <!-- cambios victor-->
        									   </form>
        								        ';
							            break;
							    }
								
							}
							?>
	                    	</p>
						<input type="submit" name="login_button" id ="login_button" value="Login" style="cursor: pointer;">
					</form>
				</div>
			</div>
		</div>
		<div class="loading"></div>
	<script>

// 	switch(currentDiv){
// 		case "home": {
// 						break;
// 						}
// 		case "login": {
// 							toggleRegisterForms('login');
// 							break;
// 						} 
// 		case "patient": {
// 							toggleRegisterForms('intro_patients');
// 							break;
// 						}
// 		case "doctor": {
// 							toggleRegisterForms('intro_doctors');
// 							break;
// 						}
// 	}

	$('#lang_select').change(function(){
		var lang = $('#lang_select option:selected').val();
		
		$.ajax({
			url: "register.php",
			type: "POST",
			data: "lang=" + lang,
			cache:  false,

			success: function(data){
				location.reload();
			}
		});
		
	});
	
	$('input[name="search_text_input_specialization"]').keydown(function (e) {
		var str_length = $(this).length;
		var position = $(this).getCursorPosition();
		var deleted = '';
		var val = $(this).val();
		var codes = $('#specialization_code').val().split(",");
		
		if (e.which == 8) {
			if (position[0] == position[1]) {
				if (position[0] == 0)
					deleted = '';
				else
					deleted = val.substr(position[0] - 1, 1);
			}
			else {
				deleted = val.substring(position[0], position[1]);
			}
		}
		else if (e.which == 46) {
			var val = $(this).val();
			if (position[0] == position[1]) {
		            
				if (position[0] === val.length)
					deleted = '';
				else
					deleted = val.substr(position[0], 1);
			}
			else {
				deleted = val.substring(position[0], position[1]);
			}
		}
		if(deleted == ","){
			//alert(valArray.join(", "));
			codes.pop();
			var newVal = $("input[name=search_text_input_specialization_holder]").val();
			var newValArr = newVal.split(",");
			newValArr.pop();
			$("input[name=search_text_input_specialization]").val("");
			
			$('#specialization_code').val(codes.join(","));
 			$("input[name=search_text_input_specialization_holder]").val(newValArr.join(","));
 			$("input[name=search_text_input_specialization]").val(newValArr.join(", ") + ", a");
		}
	});

        	
	</script>

</body>
</html>