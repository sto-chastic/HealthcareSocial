<?php
include ("includes/search_results_header.php");

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

?>

<style>
    .list_search_sub p {
        color: white;
}
    .top_banner_title {
        height: 180px;
}

</style>

<div class="ds_wrapper">
	<?php
		if(isset($_SESSION ['searchparams'])){
	?>
<div class="ds_left_column" id="main_column">
	<div class="ds_results_all">
		<div class="ds_results_top">
		<?php 
			switch ($lang){
					 		
				case("en"):
		?>
					<div class="ds_title_doc">
						<p>Doctor</p>
					</div>
					<div class="ds_title_loc">
						<p>Location</p>
					</div>
					<div class="ds_title_ava">
						<p>Availability</p>
						<span style="font-size: 10px">(3 closest available dates shown)</span>
					</div>
		<?php 
			        break;
				
				case("es"):
		?>
					<div class="ds_title_doc">
						<p>Doctor</p>
					</div>
					<div class="ds_title_loc">
						<p>Ubicación</p>
					</div>
					<div class="ds_title_ava">
						<p>Disponibilidad</p>
						<span style="font-size: 10px">(Las 3 fechas disponibles más cercanas)</span>
					</div>
		<?php 
					break;
			}
		?>

		</div>
	<?php 
		}
	?>
    			<?php
    				if(isset($_SESSION ['searchparams'])){
    				            $crypt = new Crypt();
							$List = $SnM->genFilteredDocs ( $_SESSION ['searchparams'] [0], $_SESSION ['searchparams'] [1], $_SESSION ['searchparams'] [2], $_SESSION ['searchparams'] [3], $_SESSION ['searchparams'] [4], $_SESSION ['searchparams'] [5], $_SESSION ['searchparams'] [6], $_SESSION ['searchparams'] [7] );
							
							if (! empty ( $List [0] )) {
								
								$docsToDisplay = $SnM->givePoints ( $List [0], $List [2], $_SESSION ['searchparams'] [4], $List [1], $_SESSION ['d8'], $payment, $_SESSION ['searchparams'] [7] );
								$rankedDocs = $SnM->rankDocs($docsToDisplay);
								$docHeadCount = $SnM->docCount($rankedDocs);
								
								$_SESSION ['doc_count'] = $docHeadCount;
								$_SESSION ['docsToDisplay'] = $rankedDocs;
								
								$date = $_SESSION ['d8'];
								
								$num = 0; // the number to display beside the search result
								$markerInfo = [ ];
								$docIDs = array_keys ( $docHeadCount );
								
								$printed = array_fill_keys ( $docIDs, FALSE );
								
								// print_r($rankedDocs); //this array has office info
								foreach ( $rankedDocs as $doc ) {
									if (! $printed [$doc['username']]) {
										$num ++;
										if ($docHeadCount[$doc['username']] === 1) {
											// echo $printed[$doc['username']]?"TRUE":"FALSE";
											switch ($doc['office']) {
												case 1 :
													{
														$adXln1 = "ad1ln1";
														$adXln2 = "ad1ln2";
														$adXln3 = "ad1ln3";
														$adXlat = "ad1lat";
														$adXlng = "ad1lng";
														break;
													}
												case 2 :
													{
														$adXln1 = "ad2ln1";
														$adXln2 = "ad2ln2";
														$adXln3 = "ad2ln3";
														$adXlat = "ad2lat";
														$adXlng = "ad2lng";
														break;
													}
												case 3 :
													{
														$adXln1 = "ad3ln1";
														$adXln2 = "ad3ln2";
														$adXln3 = "ad3ln3";
														$adXlat = "ad3lat";
														$adXlng = "ad3lng";
														break;
													}
											}
											$stmt = $con->prepare ( "SELECT first_name, last_name, profile_pic, username FROM users WHERE username = ?" );
											$doc_username_e = $crypt->EncryptU($doc['username']);
											$stmt->bind_param ("s", $doc_username_e);
											$stmt->execute ();
											$docSQL = $stmt->get_result ();
											$currentDoc = mysqli_fetch_assoc($docSQL);
											if (mysqli_num_rows ($docSQL) != 1) {
												continue;
											}
											$stmt = $con->prepare ( "SELECT specializations, md_conn, pat_conn, $adXln1 as adln1, $adXln2 as adln2, $adXln3 as adln3, $adXlat as adlat, $adXlng as adlng FROM basic_info_doctors WHERE username = ?" );
											$stmt->bind_param ( "i", $doc['username'] );
											$stmt->execute ();
											$docSQL = $stmt->get_result ();
											$currentDoc = array_merge ( $currentDoc, mysqli_fetch_assoc($docSQL));
											$mainSpecialization = unserialize ( $currentDoc['specializations'] );
											$specQuery = mysqli_query ( $con, "SELECT en, es FROM specializations WHERE id = $mainSpecialization[0]" );
											$specList = mysqli_fetch_assoc ( $specQuery );
											//$mainSpec = substr ( $specList ['es'], 0, strpos ( $specList ['en'], "\\" ) );
											if( strpos ( $specList [$lang], "\\" ) !== false){
												$mainSpec = substr ( $specList [$lang], 0, strpos ( $specList [$lang], "\\" ));
											}
											else{
												$mainSpec = $specList [$lang];
											}
											$markerInfo [] = array (
													'num' => $num,
													'firstNLast' => $currentDoc ['first_name'] . " " . $currentDoc ['last_name'],
													'adln1' => $currentDoc ['adln1'],
													'adln2' => $currentDoc ['adln2'],
													'adln3' => $currentDoc ['adln3'],
													'adlat' => $currentDoc ['adlat'],
													'adlng' => $currentDoc ['adlng'] 
											);
											// TODO:bookmark;
											
											$doc_user_obj = new User($con, $doc['username'], $doc_username_e);
											$upToDate = $doc_user_obj->isUpToDate();
											if (!$upToDate){
											    continue;
											}
											
											$app_dur_tab = $doc_user_obj->getAppoDurationTable();// ;
											$stmt = $con->prepare ( "SELECT id,appo_type,duration FROM $app_dur_tab WHERE appo_type LIKE ? OR appo_type LIKE ? LIMIT 1" );
											$at_en = '_irst__ime';
											$at_es = '_rimera__ez';
											$stmt->bind_param ( "ss", $at_en, $at_es );
											$stmt->execute ();
											$verification_query = $stmt->get_result ();
											
											if (mysqli_num_rows ( $verification_query ) == 1) {
												$_id_arr = mysqli_fetch_assoc ( $verification_query );
												$appointment_type = $_id_arr ['appo_type'];
												$appo_type_id = $_id_arr ['id'];
												$appointment_duration = $_id_arr ['duration'];
											} else {
												continue;
											}
											$available_days_array = $SnM->closestAppointmentDays ( $date, $doc['username'], $doc_username_e, $doc['office'], $payment, $appointment_duration );
											$days_processed = [ ];
											$days_content_arr = [ ];
											foreach ( $available_days_array as $day_raw ) {
												
												// Construction of the dates title
												$stmt = $con->prepare ( "SELECT d,dw,m,y FROM calendar_table WHERE dt=?" );
												$stmt->bind_param ( "s", $day_raw );
												$stmt->execute ();
												
												$q = $stmt->get_result ();
												// if(mysqli_num_rows($q) == 0){
												// continue;
												// }
												$arr = mysqli_fetch_assoc ( $q );
												$_temp_str = "";
												
												switch ($lang) {
													case "en" :
														$week_tab_col = "days_short_eng";
														$months_tab_col = "months_eng";
														break;
													case "es" :
														$week_tab_col = "days_short_es";
														$months_tab_col = "months_es";
														break;
												}
												
												$year = $arr ['y'];
												$month = $arr ['m'];
												
												$stmt = $con->prepare ( "SELECT $week_tab_col FROM days_week WHERE dw=?" );
												$stmt->bind_param ( "i", $arr ['dw'] );
												$stmt->execute ();
												
												$query = $stmt->get_result ();
												$days_arr = mysqli_fetch_assoc ( $query );
												
												$_temp_str .= $days_arr [$week_tab_col] . "<br>";
												
												$stmt = $con->prepare ( "SELECT $months_tab_col FROM months WHERE id=?" );
												$stmt->bind_param ( "i", $arr ['m'] );
												$stmt->execute ();
												
												$query = $stmt->get_result ();
												$month_arr = mysqli_fetch_assoc ( $query );
												
												$_temp_str .= substr ( $month_arr [$months_tab_col], 0, 3 ) . ", " . $arr ['d'];
												
												$days_processed [] = $_temp_str;
												
												// Times content
												
												// TODO: bookmark;
												$appointments_calendar = new Appointments_Calendar ( $con, $doc['username'], $doc_username_e, $year, $month );
												
												$booking_info_array = array (
														"doc_name" => $currentDoc['first_name'] . " " . $currentDoc['last_name'],
														"year" => $year,
														"month" => $month,
														"day" => $arr ['d'],
														"week_day" => $days_arr [$week_tab_col],
														"doctor_username" => $doc ['username'],
														"payment_method" => $payment,
														"appo_type_id" => $appo_type_id,
														"insurance_name" => ucwords ( $_SESSION ['i_name'] ),
														"adln1" => $currentDoc['adln1'],
														"adln2" => $currentDoc['adln2'],
														"time_st" => "",
														"time_end" => "",
														"display_time" => "" 
												);
												
												if ($payment == 'insu') {
													$office_arr = array (
															"0" => $doc ['office'] 
													);
													$day_content = $appointments_calendar->getDay_searchResults ( $arr ['d'], $payment, $appointment_duration, $booking_info_array, $office_arr, FALSE );
													$days_content_arr [] = $day_content;
												} elseif ($payment == 'part') {
													$day_content = $appointments_calendar->getDay_searchResults ( $arr ['d'], $payment, $appointment_duration, $booking_info_array, NULL, FALSE );
													$days_content_arr [] = $day_content;
												}
											}
											
											//if (empty ( $days_processed )) {
											//	continue;
											//}
											$prof_link = bin2hex($doc_username_e);
											echo <<<EOS
                        <div class="ds_result" name="ds_result_$num">
                            <div class="ds_doctor_data">
                                <div class="ds_number">
                                    $num
                                </div>
                                <div class="ds_profile_pic">
                                		<img src={$txt->entities($doc_user_obj->getProfilePicFast())} alt="Profile pic">
                                </div>
                                <div class="ds_docInfo">
                                <a href="$prof_link">
                                {$txt->entities($currentDoc['first_name'])} {$txt->entities($currentDoc['last_name'])} <br>
                                    <p>$mainSpec</p>
                                </a>
                        </div>
EOS;
?>
						<ul class="ds_commsLikeSearch">
							<li>
                                <ul class="view_like">
                                <li><img src="assets/images/icons/docconnectionsc.png"></li>
                                <li>
		                    		<div class='num_posts' href='requests.php'> <?php echo $txt->entities($currentDoc['md_conn']);?>
			                    		
			                    		<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<ul class='connection_data' id='deep_blue'><p class="connections_title">Doctor Connections</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<ul class='connection_data' id='deep_blue'><p class="connections_title">Conexiones: Doctores</p>
									<?php 
												break;
										}
									?>
									</ul>
								</div>
		                		</li>
						</ul>
					</li>
					<li id="division" style="border-right: 1px solid #ddd; margin: 0px 10px;">
					</li>

					
					<li>
						<ul class="view_like" > 
							<li><img src="assets/images/icons/patconnectionsc.png"></li>
		                    	<li>
			                    	<div class='num_likes' href='requests.php'><?php echo $txt->entities($currentDoc['pat_conn']); ?>
			                    		
									<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<ul class='connection_data' ><p class="connections_title">Patient Connections</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<ul class='connection_data' ><p class="connections_title">Conexiones: Pacientes</p>
									<?php 
												break;
										}
									?>
			                    		
									</ul>
								</div>
		                    	</li>	
						</ul>
					</li>
				</ul>
			</div>	
<?php
                                    			echo <<<EOS
                                
                                <div class="ds_docDist">
                                    <div class="ds_address_box" id="ds_adress_{$num}" name="active">
                                    <p>{$txt->entities($currentDoc['adln1'])}</p>
                                    </div>
                                </div>
EOS;
                                    			if(!array_key_exists(0, $days_processed) && !array_key_exists(1, $days_processed) && !array_key_exists(2, $days_processed)){
                                    			    switch($lang){
                                    			        case("en"): $no_availability_mssg = "This doctor has no available appointments available soon.";
                                    			        break;
                                    			        case("es"): $no_availability_mssg = "Este doctor no tiene citas disponibles proximamente.";
                                    			        break;
                                    			    }
                                    			    echo '
                                    <div class="ds_availability">
                                            <div class="ds_ava_content">'.
                                            $no_availability_mssg
        								        .'</div>
                                    </div>
                                </div>';
                                    			} else {
                                    			    switch($lang){
                                    			        case("en"): $no_availability_mssg = "No available appointments for this date.";
                                    			        break;
                                    			        case("es"): $no_availability_mssg = "No hay citas disponibles para esta fecha.";
                                    			        break;
                                    			    }
											echo '
                                <div class="ds_availability">
                                        <div class="ds_ava_banner">
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 0, $days_processed )) ? $days_processed [0] : 'NA') . '
											</div>
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 1, $days_processed )) ? $days_processed [1] : 'NA') . '
											</div>
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 2, $days_processed )) ? $days_processed [2] : 'NA') . '
											</div>
									   </div>
                                        <div class="ds_ava_content">
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 0, $days_content_arr )) ? $days_content_arr [0] : $no_availability_mssg) . '
											</div>
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 1, $days_content_arr )) ? $days_content_arr [1] : $no_availability_mssg) . '
											</div>
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 2, $days_content_arr )) ? $days_content_arr [2] : $no_availability_mssg) . '
											</div>
									   </div>
                                </div>
                            </div>';
                                    			}
										} else {
											$positions = array_keys ( array_column ( $rankedDocs, 'username' ), $doc ['username'] );
											// print_r($positions);
											if (count ( $positions ) == 2) {
												$doc = $rankedDocs [$positions [0]];
												$doc2 = $rankedDocs [$positions [1]];
												switch ($doc ['office']) {
													case 1 :
														{
															$adXln1 = "ad1ln1";
															$adXln2 = "ad1ln2";
															$adXln3 = "ad1ln3";
															$adXlat = "ad1lat";
															$adXlng = "ad1lng";
															break;
														}
													case 2 :
														{
															$adXln1 = "ad2ln1";
															$adXln2 = "ad2ln2";
															$adXln3 = "ad2ln3";
															$adXlat = "ad2lat";
															$adXlng = "ad2lng";
															break;
														}
													case 3 :
														{
															$adXln1 = "ad3ln1";
															$adXln2 = "ad3ln2";
															$adXln3 = "ad3ln3";
															$adXlat = "ad3lat";
															$adXlng = "ad3lng";
															break;
														}
												}
												
												switch ($doc2 ['office']) {
													case 1 :
														{
															$adX2ln1 = "ad1ln1";
															$adX2ln2 = "ad1ln2";
															$adX2ln3 = "ad1ln3";
															$adX2lat = "ad1lat";
															$adX2lng = "ad1lng";
															break;
														}
													case 2 :
														{
															$adX2ln1 = "ad2ln1";
															$adX2ln2 = "ad2ln2";
															$adX2ln3 = "ad2ln3";
															$adX2lat = "ad2lat";
															$adX2lng = "ad2lng";
															break;
														}
													case 3 :
														{
															$adX2ln1 = "ad3ln1";
															$adX2ln2 = "ad3ln2";
															$adX2ln3 = "ad3ln3";
															$adX2lat = "ad3lat";
															$adX2lng = "ad3lng";
															break;
														}
												}
												$stmt = $con->prepare ( "SELECT first_name, last_name, profile_pic FROM users WHERE username = ?" );
												$doc_username_e = $crypt->EncryptU($doc['username']);
												$stmt->bind_param ( "i", $doc_username_e);
												$stmt->execute ();
												$docSQL = $stmt->get_result ();
												$currentDoc = mysqli_fetch_assoc ( $docSQL );
												
												$stmt = $con->prepare ( "SELECT specializations, md_conn, pat_conn, $adXln1 as adln1, $adXln2 as adln2, $adXln3 as adln3, $adXlat as adlat, $adXlng as adlng, $adX2ln1 as ad2ln1, $adX2ln2 as ad2ln2, $adX2ln3 as ad2ln3, $adX2lat as ad2lat, $adX2lng as ad2lng FROM basic_info_doctors WHERE username = ?" );
												$stmt->bind_param ( "i", $doc ['username'] );
												$stmt->execute ();
												$docSQL = $stmt->get_result ();
												$currentDoc = array_merge ( $currentDoc, mysqli_fetch_assoc ( $docSQL ) );
												$mainSpecialization = unserialize ( $currentDoc ['specializations'] );
												$specQuery = mysqli_query ( $con, "SELECT en, es FROM specializations WHERE id = $mainSpecialization[0]" );
												$specList = mysqli_fetch_assoc ( $specQuery );
												if( strpos ( $specList [$lang], "\\" ) !== false){
													$mainSpec = substr ( $specList [$lang], 0, strpos ( $specList [$lang], "\\" ));
												}
												else{
													$mainSpec = $specList [$lang];
												}
												$markerInfo [] = array (
														'num' => $num . "a",
														'firstNLast' => $currentDoc ['first_name'] . " " . $currentDoc ['last_name'],
														'adln1' => $currentDoc ['adln1'],
														'adln2' => $currentDoc ['adln2'],
														'adln3' => $currentDoc ['adln3'],
														'adlat' => $currentDoc ['adlat'],
														'adlng' => $currentDoc ['adlng'] 
												);
												$markerInfo [] = array (
														'num' => $num . "b",
														'firstNLast' => $currentDoc ['first_name'] . " " . $currentDoc ['last_name'],
														'adln1' => $currentDoc ['ad2ln1'],
														'adln2' => $currentDoc ['ad2ln2'],
														'adln3' => $currentDoc ['ad2ln3'],
														'adlat' => $currentDoc ['ad2lat'],
														'adlng' => $currentDoc ['ad2lng'] 
												);
												
												// TODO:bookmark;
												$doc_user_obj = new User($con, $doc['username'], $doc_username_e);
												$app_dur_tab = $doc_user_obj->getAppoDurationTable();// ;
												
												$stmt = $con->prepare ( "SELECT id,appo_type,duration FROM $app_dur_tab WHERE appo_type LIKE ? OR appo_type LIKE ? LIMIT 1" );
												
												$at_en = '_irst__ime';
												$at_es = '_rimera _ez';
												$stmt->bind_param ( "ss", $at_en, $at_es );
												$stmt->execute ();
												$verification_query = $stmt->get_result ();
												
												if (mysqli_num_rows ( $verification_query ) == 1) {
													$_id_arr = mysqli_fetch_assoc ( $verification_query );
													$appointment_type = $_id_arr ['appo_type'];
													$appo_type_id = $_id_arr ['id'];
													$appointment_duration = $_id_arr ['duration'];
												} else {
													continue;
												}
												
												$available_days_array = $SnM->closestAppointmentDays ( $date, $doc ['username'],$doc_username_e, $doc ['office'], $payment, $appointment_duration );
												
												$days_processed = [ ];
												$days_content_arr = [ ];
												foreach ( $available_days_array as $day_raw ) {
													
													// Construction of the dates title
													$stmt = $con->prepare ( "SELECT d,dw,m,y FROM calendar_table WHERE dt=?" );
													$stmt->bind_param ( "s", $day_raw );
													$stmt->execute ();
													
													$q = $stmt->get_result ();
													// if(mysqli_num_rows($q) == 0){
													// continue;
													// }
													$arr = mysqli_fetch_assoc ( $q );
													$_temp_str = "";
													
													switch ($lang) {
														case "en" :
															$week_tab_col = "days_short_eng";
															$months_tab_col = "months_eng";
															break;
														case "es" :
															$week_tab_col = "days_short_es";
															$months_tab_col = "months_es";
															break;
													}
													
													$year = $arr ['y'];
													$month = $arr ['m'];
													
													$stmt = $con->prepare ( "SELECT $week_tab_col FROM days_week WHERE dw=?" );
													$stmt->bind_param ( "i", $arr ['dw'] );
													$stmt->execute ();
													
													$query = $stmt->get_result ();
													$days_arr = mysqli_fetch_assoc ( $query );
													
													$_temp_str .= $days_arr [$week_tab_col] . "<br>";
													
													$stmt = $con->prepare ( "SELECT $months_tab_col FROM months WHERE id=?" );
													$stmt->bind_param ( "i", $arr ['m'] );
													$stmt->execute ();
													
													$query = $stmt->get_result ();
													$month_arr = mysqli_fetch_assoc ( $query );
													
													$_temp_str .= substr ( $month_arr [$months_tab_col], 0, 3 ) . ", " . $arr ['d'];
													
													$days_processed [] = $_temp_str;
													
													// Times content
													
													// TODO: bookmark;
													$appointments_calendar = new Appointments_Calendar ( $con, $doc ['username'], $crypt->EncryptU($doc ['username']), $year, $month );
													
													$booking_info_array = array (
															"doc_name" => $currentDoc ['first_name'] . " " . $currentDoc ['last_name'],
															"year" => $year,
															"month" => $month,
															"day" => $arr ['d'],
															"week_day" => $days_arr [$week_tab_col],
															"doctor_username" => $doc ['username'],
															"payment_method" => $payment,
															"appo_type_id" => $appo_type_id,
															"insurance_name" => ucwords ( $_SESSION ['i_name'] ),
															"adln1" => $currentDoc ['adln1'],
															"adln2" => $currentDoc ['adln2'],
															"time_st" => "",
															"time_end" => "",
															"display_time" => "" 
													);
													
													if ($payment == 'insu') {
														$office_arr = array (
																"0" => $doc ['office'] 
														);
														$day_content = $appointments_calendar->getDay_searchResults ( $arr ['d'], $payment, $appointment_duration, $booking_info_array, $office_arr, FALSE );
														$days_content_arr [] = $day_content;
													} elseif ($payment == 'part') {
														$day_content = $appointments_calendar->getDay_searchResults ( $arr ['d'], $payment, $appointment_duration, $booking_info_array, NULL, FALSE );
														$days_content_arr [] = $day_content;
													}
												}
												
												if (empty ( $days_processed )) {
													continue;
												}
												
												$_aux_array ["ds_adress_" . $num] = $doc ['username'];
												$prof_link = bin2hex($crypt->EncryptU($doc ['username']));
												echo <<<EOS
                        <div class="ds_result" name="ds_result_$num">
                            <div class="ds_doctor_data">  
                                <div class="ds_number">
                                    $num
                                </div>
                                <div class="ds_profile_pic">
                                		<img src={$txt->entities($doc_user_obj->getProfilePicFast())} alt="Profile pic">
                                </div>
                                <div class="ds_docInfo">
								<a href="$prof_link">
                                {$txt->entities($currentDoc['first_name'])} {$txt->entities($currentDoc['last_name'])} <br>
                                    <p>$mainSpec</p>
</a>
                        </div>
EOS;
?>
						<ul class="ds_commsLikeSearch">
							<li>
                                <ul class="view_like">
                                <li><img src="assets/images/icons/docconnectionsc.png"></li>
                                <li>
		                    		<div class='num_posts' href='requests.php'> <?php echo $txt->entities($currentDoc['md_conn']);?>
			                    		
			                    		<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<ul class='connection_data' id='deep_blue'><p class="connections_title">Doctor Connections</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<ul class='connection_data' id='deep_blue'><p class="connections_title">Conexiones: Doctores</p>
									<?php 
												break;
										}
									?>
									</ul>
								</div>
		                		</li>
						</ul>
					</li>
					<li id="division" style="border-right: 1px solid #ddd; margin: 0px 10px;">
					</li>

					
					<li>
						<ul class="view_like" > 
							<li><img src="assets/images/icons/patconnectionsc.png"></li>
		                    	<li>
			                    	<div class='num_likes' href='requests.php'><?php echo $txt->entities($currentDoc['pat_conn']); ?>
			                    		
									<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<ul class='connection_data' ><p class="connections_title">Patient Connections</p>
									<?php 
										        break;
											
											case("es"):
									?>
												<ul class='connection_data' ><p class="connections_title">Conexiones de Pacientes</p>
									<?php 
												break;
										}
									?>
			                    		
									</ul>
								</div>
		                    	</li>	
						</ul>
					</li>
				</ul>
			</div>	
<?php
                                    			echo <<<EOS
                                
                                <div class="ds_docDist">
                                		
                                    <div class="ds_address_box" id="ds_adress_{$num}a office_num_{$doc['office']}" name="active">
                                    		<p>{$txt->entities($currentDoc['adln1'])}</p>
                                    </div>
								
                                    <div class="ds_address_box inactive" id="ds_adress_{$num}b office_num_{$doc2['office']}">
                                    		<p class="inactive">{$txt->entities($currentDoc['ad2ln1'])}</p>
                                    </div>
									
                                </div>
EOS;
												echo '
                                <div class="ds_availability" id="ds_availability_' . $num . '">
                                        <div class="ds_ava_banner">
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 0, $days_processed )) ? $days_processed [0] : 'NA') . '
											</div>
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 1, $days_processed )) ? $days_processed [1] : 'NA') . '
											</div>
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 2, $days_processed )) ? $days_processed [2] : 'NA') . '
											</div>
									   </div>
                                        <div class="ds_ava_content">
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 0, $days_content_arr )) ? $days_content_arr [0] : '') . '
											</div>
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 1, $days_content_arr )) ? $days_content_arr [1] : '') . '
											</div>
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 2, $days_content_arr )) ? $days_content_arr [2] : '') . '
											</div>
									   </div>
                                </div>
                            </div>';
											} else {
												$doc = $rankedDocs [$positions [0]];
												$doc2 = $rankedDocs [$positions [1]];
												$doc3 = $rankedDocs [$positions [2]];
												switch ($doc ['office']) {
													case 1 :
														{
															$adXln1 = "ad1ln1";
															$adXln2 = "ad1ln2";
															$adXln3 = "ad1ln3";
															$adXlat = "ad1lat";
															$adXlng = "ad1lng";
															break;
														}
													case 2 :
														{
															$adXln1 = "ad2ln1";
															$adXln2 = "ad2ln2";
															$adXln3 = "ad2ln3";
															$adXlat = "ad2lat";
															$adXlng = "ad2lng";
															break;
														}
													case 3 :
														{
															$adXln1 = "ad3ln1";
															$adXln2 = "ad3ln2";
															$adXln3 = "ad3ln3";
															$adXlat = "ad3lat";
															$adXlng = "ad3lng";
															break;
														}
												}
												switch ($doc2 ['office']) {
													case 1 :
														{
															$adX2ln1 = "ad1ln1";
															$adX2ln2 = "ad1ln2";
															$adX2ln3 = "ad1ln3";
															$adX2lat = "ad1lat";
															$adX2lng = "ad1lng";
															break;
														}
													case 2 :
														{
															$adX2ln1 = "ad2ln1";
															$adX2ln2 = "ad2ln2";
															$adX2ln3 = "ad2ln3";
															$adX2lat = "ad2lat";
															$adX2lng = "ad2lng";
															break;
														}
													case 3 :
														{
															$adX2ln1 = "ad3ln1";
															$adX2ln2 = "ad3ln2";
															$adX2ln3 = "ad3ln3";
															$adX2lat = "ad3lat";
															$adX2lng = "ad3lng";
															break;
														}
												}
												switch ($doc3 ['office']) {
													case 1 :
														{
															$adX3ln1 = "ad1ln1";
															$adX3ln2 = "ad1ln2";
															$adX3ln3 = "ad1ln3";
															$adX3lat = "ad1lat";
															$adX3lng = "ad1lng";
															break;
														}
													case 2 :
														{
															$adX3ln1 = "ad2ln1";
															$adX3ln2 = "ad2ln2";
															$adX3ln3 = "ad2ln3";
															$adX3lat = "ad2lat";
															$adX3lng = "ad2lng";
															break;
														}
													case 3 :
														{
															$adX3ln1 = "ad3ln1";
															$adX3ln2 = "ad3ln2";
															$adX3ln3 = "ad3ln3";
															$adX3lat = "ad3lat";
															$adX3lng = "ad3lng";
															break;
														}
												}
												$stmt = $con->prepare ( "SELECT first_name, last_name, profile_pic FROM users WHERE username = ?" );
												$doc_username_e = $crypt->EncryptU($doc['username']);
												$stmt->bind_param ( "i", $doc_username_e);
												$stmt->execute ();
												$docSQL = $stmt->get_result ();
												$currentDoc = mysqli_fetch_assoc ( $docSQL );
												
												$stmt = $con->prepare ( "SELECT specializations, md_conn, pat_conn, $adXln1 as adln1, $adXln2 as adln2, $adXln3 as adln3, $adXlat as adlat, $adXlng as adlng, $adX2ln1 as ad2ln1, $adX2ln2 as ad2ln2, $adX2ln3 as ad2ln3, $adX2lat as ad2lat, $adX2lng as ad2lng, $adX3ln1 as ad3ln1, $adX3ln2 as ad3ln2, $adX3ln3 as ad3ln3, $adX3lat as ad3lat, $adX3lng as ad3lng FROM basic_info_doctors WHERE username = ?" );
												$stmt->bind_param ( "i", $doc ['username'] );
												$stmt->execute ();
												$docSQL = $stmt->get_result ();
												$currentDoc = array_merge ( $currentDoc, mysqli_fetch_assoc ( $docSQL ) );
												$mainSpecialization = unserialize ( $currentDoc ['specializations'] );
												$specQuery = mysqli_query ( $con, "SELECT en, es FROM specializations WHERE id = $mainSpecialization[0]" );
												$specList = mysqli_fetch_assoc ( $specQuery );
												if( strpos ( $specList [$lang], "\\" ) !== false){
													$mainSpec = substr ( $specList [$lang], 0, strpos ( $specList [$lang], "\\" ));
												}
												else{
													$mainSpec = $specList [$lang];
												}
												$markerInfo [] = array (
														'num' => $num . "a",
														'firstNLast' => $currentDoc ['first_name'] . " " . $currentDoc ['last_name'],
														'adln1' => $currentDoc ['adln1'],
														'adln2' => $currentDoc ['adln2'],
														'adln3' => $currentDoc ['adln3'],
														'adlat' => $currentDoc ['adlat'],
														'adlng' => $currentDoc ['adlng'] 
												);
												$markerInfo [] = array (
														'num' => $num . "b",
														'firstNLast' => $currentDoc ['first_name'] . " " . $currentDoc ['last_name'],
														'adln1' => $currentDoc ['ad2ln1'],
														'adln2' => $currentDoc ['ad2ln2'],
														'adln3' => $currentDoc ['ad2ln3'],
														'adlat' => $currentDoc ['ad2lat'],
														'adlng' => $currentDoc ['ad2lng'] 
												);
												$markerInfo [] = array (
														'num' => $num . "c",
														'firstNLast' => $currentDoc ['first_name'] . " " . $currentDoc ['last_name'],
														'adln1' => $currentDoc ['ad3ln1'],
														'adln2' => $currentDoc ['ad3ln2'],
														'adln3' => $currentDoc ['ad3ln3'],
														'adlat' => $currentDoc ['ad3lat'],
														'adlng' => $currentDoc ['ad3lng'] 
												);
												
												// TODO:bookmark;
												$doc_user_obj = new User($con, $doc['username'], $doc_username_e);
												$app_dur_tab = $doc_user_obj->getAppoDurationTable();// ;
												
												$stmt = $con->prepare ( "SELECT id,appo_type,duration FROM $app_dur_tab WHERE appo_type LIKE ? OR appo_type LIKE ? LIMIT 1" );
												
												$at_en = '_irst__ime';
												$at_es = '_rimera _ez';
												$stmt->bind_param ( "ss", $at_en, $at_es );
												$stmt->execute ();
												$verification_query = $stmt->get_result ();
												
												if (mysqli_num_rows ( $verification_query ) == 1) {
													$_id_arr = mysqli_fetch_assoc ( $verification_query );
													$appointment_type = $_id_arr ['appo_type'];
													$appo_type_id = $_id_arr ['id'];
													$appointment_duration = $_id_arr ['duration'];
												} else {
													continue;
												}
												
												$available_days_array = $SnM->closestAppointmentDays ( $date, $doc ['username'],$doc_username_e, $doc ['office'], $payment, $appointment_duration );
												
												$days_processed = [ ];
												$days_content_arr = [ ];
												foreach ( $available_days_array as $day_raw ) {
													
													// Construction of the dates title
													$stmt = $con->prepare ( "SELECT d,dw,m,y FROM calendar_table WHERE dt=?" );
													$stmt->bind_param ( "s", $day_raw );
													$stmt->execute ();
													
													$q = $stmt->get_result ();
													
													$arr = mysqli_fetch_assoc ( $q );
													$_temp_str = "";
													
													switch ($lang) {
														case "en" :
															$week_tab_col = "days_short_eng";
															$months_tab_col = "months_eng";
															break;
														case "es" :
															$week_tab_col = "days_short_es";
															$months_tab_col = "months_es";
															break;
													}
													
													$year = $arr ['y'];
													$month = $arr ['m'];
													
													$stmt = $con->prepare ( "SELECT $week_tab_col FROM days_week WHERE dw=?" );
													$stmt->bind_param ( "i", $arr ['dw'] );
													$stmt->execute ();
													
													$query = $stmt->get_result ();
													$days_arr = mysqli_fetch_assoc ( $query );
													
													$_temp_str .= $days_arr [$week_tab_col] . "<br>";
													
													$stmt = $con->prepare ( "SELECT $months_tab_col FROM months WHERE id=?" );
													$stmt->bind_param ( "i", $arr ['m'] );
													$stmt->execute ();
													
													$query = $stmt->get_result ();
													$month_arr = mysqli_fetch_assoc ( $query );
													
													$_temp_str .= substr ( $month_arr [$months_tab_col], 0, 3 ) . ", " . $arr ['d'];
													
													$days_processed [] = $_temp_str;
													
													// Times content
													
													// TODO: bookmark;
													$appointments_calendar = new Appointments_Calendar ( $con, $doc['username'], $doc_username_e, $year, $month );
													
													$booking_info_array = array (
															"doc_name" => $currentDoc ['first_name'] . " " . $currentDoc ['last_name'],
															"year" => $year,
															"month" => $month,
															"day" => $arr ['d'],
															"week_day" => $days_arr [$week_tab_col],
															"doctor_username" => $doc ['username'],
															"payment_method" => $payment,
															"appo_type_id" => $appo_type_id,
															"insurance_name" => ucwords ( $_SESSION ['i_name'] ),
															"adln1" => $currentDoc ['adln1'],
															"adln2" => $currentDoc ['adln2'],
															"time_st" => "",
															"time_end" => "",
															"display_time" => "" 
													);
													
													if ($payment == 'insu') {
														$office_arr = array (
																"0" => $doc ['office'] 
														);
														$day_content = $appointments_calendar->getDay_searchResults ( $arr ['d'], $payment, $appointment_duration, $booking_info_array, $office_arr, FALSE );
														$days_content_arr [] = $day_content;
													} elseif ($payment == 'part') {
														$day_content = $appointments_calendar->getDay_searchResults ( $arr ['d'], $payment, $appointment_duration, $booking_info_array, NULL, FALSE );
														$days_content_arr [] = $day_content;
													}
												}
												
												if (empty ( $days_processed )) {
													continue;
												}
												
												$_aux_array ["ds_adress_" . $num] = $doc ['username'];
												$prof_link = bin2hex($crypt->EncryptU($doc ['username']));
												echo <<<EOS
                        <div class="ds_result" name="ds_result_$num">
                            <div class="ds_doctor_data">        
                                <div class="ds_number">
                                    $num
                                </div>
                                <div class="ds_profile_pic">
                                		<img src={$txt->entities($doc_user_obj->getProfilePicFast())} alt="Profile pic">
                                </div>
                                <div class="ds_docInfo">
								<a href="$prof_link">
                                {$txt->entities($currentDoc['first_name'])} {$txt->entities($currentDoc['last_name'])} <br>
                                		<p>{$txt->entities($mainSpec)}</p>
</a>
                              </div>
EOS;
?>
						<ul class="ds_commsLikeSearch">
							<li>
                                <ul class="view_like">
                                <li><img src="assets/images/icons/docconnectionsc.png"></li>
                                <li>
		                    		<div class='num_posts' href='requests.php'> <?php echo $txt->entities($currentDoc['md_conn']);?>
			                    		
			                    		<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<ul class='connection_data' id='deep_blue'><p class="connections_title">Doctor Connections</p>
									<?php 
										        break;
											
											case("es"):
									?>

												<ul class='connection_data' id='deep_blue'><p class="connections_title">Conexiones: Doctores</p>

									<?php 
												break;
										}
									?>
									</ul>
								</div>
		                		</li>
						</ul>
					</li>
					<li id="division" style="border-right: 1px solid #ddd; margin: 0px 10px;">
					</li>

					
					<li>
						<ul class="view_like" > 
							<li><img src="assets/images/icons/patconnectionsc.png"></li>
		                    	<li>
			                    	<div class='num_likes' href='requests.php'><?php echo $txt->entities($currentDoc['pat_conn']); ?>
			                    		
									<?php 
										switch ($lang){
												 		
											case("en"):
									?>
												<ul class='connection_data' ><p class="connections_title">Patient Connections</p>
									<?php 
										        break;
											
											case("es"):
									?>

												<ul class='connection_data' ><p class="connections_title">Conexiones de Pacientes</p>

									<?php 
												break;
										}
									?>
			                    		
									</ul>
								</div>
		                    	</li>	
						</ul>
					</li>
				</ul>
			</div>	
<?php
                                    			echo <<<EOS
                                
                                <div class="ds_docDist">
                                    <div class="ds_address_box" id="ds_adress_{$num}a office_num_{$doc['office']}" name="active">
                                    		<p>{$txt->entities($currentDoc['adln1'])}</p>
                                    </div>
                                    <div class="ds_address_box inactive" id="ds_adress_{$num}b office_num_{$doc2['office']}">
                                    		<p class="inactive">{$txt->entities($currentDoc['ad2ln1'])}</p>
                                    </div>
                                    <div class="ds_address_box inactive" id="ds_adress_{$num}c office_num_{$doc3['office']}">
                                    		<p class="inactive">{$txt->entities($currentDoc['ad3ln1'])}</p>
                                    </div>
                                </div>
EOS;
												echo '
                                <div class="ds_availability" id="ds_availability_' . $num . '">
                                        <div class="ds_ava_banner">
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 0, $days_processed )) ? $days_processed [0] : 'NA') . '
											</div>
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 1, $days_processed )) ? $days_processed [1] : 'NA') . '
											</div>
											<div class="ds_ava_banner_element">
												' . ((array_key_exists ( 2, $days_processed )) ? $days_processed [2] : 'NA') . '
											</div>
									   </div>
                                        <div class="ds_ava_content">
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 0, $days_content_arr )) ? $days_content_arr [0] : '') . '
											</div>
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 1, $days_content_arr )) ? $days_content_arr [1] : '') . '
											</div>
											<div class="ds_ava_element style-2">
												' . ((array_key_exists ( 2, $days_content_arr )) ? $days_content_arr [2] : '') . '
											</div>
									   </div>
                                </div>
                            </div>';
											}
										}
									}
									$printed[$doc['username']] = TRUE;
									}
								
								$_SESSION['markers'] = $markerInfo;
							}
					echo '</div>';
    				}
			?>
	<?php
		if(isset($_SESSION['searchparams'])){
	?>
    	<div class="ds_right_column">
    		<div class="map" id="map">
    			<script
    				src="https://maps.googleapis.com/maps/api/js?key=&callback=myMap"
    				async defer>
        			</script>
    		</div>
    	</div>
    	<?php
    		}
    	?>
    </div>
</div>

<!-- <div class='pay_button' id='pay_service' data-toggle='modal' data-target='#confirm_appo_sele'>Pay</div> -->

<?php
if (isset ( $_SESSION ['confirm_email_alert'] )) {
	$email_not_set = 1;
	?>
<div class="modal fade" id="confirm_email_alert" tabindex="-1"
	role="dialog" aria-labelledby="postModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">

			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<?php 
					switch ($lang){
							 		
						case("en"):
				?>
							<h4 class="modal-title" id="myModalLabel">Confirm Email</h4>
				<?php 
					        break;
						
						case("es"):
				?>
							<h4 class="modal-title" id="myModalLabel">Confirmar Correo</h4>
				<?php 
							break;
					}
				?>
				
			</div>
			<div class="modal-body">
			<?php 
				switch ($lang){
						 		
					case("en"):
			?>
						<p>
							You have successfully registered! <br> You can now schedule an
							appointment, but <b>you MUST confirm your email</b> by clicking on
							the link sent to the email address providided <b>within 2 hours</b>
							of scheduling the appointment, or <b>your appointment will be
								automatically CANCELED!</b>
						</p>
			<?php 
				        break;
					
					case("es"):
			?>
						<p>
							¡Te has registrado correctamente! <br> Ahora puedes agendar una
							cita, pero <b>DEBES confirmar tu correo</b> haciendo click en
							el link que enviamos a tu correo <b>dentro de 2 horas</b>
							de agendada la cita, de lo contrario <b>tu cita será
								automáticamente CANCELADA!</b>
						</p>
			<?php 
						break;
				}
			?>

			</div>

			<div class="modal-footer">
				<?php 
					switch ($lang){
							 		
						case("en"):
				?>
							<button type="button" class="btn btn-primary" data-dismiss="modal">Accept</button>
				<?php 
					        break;
						
						case("es"):
				?>
							<button type="button" class="btn btn-primary" data-dismiss="modal">Aceptar</button>
				<?php 
							break;
					}
				?>
			</div>

		</div>
	</div>
</div>



<?php
} else {

	$email_not_set = 0;
}
?>


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
<script>

$(document).ready(function(){
	$('.nav-tabs > li'). css('width', '50%');
});

function selectTime4BookingSearchScreen(booking_array){
	$('#modal_doctor_name').html(booking_array.doc_name);
	$('#modal_display_time').html(booking_array.display_time);
	$('#modal_appo_date').html(booking_array.week_day+", "+booking_array.day+" / "+booking_array.month+" / "+booking_array.year);
	$('#modal_addln1').html(booking_array.adln1);
	$('#modal_addln3').html(booking_array.adln2);
	$('#modal_insurance').html(booking_array.insurance_name);

	$( "input[name='year']" ).val(booking_array.year);
	$( "input[name='month']" ).val(booking_array.month);
	$( "input[name='day']" ).val(booking_array.day);
	$( "input[name='profile_owner']" ).val(booking_array.doctor_username);
	$( "input[name='payment_method']" ).val(booking_array.payment_method);
	$( "input[name='ap_id']" ).val(booking_array.appo_type_id);
	$( "input[name='ap_st']" ).val(booking_array.time_st);
	$( "input[name='ap_end']" ).val(booking_array.time_end);
}
</script>

		<?php		
			if(isset($_SESSION ['searchparams'])){
		?>
<script>
	$(document).ready(function(){
		$('.nav-tabs > li'). css('width', '50%');


		
		if(<?php echo $email_not_set;?> == 1){
			$("#confirm_email_alert").modal("show");
		}

		$("[id^=ds_adress_]").click(function(){
			var id = $(this).attr('id').split(" ");
			
			if(id.length > 1){
				
				var elem_num = id[0].substring(id[0].length-2, id[0].length-1);
				$('#ds_availability_' + elem_num).html('<center><img id="loading" src="assets/images/icons/logowhite.gif"></center>');
				var _index = id[0].substring(0, id[0].length-1);
				var username_arr = <?php echo json_encode($_aux_array);?>;
	
				switch(id[1].substring(id[1].length-1, id[1].length)){
					case '1':
						var office = 1;
						break;
					case '2':
						var office = 2;
						break;
					case '3':
						var office = 3;
						break;
				}
				
				var username = username_arr[_index];
				var payment_method = '<?php echo $payment;?>';
				var appo_type_id = '<?php echo $appo_type_id;?>';
				var insurance_name = '<?php echo ucwords($_SESSION['i_name']);?>';
				var date = '<?php echo $date; ?>';
				
				$.ajax({
					type: "POST",
					url: "includes/handlers/ajax_change_office_calendar.php",
					data: "user=" + username + "&date=" + date + "&office=" + office + "&payment=" + payment_method + "&insurance=" + insurance_name,
					success: function(response){
						$('#ds_availability_' + elem_num).html(response); 
					    //alert(response);
					}
				});
			}
		});

		var wasInactive;
		$(".ds_address_box").click(function(){
			wasInactive = $(this).attr('name');
			if(wasInactive != "active"){
				$(this).siblings("[name='active']").addClass("inactive");
//				$(this).siblings("[name='active']").attr("name","");
 				$(this).siblings("[name='active']").removeAttr("name");
//				$(this).removeClass("inactive");
				$(this).attr("name","active");
			}
			else {
				//doNothing;
			}
		});	

		//Button for scheduling appointments
		$('#accept_appointment_booking').click(function(){
			$("#schedule_message").html('<center><img id="loading" src="assets/images/icons/logowhite.gif"></center>');
			$.ajax({
				type: "POST",
				url: "includes/handlers/ajax_confirm_time_selection.php",
				data: $('form.book_appointment_form').serialize(), //What we send!
				cache: false,
				success: function(response){
					if (response.indexOf('REMOVE:') >= 0) {
					    //alert(response);
					    location.reload();
					}
					else{
						top.window.location.href = 'patient_appointment_viewer.php?cid=' + response;
					}
				},
				error: function(jqXHR, exception) {
					if (jqXHR.status === 409) {
						$("#schedule_message").html('<?php echo "Failed to add appointment, this slot was already taken by someone else. Try refreshing the page and try again." ?>');
					}
					else if (jqXHR.status === 412) {
						$("#schedule_message").html('<?php echo "Failed to add appointment, this slot is already filled in your calendar, select a different time and try again." ?>');
					}
					else if (jqXHR.status === 400) {
						$("#schedule_message").html('<?php echo "Failed to add appointment; bad request, refresh the page and try again." ?>');
					}
				}
			});
		});
		
	});

</script>

<?php
			}
?>

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

