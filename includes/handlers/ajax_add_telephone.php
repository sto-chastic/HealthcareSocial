<?php

//TODO: DEPRECATED AJAX!


require '../../config/config.php';
include('../classes/User.php');
include('../classes/Calendar.php');
include_once('../classes/TxtReplace.php');

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
        
        $lang = $_SESSION['lang'];
        
    }
    else{
        $is1 = $_SESSION['username'];
        $is3 = mysqli_num_rows($verification_query);
        $userLoggedIn = "";
        session_start();
        session_destroy();
        header("Location: register.php");
    }
    
}
else{
    $is1 = $_SESSION['username'];
    $userLoggedIn = "";
    session_start();
    session_destroy();
    header("Location: register.php");
}

if(isset($_POST['telephone'])){
    $txtrep = new TxtReplace();
    $description = htmlspecialchars($_POST['telephone']);
    $description = strip_tags($description);
    $description = $txtrep->entities($description);
    
	$user_obj = new User($con,$userLoggedIn,$userLoggedIn_e);
	$phone_table = $user_obj->getPhoneTable();
	
	$query_str = "INSERT INTO $phone_table (`id`, `telephone`, `office_num`) VALUES (1,?,'') ON DUPLICATE KEY UPDATE
	telephone=?";

	$stmt = $con->prepare($query_str);
	
	$stmt->bind_param("ss", $description, $description);
	$stmt->execute();
	
	switch($lang){
		case "en":
			echo "<div class='floating_message'> Saved Changes. </div>";
			break;
		case "es":
			echo "<div class='floating_message'> Cambios Guardados. </div>";
			break;
	}
	
	echo '<script>
		$(document).ready(function(){
			$(".floating_message").show(300);
			setTimeout(function () {
				$(".floating_message").hide(300);
			}, 1500);
		});
	</script>';
    
}
?>