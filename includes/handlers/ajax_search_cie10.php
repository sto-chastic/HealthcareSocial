<?php
include("../../config/config.php");
include("../../includes/classes/User.php");
include("../classes/TxtReplace.php");

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
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}

$query = rtrim(ltrim(strtolower($_POST['query'])));
$userLoggedIn = $_POST['usr'];
$diagnosis_num = $_POST['diagnosis_num'];
$aid = $_POST['aid'];
$user_obj = new User($con,$userLoggedIn, $userLoggedIn_e);
$country = $user_obj->getCountry_Doctor();

// $sql = "SELECT `cie_code`,`desc` FROM cie_10_" . $country . " WHERE (`cie_code` LIKE ?) LIMIT 6";;
// $stmt = $con->prepare($sql);
// $stmt->bind_param("s", $query);
// $stmt->execute();
// $code_search_q = $stmt->get_result();

if((strpos($query, '%') === FALSE)){
	$search_terms = explode(" ", $query);
	$sql = "SELECT `cie_code`,`desc` FROM cie_10_" . $country . " WHERE (`desc_search` LIKE ? OR `cie_code` LIKE ?) LIMIT 6";
	$stmt = $con->prepare($sql);
	$stmt->bind_param("ss", $term, $poss_code);
	$or_query = $_POST['query'];
	$poss_code = ucfirst($or_query) . '%';
	
	$term = '%';
	for($i=0;$i<count($search_terms);$i++){
		$term .= $search_terms[$i] . '%';
	}
}
else{
	$sql = "SELECT `cie_code`,`desc` FROM cie_10_" . $country . " WHERE (`desc_search` LIKE ? OR `cie_code` LIKE ?) LIMIT 6";
	$stmt = $con->prepare($sql);
	
	$stmt->bind_param("ss", $term, $poss_code);
	$or_query = $_POST['query'];
	$poss_code = ucfirst($or_query) . '%';
	
	$term = '%' . $query . '%';
}

$stmt->execute();
$result_q = $stmt->get_result();
$stmt->close();

if($query != ""){
	
	while($arr = mysqli_fetch_assoc($result_q)){
		$cie_code = $arr['cie_code'];
		$description = $arr['desc'];
		echo <<<EOS
        		<a href="javascript:void(0);" onclick="selectSearchCIE('$cie_code', '$userLoggedIn', '$aid', '$diagnosis_num')">
        			<div class='resultSympMedDisplay'>
        				$description
        			</div>
        		</a>
EOS;
	}
}
?>