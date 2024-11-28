<?php 
include("../../config/config.php");
include("../../includes/classes/TxtReplace.php");

$txtRep = new TxtReplace();
$query = $_POST['query'];
$query = $txtRep->ignoreLeftOfPeriod($query);
$query = $txtRep->prepareForSearchNoCommas($query);
$query2 = "%".$query."%";

$lang = $_SESSION['lang'];

$results_div_id= $txtRep->entities($_POST['results_div_id']);
$input_div_id= $txtRep->entities($_POST['input_div_id']);
$h_input_div_id="ds_ins_code";



//echo $query;
//echo $insQuery->num_rows;
if($query != ""){
    $sql = "SELECT id, en, es FROM insurance_CO WHERE (search_en LIKE ?) OR (search_es LIKE ?) LIMIT 8";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("ss", $query2, $query2);
    $stmt->execute();
    $insQuery = $stmt->get_result();
    while($arr = mysqli_fetch_assoc($insQuery)){
        $ins_name=$arr[$lang];
        $ins_code=$arr['id'];
        echo <<<EOS
            <div class="resultDisplayMainBar">
                <a href="javascript:void(0);" onclick="selectSearchResultUnique('$ins_name', '$results_div_id', '$input_div_id','$ins_code','$h_input_div_id')">
    							<div class='resultSympMedDisplay'>
    								+ $ins_name
    							</div>
    						</a>
            </div>
EOS;
    }
} else {
    $one=1;
    $sql = "SELECT id, en, es FROM insurance_CO WHERE ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $one);
    $stmt->execute();
    $insQuery = $stmt->get_result();
    while($arr = mysqli_fetch_assoc($insQuery)){
        $ins_name=$arr[$lang];
        $ins_code=$arr['id'];
        echo <<<EOS
            <div class="resultDisplayMainBar">
                <a href="javascript:void(0);" onclick ="selectSearchResultUnique('$ins_name', '$results_div_id', '$input_div_id','$ins_code','$h_input_div_id')">
    							<div class='resultSympMedDisplay'>
    								+ $ins_name
    							</div>
    						</a>
            </div>
EOS;
    }
}

?>

