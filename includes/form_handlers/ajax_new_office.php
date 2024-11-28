<?php
include("../../config/config.php");
include("../classes/User.php");

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
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: register.php");
	$stmt->close();
}

if (isset($_SESSION['lang'])){
    $lang = $_SESSION['lang'];
} else {
    $lang = "es";
}

$office_num = $_REQUEST['office_num'];
switch($lang) {
    case("en"):{
        switch ($office_num){
            case 2:
                $statement = "ad2nick";
                $default_name = "Office 2";
                $response = "office2_div";
                break;
            case 3:
                $statement = "ad3nick";
                $default_name = "Office 3";
                $response = "office3_div";
                break;
        }
        break;
    }
    case("es"):{
        switch ($office_num){
            case 2:
                $statement = "ad2nick";
                $default_name = "Consultorio 2";
                $response = "office2_div";
                break;
            case 3:
                $statement = "ad3nick";
                $default_name = "Consultorio 3";
                $response = "office3_div";
                break;
        }
        break;
    }
    
    
}


$sql = "UPDATE basic_info_doctors SET " . $statement . " = ? WHERE username = ?";

$stmt = $con->prepare($sql);
$stmt->bind_param("ss",$default_name,$userLoggedIn);
$stmt->execute();

echo $response;
?>