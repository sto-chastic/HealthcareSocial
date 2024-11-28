<?php
	require '../../config/config.php';
	include('../classes/Post.php');
	include('../classes/User.php');
    $crypt = new Crypt();
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
			$userLoggedIn = $temp_user;
			$userLoggedIn_e = $temp_user_e;//Retrieves username
			$user = mysqli_fetch_array($verification_query);
			$stmt->close();
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: ../register.php");
		}

	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../register.php");
	}

	$var1 = false;
	$var2 = false;
	$var3 = false;
	$var4 = false;

	if($userLoggedIn != "" && isset($_GET['post_id']) && isset($_GET['u_t']) && isset($_GET['u_f'])){
		$post_id = $_GET['post_id'];
		$user_from = "";
		$user_to = "";

		$stmt = $con->prepare("SELECT username FROM users WHERE username=?");
		$stmt->bind_param("s", $temp_user_e);
		$get_user_to = $crypt->Decrypt(pack("H*", $_GET['u_t']));
        
        if( $get_user_to == ""){
			$user_to = "0000";
			$var2 = true;
		}
		else if($get_user_to != ""){
			$temp_user_e= pack("H*",$_GET['u_t']);
			$stmt->execute();
			$stmt->store_result();
			$numrows = $stmt->num_rows;

			if($numrows > 0){
				$var2 = true;
				$user_to =  $get_user_to;
			}
		}

		$temp_user_e= pack("H*",$_GET['u_f']);
		$stmt->execute();
		$stmt->store_result();
		$numrows = $stmt->num_rows;

		if($numrows > 0){
			$var3 = true;
			$user_from =  $crypt->Decrypt(pack("H*",$_GET['u_f']));
		}

		if(($user_from == $userLoggedIn) || ($user_to == $userLoggedIn)){
			$var1 = true;
		}

		if($var1){
		    $user_from_e = $crypt->EncryptU($user_from);
			$temp_user = new User($con, $user_from, $user_from_e);
			$post_tab = $temp_user->getPostsTable();

			$stmt = $con->prepare("SELECT * FROM $post_tab WHERE global_id=? AND added_by = ? AND user_to=?");
			$stmt->bind_param("sss", $temp_id, $user_from, $user_to);
			$temp_id = $_GET['post_id'];

			//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','$temp_id')");

			$stmt->execute();
			$stmt->store_result();
			$numrows = $stmt->num_rows;

			if($numrows > 0)
				$var4 = true;
		}

	}

	$stmt->close();
	if(isset($_POST['result']) && $var1 && $var2 && $var3 && $var4){

		if($_POST['result'] == 'true'){
			$my_post = new Post($con, $userLoggedIn, $userLoggedIn_e);
			$my_post->removePosts($post_id, $user_from, $user_to);
		}
	}
?>