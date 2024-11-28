<?php 
	include("../../config/config.php");
	include("../classes/TxtReplace.php");
	
	$txt_rep = new TxtReplace();

	$query = $_POST['query'];

	$cleantxt = strip_tags($query);
	$cleantxt = $txt_rep->entities($cleantxt);
	echo $cleantxt;
?>