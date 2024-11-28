<?php

$input = $_REQUEST['input'];
$return_str = $input;

$input_array = explode(':',$input);

if(sizeof($input_array) == 1){
	if($return_str > 12){
		$return_str = '12';
	}
	
	if($return_str > 1){
		$return_str = $return_str . ":";
	}
	elseif(strlen($return_str) == 2){
		$return_str = $return_str . ":";
	}
	
	if(strlen($return_str) > 2){
		$return_str = substr($return_str, 0, 2);
	}
	
}
elseif(sizeof($input_array) == 2){
	if($input_array[1] > 59){
		$return_str = $input_array[0] . ":" . "59";
	}
	
	if(strlen($input_array[1]) == 3){
		$a_or_p = substr($input_array[1], 2, 3);
		$non_a_or_p = substr($input_array[1], 0, 2);
		if($a_or_p == 'p'){
			$extra = $non_a_or_p . "pm";
		}
		else{
			$extra = $non_a_or_p . "am";
		}
		
		if(strlen($extra) > 4){
			$return_str = substr($extra, 0, 4);
		}
		
		$return_str = $input_array[0] . ":" . $extra;
	}

}

$max_val = strlen($input_array[0]) + 5;

if(strlen($return_str) > $max_val){
	$return_str = substr($return_str, 0, $max_val);
}

echo $return_str;

?>