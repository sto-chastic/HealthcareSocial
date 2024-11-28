<?php 

	include("includes/header.php");

	if(isset($_POST['cancel'])){
		header("Location: settings.php");
	}

	if(isset($_POST['close_account'])){

		$stmt = $con->prepare("UPDATE users SET user_closed='yes' WHERE username=?");

		$stmt->bind_param("s", $userLoggedIn);
		$stmt->execute();
		$users_details_query = $stmt->get_result();

		//$close_query = mysqli_query($con, "UPDATE users SET user_closed='yes' WHERE username='$userLoggedIn'");
		session_destroy();
		header("Location: register.php");
	}

?>

<div class= "main_column column">
    <div class="title_tabs" ><?php switch($lang){
        		    case("en"):
        		        echo "Settings";
        		        break;
        		    case("es");
                        echo "Configuración";
                        break;
        		}?></div>
	<div class="main_settings">
    	<?php 
    		switch ($lang){
    				 		
    			case("en"):
    	?>
    				<h1>Close Account</h1>
    
    				<h2>Are you sure you want to close your account?</h2>
    				<p>Closing your account will hide your profile and all your activity from other users.<br>
    			
    				You can re-open your account by logging in again in the future.</p>
    	<?php 
    		        break;
    			
    			case("es"):
    	?>
    				<h1>Cerrar Cuenta</h1>
    
    				<h2>¿Estás seguro que desea cerrar tu cuenta?</h2>
    				<p>Cerrar tu cuenta hará que tu perfil no sea visible y esconderá tu actividad.<br>
    			
    				En un futuro, puedes reabrir tu cuenta iniciando sesión.</p>
    	<?php 
    				break;
    		}
    	?>
    	
    
    	<form action="close_account.php" method="POST">
    		<?php 
    			switch ($lang){
    					 		
    				case("en"):
    		?>
    					<input type="submit" name="close_account" id="close_account" value="Close Account" class="danger settings_submit_buttons">
    					<input type="submit" name="cancel" id="update_details" value="Cancel" class="info settings_submit_buttons"><br>
    		<?php 
    			        break;
    				
    				case("es"):
    		?>
    					<input type="submit" name="close_account" id="close_account" value="Cerrar Cuenta" class="danger settings_submit_buttons">
    					<input type="submit" name="cancel" id="update_details" value="Cancelar" class="info settings_submit_buttons"><br>
    		<?php 
    					break;
    			}
    		?>
    
    	</form>
	</div>
</div>
<script>
	$(document).ready(function(){
        $('.grey_banner').fadeTo(1500,0.8);
        $('.title_search').delay(1500).animate({ height: 166, paddingTop: 97, backgroundSize: '100%'}, 2000);	
    });
</script>       