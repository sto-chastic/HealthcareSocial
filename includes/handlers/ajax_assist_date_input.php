<?php

$input = $_REQUEST['input'];
$return_str = $input;

$input_array = explode('/',$input);

if(sizeof($input_array) == 1){
	if($return_str > 31){
		$return_str = '31';
	}
	if(strlen($input_array[0]) == 2){
		$return_str = $return_str . "/";
	}
	elseif($input_array[0] > 3){
		$return_str = $return_str . "/";
	}
}
elseif(sizeof($input_array) == 2){
	if($input_array[1] > 12){
		$return_str = $input_array[0] . "/" . "12";
	}
	if(strlen($input_array[1]) == 2){
		$return_str = $return_str . "/";
	}
	elseif($input_array[1] > 1){
		$return_str = $return_str . "/";
	}
}
elseif(sizeof($input_array) == 3){
	$date_time = new DateTime();
	$year = $date_time->format("y");
	if($input_array[2] > $year){
		$return_str = $input_array[0] . "/" . $input_array[1] . "/" . $year;
	}
}

if(strlen($return_str) > 8){
	$return_str = substr($return_str, 0, 8);
}

if(sizeof($input_array) == 3 && strlen($input_array[2]) == 2){
	$date_time = new DateTime();
	$present_day = $date_time->format('d');
	$present_month = $date_time->format('m');
	$present_year = $date_time->format('y');
	
	if($present_year.$present_month.$present_day < $input_array[2].$input_array[1].$input_array[0]){
		$return_str = $date_time->format('d/m/y');
	}
}

echo $return_str;

?>