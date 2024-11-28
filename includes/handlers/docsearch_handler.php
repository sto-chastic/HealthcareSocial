<?php 

if(isset($_POST['go_search'])){
    include_once 'includes/classes/TxtReplace.php';
    include_once 'includes/classes/SearchNMap.php';
	$searchquery = $_POST['query']; //get the query
	$date =$_POST['date_query'];
 	$insurance = "CO00";
	$initialCity=NULL;
	$initialPos=array(4.6552961, -74.1086962);
	$radius=10; //in km!!
	
	//Create a new search object
	$search = new SearchNMap($con);
	//We create a TxtReplace object which we can prepare for search and look for it in the specializations database
	$searchQuery_obj = new TxtReplace();
	$searchQuery_obj = $searchQuery_obj->prepareForSearch($searchquery);
	$searchQuery_obj = "%".$searchQuery_obj."%";
	
	$_SESSION['insurance'] = $insurance;
	$_SESSION['initialCity'] = $initialCity;
	$_SESSION['initialCity'] = $initialCity;
	$_SESSION['searchQuery_obj']=$searchQuery_obj;
	$_SESSION['whatnow']=$_POST;
	$_SESSION['searchquery']= $searchquery;
	$_SESSION['date_q']=$date;
	header("Location: docsearch.php");
// 	$List = $search->genFilteredDocs($insurance,$searchQuery_obj,$initialCity,$initialPos,$radius);
	
}

?>