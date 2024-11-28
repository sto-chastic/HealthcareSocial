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
		$userLoggedIn = $temp_user;
		$userLoggedIn_e = $temp_user_e;
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
	
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

//Get latest height
$table_height = $user_obj->getHeight();
$stmt = $con->prepare("SELECT height,date_time FROM $table_height ORDER BY date_time DESC LIMIT 1");
$stmt->execute();
$query = $stmt->get_result();
$height_arr = mysqli_fetch_assoc($query);
$height = $height_arr['height'];
$height_date = $height_arr['date_time'];

//Get latest weight
$table_weight = $user_obj->getWeight();
$stmt = $con->prepare("SELECT weight,date_time FROM $table_weight ORDER BY date_time DESC LIMIT 1");
$stmt->execute();
$query = $stmt->get_result();
$weight_arr = mysqli_fetch_assoc($query);
$weight = $weight_arr['weight'];
$weight_date = $weight_arr['date_time'];

if($weight > 0 && $height > 0){
    //Calculations
    
    if($height_date > $weight_date){
    	$date_time = $height_date;
    }
    else{
    	$date_time = $weight_date;
    }
    
    $BMI = $weight/pow($height,2);
    
    $table = $user_obj->getBMI();
    $stmt = $con->prepare("INSERT INTO $table (`PHID`, `BMI`, `date_time`) VALUES ('',?,?)");
    $stmt->bind_param("ss",$BMI,$date_time);
    $stmt->execute();
}
?>