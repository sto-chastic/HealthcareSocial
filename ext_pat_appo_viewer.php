<?php 
include("includes/header2.php");
//TODO: change languages
$lang = $_SESSION['lang']; 

$current_day = date ( "d" );
$current_month = date ( "m" ); // Gets current month
$current_year = date ( "Y" );

// Language Tables:

switch ($lang){
	
	case("en"):
		$days_week_row_lang = 'days_short_eng';
		$months_row_lang = 'months_eng';
		break;
		
	case("es"):
		$days_week_row_lang = 'days_short_es';
		$months_row_lang = 'months_es';
		break;
}



$cid = $_REQUEST['cid'];

$profile_user_obj = $doctor_user_obj = $user_obj;

$ext_pat_tab = $doctor_user_obj->getExternalPatients_Tab();
$appo_dets = $doctor_user_obj->getAppointmentsDetails_Doctor();
$appo_cal = $doctor_user_obj->getAppointmentsCalendar();


$stmt = $con->prepare("SELECT patient_username FROM $appo_dets WHERE consult_id=?");
$stmt->bind_param("s",$cid);
$stmt->execute();

$username_q = $stmt->get_result();

if(!$doctor_user_obj->isDoctor() || mysqli_num_rows($username_q) < 1){
	header("Location: index.php");
}


$stmt = $con->prepare("SELECT t1.*,t2.office FROM $ext_pat_tab as t1 LEFT JOIN $appo_dets as t2 ON t1.username = t2.patient_username WHERE t2.consult_id = ?");
$stmt->bind_param("s",$cid);
$stmt->execute();

$q_ext = $stmt->get_result();
$arr_ext = mysqli_fetch_assoc($q_ext);


$stmt = $con->prepare("SELECT * FROM $appo_cal WHERE consult_id = ?");
$stmt->bind_param("s",$_REQUEST['cid']);
$stmt->execute();

$q_cal = $stmt->get_result();
$arr_cal = mysqli_fetch_assoc($q_cal);


switch ($lang){
	case "en":
		$dayoftheweek = "days_short_eng";
		$monthnumbtoname = "months_eng";
		break;
	case "es":
		$dayoftheweek = "days_short_es";
		$monthnumbtoname = "months_es";
		break;
}

$stmt = $con->prepare("
		SELECT t1.d,t2.$dayoftheweek, t3.$monthnumbtoname
		FROM calendar_table AS t1 LEFT JOIN (days_week AS t2, months as t3)
		ON (t2.dw = t1.dw AND t3.id = t1.m)
		WHERE t1.dt = ?
		");
$stmt->bind_param("s", $dt);

$dt = $arr_cal['year'] . "-" . $arr_cal['month'] . "-" . $arr_cal['day'];

$stmt->execute();
$dt_q = $stmt->get_result();
$dt_arr= mysqli_fetch_array($dt_q);

$display_date = $dt_arr[$dayoftheweek] . ", " . $dt_arr['d'] . " / " . $dt_arr[$monthnumbtoname] . " / " . $arr_cal['year'];

$time_obj = new DateTime($arr_cal['time_start']);
$display_time = $time_obj->format("g:i a");


$office_nick = "ad" . $arr_ext['office'] . "nick";
$office_add = "ad" . $arr_ext['office'] . "ln1";

$stmt = $con->prepare("SELECT $office_nick,$office_add FROM basic_info_doctors WHERE username = ?");
$stmt->bind_param("s",$userLoggedIn);
$stmt->execute();

$q_offi = $stmt->get_result();
$arr_offi = mysqli_fetch_assoc($q_offi);

$hideCalendar = FALSE;
?>

<style type="text/css">
	
	
</style>

<div class= "top_banner_title">
	<div class="top_banner_title_text_container">
		<?php 
			switch ($lang){
					 		
				case("en"):
		?>
					<h1>External Patient Appointment</h1>
					<h2>patient info</h2>
		<?php 
			        break;
				
				case("es"):
		?>
					<h1>Cita de Paciente Externo</h1>
					<h2>datos del paciente</h2>
		<?php 
					break;
			}
		?>
		
	</div>
</div>

<div class="wrapper">

<div class="main_column column" id="main_column">
	<div class="title_tabs">
		<?php 
    			switch ($lang){
    					 		
    				case("en"):
    		?>
    					data update
    		<?php 
    			        break;
    				
    				case("es"):
    		?>
    					actualización de datos
    		<?php 
    					break;
    			}
    		?>
	</div>
	<div class="external_div">
    		<?php 
    			switch ($lang){
    					 		
    				case("en"):
    		?>
    					<h2>Overview</h2>
    		<?php 
    			        break;
    				
    				case("es"):
    		?>
    					<h2>Vista General</h2>
    		<?php 
    					break;
    			}
    		?>
    		
    		
    		<?php 
    			switch ($lang){
    					 		
    				case("en"):
    		?>
    					<p style="color: #9d9d9d;">
    						This appointment with <b> <?php echo ucwords($txt_rep->entities($arr_ext['name']));?> </b> is scheduled for <b> <?php echo $txt_rep->entities($display_time);?> </b> 
    						on <b> <?php echo $txt_rep->entities($display_date);?> </b>. <br>
    						It will be held at office: <b> <?php echo $txt_rep->entities($arr_offi[$office_nick]);?> </b>, located at <b> <?php echo $txt_rep->entities($arr_offi[$office_add]);?> </b>.
    					</p>
    		<?php 
    			        break;
    				
    				case("es"):
    		?>
    					<p style="color: #9d9d9d;">
    						Esta cita con <b> <?php echo ucwords($txt_rep->entities($arr_ext['name']));?> </b> está programada para las <b> <?php echo $txt_rep->entities($display_time);?> </b> 
    						el <b> <?php echo $txt_rep->entities($display_date);?> </b>. <br>
    						Tengrá lugar en la oficina: <b> <?php echo $txt_rep->entities($arr_offi[$office_nick]);?> </b>, ubicada en <b> <?php echo $txt_rep->entities($arr_offi[$office_add]);?> </b>.
    					</p>
    		<?php 
    					break;
    			}
    		?>
    
    		
    		<div class="deep_blue" id="ext_pat_resched">
    		<?php 
    		switch ($lang){
    			
    			case("en"):
    				echo "Reschedule";
    				break;
    				
    			case("es"):
    				echo "Reprogramar";
    				break;
    		}?>
    		</div>
    		<div class="deep_blue" id="ext_pat_close" style=" display: none;">
    		<?php 
    		switch ($lang){
    			
    			case("en"):
    				echo "Close";
    				break;
    				
    			case("es"):
    				echo "Cerrar";
    				break;
    		}?>
    		</div>
    		<hr style="margin-bottom: 0px;">
    		<div class="hidden_cal_cont" style=" display: none; width: 100%;">
    		
    				<div class="profile_full_block" <?php if(!$hideCalendar){
    				    echo "style='display:none;'";
    				}    
                    ?>
                    >
    				<?php 
    				switch ($lang){
    					
    					case("en"):
    						echo "This doctor is not a Premium Doctor, for which this function is not available.";
    						break;
    						
    					case("es"):
    						echo "Este doctor no es un Doctor Premium, por lo que esta función no se encuentra disponible.";
    						break;
    				}?>
    				</div>
			
				<div class="profile_calendar_block" style="margin-left: 0px; width: 45%;"
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
        							
        							$years_q = mysqli_query($con, "SELECT DISTINCT y FROM calendar_table WHERE y<$year_lim");
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
    					
    					
    					$stmt = $con->prepare ( "SELECT * FROM basic_info_doctors WHERE username=?" );
    					$stmt->bind_param ( "s", $userLoggedIn );
    					$stmt->execute ();
    					$query = $stmt->get_result ();
    					$res = mysqli_fetch_array ( $query );
    
    					
    					?>
                    
                    
            
			<div class="schedule_type" <?php if($hideCalendar){
        				    echo "style='display:none;'";
        				}    
                        ?>>
    					<?php
    					switch ($lang){
    						
    						case("en"):
    							echo '<div class="profile_calendar_insert_ext">
    									<p>Select an Office</p>
    									<form>
    										<div><input type="radio" class="check" id="radio_office1" name="office_selected" value="1" checked> <b>' . $txt_rep->entities ( $res ['ad1nick'] ) . ' </b></div>';
    							break;
    							
    						case("es"):
    							echo '<div class="profile_calendar_insert_ext">
    									<p>Seleccionar Oficina</p>
    									<form>
    										<div><input type="radio" class="check" id="radio_office1" name="office_selected" value="1" checked> <b>' . $txt_rep->entities ( $res ['ad1nick'] ) . ' </b></div>';
    							break;
    					}
    					
    
    					if ($res ['ad2nick'] != '') {
    						echo '<div><input type="radio" id="radio_office2" name="office_selected" value="2"> <b>' . $txt_rep->entities ( $res ['ad2nick'] ) . ' </b></div>';
    					}
    					if ($res ['ad3nick'] != '') {
    						echo '<div><input type="radio" id="radio_office3" name="office_selected" value="3"> <b>' . $txt_rep->entities ( $res ['ad3nick'] ) . '</b></div> ';
    					}
    					echo '</form>
    						</div>';
    					
    					?>
    					
    					<p>
    					<?php 
    					switch ($lang){
    						
    						case("en"):
    							echo "What type of appointment is needed?";
    							break;
    							
    						case("es"):
    							echo "¿Qué tipo de cita se necesita?";
    							break;
    					}?>
    					</p>
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
			
    					<p><?php 
    					switch ($lang){
    						
    						case("en"):
    							echo "Select payment type:";
    							break;
    							
    						case("es"):
    							echo "Seleccionar tipo de pago:";
    							break;
    					}?>
    					</p>
    					
    					<?php
    					if(!$hideCalendar){
    						$user_insurance = $user_obj->getInsuranceCompany_Patient();
    						
    						$insurances_tab = $user_obj->getInsurancesTable();
    						$stmt = $con->prepare("SELECT $lang FROM $insurances_tab WHERE id=?");
    						$stmt->bind_param("s",$user_insurance);
    						$stmt->execute();
    						$_q_ins = $stmt->get_result();
    						$_arr_ins = mysqli_fetch_assoc($_q_ins);
    						$user_insurance_name = $_arr_ins[$lang];
    						
    						//print_r($profile_user_obj->getAvailableOfficesByInsurance($user_insurance));
    					}
    					?>
						<div id="payment_arrow">
        					<select name = "payment_type" id = "payment_type">
        		
        						<?php
        						if(!$hideCalendar){
        							switch ($lang){
        								
        								case("en"):
        									echo "<option selected='selected' value='part'>Cash</option>";
        									echo "<option selected='selected' value='insu'>Insurance</option>";
        									break;
        									
        								case("es"):
        									echo "<option selected='selected' value='part'>Efectivo</option>";
        									echo "<option selected='selected' value='insu'>Seguro/Prepagada/EPS</option>";
        									break;
        							}
        
        							$payment_method = 'part';
        						}
        						?>
        		
        					</select>
        				</div>	
    				</div>
				
			</div>
    				
    			<div id="day_container_div_profile" style=" padding-right: 0px;">
    				<?php
    				if(!$hideCalendar){ 
    					echo "<iframe src='day_frame_offices_selfbooking.php?d=" . $current_day . "&m=" . $current_month . "&y=" . $current_year . "&pm=" . $payment_method . "&po=" . bin2hex($userLoggedIn_e) . "&at=" . $ini_appo_id . "&off=1' id='day_iframe2' frameborder='0' scrolling='no'  width: 100%;'>" . "</iframe>";
    				}
    				?>
    			</div>
    			<hr style="margin-bottom: 0px;  margin-top: 42px;">
    		</div>
    		<div class="ext_patient_info_holder" style=" display: inline-block; width: 100%;">
    			<h2>
    			<?php 
    			switch ($lang){
    				
    				case("en"):
    					echo "Patient Information";
    					break;
    					
    				case("es"):
    					echo "Información del Paciente";
    					break;
    			}
    			?>
    			</h2>
    			<form class="edit_ext_pat_form" action="" method="POST">
    				<div class="dashboard_tag_block">
    	 				<p class="dashboard_tag">
    						<?php 
    						switch ($lang){
    							
    							case("en"):
    								echo "Full Name";
    								break;
    								
    							case("es"):
    								echo "Nombre Completo";
    								break;
    						}
    						?>
    					</p>
    					<input type="text" name="pat_name" placeholder="<?php 
    						switch ($lang){
    							
    							case("en"):
    								echo "ex: David Jimenez";
    								break;
    								
    							case("es"):
    								echo "ej: David Jimenez";
    								break;
    						}
    						?>" 
    					value="<?php
    						echo $txt_rep->entities($arr_ext['name']);
    					?>"
    					disabled>
    				</div>
    				<div class="dashboard_tag_block">
    	 				<p class="dashboard_tag">
    						<?php 
    						switch ($lang){
    							
    							case("en"):
    								echo "Patient Contact Info.";
    								break;
    								
    							case("es"):
    								echo "Información de Contacto";
    								break;
    						}
    						?>
    						
    					</p>
    					<input type="text" name="pat_contact" placeholder="<?php 
    						switch ($lang){
    							
    							case("en"):
    								echo "ex: +57 555 5555555";
    								break;
    								
    							case("es"):
    								echo "ej: +57 555 5555555";
    								break;
    						}
    						?>" 
    					value="<?php
    						echo $txt_rep->entities($arr_ext['contact_info']);
    					?>" 
    					disabled>
    				</div>
    			
				
    				<div class="dashboard_tag_block">
    	 				<p class="dashboard_tag">
    	 					<?php 
    						switch ($lang){
    							
    							case("en"):
    								echo "Patient Insurance";
    								break;
    								
    							case("es"):
    								echo "Seguro/Prepagada/EPS";
    								break;
    						}
    						?>
    						
    					</p>
    					<input type="text" name="pat_insurance" placeholder="<?php 
    						switch ($lang){
    							
    							case("en"):
    								echo "ex: Colmedica";
    								break;
    								
    							case("es"):
    								echo "ej: Colmedica";
    								break;
    						}
    						?>" 
    					value="<?php
    						echo $txt_rep->entities($arr_ext['insurance']);
    					?>" 
    					disabled>
    				</div>
    				<div class="dashboard_tag_block">
    	 				<p class="dashboard_tag">
    	 					<?php 
    						switch ($lang){
    							
    							case("en"):
    								echo "Additional Notes";
    								break;
    								
    							case("es"):
    								echo "Notas Adicionales";
    								break;
    						}
    						?>
    						
    					</p>
    					<textarea id="patient_notes" class="style-2" name="patient_notes" placeholder="<?php 
    						switch ($lang){
    							
    							case("en"):
    								echo "Any additional information";
    								break;
    								
    							case("es"):
    								echo "Información Adicional";
    								break;
    						}
    					?>" disabled><?php
    					
    						echo $txt_rep->entities($arr_ext['notes']);
    					?></textarea>
    				</div>
    				<div class="center_3_button_navigation" style="margin-top: 0px;">
    				<input type="hidden" name="cid" value="<?php echo $cid;?>">
        				
        				<input type="button" name="edit_patient_info" id="edit_patient_info" value="<?php 
        					switch ($lang){
        						
        						case("en"):
        							echo "Edit";
        							break;
        							
        						case("es"):
        							echo "Editar";
        							break;
        					}
        				?>" style="display: inline-block">
        				<input type="button" name="save_patient_info" id="save_patient_info" value="<?php 
        					switch ($lang){
        						
        						case("en"):
        							echo "Save";
        							break;
        							
        						case("es"):
        							echo "Guardar";
        							break;
        					}
        				?>" style="display: none">
    				</div>
    				</form>
    		</div>
    	</div>
    </div>
</div>    
    <div class="modal fade" id="confirm_self_booking" tabindex="-1"
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
    					switch ($lang){
    						
    						case("en"):
    							echo "Confirm Booking";
    							break;
    							
    						case("es"):
    							echo "Confirmar";
    							break;
    					}
    				?>
    				</h4>
    			</div>
    
    			<div class="modal-body">
    				<p>
    				<?php 
    					switch ($lang){
    							 		
    						case("en"):
    				?>
    							You are <b>rescheduling</b> this appointment for: <b> <span
    							id="modal_display_time"></span></b> on <b><span
    							id="modal_appo_date"></span></b>. <br> This appointment will be
    							held at office: <b><span id="modal_nick"></span></b>, located at <b><span
    							id="modal_addln1"></span></b> (Office: <b><span id="modal_addln3"></span></b>).
    				<?php 
    					        break;
    						
    						case("es"):
    				?>
    							Estás <b>reprogramando</b> esta cita para las: <b> <span
    							id="modal_display_time"></span></b> el <b><span
    							id="modal_appo_date"></span></b>. <br> Esta cita
    							tandrá lugar: <b><span id="modal_nick"></span></b>, ubicada en <b><span
    							id="modal_addln1"></span></b> (Oficina: <b><span id="modal_addln3"></span></b>).
    				<?php 
    							break;
    					}
    				?>
    
    				</p>
    				<form class="book_appointment_form_res" action="" method="POST">
    
    					<input type="hidden" name="year" value=""> 
    					<input type="hidden"name="month" value="">
    					<input type="hidden" name="day" value=""> 
    					<input type="hidden" name="profile_owner" value=""> 
    					<input type="hidden" name="payment_method" value=""> 
    					<input type="hidden" name="ap_id" value=""> 
    					<input type="hidden" name="ap_st" value=""> 
    					<input type="hidden" name="ap_end" value="">
    					<input type="hidden" name="old_aid" value="<?php echo $cid;?>">
    					<input type="hidden" name="ext_username" value="<?php echo $arr_ext['username']; ?>">
    				</form>
    			</div>
    
    			<div class="modal-footer">
    				<button type="button" class="btn btn-primary"
    					name="accept_appointment_booking" id="accept_appointment_booking">
    					<?php 
    					switch ($lang){
    						
    						case("en"):
    							echo "Reschedule Appointment";
    							break;
    							
    						case("es"):
    							echo "Reprogramar Cita";
    							break;
    					}
    					?></button>
    				<button type="button" class="btn btn-default" data-dismiss="modal">
    				<?php 
    					switch ($lang){
    						
    						case("en"):
    							echo "Close";
    							break;
    							
    						case("es"):
    							echo "Cerrar";
    							break;
    					}
    				?>
    				</button>
    			</div>
    		</div>
    	</div>
    </div>

<script>

var curr_month = $("#month_selected").find(":selected").val();
var curr_year = $("#year_selected").find(":selected").val();
var curr_day = '<?php echo $current_day; ?>';
var day = '<?php echo $current_day; ?>';
var view_type = 1;
var payment_method = $("#payment_type").find(":selected").val();
var selected_appo_id = $("#appo_type").find(":selected").val();
var profileUsername = '<?php echo bin2hex($txt_rep->entities($userLoggedIn_e)); ?>';

$(document).ready(function(){

	//initial calendar load

	var ajaxprofcal = $.ajax({
		url: "includes/handlers/ajax_profile_calendar.php",
		type: "POST",
		data: "profile_user=" + profileUsername + "&month=" + curr_month + "&year=" + curr_year + "&payment_method=" + payment_method + "&appo_type_id=" + selected_appo_id,
		cache: false,

		success: function(response){
			$(".calendar_container").html(response);
		}
	});

	$("#payment_type,#appo_type,input[name='office_selected']").change(function(){
		var month = $("#month_selected").find(":selected").val();
		var year = $("#year_selected").find(":selected").val();
		if ($('#selected_day_inp').val() != "")
			var day = $('#selected_day_inp').val();
		else
			var day = curr_day;
		
		var payment_method = $("#payment_type").find(":selected").val();
		var appo_type = $("#appo_type").find(":selected").val();
		var office_sel = $("input[name='office_selected']:checked").val();
		setFreeSlotsDay(year,month,day,payment_method,appo_type,office_sel);
	});

	
	$('#ext_pat_resched').on('click', function(){
		$('#ext_pat_resched').css({"display":"none"});
		$('#ext_pat_close').css({"display":"block"});
		$('.hidden_cal_cont').css({"display":"inline-block"});

		if($('#save_patient_info').css("display") != "none"){
			$('#save_patient_info').css({"display":"none"});
			$('#edit_patient_info').css({"display":"inline-block"});

			$.ajax({
				type: "POST",
				url: "includes/handlers/ajax_ext_pat_data.php",
				data: $('form.edit_ext_pat_form').serialize(), //What we send!
				cache: false,
				success: function(response){
				}
			});

			$('input[name="pat_name"]').attr('disabled','disabled');
			$('input[name="pat_contact"]').attr('disabled','disabled');
			$('input[name="pat_insurance"]').attr('disabled','disabled');
			$('textarea[name="patient_notes"]').attr('disabled','disabled');
		}
	});

	$('#ext_pat_close').on('click', function(){
		$('#ext_pat_close').css({"display":"none"});
		$('#ext_pat_resched').css({"display":"block"});
		$('.hidden_cal_cont').css({"display":"none"});
	});
	
	
	$('#edit_patient_info').on('click', function(){
		$('#edit_patient_info').css({"display":"none"});
		$('#save_patient_info').css({"display":"inline-block"});

		$('input[name="pat_name"]').removeAttr('disabled');
		$('input[name="pat_contact"]').removeAttr('disabled');
		$('input[name="pat_insurance"]').removeAttr('disabled');
		$('textarea[name="patient_notes"]').removeAttr('disabled');
	});
	$('#save_patient_info').on('click', function(){
		$('#save_patient_info').css({"display":"none"});
		$('#edit_patient_info').css({"display":"inline-block"});
		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_ext_pat_data.php",
			data: $('form.edit_ext_pat_form').serialize(), //What we send!
			cache: false,
			success: function(response){
			}
		});

		$('input[name="pat_name"]').attr('disabled','disabled');
		$('input[name="pat_contact"]').attr('disabled','disabled');
		$('input[name="pat_insurance"]').attr('disabled','disabled');
		$('textarea[name="patient_notes"]').attr('disabled','disabled');

	});

	$('#accept_appointment_booking').click(function(){
		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_reschedule_time_selection.php",
			data: $("form.book_appointment_form_res").serialize(), //What we send!
			cache: false,
			success: function(response){
				//alert(response);
				location.reload();
			},
			error: function(jqXHR, exception) {
				if (jqXHR.status === 409) {
					alert('<?php echo "Failed to add appointment, this slot was already taken by someone else. Try refreshing the page and try again." ?>');
				}
				else if (jqXHR.status === 412) {
					alert('<?php echo "Failed to add appointment, this slot is already filled in your calendar, select a different time and try again." ?>');
				}
				else if (jqXHR.status === 400) {
					alert('<?php echo "Failed to add appointment; bad request, refresh the page and try again." ?>');
				}
			}
		});
	});

});

function selectDay4Booking(year,month,day,payment_method,user,appo_type_id){
	$('#selected_day_inp').val(day);
	$('#selected_month_inp').val(month);
	$('#selected_year_inp').val(year);

	var payment_method = $("#payment_type").find(":selected").val();
	var appo_type = $("#appo_type").find(":selected").val();
	var office_sel = $("input[name='office_selected']:checked").val();
	setFreeSlotsDay(year,month,day,payment_method,appo_type,office_sel);
	
}

function setFreeSlotsDay(year,month,day,payment_method,appo_type,office_sel){
	$("#day_iframe2").attr('src', "day_frame_offices_selfbooking.php?d=" + day + "&m=" + month + "&y=" + year + "&pm=" + payment_method + "&at=" + appo_type + "&off=" + office_sel);
}

</script>
