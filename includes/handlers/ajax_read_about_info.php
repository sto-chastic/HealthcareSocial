<?php
include("../../config/config.php");
include("../classes/User.php");
include("../classes/TxtReplace.php");
$crypt = new Crypt();
$txtrep = new TxtReplace();
if(isset($_SESSION['username']) && isset($_SESSION['messages_token'])){
    $lang = $_SESSION['lang'];
	$temp_user = $_SESSION['username'];
	$temp_user_e = $_SESSION['username_e'];
	$temp_messages_token= $_SESSION['messages_token'];
	$stmt = $con->prepare("SELECT email FROM users WHERE username=? AND messages_token =?");
	$stmt->bind_param("ss", $temp_user_e, $temp_messages_token);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	
	if(mysqli_num_rows($verification_query) == 1){
		$userLoggedIn = $temp_user;
		$userLoggedIn_e = $temp_user_e;
		$user = mysqli_fetch_array($verification_query);
		$stmt->close();
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
	}
	
}


if($_REQUEST['profile_username'] == '' && isset($userLoggedIn)){
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	$editable = TRUE;
}
elseif($_REQUEST['profile_username'] != ''){
    $profile_username_e = pack("H*", $txtrep->entities($_REQUEST['profile_username']));
    $profile_username = $crypt->Decrypt($profile_username_e);
    
    $user_obj = new User($con, $profile_username, $profile_username_e);
    $editable = FALSE;
}
else{
	die;
}

$lang = $_SESSION['lang'];
$about_element = $_REQUEST['about_element'];
$txtrep = new TxtReplace();

switch ($about_element) {
	case 'educación':
	case 'education':
		switch ($lang){
			
			case("en"):
				$str = "";
				$edu_tab = $user_obj->getEducation_tab();
				$stmt = $con->prepare("SELECT * FROM $edu_tab ORDER BY end_date DESC LIMIT 6");
				$stmt->execute();
				$q = $stmt->get_result();
				
				if(mysqli_num_rows($q) > 0){ 
				    ?>
                    <script>
                    	$('#education_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php  
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'><b>From-To</b></div>
						<div class='about_obt_title'><b>Obtained Title</b></div>
						<div class='about_institution'><b>Institution</b></div>
					</div>"	;
				}else{
				    ?>
                    <script>
                    	$('#education_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php  
				}
				
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title_obtained'];
					$institution = $arr['institution'];
					$start_date = $arr['start_date'];
					$end_date = $arr['end_date'];
					
					$edu_start_date_obj = new DateTime($start_date);
					$edu_start_date_formated = $edu_start_date_obj->format('Y');
					
					$edu_end_date_obj = new DateTime($end_date);
					$edu_end_date_formated = $edu_end_date_obj->format('Y');
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($edu_start_date_formated . "-" . $edu_end_date_formated). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($title) . "</div>
						<div class='about_institution'>" . $txtrep->entities($institution). "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('education', 'id', '{$txtrep->entities($id)}')">
						<div class='delete_button btn-danger' id='delete_data'>x</div>
				</a>
EOS;
					}
					$str .="</div>";
					
					
				}
				break;
				
			case("es"):
				$str = "";
				$edu_tab = $user_obj->getEducation_tab();
				$stmt = $con->prepare("SELECT * FROM $edu_tab ORDER BY end_date DESC LIMIT 6");
				$stmt->execute();
				$q = $stmt->get_result();
				
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#education_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php  
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'><b>De-A</b></div>
						<div class='about_obt_title'><b>Título Obtenido</b></div>
						<div class='about_institution'><b>Institución</b></div>
					</div>"	;
				}else{
				    ?>
                    <script>
                    	$('#education_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php  
				}
				
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title_obtained'];
					$institution = $arr['institution'];
					$start_date = $arr['start_date'];
					$end_date = $arr['end_date'];
					
					$edu_start_date_obj = new DateTime($start_date);
					$edu_start_date_formated = $edu_start_date_obj->format('Y');
					
					$edu_end_date_obj = new DateTime($end_date);
					$edu_end_date_formated = $edu_end_date_obj->format('Y');
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($edu_start_date_formated . "-" . $edu_end_date_formated). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($title) . "</div>
						<div class='about_institution'>" . $txtrep->entities($institution). "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('education', 'id', '{$txtrep->entities($id)}')">
						<div class='delete_button btn-danger' id='delete_data'>x</div>
				</a>
EOS;
					}
					$str .="</div>";
					
					
				}
				break;
		}

		break;
	
	case 'trabajo':
	case 'job':
		switch ($lang){
			
			case("en"):
				$str = "";
				$edu_tab = $user_obj->getJobs_tab();
				$stmt = $con->prepare("SELECT * FROM $edu_tab WHERE end_date = '0000-00-00' ORDER BY start_date DESC LIMIT 6");
				$stmt->execute();
				$q = $stmt->get_result();
				
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#jobs_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php  
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'><b>From-To</b></div>
						<div class='about_obt_title'><b>Job Title</b></div>
						<div class='about_institution'><b>Institution</b></div>
					</div>"	;
				}else{
				    ?>
                    <script>
                    	$('#jobs_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php 
				}
				
				
				$first_results_num = mysqli_num_rows($q);
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title'];
					$institution= $arr['institution'];
					$start_date = $arr['start_date'];
					$end_date = $arr['end_date'];
					
					$edu_start_date_obj = new DateTime($start_date);
					$edu_start_date_formated = $edu_start_date_obj->format('Y');
					
					if($end_date != '0000-00-00'){
						$edu_end_date_obj = new DateTime($end_date);
						$edu_end_date_formated = $edu_end_date_obj->format('Y');
					}else{
					    switch($lang){
					        case("en"): $edu_end_date_formated = 'Present';
					           break;
					        case("es"): $edu_end_date_formated = 'Presente';
					           break;
					    }
					}
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($edu_start_date_formated . "-" . $edu_end_date_formated). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($title) . "</div>
						<div class='about_institution'>" . $txtrep->entities($institution). "</div>";
					if($editable){
						$str .= <<<EOS
							<a href="javascript:void(0);" onclick="delete_table_element('job', 'id', '{$txtrep->entities($id)}')">
									<div class='delete_button btn-danger' id='delete_data'>x</div>
							</a>
EOS;
					}
					$str .="</div>";
				}
				$second_results_num= 6 - $first_results_num;
				$stmt = $con->prepare("SELECT * FROM $edu_tab WHERE end_date != '0000-00-00' ORDER BY end_date DESC LIMIT $second_results_num");
				$stmt->execute();
				$q = $stmt->get_result();
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title'];
					$institution= $arr['institution'];
					$start_date = $arr['start_date'];
					$end_date = $arr['end_date'];
					
					$edu_start_date_obj = new DateTime($start_date);
					$edu_start_date_formated = $edu_start_date_obj->format('Y');
					
					if($end_date != '0000-00-00'){
						$edu_end_date_obj = new DateTime($end_date);
						$edu_end_date_formated = $edu_end_date_obj->format('Y');
					}else{
					    switch($lang){
					        case("en"): $edu_end_date_formated = 'Present';
					        break;
					        case("es"): $edu_end_date_formated = 'Presente';
					        break;
					    }
					}
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($edu_start_date_formated . "-" . $edu_end_date_formated). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($title) . "</div>
						<div class='about_institution'>" . $txtrep->entities($institution). "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('job', 'id', '{$txtrep->entities($id)}')">
						<div class='delete_button btn-danger' id='delete_data'>x</div>
				</a>
EOS;
					}
					
					$str .="</div>";
					
				}
				break;
				
			case("es"):
				$str = "";
				$edu_tab = $user_obj->getJobs_tab();
				$stmt = $con->prepare("SELECT * FROM $edu_tab WHERE end_date = '0000-00-00' ORDER BY start_date DESC LIMIT 6");
				$stmt->execute();
				$q = $stmt->get_result();
				
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#jobs_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php 
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'><b>De-A</b></div>
						<div class='about_obt_title'><b>Cargo</b></div>
						<div class='about_institution'><b>Institución</b></div>
					</div>"	;
				}else{
				    ?>
                    <script>
                    	$('#jobs_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php 
				}
				
				$first_results_num = mysqli_num_rows($q);
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title'];
					$institution= $arr['institution'];
					$start_date = $arr['start_date'];
					$end_date = $arr['end_date'];
					
					$edu_start_date_obj = new DateTime($start_date);
					$edu_start_date_formated = $edu_start_date_obj->format('Y');
					
					if($end_date != '0000-00-00'){
						$edu_end_date_obj = new DateTime($end_date);
						$edu_end_date_formated = $edu_end_date_obj->format('Y');
					}else{
					    switch($lang){
					        case("en"): $edu_end_date_formated = 'Present';
					        break;
					        case("es"): $edu_end_date_formated = 'Presente';
					        break;
					    }
					}
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($edu_start_date_formated . "-" . $edu_end_date_formated). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($title) . "</div>
						<div class='about_institution'>" . $txtrep->entities($institution). "</div>";
					if($editable){
						$str .= <<<EOS
							<a href="javascript:void(0);" onclick="delete_table_element('job', 'id', '{$txtrep->entities($id)}')">
									<div class='delete_button btn-danger' id='delete_data'>x</div>
							</a>
EOS;
					}
					$str .="</div>";
				}
				$second_results_num= 6 - $first_results_num;
				$stmt = $con->prepare("SELECT * FROM $edu_tab WHERE end_date != '0000-00-00' ORDER BY end_date DESC LIMIT $second_results_num");
				$stmt->execute();
				$q = $stmt->get_result();
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title'];
					$institution= $arr['institution'];
					$start_date = $arr['start_date'];
					$end_date = $arr['end_date'];
					
					$edu_start_date_obj = new DateTime($start_date);
					$edu_start_date_formated = $edu_start_date_obj->format('Y');
					
					if($end_date != '0000-00-00'){
						$edu_end_date_obj = new DateTime($end_date);
						$edu_end_date_formated = $edu_end_date_obj->format('Y');
					}else{
					    switch($lang){
					        case("en"): $edu_end_date_formated = 'Present';
					        break;
					        case("es"): $edu_end_date_formated = 'Presente';
					        break;
					    }
					}
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($edu_start_date_formated . "-" . $edu_end_date_formated). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($title) . "</div>
						<div class='about_institution'>" . $txtrep->entities($institution). "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('job', 'id', '{$txtrep->entities($id)}')">
						<div class='delete_button btn-danger' id='delete_data'>x</div>
				</a>
EOS;
					}
					
					$str .="</div>";
					
				}
				break;
		}

		break;
	
	case 'conferencia':
	case 'conference':
		switch ($lang){
			
			case("en"):
				$str = "";
				$_tab = $user_obj->getConferences_tab();
				$stmt = $con->prepare("SELECT * FROM $_tab ORDER BY `date` DESC LIMIT 6");
				$stmt->execute();
				$q = $stmt->get_result();
				
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#conferences_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php 
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'><b>Date</b></div>
						<div class='about_obt_title'><b>Conference</b></div>
						<div class='about_institution'><b>Role</b></div>
					</div>"	;
				}else{
				    ?>
                    <script>
                    	$('#conferences_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php 
				}
				
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title'];
					$role = $arr['role'];
					$date = $arr['date'];
					
					$date_obj = new DateTime($date);
					$date_formated = $date_obj->format('Y');
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($date_formated). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($title) . "</div>
						<div class='about_institution'>" . $txtrep->entities($role). "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('conference', 'id', '{$txtrep->entities($id)}')">
						<div class='delete_button btn-danger ' id='delete_data'>x</div>
				</a>
EOS;
					}
					$str .="</div>";
				}
				break;
				
			case("es"):
				$str = "";
				$_tab = $user_obj->getConferences_tab();
				$stmt = $con->prepare("SELECT * FROM $_tab ORDER BY `date` DESC LIMIT 6");
				$stmt->execute();
				$q = $stmt->get_result();
				
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#conferences_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php 
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'><b>Fecha</b></div>
						<div class='about_obt_title'><b>Conferencia</b></div>
						<div class='about_institution'><b>Rol</b></div>
					</div>"	;
				}else{
				    ?>
                    <script>
                    	$('#conferences_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php 
				}
				
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title'];
					$role = $arr['role'];
					$date = $arr['date'];
					
					$date_obj = new DateTime($date);
					$date_formated = $date_obj->format('Y');
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($date_formated). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($title) . "</div>
						<div class='about_institution'>" . $txtrep->entities($role). "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('conference', 'id', '{$txtrep->entities($id)}')">
						<div class='delete_button btn-danger'id='delete_data'>x</div>
				</a>
EOS;
					}
					$str .="</div>";
				}
				break;
		}

		break;
	case 'descripción':
	case 'description':
		$str = $user_obj->getDescription();
		break;
	case 'webpage':
		switch ($lang){
			
			case("en"):
				$str = "";
				$_tab = $user_obj->getWebpages_tab();
				$stmt = $con->prepare("SELECT * FROM $_tab ORDER BY `web_page_code`");
				$stmt->execute();
				$q = $stmt->get_result();
				
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#websites_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php  
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'><b>Website</b></div>
						<div class='about_obt_title'><b>URL / Profile</b></div>
					</div>"	;
				}else {
				    ?>
                    <script>
                    	$('#websites_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php  
				    
				}
				
				while($arr = mysqli_fetch_assoc($q)){
					$web_page_code= $arr['web_page_code'];
					$url= $arr['url'];
					
					$stmt = $con->prepare("SELECT `name` FROM `webpages` WHERE `web_page_code`=?");
					$stmt->bind_param("i",$web_page_code);
					$stmt->execute();
					$query = $stmt->get_result();
					$arr2 = mysqli_fetch_assoc($query);
					
					$name=$arr2['name'];
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($name). "</div>
						<div class='about_obt_title' >" . $txtrep->entities($url) . "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('webpage', 'web_page_code', '{$txtrep->entities($web_page_code)}')">
						<div class='delete_button btn-danger' id='delete_data' >x</div>
				</a>
EOS;
					}
					$str .="</div>";
				}
				break;
				
			case("es"):
				$str = "";
				$_tab = $user_obj->getWebpages_tab();
				$stmt = $con->prepare("SELECT * FROM $_tab ORDER BY `web_page_code`");
				$stmt->execute();
				$q = $stmt->get_result();
				
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#websites_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php 
					$str .= "<div class='about_line_holderbox'>

						<div class='about_st_year'><b>Portal</b></div>
						<div class='about_obt_title'><b>URL / Perfil</b></div>
					</div>"	;
				}else {
				    ?>
                    <script>
                    	$('#websites_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php  
				    
				}
				
				while($arr = mysqli_fetch_assoc($q)){
					$web_page_code= $arr['web_page_code'];
					$url= $arr['url'];
					
					$stmt = $con->prepare("SELECT `name` FROM `webpages` WHERE `web_page_code`=?");
					$stmt->bind_param("i",$web_page_code);
					$stmt->execute();
					$query = $stmt->get_result();
					$arr2 = mysqli_fetch_assoc($query);
					
					$name=$arr2['name'];
					
					$str .= "<div class='about_line_holderbox'>
						<div class='about_st_year'>" . $txtrep->entities($name). "</div>
						<div class='about_obt_title'>" . $txtrep->entities($url) . "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('webpage', 'web_page_code', '{$txtrep->entities($web_page_code)}')">
						<div class='delete_button btn-danger' id='delete_data'>x</div>
				</a>
EOS;
					}
					$str .="</div>";
				}
				break;
		}

		break;
	case 'publicación':
	case 'publication':
		switch ($lang){
			
			case("en"):
				$str = "";
				$_tab = $user_obj->getPublications_tab();
				$stmt = $con->prepare("SELECT * FROM $_tab ORDER BY `year` DESC LIMIT 20");
				$stmt->execute();
				$q = $stmt->get_result();
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#publications_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php 
					
				}else {
				    ?>
                    <script>
                    	$('#publications_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php  
				    
				}
				
				// 		if(mysqli_num_rows($q) > 0){
				// 			$str .= "<div class='about_line_holderbox'>
				// 						<div class='about_st_year'><b>Date</b></div>
				// 						<div class='about_obt_title'><b>Conference</b></div>
				// 						<div class='about_institution'><b>Role</b></div>
				// 					</div>"	;
				// 		}
				
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title'];
					$main_authors= $arr['main_authors'];
					$journal= $arr['journal'];
					$page_vol= $arr['page_vol'];
					$year= $arr['year'];
					
					$date_obj = new DateTime($year);
					$date_formated = $date_obj->format('Y');
					
					$str .= "<div class='about_line_holderbox about_line_holderbox_publications'>
						<div class='about_full'>" . $txtrep->entities($main_authors) . " (" . $txtrep->entities($year) . "). " . $txtrep->entities($title) .". <i>" . $txtrep->entities($journal). ",</i> " . $txtrep->entities($page_vol). "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('publication', 'id', '{$txtrep->entities($id)}')">
						<div class='delete_button btn-danger' id='delete_data'>x</div>
				</a>
EOS;
					}
					$str .="</div>";
				}
				break;
				
			case("es"):
				$str = "";
				$_tab = $user_obj->getPublications_tab();
				$stmt = $con->prepare("SELECT * FROM $_tab ORDER BY `year` DESC LIMIT 20");
				$stmt->execute();
				$q = $stmt->get_result();
				if(mysqli_num_rows($q) > 0){
				    ?>
                    <script>
                    	$('#publications_editable_list').css({"border":"1px solid #ddd"});
                    </script>
                    
                    <?php 
					
				}else {
				    ?>
                    <script>
                    	$('#publications_editable_list').css({"border":"none"});
                    </script>
                    
                    <?php  
				    
				}
				
				// 		if(mysqli_num_rows($q) > 0){
				// 			$str .= "<div class='about_line_holderbox'>
				// 						<div class='about_st_year'><b>Date</b></div>
				// 						<div class='about_obt_title'><b>Conference</b></div>
				// 						<div class='about_institution'><b>Role</b></div>
				// 					</div>"	;
				// 		}
				
				while($arr = mysqli_fetch_assoc($q)){
					$id = $arr['id'];
					$title = $arr['title'];
					$main_authors= $arr['main_authors'];
					$journal= $arr['journal'];
					$page_vol= $arr['page_vol'];
					$year= $arr['year'];
					
					$date_obj = new DateTime($year);
					$date_formated = $date_obj->format('Y');
					
					$str .= "<div class='about_line_holderbox about_line_holderbox_publications'>
						<div class='about_full'>" . $txtrep->entities($main_authors) . " (" . $txtrep->entities($year) . "). " . $txtrep->entities($title) .". <i>" . $txtrep->entities($journal). ",</i> " . $txtrep->entities($page_vol). "</div>";
					if($editable){
						$str .= <<<EOS
				<a href="javascript:void(0);" onclick="delete_table_element('publication', 'id', '{$txtrep->entities($id)}')">
						<div class='delete_button btn-danger' id='delete_data'>x</div>
				</a>
EOS;
					}
					$str .="</div>";
				}
				break;
		}

		break;
}

echo $str;


?>