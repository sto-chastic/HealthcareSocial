<?php 

require 'config/config.php';
include('includes/classes/User.php');
include("includes/classes/Message.php");
include("includes/classes/TimeStamp.php");
include("includes/classes/TxtReplace.php");
include("includes/classes/Settings.php");

?>

<!DOCTYPE html>
<html>
<head>

	<title>QuickChat</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="ROBOTS" content="NOINDEX, NOFOLLOW, NOSNIPPET, NOARCHIVE">


	<!-- Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="assets/js/bootstrap.js"></script>
	<script src="assets/js/bootbox.min.js"></script>
	<script src="assets/js/confidr.js"></script>
	<script src="assets/js/jquery.jcrop.js"></script>
	<script src="assets/js/jcrop_bits.js"></script>

	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<link rel="stylesheet" type="text/css" href="assets/css/calendar.css">
	<link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />
	
</head>
<body>

	<style type="text/css">
		*{
			font-family: Arial, Helvetica, Sans-serif;
		}
	</style>

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
			
			//Objects
			$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
			$txt_rep = new TxtReplace();
			$message_obj = new Message ($con, $userLoggedIn, $userLoggedIn_e);
			
			//Language
			
			$lang = $_SESSION['lang']; 
		
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: register.php");
			$stmt->close();
		}
		
		$never_used = 0;
		if(isset($_GET['u'])){
		    if($_GET['u'] == "new"){
			    $user_to = "new";
			    $user_to_e = "new";
				$never_used = 1;
			}
			else{
				$user_to = $crypt->Decrypt(pack("H*",$_GET['u']));
				$user_to_e = $crypt->EncryptU($user_to);
			}
			
		}else{
			$user_to = $message_obj->getMostRecentUser();
			if($user_to == ""){
				$user_to = "new";
				$user_to_e = "new";
			} else {
			    $user_to_e = $crypt->EncryptU($user_to);
			}
		}
		
		$fixed_condition = FALSE;
		if(isset($_GET['f']))
			$fixed_condition = $_GET['f'];
		
		if($fixed_condition == 1 && isset($user_to)){
			$fixed_condition = TRUE;
		}
		
		$user_to_obj = "";
		
		if($user_to != "new"){
			try{
				$user_to_obj = new User($con, $user_to, $user_to_e); //Assigning if possible
			}
			catch ( Exception $e ){
				switch ($lang)
				{
					case "en":
						echo "Unable to reach messages at this time, please contact ConfiDr.";
						die;
						break;
					case "es":
						echo "No ha sido posible acceder a mensajes en este momento, por favor ponte en contacto con ConfiDr.";
						die;
						break;
				}
			}
		}
		
		if($user_to_obj != ""){
			$user_to_fnl = $txt_rep->entities($user_to_obj->getFirstAndLastName());
		}
		else{
			$user_to_fnl = "";
		}
		
		if($user_to != "new"){
			$message_availability = $message_obj->checkAvailabilityToMessage($user_to);
		}
		else{
			$message_availability = TRUE;
		}
		
		$fee = NULL;
		
		if(isset($_POST['confirm_purchase'])){
		    $user_to_e = $crypt->EncryptU($user_to);
		    $settings = new Settings($con, $user_to, $user_to_e);
			$fee = $settings->getSettingsValues('payed_messages_cost'); 
			$initial_patient_message = $_POST['initial_patient_message'];
			$message_obj->createMessagingPaymentInstance($user_to,$initial_patient_message,$fee);
		}
		
		if(isset($_POST['cancel_payment'])){
			$message_obj->cancelPendingPaymentAcceptance($user_to);
		}
		
		
		
	?>
	
	<?php 
	if($fixed_condition)
		echo '<div class="conversations_selector" style="display:none">';
	else{
		echo '<div class="conversations_selector">';
	}
	?>
		<h4>
		<?php 
			switch ($lang){
				
				case("en"):
					echo "Conversations";
					break;
					
				case("es"):
					echo "Conversaciones";
					break;
			}
		?>
		</h4>
		<div class="message_settings_dropdown" id="message_settings_dropdown"></div>
		
		<?php 
		if($user_obj->isDoctor()){
			?>
			<a href="javascript:void(0);" onclick="openMessageSettings();">
			<img src="assets/images/icons/wrench.png" class="icon_position"
			onmouseover="this.src='assets/images/icons/wrench.png'"
			onmouseout="this.src='assets/images/icons/wrenchh.png'" id="message_settings_drop_down_button" title= "message_settings"/>
			</a>
  
			<?php 
		}
		  ?>

		<div class = "loaded_conversations style-2" >
			<?php 
				echo $message_obj->getConvos($user_to); //This is already escaped for html in Messages
			?>
		</div>
		<div id="send_new_conversation">
			<a href="messages_frame.php?u=new">
			<?php 
			switch ($lang){
				
				case("en"):
					echo "Send new message.";
					break;
					
				case("es"):
					echo "Enviar Nuevo Mensaje";
					break;
			}
			?>
			</a>
		</div>
	</div>
	
	<?php 
		if($fixed_condition)
			echo '<div class="messages_main_column" style="width:100%;margin-left: 0px;">';
		else{
			echo '<div class="messages_main_column">';
		}
	?>
	
		<?php
			if($user_to != "new"){
				switch ($lang){	
					case("en"):
						echo "<h4>You and <a target='_parent' href='".bin2hex($user_to_e)."'>" . $txt_rep->entities($user_to_obj->getFirstAndLastName()) . "</a></h4>";
						break;
						
					case("es"):
						echo "<h4>Tu y <a target='_parent' href='".bin2hex($user_to_e)."'>" . $txt_rep->entities($user_to_obj->getFirstAndLastName()) . "</a></h4>";
						break;
				}
				echo '<div class="messages_mini_top_banner">';
				if(!$user_to_obj->isDoctor() && $user_obj->isDoctor()){
					
					echo '<form>';
					
					if($message_obj->checkEnabledChat($user_to)){
						switch ($lang){
							case("en"):
								echo '<input type="radio" name="enabled_chat" id="enabled_chat" value="1" checked> <div id="enabled_chat_text">Free Messages</div>
								<div class="info_icon">i<span class="tip_left" > <u>Free Messages:</u> The patient will be able to message you without paying for this service and for unlimited time.</span></div>
	  							<input type="radio" name="enabled_chat" id="enabled_chat" value="0" > <div id="enabled_chat_text">Require Payment</div>';
								break;
								
							case("es"):
								echo '<input type="radio" name="enabled_chat" id="enabled_chat" value="1" checked> <div id="enabled_chat_text">Mensajes Gratis</div>
								<div class="info_icon">i<span class="tip_left" > <u>Mensajes Gratis:</u> Mientras estÃ© activado, el paciente podrÃ¡ enviarte mensajes sin pagarte por este servicio.</span></div>
	  							<input type="radio" name="enabled_chat" id="enabled_chat" value="0" > <div id="enabled_chat_text">Requerir Pago</div>';
								break;
						}

					}
					else{
						switch ($lang){
							case("en"):
								echo '<input type="radio" name="enabled_chat" id="enabled_chat" value="1"> <div id="enabled_chat_text">Free Messages</div>
								<div class="info_icon">i<span class="tip_left" > <u>Free Messages:</u> The patient will be able to message you without paying for this service and for unlimited time.</span></div>
	  							<input type="radio" name="enabled_chat" id="enabled_chat" value="0" checked> <div id="enabled_chat_text">Require Payment</div>';
								
								break;
								
							case("es"):
								echo '<input type="radio" name="enabled_chat" id="enabled_chat" value="1"> <div id="enabled_chat_text">Mensajes Gratis</div>
								<div class="info_icon">i<span class="tip_left" > <u>Mensajes Gratis:</u> Mientras estÃ© activado, el paciente podrÃ¡ enviarte mensajes sin pagarte por este servicio.</span></div>
	  							<input type="radio" name="enabled_chat" id="enabled_chat" value="0" checked> <div id="enabled_chat_text">Requerir Pago</div>';
								
								break;
						}

					}
					switch ($lang){
						case("en"):
							echo '<div class="info_icon">i<span class="tip"> <u>Require Payment:</u> The patient will need to pay you a fee in order to message you and it will only be for limited time (2 weeks). This fee is selected in Message Settings, located in the top bar of the messages bar on the left.</span></div>
  								</form>';
							
							break;
							
						case("es"):
							echo '<div class="info_icon">i<span class="tip"> <u>Requerir Pago:</u> El paciente deberá pagarte un valor para poder enviarte mensajes por un tiempo limitado (2 semanas). Éste valor se escoge en Configuraciones de Mensajes, ubicado en la parte superiror de la barra de mensajes de la izquierda.</span></div>
  								</form>';
							
							break;
					}

				}
				echo ' <div id="remaining_time"></div>
					</div>';
				
				if($user_obj->isDoctor()){
					echo '<input type="hidden" name="termination_time" id="termination_time" value="' . $message_obj->getRemainingTime($user_to, $userLoggedIn) . '">';
				}
				else{
					echo '<input type="hidden" name="termination_time" id="termination_time" value="' . $message_obj->getRemainingTime($userLoggedIn, $user_to) . '">';
				}
				
				echo "<div class='loaded_messages style-2' id='scroll_messages'>"; //scroll messages allows to load messages in inverse order.
				//Set messages as opened
				$my_messages_tab = $message_obj->getMessagesTable();
				$stmt = $con->prepare("UPDATE $my_messages_tab SET opened='yes',viewed = 'yes' WHERE user_to=? OR user_from = ?");
				$stmt->bind_param("ss", $user_to, $user_to);
				$stmt->execute();
				echo "</div>";
			}
			else{
				switch ($lang){
					case("en"):
						echo "<h4>New Message</h4>";
						
						break;
						
					case("es"):
						echo "<h4>Nuevos Mensajes</h4>";
						
						break;
				}
				
			}
		?>

		<div class="messages_post">
		   
			<?php 
				if($user_to == "new"){
					?>
					<div id="select_message_ux">
					<?php 
					
						switch ($lang){
								 		
							case("en"):
					?>
								<h2>Select a doctor or patient you would like to message.<br><br>
								To: 
					<?php 
						        break;
							
							case("es"):
					?>
								<h2>Selecciona un doctor o paciente para enviar un mensaje.<br><br>
								Para: 
					<?php 
								break;
						}
					?>

					<input type='text' onkeyup='getUsers(this.value)' name='q' placeholder='<?php 
					switch ($lang){
						
						case("en"):
							echo "Name";
							break;
							
						case("es"):
							echo "Nombre";
							break;
					}
					?>' autocomplete='off' id='search_text_input_messages'>
					</h2><div class='results style-2' ></div>
					</div>
					<?php
				}
				
				elseif($message_obj->checkAvailabilityToMessage($user_to)){
					?>
						<h1>
						<?php 
						switch ($lang){
							
							case("en"):
								echo "Comment";
								break;
								
							case("es"):
								echo "Comentar";
								break;
						}
						?>
						</h1>

						<textarea class= "style-2" name='message_body' id='message_text_area' placeholder='<?php 

						switch ($lang){
							
							case("en"):
								echo "Type in your message.";
								break;
								
							case("es"):
								echo "Escribe tu mensaje.";
								break;
						}
						?>'></textarea>
						<input type="hidden" name="pb" id="pb" value="0">
						<div name='post_message' class='info' id='message_submit'>
						<?php 
						switch ($lang){
							
							case("en"):
								echo "Send";
								break;
								
							case("es"):
								echo "Enviar";
								break;
						}
						?>
						</div>
						<script>
							var ddd = document.getElementById('scroll_messages');
							ddd.scrollTop = ddd.scrollHeight;
						</script>
					<?php
				}
				else{
					
					if($message_obj->checkPendingPaymentAcceptance($user_to)){
						?>
						<form action="messages_frame.php?u=<?php echo bin2hex($user_to_e); echo ($fixed_condition)? 'f=1':'';?>" method="POST">
					        	<input type="submit" class="cancel_payment" name="cancel_payment" value="
						        	<?php 
									switch ($lang){
										
										case("en"):
											echo "Cancel Payment";
											break;
											
										case("es"):
											echo "Cancelar Pago";
											break;
									}
								?>
							">
					    	</form>
						<?php
					}
					else{
						$payments_tab = $user_obj->getPaymentsTab();
						$stmt = $con->prepare("SELECT * FROM $payments_tab");
						$stmt->execute();
						
						$q = $stmt->get_result();
						$nums = mysqli_num_rows($q);
						
						if($nums > 0){
							
							echo "<div class='pay_button' id='pay_service' data-toggle='modal' data-target='#confirm_payment'>";
									switch ($lang){
										
										case("en"):
											echo "Pay";
											break;
											
										case("es"):
											echo "Pagar";
											break;
									}
							echo "</div>";
						}
						else{
							echo "<a href='payments.php' target='_parent'><div class='pay_button' id='pay_service'>";
							switch ($lang){
								
								case("en"):
									echo "Add Payment Method";
									break;
									
								case("es"):
									echo "Agregar Medio de Pago";
									break;
							}
							echo "</div></a>";
						}
					}
				}
			?>
		</div>

	</div>
	
	<?php
	if(!$user_obj->isDoctor()){
	?>
	<div class="modal fade" id="confirm_payment" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">

	      <div class="modal-header">
	        <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true" style="margin-top: 0px;">×</button>
	        <h4 class="modal-title" id="myModalLabel">
	        
			<?php 
				switch ($lang){
					
					case("en"):
						echo "Purchase Message Services";
						break;
						
					case("es"):
						echo "Comprar Servicios de Mensajes";
						break;
				}
			?>
			</h4>
	      </div>

	      <div class="modal-body">
	      	<?php 
	      		
	      		$user_to_e = $crypt->EncryptU($user_to);
	      	
				switch ($lang){
						 		
					case("en"):
			?>
				        <p>
				        		Do you really wish to purchase two weeks of message services to talk with <b>Dr. <?php echo $txt_rep->entities($user_to_obj->getFirstAndLastName());?></b>?
				        		<b> You will be charged <?php $settings = new Settings($con, $user_to,$user_to_e);
				        			$fee = $settings->getSettingsValues("payed_messages_cost");
								echo $fee;?>	COP </b> when the doctor replies to you. If in two days the doctor has not replied, you will not be charged and the transaction will be canceled.
				        </p>
			<?php 
				        break;
					
					case("es"):
			?>
				        <p>
				        		Desea comprar dos semanas para escribirle al <b>Dr. <?php echo $txt_rep->entities($user_to_obj->getFirstAndLastName());?></b>?
				        		<b> Te será cargado un saldo de <?php $settings = new Settings($con, $user_to,$user_to_e);
				        			$fee = $settings->getSettingsValues("payed_messages_cost");
								echo $fee;?>	COP </b> una vez que el doctor te responda. Si en dos días el doctor no te ha respondido, no se te cobrará y la transacción se cancelará.
				        </p>
			<?php 
						break;
				}
			?>
				<p><?php 
	     	   	switch ($lang){
	     	   		
	     	   		case("en"):
	     	   			echo "Use the space below to explain your needs to the doctor. This way the doctor will know <b>if he/she can help you by messaging you, or not</b>. This way you can <b>avoid unnecessary charges</b>.";
	     	   			break;
	     	   			
	     	   		case("es"):
	     	   			echo "Use el espacio debajo para explicar sus nececidades al doctor. De esta forma el doctor sabrá <b>si puede, o no, ayudarte por mensajes</b>. De esta forma puedes <b>evitar cargos innecesarios</b>.";
	     	   			break;
	     	   	}
	     	   	?>
	     	   	</p>
	      </div>
		  <form action="messages_frame.php?u=<?php echo bin2hex($user_to_e); echo ($fixed_condition)? 'f=1':'';?>" method="POST">
	        <div class="modal-footer">
	        		<textarea name="initial_patient_message" id="initial_patient_message" placeholder='<?php 
	     	   	switch ($lang){
	     	   		
	     	   		case("en"):
	     	   			echo "Type HERE a small message to the doctor detailing your inquiries.";
	     	   			break;
	     	   			
	     	   		case("es"):
	     	   			echo "Escriba AQUÍ un pequeño mensaje detallando sus necesidades para el doctor.";
	     	   			break;
	     	   	}
	     	   	?>'></textarea>
	     	   	<button type="button" class="btn btn-default" id="cancel_button" data-dismiss="modal">
	     	   	<?php 
	     	   	switch ($lang){
	     	   		
	     	   		case("en"):
	     	   			echo "Cancel";
	     	   			break;
	     	   			
	     	   		case("es"):
	     	   			echo "Cancelar";
	     	   			break;
	     	   	}
	     	   	?></button>
	     	   	<input type="submit" name="confirm_purchase" class="btn btn-primary" value='<?php 
	     	   	switch ($lang){
	     	   		
	     	   		case("en"):
	     	   			echo "Accept";
	     	   			break;
	     	   			
	     	   		case("es"):
	     	   			echo "Aceptar";
	     	   			break;
	     	   	}
	     	   	?>'>
	     	  
	    	    </div>
	    	  </form>
	    </div>
	  </div>
	</div>
	<?php
	}
	?>
	
	<div class="modal fade" id="payment_transaction_accepted" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">

			<div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">
				<?php 
					switch ($lang){
						
						case("en"):
							echo "Successful Transaction!";
							break;
							
						case("es"):
							echo "Â¡TransacciÃ³n Exitosa!";
							break;
					}
				?>
				</h4>
		    </div>

		    <div class="modal-body">
		      	<?php 
					switch ($lang){
							 		
						case("en"):
				?>
					        <p>
					        		The payment for your messaging services was received successfully. This will be reflected soon in your ConfiDr. Balance.
					        </p>
				<?php 
					        break;
						
						case("es"):
				?>
					        <p>
					        		El pago por sus servicios de mesajerçia fue recibido exitosamente. Este se verá reflejado pronto en su estado de cuenta ConfiDr.
					        </p>
				<?php 
							break;
					}
				?>
	
	      	</div>
	      	<div class="modal-footer">
    	     	<button type="button" class="btn btn-default" id="cancel_button" data-dismiss="modal">
    		     	   	<?php 
    	     	   	switch ($lang){
    	     	   		
    	     	   		case("en"):
    	     	   			echo "Accept";
    	     	   			break;
    	     	   			
    	     	   		case("es"):
    	     	   			echo "Aceptar";
    	     	   			break;
    	     	   	}
    	     	   	?>
    	     	</button>
    	    </div>	
	    </div>
	  </div>
	</div>
	
	<div class="modal fade" id="payment_transaction_rejected" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">

			<div class="modal-header">
		        <button type="button" class="bootbox-close-button close" data-dismiss="modal" aria-hidden="true" style="margin-top: 0px;">×</button>
		        <h4 class="modal-title" id="myModalLabel">
				<?php 
					switch ($lang){
						
						case("en"):
							echo "Failed Transaction";
							break;
							
						case("es"):
							echo "TransacciÃ³n Fallida";
							break;
					}
				?>
				</h4>
		    </div>

		    <div class="modal-body">
		      	<?php 
					switch ($lang){
							 		
						case("en"):
				?>
					        <p>
					        		The payment for your messaging services could not be completed due to a problem with the buyer's payment method. 
					        </p>
				<?php 
					        break;
						
						case("es"):
				?>
					        <p>
					        		El pago por sus servicios de mesajerÃ­a no pudo ser completada debido a un problema con el medio de pago del comprador.
					        </p>
				<?php 
							break;
					}
				?>
	
	      	</div>
      		<div class="modal-footer">
    	     	<button type="button" class="btn btn-default" id="cancel_button" data-dismiss="modal">
    		     	   	<?php 
    	     	   	switch ($lang){
    	     	   		
    	     	   		case("en"):
    	     	   			echo "Accept";
    	     	   			break;
    	     	   			
    	     	   		case("es"):
    	     	   			echo "Aceptar";
    	     	   			break;
    	     	   	}
    	     	   	?>
    	     	</button>
	    	</div>	
	    </div>
	  </div>
	</div>
	
	<div class="modal fade" id="payment_transaction_wait" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">

			<div class="modal-header">
		        <h4 class="modal-title" id="myModalLabel">
				<?php 
					switch ($lang){
						
						case("en"):
							echo "Processing Transaction";
							break;
							
						case("es"):
							echo "Procesando TransacciÃ³n";
							break;
					}
				?>
				</h4>
		    </div>

		    <div class="modal-body">
		      	<?php 
					switch ($lang){
							 		
						case("en"):
				?>
					        <p>
					        		The payment is currently being processed, please wait.
					        </p>
				<?php 
					        break;
						
						case("es"):
				?>
					        <p>
					        		El pago se estÃ¡ procesando, por favor espere.
					        </p>
				<?php 
							break;
					}
				?>
	      	</div>
	    </div>
	  </div>
	</div>
	
	
		<div class="modal fade" id="payment_off" tabindex="-1" role="dialog" aria-labelledby="postModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">

			<div class="modal-header">
		        <h4 class="modal-title" id="myModalLabel">
				<?php 
					switch ($lang){
						
						case("en"):
							echo "Payment: Off";
							break;
							
						case("es"):
							echo "Pago: Apagado";
							break;
					}
				?>
				</h4>
		    </div>

		    <div class="modal-body">
		      	<?php 
					switch ($lang){
							 		
						case("en"):
				?>
					        <p>
					        		You have turned "off" the payment requirement for this patient (or for all patients). This means he/she can message you and you can message him/her without her being charged. If at some point you wish to charge the patient for this service, you can turn the patient payment "on" again, and if the patient had requested to purchase your messages, him/she will be charged your fee when you message them again.
					        </p>
				<?php 
					        break;
						
						case("es"):
				?>
					        <p>
					        		Has apagado el cobro por mensajes para este paciente (o para todos los pacientes). Esto quiere decir que el/ella te puede escribir y tu a el sin que él te pague por este servicio. Si en algún momento deseas cobrarle a este paciente por este servicio, puedes encender el cobro nuevamente, y si el paciente ha ofrecido comprar este servicio, se le cobrará el saldo una vez le escribas de nuevo.
					        </p>
				<?php 
							break;
					}
				?>
	      	</div>
	      	<div class="modal-footer">
    	     	<button type="button" class="btn btn-default" id="cancel_button" data-dismiss="modal">
    		     	   	<?php 
    	     	   	switch ($lang){
    	     	   		
    	     	   		case("en"):
    	     	   			echo "Accept";
    	     	   			break;
    	     	   			
    	     	   		case("es"):
    	     	   			echo "Aceptar";
    	     	   			break;
    	     	   	}
    	     	   	?>
    	     	</button>
	     	</div>
	    </div>
	  </div>
	</div>
	
	<script>
	
	function loadMessages(user_to,latest_loaded_message_id){
		var ajaxLMess = $.ajax({
			url: "includes/handlers/ajax_load_messages_conversation.php",
			type: "POST",
			data: "user_to=" + user_to + "&latest_load_mess_id=" + latest_loaded_message_id,
			cache:false,

			success: function(response){
				$('#scroll_messages').find('#latest_loaded_message_id').remove();
				$('#scroll_messages').find('#ending').remove();
				$('#scroll_messages').find('#scroll_marker').remove();

				//$('#loading').hide();
				if(latest_loaded_message_id == 0){
					$('#scroll_messages').hide().html(response).fadeIn();
					var ddd = document.getElementById("scroll_messages");// Make messages load in inverse order.
					ddd.scrollTop = ddd.scrollHeight;
				}
				else{
					$('#scroll_messages').prepend(response).fadeIn();
					//alert($('#scroll_messages').find('#scroll_marker').offset().top);
					var ddd = document.getElementById("scroll_messages");
					ddd.scrollTop = $('#scroll_messages').find('#scroll_marker').offset().top - $('#scroll_messages').innerHeight();
				}
			}
		});
	}

	function storeMessageData(user_to, message_body, callback) {
		if($('#pending_acceptance').length > 0){
			var acc_payment = $('#pending_acceptance').val();
			
			if($("#pending_acceptance").val() == 1 && message_body != ''){
				$('#pb').val(1);
				$("#message_text_area").prop('disabled', true);
				$("#message_submit").css({"background-color":"grey"});

				$("#payment_transaction_wait").modal('show');
				
				var ajaxAccPay = $.ajax({
					url: "includes/handlers/ajax_accept_message_payment.php",
					type: "POST",
					data: "patient_user=" + user_to,
					cache:false,
	
					success: function(response){

						$('#pb').val(0);
						$("#message_text_area").prop('disabled', false);
						$("#message_submit").css({"background-color":"#f38ead"});

						$("#payment_transaction_wait").modal('hide');
						
						<?php //TODO: here?>
 						if(response == "acc"){
 							//alert("Payment Accepted.");
 							$('#payment_transaction_accepted').modal('show'); 
							$("#pending_acceptance").val(0);
							
							$("#message_text_area").val('');
							$("#message_text_area").focus();
							loadMessages(user_to,0);

							var ajaxSendMess = $.ajax({
								url: "includes/handlers/ajax_send_message.php",
								type: "POST",
								data: "user_to=" + user_to + "&message_body=" + message_body,
								cache:false,
					
								success: function(response){
									$("#message_text_area").val('');
									$("#message_text_area").focus();
									loadMessages(user_to,0);
								}
							});
							
							callback(user_to,message_body);
 						}
 						else if(response == "den"){
 							$('#payment_transaction_rejected').modal('show');
							//alert("Payment error: There was a problem charging the user, for which the messages could not be activated.");
 						}
 						else if(response == "ign"){
 							$('#payment_off').modal('show');
 							
							$("#pending_acceptance").val(0);
							
							$("#message_text_area").val('');
							$("#message_text_area").focus();
							loadMessages(user_to,0);

							var ajaxSendMess = $.ajax({
								url: "includes/handlers/ajax_send_message.php",
								type: "POST",
								data: "user_to=" + user_to + "&message_body=" + message_body,
								cache:false,
					
								success: function(response){
									$("#message_text_area").val('');
									$("#message_text_area").focus();
									loadMessages(user_to,0);
								}
							});
							
							callback(user_to,message_body);
 						}
					}
				});
			}
		}
		else{
			var ajaxSendMess = $.ajax({
				url: "includes/handlers/ajax_send_message.php",
				type: "POST",
				data: "user_to=" + user_to + "&message_body=" + message_body,
				cache:false,
	
				success: function(response){
					$("#message_text_area").val('');
					$("#message_text_area").focus();
					loadMessages(user_to,0);
				}
			});
			
			callback(user_to,message_body);
		}
	}
	
	function changeMessBar(user_to) {
		var ajaxChangeMessBar = $.ajax({
			url: "includes/handlers/ajax_update_message_bar.php",
			type: "POST",
			data: "selected_user=" + user_to,
			cache:false,
	
			success: function(response){
				$("#conversations_selector_bar").hide().html(response).fadeIn();
			}
		});
	}

	function twelveHourFormat() {
		var today = new Date();
		var hours = today.getHours();
		var minutes = today.getMinutes();
		var ampm = hours >= 12 ? 'pm' : 'am';
		hours = hours % 12;
		hours = hours ? hours : 12; // the hour '0' should be '12'
		minutes = minutes < 10 ? '0'+minutes : minutes;
		var strTime = hours + ':' + minutes + ampm;
		return strTime;
	}

	//function connect_ws(){

	//}
	
	$(document).ready(function(){
		var user_to_name = '<?php echo $user_to_fnl;?>';
		
		var user_to = '<?php echo bin2hex($user_to_e); //TODO: cambiar los alert_dot?>';
		var userLoggedIn = '<?php echo bin2hex($userLoggedIn_e); ?>';
		var messagesToken = '<?php echo $_SESSION['messages_token']; ?>';

		//TODO: Comment following line for deployment
		//var websocket = new WebSocket("ws://localhost:8090/confidr/socket_handlers/php-socket.php");
		var websocket = new WebSocket("ws://confidr.com:8090/includes/socket_handlers/php-socket.php"); 
		websocket.onopen = function(event) {
			var initial_connection_messageJSON = {
				chat_user_username: userLoggedIn,
				veri_token: messagesToken
			};
			//alert(JSON.stringify(initial_connection_messageJSON));
			websocket.send(JSON.stringify(initial_connection_messageJSON));
		
			//showMessage("<div class='chat-connection-ack'>Connection is established!</div>");		
		}

		websocket.onmessage = function(event) {
			var Data = JSON.parse(event.data);
			
			if(Data.sender == user_to){
				//the conversation of the sender is open
				
				if('<?php echo $message_availability; ?>' == 0){
					//Reloads the page if the doctor sent a message
					window.location.href = "messages_frame.php?=" + Data.sender;
				}
				
				var item = $('<div class="message" id="green">' + Data.message + '</div><br><br>').hide();
				$('#scroll_messages').append(item);
				item.fadeIn();
				var ddd = document.getElementById("scroll_messages");// Make messages load in inverse order.
				ddd.scrollTop = ddd.scrollHeight;

				if(Data.message.length < 12){
					$('#message_'+user_to).hide().html(Data.message).fadeIn();
				}
				else{
					$('#message_'+user_to).hide().html(Data.message.substring(0,12) + "...").fadeIn();
				}
				$('#time_'+user_to).hide().text(twelveHourFormat()).fadeIn();
			}
			else if($('#first_in_list').val() == Data.sender){
				//the conversation of the sender is not open, but it is the first conversaiton on the list
				if(Data.message.length < 12){
					$('#message_'+Data.sender).hide().html("<b>" +Data.message+"</b>").fadeIn();
				}
				else{
					$('#message_'+Data.sender).hide().html("<b>" +Data.message.substring(0,12) + "..."+"</b>").fadeIn();
				}
				$('#time_'+Data.sender).hide().html("<b>" +twelveHourFormat()+"</b>").fadeIn();
				if($('#alert_dot_'+Data.sender).hasClass("hidden")){
					$('#alert_dot_'+Data.sender).toggleClass('hidden');
				}
			}
			else if('<?php echo $never_used;?>' == 1){
				window.location.href = "messages_frame.php?=" + Data.sender;
			}
			else{
				setTimeout(function(){
					changeMessBar(user_to);						
				}, 1000);
			}
		};
		
		websocket.onerror = function(event){
			//showMessage("<div class='error'>Problem due to some Error</div>");
		};
		websocket.onclose = function(event) {
		    //console.log('Socket is closed. Reconnect will be attempted in 1 second.', event.reason);
		    console.log('Socket is closed. Reload webpage.', event.reason);
		    //setTimeout(function() {
		    	//	connect_ws();
		    //}, 1000);
		  };
		
		if($('#scroll_messages').length > 0){
			loadMessages(user_to,0);
			if('<?php echo $message_availability; ?>' == 0){
				var fee = '<?php
							if($user_to_fnl != ""){
								$user_to_e = $crypt->EncryptU($user_to);
								if(!$user_obj->isDoctor()){
									$settings = new Settings($con, $user_to, $user_to_e);
									$fee = $settings->getSettingsValues("payed_messages_cost");
									echo $fee;
								}
							}
							else{
								echo "";	
							}
							?>';
				var lang = '<?php echo $lang;?>';

				if(lang == 'en'){
		 			var str = '<div class="message payment_request_text"><b>Dr. ' + user_to_name + ' charges a fee of &nbsp;&nbsp;<span id="highlight_payment">' + fee + 'COP</span>&nbsp;&nbsp; for the messages service.</b><br>Acquiring this service enables you to message this doctor for 2 weeks, and is only charged after the doctor replies to you. <br>If the doctor have not replied in 2 days after acquisition, the request will be withdrawn and you will not be charged. </div>';
				}
				else if(lang == 'es'){
					var str = '<div class="message payment_request_text"><b>Dr. ' + user_to_name + ' cobra un valor de &nbsp;&nbsp;<span id="highlight_payment">' + fee + 'COP</span>&nbsp;&nbsp; por sus servicios de mensajes.</b><br>Adquirir este servicio te permite enviarle mensajes a este doctor por 2 semanas. <br>Si el doctor no te responde en 2 dí­as después de ser adquirido, la transacción será cancelada y el valor no te será cobrado.</div>';
				}
				setTimeout(function(){
					$('#scroll_messages').hide().append(str).fadeIn();
					var ddd = document.getElementById('scroll_messages');
					ddd.scrollTop = ddd.scrollHeight;		
				}, 1000);
			}

		}
		
		
		$('#message_submit').on('click',function(){
			var message_body = $("#message_text_area").val();
			var pb = $("#pb").val();

			if(pb == 0){
			
				storeMessageData(user_to, message_body, function(user_to,message_body){
						
					if(message_body.trim() != ""){
						var messageJSON = {
							chat_user_username: userLoggedIn,
							chat_target: user_to,
							chat_message: message_body,
							veri_token: messagesToken
						};
						websocket.send(JSON.stringify(messageJSON));
					}
					
					if($('#first_in_list').val() == user_to){
						if(message_body.length < 12){
							$('#message_'+user_to).hide().html(message_body).fadeIn();
						}
						else{
							$('#message_'+user_to).hide().html(message_body.substring(0,12) + "...").fadeIn();
						}
						$('#time_'+user_to).hide().text(twelveHourFormat()).fadeIn();
					}
					else{
						setTimeout(function(){
							changeMessBar(user_to);						
						}, 1000);
					}
				});
			}
		});

		$('#message_text_area').bind("enterKeyPress",function(e){
			var message_body = $("#message_text_area").val();

			storeMessageData(user_to, message_body, function(user_to,message_body){
				
				if(message_body.trim() != ""){
					var messageJSON = {
						chat_user_username: userLoggedIn,
						chat_target: user_to,
						chat_message: message_body,
						veri_token: messagesToken
					};
					websocket.send(JSON.stringify(messageJSON));
				}

				if($('#first_in_list').val() == user_to){
					if(message_body.length < 12){
						$('#message_'+user_to).hide().html(message_body).fadeIn();
					}
					else{
						$('#message_'+user_to).hide().html(message_body.substring(0,12) + "...").fadeIn();
					}
					$('#time_'+user_to).hide().text(twelveHourFormat()).fadeIn();
				}
				else{
					setTimeout(function(){
						changeMessBar(user_to);						
					}, 1000);
				}
				
			});
		});
		$('#message_text_area').keyup(function(e){
		    if(e.keyCode == 13 && !e.shiftKey)
		    {
		        $(this).trigger("enterKeyPress");
		    }
		});
		
		$('#scroll_messages').bind('scroll', function(){
			var user_to = '<?php echo $crypt->EncryptU($user_to);?>';

			var latest_loaded_message_id = $('#scroll_messages').find('#latest_loaded_message_id').val();
			var ending = $('#scroll_messages').find('#ending').val();
			
			if($(this).scrollTop() == 0 && ending == 0){
				//scrollHeight:tamaÃ±o todo el contenido
				//scrollTop: numero de pixeles que se han scrolleado
				//innerHeight: tamaÃ±o del div
				//$('#loading').show();
				loadMessages(user_to,latest_loaded_message_id);
			}
			return false;
		});

		$('input[type=radio][name=enabled_chat]').change(function() {
			var ajaxChangeEnabled = $.ajax({
				url: "includes/handlers/ajax_change_conversation_enabling.php",
				type: "POST",
				data: "patient_user=" + user_to + "&status=" + this.value,
				cache:false,
		
				success: function(response){
					//alert(response);ajax

				}
			});
		});


		if($('#termination_time').length > 0){
			var SECOND = 1000;
	
 			var remaining_time = document.getElementById('remaining_time');
//			var remaining_time = $('#remaining_time');
	
			function bindClockTick(callback) {
			    function tick() {
			        var now = Date.now();
			        
			        var ret = callback(now);
			        
			        if(ret == 1){
			        		setTimeout(tick, SECOND - (now % SECOND));
			   		}
			    }
			    
			    tick();
			}
	
			bindClockTick(function(ms) {
				var remaining_time = document.getElementById('remaining_time');
				
				curr_remaining_time = $('#termination_time').val() - 1;
				if(curr_remaining_time < 0){
					return 0;
				}
				else if(curr_remaining_time == 0){
					location.reload();
				}
				
				$('#termination_time').val(curr_remaining_time);
				var remainder = 0;
				
				var days = Math.floor(curr_remaining_time/86400);
				remainder = curr_remaining_time % 86400;
				
				var hours = Math.floor(remainder/3600);
				remainder = remainder % 3600;

				var minutes = Math.floor(remainder/60);
				remainder = remainder % 60;

				var seconds = remainder;
				var lang = '<?php echo $lang;?>';
				//alert(lang);
				if(lang == 'en'){
					if(days != 0){
						remaining_time.innerHTML = "Remaining time: " + days + " day(s), " + hours + " hour(s)...";
					}
					else if(hours != 0){
						remaining_time.innerHTML = "Remaining time: " + hours + " hour(s), " + minutes + " minute(s)...";
					}
					else if(minutes != 0){
						remaining_time.innerHTML = "Remaining time: " + minutes + " minute(s), " + seconds + " second(s).";
					}
					else{
						remaining_time.innerHTML = "Remaining time: " + seconds + " second(s).";
					}
				}
				else if(lang == 'es'){
					if(days != 0){
						//remaining_time.val = "Tiempo restante: " + days + " dia(s), " + hours + " hora(s)...";
						remaining_time.innerHTML = "Tiempo restante: " + days + " dia(s), " + hours + " hora(s)...";
					}
					else if(hours != 0){
						remaining_time.innerHTML = "Tiempo restante: " + hours + " hora(s), " + minutes + " minuto(s)...";
					}
					else if(minutes != 0){
						remaining_time.innerHTML = "Tiempo restante: " + minutes + " minuto(s), " + seconds + " segundo(s).";
					}
					else{
						remaining_time.innerHTML = "Tiempo restante: " + seconds + " segundo(s).";
					}
				}

				return 1;
			});
		}
		//connect_ws();

	});

	</script>
	</body>
</html>