<?php
require 'config/config.php';
include('includes/classes/User.php');
include('includes/classes/Post.php');
include("includes/classes/Message.php");
include("includes/classes/Settings.php");
include("includes/classes/Notification.php");
include("includes/classes/TimeStamp.php");
include("includes/classes/TxtReplace.php");
include("includes/classes/Calendar.php");
include("includes/classes/Appointments_Calendar.php");
include("includes/classes/Email_Creator.php");
require 'includes/form_handlers/login_handler.php';
require 'includes/form_handlers/register_handler.php';
$crypt = new Crypt();

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
		$userLoggedIn = $temp_user; //Retrieves username
		$userLoggedIn_e = $temp_user_e;
		$user = mysqli_fetch_array($verification_query);
		//$messages_token = $temp_messages_token;
		$stmt->close();
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: register.php");
		$stmt->close();
	}
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	$txt_rep = new TxtReplace();
	$settings = new Settings($con, $userLoggedIn, $userLoggedIn_e);
	
	//LANGUAGE RETRIEVAL
	
	$lang = $settings->getLang();
	$_SESSION["lang"] = $lang;
	$not_logged = FALSE;
}
else{
	$not_logged = TRUE;
	$txt_rep = new TxtReplace();
	if(isset($_GET['lang'])){
		switch($_GET['lang']){
			case("en"):
				$lang = 'en';
				break;
			case ("es"):
				$lang = 'es';
				break;
			default:
				$lang = 'es';
		}
	}
	else{
		$lang = 'es';
	}
	$_SESSION["lang"] = $lang;
}


//Check if it is a legal user

if(isset($_GET['profile_username'])){
	
	if( !preg_match("/^[a-zA-Z0-9\-]+$/", $_GET['profile_username']) )
	{
		header("Location: user_closed.php");
		die;
	}
	else{
		$profile_given_id = $txt_rep->entities($_GET['profile_username']);
	}
	
	$stmt = $con->prepare("SELECT username FROM profile_register WHERE verbal_rich_link=? OR custom_link=?");
	
	$stmt->bind_param("ss", $profile_given_id,$profile_given_id);
	$stmt->execute();
	$non_username_query = $stmt->get_result();
	
	if(mysqli_num_rows($non_username_query) == 1){
		$temp_usrnm_arr = mysqli_fetch_assoc($non_username_query);
		
		$temp_user_e = $temp_usrnm_arr['username'];
	}
	else{
		$temp_user_e = @pack("H*",$profile_given_id);
	}
	
	$stmt = $con->prepare("SELECT email FROM users WHERE username=?");
	
	$stmt->bind_param("s", $temp_user_e);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	
	if(mysqli_num_rows($verification_query) === 1){
		$temp_user = $crypt->Decrypt($temp_user_e);
		//username corrsponds to the username of the profile being visited
		
		if(!$not_logged){
			$message_obj = new Message($con, $userLoggedIn, $userLoggedIn_e);
		}
		$username = $temp_user;
		$username_e = $temp_user_e;
		$profile_user_obj = new User($con,$username, $username_e);
		
		if(!$not_logged){
			if($username == $userLoggedIn){
				$my_profile = TRUE;
			}
			else{
				$my_profile = FALSE;
			}
		}
		else{
			$my_profile = FALSE;
		}
		
		
		$isDoctor = $profile_user_obj->isDoctor();
		
		if(!$isDoctor && $my_profile){
			header("Location: health_info_input.php");
			die;
		}
		elseif(!$isDoctor && !$my_profile){
			header("Location: user_closed.php");
			die;
		}
		
		
		$isUpToDate = $profile_user_obj->isUpToDate();
		
		$hideCalendar = FALSE;
		if($isDoctor && !$isUpToDate){
		    $hideCalendar = TRUE;
		}
		$num_friends = $profile_user_obj->getNumFriends();
		//$username = $profile_user_obj->getUsername();
		
		$current_day = date("d");
		$current_month = date("m"); //Gets current month
		$current_year = date("Y");
		
		if(!$hideCalendar){
		  $scheduling_calendar = new Appointments_Calendar($con, $username, $username_e, $current_year, $current_month);
		}
		//$payment_method = 'part';

		if(!$not_logged){
			$country = $user_obj->getCountry();
		}
		
		//Language Tables:
		switch($lang){
		    case("en"):{
		        $days_week_row_lang = 'days_short_eng';
		        $months_row_lang = 'months_eng';
		        break;
		        }
		    case ("es"):{
                $days_week_row_lang = 'days_short_es';
		        $months_row_lang = 'months_es';
		        break;
		        }
		}
		
		$stmt->close();
	}
	else{
		header("Location: user_closed.php");
		$stmt->close();
		die;
	}
}
else{
	header("Location: user_closed.php");
	$stmt->close();
	die;
}

?>

<!DOCTYPE html>
<html>
<head>
	<title><?php 
	echo $txt_rep->entities($profile_user_obj->getFirstAndLastName()) . " - " .
	   	$txt_rep->entities($profile_user_obj->getSpecializationsText($lang)) . " | ConfiDr."
	?></title>
	
	 <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
	 <meta name="description" content ="<?php 
        	 switch($lang){
        	     case("en"): {
        	         echo "Profile of " . $txt_rep->entities($profile_user_obj->getFirstAndLastName()) . ", " .
            	         $txt_rep->entities($profile_user_obj->getSpecializationsText($lang)) . ", on ConfiDr. ".
                        "Request an appointment, ask questions directly via messaging or find out more about this doctor.";
        	         break;
        	     } 
        	     case("es"):{
        	         echo "Perfil de " . $txt_rep->entities($profile_user_obj->getFirstAndLastName()) . ", " .
            	         $txt_rep->entities($profile_user_obj->getSpecializationsText($lang)) . ", en ConfiDr. " .
                       "Pide una cita, haz preguntas directamente via mensaje o descubre más acerca de este médico.";
        	         break;
        	     }
        	 }
	 ?>">


	 <meta http-equiv="content-type" content="text/html;charset=UTF-8" />


	<!-- Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="assets/js/bootstrap.js"></script>
	<script src="assets/js/bootbox.min.js"></script>
	<script src="assets/js/jquery.jcrop.js"></script>
	<script src="assets/js/jcrop_bits.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="assets/js/confidr.js"></script>
	<script src="assets/js/graphs.js"></script>

	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
	<link rel="stylesheet" href="assets/css/jquery.Jcrop.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<link rel="stylesheet" type="text/css" href="assets/css/profile.css">

	<!-- FavIcon -->
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
	
</head>
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-118239678-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'UA-118239678-1');
</script>
<body class="style-3">

	<div class="top_bar">
		<div class="logo" >
			<a href="index.php" title="ConfiDr.">

                <img src="assets/images/icons/logo.png" class="logo_position" title="ConfiDr.">

            </a> 

           <!--  <div class="separator">
			</div> -->

		</div>

		<div class="search">
			<!-- GET method is for passing parameters in the url -->
			
<?php if(!$not_logged){?>
			<form action="search.php" method="GET" name="search_form">

				<?php 
				 	switch ($lang){
	 		
				 		case("en"):
						 	?>
						 	<input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $lang;?>')" name="q" placeholder="Search ConfiDr." autocomplete="off" id="search_text_input">
							<?php 
							break;
						
						case("es"):
							?>
						 	<input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $lang;?>')" name="q" placeholder="Busca ConfiDr." autocomplete="off" id="search_text_input">
							<?php 
							break;
						
				 	}
				 ?>

				<div class="button_holder">
					<img src="assets/images/icons/search-icon.png">
				</div>

			</form>

			<div class="search_results style-2">
			</div>

			<div class="search_results_footer_empty">
			</div>
<?php }
else{
?>
				<?php 
				 	switch ($lang){
	 		
				 		case("en"):
						 	?>
						 	<a href="docsearch.php">
						 		<div>
						 			Find More Doctors
						 		</div>
 				 				<div class="button_holder">
									<img src="assets/images/icons/search-icon.png">
								</div>
						 	</a>
							<?php 
							break;
						
						case("es"):
							?>
						 	<a href="docsearch.php">
						 		<div>
						 			Encuentra Más Doctores
						 		</div>
 				 				<div class="button_holder">
									<img src="assets/images/icons/search-icon.png">
								</div>
						 	</a>
							<?php 
							break;
				 	}
				 ?>

<?php }?>
		</div>
		
<?php if(!$not_logged){?>
		<navc> 
			<?php
				switch ($lang){
						 		
					case("en"):
			?>
						<button onclick="myFunction()" class="menu_settings"> </button>
						  	<div id="menu_display" class="drop_settings"> 
						    <a href="settings.php"> 
				            		<img src="assets/images/icons/wrench.png" class="icon_position"                                 
				            	 	onmouseover="this.src='assets/images/icons/wrenchh.png'"
				                onmouseout="this.src='assets/images/icons/wrench.png'" title= "Ajustes"/>            
				            	 	Settings
				            	</a>
				            	
							<a href="payments.php"> 
				            		<img src="assets/images/icons/pay.png" class="icon_position"                                 
				            	 	onmouseover="this.src='assets/images/icons/payh.png'"
				                onmouseout="this.src='assets/images/icons/pay.png'" title= "Payments"/>            
				            	 	Payments
				            	</a>
				            	
				            	<?php if(!$user_obj->isDoctor()){?>
								<a href="health_info_input.php"> 
					            		<img src="assets/images/icons/medhist.png" class="icon_position"                                 
					            	 	onmouseover="this.src='assets/images/icons/medhitsh.png'"
					                onmouseout="this.src='assets/images/icons/medhist.png'" title= "Ajustes"/>            
					            	 	Medical Info.
					            	</a>
				            	<?php }?>
				            	
				            	<a href="includes/handlers/logout.php">                
				                <img src="assets/images/icons/key.png" class="icon_position"
				                onmouseover="this.src='assets/images/icons/keyh.png'"
				                onmouseout="this.src='assets/images/icons/key.png'" title= "Log"/>                
				            		Log Out  
				            	</a>
				    	  	</div>
			<?php 
				        break;
					
					case("es"):
			?>
						<button onclick="myFunction()" class="menu_settings"> </button>
						  	<div id="menu_display" class="drop_settings"> 
						    <a href="settings.php"> 
				            		<img src="assets/images/icons/wrench.png" class="icon_position"                                 
				            	 	onmouseover="this.src='assets/images/icons/wrenchh.png'"
				                onmouseout="this.src='assets/images/icons/wrench.png'" title= "Ajustes"/>            
				            	 	Configuración
				            	</a>
				            	
				            	<a href="payments.php"> 
				            		<img src="assets/images/icons/pay.png" class="icon_position"                                 
				            	 	onmouseover="this.src='assets/images/icons/payh.png'"
				                onmouseout="this.src='assets/images/icons/pay.png'" title= "Pagos"/>           
				            	 	Pagos
				            	</a>
				            	
				            	<?php if(!$user_obj->isDoctor()){?>
								<a href="health_info_input.php"> 
					            		<img src="assets/images/icons/medhist.png" class="icon_position"                                 
					            	 	onmouseover="this.src='assets/images/icons/medhitsh.png'"
					                onmouseout="this.src='assets/images/icons/medhist.png'" title= "Ajustes"/>            
					            	 	Info. Médica
					            	</a>
				            	<?php }?>
				            	
				            	<a href="includes/handlers/logout.php">                
				                <img src="assets/images/icons/key.png" class="icon_position"
				                onmouseover="this.src='assets/images/icons/keyh.png'"
				                onmouseout="this.src='assets/images/icons/key.png'" title= "Log"/>                
				            		Cerrar Sesión  
				            	</a>
				    	  	</div>
			<?php 
						break;
				}
			?> 
		</navc>
        
         
		<nav>

			<?php 
				//Unread Messages

				$messages = new Message($con, $userLoggedIn, $userLoggedIn_e);
				$num_messages = $messages->getUnreadNumber();


				//Unread Notifications

				$notifications = new Notification($con, $userLoggedIn, $userLoggedIn_e);
				$num_notifications = $notifications->getUnreadNumber();


				//Unread Notifications

				$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
				if($user_obj->isDoctor()){
					$num_friend_requests = $user_obj->getNumFriendRequests();
				}
			?>

			<!--cambios en el logo de home-->
			<a href="javascript:void(0);" onclick="getDropdownData('message')">
				<?php 
					if($num_messages > 0)
						echo '<span class="notification_badge" id="unread_messages">' . $txt_rep->entities($num_messages) . '</span>'
				?>

				<img src="assets/images/icons/quick.png" class="icon_position"
                onmouseover="this.src='assets/images/icons/quickh.png'"
                onmouseout="this.src='assets/images/icons/quick.png'" id="message_drop_down_button" title= "QuickChat"/>
			</a>
			<a href="javascript:void(0);" onclick="getDropdownData('notification')">
				<?php 
					if($num_notifications > 0)
						echo '<span class="notification_badge" id="unread_notifications">' . $txt_rep->entities($num_notifications) . '</span>'
				?>

				<img src="assets/images/icons/notifications.png" class="icon_position"
                  onmouseover="this.src='assets/images/icons/notificationsh.png'"
                  onmouseout="this.src='assets/images/icons/notifications.png'" id="notification_drop_down_button" title= "notificaciones" />
				
			</a>
			<a href="requests.php">
				<?php
				if($user_obj->isDoctor()){
					if($num_friend_requests > 0)
						echo '<span class="notification_badge" id="unread_fri_req">' . $txt_rep->entities($num_friend_requests) . '</span>';
				}
				?>

				<img src="assets/images/icons/network.png" class="icon_position"
                  onmouseover="this.src='assets/images/icons/networkh.png'"
                  onmouseout="this.src='assets/images/icons/network.png'" title= "conexiones"/>

			</a>
		</nav>
		
		
		<div class="separator">
				</div>
        <navb>
        	<a href="
				<?php 
					echo bin2hex($txt_rep->entities($userLoggedIn_e));
				 ?>">
				<?php 
					echo $txt_rep->entities($user['first_name']);
				 ?>   
			</a>

            <a href="
				<?php 
					echo bin2hex($txt_rep->entities($userLoggedIn_e));
				 ?>"> <img src="<?php echo $txt_rep->entities($user_obj->getProfilePicFast()); ?>"> </a>   
          
            
         </navb>  

		<div class="dropdown_data_window style-2" style="height:0px; border:none;"></div>
		<input type="hidden" id="dropdown_data_type" value="">
		<input type="hidden" id="dropdown_data_user" value="">
<?php }
else{
?>
		<div class="btn-group" style=" float: right;right: 25px;top: 5px;t: 25px;">
        		<?php $current_lang = $lang;?>
        		<a href="<?php echo 'profile.php?profile_username='.$profile_given_id.'&lang=en'?>">
	        		<div class="btn btn-success <?php if($current_lang=="en"){echo "active";}?>" name="lang_pref_en" id="lang_pref_en">
	            		English
	            </div>
            </a>
            <a href="<?php echo 'profile.php?profile_username='.$profile_given_id.'&lang=es'?>">
        			<div class="btn btn-success <?php if($current_lang=="es"){echo "active";}?>" name="lang_pref_es" id="lang_pref_es">
					Español
				</div>
			</a>
		</div>
	
<?php 	
}
?>
	</div>

<?php if(!$not_logged){?>
	<script>

		$(document).ready(function(){

			$('.dropdown_data_window').scroll(function(){
				var inner_height = $('.dropdown_data_window').innerHeight(); //Div containing posts, not used
				var scroll_top = $('.dropdown_data_window').scrollTop();
				var page = $('.dropdown_data_window').find('.nextPageDropdownData').val();//only finds the input .nextPage located in posts_area and gets its value.
				var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

				if((scroll_top + inner_height >= $('.dropdown_data_window')[0].scrollHeight) && noMoreData == 'false'){

					var pageName; //THIS holds page to send the ajax request
					var type = $('#dropdown_data_type').val();

					if(type == 'notification'){
						pageName = "ajax_load_notifications.php";
					}
					else if(type == 'message'){
						pageName = "ajax_load_messages.php";
					}

					
					//Ajax call again
					var ajaxReq = $.ajax({
						url: "includes/handlers/" + pageName,
						type: "POST",
						data: "page=" + page,
						cache:false,

						success: function(responseM){
							$('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextpage
							$('.dropdown_data_window').find('.noMoreDropdownData').remove(); //Removes current next page

							$('.dropdown_data_window').append(responseM);
						}
					});

				}
				/*else if(noMorePosts == 'true'){
					$('#loading').hide();
				}*/

				return false;
			}); 
		});


	</script>
<?php }?>
	<script>
	function myFunction() {
	    $("#menu_display").toggleClass("show");
	}
	// Close the dropdown if the user clicks outside of it
 	window.onclick = function(event) {
 		if (!event.target.matches('.menu_settings') && $('.drop_settings').hasClass("show")) {
			$('.drop_settings').toggleClass("show");
		}
	}


	</script>
	

	<div class= "top_banner">
	</div>
	<div class= "top_min_height">		
+	</div>
	<div class="user_info_div" itemscope itemtype="http://schema.org/Physician">
			<div class = "user_photo" itemprop="image">
				<img src=<?php echo $profile_user_obj->getProfilePicFast(); ?>>
			</div>
		
			<div class="name_title" itemprop="name">
				<h1>
					<?php 
						echo "Doctor ". $txt_rep->entities($profile_user_obj->getFirstAndLastName()) . "<br>";
				 	?>
			 	</h1>

				<h2 class="name_speciality" itemprop="medicalSpecialty">
					<?php 
						echo $txt_rep->entities($profile_user_obj->getSpecializationsText($lang));
					 ?>
            		</h2>
            		<h2 class="name_description" id="name_description">
            		 	<?php 
// 						echo $txt_rep->entities($profile_user_obj->getDescription());
 					?>
				</h2>
				<?php
					if($my_profile){
						
						switch ($lang){
							
							case("en"):
								echo <<<EOS
								<div class='about_line_holderbox' id='add_description_holder' style=" height: auto; background-color:none; display: none; border:none;">
									<input type="text" name="profile_description_inp" id="profile_description_inp" placeholder="Type your quick description (max: 120 chars)" maxlength="120" style=" width: 400px;" required>
									<a href="javascript:void(0);" onclick="add_about_info('description');hide_add_about_info('description');">
										<p id='add_description_link_close' >Done</p>
									</a>
								</div>
								<a href="javascript:void(0);" onclick="show_add_about_info('description');">
									<p id='add_description_link'>Edit Description</p>
								</a>
EOS;
								break;
								
							case("es"):
								echo <<<EOS
								<div class='about_line_holderbox' id='add_description_holder' style=" height: auto; background-color:none; display: none; border:none;">
									<input type="text" name="profile_description_inp" id="profile_description_inp" placeholder="Escribe una breve descripción (max: 120 chars)" maxlength="120" style=" width: 400px;" required>
									<a href="javascript:void(0);" onclick="add_about_info('description');hide_add_about_info('description');">
										<p id='add_description_link_close' >Aceptar</p>
									</a>
								</div>
								<a href="javascript:void(0);" onclick="show_add_about_info('description');">
									<p id='add_description_link'>Editar Descripción</p>
								</a>
EOS;
								break;
						}
						

					}
				?>
            		
            		<ul class="commsLike">
					<li>
						<ul class="view_like">
							<li><img src="assets/images/icons/docconnections.png"></li>
		                    	<li>
		                    		<div class='num_posts' href='requests.php'> <?php echo $txt_rep->entities($profile_user_obj->getDoctorConnections_num());?>
			                    		
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
				                    		<?php
				                    			$doctors_array = $profile_user_obj->getDoctorConnections_array();
				                    			//echo "<div class='num_posts' href='requests.php'>" . $txt_rep->entities($profile_user_obj->getDoctorConnections_num());
				                    		
				                    			if(!empty($doctors_array)){
				                    				//echo "<ul class='connection_data' id='deep_blue'><h1>Doctor Connections</h1>";
					                 	   		for($i=0;$i<=6;$i++){
					                 	   			if($i >= $profile_user_obj->getDoctorConnections_num()){
					                  	  				break;
					                 	   			}
					                  	  			$_temp_usern = $doctors_array[$i];
					                  	  			$_temp_usern_e = $crypt->EncryptU($_temp_usern);
					                   	 			$_temp_usr_obj = new User($con,$_temp_usern, $_temp_usern_e);
					                   	 			echo "<li><img src='" .$txt_rep->entities($_temp_usr_obj->getProfilePicFast())."'>"."<a href='" . bin2hex($txt_rep->entities($_temp_usr_obj->username_e)) . "'>Dr.  " .  $txt_rep->entities($_temp_usr_obj->getFirstAndLastNameFast()) . "<br><span class='connection_data_diff-size-font'>" . $txt_rep->entities($_temp_usr_obj->getSpecializationsText($lang)) ."</span></a></li>";
					                 	   		}
					                 	   		switch ($lang){
					                 	   			
					                 	   			case("en"):
					                 	   				echo "<br><a style='padding: 0;' href='connections.php?profile_username=" . bin2hex($username_e) . "'>View all</a>";
					                 	   				break;
					                 	   				
					                 	   			case("es"):
					                 	   				echo "<br><a style='padding: 0;' href='connections.php?profile_username=" . bin2hex($username_e) . "'>Ver todos.</a>";
					                 	   				break;
					                 	   		}
					                 	   		
					                 	   		
				                    			}
				                    			//echo "</div>";
										?>
									</ul>
								</div>
		                		</li>
						</ul>
					</li>
					<li id="division">
					</li>

					
					<li>
						<ul class="view_like" > 
							<li><img src="assets/images/icons/patconnections.png"></li>
		                    	<li>
			                    	<div class='num_likes' href='requests.php'><?php echo $txt_rep->entities($profile_user_obj->getPatientConnections_num()); ?>
			                    		
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
			                    		
			                    		
					                    	<?php
					                    		if($my_profile){
					                    			$patients_array = $profile_user_obj->getPatientConnections_array();
					                    		}
					                    		else{
					                    			$patients_array= [];
					                    		}
					                    		//echo "<div class='num_likes' href='requests.php'>" . $txt_rep->entities($profile_user_obj->getPatientConnections_num());
					                    		
					                    		if(!empty($patients_array) && $my_profile && FALSE){ //CURRENTLY NOT SHOWN
					                    			//echo "<ul class='connection_data'><h1>Patient Connections</h1>";
						                    		for($i=0;$i<=3;$i++){
						                    			if($i >= $profile_user_obj->getPatientConnections_num()){
						                    				break;
						                    			}
						                    			$_temp_usern = $patients_array[$i];
						                    			$_temp_usern_e = $crypt->EncryptU($_temp_usern);
						                    			$_temp_usr_obj = new User($con,$_temp_usern, $_temp_usern_e);
						                    			echo "<li><img src='" .$txt_rep->entities($_temp_usr_obj->getProfilePicFast())."'>"."<a href='" . $txt_rep->entities($_temp_usr_obj->getUsername()) . "'>" .  $txt_rep->entities($_temp_usr_obj->getFirstAndLastNameFast()) . "</a></li>";
						                    		}			
					                    		}
										?>
									</ul>
								</div>
		                    	</li>	
						</ul>
					</li>
				</ul>
				
            	
		</div>
	</div>
<div class = "wrapper_top">		
	<div class="wrapper">