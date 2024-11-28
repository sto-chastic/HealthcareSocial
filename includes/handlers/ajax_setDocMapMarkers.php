<?php
session_start();

$docMapInfo = $_SESSION['docMarkers'];
//$docMapInfo=[array('num'=>"1", 'firstNLast'=>"JM",'adln1'=> "hi",'adln2'=> "hey",'adln3'=> "ho",'adlat'=> 4.71,'adlng'=> -74.072)];
//Create parent XML document

$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);
header("Content-type: text/xml");

for($i=0;$i<sizeof($docMapInfo);$i++){
    $node = $dom->createElement("marker");
    $newnode = $parnode->appendChild($node);
    $newnode->setAttribute("officeNick",$docMapInfo[$i]['nick']);
    $newnode->setAttribute("officeid",$docMapInfo[$i]['num']);
    $newnode->setAttribute("names",$docMapInfo[$i]['firstNLast']);
    $newnode->setAttribute("aline1", $docMapInfo[$i]['adln1']);
    $newnode->setAttribute("aline2", $docMapInfo[$i]['adln2']);
    $newnode->setAttribute("aline3", $docMapInfo[$i]['adln3']);
    $newnode->setAttribute("lat", $docMapInfo[$i]['adlat']);
    $newnode->setAttribute("lng", $docMapInfo[$i]['adlng']);    
}

echo $dom->saveXML();

?>