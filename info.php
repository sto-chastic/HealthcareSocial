<?php
	//require 'config/config.php';
	include('includes/header.php');
	/*
	include('includes/classes/Post.php');
	include('includes/classes/User.php');
	include('includes/classes/ICS.php');
	include('includes/classes/Calendar.php');*/

 	

	if(isset($_POST['add_to_cal'])){

		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: attachment; filename=invite.ics');

		$ics = new ICS(array(
		  'location' => $_POST['location'],
		  'description' => $_POST['description'],
		  'dtstart' => $_POST['date_start'],
		  'dtend' => $_POST['date_end'],
		  'summary' => $_POST['summary'],
		  'url' => $_POST['url'],
		  'uid' => $_POST['uid']
		));

		echo $ics->to_string();
	}//2017-5-2 11:00AM


	$confirm_appointment_salt = "/|/e#rNap28?iC_!";
	$username_e = $user_obj->username_e;
	
	$username_e_e = $crypt->encryptString($username_e,"/|/e#rNap28?iC_!");
	echo $username_e;
	echo "<br>";
	echo $username_e_e;
	echo "<br>";
	echo $crypt->decryptString($username_e_e,"/|/e#rNap28?iC_!");
	echo "<br>";
	
	$username_e_e = $crypt->Encrypt($username_e,"/|/e#rNap28?iC_!");
	echo $username_e;
	echo "<br>";
	echo $username_e_e = bin2hex($username_e_e);
	echo "<br>";
	echo $crypt->Decrypt(pack("H*",$username_e_e),"/|/e#rNap28?iC_!");
	echo "<br>";
	
	
// $specialization = $user_obj->getSpecializationsText('en');
// echo $specialization; /opt/bitnami/apache2/htdocs
// 	echo $_SERVER['DOCUMENT_ROOT'];
// 	$pth_str = explode("/",$_SERVER['DOCUMENT_ROOT']);
	
// 	$path = "";
// 	for($i=0;$i<= count($pth_str)-2 ;$i++){
// 		$path .= $pth_str[$i] . "/";
// 	}
// 	echo $path;
?>

<form method="post" action="info.php">
  <input type="date" name="date_start" placeholder="2017-1-16 9:00AM">
  <input type="date" name="date_end" placeholder="2017-1-16 10:00AM">
  <input type="text" name="location" placeholder="123 Fake St, New York, NY">
  <input type="text" name="description" placeholder="This is my description">
  <input type="text" name="summary" placeholder="This is my summary">
  <input type="text" name="url" placeholder="http://example.com">
  <input type="text" name="uid" placeholder="http://example.com">
  <input type="submit" value="Add to Calendar" name="add_to_cal">
</form>

