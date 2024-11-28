<?php

include("../../config/config.php");
include("../../includes/classes/User.php");
include("../classes/TxtReplace.php");

$query = $_POST['query'];
$type = $_POST['type'];//table name
$lang = $_POST['lang'];
if(isset($_SESSION['lang'])){
	$lang = $_SESSION['lang'];
}

$search_term = rtrim(ltrim(strtolower($query)));


if(strpos($type, "2") !== false && $search_term != ""){
	$full_type = $type;
	$specs_arr = explode("2",$type);
	$type = $specs_arr[0];
	$second_column = $specs_arr[1];
	
	switch ($lang){
		case "en":
			$title = $lang;
			$stmt = $con->prepare("SELECT $second_column,en,id FROM $type WHERE $lang LIKE ? ORDER BY percent DESC,en LIMIT 4");
			break;
		case "es":
			$title = $lang;
			$stmt = $con->prepare("SELECT $second_column,es,id FROM $type WHERE $lang LIKE ? ORDER BY percent DESC,en LIMIT 4");
			break;
	}
	$stmt->bind_param("s", $search_term);
	
	$stmt->execute();
	$full_text_check_q = $stmt->get_result();
	
	if(mysqli_num_rows($full_text_check_q) > 0){
		while($arr = mysqli_fetch_array($full_text_check_q)){
			$i_title = ucwords($arr[$title]);
			
			$i_id = $arr['id'];
			$displ = $i_title . " <b>" . $arr[$second_column] . "</b>";
			echo <<<EOS
			<a href="javascript:void(0);" onclick="selectSearchResultHealth('$arr[$second_column]', '$i_id', '$full_type','$second_column')">
				<div class='resultHealthDisplay'>
					$displ
				</div>
			</a>
EOS;
		}
	}
	else{
		
		switch ($lang){
			case "en":
				$title = $lang;
				$stmt = $con->prepare("SELECT distinct en FROM $type WHERE $lang LIKE ? ORDER BY percent DESC,en LIMIT 4");
				break;
			case "es":
				$title = $lang;
				$stmt = $con->prepare("SELECT distinct es FROM $type WHERE $lang LIKE ? ORDER BY percent DESC,en LIMIT 4");
				break;
		}
		$stmt->bind_param("s", $search_term);
		$search_term = '%' . $search_term . '%';
		$stmt->execute();
		$resultsReturned = $stmt->get_result();
		
		while($arr = mysqli_fetch_array($resultsReturned)){
			$i_title = ucwords($arr[$title]);
			//$i_id = $arr['id'];
			//$debug = mysqli_query($con, "INSERT INTO debug VALUES('','$i_title')");
			echo <<<EOS
			<a href="javascript:void(0);" onclick="selectPreSearchResultHealth('$i_title','$full_type','$lang')">
					<div class='resultHealthDisplay'>
						$i_title
					</div>
				</a>
EOS;
		}
	}
}
elseif($search_term != ""){
	switch ($lang){
		case "en":
			$title = $lang;
			$stmt = $con->prepare("SELECT en,id FROM $type WHERE $lang LIKE ? ORDER BY percent DESC,en LIMIT 4");
			break;
		case "es":
			$title = $lang;
			$stmt = $con->prepare("SELECT es,id FROM $type WHERE $lang LIKE ? ORDER BY percent DESC,en LIMIT 4");
			break;
	}
	$stmt->bind_param("s", $search_term);
	$search_term = '%' . $search_term . '%';
	$stmt->execute();
	$resultsReturned = $stmt->get_result();
	
	while($arr = mysqli_fetch_array($resultsReturned)){
		$i_title = ucwords($arr[$title]);
		$i_id = $arr['id'];
		
		echo <<<EOS
			<a href="javascript:void(0);" onclick="selectSearchResultHealth('$i_title', '$i_id', '$type')">
				<div class='resultHealthDisplay'>
					$i_title
				</div>
			</a>
EOS;
	}
}

?>