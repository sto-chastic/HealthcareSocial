<?php 
include("../../config/config.php");
include("../../includes/classes/TxtReplace.php");

$txtRep = new TxtReplace();
$query = $_POST['query'];
$query = $txtRep->ignoreLeftOfPeriod($query);
$query = $txtRep->prepareForSearchNoCommas($query);
$query2 = "%".$query."%";

$results_div_id= $txtRep->entities($_POST['results_div_id']);
$input_div_id= $txtRep->entities($_POST['input_div_id']);
$h_input_div_id="ds_city_code";

$lang = $_SESSION['lang'];

switch ($lang){
	
	case("en"):
		echo <<<EOS
            <div class="resultDisplayMainBar">
                <a href="javascript:void(0);" onclick="setUserSearchLocation()">
    							<div class='resultSympMedDisplay'>
    								+ Current Location
    							</div>
    						</a>
            </div>
EOS;
		break;
		
	case("es"):
		echo <<<EOS
            <div class="resultDisplayMainBar">
                <a href="javascript:void(0);" onclick="setUserSearchLocation()">
    							<div class='resultSympMedDisplay'>
    								+ Ubicación Actual
    							</div>
    						</a>
            </div>
EOS;
		break;
}

//echo $query;
//echo $insQuery->num_rows;
if($query != ""){
    $sql = "SELECT city_code, city FROM cities_CO WHERE (city_search LIKE ?) LIMIT 8";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("s", $query2);
    $stmt->execute();
    $cityQuery = $stmt->get_result();
    while($arr = mysqli_fetch_assoc($cityQuery)){
        $city_name=$arr['city'];
        $city_code=$arr['city_code'];
        echo <<<EOS
            <div class="resultDisplayMainBar">
                <a href="javascript:void(0);" onclick="selectSearchResultUnique('$city_name', '$results_div_id', '$input_div_id','$city_code','$h_input_div_id')">
    							<div class='resultSympMedDisplay'>
    								+ $city_name
    							</div>
    						</a>
            </div>
EOS;
    }
} else {
    $one=1;
    $sql = "SELECT city_code, city FROM cities_CO WHERE ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $one);
    $stmt->execute();
    $cityQuery = $stmt->get_result();
    while($arr = mysqli_fetch_assoc($cityQuery)){
        $city_name=$arr['city'];
        $city_code=$arr['city_code'];
        echo <<<EOS
            <div class="resultDisplayMainBar">
                <a href="javascript:void(0);" onclick="selectSearchResultUnique('$city_name', '$results_div_id', '$input_div_id','$city_code','$h_input_div_id')">
    							<div class='resultSympMedDisplay'>
    								+ $city_name
    							</div>
    						</a>
            </div>
EOS;
    }
}

?>

