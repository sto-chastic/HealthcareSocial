<?php 
	require 'config/config.php';
	include('includes/classes/User.php');
	include('includes/classes/Post.php');
	include("includes/classes/Message.php");
	include("includes/classes/Notification.php");
	include("includes/classes/TimeStamp.php");
	include("includes/classes/TxtReplace.php");
	include("includes/classes/Calendar.php");
	include("includes/classes/Appointments_Calendar.php");
	include("includes/classes/Appointments_Master.php");
	include("includes/classes/Settings.php");
	
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

		$comments = $user_obj->getCommentsTable();
		$connection_requests = $user_obj->getRequestsTable();
		$likes = $user_obj->getLikesTable();
		$messages = $user_obj->getMessagesTable();
		$notifications = $user_obj->getNotificationsTable();
		$posts = $user_obj->getPostsTable();
		
		//LANGUAGE RETRIEVAL
		
		$lang = $settings->getLang();
		$_SESSION["lang"] = $lang;

	}
	else{
		$userLoggedIn = "";
		$userLoggedIn_e = "";
		//session_start();
		session_destroy();
		header("Location: register.php");
		//$stmt->close();
	}


?>

<!DOCTYPE html>
<html>
<head>
	<title>ConfiDr.</title>
	
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

                <img src="assets/images/icons/logo.png" class="logo_position" >

            </a>
		</div>

		<div class="search">
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

			<div class="search_results"  id="style-2"></div>

			<div class="search_results_footer_empty">
			</div>

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
					            	 	onmouseover="this.src='assets/images/icons/medhisth.png'"
					                onmouseout="this.src='assets/images/icons/medhist.png'" title= "Med. History"/>            
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
					            	 	onmouseover="this.src='assets/images/icons/medhisth.png'"
					                onmouseout="this.src='assets/images/icons/medhist.png'" title= "Med. History"/>            
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
                  onmouseover="this.src='assets/images/icons/networkH.png'"
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

	</div>

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
							if($('.dropdown_data_window').find('.noMoreDropdownData').val() == 'false'){
								$('.dropdown_data_window').find('.noMoreDropdownData').remove(); //Removes current next page
								$('.dropdown_data_window').append(responseM);
							}
							
						}
					});

				}
				/*else if(noMorePosts == 'true'){
					$('#loading').hide();
				}*/

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
	
<!-- 	<div class= "top_banner"> -->

<!-- 	</div> -->
	
 	<?php
 	$show_animation = TRUE;
 	
 	if(isset($_SESSION['animation_hide'])){
 		if($_SESSION['animation_hide'] == TRUE){
 			$show_animation = FALSE;
 		}
 	}
 	switch ($lang){
 		
 		case("en"):
		 	?>
		 	<div class="title_text" id="<?php echo ($show_animation)? 'animate_title_text':'animated_title_text';?>"> <p><span class="caps_confidr"> Hi, share all </span> you want about healthcare  </p>        
			</div> 
			<?php 
			break;
		
		case("es"):
			?>
		 	<div class="title_text" id="<?php echo ($show_animation)? 'animate_title_text':'animated_title_text';?>"> <p><span class="caps_confidr"> Hola, comparte </span> tu experiencia en salud </p>        
			</div> 
			<?php 
			break;
		
 	}
	?>
	<div class="grey_banner" id="<?php echo ($show_animation)? 'animate_grey_banner':'animated_grey_banner';?>">
 	</div> 
 	<div class="title_search">
 	</div> 

	<div class="wrapper" id="<?php echo ($show_animation)? 'animate_wrapper':'animated_wrapper';?>">