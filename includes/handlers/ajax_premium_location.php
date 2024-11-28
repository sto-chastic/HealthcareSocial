<?php 
include("../../config/config.php");
include("../../includes/classes/TxtReplace.php");

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

$txtRep = new TxtReplace();
$query = $_POST['query'];
$query = $txtRep->ignoreLeftOfPeriod($query);
$query = $txtRep->prepareForSearchNoCommas($query);
$query2 = "%".$query."%";

$results_div_id= $txtRep->entities($_POST['results_div_id']);
$input_div_id= $txtRep->entities($_POST['input_div_id']);
$dept_div_id= "prem_ad1adm2";
$h_input_div_id="prem_ad1cityCode";

//echo $insQuery->num_rows;
if($query != ""){
    $sql = "SELECT city_code, city, adm2 FROM cities_CO WHERE (city_search LIKE ?) LIMIT 8";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $query2);
} else {
    $one=1;
    $sql = "SELECT city_code, city, adm2 FROM cities_CO WHERE ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $one);
}
$stmt->execute();
$cityQuery = $stmt->get_result();
while($arr = mysqli_fetch_assoc($cityQuery)){
    $city_name=$arr['city'];
    $city_code=$arr['city_code'];
    $adm2 = $arr['adm2'];
    echo <<<EOS
        <div class="premiumCityResultBox">
            <a href="javascript:void(0);" onclick="selectCityAndDepartment('$city_name', '$results_div_id', '$input_div_id','$city_code','$h_input_div_id', '$dept_div_id', '$adm2')">
							<div class='premiumCityResult'>
                            $city_name
							</div>
            </a>
        </div>
EOS;
    }


?>

