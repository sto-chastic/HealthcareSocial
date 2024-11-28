<?php  
	include('includes/profile_header.php');
	

	if(!empty($error_array)){
		echo <<<EOS
				<script>
					function tabChangerLogReg(tab){
   						$('#register_login_nav_tabs a[href="#' + tab + '"]').tab('show');
					};
				</script>
EOS;
		if(in_array("Incorect Email or Password.<br>", $error_array)){
			echo "
				<script>
					$(document).ready(function(){
						$('#confirm_appo_sele').modal('show');
						tabChangerLogReg('login_modal_div');
						tabChanger('sched_appo');
					})
				</script>";
		}
		else{
			echo "
				<script>
					$(document).ready(function(){
						$('#confirm_appo_sele').modal('show');
						tabChangerLogReg('register_modal_div');
						tabChanger('sched_appo');
					})
				</script>";
		}

	}
	
	if(isset($_SESSION['confirm_email_alert'])){
		if($_SESSION['confirm_email_alert'] == 1){
			echo "
					<script>
						$(document).ready(function(){
							tabChanger('sched_appo');
						})
					</script>";
		}
	}

	if(isset($_SESSION['lang'])){
	    $lang = $_SESSION['lang'];
	}
	else{
		$lang="es";
		$_SESSION['lang'] = "es";
	}
    
    //echo $lang;
    
	if(isset($_POST['remove_connection'])){
		$user_obj->removeFriend($username);
		//header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

	}

	if(isset($_POST['request_connection'])){
		$user_obj->sendRequest($username);
	}

	if(isset($_POST['respond_request'])){
		header("Location: requests.php");
	}

?>	
	<style type="text/css">
		.wrapper{
			margin-left: 0px;
			padding-left:0px;
			margin: 0;
			z-index: 0;
		    min-height: 100vh
		}
		
	</style>

	<div class="profile_left">

		
		<?php
		
			if($profile_user_obj->isClosed()){
				header("Location: user_closed.php");
			} 
			
			if(!$not_logged){
				if($userLoggedIn != $username){
						
					echo '<form action="' . bin2hex($txt_rep->entities($username_e)) .'" method="POST">';
					switch($lang){
					    case("en"):{
	            				if($user_obj->isFriend($username)){
	            					echo '<input type="submit" name="remove_connection" class="danger" value="Remove Connection"><br>';
	            				}
	            				elseif($user_obj->didSendRequest($username)){
	            					echo '<input type="submit" name="request_sent" class="default" value="Request Sent"><br>';
	            				}
	            				elseif($user_obj->isDoctor()){
	            					if($user_obj->didReceiveRequest($username)){
	            						echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
	            					}
	            					else{
	            						echo '<input type="submit" name="request_connection" class="success" value="Request Connection"><br>';
	            					}
	            				}
	            				else{
	            					echo '<input type="submit" name="request_connection" class="success" value="Request Connection"><br>';
	            				}
	            				break;
					    }
					    case("es"):{
					        if($user_obj->isFriend($username)){
					            echo '<input type="submit" name="remove_connection" class="danger" value="Remover Conexión"><br>';
					        }
					        elseif($user_obj->didSendRequest($username)){
					            echo '<input type="submit" name="request_sent" class="default" value="Solicitud Enviada"><br>';
					        }
					        elseif($user_obj->isDoctor()){
					            if($user_obj->didReceiveRequest($username)){
					                echo '<input type="submit" name="respond_request" class="warning" value="Responder a Solicitud"><br>';
					            }
					            else{
					                echo '<input type="submit" name="request_connection" class="success" value="Solicitar Conexión"><br>';
					            }
					        }
					        else{
					            echo '<input type="submit" name="request_connection" class="success" value="Solicitar Conexión"><br>';
					        }
					        break;
					    }
					}
					echo "</form>";
	 			}
	
	// 			if($user_obj->isFriend($username) == true){
	// 			    switch($lang){
	// 			        case "en":{
	// 			            echo "<input type='submit' class='deep_blue' data-toggle='modal' data-target='#post_form' value='Post'>";
	// 			            break;
	// 			        }
	// 			        case "es":{
	// 			            echo "<input type='submit' class='deep_blue' data-toggle='modal' data-target='#post_form' value='Publicar'>";
	// 			            break;
	// 			        }
	// 			    }
	// 			}
				if($userLoggedIn != $username){
					$mutualFriends = $user_obj->getMutualFriends($username);
					echo '<div class="profile_info_button">';
					switch($lang){
					    case("en"): {
	            				if($mutualFriends == 1)
	            					echo $txt_rep->entities($mutualFriends) . " mutual connection.";
	            				else 
	            					echo $txt_rep->entities($mutualFriends) . " mutual connections.";
	            				echo '</div>';
	            				break;
					    }
					    case("es"): {
					        if($mutualFriends == 1)
					            echo $txt_rep->entities($mutualFriends) . " conexión en común.";
					            else
					                echo $txt_rep->entities($mutualFriends) . " conexiones en común.";
					                echo '</div>';
					                break;
					    }
					}
				}
			}
		?>
		
		<div class="profile_left_info">
			<div id="country" class="profile_left_info_element" >
				<?php 
				switch ($lang){
					case "en":
						echo "Country: ";
						break;
					case "es":
						echo "País: ";
						break;
				}
				?>
				<h1>
				<?php 
					$profile_country = $profile_user_obj->getCountry_Doctor();
					switch ($lang){
						case "es":{
							switch($profile_country){
								case "CO":
									echo "Colombia";
									break;
								case "US":
									echo "Estados Unidos";
									break;
							}
							break;
						}
						case "en":{
							switch($profile_country){
								case "CO":
									echo "Colombia";
									break;
								case "US":
									echo "United States";
									break;
							}
							break;
						}
					}
				?>
				</h1>
			</div>
			<div id="offices" class="profile_left_info_element">
				<?php 
				
				//Doctor offices load
				$stmt = $con->prepare("SELECT * FROM basic_info_doctors WHERE username=?");
				$stmt->bind_param("s",$username);
				$stmt->execute();
				$query = $stmt->get_result();
				$basic_doc_info = mysqli_fetch_assoc($query);
				if($basic_doc_info['ad1ln1']!=""){
        				switch ($lang){
        				    case "en":
        				        echo "<h1>Offices</h1>";
        				        break;
        				    case "es":
        				        echo "<h1>Consultorios</h1>";
        				        break;
        				}
				}
				for($i = 1; $i <= 3; $i++){
					$nick_label = "ad" . $i . "nick";
					if($basic_doc_info[$nick_label] ==""){
						continue;
					}
					
					//Get city info
					$of_city_label = "ad" . $i ."city";
					
					$stmt = $con->prepare("SELECT city, adm2 FROM cities_CO WHERE city_code=?");
					$stmt->bind_param("s",$basic_doc_info[$of_city_label]);
					$stmt->execute();
					$query = $stmt->get_result();
					
					$city_q_arr = mysqli_fetch_assoc($query);
					$of_city = $city_q_arr['city'];
					$of_adm2 = $city_q_arr['adm2'];
					
					//Get address info
					$of_addr1_label = "ad" . $i ."ln1";
					$of_addr2_label = "ad" . $i ."ln2";
					$of_addr3_label = "ad" . $i ."ln3";
					
					//Get telephone
					
					$phone_table = $profile_user_obj->getPhoneTable();
					
					$query_str = "SELECT `telephone` FROM $phone_table WHERE id=?";
					
					$stmt = $con->prepare($query_str);
					$stmt->bind_param("i",$i);
					$stmt->execute();
					
					$phone_query = $stmt->get_result();
					$phone_arr = mysqli_fetch_array($phone_query);
					if ($basic_doc_info[$of_addr1_label]!=""){
        					switch ($lang){ 
        						case "en":
        						    echo "<div id='right_office_".$i."'><h3>Office " . $i . "</h3>";
        							echo "<span>" . $basic_doc_info[$of_addr1_label] . " <br>" . $basic_doc_info[$of_addr3_label]. ", " . $basic_doc_info[$of_addr2_label]."</span><br>";
        							echo "<span>" . $of_city . ", " . $of_adm2 ."</span>";
        							break;
        						case "es":
        							echo "<div id='right_office_".$i."'><h3>Consultorio " . $i . "</h3>";
        							echo "<span>" . $basic_doc_info[$of_addr1_label] . " <br>" . $basic_doc_info[$of_addr3_label]. ", " . $basic_doc_info[$of_addr2_label]."</span><br>";
        							echo "<span>" . $of_city . ", " . $of_adm2 ."</span>";
        							break;
        					}
        					
        					if($phone_arr != ""){
        						
        						switch ($lang){
        							case "en":
        								echo "<p>Tel: ";
        								echo $txt_rep->entities($phone_arr['telephone']) ."</p></div>";
        								break;
        							case "es":
        								echo "<p>Tel: ";
        								echo $txt_rep->entities($phone_arr['telephone'])."</p></div>";
        								break;
        						}
        					}
					}
					
				}
					
				
				?>
			</div>
		</div>
	
    </div>

	<div class="right_column column">
		<div class="div_awards">		
    		<h3> <?php 
    		switch($lang){
    		    case("en"):
    		        echo "Awards";
    		        break;
    		    case("es"):
    		        echo "Premios";
    		        break;
    		}?> </h3>
    		
    		<input type="hidden" name="loading_awards" id="loading_awards" value="0">
    		<div id="profile_award_container" class="style-2">
    		</div>
    		
    	</div>
		
	</div>

	<div class="profile_main_column column">

		<ul class="nav nav-tabs" role="tablist" id="profileTabs">
		  <li role="presentation" <?php if ($my_profile || $hideCalendar) echo "class='active'";?>><div class="arrow-down"></div><a href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab"><span id="home_tab">hol</span> 
		  <?php switch($lang){
		      case("en"):
		          echo "Wall";     
		          break;
		      case("es"):
		          echo "Muro";
		          break;
		  }?>
		  </a></li>
		<?php 
        if(!$my_profile){
            ?>
            <li role="presentation" <?php if (!$hideCalendar) echo "class='active'";?>><div class="arrow-down"></div><a href="#sched_appo" aria-controls="sched_appo" role="tab" data-toggle="tab"><span id="my_calendar_tab">hol</span>
            <?php 
            switch($lang){
                case("en"):  
                    echo 'Appointments';
                    break;
                case("es"):
                    echo 'Citas';
                    break;
            }   ?>
            </a></li>
            <?php  
        }
        ?>
		  <li role="presentation"><div class="arrow-down"></div><a href="#about_div" aria-controls="about_div" role="tab" data-toggle="tab"><span id="about_tab">hol</span>
		  <?php switch($lang){
		      case("en"):
		      	  echo "About ". $profile_user_obj->getFirstNameShort(30);
		          break;
		      case("es"):
		      	  echo "Acerca de ". $profile_user_obj->getFirstNameShort(30);
		          break;
		  }?></a></li>
		  
		  <li role="presentation"><div class="arrow-down"></div><a href="#messages_profile" aria-controls="messages_profile" role="tab" data-toggle="tab"><span id="chat_tab">hol</span>
		  <?php switch($lang){
		      case("en"):
		          echo "Messages";     
		          break;
		      case("es"):
		          echo "Mensajes";
		          break;
		  }?></a></li>
		</ul>
			<script>
		 	var isMyProfile ='<?php echo $my_profile;?>';
		 	var isNotLogged ='<?php echo $not_logged;?>'; 

                if(isMyProfile){
                		$('.nav-tabs > li'). css('width', '33.33%');
                }
                else{
                		$('.nav-tabs > li'). css('width', '25%');
                }
			</script>

		<div class="tab-content">
			<div role="tabpanel" <?php echo ($my_profile || $hideCalendar)? 'class="tab-pane active in"':'class="tab-pane"'?> id="newsfeed_div">
			<?php if(!$not_logged){?>
				<?php if($user_obj->isFriend($username) || $my_profile){?>
	    				<form class="post_form" action="" id="profile_post" method="POST">
		    			
		    			 	<?php
		    			 	$post_area_plchldr = "";
		    			 	if ($my_profile){
		    			 	    if($lang=="en"){
		    			 	        $post_area_plchldr="Share here updates, photos, articles, scientific studies... Do NOT share private or sensible data!";
		    			 	    } else if($lang=="es") {
		    			 	        $post_area_plchldr="Comparte aquí actualizaciones, fotos, artículos, estudios cientificos... ¡NO compartas información privada o sensible!";
		    			 	    }
		    			 	} else {
		    			 	    if($lang=="en"){
		    			 	        $post_area_plchldr="This will appear publicly on the user's profile page and their newsfeed.";
		    			 	    } else if($lang=="es") {
		    			 	        $post_area_plchldr="Esto aparecerá públicamente en el perfil del usuario y sus noticias.";
		    			 	    }
		    			 	    
		    			 	}
		    			 	switch ($lang){
		    			 		
		    			 		case("en"):
		    					 	?>
		    						<textarea name="profile_post_body" id="profile_post_body"
		    							placeholder="<?php echo $post_area_plchldr;?>" required></textarea>
		    						<input type="hidden" name="profile_owner" id="profile_owner" value="<?php echo bin2hex($txt_rep->entities($username_e)); ?>" readonly>
		    						<input type="hidden" name="profile_owner_h" id="profile_owner_h" value="<?php echo bin2hex($txt_rep->entities($username_e)); ?>" readonly>
		    						<input type="submit" name="post" id="submit_profile_post" style="top:30px;" value="Share"></input>
		    						<?php 
		    						break;
		    		
		    					case("es"):
		    						?>
		    						<textarea name="profile_post_body" id="profile_post_body"
		    							placeholder="<?php echo $post_area_plchldr;?>" required></textarea>
		    						<input type="hidden" name="profile_owner" id="profile_owner" value="<?php echo bin2hex($txt_rep->entities($username_e)); ?>" readonly>
		    						<input type="hidden" name="profile_owner_h" id="profile_owner_h" value="<?php echo bin2hex($txt_rep->entities($username_e)); ?>" readonly>
		    						<input type="submit" name="post" id="submit_profile_post" style="top:30px;" value="Compartir"></input>
		    						<?php 
		    						break;
		    			 	}
		    			 	?>
		    				
		    			</form>
		    		<?php }?>
    			<?php }?>
			 	<div class="posts_area">
        			 	<center>
        					<img id="loading" src="assets/images/icons/logowhite.gif">
        				</center>
			 	</div>
			</div>
			<?php if(!$my_profile){?>
			<div role="tabpanel" class="tab-pane fade <?php if(!$hideCalendar) echo "in active";?>" id="sched_appo">


                <div class="title_alert" <?php if(!$hideCalendar){
				    echo "style='display:none;'";
				}    
                ?>><?php switch($lang){
                    case("en"):
                        echo "This doctor is not a Premium Doctor, so this function is not currently available.";
                        break;
                    case("es"):
                        echo "Este doctor no es un Doctor Premium, por lo cual esta función no está disponible en el momento.";
                        break;
                }?>
                </div>
				<div class="title_tabs"><?php switch($lang){
                    case("en"):
                        echo "Book Appointments";
                        break;
                    case("es"):
                        echo "Agendar citas";
                        break;
                }?></div>
                
                <div class="schedule_background" <?php if($hideCalendar){
        				    echo "style='display:none;'";
        				}?>>
        				<div class="profile_calendar_block" 
        				<?php if($hideCalendar){
        				    echo "style='display:none;'";
        				}else{
        				    echo "style='display:inline-block;'";
        				}
                        ?>">
                            <div class="calendar_subtitle"  <?php if($hideCalendar){
                                echo "style='display:none;'";
            				}    
                            ?>><?php switch($lang){
                                case("en"):
                                    echo "Select a date";
                                    break;
                                case("es"):
                                    echo "Escoge una fecha";
                                    break;
                                }?>
                           </div>
                           <p style="font-family: 'Coves-Bold'; color: #777; margin-bottom: 23px;" <?php if($hideCalendar){
            				    echo 'display:none;';
            				}    
                            ?>><?php switch($lang){
                                case("en"):
                                    echo "Select the best date avaliable";
                                    break;
                                case("es"):
                                    echo "Selecciona la mejor fecha disponible";
                                    break;
                                }?></p>
        					<div style="display: inline-block; width: 100%;" >
        						<div class="month_selector">
        							
        							<?php
        								$curr_month_lang_query = mysqli_query($con, "SELECT $months_row_lang FROM months WHERE id='$current_month'");
        								$arr = mysqli_fetch_array($curr_month_lang_query);
        								$curr_month_lang = $arr[$months_row_lang];
        							?>
        							<select name="month_selected" id="month_selected">
        								<?php
        									$drop_dwn_month = "";
        									$query = mysqli_query($con, "SELECT $months_row_lang,id FROM months ORDER BY id ASC");
        									foreach($query as $arr){
        										if($curr_month_lang == $arr[$months_row_lang]){
        											$drop_dwn_month = $drop_dwn_month . "<option selected='selected' value='" . $arr['id'] . "'>" . $arr[$months_row_lang] . "</option>";
        										}
        										else{
        											$drop_dwn_month = $drop_dwn_month . "<option value='" . $arr['id'] . "'>" . $arr[$months_row_lang] . "</option>";
        										}
        									}
        									if(!$hideCalendar){
        									   echo $drop_dwn_month;
        									}
        								?>
        							</select>
        						</div>
        						<div class="year_selector">
        							
        							<select name="year_selected" id="year_selected">
        							<?php 
        							$drop_dwn_year = "";
        							$year_lim = date('Y', strtotime('+2 years'));
        							$years_q = mysqli_query($con, "SELECT DISTINCT y FROM calendar_table WHERE y<$year_lim AND y>=$current_year");
        							foreach($years_q as $arr){
        								if($current_year == $arr['y']){
        									$drop_dwn_year= $drop_dwn_year. "<option selected='selected' value='" . $arr['y'] . "'>" . $arr['y'] . "</option>";
        								}
        								else{
        									$drop_dwn_year= $drop_dwn_year. "<option value='" . $arr['y'] . "'>" . $arr['y'] . "</option>";
        								}
        							}
        							if(!$hideCalendar){
        							     echo $drop_dwn_year;
        							}
        							?>
        							</select>
        						</div>
        					</div>
        					<div class='calendar_header_container'>
        						<?php
        							$days_str = "";
        							$week_days_lang_query = mysqli_query($con, "SELECT $days_week_row_lang FROM days_week ORDER BY dw ASC ");
        		
        							foreach ($week_days_lang_query as $value_day) {
        								$days_str = $days_str . "<div class='calendar_day_block' id='header_day'>" . $value_day[$days_week_row_lang] . "</div>";
        							}
        							if(!$hideCalendar){
        							     echo $days_str;
        							}
        						?>
        					</div>
        		
        					<?php 
        					if(!$hideCalendar){
        					    echo "<div class='calendar_container'>
        					           </div>";
        					}
        					?>
                				<div class="schedule_type" <?php if($hideCalendar){
                				    echo "style='display:none;'";
                				}    
                                ?>>
        
                					<p><?php switch($lang){
                                    case("en"):
                                        echo "What type of appointment do you need?";
                                        break;
                                    case("es"):
                                        echo "¿Qué tipo de cita necesitas?";
                                        break;
                                    }?></p>
                                    <div id="appo_arrow">
                    					<select name = "appo_type" id = "appo_type">
                    		
                    						<?php					
                    							$appo_duration_tab = $profile_user_obj->getAppoDurationTable();
                    							$drop_dwn = "";
                    							$query = mysqli_query($con, "SELECT appo_type,duration,id FROM $appo_duration_tab WHERE deleted = 0 ORDER BY appo_type ASC");
                    							foreach($query as $key => $arr){
                    								if($key == 0){
                    									$ini_appo_id = $txt_rep->entities($arr['id']);
                    								}
                    								$drop_dwn = $drop_dwn . "<option value='" . $txt_rep->entities($arr['id']) . "'>" . $txt_rep->entities($arr['appo_type']) . "</option>";
                    							}
                    							if(!$hideCalendar){
                                                    echo $drop_dwn;
                    							}
                    						?>
                    		
                    					</select>
                    				</div>	
                					
                					<p><?php switch($lang){
                                    case("en"):
                                        echo "Select payment type:";
                                        break;
                                    case("es"):
                                        echo "Escoge el método de pago";
                                        break;
                                    }?></p>
                					
                					<?php
                					if(!$hideCalendar){
                						if(!$not_logged){
	                						$user_insurance = $user_obj->getInsuranceCompany_Patient();
	                						
	                						$insurances_tab = $user_obj->getInsurancesTable();
	                						$stmt = $con->prepare("SELECT $lang FROM $insurances_tab WHERE id=?");
	                						$stmt->bind_param("s",$user_insurance);
	                						$stmt->execute();
	                						$_q_ins = $stmt->get_result();
	                						$_arr_ins = mysqli_fetch_assoc($_q_ins);
	                						$user_insurance_name = $_arr_ins[$lang];
                						}
                						else{
                							$user_insurance = "";
                						}
                						
                					}
                					?>
                				<div id="payment_arrow">
                					<select name = "payment_type" id = "payment_type">
                		
                						<?php
                						if(!$hideCalendar){
                							if($user_insurance == ''){
                								switch($lang){
                									case "en":
                										echo "<option selected='selected' value='part'>Cash</option>";
                										break;
                									case "es":
                										echo "<option selected='selected' value='part'>Efectivo</option>";
                										break;
                								}
                								
                								$payment_method = 'part';
                							}
                							elseif($user_insurance == $country . '00'){
                								switch($lang){
                									case "en":
                										echo "<option selected='selected' value='part'>Cash</option>";
 			 										break;
                									case "es":
                										echo "<option selected='selected' value='part'>Efectivo</option>";
                										break;
                								}
                								$payment_method = 'part';
                							}
                							else{
                								switch($lang){
                									case "en":
                										echo "<option selected='selected' value='part'>Cash</option>";
                										break;
                									case "es":
                										echo "<option selected='selected' value='part'>Efectivo</option>";
                										break;
                								}
                								echo "<option selected='selected' value='" . $user_insurance . "'>" . $user_insurance_name . "</option>";
                								$payment_method = $user_insurance;
                							}
                						}
                						?>
                		
                					</select> 
                					<div class="info_icon" style="right: -21px; position:absolute; left: auto;">i<span class="tip"> <u><?php
//                 						switch($lang){
// 	                                    case("en"):
// 	                                        echo "ConfiDr Pay:</u> Is a service we provide for paying consults when you do not have insurance or do not want to use it. You will be required to insert a payment method if you do not have one stablished already. <br> <u>Insurance:</u> The insurance option shown can be changed in 'Settings'. </span>";
// 	                                        break;
// 	                                    case("es"):
// 	                                        echo "ConfiDr Pay:</u> Es un servicio que proveemos para que pagues tus consultas cuando no tienes seguro o no lo quieres usar. Deberás insertar un método de pago si no has establecido uno.<br> <u>Seguro:</u> La opción de seguro se puede cambiar en 'Configuración'. </span>";
// 	                                        break;
//                                     }
	                					switch($lang){
	                						case("en"):
	                							echo "Cash:</u> Select this if you do not have an insurance or do not intend to use it. You will pay directly to the doctor. <br> <u>Insurance:</u> If you have selected an insurance (selected in <i>Medical Info</i>), and this insurance is accepted by the doctor, this option will be enabled. Notice some insurance have some fees which you should pay directly to the doctor.</span>";
	                							break;
	                						case("es"):
	                							echo "Efectivo:</u> Selecciona esta opción si no tienes seguro o no lo quieres usar. Deberás pagar el valor de la consulta directamente al doctor.<br> <u>Seguro:</u> Si seleccionaste un seguro (se selecciona en <i>Info. Médica</i>), y este seguro es aceptado por este doctor, esta opción será habilitada. Algunos seguros requieren el pago de un deducible que deberá ser pagado directamente al doctor.</span>";
	                							break;
	                					}
                                    ?>
                				
                					</div>
                				</div>
    				
        				</div>
        			</div>

				<div id="day_container_div_profile">
					<?php
					if(!$hideCalendar){
					    echo "<iframe src='day_frame.php?d=" . $current_day . "&m=" . $current_month . "&y=" . $current_year . "&pm=" . $payment_method . "&po=" . bin2hex($txt_rep->entities($username_e)) . "&at=" . $ini_appo_id . "' id='day_iframe' frameborder='0' scrolling='no' ></iframe>";
					}
					?>
				</div>
			</div>
			</div>		
			<?php }//closing the if for if it is my profile?>
			<div role="tabpanel" class="tab-pane fade <?php //if ($hideCalendar) echo "in active";?>" id="about_div">
				<div class="title_tabs" <?php if($hideCalendar){
				    echo "style='display:none;'";
				}    
                ?> ><?php switch($lang){
                    case("en"):
                        echo "Profesional experience";
                        break;
                    case("es"):
                        echo "Experiencia profesional";
                        break;
                }?></div>
            <div class="profile_background"> 
				<div id="jobs_div">
				<?php
				switch($lang){
				        case("en"):{
				            $str = "<h4> Experience </h4>";
				            $str .= "<div class='about_info_holder_box style-2' id='jobs_editable_list'></div>";
				            if($my_profile){
				                $str .= <<<EOS
        									<a href="javascript:void(0);" onclick="show_add_about_info('jobs');">
        										<p  id='add_jobs_link'>Add</p>
        									</a>
        									<div class='about_line_holderbox' id='add_jobs_holder' style=" height: auto; display: none;">
        										<h5>Add a Job</h5>
        										<input type="text" name="job_title" id="job_title" placeholder="Job Title" maxlength="70" required>
        										<input type="text" name="job_institution" id="job_institution" placeholder="Institution" maxlength="70" required>
        										<input type="text" name="job_start_date" id="job_start_date" placeholder="Start Date" style="cursor: pointer;" readonly>
        										<input type="text" name="job_end_date" id="job_end_date" placeholder="End Date" style="cursor: pointer;" readonly>
        										<a href="javascript:void(0);" onclick="add_about_info('job');">
        											<div class="small_1char_butt">+</div>
        										</a>
                                                <div class="title_plus_check"><b>  Current Job?</b><input type="checkbox" name="job_current_check" id="job_current_check"></div>
        										<a href="javascript:void(0);" onclick="hide_add_about_info('jobs');add_about_info('job');">
        											<p>Done</p>
        										</a>
        									</div>
EOS;
				            }
				            break;
				        }
				        case("es"):{
				            $str = "<h4> Experiencia </h4>";
				            $str .= "<div class='about_info_holder_box style-2' id='jobs_editable_list'></div>";
				            if($my_profile){
				                $str .= <<<EOS
        									<a href="javascript:void(0);" onclick="show_add_about_info('jobs');">
        										<p  id='add_jobs_link'>Agregar</p>
        									</a>
        									<div class='about_line_holderbox' id='add_jobs_holder' style=" height: auto; display: none;">
        										<h5>Agregar un empleo</h5>
        										<input type="text" name="job_title" id="job_title" placeholder="Cargo" maxlength="70" required>
        										<input type="text" name="job_institution" id="job_institution" placeholder="Institución" maxlength="70" required>
        										<input type="text" name="job_start_date" id="job_start_date" placeholder="Fecha de inicio" style="cursor: pointer;" readonly>
        										<input type="text" name="job_end_date" id="job_end_date" placeholder="Fecha de terminación" style="cursor: pointer;" readonly>
          										<a href="javascript:void(0);" onclick="add_about_info('job');">
        											<div class="small_1char_butt">+</div>
        										</a>
                                                <div class="title_plus_check"><b>  ¿Es tu trabajo actual?</b><input type="checkbox" name="job_current_check" id="job_current_check"></div>
        										<a href="javascript:void(0);" onclick="hide_add_about_info('jobs');add_about_info('job');">
        											<p>Terminar</p>
        										</a>
        									</div>
EOS;
				            }
				            break;
				        }
				}
					echo $str;
				?>
				</div>
				<hr>
				<div id="education_div">
				<?php
				switch($lang){
				    case("en"):{
				        $str = "<h4> Education </h4>";
				        $str .= "<div class='about_info_holder_box style-2' id='education_editable_list' ></div>";
				        if($my_profile){
				            $str .= <<<EOS
									<a href="javascript:void(0);" onclick="show_add_about_info('education');">
										<p id='add_education_link'>Add</p>
									</a>
									<div class='about_line_holderbox' id='add_education_holder' style=" height: auto; display: none;">
										<h5>Add an Education</h5>
										<input type="text" name="edu_title" id="edu_title" placeholder="Obtained Title" maxlength="70" required>
										<input type="text" name="edu_institution" id="edu_institution" placeholder="Institution" maxlength="70" required>
										<input type="text" name="edu_start_date" id="edu_start_date" placeholder="Start Date" style="cursor: pointer;" readonly>
										<input type="text" name="edu_end_date" id="edu_end_date" placeholder="Graduation Date" style="cursor: pointer;" readonly>
										<a href="javascript:void(0);" onclick="add_about_info('education');">
											<div class="small_1char_butt">+</div>
										</a>
										<a href="javascript:void(0);" onclick="hide_add_about_info('education');add_about_info('education');">
											<p>Done</p>
										</a>
									</div>
EOS;
				        }
				        break;
				    }
				    case("es"):{
				        $str = "<h4> Educación </h4>";
				        $str .= "<div class='about_info_holder_box style-2' id='education_editable_list'></div>";
				        if($my_profile){
				            $str .= <<<EOS
									<a href="javascript:void(0);" onclick="show_add_about_info('education');">
										<p id='add_education_link'>Agregar</p>
									</a>
									<div class='about_line_holderbox' id='add_education_holder' style=" height: auto; display: none;">
										<h5>Agrega a tu educación</h5>
										<input type="text" name="edu_title" id="edu_title" placeholder="Título Obtenido" maxlength="70" required>
										<input type="text" name="edu_institution" id="edu_institution" placeholder="Institución" maxlength="70" required>
										<input type="text" name="edu_start_date" id="edu_start_date" placeholder="Fecha de inicio" style="cursor: pointer;" readonly>
										<input type="text" name="edu_end_date" id="edu_end_date" placeholder="Fecha de grado" style="cursor: pointer;" readonly>
										<a href="javascript:void(0);" onclick="add_about_info('education');">
											<div class="small_1char_butt">+</div>
										</a>
										<a href="javascript:void(0);" onclick="hide_add_about_info('education');add_about_info('education');">
											<p>Terminar</p>
										</a>
									</div>
EOS;
				        }
				        break;
				    }
				}
				echo $str;
				
				?>
				</div>
				<hr>
				<div id="websites_div">
				<?php
			    switch($lang){
			        case("en"):{
			            
			            $str = "<h4> Websites </h4>";
			            $str .= "<div class='about_info_holder_box style-2' id='websites_editable_list'></div>";
			            if($my_profile){
			                $str .= <<<EOS
									<a href="javascript:void(0);" onclick="show_add_about_info('websites');">
							 			<p id='add_websites_link'>Add</p>
									</a>
									<div class='about_line_holderbox' id='add_websites_holder' style=" height: auto; display: none;">
										<h5>Add a Website</h5>
                                        <div id="network_arrow">
										  <select name="webpage_code" id="webpage_code">
										  
EOS;
			                $query = mysqli_query($con,"SELECT * FROM webpages");
			                while($arr = mysqli_fetch_assoc($query)){
			                    $str .= "<option value='" . $arr['web_page_code'] . "'>" . $arr['name'] . "</option>";
			                }
			                $str .= <<<EOS
										  </select>
                                        </div>
										<input type="text" name="webpage_url" id="webpage_url" placeholder="URL / User (ex: www.mypersonalwebsite.com)" maxlength="100" required>
										<a href="javascript:void(0);" onclick="add_about_info('webpage');">
											<div class="small_1char_butt">+</div>
										</a>
										<a href="javascript:void(0);" onclick="hide_add_about_info('websites');add_about_info('webpage');">
											<p>Done</p>
										</a>
									</div>
EOS;
			            }
			            break;
			        }
			        case("es"):{
			       
			            $str = "<h4> Portales Web </h4>";
			            $str .= "<div class='about_info_holder_box style-2' id='websites_editable_list'></div>";
			            if($my_profile){
			                $str .= <<<EOS
									<a href="javascript:void(0);" onclick="show_add_about_info('websites');">
							 			<p id='add_websites_link'>Agregar</p>
									</a>
									<div class='about_line_holderbox' id='add_websites_holder' style=" height: auto; display: none;">
										<h5>Agregar una página web</h5>
										
										<div id="network_arrow">
										  <select name="webpage_code" id="webpage_code">
										  
EOS;
			                $query = mysqli_query($con,"SELECT * FROM webpages");
			                while($arr = mysqli_fetch_assoc($query)){
			                    $str .= "<option value='" . $arr['web_page_code'] . "'>" . $arr['name'] . "</option>";
			                }
			                $str .= <<<EOS
										  </select>
                                        </div>
										<input type="text" name="webpage_url" id="webpage_url" placeholder="URL / Usuario (ej: www.mipaginapersonal.com)" maxlength="100"  required>
										<a href="javascript:void(0);" onclick="add_about_info('webpage');">
											<div class="small_1char_butt">+</div>
										</a>
										<a href="javascript:void(0);" onclick="hide_add_about_info('websites');add_about_info('webpage');">
											<p>Terminar</p>
										</a>
									</div>
EOS;
			            }
			            break;
			        }
						
			    }
					echo $str;
				?>
				</div>
				<hr>
				<div id="publications_div">
				<?php
				switch($lang){
				    case("en"):{
				        $str = "<h4>Featured Publications</h4>";
				        $str .= "<div class='about_info_holder_box style-2' id='publications_editable_list'></div>";
				        if($my_profile){
				            $str .= <<<EOS
									<a href="javascript:void(0);" onclick="show_add_about_info('publications');">
										<p id='add_publications_link'>Add</p>
									</a>
									<div class='about_line_holderbox' id='add_publications_holder' style=" height: auto; display: none;">
										<h5>Add a Publication</h5>
										<input type="text" name="publi_title" id="publi_title" placeholder="Title" maxlength="200" required>
										<input type="text" name="publi_authors" id="publi_authors" placeholder="Main Authors" maxlength="250" required>
										<input type="text" name="publi_journal" id="publi_journal" placeholder="Journal" maxlength="200" required>
										<input type="text" name="publi_volume" id="publi_volume" placeholder="Vol., Pg." maxlength="50" required>
										<input type="text" name="publi_date" id="publi_date" placeholder="Date" style="cursor: pointer;" onfocus="(this.type='date',this.placeholder='')" readonly>
										<a href="javascript:void(0);" onclick="add_about_info('publication');">
											<div class="small_1char_butt">+</div>
										</a>
										<a href="javascript:void(0);" onclick="hide_add_about_info('publications');add_about_info('publication');">
											<p>Done</p>
										</a>
									</div>
EOS;
				        }
				        break;
				    }
				    case("es"):{
				        $str = "<h4>Publicaciones Destacadas</h4>";
				        $str .= "<div class='about_info_holder_box style-2' id='publications_editable_list'></div>";
				        if($my_profile){
				            $str .= <<<EOS
                                        <a href="javascript:void(0);" onclick="show_add_about_info('publications');">
										<p id='add_publications_link'>Agregar</p>
									</a>
									<div class='about_line_holderbox' id='add_publications_holder' style=" height: auto; display: none;">
										<h5>Agrega una publicación</h5>
										<input type="text" name="publi_title" id="publi_title" placeholder="Título" maxlength="200" required>
										<input type="text" name="publi_authors" id="publi_authors" placeholder="Autores principales" maxlength="250" required>
										<input type="text" name="publi_journal" id="publi_journal" placeholder="Revista" maxlength="200" required>
										<input type="text" name="publi_volume" id="publi_volume" placeholder="Vol., Pg." maxlength="50" required>
										<input type="text" name="publi_date" id="publi_date" placeholder="Fecha" style="cursor: pointer;" onfocus="(this.type='date',this.placeholder='')" readonly>

										<a href="javascript:void(0);" onclick="add_about_info('publication');">
											<div class="small_1char_butt">+</div>
										</a>
										<a href="javascript:void(0);" onclick="hide_add_about_info('publications');add_about_info('publication');">
											<p>Terminar</p>
										</a>
									</div>
EOS;
				        }
				        break;
				    }
				}
				echo $str;
				
				?>
				</div>
				<hr>
				<div id="conferences_div">
				<?php
				switch($lang){
				    case("en"):{
				        $str = "<h4>Conferences Attended</h4>";
				        $str .= "<div class='about_info_holder_box style-2' id='conferences_editable_list' ></div>";
				        if($my_profile){
				            $str .= <<<EOS
									<a href="javascript:void(0);" onclick="show_add_about_info('conferences');">
										<p id='add_conferences_link' >Add</p>
									</a>
									<div class='about_line_holderbox' id='add_conferences_holder' style=" height: auto; display: none;">
										<h5>Add a Conference Attended</h5>
										<input type="text" name="conf_title" id="conf_title" placeholder="Conference Title" maxlength="70" required>
										<input type="text" name="conf_role" id="conf_role" placeholder="Role (ex: expositor)" maxlength="70" required>
										<input type="text" name="conf_date" id="conf_date" placeholder="Date" style="cursor: pointer;" readonly>
										<a href="javascript:void(0);" onclick="add_about_info('conference');">
											<div class="small_1char_butt">+</div>
										</a>
										<a href="javascript:void(0);" onclick="hide_add_about_info('conferences');add_about_info('conference');">
											<p>Done</p>
										</a>
									</div>
EOS;
				        }
				        break;
				    }
				    case("es"):{
				        $str = "<h4>Conferencias Asistidas</h4>";
				        $str .= "<div class='about_info_holder_box style-2' id='conferences_editable_list' ></div>";
				        if($my_profile){
				            $str .= <<<EOS
									<a href="javascript:void(0);" onclick="show_add_about_info('conferences');">
										<p id='add_conferences_link'>Agregar</p>
									</a>
									<div class='about_line_holderbox' id='add_conferences_holder' style=" height: auto; display: none;">
										<h5>Agrega una Conferencia Asistida</h5>
										<input type="text" name="conf_title" id="conf_title" placeholder="Título de Conferencia" maxlength="70" required>
										<input type="text" name="conf_role" id="conf_role" placeholder="Rol (ej: expositor)" maxlength="70" required>
										<input type="text" name="conf_date" id="conf_date" placeholder="Fecha" style="cursor: pointer;" readonly>
										<a href="javascript:void(0);" onclick="add_about_info('conference');">
											<div class="small_1char_butt">+</div>
										</a>
										<a href="javascript:void(0);" onclick="hide_add_about_info('conferences');add_about_info('conference');">
											<p>Terminar</p>
										</a>
									</div>
EOS;
				        }
				        break;
				    }
				}
				echo $str;
					
				?>
				</div>	
			</div>
		</div>	
			<?php if(!$not_logged){?>
			<div role="tabpanel" class="tab-pane fade" id="messages_profile">
			<div class="title_tabs" <?php if($hideCalendar){
				    echo "style='display:none;'";
				}    
                ?> ><?php switch($lang){
                    case("en"):
                        echo "Quick Chat";
                        break;
                    case("es"):
                        echo "Quick Chat";
                        break;
                }?></div>
				<?php 
					if($my_profile){
						echo "<iframe src='messages_frame.php' id='messages_frame' frameborder='0' scrolling='no' ></iframe>";
					}
					else{
						echo "<iframe src='messages_frame.php?u=" .bin2hex($crypt->EncryptU($username))."&f=1' id='messages_frame' frameborder='0' scrolling='no' ></iframe>";
					}
				?>
				
			</div>
			<?php 
			}
			else{
			?>
			<div role="tabpanel" class="tab-pane fade" id="messages_profile">
			<div class="title_tabs" <?php if($hideCalendar){
				    echo "style='display:none;'";
				}    
                ?> ><?php switch($lang){
                    case("en"):
                        echo "Quick Chat";
                        break;
                    case("es"):
                        echo "Quick Chat";
                        break;
                }?></div>

					<iframe src='createaccount_frame.php?t=messages' id='messages_frame' frameborder='0' scrolling='no' ></iframe>
				
			</div>
			
			
			<?php 	
			}
			?>
		</div>
	</div>
</div>

	<!-- Modal -->
	<script>

		var pu_e = '<?php echo bin2hex($crypt->EncryptU($username)); ?>'; //profile we are on
		var profileUsername = '<?php echo bin2hex($txt_rep->entities($username_e)); ?>'; //profile we are on
		var my_profile = '<?php echo $my_profile ?>';
		
		var curr_day = '<?php echo $txt_rep->entities($current_day); ?>';
		var curr_month = '<?php echo $txt_rep->entities($current_month); ?>';
		var curr_year = '<?php echo $txt_rep->entities($current_year); ?>';
		var payment_method = $("#payment_type").find(":selected").val();
		
		var selected_appo_id = $("#appo_type").find(":selected").val();

		$(document).ready(function(){
			loadAwards(0);
			$('#profile_award_container').bind('scroll', function(){
				//alert($('#loading_awards').val());
				if($('#loading_awards').val() == 0){
					var latest_loaded_element_id = $(this).find('#latest_loaded_element_id_').val();
					var ending = $(this).find('#ending_').val();
	
					if($(this).scrollTop() + $(this).innerHeight() >=  $(this)[0].scrollHeight && ending == 0){

						$('#loading_awards').val(1);
						//scrollHeight:tamaño todo el contenido scrollWidth()
						//scrollTop: numero de pixeles que se han scrolleado scrollLeft()
						//innerHeight: tamaño del div  scrolltop + innerheight = scrollheight innerWidth()
						loadAwards(latest_loaded_element_id);
					}
					return false;
				}
			});
			if(my_profile){//This runs if this is the user's profile

				//Initial Load About info

				read_about_info('education');
				read_about_info('job');
				read_about_info('conference');
				read_about_info('description');
				read_about_info('webpage');
				read_about_info('publication');
				//calendar
				$( function() {					
					//edu start_date menu
					$( "#edu_start_date" ).datepicker({ minDate: "-80Y", maxDate: -0, changeMonth: true,
					      changeYear: true, numberOfMonths:1, dateFormat: "dd-mm-yy"});
					//edu end_date menu
					$( "#edu_end_date" ).datepicker({ minDate: "-80Y", maxDate: -0, changeMonth: true,
					      changeYear: true, numberOfMonths:1, dateFormat: "dd-mm-yy"});

					//job start_date menu
					$( "#job_start_date" ).datepicker({ minDate: "-80Y", maxDate: -0, changeMonth: true,
					      changeYear: true, numberOfMonths:1, dateFormat: "dd-mm-yy"});
					//job end_date menu
					$( "#job_end_date" ).datepicker({ minDate: "-80Y", maxDate: -0, changeMonth: true,
					      changeYear: true, numberOfMonths:1, dateFormat: "dd-mm-yy"});

					//conference date menu
					$( "#conf_date" ).datepicker({ minDate: "-80Y", maxDate: -0, changeMonth: true,
					      changeYear: true, numberOfMonths:1, dateFormat: "dd-mm-yy"});

					//publications date menu
					$( "#publi_date" ).datepicker({ minDate: "-80Y", maxDate: -0, changeMonth: true,
					      changeYear: true, numberOfMonths:1, dateFormat: "dd-mm-yy"});
				      
					//Get the current date in a dateobject
					var currentDate = new Date();
					//Offset the current date by how many days you need
					currentDate.setDate(currentDate.getDate());
				});

				//checkbox hide end date

				$('#job_current_check').change(function() {
				    if($('#job_current_check').is(":checked")){
				    		$('#job_end_date').css({"display" : "none"});
				    }
				    else{
				    		$('#job_end_date').css({"display" : "inline-block"});
				    }
				});

			}
			else{
				//Initial Load About info
				
				read_about_info('education',profileUsername);
				read_about_info('job',profileUsername);
				read_about_info('conference',profileUsername);
				read_about_info('description',profileUsername);
				read_about_info('webpage',profileUsername);
				read_about_info('publication',profileUsername);
				
			}

			$("#year_selected").change(function(){
				getProfileCalendar();
			});

			$("#month_selected").change(function(){
				getProfileCalendar();
			});
			
		//POSTS LOADING FUNCTION -----------------
			$('#loading').show();
			//Original Ajax request for loading first posts
			$.ajax({
				url: "includes/handlers/ajax_load_profile_posts.php",
				type: "POST",
				data: "page=1&profileUsername=" + pu_e,
				cache:false,

				success: function(data){
					$('#loading').hide();
					$('.posts_area').html(data);
				}
			});
			
			$(window).scroll(function(){
				var height = $('.posts_area').height(); //Div containing posts, not used
				var scroll_top = $(this).scrollTop();
				var page = $('.posts_area').find('.nextPage').val();//only finds the input .nextPage located in posts_area and gets its value.
				var noMorePosts = $('.posts_area').find('.noMorePosts').val();

				if((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false'){
					$('#loading').show();
					
					//Ajax call again
					var ajaxReq = $.ajax({
						url: "includes/handlers/ajax_load_profile_posts.php",
						type: "POST",
						data: "page=" + page + "&profileUsername=" + pu_e,
						cache:false,

						success: function(response){
							$('.posts_area').find('.nextPage').remove(); //Removes current .nextpage
							$('.posts_area').find('.noMorePosts').remove(); //Removes current next page

							$('#loading').hide();
							$('.posts_area').append(response);
						}
					});

				}

				return false;
			}); 
			
		//CALENDAR LOADING FUNCTION -----------------


		//Appointment duration change
			$("#appo_type").change(function(){
				selected_appo_id = $("#appo_type").find(":selected").val();
				payment_method = $("#payment_type").find(":selected").val();
				
				var month = $("#month_selected").find(":selected").val();
				var year = $("#year_selected").find(":selected").val();
				if ($('#selected_day_inp').val() != "")
					var day = $('#selected_day_inp').val();
				else
					var day = curr_day;

				var profileUsername = '<?php echo bin2hex($txt_rep->entities($username_e)); ?>'; //profile we are on
				var ajaxreq = $.ajax({
					url: "includes/handlers/ajax_profile_calendar.php",
					type: "POST",
					data: "profile_user=" + pu_e + "&month=" + month + "&year=" + year + "&payment_method=" + payment_method + "&appo_type_id=" + selected_appo_id,
					cache: false,

					success: function(response){
						$(".calendar_container").html(response);
					}
				});

			//Day selection change

				$("#day_iframe").attr('src', "day_frame.php?d=" + day + "&m=" + month + "&y=" + year + "&pm=" + payment_method + "&po=" + profileUsername + "&at=" + selected_appo_id);

			});
			
			//payment type change
			$("#payment_type").change(function(){
				selected_appo_id = $("#appo_type").find(":selected").val();
				payment_method = $("#payment_type").find(":selected").val();
				
				var month = $("#month_selected").find(":selected").val();
				var year = $("#year_selected").find(":selected").val();
				if ($('#selected_day_inp').val() != "")
					var day = $('#selected_day_inp').val();
				else
					var day = curr_day;

				var profileUsername = '<?php echo bin2hex($txt_rep->entities($username_e)); ?>'; //profile we are on
				var ajaxreq = $.ajax({
					url: "includes/handlers/ajax_profile_calendar.php",
					type: "POST",
					data: "profile_user=" + pu_e + "&month=" + month + "&year=" + year + "&payment_method=" + payment_method + "&appo_type_id=" + selected_appo_id,
					cache: false,

					success: function(response){
						$(".calendar_container").html(response);
					}
				});
			
			//Day selection change
				
				$("#day_iframe").attr('src', "day_frame.php?d=" + day + "&m=" + month + "&y=" + year + "&pm=" + payment_method + "&po=" + profileUsername + "&at=" + selected_appo_id);
				
			});
			var ajaxprofcal = $.ajax({
				url: "includes/handlers/ajax_profile_calendar.php",
				type: "POST",
				data: "profile_user=" + pu_e + "&month=" + curr_month + "&year=" + curr_year + "&payment_method=" + payment_method + "&appo_type_id=" + selected_appo_id,
				cache: false,

				success: function(response){
					$(".calendar_container").html(response);
				}
			});
		});

	</script>
<?php //TODO?>
	<script>
		
		function getProfileCalendar(){
			var month = $("#month_selected").find(":selected").val();
			var year = $("#year_selected").find(":selected").val();

			var payment_method = $("#payment_type").find(":selected").val();
			var profileUsername = '<?php echo bin2hex($txt_rep->entities($username_e)); ?>'; //profile we are on
			var selected_appo_id = $("#appo_type").find(":selected").val();

			var ajaxreq = $.ajax({
				url: "includes/handlers/ajax_profile_calendar.php",
				type: "POST",
				data: "profile_user=" + pu_e + "&month=" + month + "&year=" + year + "&payment_method=" + payment_method + "&appo_type_id=" + selected_appo_id,
				cache: false,

				success: function(response){
					$(".calendar_container").html(response);
				}
			});

		}

		function loadAwards(latest_loaded_element_id){
			var profile_user = '<?php echo bin2hex($txt_rep->entities($username_e)); ?>';
			$.ajax({
				url: "includes/handlers/ajax_profile_award_load.php",
				type: "POST",
				data: "profile_user=" + profile_user + "&latest_element_id=" + latest_loaded_element_id,
				cache:  false,
				//async: false,

				success: function(response){
					$('#profile_award_container').find('#latest_loaded_element_id_').remove();
					$('#profile_award_container').find('#ending_').remove();
		
					if(latest_loaded_element_id == 0){
						$('#profile_award_container').hide().html(response).fadeIn();
					}
					else{
						$('#profile_award_container').append(response).fadeIn();
					}

					$('#loading_awards').val(0);
				}
			});

		}

		function loadAwardsSync(latest_loaded_element_id){
			var profile_user = '<?php echo bin2hex($txt_rep->entities($username_e)); ?>';


			return $.ajax({
				url: "includes/handlers/ajax_profile_award_load.php",
				type: "POST",
				data: "profile_user=" + profile_user + "&latest_element_id=" + latest_loaded_element_id,
				cache:  false,

				success: function(response){
					$('#profile_award_container').find('#latest_loaded_element_id_').remove();
					$('#profile_award_container').find('#ending_').remove();
		
					if(latest_loaded_element_id == 0){
						$('#profile_award_container').hide().html(response).fadeIn();
					}
					else{
						$('#profile_award_container').append(response).fadeIn();
					}
				}
			});

		}
	
	</script>
	
	
<?php $isLoggedIn = !$not_logged;?>
	
<div class="modal fade" id="confirm_appo_sele" tabindex="-1"
	role="dialog" aria-labelledby="postModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">
					<?php
						$just_registered = 0;
						if (isset ( $_SESSION ['confirm_email_alert'] )) {
							if ($_SESSION ['confirm_email_alert'] == 1) {
								$just_registered = 1;
							}
						}
						
						if (! isset ( $_SESSION ['one_appointment_limit'] )) {
							$one_appointment_limit = 1;
						} else {
							if ($_SESSION ['one_appointment_limit'] == 1) {
								$one_appointment_limit = 1;
							} else {
								$one_appointment_limit = 0;
							}
						}
						
						if (($isLoggedIn && ! $just_registered) || ($isLoggedIn && $just_registered && ! $one_appointment_limit)) {
							switch ($lang){
								
								case("en"):
									echo "Confirm Appointment Selection";
									break;
									
								case("es"):
									echo "Confirmar la Cita Seleccionada";
									break;
							}
						} else {
							switch ($lang){
								
								case("en"):
									echo "Register / Login";
									break;
									
								case("es"):
									echo "Registrarse / Iniciar Sesión";
									break;
							}
							
						}
					?>
      			</h4>
			</div>
      
      	<?php
      	
			if (($isLoggedIn && ! $just_registered) || ($isLoggedIn && $just_registered && ! $one_appointment_limit)) {
                echo '<div class="modal-body">';
                switch ($lang){
		              
                    case("en"):
                        echo '<p>You are selecting an appointment with Dr. <b><span
    						id="modal_doctor_name"></span></b> from <b><span
    						id="modal_display_time"></span></b> on <b><span
    						id="modal_appo_date"></span></b>. This appointment will be held at
    					<b><span id="modal_addln1"></span></b>, (Office:) <b><span
    						id="modal_addln3"></span></b>. <br> Insurance: <b><span
    						id="modal_insurance"></span></b></p>';
                        break;
                    case("es"):
                        echo '<p>Estás seleccionando una cita con el Dr. <b><span
    						id="modal_doctor_name"></span></b> desde las <b><span
    						id="modal_display_time"></span></b> el <b><span
    						id="modal_appo_date"></span></b>. Esta cita será en 
    					<b><span id="modal_addln1"></span></b>, (Oficina:) <b><span
    						id="modal_addln3"></span></b>. <br> Seguro / Prepagada / EPS : <b><span
    						id="modal_insurance"></span></b></p>';
                        break;
                }
                
                echo '			
                    </div>
            			<form class="book_appointment_form" action="" method="POST">
            				<input type="hidden" name="year" value=""> <input type="hidden"
            					name="month" value=""> <input type="hidden" name="day" value=""> <input
            					type="hidden" name="profile_owner" value=""> <input type="hidden"
            					name="payment_method" value=""> <input type="hidden" name="ap_id"
            					value=""> <input type="hidden" name="ap_st" value=""> <input
            					type="hidden" name="ap_end" value="">
            			</form>
            			<div class="modal-footer" id="schedule_message">';

                
				switch ($lang){
						 		
					case("es"):
			
						echo '
                        <button type="button" class="btn btn-primary"
							name="accept_appointment_booking" id="accept_appointment_booking">Agendar Cita</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>';
	
				        break;
					
                    case("en"):
                        echo '
						<button type="button" class="btn btn-primary"
							name="accept_appointment_booking" id="accept_appointment_booking">Book Appointment</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
			 
						break;
				}
				
			echo '</div>';
		
		} elseif ($isLoggedIn && $just_registered && $one_appointment_limit) {
		
			
    			echo '<div class="modal-body">
    				<p>';

    					switch ($lang){
    							 		
    						case("en"):
                            echo '
    							You have not yet confirmed your email. Only 1 appointment can be
    							scheduled without confirming your email.<br> Please check your
    							email and click on the link provided to confirm.';

    					        break;
    						
    						case("es"):
                            echo '
    							Aún no has confirmado tu correo. Sólo 1 cita puede ser
    							agendada sin confirmar el correo.<br> Revisa tu
    							correo y has click en el link enviado para confirmar.';

    							break;
    					}

            echo '
    				</p>
    			</div>
    			<div class="modal-footer"
    				id="schedule_message">
            ';

			switch ($lang){
					 		
				case("en"):
		            echo '
					<button type="button" class="btn btn-primary" data-dismiss="modal">Accept</button>';

			        break;
				
				case("es"):
                    echo '
					<button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>';
		
					break;
			}

    			echo '	
    			</div>';
		} else {
		    echo '
			<div class="modal-body">
            ';

			switch ($lang){
					 		
				case("en"):
                    echo '
					<p>In order to book an appointment you must be a registered user. If
					you are registered, please login and try again.</p>';
			        break;
				
				case("es"):
                    echo '
					<p>Para agendar una cita debes ser un usuario registrado. Si ya estás
					registrado, inicia sesión e intenta de nuevo.</p>';
					break;
			}

			echo '
			</div>
			<ul class="nav nav-tabs" role="tablist" id="register_login_nav_tabs">';

			switch ($lang){
					 		
				case("en"):
                    echo '
					<li role="presentation" class="active"><div class="arrow-down"></div>
						<a href="#register_modal_div" aria-controls="register_modal_div"
						role="tab" data-toggle="tab"> <span id="reg_tab"></span> Register</a></li>
					<li role="presentation"><div class="arrow-down"></div> <a
						href="#login_modal_div" aria-controls="login_modal_div" role="tab"
						data-toggle="tab"> <span id="log_tab"></span>Login</a></li>';

			        break;
				
				case("es"):
                    echo '
					<li role="presentation" class="active"><div class="arrow-down"></div>
						<a href="#register_modal_div" aria-controls="register_modal_div"
						role="tab" data-toggle="tab"> <span id="reg_tab"></span> Registrarse</a></li>
					<li role="presentation"><div class="arrow-down"></div> <a
						href="#login_modal_div" aria-controls="login_modal_div" role="tab"
						data-toggle="tab"> <span id="log_tab"></span>Iniciar Sesión</a></li>';

					break;
			}

            echo '
			</ul>
			<div class="tab-content">
				<div role="tabpanel" class="tab-pane fade in active"
					id="register_modal_div">
					<form action="docsearch.php" method="POST">
					<div class="modal_tag_block">
    						<input id="input_modal" type="text" name="reg_fname" placeholder="';
    						
			switch ($lang){
				
				case("en"):
					echo "First Name";
					break;
					
				case("es"):
					echo "Nombres";
					break;
			}
            echo '" value="';
            if (isset ( $_SESSION ['reg_fname'] )) {
                echo $txt_rep->entities ( $_SESSION ['reg_fname'] );
            }
    			echo '" required>';
                        
            switch ($lang){
            	
                	case("en"):
                		if (in_array( "Your first name must be between 2 and 25 characters.<br>", $error_array ))
                			echo "<p id='wrong_input'>Your first name must be between 2 and 25 characters.<br></p>";
            			else if (in_array ( "Your name can only have 1 first name and 1 middle name maximum.<br>", $error_array ))
            				echo "<p id='wrong_input'>Your name can only have 1 first name and 1 middle name maximum.<br></p>";
                		break;
                		
                	case("es"):
                		if (in_array ( "Your first name must be between 2 and 25 characters.<br>", $error_array ))
                			echo "<p id='wrong_input'>Tu primer nombre debe ser entre 2 y 25 caracteres.<br></p>";
            			else if (in_array ( "Your name can only have 1 first name and 1 middle name maximum.<br>", $error_array ))
            				echo "<p id='wrong_input'>Solo puedes ingresar un primer nombre y un segundo nombre máximo.<br></p>";
                		break;
            }
    
            echo'
					</div>
					<div class="modal_tag_block">
                        <input id="input_modal"  type="text" name="reg_lname"
    							placeholder="';
    							
			switch ($lang){
			
				case("en"):
					echo "Last Name";
					break;
					
				case("es"):
					echo "Apellidos";
					break;
			}
            echo '" value="';
            
			if (isset ( $_SESSION ['reg_lname'] )) {
				echo $txt_rep->entities ( $_SESSION ['reg_lname'] );
			}
    								
            echo '" required>';
                        

            switch ($lang){
            	
            	case("en"):
            		if (in_array ( "Your last name must be between 2 and 25 characters.<br>", $error_array ))
            			echo "<p id='wrong_input'>Your last name must be between 2 and 25 characters.<br></p>";
				else if (in_array ( "Your last name can only have your family name and a second family name maximum.<br>", $error_array ))
					echo "<p id='wrong_input'>Your last name can only have your family name and a second family name maximum.<br></p>";
            		break;
            		
            	case("es"):
            		if (in_array ( "Your last name must be between 2 and 25 characters.<br>", $error_array ))
            			echo "<p id='wrong_input'>Tu apellido debe tener entre 2 y 25 caracteres.<br></p>";
            		else if (in_array ( "Your last name can only have your family name and a second family name maximum.<br>", $error_array ))
            			echo "<p id='wrong_input'>Solo puedes ingresar máximo 2 apellidos.<br></p>";
            		break;
            }
    
    			echo '
					</div>
					<div class="modal_tag_block">
                        <input id="input_modal" type="email" name="reg_email"
    							placeholder="';
    							
    							
			switch ($lang){
				
				case("en"):
					echo "Email";
					break;
					
				case("es"):
					echo "Correo";
					break;
			}
    			echo '" value="';
			if (isset ( $_SESSION ['reg_email'] )) {
				echo $txt_rep->entities ( $_SESSION ['reg_email'] );
			}
    			echo '" required>
    					</div>	 
					<div class="modal_tag_block">	
						<input id="input_modal"  type="email" name="reg_email2"
							placeholder="';
							
			switch ($lang){
				
				case("en"):
					echo "Confirm Email";
					break;
					
				case("es"):
					echo "Confirmar Correo";
					break;
			}
            echo '" value="';
            
			if (isset ( $_SESSION ['reg_email2'] )) {
				echo $txt_rep->entities ( $_SESSION ['reg_email2'] );
			}
            echo '" required>';
                    
            switch ($lang){
                	
                	case("en"):
                		if (in_array ( "Email already in use.<br>", $error_array ))
                			echo "<p id='wrong_input'>Email already in use.<br></p>";
                		else if (in_array ( "Email Invalid Format.<br>", $error_array ))
                			echo "<p id='wrong_input'>Email Invalid Format.<br></p>";
                		else if (in_array ( "Emails don't match.<br>", $error_array ))
                			echo "<p id='wrong_input'>Emails don't match.<br></p>";
                		break;
                		
                	case("es"):
                		if (in_array ( "Email already in use.<br>", $error_array ))
                			echo "<p id='wrong_input'>El correo ya existe.<br></p>";
                		else if (in_array ( "Email Invalid Format.<br>", $error_array ))
                			echo "<p id='wrong_input'>Formato incorrecto.<br></p>";
                		else if (in_array ( "Emails don't match.<br>", $error_array ))
                		    echo "<p id='wrong_input'>Los correos no concuerdan.<br></p>";
                		break;
            }

			echo '
					</div>
					<div class="modal_tag_block">
                        <input id="input_modal" type="password" name="reg_passwrd"
    							placeholder="';
    							
			switch ($lang){
				
				case("en"):
					echo "Password";
					break;
					
				case("es"):
					echo "Contraseña";
					break;
			}
    			echo '" required>
    				</div>			
    				<div class="modal_tag_block">			 
    					<input id="input_modal"  type="password"
    							name="reg_passwrd2" placeholder="'; 
			switch ($lang){
				
				case("en"):
					echo "Confirm Password";
					break;
					
				case("es"):
					echo "Confirmar Contraseña";
					break;
			}
            echo '" required>';
                          
                        
            switch ($lang){
                        	
                	case("en"):
                		if (in_array ( "Your passwords do not match.<br>", $error_array ))
                			echo " <p id='wrong_input'> Your passwords do not match.<br></p>";
                		else if (in_array ( "Your password must only contain characters and numbers.<br>", $error_array ))
                			echo "<p id='wrong_input'> Your password must only contain characters and numbers.<br></p>";
                		else if (in_array ( "Your password must be between 10 and 30 characters.<br>", $error_array ))
                			echo "<p id='wrong_input'> Your password must be between 10 and 30 characters.<br></p>";
                		break;
                		
                	case("es"):
                		if (in_array ( "Your passwords do not match.<br>", $error_array ))
                			echo " <p id='wrong_input'> Tus contraseñas no concuerdan.<br></p>";
                		else if (in_array ( "Your password must only contain characters and numbers.<br>", $error_array ))
                			echo "<p id='wrong_input'> Tu contraseña sólo puede contener caracteres y números.<br></p>";
                		else if (in_array ( "Your password must be between 10 and 30 characters.<br>", $error_array ))
                			echo "<p id='wrong_input'> Tu contraseña debe ser entre 10 y 30 caracteres.<br></p>";
                		break;
            }
            echo '
                    </div>
                    <select name="reg_adcountry" id="reg_adcountry">
						<option selected="selected" value="CO">Colombia</option>
					</select>
					<input type="hidden" name="search_true" value="1">
					<div class="modal-footer">
            ';

			switch ($lang){
					 		
				case("en"):
				    echo '
							<input type="submit" name="register_button_patient" class="btn btn-primary"
								value="Register"
								
								id="register_button_patient">
							<button type="button" class="btn btn-default"
								id="cancel_button_reglog_mod_div" data-dismiss="modal">Cancel</button>';
						        break;
							
				case("es"):
					echo '
							<input type="submit" name="register_button_patient"  class="btn btn-primary"
								value="Registrarse"
								
								id="register_button_patient">
							<button type="button" class="btn btn-default"
								id="cancel_button_reglog_mod_div" data-dismiss="modal">Cancelar</button>';
								break;
			}
			echo '</div>';

            switch ($lang){
                	
                	case("en"):
                		if (in_array ( "<span style='color: #14C800'> You are all set. Please login. </span><br>", $error_array ))
                			echo "<span style='color: #14C800'> You are all set. Please login. </span><br>";
                		break;
                		
                	case("es"):
                		if (in_array ( "<span style='color: #14C800'> You are all set. Please login. </span><br>", $error_array ))
                			echo "<span style='color: #14C800'> ¡Listo!. Ahora inicia sesión. </span><br>";
                		break;
            }

			echo '
              		</form>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="login_modal_div">
					<form action="docsearch.php" method="POST">
					<div class="modal_tag_block">	
						<input id="input_modal" type="email" name="log_email" placeholder="';
						
						
			switch ($lang){
				
				case("en"):
					echo "Email Adress";
					break;
					
				case("es"):
					echo "Correo";
					break;
			}
			echo '" value="';
							
			if (isset ( $_SESSION ['log_email'] )) {
				echo $txt_rep->entities ( $_SESSION ['log_email'] );
			}
			echo '" required>
				</div>
				<div class="modal_tag_block">
						<input id="input_modal" type="password" name="log_passwrd"
							placeholder="';
							
			switch ($lang){
				
				case("en"):
					echo "Password";
					break;
					
				case("es"):
					echo "Contraseña";
					break;
			}
			
			echo '"> <input type="hidden" name="search_true"
							value="1">';
						
            if(in_array("Incorect Email or Password.<br>", $error_array)){
                           
               	switch ($lang){
               		
               		case("en"):
               			echo " <p id='wrong_input'>Incorect Email or Password.<br>";
               			break;
               			
               		case("es"):
               			echo " <p id='wrong_input'>Correo o contraseña incorrecta.<br>";
               			break;
               	}
                       			 
			}
			
			echo '   </p>
                    </div>		
						<div class="modal-footer">';
			switch ($lang){
					 		
				case("en"):
				    echo '
					<input type="submit" name="login_button" value="Login" class="btn btn-primary">
					<button type="button" class="btn btn-default"
						id="cancel_button_reglog_mod_div" data-dismiss="modal">Cancel</button>';

			        break;
									
                case("es"):
                    echo '
                    <input type="submit" name="login_button" value="Iniciar Sesión" class="btn btn-primary">
					<button type="button" class="btn btn-default"
						id="cancel_button_reglog_mod_div" data-dismiss="modal">Cancelar</button>';

                    break;
            }
            echo '

						</div>
					</form>
				</div>
			</div>';
			

		}
		?>
    		</div>
	</div>
</div>

</body>
</html>