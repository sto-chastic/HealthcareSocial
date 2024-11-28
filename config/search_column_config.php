<?php
include("config.php");
include("../includes/classes/TxtReplace.php");

//generate searchables in specializations
$stmt = $con->prepare("SELECT * FROM specializations");
$stmt->execute();
$query = $stmt->get_result();

$txtRep = new TxtReplace();
while($row=mysqli_fetch_array($query)){
    
    $engstring=$row['en'];
    $eng = $txtRep->prepareForSearch($engstring);
    
    $spastring=$row['es'];
    $span = $txtRep->prepareForSearch($spastring);
    
    $stmt = $con->prepare("UPDATE specializations SET en_search=? WHERE id=?");
    $stmt->bind_param("ss",$eng,$row['id']);
    $stmt->execute();
    
    $stmt = $con->prepare("UPDATE specializations SET es_search=? WHERE id=?");
    $stmt->bind_param("ss",$span,$row['id']);
    $stmt->execute();
}

//generate searchables in cities
$stmt = $con->prepare("SELECT * FROM cities_CO");
$stmt->execute();
$query = $stmt->get_result();


while($row=mysqli_fetch_array($query)){
    
    $city_accents=$row['city'];
    $city_searchable = $txtRep->prepareForSearchNoCommas($city_accents);
    $city_code=$row['city_code'];
    echo $city_searchable;
    echo $row['city_code'];
    $stmt = $con->prepare("UPDATE cities_CO SET city_search=? WHERE city_code=?");
    $stmt->bind_param("ss",$city_searchable,$city_code);
    $stmt->execute();
}

echo "executed the search columns. search your databases to make sure everything is all right.";
?>