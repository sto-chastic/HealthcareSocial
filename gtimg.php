<?php

//Function for loading images.

require 'config/config.php';
include('includes/classes/User.php');
include_once('includes/classes/Crypt.php');

if( !preg_match("/^[a-zA-Z0-9]+$/", $_GET['img']) )
{
	header('HTTP/1.1 404 Not Found');
	exit();
}
else{
	$imgString_raw = $_GET['img'];
}

$table = "users";

if(isset($_GET['t'])){
	if( !preg_match("/^[a-zA-Z0-9]+$/", $_GET['t']) )
	{
		header('HTTP/1.1 404 Not Found');
		exit();
	}
	else{
		$t = $_GET['t'];
		
		if ($t == 1){
			$table = "temp_imgs";
		}
		else{
			header('HTTP/1.1 404 Not Found');
			exit();
		}
	}
}

$crypt = new Crypt();
$temp_user_e = $crypt->Decrypt(pack("H*",$imgString_raw),"--jasd>/|/e#rNImg674Cod3)%ap28?iC_!");

$stmt = $con->prepare("SELECT * FROM $table WHERE username=?");
$stmt->bind_param("s", $temp_user_e);
$stmt->execute();
$user_query = $stmt->get_result();

if (mysqli_num_rows($user_query) != 1)
{
	header('HTTP/1.1 404 Not Found');
	exit();
}
else{
	$arr = mysqli_fetch_array($user_query);
	$pp = $arr['profile_pic'];
}

$img = file_get_contents($pp);
header("Content-type: image/png");
echo($img);
exit();
?>