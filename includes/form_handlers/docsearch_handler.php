<?php 

if(isset($_POST['go_search'])){
    include_once 'includes/classes/TxtReplace.php';
    include_once 'includes/classes/SearchNMap.php';
    //Create a new search and text object, set error trigger to false, make sure session variables are reset before assigning them
    $SnM = new SearchNMap($con);
    $txt = new TxtReplace();
    $error = FALSE;

    $_SESSION['queryL']= "";
    $_SESSION['d8']="";
    $_SESSION['locList'] = "";
    $_SESSION['i_name'] = "";
    $_SESSION['i_code']= "";
    $_SESSION['error']="";
    $_SESSION['searchparams']="";
    $_SESSION['List'] = "";
    $_SESSION['doc_count']="";
    $_SESSION['docsToDisplay']="";
    
    
    //Language
    
    $lang = $_SESSION['lang'];
    
    switch ($lang){
    	
    	case("en"):
    		$insu_search_lang = "search_en";
    		break;
    		
    	case("es"):
    		$insu_search_lang = "search_es";
    		break;
    }
    
    
	$searchquery = $_POST['query']; 
	$date =$_POST['date_query'];
	$city_name = $_POST['search_location_name'];
	$city_code = $_POST['ds_city_code'];
	$coords = array_filter($_POST['pos']+[0,0]);
	$ins_name = $_POST['searched_insurance1'];
	$ins_code = $_POST['ds_ins_code'];
	$rad = $_POST['radius'];
	
	//Security checks
	//We create a TxtReplace object which we can prepare for search and look for it in the specializations database
	$query = mb_substr($searchquery, 0, 128);
	$query = $txt->prepareForSearch($query);
	$queryList = $SnM->searchQueryType($query);
	$_SESSION['queryL']= $queryList;
	
	
	//Date security checks
	if(is_string($date)){
        if(strlen($date)>10){
	        $error = TRUE;
	    } else if(substr_count($date,"-")!=2){
	            $error=TRUE;
	        }
        else {
            $_SESSION['d8']=$date;
        }
	} else {
	    $error = TRUE;
	}
	
	
	//Location security checks
	$city_name = $txt->prepareForSearchNoCommas($city_name);
	$city_code = $txt->prepareCodeForSearch($city_code);
	
    if(is_string($city_name) && is_string($city_code)) {
        if (strlen($city_name) <30 && strlen($city_code)<6) {
            if($city_code=="" && !$coords){
                $cityToLookFor = "%".$city_name."%";
                $stmt = $con->prepare("SELECT city_code, city FROM cities_CO WHERE (city_search LIKE ?) LIMIT 1");
                $stmt->bind_param("s", $cityToLookFor);
                $stmt->execute();
                $cityQuery = $stmt->get_result();
                if(mysqli_num_rows($cityQuery)>0) {
                    while($arr = mysqli_fetch_assoc($cityQuery) ){
                        $city_name=$arr['city'];
                        $city_code=$arr['city_code'];
                    }
                    $coords = FALSE;
                } else {
                    $error=FALSE;
                }
            }
        } else {
            $error = TRUE;
        }
    } else {
        $error = TRUE;
    }
    $locationList = ["","","","",""];
    if(!$error) {
        $locationList = $SnM->locationSearchType($city_name,$city_code,$coords, $rad+0);
        if($locationList[0]=="ERROR") {
            $error = TRUE;
        }
    }
    $_SESSION['locList'] = $locationList;
    //Insurance security checks
    $ins_name = $txt->prepareForSearchExceptSpaces($ins_name);
    $ins_code = $txt->prepareCodeForSearch($ins_code);
    
    if(is_string($ins_name) && is_string($ins_code)) {
        if (strlen($ins_name) <30 && strlen($ins_code)<5) {
            if($ins_code==""){
                $insToLookFor = "%".$ins_name."%";
                $stmt = $con->prepare("SELECT id, $insu_search_lang FROM insurance_CO WHERE ($insu_search_lang LIKE ?) LIMIT 1");
                $stmt->bind_param("s", $insToLookFor);
                $stmt->execute();
                $insQuery = $stmt->get_result();
                if(mysqli_num_rows($insQuery)>0) {
                    while($arr = mysqli_fetch_assoc($insQuery) ){
                    		$ins_name=$arr[$insu_search_lang];
                        	$ins_code=$arr['id'];
                    }
                } else {
                    $error=TRUE;
                }
            }
            
            if($ins_code == "CO00"){
           	 	$ins_code = "all";
            }
            $_SESSION['i_name'] = $ins_name;
            $_SESSION['i_code']= $ins_code;
        } else {
            $error = TRUE;
        }
    } else {
        $error = TRUE;
    }

    
    $_SESSION['searchparams']=[$locationList[0],$queryList[0],$queryList[1],$ins_code,$queryList[2],$locationList[2],$locationList[3],$locationList[4]];
	$_SESSION['List'] = $List;
	$_SESSION['error']=$error;
	header("Location: docsearch.php");
}

?>