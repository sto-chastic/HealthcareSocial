<?php
	include("includes/header2.php");
	require 'includes/form_handlers/basic_info_form.php';
	require 'includes/form_handlers/premium2_handler.php';

	//Previous info load
	if(isset($_SESSION['lang'])){
	    $lang = $_SESSION['lang'];
	    
	} else $lang = "es";
	
	$stmt = $con->prepare("SELECT ad1nick, ad2nick, ad3nick FROM basic_info_doctors WHERE username=?");
	$stmt->bind_param("s",$userLoggedIn_e);
	$stmt->execute();
	$adnicks = mysqli_fetch_assoc($stmt->get_result());
	
	if($adnicks['ad1nick'] != '' || $adnicks['ad2nick'] != '' || $adnicks['ad3nick'] != ''){
	    header("Location: calendar_settings.php");
	}
	
	$stmt = $con->prepare("SELECT * FROM users WHERE username=?");
	$stmt->bind_param("s",$userLoggedIn_e);
	$stmt->execute();
	$basic_info = mysqli_fetch_array($stmt->get_result());

	//Language Tables:
	
	switch ($lang){
		
		case("en"):
			$months_row_lang = 'months_eng';
			$days_week_row_lang = 'days_short_eng';
			break;
			
		case("es"):
			$months_row_lang = 'months_es';
			$days_week_row_lang = 'days_short_es';
			break;
	}

	
	
	$error_array = [];
	
		
?>
<!-- CSS -->
<link rel="stylesheet" type="text/css" href="assets/css/premium2.css">

<!-- Javascript -->
<script src="assets/js/confidrmap.js"></script>
<script>

	firstNLast = "<?php echo $user_obj->getFirstAndLastName(); ?>";

</script>

<div class= "top_banner_title">
</div>
<div class="top_banner_title_text_container">
	<h1><?php 
	switch($lang){
	    case("en"):
	        echo "Premium Account Creation";
	        break;
	    case("es"):
	        echo "Crea tu cuenta Premium";
	        break;
	}
	?></h1>

	<h2><?php 
	switch($lang){
	    case("en"):
	        echo "Welcome to the integral experience ConfiDr ";
	        break;
	    case("es"):
	        echo "Bienvenido a la experiencia integral  ConfiDr";
	        break;
	}
	?></h2>
</div>

<div class="wrapper">

	<div class="main_column column" id="main_column">
    	<div class="title_tabs" ><?php switch($lang){
    		    case("en"):
    		        echo "Premium";
    		        break;
    		    case("es");
                    echo "Premium";
                    break;
    		}?></div>
		<div id="signup_pg2">
			<form action="premium2.php" method="POST">
				<h3><?php 
				switch($lang){
				    case("en"):
				        echo "Insert your location information for your primary office below";
				        break;
				    case("es"):
				        echo "Inserta la información de tu ubicación principal";
				        break;
				}
				?></h3>
				
				<div class = "location_info">
    					<div class = "location_data">
            				
            				<p><?php 
                				switch($lang){
                				    case("en"):
                				        echo "Street Name";
                				        break;
                				    case("es"):
                				        echo "Dirección línea 1";
                				        break;
                				}
                				?></p>
            				<input type="text" id="prem_ad1ln1" name="prem_ad1ln1" placeholder="<?php 
                				switch($lang){
                				    case("en"):
                				        echo "Ex: St Street 21";
                				        break;
                				    case("es"):
                				        echo "Ej: Cll 50 Cr 2";
                				        break;
                				}
                				?>" value="<?php 
                                if(isset($_SESSION['prem_ad1ln1'])){
                                    echo $txt_rep->entities($_SESSION['prem_ad1ln1']);
                                }
                             ?>" required>
                             <p><?php 
                				switch($lang){
                				    case("en"):
                				        echo "Office Number";
                				        break;
                				    case("es"):
                				        echo "Número de oficina";
                				        break;
                				}
                				?></p>
										    
                        	<input type="text" id="prem_ad1ln2" name="prem_ad1ln2" placeholder="<?php 
                				switch($lang){
                				    case("en"):
                				        echo "Ex: 303";
                				        break;
                				    case("es"):
                				        echo "Ej: 303";
                				        break;
                				}
                				?>" value="<?php 
                                if(isset($_SESSION['prem_ad1ln2'])){
                                    echo $txt_rep->entities($_SESSION['prem_ad1ln2']);
                                }
                             ?>" required>

                      	<p><?php 

                				switch($lang){
                				    case("en"):
                				        echo "Building Name";
                				        break;
                				    case("es"):
                				        echo "Nombre de Edificio";
                				        break;
                				}

                				?></p>   
                         <input type="text" id="prem_ad1ln3" name="prem_ad1ln3" placeholder="<?php 
                				switch($lang){
                				    case("en"):
                				        echo "Ex: Empire State";
                				        break;
                				    case("es"):
                				        echo "Ex: Bacata";
                				        break;
                				}

                				?>" value="<?php 
                            if(isset($_SESSION['prem_ad1ln3'])){
                                echo $txt_rep->entities($_SESSION['prem_ad1ln3']);
                            }
                         ?>" required>
                         <p><?php 
                				switch($lang){
                				    case("en"):
                				        echo "City";
                				        break;
                				    case("es"):
                				        echo "Ciudad";
                				        break;
                				}
                				?></p>
                			
						<!--  	<p style=" font-size: 10px;"> <?php 
                				switch($lang){
                				    case("en"):
                				        echo "(Select one from the dropdown menu)";
                				        break;
                				    case("es"):
                				        echo "(Selecciona una del menú)";
                				        break;
                				}
                				?></p> -->
							
                        	<input type="text" id="prem_ad1cityName" name="prem_ad1city" placeholder="<?php 
                				switch($lang){
                				    case("en"):
                  			        echo "Ex: New York City";
                				        break;
                				    case("es"):
                				        echo "Ej: Bogotá";

                				        break;
                				}
                				?>" value="<?php 
                                if(isset($_SESSION['prem_ad1city'])){
                                    echo $txt_rep->entities($_SESSION['prem_ad1city']);
                                }
                             ?>" required>
						<input type="hidden" name="prem_ad1cityCode" id = "prem_ad1cityCode" value="<?php 
                                if(isset($_SESSION['prem_ad1cityCode'])){
                                    echo $txt_rep->entities($_SESSION['prem_ad1cityCode']);
                                }
                             ?>" required>
                         <div class="style-2" id="premCityResultsBox"></div>
                     	 <p><?php 
            				switch($lang){
            				    case("en"):
            				        echo "State";
            				        break;
            				    case("es"):
            				        echo "Departamento";
            				        break;
            				}
            				?></p> 
                         
                        	 <input type="text" name="prem_ad1adm2" id="prem_ad1adm2" placeholder="<?php 
                				switch($lang){
                				    case("en"):
                				        echo "Ex: New York";
                				        break;
                				    case("es"):
                				        echo "Ej: Cundinamarca";
                				        break;
                				}
                				?>" value="<?php 
                                if(isset($_SESSION['prem_ad1adm2'])){
                                    echo $txt_rep->entities($_SESSION['prem_ad1adm2']);
                                }?>" readonly required>
                                
                          <input type="hidden" id="prem_lat" name="prem_lat" value="" readonly required>
                          <input type="hidden" id="prem_lng" name="prem_lng" value="" readonly required>
                          <input id="address" value="" type="hidden" readonly>
						 <p><?php 
            				switch($lang){
            				    case("en"):
            				        echo "Country";
            				        break;
            				    case("es"):
            				        echo "País";
            				        break;
            				}
            				?></p>  
    					<select name="reg_adcountry" id="reg_adcountry" hidden>
            				<option selected='selected' value='CO'>Colombia</option>
            				<option value='US'>United States</option>
        				</select>						 		    
                          <input id="geocode" type="button" value="<?php 
                				switch($lang){
                				    case("en"):
                				        echo "Set pin location";
                				        break;
                				    case("es"):
                				        echo "Ubicar dirección";
                				        break;
                				}
                				?>">
                    </div>
                    <div class= "location_map"> 
                     	<div class="map_locator">
                				<div id="floating-panel" style="height:0px; width:0px">  
                				</div> 
            					<div id="map">
                				</div>
    						</div>
	    					<script async defer 
	    						src="https://maps.googleapis.com/maps/api/js?key=AIzaSyChTdZYhzn_sWxkG0PhhZ_Z-9NZ5uWu_Is&callback=initLocatorMap">
	    					</script>
    					</div>
				</div>
				
                <div class="prem_ins">
                		<h2><?php
                     switch($lang){
                         case("en"):
                             echo "Select which type of insurance (or insurances) you accept at this location <br><b>(could be more than one if sepparated by commas)</b>";
                             break;
                         case("es"):
                             echo "Escoge cuáles seguros aceptas en esta ubicación <br><b>(puede ser más de uno, si los separas con comas)</b>";
                             break;
                     }
                     	$search_col = "search_" . $lang;
                     ?></h2> 
            			<input  type="text" onkeyup="sanitizeSearchInsurance(this.value, '<?php echo $lang; ?>', '<?php echo $search_col ;?>', 'search_insurance_reg_div', 'search_text_input_insurance1')" placeholder="<?php 
            			switch($lang){
            			    case("en"):
            			        echo "Accepted insurance companies";
            			        break;
            			    case("es"):
            			        echo "Seguros médicos aceptados";
            			        break; 
            			}
            			?>" autocomplete="off" id="search_text_input_insurance1" name="searched_insurance1" value=<?php 
            				if(isset($_SESSION['searched_insurance1'])){
            					echo '"' . $txt_rep->entities($_SESSION['searched_insurance1']) . '"';
            				}
            				else{
            					echo '""';
            				}
            				?>
            			>
            			<?php
            				if(!empty($insu_err_arr1)){
            					$rej_insu = implode(", ", $insu_err_arr1);
            					echo "<div style='margin-top:10px; display: inline-block ;'>
            							<p id='incorrect'>The insurance(s): " . $rej_insu . ", did not match any of the insurance companies in our system, please contact ConfiDr.<br></p>
            						</div>
            						";
            				}
            			?>
            			<div class="style-2" id="search_insurance_reg_div"></div>
            			
					<?php
						
						$search_col = $lang . "_search";
						
						if(FALSE){
							
						//THIS IS INSERTED IN THE REGISTER, not here
                    ?>
            			
            			<h3><?php switch($lang){
            			    case("en"):
            			        echo "You may now select your specializations to be featured in your profile (up to four)";
            			        break;
            			    case("es"):
            			        echo "Ahora puedes seleccionar tus especializaciones para ser mostradas en tu perfil (máximo 4)";
            			        break;
            			}?></h3>
            										
            			<input type="text" onkeyup="sanitizeSearchSpecialization(this.value, '<?php echo $lang; ?>', '<?php echo $search_col ;?>', 'search_specialization_reg', 'search_text_input_specialization','specialization_code')" placeholder="Specialization" autocomplete="off" id="search_text_input_specialization" name="search_text_input_specialization" required>
            			<?php 
//						if(in_array("specialization_not_found", $error_array))
//						echo "<p id='incorrect'>The specialization you inserted, did not match any of the specializations in our system, please contact ConfiDr.<br></p>";
					?>
					<div id="search_specialization_reg"></div>
        				<input type="hidden" name="specialization_code" id="specialization_code">
        				
        				<?php
						}
        				?>
        			</div>
        			
        			<div class="prem_app_cost">
        				<h2><?php switch($lang){
            			    case("en"):
            			        echo "How much do you charge for a first-time visit?<br><b>must be greater than or equal to 10000 (COP)</b>";
            			        break;
            			    case("es"):
            			        echo "¿Cuánto cobras por una visita particular de primera vez?<br><b>debe ser mayor o igual a 10000 (COP)</b>";
            			        break;
            			}?></h2>
            			<input type="number" name="cost_appo" id="cost_appo" placeholder="eg 50000 or 200000 (COP). " step="1"
            				min="10000" max="5000000"
            				value="<?php 
                            if(isset($_SESSION['cost_appo'])){
                                echo $txt_rep->entities($_SESSION['cost_appo']);
                            }
                         ?>" required>
            			
        			</div>
        			<div class="center_3_button_navigation" style="margin-top: 0px;">
            			<input type="submit" name="premium2_button_doctor" value="<?php
            			switch($lang){
            			    case("en"):
            			        echo "Register";
            			        break;
            			    case("es"):
            			        echo "Registrarme";
            			        break;
            			}
            			?>" id="premium2_button_doctor">
            			</div>	
            		</div>
		</form>
	</div>
</div>