<?php 

	include("includes/header.php");
	include("includes/form_handlers/settings_handler.php");
	//if (isset($_SESSION['lang']))
	  //  $lang = $_SESSION['lang'];
    //else
      //  $lang = "es";
?>
<div class="main_column column">
	<div class="title_tabs" ><?php switch($lang){
    		    case("en"):
    		        echo "Settings";
    		        break;
    		    case("es");
                    echo "Configuración";
                    break;
    		}?></div>
	<div class="main_settings settings_div">
    	<h1>
    	<?php 
            	switch($lang) {
            	    case("en"):
            	        echo "Account Settings";
            	        break;
            	    case("es"):
            	        echo "Configuración de tu cuenta";
            	        break;
            	}
    	?>
    	</h1>
    	<div class="container">
        	<?php 
        		echo "<img src= '" . $txt_rep->entities($user_obj->getProfilePicFast()) . "' id='settings_up_pic'>";
        	?>
        	<div class="up_photo">
        		<img src="assets/images/icons/up_photo.png">
        		<a href="upload.php">
        		
        	<?php 
                	switch($lang) {
                	    case("en"):
                	        echo "Upload new profile picture</a>";
                	        break;
                	    case("es"):
                	        echo "Cargar nueva imagen de perfil</a>";
                	        break;
                	}
        	$user_data_query= mysqli_query($con, "SELECT first_name,last_name,email FROM users WHERE username='$userLoggedIn_e'");
        	$arr = mysqli_fetch_array($user_data_query);
        
        	?>
        	</div>
        </div>	
    	<div class="up_basic_info">
        	<?php 
                	switch($lang) {
                	    case("en"):
                	        echo "<h1>Modify the values and click 'Update Details.</h1>";
                	        break;
                	    case("es"):
                	        echo "<h1>Modifica los valores y haz click en 'Actualizar Detalles.</h1>";
                	        break;
                	}
        	?>
        	
        	<form action="settings.php" method="POST">
        	<h2><?php 
        	   switch($lang){
        	       case("en"):
        	           echo "Names:";
        	           break;
        	       case("es"):
        	           echo "Nombres:";
        	           break;
            }?></h2>
            
            <input type="text" name="first_name" value="<?php echo $txt_rep->entities($arr['first_name']);?>" id="settings_input">
            
            <h2><?php 
        	   switch($lang){
        	       case("en"):
        	           echo "Last Name:";
        	           break;
        	       case("es"):
        	           echo "Apellidos:";
        	           break;
            }?></h2>
            <input type="text" name="last_name" value="<?php echo $txt_rep->entities($arr['last_name']);?>" id="settings_input">
        	<h2>Email:</h2> <input type="text" name="email" value="<?php echo $txt_rep->entities($arr['email']); ?>" id="settings_input">
        
        		<?php echo $message; ?>
        
        		<input type="submit" name="update_details" id="save_details" value="<?php 
                    	   switch($lang){
                    	       case("en"):
                    	           echo "Update Details";
                    	           break;
                    	       case("es"):
                    	           echo "Actualizar Detalles";
                    	           break;
                        }?>" class="info settings_submit_buttons">
        	</form>
       	</div> 
       	<div class="up_basic_info">
        	<h1><?php 
        	   switch($lang){
        	       case("en"):
        	           echo "Change Password";
        	           break;
        	       case("es"):
        	           echo "Cambiar contraseña";
        	           break;
            }?></h1>
        
        	<form action="settings.php" method="POST">
        		<h2><?php 
        	       switch($lang){
                	       case("en"):
                	           echo "Old password";
                	           break;
                	       case("es"):
                	           echo "Contraseña antigua";
                	           break;
                }?></h2> <input type="password" name="old_password" value="" id="settings_input">
        		<h2><?php 
        	       switch($lang){
                	       case("en"):
                	           echo "New password";
                	           break;
                	       case("es"):
                	           echo "Contraseña nueva";
                	           break;
                }?></h2><input type="password" name="new_password" value="" id="settings_input">
        		<h2><?php 
        	       switch($lang){
                	       case("en"):
                	           echo "Re-enter new password:";
                	           break;
                	       case("es"):
                	           echo "Confirma la contraseña nueva";
                	           break;
                }?></h2><input type="password" name="new_password2" id="settings_input">
        
        		<?php echo $password_message; ?>
        
        		<input type="submit" name="update_password" id="save_details" value="<?php 
        	   switch($lang){
        	       case("en"):
        	           echo "Update Password";
        	           break;
        	       case("es"):
        	           echo "Actualizar Contraseña";
        	           break;
            }?>" class="info settings_submit_buttons">
        	</form>
        </div>
        <div class="up_basic_info">
        		<h1>
        		<?php 
	        	   switch($lang){
	        	       case("en"):
	        	           echo "Preferred language";
	        	           break;
	        	       case("es"):
	        	           echo "Idioma preferido";
	        	           break;
	            }
	        ?>
            </h1>
	        	<form action = "settings.php" method="POST">
	        		<div class="btn-group">
		        		<?php $current_lang = $settings->getLang();?>
		        		<button type="button" class="btn btn-success <?php if($current_lang=="en"){echo "active";}?>" name="lang_pref_en" id="lang_pref_en">
		            		English
		            </button>
		        		<button type="button" class="btn btn-success <?php if($current_lang=="es"){echo "active";}?>" name="lang_pref_es" id="lang_pref_es">
						Español
					</button>
				</div>
				<br>
	        		<input type="hidden" name="lang_pref_val" id="lang_pref_val" value="<?php echo $current_lang;?>">
	        		<input type="submit" name="update_language" id="save_details" value="<?php 
	        	   switch($lang){
	        	       case("en"):
	        	           echo "Update Language";
	        	           break;
	        	       case("es"):
	        	           echo "Actualizar Idioma";
	        	           break;
	            }?>" class="info settings_submit_buttons">
	        	</form>
        </div>
        <?php if($user_obj->isDoctor()){?>
        <div class="up_basic_info">
        		<h1>
        		<?php 
	        	   switch($lang){
	        	       case("en"):
	        	           echo "Profile Posts Privacy";
	        	           break;
	        	       case("es"):
	        	           echo "Privacidad de Publicaciones en tu Perfil";
	        	           break;
	            }
	        ?>
            </h1>
            
	        	<form action = "settings.php" method="POST" id="privacy_div">
				<?php
					if(!isset($priv_selected)){
						$priv_selected = $settings->getSettingsValues("profile_privacy");
					}
					$checked_prof_priv = ($priv_selected)? "checked":"";
					$checked_prof_publ = (!$priv_selected)? "checked":"";
					switch($lang){
						case("en"):
							echo "<h2>Choosing <i>Private</i> makes posts on your wall only viewable by your connections (<u>Note</u>: This will affect your discoverability, and will make it harder for patients to find you).<br></h2>";
							echo '<input type="radio" name="prof_privacy" value="private" ' . $checked_prof_priv .'> <b>Private</b> <br><br>';
							echo "<h2>Choosing <i>Public</i> allows everyone to see the posts in your wall, even by external search engines which will boost your discoverability.</h2>";
							echo '<input type="radio" name="prof_privacy" value="public" ' . $checked_prof_publ.'> <b>Public </b> ';
							break;
						case("es"):
							echo "<h2>Seleccionar <i>Privado</i> hace que las publicaciones en tu muro solo sean visibles por tus conexiones (<u>Nota</u>: Esto afecta tu descubribilidad y hace más dificil para los pacientes encontrarte).<br></h2>";
							echo '<input type="radio" name="prof_privacy" value="private" ' . $checked_prof_priv .'> <b>Privado</b> <br><br>';
							echo "<h2>Seleccionar <i>Público</i> permite a todos ver las publicaciones en tu muro, incluso a los motores de búsqueda externos, por lo que aumentará tu descubribilidad.</h2>";
							echo '<input type="radio" name="prof_privacy" value="public" ' . $checked_prof_publ.'> <b> Público </b>';
							break;
					}
		        ?>
		        		
				<br>
	        		<input type="submit" name="update_prof_priv" id="save_details" value="<?php 
	        	   switch($lang){
	        	       case("en"):
	        	           echo "Update Privacy";
	        	           break;
	        	       case("es"):
	        	           echo "Actualizar Privacidad";
	        	           break;
	            }?>" class="info settings_submit_buttons">
	        	</form>
        </div>
        <?php }?>	
        <div class="up_basic_info">
        	<h1><?php 
        	   switch($lang){
        	       case("en"):
        	           echo "Close account";
        	           break;
        	       case("es"):
        	           echo "Cerrar cuenta";
        	           break;
            }?></h1>
        	<form action = "settings.php" method="POST">
        		<input type="submit" name="close_account" id="close_account" value="<?php 
        	   switch($lang){
        	       case("en"):
        	           echo "Close account";
        	           break;
        	       case("es"):
        	           echo "Cerrar cuenta";
        	           break;
            }?>" class="danger settings_submit_buttons">
        	</form>
        </div>	
	</div>

</div>
<script>
	$(document).ready(function(){
		//header appearance 
			$('.grey_banner').fadeTo(500,0.8);
			$('.title_text').delay(500).animate({ paddingTop: 98}, 1000);
			$('.wrapper').delay(500).animate({ marginTop: 166}, 1000);	
    });   
</script>

</div>

