<?php 
	include("../../config/config.php");
	include("../classes/User.php");
	include("../classes/Post.php");
	include("../classes/TimeStamp.php");
	include("../classes/TxtReplace.php");
	include("../classes/Settings.php");
    $crypt = new Crypt();
    $txt_rep = new TxtReplace();
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
			header("Location: ../../register.php");
			$stmt->close();
		}
	}
	else{
		$userLoggedIn = "";
	}

	
	$limit = 4; //Number of posts loaded each time;
	
	if($userLoggedIn!=""){
	    $posts= new Post($con, $userLoggedIn, $userLoggedIn_e);
	    $_REQUEST['profileUsername'] = $txt_rep->entities($_REQUEST['profileUsername']);
	    $_REQUEST['profileUsername'] = $crypt->Decrypt(pack("H*",$_REQUEST['profileUsername']));//profile_owner is the encrypted version

	    $posts->loadProfilePosts($_REQUEST, $limit);
	}
	elseif($_REQUEST['page']==="1"){
		$limit = 10; //Number of posts loaded each time;
		$_REQUEST['profileUsername'] = $txt_rep->entities($_REQUEST['profileUsername']);
		$profile_owner_e = pack("H*",$_REQUEST['profileUsername']);
		$_REQUEST['profileUsername'] = $crypt->Decrypt($profile_owner_e);//profile_owner is the encrypted version
		$posts= new Post($con, $_REQUEST['profileUsername'], $profile_owner_e);
		$posts->loadProfilePosts_public($_REQUEST, $limit); //TODO:Create new function that does not show comments or likes, just the post, and ask to create an user to view more
	}
 ?>

