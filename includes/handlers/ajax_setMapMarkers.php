<?php
session_start();
//Initial query to select all
$srchRes = $_SESSION['markers'];
//$srchRes=[array('num'=>"1", 'firstNLast'=>"JM",'adln1'=> "hi",'adln2'=> "hey",'adln3'=> "ho",'adlat'=> 4.71,'adlng'=> -74.072)];
//Create parent XML document

$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);
header("Content-type: text/xml");

for($i=0;$i<sizeof($srchRes);$i++){
    
    $node = $dom->createElement("marker");
    $newnode = $parnode->appendChild($node);
    $newnode->setAttribute("pageid",$srchRes[$i]['num']);
    $newnode->setAttribute("names",$srchRes[$i]['firstNLast']);
    $newnode->setAttribute("aline1", $srchRes[$i]['adln1']);
    $newnode->setAttribute("aline2", $srchRes[$i]['adln2']);
    $newnode->setAttribute("aline3", $srchRes[$i]['adln3']);
    $newnode->setAttribute("lat", $srchRes[$i]['adlat']);
    $newnode->setAttribute("lng", $srchRes[$i]['adlng']);
    
}

echo $dom->saveXML();

?>