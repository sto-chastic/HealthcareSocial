<?php 
require 'config/config.php';
include_once('includes/classes/User.php');
include('includes/classes/Post.php');
include('includes/classes/Notification.php');
include('includes/classes/TxtReplace.php');
include('includes/classes/TimeStamp.php');
include('includes/classes/Settings.php');
?>

<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	
	<!-- Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<script src="assets/js/bootstrap.js"></script>
	<script src="assets/js/bootbox.min.js"></script>
	
	
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	
	<style type="text/css">
		* {
			font-size: 12px;
			font-family: Arial, Helvetica, Sans-serif;
			margin:0px;
			overflow:hidden;
		}
	</style>
	
</head>
<body>

	<?php 
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
			$time_stamp = new TimeStamp();

			$comments = $user_obj->getCommentsTable();
			$connection_requests = $user_obj->getRequestsTable();
			$likes = $user_obj->getLikesTable();
			$messages = $user_obj->getMessagesTable();
			$notifications = $user_obj->getNotificationsTable();
			$posts = $user_obj->getPostsTable();

			//LANGUAGE RETRIEVAL

			$lang = $_SESSION['lang'];
			
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: register.php");
			$stmt->close();
		}
	 ?>

	<script>
		function toggle() {
			var element = document.getElementById("comment_section");

			if(element.style.display == "block"){
				element.style.display = "none";
			}
			else{
				element.style.display = "block";
			}
		}
	</script>

	<?php 
		//Get id of post
		if(isset($_GET['post_id'])){

			$stmt = $con->prepare("SELECT added_by FROM $posts WHERE global_id=?");
			$stmt->bind_param("s", $temp_id);
			$temp_id = $_GET['post_id'];
			$stmt->execute();

			$verification_query = $stmt->get_result();

			if(mysqli_num_rows($verification_query) == 1){
				$post_id = $temp_id;

				$stmt = $con->prepare("SELECT added_by, user_to FROM $posts WHERE global_id=?");
				$stmt->bind_param("s", $post_id);
				$stmt->execute();

				$user_query = $stmt->get_result();
				$arr = mysqli_fetch_array($user_query);
				$stmt->close();

				$posted_by = $arr['added_by'];
				$user_to = $arr['user_to'];
				
				$posted_by_e =  $crypt->EncryptU($posted_by);
				
				if(isset($_POST['postComment' . $post_id])){
					$post_body = $_POST['post_body'];
					$empty_check = trim($post_body);
					
					if($empty_check != ""){
					    $post_body = $crypt->encryptString($post_body,$userLoggedIn_e."98634jghajGMHBSsd8712ugyjbdu6gDHnahvgAadjhasadf");
						$post_body = mysqli_escape_string($con, $post_body);
						$date_time_now = date("Y-m-d H:i:s");
						$unique_comment_id = md5($post_id . date("ymdHis") . mt_rand(0,9));
	
						if($userLoggedIn == $user_to){//commenting on my own post. TODO: this is unnecessary as 'else' covers this case.
							$connections = $user_obj->getConnections_tab();
							$friends_query = mysqli_query($con, "SELECT username_friend FROM $connections");
							foreach ($friends_query as $i) {
	              				$username_friend_e= $crypt->EncryptU($i['username_friend']);
	              				$friend = new User($con, $i['username_friend'], $username_friend_e);
								$friend_comments_tab = $friend->getCommentsTable();
								//echo $friend_comments_tab;
								$stmt = $con->prepare("INSERT INTO $friend_comments_tab VALUES ('',? ,? , ?, ?, ?, 'no', ?)");
								$stmt->bind_param("ssssss",$unique_comment_id, $post_body, $userLoggedIn, $posted_by, $date_time_now, $post_id);
								$stmt->execute();
	
								//$insert_post = mysqli_query($con, "INSERT INTO $friend_comments_tab VALUES ('', '$post_body', '$userLoggedIn', '$posted_by','$date_time_now', 'no', '$post_id')");
							}
	
							$stmt = $con->prepare("INSERT INTO $comments VALUES ('',? ,? , ?, ?, ?, 'no', ?)");
							$stmt->bind_param("ssssss", $unique_comment_id, $post_body, $userLoggedIn, $posted_by, $date_time_now, $post_id);
							$stmt->execute();
							$stmt->close();
	
							//$insert_post = mysqli_query($con, "INSERT INTO $comments VALUES ('', '$post_body', '$userLoggedIn', '$posted_by','$date_time_now', 'no', '$post_id')");
	
						}
						else if($user_to == "0000"){
                            $posted_by_e = $crypt->EncryptU($posted_by);
                            $user_from_obj = new User($con, $posted_by, $posted_by_e);
                            $user_from_connections_tab = $user_from_obj->getConnectionsTable();
                            $user_from_comments = $user_from_obj->getCommentsTable();
                            $friends_query = mysqli_query($con, "SELECT username_friend FROM $user_from_connections_tab");
                            foreach ($friends_query as $i) {
                                $i['username_friend_e'] = $crypt->EncryptU($i['username_friend']);
                                $friend = new User($con, $i['username_friend'], $i['username_friend_e']);
                                $friend_comments_tab = $friend->getCommentsTable();

	
                                $stmt = $con->prepare("INSERT INTO $friend_comments_tab VALUES ('', ?,  ?, ?, ?, ?, 'no', ?)");
							
                                $stmt->bind_param("ssssss", $unique_comment_id, $post_body, $userLoggedIn, $posted_by, $date_time_now, $post_id);
                                $stmt->execute();
	
								//$insert_post = mysqli_query($con, "INSERT INTO $friend_comments_tab VALUES ('', '$post_body', '$userLoggedIn', '$posted_by','$date_time_now', 'no', '$post_id')");
							}
							$stmt = $con->prepare("INSERT INTO $user_from_comments VALUES ('', ?, ?, ?, ?, ?, 'no', ?)");
							$stmt->bind_param("ssssss", $unique_comment_id, $post_body, $userLoggedIn, $posted_by, $date_time_now, $post_id);
							$stmt->execute();
	
							//$insert_post = mysqli_query($con, "INSERT INTO $user_from_comments VALUES ('', '$post_body', '$userLoggedIn', '$posted_by','$date_time_now', 'no', '$post_id')");
	
						}
						else{
              $user_to_e = $crypt->EncryptU($user_to);
              $user_to_obj = new User($con, $user_to, $user_to_e);
							$user_to_tab = $user_to_obj->getConnectionsTable();
							$user_to_comments = $user_to_obj->getCommentsTable();
	
							$friends_query = mysqli_query($con, "SELECT username_friend FROM $user_to_tab");
							foreach ($friends_query as $i) {

                $i['username_friend_e'] = $crypt-> EncryptU($i['username_friend']);

								$friend = new User($con, $i['username_friend'], $i['username_friend_e']);
								$friend_comments_tab = $friend->getCommentsTable();
	
								$stmt = $con->prepare("INSERT INTO $friend_comments_tab VALUES ('', ?, ?, ?, ?, ?, 'no', ?)");
						
								$stmt->bind_param("ssssss", $unique_comment_id, $post_body, $userLoggedIn, $posted_by, $date_time_now, $post_id);
								$stmt->execute();
								//$insert_post = mysqli_query($con, "INSERT INTO $friend_comments_tab VALUES ('', '$post_body', '$userLoggedIn', '$posted_by','$date_time_now', 'no', '$post_id')");
							}
	
							$stmt = $con->prepare("INSERT INTO $user_to_comments VALUES ('', ?, ?, ?, ?, ?, 'no', ?)");
							$stmt->bind_param("ssssss", $unique_comment_id, $post_body, $userLoggedIn, $posted_by, $date_time_now, $post_id);
							$stmt->execute();
							//$insert_post = mysqli_query($con, "INSERT INTO $user_to_comments VALUES ('', '$post_body', '$userLoggedIn', '$posted_by','$date_time_now', 'no', '$post_id')");
	
						}
						if($posted_by != $userLoggedIn){ //the owner is not me, notifies the owner, ie, who posted the post: posted_by
							$notification = new Notification($con, $userLoggedIn, $userLoggedIn_e);
							$notification->insertNotification($post_id, $posted_by, "comment");
						}
	
						if($user_to != '0000' && $user_to != $userLoggedIn){ //notifies the person it was posted to.
						  $notification = new Notification($con, $userLoggedIn, $userLoggedIn_e);
							$notification->insertNotification($post_id, $user_to, "profile_comment");
						}
	
						
						
						
						$get_commenters = mysqli_query($con, "SELECT * FROM $comments WHERE post_id ='$post_id'");//post_id is safe already
	
						$notify_users = array();
						while($arr = mysqli_fetch_array($get_commenters)){
	
							if($arr['posted_by']/*message*/ != $posted_by /*post*/ && $arr['posted_by'] != $user_to
								&& $arr['posted_by'] != $userLoggedIn && !in_array($arr['posted_by'], $notify_users)){//the person who commented is not the person who posted, & the person who commented is not the person who the post was for.
	  
								$notification = new Notification($con, $userLoggedIn, $userLoggedIn_e);
								$notification->insertNotification($post_id, $arr['posted_by'], "comment_non_owner");
	
								array_push($notify_users, $arr['posted_by']);
	
							}
						}
						switch ($lang){
							case("en"):
								echo "<div id='comment_iframe_status'>Comment posted.</div>";
								break;
								
							case("es"):
								echo "<div id='comment_iframe_status'>Comentario Publicado.</div>";
								break;
						}
						
					}
					else{
						switch ($lang){
							
							case("en"):
								echo "<div id='comment_iframe_status'>Comment cannot be empty.</div>";
								break;
								
							case("es"):
								echo "<div id='comment_iframe_status'>El comentario no puede ser vacio.</div>";
								break;
						}
					}
				}
			}
		}
		else{
			$stmt->close();
			switch ($lang){
				
				case("en"):
					echo "<div id='comment_iframe_status'>404 Post not found.</div>";
					break;
					
				case("es"):
					echo "<div id='comment_iframe_status'>404 Publicación no encontrada.</div>";
					break;
			}
			
		}

	?>

	<form action="comment_frame.php?post_id=<?php echo $txt_rep->entities($post_id); ?>" id="comment_form" name="postComment<?php echo $txt_rep->entities($post_id); ?>" method="POST">
		<textarea  id="post_comment" class="style-2" name="post_body" placeholder='<?php 
			switch ($lang){
				case("en"):
					echo "Write a comment to this post.";
					break;
				case("es"):
					echo "Escribe un comentario a este post.";
					break;
			}
		?>'></textarea>
		<?php 
		
			switch ($lang){
					 		
				case("en"):
		?>
					<input type="submit" name="postComment<?php echo $txt_rep->entities($post_id); ?>" value="Comment"></input>
		<?php 
			        break;
				
				case("es"):
		?>
					<input type="submit" name="postComment<?php echo $txt_rep->entities($post_id); ?>" value="Comentar"></input>
		<?php 
					break;
			}
		?>
		
	</form>
	
	
	<script>

			
        	$("#comment_iframe").click(function(){    
            	   
           	    $(this).css( {backgroundColor: "green"} );            
            });

	       	$('#post_comment').on('keydown', function(e){
	            var that = $(this);
	            if (that.scrollTop()) {
	                $(this).height(function(i,h){
	                    return h + 10;
	                });
	            }
	        });
	        
	       	

	     	
	       	

	</script>
	
	<div class="comments_listed style-2">
    	<!-- Load Comments -->
    	<?php
    		$get_comments = mysqli_query($con, "SELECT * FROM $comments WHERE post_id='$post_id' ORDER BY date_added DESC");
    		$count = mysqli_num_rows($get_comments);
    		if ($count != 0){
    			while($comment = mysqli_fetch_array($get_comments)) {
    				$comment_body = $comment['post_body'];
    				$posted_to = $comment['posted_to'];
    				$posted_by = $comment['posted_by'];
    				$posted_by_e = $crypt->EncryptU($posted_by);
    				$date_added = $comment['date_added'];
    				$removed = $comment['removed'];
    				$unique_comment_id = $comment['comment_global_id'];
    				//TODO:
    				$comment_body = $crypt->decryptString($comment_body,$posted_by_e."98634jghajGMHBSsd8712ugyjbdu6gDHnahvgAadjhasadf");
    				
    				if($removed != 'no'){
    					continue;
    				}
    
    				$date_time_now = date("Y-m-d H:i:s");
    				$start_date = new DateTime($date_added); //Time of post
    				$end_date = new DateTime($date_time_now); //Current time
    				$interval = $start_date->diff($end_date); //Difference between dates
    
    				$time_message = $time_stamp->getTimeStamp($interval);
    
    
    				$posted_by_obj = new User($con, $posted_by, $posted_by_e);				
    				if($posted_by == $userLoggedIn){
    				?>
    		       	<script>
    
    		       	
    		       	
    					$(document).ready(function(){
    						lang = '<?php echo $lang;?>';
    						
    						switch(lang){
    						case 'en': confirmDelete="Do you want to delete this comment?";
    							break;
    						case 'es': confirmDelete="Quieres borrar este comentario?";
    							break; 
    						}
    						
    						$('#delcomment_<?php echo $unique_comment_id; ?>').on('click', function(){
    							bootbox.confirm(confirmDelete, function(result){
    								if(result){
    									$.ajax({
    										type: "POST",
    										url: "includes/form_handlers/delete_comment.php",
    										data: "comm_id=" + "<?php echo $unique_comment_id; ?>", //What we send!
    										success: function() {
    											//alert(msg);
    											location.reload();
    										}
    									});
    								}
    							});
    						});
    					});
    				</script>
    				<?php 
    				}
    				?>
    				
        				<div class="comment_section">
        					<a href="<?php echo bin2hex($txt_rep->entities($crypt->EncryptU($posted_by))); ?>" target="_parent">
        						<img src="<?php echo $txt_rep->entities($posted_by_obj->getProfilePicFast()); ?>" title="<?php echo $txt_rep->entities($posted_by_obj->getFirstAndLastNameFast()); ?>" >
        					</a>
        					<a href="<?php echo bin2hex($txt_rep->entities($crypt->EncryptU($posted_by)));?>" target="_parent">
        						<b><?php echo $txt_rep->entities($posted_by_obj->getFirstAndLastNameFast()); ?></b>
        					</a>
        					<div id="comment_section_date">- <?php echo $txt_rep->entities($time_message) . "</div><br><div class='style-2' id='comment_section_text'>".  $txt_rep->entities($comment_body);?>
        					</div>
        					<?php
        					if($posted_by == $userLoggedIn){
        					?>
        						<button class="delete_button btn-danger" style="background-color: #f8f8f8; position: absolute;top: 0px; right: 0px;" id="<?php echo 'delcomment_'.$unique_comment_id?>">x</button>
        					<?php 
        					}
        					?>
        				</div>
    				
    				<?php
    
    			}
    		}
    		else {
    			switch ($lang){
    				
    				case("en"):
    					echo "<div id='comment_iframe_status' style='text-align:center;top: 53px;'><br>No comments to show.</div>";
    					break;
    					
    				case("es"):
    					echo "<div id='comment_iframe_status' style='text-align:center;top: 53px;'><br>No hay comentarios para mostrar.</div>";
    					break;
    			}
    		}
    	 ?>
    	</div>
    </div>	
</body>
</html>