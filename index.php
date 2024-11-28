<?php
include ('includes/header.php');
if (isset ( $_POST ['post'] )) {
	$post = new Post ( $con, $userLoggedIn, $userLoggedIn_e);
	$post->submitPost ( $_POST ['post_text'], '0000' );
}

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

// Graph settings
$num_points = 10;

// txt replace

$txtrep = new TxtReplace ();

// up to date
if ($user_obj->isDoctor ()) {
	$isUpToDate = $user_obj->isUpToDate ();
} else {
	$isUpToDate = 0;
}

if (isset ( $_GET ['mu'] )) {
	$message_user = $_GET ['mu'];
} else {
	$message_user = 0;
}

$isDoctor = $user_obj->isDoctor ();

//Previous login register

$_date = date("Y-m-d");

if(strtotime($settings->getSettingsValues("last_login")) < strtotime($_date)){
	$_date2 = date("Y-m-d H:i:s");
	$settings->setSettingsValues_strings("last_login", $_date2);
	$_SESSION['animation_hide'] = TRUE;
}

?>
<script src="assets/js/register.js"></script>
<div class="right_column column">
	<iframe src='coming_up.php' id='coming_up_iframe' frameborder='0'></iframe>
</div>
<div class="main_column column">
	<?php 
	switch ($lang){
		
		case("en"):
			?>
			
			<ul class="nav nav-tabs" role="tablist" id="profileTabs">
				<li role="presentation" class="active">
					<div class="arrow-down"></div> <a href="#newsfeed_div"
					aria-controls="newsfeed_div" role="tab" data-toggle="tab"> <span
						id="home_tab">hol</span> Home
				</a>
				</li>
		            	<?php
															if (! $isDoctor) {
																echo '<li role="presentation">
						  		<div class="arrow-down"></div>
						  		<a href="#search" aria-controls="search" role="tab" data-toggle="tab">					
					  				<span id="search_tab">hol</span>Search
					    			</a>
				           	</li>';
															}
															?>
				  	<li role="presentation">
					<div class="arrow-down"></div> <a href="#messages"
					aria-controls="messages" role="tab" data-toggle="tab"> <span
						id="chat_tab">hol</span>Messages
				</a>
				</li>
				  	<?php
							if (! $isDoctor) {
								echo '<li role="presentation">
						   		<div class="arrow-down"></div>
						   		<a href="#my_calendar" aria-controls="my_calendar" role="tab" data-toggle="tab">
						   			<span id="my_calendar_tab">hol</span>Calendar</a>
						   	</li>';
								
								echo '<li role="presentation">
						   		<div class="arrow-down"></div>	
						   		<a href="#health_stats" aria-controls="health_stats" role="tab" data-toggle="tab">
						   			<span id="health_tab">hol</span>My Health Stats
						   			</a>
						   	</li>';
							} else {
								echo '<li role="presentation">
						   		<div class="arrow-down"></div>
						   		<a href="#my_calendar" aria-controls="my_calendar" role="tab" data-toggle="tab">
						   			<span id="my_calendar_tab">hol</span>Calendar</a>
						   	</li>';
							}
							?>
			</ul>
			<?php
			break;
		
		case("es"):
			?>
			
			<ul class="nav nav-tabs" role="tablist" id="profileTabs">
				<li role="presentation" class="active">
					<div class="arrow-down"></div> <a href="#newsfeed_div"
					aria-controls="newsfeed_div" role="tab" data-toggle="tab"> <span
						id="home_tab">hol</span> Inicio
				</a>
				</li>
		            	<?php
						if (! $isDoctor) {
							echo '<li role="presentation">
					  		<div class="arrow-down"></div>
					  		<a href="#search" aria-controls="search" role="tab" data-toggle="tab">					
				  				<span id="search_tab">hol</span>Búsqueda
				    			</a>
			           	</li>';
					}
					?>
				  	<li role="presentation">
					<div class="arrow-down"></div> <a href="#messages"
					aria-controls="messages" role="tab" data-toggle="tab"> <span
						id="chat_tab">hol</span>Mensajes
				</a>
				</li>
				  	<?php
							if (! $isDoctor) {
								echo '<li role="presentation">
						   		<div class="arrow-down"></div>
						   		<a href="#my_calendar" aria-controls="my_calendar" role="tab" data-toggle="tab">
						   			<span id="my_calendar_tab">hol</span>Calendario</a>
						   	</li>';
								
								echo '<li role="presentation">
						   		<div class="arrow-down"></div>	
						   		<a href="#health_stats" aria-controls="health_stats" role="tab" data-toggle="tab">
						   			<span id="health_tab">hol</span>Mis Estadísticas
						   			</a>
						   	</li>';
							} else {
								echo '<li role="presentation">
						   		<div class="arrow-down"></div>
						   		<a href="#my_calendar" aria-controls="my_calendar" role="tab" data-toggle="tab">
						   			<span id="my_calendar_tab">hol</span>Calendario</a>
						   	</li>';
							}
							?>
			</ul>
			<?php
			break;
		
	}
	
	?>
	
	<div class="tab-content">

		<div role="tabpanel" class="tab-pane fade in active" id="newsfeed_div">
			<?php 
		 	switch ($lang){
		 		
		 		case("en"):
				 	?>
					<div class="title_tabs">Your Network Activity </div>
					<?php 
					break;
	
				case("es"):
					?>
					<div class="title_tabs">Actividad en tú Red </div>
					<?php 
					break;
		 	}
		 	?>

			<form class="post_form" action="index.php" method="POST">
			
			 	<?php 
			 	switch ($lang){
			 		
			 		case("en"):
					 	?>
						<textarea class="style-2" name="post_text" id="post_text"
							placeholder="Share here updates, photos, articles, scientific studies... Do NOT share private or sensible data!"></textarea>
						<input type="submit" name="post" id="post_button" value="Share"></input>
						<?php 
						break;
		
					case("es"):
						?>
						<textarea class="style-2" name="post_text" id="post_text"
							placeholder="Comparte aquí actualizaciones, fotos, artículos, estudios cientificos... ¡NO compartas información privada o sensible!"></textarea>
						<input type="submit" name="post" id="post_button" value="Compartir"></input>
						<?php 
						break;
			 	}
			 	?>
				
			</form>
			<div class="posts_area">
				<center>
					<img id="loading" src="assets/images/icons/logowhite.gif">
				</center>
			</div>
		</div>
			
			<?php if(!$isDoctor){?>
			<div role="tabpanel" class="tab-pane fade" id="search">
		 	<?php 
		 	switch ($lang){
		 		
		 		case("en"):
				 	?>
					<div class="title_tabs">Search </div>
					<?php 
					break;
	
				case("es"):
					?>
					<div class="title_tabs">Búsqueda </div>
					<?php 
					break;
		 	}
		 	?>
			

			<div class="menu_suggest">

				<div class="search_doctor">
					<form action="register.php" method="POST"
						style="text-align: right; margin-top: 0;">
						
					 	<?php 
					 	switch ($lang){
					 		
					 		case("en"):
							 	?>
								<ul class="list_search">
									<li>
										<ul class="list_search_sub"
											>
											<li><p>
													Type of doctor <br> or symptom
												</p></li>
											<li><input type="text" name="query" id="search_specialist"
												placeholder="ex:gastroenterology" required autocomplete="OFF"></li>
										</ul>
									</li>
									<li style="display: inline; text-align: left;">
										<ul class="list_search_sub">
											<li><p>
													<br>Date
												</p></li>
											<li><input type="text" id="datepicker" name="date_query"
												
												readonly="true" required></li>
										</ul>
									</li>
									<li>
										<ul class="list_search_sub"
										>
											<li><p>
													<br>Location
												</p></li>
											<li><input type="text" id="search_location"
												name="search_location_name" value="Bogotá, D.C."
												autocomplete="off" required>
												<div id="docsearch_location_reg"></div> <input type="hidden"
												id="ds_city_code" name="ds_city_code" value="CO001"> <input
												type="hidden" id="ds_lat" name="pos[lat]"> <input
												type="hidden" id="ds_lng" name="pos[lng]"></li>
										</ul>
									</li>
									<li>
										<ul class="list_search_sub">
											<li><p>
													<br>Insurance
												</p></li>
											<li><input type="text" id="search_insurance"
												name="searched_insurance1" placeholder="Ex: Allianz"
												autocomplete="off"
												required>
												<div class= "style-2" id="docsearch_insurance_reg"></div> <input type="hidden"
												id="ds_ins_code" name="ds_ins_code"></li>
										</ul>
									</li>
									<li>
										<ul class="list_search_sub" id="ul_radius">
											<li><p>
													<br>Radius
												</p>
											</li>
											<li style="height:3vh;"><input type="range" id="search_radius" name="radius"
												min="0" max="50" step="10"> <!--         		                 	<div class="search_radius_info" id="search_radius_info"> -->
												<span class="current_search_radius" style="top: -7.5vh;" id="current_search_radius"></span>
												<!--         		                 	</div> --></li>
											<li ><span class="caps_meter">0Km<span
											 style="float:right;">50Km </span></span>
										
											</li>
										
										
										</ul>
									</li>
									<li style="width: 3.2vw;" ><input
										type="submit" id="go" name="go_search" value="GO"></li>
								</ul>

								<?php 
								break;
				
							case("es"):
								?>
								<ul class="list_search">
									<li>
										<ul class="list_search_sub"
											>
											<li><p>
													Tipo de doctor <br> o síntoma
												</p></li>
											<li><input type="text" name="query" id="search_specialist"
												placeholder="ej:gastroenterología" required autocomplete="OFF"></li>
										</ul>
									</li>
									<li style="display: inline; text-align: left;">
										<ul class="list_search_sub">
											<li><p>
													<br>Fecha
												</p></li>
											<li><input type="text" id="datepicker" name="date_query"
												
												readonly="true" required></li>
										</ul>
									</li>
									<li>
										<ul class="list_search_sub"
										>
											<li><p>
													<br>Ubicación
											</p></li>
											<li><input type="text" id="search_location"
												name="search_location_name" value="Bogotá, D.C."
												autocomplete="off" required>
												<div class= "style-2" id="docsearch_location_reg"></div> <input type="hidden"
												id="ds_city_code" name="ds_city_code" value="CO001"> <input
												type="hidden" id="ds_lat" name="pos[lat]"> <input
												type="hidden" id="ds_lng" name="pos[lng]"></li>
										</ul>
									</li>
									<li>
										<ul class="list_search_sub">
											<li><p>
													<br>Seguro/Prepagada/EPS
												</p></li>
											<li><input type="text" id="search_insurance"
												name="searched_insurance1" placeholder="Ej: Allianz"
												autocomplete="off"
												required>
												<div class= "style-2" id="docsearch_insurance_reg"></div> <input type="hidden"
												id="ds_ins_code " name="ds_ins_code"></li>
										</ul>
									</li>
									<li>
										<ul class="list_search_sub" id="ul_radius">
											<li><p>
													<br>Radio
												</p></li>
											<li style="height:3vh; "><input type="range" id="search_radius" name="radius"
												min="0" max="50" step="10"> <!--         		                 	<div class="search_radius_info" id="search_radius_info"> -->
												<span class="current_search_radius" id="current_search_radius"></span>
												<!--         		                 	</div> --></li>
											<li ><span class="caps_meter">0Km<span
											 style="float:right;">50Km </span></span>
										
											</li>
										
										</ul>
									</li>
									<li style="width: 3.2vw;"><input
										type="submit" id="go" name="go_search" value="Ir"></li>
								</ul>
								<?php 
								break;
					 	}
						?>
						
						
					</form>
				</div>
				
				
			 	<?php 
			 	switch ($lang){
			 		
			 		case("en"):
					 	?>
						<div class="title_suggest">
							<p>Recently Scheduled Doctors:</p>
						</div>
						<?php 
						break;
		
					case("es"):
						?>
						<div class="title_suggest">
							<p>Doctores Agendados Recientemente:</p>
						</div>
						<?php 
						break;
			 	}
			 	
			 	
			 	
				$displayed_num_docs = 3;
				$appointments_master = new Appointments_Master ( $con, $userLoggedIn, $userLoggedIn_e);
				$recent_docs_arr = $appointments_master->getLastNViewdDoctors ( $displayed_num_docs );
				// print_r($recent_docs_arr);
				?>
				    <div class="suggest_doctor">
					<ul class="suggest_search">
				<?php
				
				switch ($lang){

					case("en"):
						for($i = 0; $i <= $displayed_num_docs && $i < sizeof ( $recent_docs_arr ); $i ++) {
							$_temp_doc_user = $recent_docs_arr [$i] ['username'];
							$_temp_doc_user_e = $crypt->EncryptU($_temp_doc_user);
							$_temp_doc_user_obj = new User ( $con, $_temp_doc_user, $_temp_doc_user_e);
							
							$_tempt_appo_time = $appointments_master->getAppointmentTimeDate ( $recent_docs_arr [$i] ['cid'], 1 );
							
							echo '<li style=" height: 110px;">

											<ul class="suggest_search_sub" style=" height: 110px;" >
					                				 <li><img src="' . $txt_rep->entities ( $_temp_doc_user_obj->getProfilePicFast () ) . '"></li>
													<li><a href="' . bin2hex($_temp_doc_user_e) . '">Dr.' . $txt_rep->entities ( $_temp_doc_user_obj->getFirstAndLastNameFast () ) . '</a>
													<p>' . $_temp_doc_user_obj->getSpecializationsText ( $lang ) . '</p>
												</li>
											</ul><br>';
							if (empty ( $_tempt_appo_time )) {
								echo '<div id="last_seen" style=" color: #ff0505;"> Canceled </div>
				        					</li>';
							} else {
								echo '<div id="last_seen"> Scheduled for:<br>' . $_tempt_appo_time ['day'] . ' / ' . $_tempt_appo_time ['month'] . ' / ' . $_tempt_appo_time ['year'] . '</div>
				        					</li>';
							}
						}
						break;
		
					case("es"):
						for($i = 0; $i <= $displayed_num_docs && $i < sizeof ( $recent_docs_arr ); $i ++) {
							$_temp_doc_user = $recent_docs_arr [$i] ['username'];
							$_temp_doc_user_e = $crypt->EncryptU($_temp_doc_user);
							$_temp_doc_user_obj = new User ( $con, $_temp_doc_user, $_temp_doc_user_e);
							
							$_tempt_appo_time = $appointments_master->getAppointmentTimeDate ( $recent_docs_arr [$i] ['cid'], 1 );
							
							echo '<li style=" height: 110px;">
											<ul class="suggest_search_sub" style=" height: 110px;" >
					                				 <li><img src="' . $txt_rep->entities ( $_temp_doc_user_obj->getProfilePicFast () ) . '"></li>
													<li><a href="' . bin2hex($_temp_doc_user_e) . '">Dr.' . $txt_rep->entities ( $_temp_doc_user_obj->getFirstAndLastNameFast () ) . '<br></a><br>
													<p>' . $_temp_doc_user_obj->getSpecializationsText ( $lang ) . '</p>
												</li>
											</ul><br>';
							if (empty ( $_tempt_appo_time )) {
								echo '<div id="last_seen" style=" color: #ff0505;"> Cancelado</div>
				        					</li>';
							} else {
								echo '<div id="last_seen"> Agendado para:<br>' . $_tempt_appo_time ['day'] . ' / ' . $_tempt_appo_time ['month'] . ' / ' . $_tempt_appo_time ['year'] . '</div>
				        					</li>';
							}
						}
						break;
			 	}
				
				?>
			        
				        </ul>
				</div>
			</div>

		</div>
			<?php }?>
			
			<div role="tabpanel" class="tab-pane fade" id="messages">
			<div class="title_tabs">Quick Chat </div>
			<iframe src='messages_frame.php' id='messages_frame' frameborder='0'
				scrolling='no'></iframe>
		</div>

		<div role="tabpanel" class="tab-pane fade" id="health_stats">
			<div class="h_stats_top_container">
			 	<?php 
			 	switch ($lang){
			 		
			 		case("en"):
					 	?>
						<div class="title_tabs">Health Stats</div>
						<?php 
						break;
		
					case("es"):
						?>
						<div class="title_tabs">Mis Estadísticas</div>
						<?php 
						break;
			 	}
			 	?>
	<div class="statistics_background">		
				<div class="health_stats_markers">

					<div class="health_stats_marker_container _marker_container_active"
						id="health_stats_weight">
						<div
							class="health_stats_marker_left health_stats_marker_box_active">
						</div>
						<div class="health_stats_marker_top">
						 	<?php 
						 	switch ($lang){
						 		
						 		case("en"):
								 	?>
									<p>Weight</p>
									<?php 
									break;
					
								case("es"):
									?>
									<p>Peso</p>
									<?php 
									break;
						 	}
						 	?>
						</div>
						<div class="health_stats_marker_bottom">
							<p class="health_stat_title" id="health_stat_title_weight"></p>
							<p class="health_stat_date" id="health_stat_date_weight"></p>
						</div>
					</div>

					<div
						class="health_stats_marker_container _marker_container_inactive"
						id="health_stats_BMI">
						<div
							class="health_stats_marker_left health_stats_marker_box_inactive">
						</div>
						<div class="health_stats_marker_top">
							<?php 
						 	switch ($lang){
						 		
						 		case("en"):
								 	?>
									<p>Body Mass Index (BMI)</p>
									<?php 
									break;
					
								case("es"):
									?>
									<p>Índice Masa Corporal</p>
									<?php 
									break;
						 	}
						 	?>
							
						</div>
						<div class="health_stats_marker_bottom">
							<p class="health_stat_title" id="health_stat_title_bmi"></p>
							<p class="health_stat_date" id="health_stat_date_bmi"></p>
						</div>
					</div>

					<div
						class="health_stats_marker_container _marker_container_inactive"
						id="health_stats_bp">
						<div
							class="health_stats_marker_left health_stats_marker_box_inactive">
						</div>
						<div class="health_stats_marker_top">
							<?php 
						 	switch ($lang){
						 		
						 		case("en"):
								 	?>
									<p>Blood Pressure</p>
									<?php 
									break;
					
								case("es"):
									?>
									<p>Presión Arterial</p>
									<?php 
									break;
						 	}
						 	?>
							
						</div>
						<div class="health_stats_marker_bottom">
							<p class="health_stat_title" id="health_stat_title_bp"></p>
							<p class="health_stat_date" id="health_stat_date_bp"></p>
						</div>
					</div>

					<div
						class="health_stats_marker_container _marker_container_inactive"
						id="health_stats_height">
						<div
							class="health_stats_marker_left health_stats_marker_box_inactive">
						</div>
						<div class="health_stats_marker_top">
							<?php 
						 	switch ($lang){
						 		
						 		case("en"):
								 	?>
									<p>Height</p>
									<?php 
									break;
					
								case("es"):
									?>
									<p>Altura</p>
									<?php 
									break;
						 	}
						 	?>
						</div>
						<div class="health_stats_marker_bottom">
							<p class="health_stat_title" id="health_stat_title_height"></p>
							<p class="health_stat_date" id="health_stat_date_height"></p>
						</div>
					</div>

				</div>
					<?php //TODO:bookmark;?>
					<div class="health_stats_content">
					<div id="canvas_graph_div">
						<canvas id="canvas_graph_index" width="400px" height="180px"> <!-- Size must be slightly higher than the graph's dimentions so that no distortion occurs -->
							    <b>Your browser does not support the canvas element, for which the graphs could not be displayed. Please update to the latest version of Chrome, Firefox or Safari.</b>
						</canvas>
					</div>
					<div id="health_stats_point_inputs"></div>
					<div id="health_stats_point_review"></div>
					<!-- call the name of the inputs "inview_bla" -->
				</div>
			</div>
		</div>
	</div>



		<div role="tabpanel" class="tab-pane fade" id="my_calendar">
			
				<?php
				$hideCalendar = FALSE;
				if ($isDoctor && ! $isUpToDate) {
					$hideCalendar = TRUE;
				}
				?>
				
			 	<?php 
			 	switch ($lang){
			 		
			 		case("en"):
					 	?>
							<div class="title_tabs"
							<?php
							
							if ($hideCalendar) {
								echo "style='display:none;'";
							}
							?>>Calendar Management <?php
							if ($isDoctor && ! $hideCalendar) {
								echo '<a href="calendar_settings.php" >Calendar Configuration</a>';
							}
							?></div>	
						<?php 
						break;
		
					case("es"):
						?>
							<div class="title_tabs"
							<?php
							
							if ($hideCalendar) {
								echo "style='display:none;'";
							}
							?>>Calendario <?php
							if ($isDoctor && ! $hideCalendar) {
								echo '<a href="calendar_settings.php" >Configuración de Calendario</a>';
							}
							?></div>	
						<?php 
						break;
			 	}
			 	?>		

	<div class='calendar_background'>
				<?php
				$select_view = "<select name='view_type' id='view_type'>
									<option value='1'> Patient Calendar </option>
									<option selected='selected' value='2'> Doctor Calendar </option>
								</select>";
				
				if ($isDoctor) {
					$ini_view_type = 1;
				} else {
					$ini_view_type = 2;
				}
				
				?>
				<div class="profile_full_block"
				<?php
				
				if (! $hideCalendar) {
					echo "style='display:none;'";
				}
				?>>
				
			 	<b>
			 	<?php 
			 	switch ($lang){
			 		
			 		case("en"):
					 	?>
							ConfiDr. Premium
							<br> Control your schedule, allow more patients to find you,
							automatically access your patient's medical information and
							much more!<br>
							Start optimizing your appointments, it's <u>free</u> for limited time!
							<a href="premium2.php">
								<div class="suscribe_ad_button">
									<p class="suscribe_ad_font_main">Start now!</p>
								</div>
							</a></b>
						<?php 
						break;
		
					case("es"):
						?>
							ConfiDr. Premium
							<br> Controla tu horario, permite que te encuentren más pacientes,
							accede automaticamente a la información médica de tus pacientes y mucho más!<br>
							Comienza a optimizar tus consultas, ¡es <u>gratis</u> por tiempo limitado!
							<a href="premium2.php">
								<div class="suscribe_ad_button">
									<p class="suscribe_ad_font_main">¡Comienza ahora!</p>
								</div>
							</a></b>
						<?php 
						break;
			 	
			 	}
			 	?>

			</div>

			<div class="profile_calendar_block"
				<?php
				
				if ($hideCalendar) {
					echo "style='display:none;'";
				}
				?>>
			 	<?php 
			 	switch ($lang){
			 		
			 		case("en"):
			 			if($isDoctor){
						 	?>
							<div class="calendar_subtitle">Consult Your Calendar</div>
							<p style="font-family: 'Coves-Bold'; color: #777; margin-bottom: 23px;">Review previous
							and upcoming appointments. You can select an upcoming appointment to see the details the patient gave you, or select a past appointment to review your annotations for the consult.</p>
							<?php
			 			}
						else{
							?>
							<div class="calendar_subtitle">Consult Your Calendar</div>
							<p style="font-family: 'Coves-Bold'; color: #777; margin-bottom: 23px;">Review previous
							and upcoming appointments. You can select an upcoming appointment to add relevant information for your doctor, or select a past appointment to review annotations, prescriptions, etc.</p>
							<?php
						}
						break;
		
					case("es"):
						if($isDoctor){
							?>
							<div class="calendar_subtitle">Consulta tu Calendario</div>
							<p style="font-family: 'Coves-Bold'; color: #777; margin-bottom: 23px;">Repasa tus consultas anteriores y las que aún no han pasado. Puedes entrar a tu consulta antes de que haya pasado para revisar la información que te dio el paciente. Cuando ya ha pasado, puedes revisar tus anotaciones, prescripciones, etc.</p>
							<?php
						}
						else{
							?>
							<div class="calendar_subtitle">Consulta tu Calendario</div>
							<p style="font-family: 'Coves-Bold'; color: #777; margin-bottom: 23px;">Repasa tus consultas anteriores y las que aún no han pasado. Puedes entrar a tu consulta antes de que haya pasado para agregar información relevante para tu médico. Cuando ya ha pasado, puedes revisar las anotaciones, prescripciones, etc.</p>
							<?php
						}
						break;
							
			 	}
			 	?>

				<div style="display: inline-block; width: 100%;">

					<div class="year_selector">
						<select name="year_selected" id="year_selected">
							<?php
							$drop_dwn_year = "";
							$year_lim = date ( 'Y', strtotime ( '+2 years' ) );
							
							$years_q = mysqli_query ( $con, "SELECT DISTINCT y FROM calendar_table WHERE y<$year_lim" );
							foreach ( $years_q as $arr ) {
								if ($current_year == $arr ['y']) {
									$drop_dwn_year = $drop_dwn_year . "<option selected='selected' value='" . $arr ['y'] . "'>" . $arr ['y'] . "</option>";
								} else {
									$drop_dwn_year = $drop_dwn_year . "<option value='" . $arr ['y'] . "'>" . $arr ['y'] . "</option>";
								}
							}
							if (! $hideCalendar) {
								echo $drop_dwn_year;
							}
							
							?>
							</select>
					</div>
					<div class="month_selector">
							<?php
							$curr_month_lang_query = mysqli_query ( $con, "SELECT $months_row_lang FROM months WHERE id='$current_month'" );
							$arr = mysqli_fetch_array ( $curr_month_lang_query );
							$curr_month_lang = $arr [$months_row_lang];
							?>
							<select name="month_selected" id="month_selected">
								<?php
								$drop_dwn_month = "";
								$query = mysqli_query ( $con, "SELECT $months_row_lang,id FROM months ORDER BY id ASC" );
								foreach ( $query as $arr ) {
									if ($curr_month_lang == $arr [$months_row_lang]) {
										$drop_dwn_month = $drop_dwn_month . "<option selected='selected' value='" . $arr ['id'] . "'>" . $arr [$months_row_lang] . "</option>";
									} else {
										$drop_dwn_month = $drop_dwn_month . "<option value='" . $arr ['id'] . "'>" . $arr [$months_row_lang] . "</option>";
									}
								}
								if (! $hideCalendar) {
									echo $drop_dwn_month;
								}
								
								?>
							</select>
					</div>
				</div>
				
    				<div class='calendar_header_container'>
    						<?php
    						$days_str = "";
    						$week_days_lang_query = mysqli_query ( $con, "SELECT $days_week_row_lang FROM days_week ORDER BY dw ASC " );
    						
    						foreach ( $week_days_lang_query as $value_day ) {
    							$days_str = $days_str . "<div class='calendar_day_block' id='header_day'>" . $value_day [$days_week_row_lang] . "</div>";
    						}
    						if (! $hideCalendar) {
    							echo $days_str;
    						}
    						
    						?>
    					</div>
    		
    
    					<?php
    					if (! $hideCalendar) {
    						echo "<div class='calendar_container'>
    					           </div>";
    					}
    					
    				 	switch ($lang){
    				 		
    				 		case("en"):
    						 	?>
    							<div class="calendar_subtitle">Search Your Consults</div>
    							
    							<!-- Contains search patient -->
    							<div class="search" id="search_patient_calendar">
    							<!-- GET method is for passing parameters in the url -->
    							<form action="" method="" name="">
    		
    								<input type="text"
    									onkeyup="searchCalendar(this.value,'<?php echo $isDoctor; ?>')"
    									name="cal_q" placeholder="Search" autocomplete="off"
    									id="search_text_input_calendar">
    							<?php 
    							break;
    			
    						case("es"):
    							?>
    							<div class="calendar_subtitle">Busca en tus Consultas</div>
    							
    							<!-- Contains search patient -->
    							<div class="search" id="search_patient_calendar">
    							<!-- GET method is for passing parameters in the url -->
    							<form action="" method="" name="">
    		
    								<input type="text"
    									onkeyup="searchCalendar(this.value,'<?php echo $isDoctor; ?>')"
    									name="cal_q" placeholder="Buscar" autocomplete="off"
    									id="search_text_input_calendar">
    							<?php 
    							break;
    				 	}
    				 	?>
    					
    
    
    						<div class="button_holder_calendar">
    							<img src="assets/images/icons/search-icon-pink.png">
    						</div>
    
    					</form>
    
    					<div class="search_cal_results" id="style-2"></div>
    
    				</div>
    
    			</div>
    
    
    
    			<div id="day_container_div"
    				<?php
    				
    				if ($hideCalendar) {
    					echo "style='display:none;'";
    				}
    				?>>
    
    				<?php
    					if (! $hideCalendar) {
    						echo "<iframe src='day_frame_home.php?d=" . $current_day . "&m=" . $current_month . "&y=" . $current_year . "&vt=" . $ini_view_type . "' id='day_iframe' frameborder='0' scrolling='no' >" . "</iframe>";
    					}
    				?>	
    			</div>
    			<div class="schedule_doctor_div">	
    				
    				<?php
    				
    				if ($isDoctor && ! $hideCalendar) {
    					
    					$stmt = $con->prepare ( "SELECT * FROM basic_info_doctors WHERE username=?" );
    					$stmt->bind_param ( "s", $userLoggedIn );
    					$stmt->execute ();
    					$query = $stmt->get_result ();
    					$res = mysqli_fetch_array ( $query );
    					
    					switch ($lang){
    						
    						case("en"):
    							
    							echo '<div class="profile_calendar_insert">
    							<div class ="calendar_subtitle" >Manually Book Appointment</div>
    				
    							<p>Insert appoinments in your calendar for non-ConfiDr. patients. Select an available slot from the column on the right.</p>
    							<form style="margin-bottom: 20px;">
    							<div><input type="radio" class="check" id="radio_office1" name="office_selected" value="1" checked> <b>' . $txtrep->entities ( $res ['ad1nick'] ) . ' </b></div>';
    							if ($res ['ad2nick'] != '') {
    								echo '<div><input type="radio" id="radio_office2" name="office_selected" value="2"> <b>' . $txtrep->entities ( $res ['ad2nick'] ) . ' </b></div>';
    							}
    							if ($res ['ad3nick'] != '') {
    								echo '<div><input type="radio" id="radio_office3" name="office_selected" value="3"> <b>' . $txtrep->entities ( $res ['ad3nick'] ) . '</b></div> ';
    							}
    							echo '</form>';
    							
    							echo '<p>Appointment type:</p>
                                <div class="date_selector">
        							<select name = "appo_type" id = "appo_type">';
        							
        							$appo_duration_tab = $user_obj->getAppoDurationTable ();
        							$drop_dwn = "";
        							$query = mysqli_query ( $con, "SELECT appo_type,duration,id FROM $appo_duration_tab WHERE deleted = 0 ORDER BY appo_type ASC" );
        							foreach ( $query as $key => $arr ) {
        								if ($key == 0) {
        									$ini_appo_id = $txt_rep->entities ( $arr ['id'] );
        								}
        								$drop_dwn = $drop_dwn . "<option value='" . $txt_rep->entities ( $arr ['id'] ) . "'>" . $txt_rep->entities ( $arr ['appo_type'] ) . "</option>";
        							}
        							echo $drop_dwn;
        							
        							echo '</select></div>';
    							
    							echo '<p>Payment type:</p>
                                <div class="payment_selector">
        							<select name = "payment_type" id = "payment_type">
        								<option value="insu" selected=selected>Insurance</option>
        								<option value="part">Cash</option>
        							</select></div></div>';
    							$payment_method = "insu";
    							echo "<div id='day_container_div' style= 'padding-top: 17px;'> <iframe src='day_frame_offices_selfbooking.php?d=" . $current_day . "&m=" . $current_month . "&y=" . $current_year . "&pm=" . $payment_method . "&at=" . $ini_appo_id . "&off=1' id='day_iframe2' frameborder='0' scrolling='no'  width: 100%;'>" . "</iframe>";
    							echo "</div>";
    							
    							break;
    			
    						case("es"):
    							
    							echo '<div class="profile_calendar_insert">
    							<div class ="calendar_subtitle" >Agendar Cita Manualmente</div>
    						   					
    							<p>Agregar cita en tu calendario para pacientes no-ConfiDr. Selecciona un espacio disponible de la columna de la derecha.</p>
    							<form style="margin-bottom: 20px;">
    							<div><input type="radio" class="check" id="radio_office1" name="office_selected" value="1" checked> <b>' . $txtrep->entities ( $res ['ad1nick'] ) . ' </b></div>';
    							if ($res ['ad2nick'] != '') {
    								echo '<div><input type="radio" id="radio_office2" name="office_selected" value="2"> <b>' . $txtrep->entities ( $res ['ad2nick'] ) . ' </b></div>';
    							}
    							if ($res ['ad3nick'] != '') {
    								echo '<div><input type="radio" id="radio_office3" name="office_selected" value="3"> <b>' . $txtrep->entities ( $res ['ad3nick'] ) . '</b></div> ';
    							}
    							echo '</form>';
    							
    							echo '<p>Tipo de Cita:</p>
                                <div class="date_selector">
        							<select name = "appo_type" id = "appo_type">';
        							
        							$appo_duration_tab = $user_obj->getAppoDurationTable ();
        							$drop_dwn = "";
        							$query = mysqli_query ( $con, "SELECT appo_type,duration,id FROM $appo_duration_tab WHERE deleted = 0 ORDER BY appo_type ASC" );
        							foreach ( $query as $key => $arr ) {
        								if ($key == 0) {
        									$ini_appo_id = $txt_rep->entities ( $arr ['id'] );
        								}
        								$drop_dwn = $drop_dwn . "<option value='" . $txt_rep->entities ( $arr ['id'] ) . "'>" . $txt_rep->entities ( $arr ['appo_type'] ) . "</option>";
        							}
        							echo $drop_dwn;
    							
    							echo '</select></div>';
    							
    							echo '<p>Tipo de Pago:</p>
                                <div class="payment_selector">
        							<select name = "payment_type" id = "payment_type">
        								<option value="insu" selected=selected>Seguro/Prepagada/EPS</option>
        								<option value="part">Efectivo</option>
        							</select></div></div>';
    							$payment_method = "insu";
    							echo "<div id='day_container_div' style= 'padding-top: 17px;'> <iframe src='day_frame_offices_selfbooking.php?d=" . $current_day . "&m=" . $current_month . "&y=" . $current_year . "&pm=" . $payment_method . "&at=" . $ini_appo_id . "&off=1' id='day_iframe2' frameborder='0' scrolling='no'  width: 100%;'>" . "</iframe>";
    							echo "</div>";
    							
    							break;
    				 	}
    					
    				}
    				?>
    			</div>
    		</div>
    	</div>
	</div>
</div>

<script>


        $('#post_text').on('keydown', function(e){
            var that = $(this);
            if (that.scrollTop()) {
                $(this).height(function(i,h){
                    return h + 20;
                });
            }
        });

        
        
        

		var month = $("#month_selected").find(":selected").val();
		var year = $("#year_selected").find(":selected").val();
		var curr_day = '<?php echo $current_day; ?>';
		var day = '<?php echo $current_day; ?>';
		var view_type = '<?php echo $ini_view_type; ?>';

		$(document).ready(function(){


			

			//Clear date inputs
			
			$('.health_stat_data_inp').on('click focusin', function() {
                this.value = '';
            });

			var message_user = '<?php echo $message_user; ?>';
			if(message_user != 0){
				tabChanger("messages");
				selectMessageIFrame(message_user);
			}
			
			var isDoctor = '<?php echo $user_obj->isDoctor()?>';
			
			if(!isDoctor){
				//health stats markers
				drawGraphProfile('weight', '<?php echo $num_points; ?>');

				updateHStatButts('weight');
				updateHStatButts('bmi');
				updateHStatButts('bp');
				updateHStatButts('height');

				$('#health_stats_weight').on('click',function(){
					//for the border
					removeClassActive("health_stats_markers", "_marker_container_active", "_marker_container_inactive");
					$("#health_stats_weight").toggleClass("_marker_container_inactive");
					$("#health_stats_weight").toggleClass("_marker_container_active");

					//for the box
					removeClassActive("health_stats_markers", "health_stats_marker_box_active", "health_stats_marker_box_inactive");
					$("#health_stats_weight .health_stats_marker_left").toggleClass("health_stats_marker_box_inactive");
					$("#health_stats_weight .health_stats_marker_left").toggleClass("health_stats_marker_box_active");
					//for the top content
					

					//for the graph
					clearDrawGraphProfile();
					drawGraphProfile('weight', '<?php echo $num_points; ?>');
					//for the input fields
					load_insert_fields_h_stats('weight');
					move_data_h_stats(0, 'weight');
				});

				$('#health_stats_BMI').on('click',function(){
					//for the border
					removeClassActive("health_stats_markers", "_marker_container_active", "_marker_container_inactive");
					$("#health_stats_BMI").toggleClass("_marker_container_inactive");
					$("#health_stats_BMI").toggleClass("_marker_container_active");
					//for the box
					removeClassActive("health_stats_markers", "health_stats_marker_box_active", "health_stats_marker_box_inactive");
					$("#health_stats_BMI .health_stats_marker_left").toggleClass("health_stats_marker_box_inactive");
					$("#health_stats_BMI .health_stats_marker_left").toggleClass("health_stats_marker_box_active");
					//for the graph
					clearDrawGraphProfile();
					drawGraphProfile( 'bmi', '<?php echo $num_points; ?>');
					//for the input fields
					load_insert_fields_h_stats('bmi');
					move_data_h_stats(0, 'bmi');
				});

				$('#health_stats_bp').on('click',function(){
					//for the border
					removeClassActive("health_stats_markers", "_marker_container_active", "_marker_container_inactive");
					$("#health_stats_bp").toggleClass("_marker_container_inactive");
					$("#health_stats_bp").toggleClass("_marker_container_active");
					//for the box
					removeClassActive("health_stats_markers", "health_stats_marker_box_active", "health_stats_marker_box_inactive");
					$("#health_stats_bp .health_stats_marker_left").toggleClass("health_stats_marker_box_inactive");
					$("#health_stats_bp .health_stats_marker_left").toggleClass("health_stats_marker_box_active");
					//for the graph
					clearDrawGraphProfile();
					drawGraphProfile('bp', '<?php echo $num_points; ?>');
					//for the input fields
					load_insert_fields_h_stats('bp');
					move_data_h_stats(0, 'bp');
				});

				$('#health_stats_height').on('click',function(){
					//for the border
					removeClassActive("health_stats_markers", "_marker_container_active", "_marker_container_inactive");
					$("#health_stats_height").toggleClass("_marker_container_inactive");
					$("#health_stats_height").toggleClass("_marker_container_active");
					//for the box
					removeClassActive("health_stats_markers", "health_stats_marker_box_active", "health_stats_marker_box_inactive");
					$("#health_stats_height .health_stats_marker_left").toggleClass("health_stats_marker_box_inactive");
					$("#health_stats_height .health_stats_marker_left").toggleClass("health_stats_marker_box_active");
					//for the graph
					clearDrawGraphProfile();
					drawGraphProfile('height', '<?php echo $num_points; ?>');
					//for the input fields
					load_insert_fields_h_stats('height');
					move_data_h_stats(0, 'height');
				});

				//Ajax request for loading health stats inputs
				$.ajax({
					url: "includes/handlers/ajax_load_health_stats_review.php",
					type: "POST",
					data: "stat=weight&key=0",
					cache:false,

					success: function(data){
						//alert(data);
						$('#health_stats_point_review').html(data);
					}
				});
				
				$.ajax({
					url: "includes/handlers/ajax_load_health_stats_inputs.php",
					type: "POST",
					data: "stat=weight",
					cache:false,

					success: function(data){
						//alert(data);
						$('#health_stats_point_inputs').html(data);
					}
				});
			}
			else{
				//calendar selection
				
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
			}
			
			$("#year_selected").change(function(){
				getHomeCalendar();
			});

			$("#month_selected").change(function(){
				getHomeCalendar();
			});
			
			$('#loading').show();

			//Original Ajax request for loading first posts
			$.ajax({
				url: "includes/handlers/ajax_load_posts.php",
				type: "POST",
				data: "page=1",
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
						url: "includes/handlers/ajax_load_posts.php",
						type: "POST",
						data: "page=" + page,
						cache:false,

						success: function(responseP){
							$('.posts_area').find('.nextPage').remove(); //Removes current .nextpage
							$('.posts_area').find('.noMorePosts').remove(); //Removes current next page

							$('#loading').hide();
							$('.posts_area').append(responseP);
						}
					});

				}
				return false;
			});

			//initial calendar load
			var isDoctor = ('<?php echo $isDoctor; ?>')?1:0;
			var ajaxreq = $.ajax({
				url: "includes/handlers/ajax_home_calendar.php",
				type: "POST",
				data:"month=" + month + "&year=" + year + "&view_type=" + view_type + "&isdoc=" + isDoctor,
				cache: false,

				success: function(response){
					$(".calendar_container").html(response);
				}
			});
		});
	</script>

<script>


		//control navtabs when the window is resized	
		var elementPosition = $('#profileTabs').offset();
		
        
		$(window).scroll(function(){
				
				
		        if($(window).scrollTop() > (elementPosition.top-125) ){
		              	$('#profileTabs').css('position','absolute').css('top','43px'). css('width', 'calc(100%)').css('min-width',' 705px');
		              	$('.top_bar').css( 'background-color' ,'rgb(64, 64, 64)');
		              	$('.title_tabs').css( 'margin-top' ,'45px');
		              	var marginL =  $(window).scrollTop()-122 ;
		              	$('#profileTabs').css('top', marginL+'px' );
		              	
		              
		              	
		        } else {
		            	$('#profileTabs').css('position','static') . css('width', '100%');
	            		$('.top_bar').css( 'background-color' ,'rgba(64, 64, 64,0.8)');
	            		$('.title_tabs').css( 'margin-top' ,'0px');
	            		
	            		 
		        }   
		});	

		// #coming_up_iframe fixed appeareance position
		
		


		 
		$(window).scroll(function(){
			var posComingUp = $('.wrapper').outerHeight(true) - $('#coming_up_iframe').outerHeight( true ) -189 ;
			if($(window).scrollTop() < posComingUp ){
				$('#coming_up_iframe').css('top', $(window).scrollTop());
			}
			});	



		
		$(document).ready(function(){

			
			//Button for scheduling appointments
			$('#accept_appointment_booking').click(function(){
				$.ajax({
					type: "POST",
					url: "includes/handlers/ajax_confirm_time_selection.php",
					data: $('form.book_appointment_form').serialize(), //What we send!
					cache: false,
					success: function(response){
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

			
				//$('.title_search').delay(3000).animate({ paddingTop: 74}, 800);
				
			//$('.title_search').fadeOut("fast", function () {
				//$(this).css("background-image", "url(images/backgrounds/desktop.jpg)");
				//$(this).fadeIn("fast");
			//});

			$('.grey_banner').fadeTo(500,0.8);
			$('.title_text').delay(500).animate({ paddingTop: 98}, 1000);
			$('.wrapper').delay(500).animate({ marginTop: 166}, 1000);


			var isDoctor ='<?php echo $isDoctor ?>'; 

			if(isDoctor){
				$('.nav-tabs > li'). css('width', 'calc(100% / 3)');
			}
			else{
				$('.nav-tabs > li'). css('width', '20%');
			}

           });
               


		function getHomeCalendar(){
			var month = $("#month_selected").find(":selected").val();
			var year = $("#year_selected").find(":selected").val();
			var view_type = '<?php echo $ini_view_type; ?>';
			var isDoctor = ('<?php echo $isDoctor; ?>')?1:0;
			var ajaxreq = $.ajax({
				url: "includes/handlers/ajax_home_calendar.php",
				type: "POST",
				data: "month=" + month + "&year=" + year + "&view_type=" + view_type + "&isdoc=" + isDoctor,
				cache: false,

				success: function(response){
					$(".calendar_container").html(response);
				}
			});
			$("#day_iframe").attr('src', "day_frame_home.php?d=" + day + "&m=" + month + "&y=" + year + "&vt=" + view_type);
		}

		function selectDayHome(year,month,day,view_type){
			$("#day_iframe").attr('src', "day_frame_home.php?d=" + day + "&m=" + month + "&y=" + year + "&vt=" + view_type);
			$('#selected_day_inp').val(day);
			$('#selected_month_inp').val(month);
			$('#selected_year_inp').val(year);
			var isDoctor = '<?php echo $user_obj->isDoctor()?>';
			if(isDoctor){
				var payment_method = $("#payment_type").find(":selected").val();
				var appo_type = $("#appo_type").find(":selected").val();
				var office_sel = $("input[name='office_selected']:checked").val();
				setFreeSlotsDay(year,month,day,payment_method,appo_type,office_sel);
			}
		}

		function setFreeSlotsDay(year,month,day,payment_method,appo_type,office_sel){
			$("#day_iframe2").attr('src', "day_frame_offices_selfbooking.php?d=" + day + "&m=" + month + "&y=" + year + "&pm=" + payment_method + "&at=" + appo_type + "&off=" + office_sel);
		}
	</script>
<div class="loading"></div>

<script>
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

</div>

 	<?php 
 	switch ($lang){
 		
 		case("en"):
		 	?>
			<div class="modal fade" id="confirm_self_booking" tabindex="-1"
				role="dialog" aria-labelledby="postModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
			
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"
								aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="myModalLabel">Confirm Booking</h4>
						</div>
			
						<div class="modal-body">
							<p>
								You are creating an appointment from <b> <span
									id="modal_display_time"></span></b> on <b><span
									id="modal_appo_date"></span></b>. <br> This appointment will be
								held at office: <b><span id="modal_nick"></span></b>, located at <b><span
									id="modal_addln1"></span></b> (Office: <b><span id="modal_addln3"></span></b>).
							</p>
							<form class="book_appointment_form" action="" method="POST">
								<p>Insert information about the patient</p>
								<div class="dashboard_tag_block">
					 				<p class="dashboard_tag">
										Full Name
									</p>
									<input type="text" name="pat_name" placeholder="ex: David Jimenez">
								</div>
								<div class="dashboard_tag_block">
					 				<p class="dashboard_tag">
										Patient Contact Info.
									</p>
									<input type="text" name="pat_contact" placeholder="ex: +57 555 5555555">
								</div>
								<div class="dashboard_tag_block">
					 				<p class="dashboard_tag">
										Patient Insurance
									</p>
									<input type="text" name="pat_insurance" placeholder="ex: Colmedica">
								</div>
								<div class="dashboard_tag_block">
					 				<p class="dashboard_tag">
										Additional Notes
									</p>
									<textarea name="patient_notes" placeholder="Any additional information"></textarea>
								</div>	
			
								<input type="hidden" name="year" value=""> 
								<input type="hidden"name="month" value="">
								<input type="hidden" name="day" value=""> 
								<input type="hidden" name="profile_owner" value=""> 
								<input type="hidden" name="payment_method" value=""> 
								<input type="hidden" name="ap_id" value=""> 
								<input type="hidden" name="ap_st" value=""> 
								<input type="hidden" name="ap_end" value="">
								<input type="hidden" name="external" value="1">
							</form>
						</div>
			
						<div class="modal-footer">
							<button type="button" class="btn btn-primary"
								name="accept_appointment_booking" id="accept_appointment_booking"
								style="background-color: #f95c8b;">Book Appointment</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			<?php 
			break;

		case("es"):
			?>
			<div class="modal fade" id="confirm_self_booking" tabindex="-1"
				role="dialog" aria-labelledby="postModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
			
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"
								aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="myModalLabel">Confirmar Cita</h4>
						</div>
			
						<div class="modal-body">
							<p>
								Estás creando una cita a las <b> <span
									id="modal_display_time"></span></b> el <b><span
									id="modal_appo_date"></span></b>. <br> Esta cita está¡ agendada en la oficina: <b><span id="modal_nick"></span></b>, ubicada en <b><span
									id="modal_addln1"></span></b> (Oficina: <b><span id="modal_addln3"></span></b>).
							</p>
							<form class="book_appointment_form" action="" method="POST">
								<p>Insertar información del paciente</p>
								<div class="dashboard_tag_block">
					 				<p class="dashboard_tag">
										Nombre Completo
									</p>
									<input type="text" name="pat_name" placeholder="ej: David Jimenez">
								</div>
								<div class="dashboard_tag_block">
					 				<p class="dashboard_tag">
										Información de Contacto
									</p>
									<input type="text" name="pat_contact" placeholder="ej: +57 555 5555555">
								</div>
								<div class="dashboard_tag_block">
					 				<p class="dashboard_tag">
										Seguro/Prepagada/EPS
									</p>
									<input type="text" name="pat_insurance" placeholder="ej: Colmedica">
								</div>
								<div class="dashboard_tag_block">
					 				<p class="dashboard_tag">
										Notas Adicionales
									</p>
									<textarea name="patient_notes" placeholder="Alguna nota adicional"></textarea>
								</div>	
			
								<input type="hidden" name="year" value=""> 
								<input type="hidden"name="month" value="">
								<input type="hidden" name="day" value=""> 
								<input type="hidden" name="profile_owner" value=""> 
								<input type="hidden" name="payment_method" value=""> 
								<input type="hidden" name="ap_id" value=""> 
								<input type="hidden" name="ap_st" value=""> 
								<input type="hidden" name="ap_end" value="">
								<input type="hidden" name="external" value="1">
							</form>
						</div>
			
						<div class="modal-footer">
							<button type="button" class="btn btn-primary"
								name="accept_appointment_booking" id="accept_appointment_booking"
								style="background-color: #f95c8b;">Agendar Cita</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						</div>
					</div>
				</div>
			</div>
			<?php 
			break;
 	}
 	?>


</body>
</html>