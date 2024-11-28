<?php 
require 'config/config.php';
include('includes/classes/User.php');
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
</head>
<body>

	<style type="text/css">
		* {
			font-size: 12px;
			font-family: Arial, Helvetica, Sans-serif;
			margin:0px;
			overflow:hidden;
		}
		
		h1{
			font-family: Coves-Bold;
			color: rgb(241, 118, 155);
			font-size: 24px;
			line-height: 24px;
			color: #f38ead;
			font-style: normal;
			text-align: center;
			margin-top: 30px;
		}
		p{
			font-family: Coves-light;
			font-size: 18px;
			padding-top: 6px;
			padding-bottom: 10px;
			color: #7dd2f5;
			text-align: center;
		}
		
		div{
			font-family: Coves-Bold;
			color: white;
			width: 40%;
			font-size: 14px;
			text-transform: uppercase;
			margin: 0;
			padding: 7px;
			text-align: center;
			border: 2px solid #f38ead;
			background-color: #f1769b;
			border-radius: 30px;
			margin-bottom: 4px;
			position: relative;
			left: 50%;
			-webkit-transform: translate(-50%, 0);
			-moz-transform: translate(-50%, 0);
			-o-transform: translate(-50%, 0);
			-ms-transform: translate(-50%, 0);
			transform: translate(-50%, 0);
		}
		
		a{
			text-decoration: none;
		}
	</style>
	
	<?php
	
	if(isset($_GET['t'])){
		switch ($_GET['t']){
			case "messages":
				$type = "messages";
				break;
			case "comments":
				$type = "comments";
				break;
			default:
				$type = "comments";
		}
	}
	else{
		$type = "comments";
	}
	
	$lang = $_SESSION['lang'];
	
	switch ($type){
		case ("comments"):{
			switch ($lang){
				case("en"):
					echo "<h1> To view comments, please sign in, it's free! </h1>";
					echo "<p>Get the full ConfiDr. experience, you are only a couple minutes away.</p>";
					echo "<a href='register.php' target='_parent'><div>Register Here</div></a>";
					break;
				case("es"):
					echo "<h1> Para ver los comentarios, registrate en ConfiDr., ¡es gratis! </h1>";
					echo "<p>Obten la experiencia completa ConfiDr., estás sólo a un par de minutos de distancia.</p>";
					echo "<a href='register.php' target='_parent'><div>Registrate Aquí</div></a>";
					break;
			}
			break;
		}
		case ("messages"):{
			switch ($lang){
				case("en"):
					echo "<h1> To message this doctor, please sign in, it's free! </h1>";
					echo "<p>Get the full ConfiDr. experience, you are only a couple minutes away.</p>";
					echo "<a href='register.php' target='_parent'><div>Register Here</div></a>";
					break;
				case("es"):
					echo "<h1> Para mandarle un mensaje al doctor, registrate en ConfiDr., ¡es gratis! </h1>";
					echo "<p>Obten la experiencia completa ConfiDr., estás sólo a un par de minutos de distancia.</p>";
					echo "<a href='register.php' target='_parent'><div>Registrate Aquí</div></a>";
					break;
			}
			break;
		}
	}
	
	

	?>
</body>
</html>