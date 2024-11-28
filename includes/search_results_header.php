<?php
require 'config/config.php';
include ("includes/classes/TxtReplace.php");
include('includes/classes/Post.php');
include("includes/classes/Message.php");
include("includes/classes/Notification.php");
include("includes/classes/TimeStamp.php");
require 'includes/form_handlers/register_handler.php';
require 'includes/form_handlers/login_handler.php';
include ("includes/classes/SearchNMap.php");
include ("includes/classes/Calendar.php");
include ("includes/classes/User.php");
include ("includes/classes/Appointments_Calendar.php");
include ("includes/classes/Settings.php");


if (isset ( $_SESSION ['username'] ) && isset ( $_SESSION ['messages_token'] )) {
	$temp_user = $_SESSION ['username'];
	$temp_user_e = $_SESSION ['username_e'];
	$temp_messages_token = $_SESSION ['messages_token'];
	
	$stmt = $con->prepare ( "SELECT * FROM users WHERE username=? AND messages_token=?" );
	
	$stmt->bind_param ( "ss", $temp_user_e, $temp_messages_token );
	$stmt->execute ();
	$verification_query = $stmt->get_result ();
	
	if (mysqli_num_rows ( $verification_query ) == 1) {
		$userLoggedIn = $temp_user;
		$userLoggedIn_e = $temp_user_e;// Retrieves username
		$isLoggedIn = 1;
		
		$settings = new Settings($con, $userLoggedIn, $userLoggedIn_e);
		$user_obj = new User($con,$userLoggedIn, $userLoggedIn_e);
		//LANGUAGE RETRIEVAL
		
		$lang = $settings->getLang();		
		
		$stmt->close ();
	} else {
		$userLoggedIn = "";
		session_start ();
		session_destroy ();
		header ( "Location: register.php" );
		$stmt->close ();
	}
} elseif (isset ( $_SESSION ['username'] ) && isset ( $_SESSION ['confirm_email_alert'] )) {
	if ($_SESSION ['confirm_email_alert']) {
		$temp_user = $_SESSION ['username'];
		$temp_user_e = $_SESSION ['username_e'];
		
		$stmt = $con->prepare ( "SELECT * FROM users WHERE username=?" );
		
		$stmt->bind_param ( "s", $temp_user_e);
		$stmt->execute ();
		$verification_query = $stmt->get_result ();
		
		if (mysqli_num_rows ( $verification_query ) == 1) {
			$userLoggedIn = $temp_user;
			$userLoggedIn_e = $temp_user_e;
			$isLoggedIn = 1;
			$user_obj = new User($con,$userLoggedIn, $userLoggedIn_e);
			$settings = new Settings($con, $userLoggedIn, $userLoggedIn_e);
			
			//LANGUAGE RETRIEVAL
			
			$lang = $settings->getLang();
			
		} else {
			$userLoggedIn = "";
			session_start ();
			session_destroy ();
			header ( "Location: register.php" );
			$stmt->close ();
		}
	} else {
		$isLoggedIn = 0;
	}
} else {
	$isLoggedIn = 0;
}

$SnM = new SearchNMap ( $con );
$txt = new TxtReplace ();

if (isset ( $_SESSION ['i_code'] )) {
	$insurance_code = $_SESSION ['i_code'];
	
	if ($insurance_code == "all") {
		$payment = "part";
	} else {
		$payment = "insu";
	}
}

$_aux_array = [ ];

// Language

if(!isset($lang)){
	if(isset($_SESSION["lang"])){
		$lang = $_SESSION["lang"];
	}
	else{
		$lang = "es";
		$_SESSION["lang"] = $lang;
	}
}

$_SESSION["lang"] = $lang;

?>
<!DOCTYPE html>
<html>
<head>
<title>ConfiDr.</title>

<!-- Javascript -->
<script
	src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="assets/js/confidrmap.js"></script>
<script src="assets/js/confidr.js"></script>
<script src="assets/js/register.js"></script>
<script src="assets/js/bootstrap.js"></script>
<script src="assets/js/bootbox.min.js"></script>
<script src="assets/js/jquery.jcrop.js"></script>
<script src="assets/js/jcrop_bits.js"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>



<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<!-- CSS -->

<!-- <link rel="stylesheet" type="text/css" -->
<!-- 	href="assets/css/register_style.css"> -->
<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/jquery.Jcrop.css"
	type="text/css" />
	
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
		<div class="logo">
			<a href="index.php" title="ConfiDr."> <img
				src="assets/images/icons/logo.png" class="logo_position">
			</a>
		</div>
		
		
		<?php 
			if($isLoggedIn){
		?>
		
		<div class="search">
			<!-- GET method is for passing parameters in the url -->
			<form action="search.php" method="GET" name="search_form">

				<?php 
				 	switch ($lang){
	 		
				 		case("en"):
						 	?>
						 	<input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $lang;?>')" name="q"
							placeholder="Search ConfiDr." autocomplete="off"
							id="search_text_input">
							<?php 
							break;
						
						case("es"):
							?>
							<input type="text" onkeyup="getLiveSearchUsers(this.value, '<?php echo $lang;?>')" name="q"
							placeholder="Busca ConfiDr." autocomplete="off"
							id="search_text_input">
							<?php 
							break;
						
				 	}
				 ?>

				

				<div class="button_holder">
					<img src="assets/images/icons/search-icon.png">
				</div>

			</form>

			<div class="search_results style-2"></div>

			<div class="search_results_footer_empty"></div>

		</div>

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
				                onmouseout="this.src='assets/images/icons/wrench.png'" title= "Settings"/>            
				            	 	Settings
				            	</a>
				            	
							<a href="payments.php"> 
				            		<img src="assets/images/icons/wrench.png" class="icon_position"                                 
				            	 	onmouseover="this.src='assets/images/icons/wrenchh.png'"
				                onmouseout="this.src='assets/images/icons/wrench.png'" title= "Payments"/>            
				            	 	Payments
				            	</a>
				            	
				            	<?php if(!$user_obj->isDoctor()){?>
								<a href="health_info_input.php"> 
					            		<img src="assets/images/icons/medhist.png" class="icon_position"                                 
					            	 	onmouseover="this.src='assets/images/icons/medhitsh.png'"
					                onmouseout="this.src='assets/images/icons/medhist.png'" title= "Med.Hsit"/>            
					            	 	Med. History
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
				                onmouseout="this.src='assets/images/icons/wrench.png'" title= "Configuración"/>            
				            	 	Configuración
				            	</a>
				            	
				            	<a href="payments.php"> 
				            		<img src="assets/images/icons/wrench.png" class="icon_position"                                 
				            	 	onmouseover="this.src='assets/images/icons/wrenchh.png'"
				                onmouseout="this.src='assets/images/icons/wrench.png'" title= "Pagos"/>            
				            	 	Pagos
				            	</a>
				            	
				            	<?php if(!$user_obj->isDoctor()){?>
								<a href="health_info_input.php"> 
					            		<img src="assets/images/icons/medhist.png" class="icon_position"                                 
					            	 	onmouseover="this.src='assets/images/icons/medhitsh.png'"
					                onmouseout="this.src='assets/images/icons/medhist.png'" title= "Hist.Med"/>            
					            	 	Historia Med.
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
			// Unread Messages
			
			$messages = new Message ( $con, $userLoggedIn, $userLoggedIn_e);
			$num_messages = $messages->getUnreadNumber ();
			
			// Unread Notifications
			
			$notifications = new Notification ( $con, $userLoggedIn, $userLoggedIn_e);
			$num_notifications = $notifications->getUnreadNumber ();
			
			// Unread Notifications
			
			$user_obj = new User ( $con, $userLoggedIn, $userLoggedIn_e);
			if ($user_obj->isDoctor ()) {
				$num_friend_requests = $user_obj->getNumFriendRequests ();
			}
			?>

			<!--cambios en el logo de home-->
			<a href="javascript:void(0);"
				onclick="getDropdownData('message')">
				<?php
				if ($num_messages > 0)
					echo '<span class="notification_badge" id="unread_messages">' . $txt_rep->entities ( $num_messages ) . '</span>'?>

				<img src="assets/images/icons/quick.png" class="icon_position"
				onmouseover="this.src='assets/images/icons/quickh.png'"
				onmouseout="this.src='assets/images/icons/quick.png'"
				id="message_drop_down_button" title="QuickChat" />
			</a> <a href="javascript:void(0);"
				onclick="getDropdownData('notification')">
				<?php
				if ($num_notifications > 0)
					echo '<span class="notification_badge" id="unread_notifications">' . $txt_rep->entities ( $num_notifications ) . '</span>'?>

				<img src="assets/images/icons/notifications.png"
				class="icon_position"
				onmouseover="this.src='assets/images/icons/notificationsh.png'"
				onmouseout="this.src='assets/images/icons/notifications.png'"
				id="notification_drop_down_button" title="notificaciones" />

			</a> <a href="requests.php">
				<?php
				if ($user_obj->isDoctor ()) {
					if ($num_friend_requests > 0)
						echo '<span class="notification_badge" id="unread_fri_req">' . $txt_rep->entities ( $num_friend_requests ) . '</span>';
				}
				?>

				<img src="assets/images/icons/network.png" class="icon_position"
				onmouseover="this.src='assets/images/icons/networkh.png'"
				onmouseout="this.src='assets/images/icons/network.png'"
				title="conexiones" />

			</a>
		</nav>

		<div class="separator"></div>
		<navb> <a
			href="
				<?php
				echo bin2hex($txt_rep->entities ( $userLoggedIn_e ));
				?>">
				<?php
				echo $txt_rep->entities ( $user_obj->getFirstAndLastNameFast());
				?>   
			</a> <a
			href="
				<?php
				echo bin2hex($txt_rep->entities ( $userLoggedIn_e ));
				?>"> <img
			src="<?php echo $txt_rep->entities($user_obj->getProfilePicFast()); ?>">
		</a> </navb>

		<div class="dropdown_data_window" style="height: 0px; border: none;"
			id="style-2"></div>
		<input type="hidden" id="dropdown_data_type" value=""> <input
			type="hidden" id="dropdown_data_user" value="">
	
		<?php 
			}		?>
	</div>

<?php 
	if($isLoggedIn){
?>
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

				return false;
			}); 
		});



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

<?php 
	}
?>

<div class= "top_banner_title">
	<div class="top_banner_title_text_container" style=" width: 1200px; top: 112px;">
		
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
												name="searched_insurance1" placeholder="Ex: Uninsured"
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
										type="submit" id="go_docsearch" name="go_search" value="GO"></li>
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
												name="searched_insurance1" placeholder="Ej: Particular"
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
										type="submit" id="go_docsearch" name="go_search" value="Ir"></li>
								</ul>
								<?php 
								break;
					 	}
						?>
						
						
					</form>
				</div>
	</div>	
</div>
