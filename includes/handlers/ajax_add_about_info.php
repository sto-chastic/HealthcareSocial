<?php
include("../../config/config.php");
include("../classes/User.php");
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
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
	
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	$txtrep = new TxtReplace();
	$lang = $_SESSION['lang'];
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

$about_element = $_REQUEST['about_element'];

$error_array = [];

switch ($about_element) {
	case 'education':
		$edu_title = ucwords($_REQUEST['edu_title']);
		$edu_institution = ucwords($_REQUEST['edu_institution']);
		$edu_start_date = $_REQUEST['edu_start_date'];
		$edu_end_date = $_REQUEST['edu_end_date'];
		
		if($edu_title != '' && $edu_institution!= '' && $edu_start_date!= '' && $edu_end_date!= '' ){
		
			$edu_start_date_obj = new DateTime($edu_start_date);
			$edu_start_date_formated = $edu_start_date_obj->format('Y-m-d');
			
			$edu_end_date_obj = new DateTime($edu_end_date);
			$edu_end_date_formated = $edu_end_date_obj->format('Y-m-d');
			
			if($edu_start_date_obj < $edu_end_date_obj){
				$edu_tab = $user_obj->getEducation_tab();
				//echo $edu_start_date ."," . $edu_end_date;
				$stmt = $con->prepare("INSERT INTO $edu_tab (`id`, `title_obtained`, `institution`, `start_date`, `end_date`) VALUES ('',?,?,?,?)");
				$stmt->bind_param("ssss",$edu_title,$edu_institution,$edu_start_date_formated,$edu_end_date_formated);
			}
			else{
				switch ($lang){
					
					case("en"):
						echo $error_array['$about_element'] = "The \'Start Date\' needs to be earlier than the \'Graduation Date\'.";
						break;
						
					case("es"):
						echo $error_array['$about_element'] = "La \'Fecha Inicial\' necesita ser antes que la \'Fecha de Graduación\'.";
						break;
				}
			}
		}
		break;
		
	case 'job':
		$job_title= ucwords($_REQUEST['job_title']);
		$job_institution= ucwords($_REQUEST['job_institution']);
		$job_start_date= $_REQUEST['job_start_date'];
		$job_end_date= $_REQUEST['job_end_date'];
		$job_checkbox= $_REQUEST['job_checkbox'];
		
		//echo $job_checkbox;
		if($job_title!= '' && $job_institution!= '' && $job_start_date!= ''){
			$job_start_date_obj = new DateTime($job_start_date);
			$job_start_date_formated = $job_start_date_obj->format('Y-m-d');
			
			if($job_checkbox == 'false'){
				$job_end_date_obj = new DateTime($job_end_date);
				$job_end_date_formated = $job_end_date_obj->format('Y-m-d');
			}
			else{
				$job_end_date_obj = new DateTime('0000-00-00');
				$job_end_date_formated = $job_end_date_obj->format('Y-m-d');
			}
			
			if($job_start_date_obj< $job_end_date_obj || ($job_checkbox == 'true')){
				$_tab = $user_obj->getJobs_tab();
				//echo $edu_start_date ."," . $edu_end_date;
				$stmt = $con->prepare("INSERT INTO $_tab (`id`, `title`, `institution`, `start_date`, `end_date`) VALUES ('',?,?,?,?)");
				$stmt->bind_param("ssss",$job_title,$job_institution,$job_start_date_formated,$job_end_date_formated);
			}
			else{
				switch ($lang){
					
					case("en"):
						echo $error_array['$about_element'] = "The \'Start Date\' needs to be earlier than the \'End Date\'.";
						break;
						
					case("es"):
						echo $error_array['$about_element'] = "La \'Fecha Inicial\' necesita ser anterior a la \'Fecha de Terminación\'.";
						break;
				}
				
			}
		}
		break;
		
	case 'conference':
		$conf_title= ucwords($_REQUEST['conf_title']);
		$conf_role= ucwords($_REQUEST['conf_role']);
		$conf_date= $_REQUEST['conf_date'];
		
		if($conf_title!= '' && $conf_role!= '' && $conf_date!= ''){
			$conf_date_obj = new DateTime($conf_date);
			$conf_date_formated = $conf_date_obj->format('Y-m-d');
	
			$_tab = $user_obj->getConferences_tab();
			//echo $edu_start_date ."," . $edu_end_date;
			$stmt = $con->prepare("INSERT INTO $_tab (`id`, `title`, `role`, `date`) VALUES ('',?,?,?)");
			$stmt->bind_param("sss",$conf_title,$conf_role,$conf_date_formated);
		}
		break;
	case 'description':
		$description = ucfirst($_REQUEST['description']);
		if($description!= ''){
			$user_obj->addDescription($description);
		}
		break;
		
	case 'webpage':
		$webpage_code = $_REQUEST['webpage_code'];
		$webpage_url = $txtrep->entities($_REQUEST['webpage_url']);
		
		if($webpage_code != '' && $webpage_url!= ''){
			
			$_tab = $user_obj->getWebpages_tab();
			//echo $edu_start_date ."," . $edu_end_date;
			$stmt = $con->prepare("INSERT INTO $_tab (`web_page_code`, `url`) VALUES (?,?) ON DUPLICATE KEY UPDATE `web_page_code`=?, `url`=?");
			$stmt->bind_param("ssss",$webpage_code,$webpage_url,$webpage_code,$webpage_url);
		}
		break;
	case 'publication':
		$publi_title= ucwords($_REQUEST['publi_title']);
		$publi_authors= ucwords($_REQUEST['publi_authors']);
		$publi_journal= ucwords($_REQUEST['publi_journal']);
		$publi_volume= ucwords($_REQUEST['publi_volume']);
		$publi_date= $_REQUEST['publi_date'];
		
		if($publi_title!= '' && $publi_authors!= '' && $publi_journal!= ''){
			$publi_date_obj = new DateTime($publi_date);
			$publi_date_formated = $publi_date_obj->format('Y-m-d');
			
			$_tab = $user_obj->getPublications_tab();
			//echo $edu_start_date ."," . $edu_end_date;
			$stmt = $con->prepare("INSERT INTO $_tab (`id`, `title`, `main_authors`, `journal`, `page_vol`, `year`) VALUES ('',?,?,?,?,?)");
			$stmt->bind_param("sssss",$publi_title,$publi_authors,$publi_journal,$publi_volume,$publi_date_formated);
		}
		break;
}


$stmt->execute();

?>