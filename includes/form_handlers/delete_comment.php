<?php
	require '../../config/config.php';
	include('../classes/Post.php');
	include('../classes/User.php');
	
	$userLoggedIn = "";
	
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
			$crypt = new Crypt();
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: register.php");
		}

	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: register.php");
	}

	
	if($userLoggedIn !="" && isset($_REQUEST['comm_id'])){
		$comm_id = $_REQUEST['comm_id'];
		
		# Try to delete the comment, but check if the user is the poster
		$temp_user = new User($con, $userLoggedIn,$userLoggedIn_e);
		$comm_tab = $temp_user->getCommentsTable();
		
		$stmt = $con->prepare("SELECT * FROM $comm_tab WHERE posted_by=? AND comment_global_id = ?");
		$stmt->bind_param("ss", $userLoggedIn, $comm_id);
		
		$stmt->execute();
		$comment_arr_q = $stmt->get_result();
		
		$num_rows_security = mysqli_num_rows($comment_arr_q);
		
		if($num_rows_security > 0){
	
			$comment_arr = mysqli_fetch_assoc($comment_arr_q);
			
			// Proceed to delete the data form this table
			$fetched_comm_id = $comment_arr['comment_global_id'];
			
			$stmt = $con->prepare("DELETE FROM $comm_tab WHERE comment_global_id=?");
			$stmt->bind_param("s", $fetched_comm_id);
			$stmt->execute();
			// Proceed to update table form friends
			
			$friends_arr = $temp_user->getFriends();
			
			foreach($friends_arr as $i){
				
				$friend_obj = new User($con, $i['username_friend'], $crypt->EncryptU($i['username_friend']));
				$friends_comms_tab = $friend_obj->getCommentsTable();
				//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','$friends_comms_tab')");
				
				$stmt = $con->prepare("DELETE FROM $friends_comms_tab WHERE comment_global_id=?");
				$stmt->bind_param("s", $fetched_comm_id);
				$stmt->execute();
			}
			
		}
	}

?>