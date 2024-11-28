<?php 

require 'config/config.php';
include('includes/classes/User.php');
include('includes/classes/Post.php');
include('includes/classes/Notification.php');
include('includes/classes/TxtReplace.php');
include('includes/classes/TimeStamp.php');
include('includes/classes/Settings.php');

?><!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body>

	<style type="text/css">
		*{
			font-family: Arial, Helvetica, Sans-serif;
		}
		body{
			background-color: #FFF;
		}
		form{
				position: absolute;
				top:0;
				
		}
	</style>

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

				$userLoggedIn = $temp_user;
				$userLoggedIn_e = $temp_user_e;//Retrieves username

				$user = mysqli_fetch_array($verification_query);
				$stmt->close();
			}
			else{
				$userLoggedIn = "";
				session_start();
				session_destroy();
				header("Location: register.php");
			}

			$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
			$txt_rep = new TxtReplace();
			
			if(isset($_SESSION['lang'])){
			    $lang = $_SESSION['lang'];
			} else $lang = "es";
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: register.php");
		}

		//Get id of post
		if(isset($_GET['post_id'])){
			$post_id = $_GET['post_id'];
		}
		$posts = $user_obj->getPostsTable();

		$stmt = $con->prepare("SELECT likes, added_by, user_to FROM $posts WHERE global_id = ?");
		$stmt->bind_param("s", $post_id);
		$stmt->execute();

		$user_query = $stmt->get_result();
		if(mysqli_num_rows($user_query) == 0){
			echo "No Likes to Show";
			die;
		}
		$arr = mysqli_fetch_array($user_query);

		$my_likes_tab = $user_obj->getLikesTable();
		$check_query = mysqli_query($con, "SELECT * FROM $my_likes_tab WHERE post_id='$post_id'");
		$num_likes_upd = mysqli_num_rows($check_query);

		$check_query = mysqli_query($con, "UPDATE $posts SET likes ='$num_likes_upd' WHERE global_id='$post_id'");


		$post_likes = $arr['likes'];
		$posted_by = $arr['added_by'];
		$user_to = $arr['user_to'];


		//Like button
		if(isset($_POST['like_button'])){
			
			$my_posts_tab = $user_obj->getPostsTable();
			$my_likes_tab = $user_obj->getLikesTable();
			$my_username = $user_obj->getUsername();
			
			$check_query_my_likes =  mysqli_query($con, "SELECT * FROM $my_likes_tab WHERE post_id='$post_id' AND username = '$my_username'");
			$num_rows_my_likes = mysqli_num_rows($check_query_my_likes);
		
			if($num_rows_my_likes == 0){
				if($user_to =! "0000")
					$owner = $user_to;
				else
					$owner = $posted_by;
	
				$owner_obj = new User($con,$owner, $crypt->EncryptU($owner));
				$owner_likes = $owner_obj->getUserLikeFast();
				$owner_connections = $owner_obj->getConnectionsTable();
	
				$post_likes++;
				$owner_likes++;
	
	
				$owner_likes_q = mysqli_query($con, "UPDATE users SET num_likes='$owner_likes' WHERE username='$owner'");
	
				$friends_query = mysqli_query($con, "SELECT username_friend FROM $owner_connections ");
	
	
				foreach ($friends_query as $i) {
	
					$friend = new User($con, $i['username_friend'], $crypt->EncryptU($i['username_friend']));
					$friend_posts_tab = $friend->getPostsTable();
					$friend_likes_tab = $friend->getLikesTable();
	
					$update_post = mysqli_query($con, "UPDATE $friend_posts_tab SET likes ='$post_likes' WHERE global_id='$post_id' ");
					$insert_like = mysqli_query($con, "INSERT INTO $friend_likes_tab VALUES('', '$userLoggedIn', '$post_id')");
				}
	
				$owner_posts_tab = $owner_obj->getPostsTable();
				$owner_likes_tab = $owner_obj->getLikesTable();
	
				$update_post = mysqli_query($con, "UPDATE $owner_posts_tab SET likes ='$post_likes' WHERE global_id='$post_id' ");
				$insert_like = mysqli_query($con, "INSERT INTO $owner_likes_tab VALUES('', '$userLoggedIn', '$post_id')");
	
				//Insert notification
	
				if($owner != $userLoggedIn){
					$UserLoggedIn_e = $crypt->EncryptU($userLoggedIn);
					$notification = new Notification($con, $userLoggedIn, $UserLoggedIn_e);
					$notification->insertNotification($post_id, $owner, "like"); //post_id is the ID of the post been liked.
				}
			}
		}

		//Unlike button

		if(isset($_POST['unlike_button'])){
			
			$my_posts_tab = $user_obj->getPostsTable();
			$my_likes_tab = $user_obj->getLikesTable();
			$my_username = $user_obj->getUsername();
			
			$check_query_my_likes =  mysqli_query($con, "SELECT * FROM $my_likes_tab WHERE post_id='$post_id' AND username = '$my_username'");
			$num_rows_my_likes = mysqli_num_rows($check_query_my_likes);
			
			if($num_rows_my_likes != 0){
				if($user_to =! "0000")
					$owner = $user_to;
				else
					$owner = $posted_by;
	
				$owner_obj = new User($con,$owner, $crypt->EncryptU($owner));
				$owner_likes = $owner_obj->getUserLikeFast();
				$owner_connections = $owner_obj->getConnectionsTable();
	
				$post_likes--;
				$owner_likes--;
	
	
				$owner_likes_q = mysqli_query($con, "UPDATE users SET num_likes='$owner_likes' WHERE username='$owner'");
	
				$friends_query = mysqli_query($con, "SELECT username_friend FROM $owner_connections ");
	
	
				foreach ($friends_query as $i) {
	
					$friend = new User($con, $i['username_friend'], $crypt->EncryptU($i['username_friend']));
					$friend_posts_tab = $friend->getPostsTable();
					$friend_likes_tab = $friend->getLikesTable();
	
					$update_post = mysqli_query($con, "UPDATE $friend_posts_tab SET likes ='$post_likes' WHERE global_id='$post_id' ");
					$insert_like = mysqli_query($con, "DELETE FROM $friend_likes_tab WHERE post_id='$post_id' && username = '$userLoggedIn'");
				}
	
				$owner_posts_tab = $owner_obj->getPostsTable();
				$owner_likes_tab = $owner_obj->getLikesTable();
	
				$update_post = mysqli_query($con, "UPDATE $owner_posts_tab SET likes ='$post_likes' WHERE global_id='$post_id' ");
				$insert_like = mysqli_query($con, "DELETE FROM $owner_likes_tab WHERE post_id='$post_id'  && username = '$userLoggedIn'");
			}
		}


		//Check for previous likes

		$my_posts_tab = $user_obj->getPostsTable();
		$my_likes_tab = $user_obj->getLikesTable();
		$my_username = $user_obj->getUsername();

		$check_query_my_likes =  mysqli_query($con, "SELECT * FROM $my_likes_tab WHERE post_id='$post_id' AND username = '$my_username'");
		$check_query_total_likes =  mysqli_query($con, "SELECT * FROM $my_likes_tab WHERE post_id='$post_id'");

		$num_rows_total_likes = mysqli_num_rows($check_query_total_likes);
		$num_rows_my_likes = mysqli_num_rows($check_query_my_likes);
		
		//echo $num_rows_my_likes;

		switch($lang){
		    case("en"):{
		        if($num_rows_my_likes > 0){
		            echo '<form action="like.php?post_id=' . $txt_rep->entities($post_id) . '" method="POST">
					<div class="like_value">
                    		<img src="assets/images/icons/like.png">
						(' . $txt_rep->entities($num_rows_total_likes) . ') Likes

					</div>
					<input type="submit" class="comment_unlike" name="unlike_button" value="Unlike">
				</form>
				';
		        }
		        else {
		            echo '<form action="like.php?post_id=' . $txt_rep->entities($post_id) . '" method="POST">
				<div class="like_value">
					<div class="like_value">
                    		<img src="assets/images/icons/like.png">
						(' . $txt_rep->entities($num_rows_total_likes) . ') Likes
					</div>

					<input type="submit" class="comment_like" name="like_button" value="Like">
				</div>
		            
			</form>
			';
		        }
		    }
		        break;
		    case("es"):{
		        if($num_rows_my_likes > 0){
		            echo '<form action="like.php?post_id=' . $txt_rep->entities($post_id) . '" method="POST">
					<div class="like_value">
                    		<img src="assets/images/icons/like.png">
					    (' . $txt_rep->entities($num_rows_total_likes) . ') Me gusta
					</div>
					<input type="submit" class="comment_unlike" name="unlike_button" value="No me gusta">
				</form>
				';
		        }
		        else {
		            echo '<form action="like.php?post_id=' . $txt_rep->entities($post_id) . '" method="POST">
				<div class="like_value">
					<div class="like_value">
                    		<img src="assets/images/icons/like.png">
						(' . $txt_rep->entities($num_rows_total_likes) . ') Me gusta 
					</div>
					<input type="submit" class="comment_like" name="like_button" value="Me gusta">
				</div>
		            
			</form>
			';
		        }
		    }
		        break;
		}
		
	 ?>

</body>
</html>