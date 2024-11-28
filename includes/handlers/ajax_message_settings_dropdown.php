<?php
include("../../config/config.php");
include("../classes/Settings.php");
include("../classes/Message.php");
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
		$userLoggedIn = $temp_user;
		$userLoggedIn_e = $temp_user_e;
		$lang = $_SESSION['lang']; 
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
	
	//$user_obj = new User($con, $userLoggedIn);
	$txtrep = new TxtReplace();
	$settings_obj = new Settings($con, $userLoggedIn, $userLoggedIn_e);
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}


if(isset($_POST['payed_messages'])) {
	echo $settings_obj->setSettingsValues_integers('payed_messages', $_POST['payed_messages']);
}
if(isset($_POST['cost_input'])) {
	echo $settings_obj->setSettingsValues_integers('payed_messages_cost', $_POST['cost_input']);
}

if(isset($_POST['payed_messages']) || isset($_POST['cost_input'])){
	exit;
}

$payed_messages_active = $settings_obj->getSettingsValues('payed_messages');
$payed_messages_cost = $settings_obj->getSettingsValues('payed_messages_cost');
?>

<?php 
	switch ($lang){
			 		
		case("en"):
?>
			<a href="javascript:void(0);" onclick="closeDropdownMessageSettings()">
				<div style =" background-color: #f5f5f5" class="delete_button">
					X
				</div>
			</a>
			<h3>Messages Settings</h3>
			
			<form class="message_settings_post" action="" method="POST">
				<div class="messages_settings_block">
					<h1>
					Paid Messaging Service
					</h1>
					<input type="radio" name="payed_messages" id="payed_messages" value="1" <?php echo ($payed_messages_active)? "checked":"";?>> <b>Enabled</b>
					<input type="radio" name="payed_messages" id="payed_messages" value="0" <?php echo ($payed_messages_active)? "":"checked";?>> <b>Disabled <div class="info_icon" style="  float: right; top: -4px;">i<span class="tip">Enabling this, allows you to charge your patients a fee in order to message you. Conversely, disabling it allows any patient to message you for free. </span></div></b>
				</div>
				<div class="messages_settings_block">
					<p>
					Messaging Service Price. Keep in mind that from the introduced price will be deducted a 15% ConfiDr. fee.
					</p>
					<input type="number" name="cost_input" id="cost_input" placeholder="(COP)" required value="<?php 
					
						if($payed_messages_cost != ''){
							echo $payed_messages_cost;	
						}
					
					?>">
				</div>
			</form>
			<div class="div_button" id="save_message_settings_but">
				Save Settings
			</div>
<?php 
		        break;
		
		case("es"):
?>
			<a href="javascript:void(0);" onclick="closeDropdownMessageSettings()">
				<div style =" background-color: #f5f5f5" class="delete_button">
					X
				</div>
			</a>
			
			<h3>Configuración de Mensajes</h3>
			
			<form class="message_settings_post" action="" method="POST">
				<div class="messages_settings_block">
					<h1>
					Servicio de Pago de Mensajes
					</h1>
					<input type="radio" name="payed_messages" id="payed_messages" value="1" <?php echo ($payed_messages_active)? "checked":"";?>> <b>Habilitado</b>
					<input type="radio" name="payed_messages" id="payed_messages" value="0" <?php echo ($payed_messages_active)? "":"checked";?>> <b>Deshabilitado <div class="info_icon" style="float: right;top: -4px;">i<span class="tip">Habilitado te permite cobrar a los pacientes un valor por enviarte mensajes. Por otro lado, deshabilitado permite a cualquier paciente escribirte gratis. </span></div></b>
				</div>
				<div class="messages_settings_block">
					<p>
					Precio de Servicio de Mensajes. Ten en cuenta que al precio introducido se descontará cargo de 15% de ConfiDr.
					</p>
					<input type="number" name="cost_input" id="cost_input" placeholder="(COP)" required value="<?php 
					
						if($payed_messages_cost != ''){
							echo $payed_messages_cost;	
						}
					
					?>">
				</div>
			</form>
			<div class="div_button" id="save_message_settings_but">
				Guardar
			</div>
<?php 
			break;
	}
?>



<script>
	$(document).ready(function(){
		$('#save_message_settings_but').on("click",function(){
			$.ajax({
				type: "POST",
				url: "includes/handlers/ajax_message_settings_dropdown.php",
				data: $('form.message_settings_post').serialize(), //What we send!
				success: function(msg) {
					closeDropdownMessageSettings();
				},
				error: function() {
					alert("Could not be saved at this time, please try again later.");
				}
			});
		});
	});
</script>