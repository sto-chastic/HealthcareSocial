<?php 

	include("../../config/config.php");
	include("../../includes/classes/User.php");
	include("../classes/TxtReplace.php");
	
	$txtrep = new TxtReplace();
	
	$query = $txtrep->entities($_POST['query']);
	$results_div_id= $txtrep->entities($_POST['results_div_id']);
	$input_div_id= $txtrep->entities($_POST['input_div_id']);
	$search_array = explode(",", $query);

	$end = end($search_array);
	$element = rtrim(ltrim(strtolower($end)));

//Uncomment this for uppercase search
	//$element = rtrim(ltrim(ucwords(strtolower($end))));

	$prev_elements = "";
	unset($search_array[count($search_array) - 1]);
	//$search_array = array_map('strtolower',$search_array);
	//$search_array = array_map('ucwords',$search_array);


	if(count($search_array) > 0){
		//$prev_elements_arr = ucwords(strtolower($rest));
		$prev_elements = implode(",", $search_array);		
	}

	$table = 'insurance_CO';
	$lang = $_POST['lang_col'];
	$search_col_t= $_POST['search_col'];
	
	switch($lang){
		case ('en'):
			$table_element= 'en';
			break;
		case ('es'):
			$table_element= 'es';
			break;
	}
	
	switch($search_col_t){
		case ('search_en'):
			$search_col= 'search_en';
			break;
		case ('search_es'):
			$search_col= 'search_es';
			break;
	}

	if($element != ""){

		$stmt = $con->prepare("SELECT DISTINCT $table_element FROM $table WHERE $search_col LIKE ? LIMIT 6");

		$stmt->bind_param("s", $search_term);
		$search_term = '%' . $element . '%';
		$stmt->execute();
		$resultsReturned = $stmt->get_result();

		while($arr = mysqli_fetch_array($resultsReturned)){
			$i_title = $arr[$table_element];

			$trimmed_search_array=array_map('ltrim',$search_array);
			$trimmed_search_array=array_map('rtrim',$trimmed_search_array);

			if(!in_array($i_title, $trimmed_search_array)){

				echo <<<EOS
						<a href="javascript:void(0);" onclick="selectSearchResultUniversal('$i_title', '$prev_elements', '$results_div_id', '$input_div_id')">
							<div class='resultSympMedDisplay'>
								+ $i_title
							</div>
						</a>
EOS;
			}
		}

	}
?>