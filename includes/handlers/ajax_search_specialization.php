<?php 

	include("../../config/config.php");
	include("../../includes/classes/User.php");
	include("../classes/TxtReplace.php");
	
	$txtrep = new TxtReplace();
	
	$query = $txtrep->entities($_POST['query']);
	$prev_ids = $txtrep->entities($_POST['prev_ids']);
	
	$results_div_id= $txtrep->entities($_POST['results_div_id']);
	$input_div_id= $txtrep->entities($_POST['input_div_id']);
	$hidden_inp= $txtrep->entities($_POST['hidden_inp']);
	$search_array = explode(",", $query);
	
	$end = end($search_array);
	$element = rtrim(ltrim(strtolower($end)));
	
	$prev_elements = "";
	unset($search_array[count($search_array) - 1]);
	//$search_array = array_map('strtolower',$search_array);
	//$search_array = array_map('ucwords',$search_array);
	
	
	if(count($search_array) > 0){
		//$prev_elements_arr = ucwords(strtolower($rest));
		$prev_elements = implode(",", $search_array);
	}
	
	$table = 'specializations';
	$lang = $_POST['lang_col'];
	if(isset($_SESSION['lang'])){
		$lang = $_SESSION['lang'];
	}
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
		case ('en_search'):
			$search_col= 'en_search';
			break;
		case ('es_search'):
			$search_col= 'es_search';
			break;
	}

	if($element != ""){

		$stmt = $con->prepare("SELECT $table_element,id FROM $table WHERE $search_col LIKE ? LIMIT 6");

		$stmt->bind_param("s", $search_term);
		$search_term = '%' . $element . '%';
		$stmt->execute();
		$resultsReturned = $stmt->get_result();

		while($arr = mysqli_fetch_array($resultsReturned)){
			$i_title = $arr[$table_element];
			
			if (strpos($i_title, '\\') !== false) {
				$str = substr($i_title, 0, strpos($i_title, '\\'));
			}
			else{
				$str = $i_title;
			}
			$id = $arr['id'];
			
			echo <<<EOS
						<a href="javascript:void(0);" onclick="selectSearchResultToHidInp('$str', '$id', '$prev_elements', '$prev_ids', '$results_div_id', '$input_div_id', '$hidden_inp')">
							<div class='resultSympMedDisplay'>
								+ $str
							</div>
						</a>
EOS;
			
// 			echo <<<EOS
// 					<a href="javascript:void(0);" onclick="selectSearchResultToHidInp('$str', '$id', '$results_div_id', '$input_div_id', '$hidden_inp')">
// 						<div class='resultSympMedDisplay'>
// 							$str
// 						</div>
// 					</a>
// EOS;
		}
	}
?>