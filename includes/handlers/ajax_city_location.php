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
$office = $txtRep->entities($_POST['officeNumber']);

if($office == 1){
    $dept_div_id= "state_office_1";
    $h_input_div_id="cityCode_office_1";
}
elseif($office == 2){
    $dept_div_id= "state_office_2";
    $h_input_div_id="cityCode_office_2";
}
elseif($office == 3){
    $dept_div_id= "state_office_3";
    $h_input_div_id="cityCode_office_3";
}


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
        <div class="cityResultBox">
            <a href="javascript:void(0);" onclick="selectCityAndDepartment('$city_name', '$results_div_id', '$input_div_id','$city_code','$h_input_div_id', '$dept_div_id', '$adm2')">
							<div class='premiumCityResult'>
                            $city_name
							</div>
            </a>
        </div>
EOS;
    }


?>

