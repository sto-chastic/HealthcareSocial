<?php 
include("Crypt.php");
include("TxtReplace.php");
$txtrep=new TxtReplace();

require("../../config/config.php");
$one=1;
$stmt = $con->prepare("SELECT email FROM users WHERE  ?");
$stmt->bind_param("s", $one);
$stmt->execute();
$e_check = $stmt->get_result();

$num_rows = mysqli_fetch_all($e_check);
print_r($num_rows);
echo "is the number of rows <br>";
foreach($num_rows as $i){
    echo $i;
}
// ob_start(); //Turns on output buffering
// session_start();

// $timezone = date_default_timezone_set("America/Bogota");

// $con = mysqli_connect("localhost", "root", "", "confidrV3"); // dollar sign creates variable in this case connection variable . "root" , "" are username and pass

// if(mysqli_connect_errno()) //if there is a connection error
// {
// 	echo "Failed to connect: " . mysqli_connect_errno(); // echo is the same as print. dot means add to string
// }
// mysqli_query($con,"SET NAMES utf8");
// mysqli_set_charset($con, 'UTF-8');
// mb_internal_encoding('UTF-8');

// include("TxtReplace.php");
// include("SearchNMap.php");
// /**
//  * 
//  * @var string $insurance
//  */
// $insurance = "CO00";
// $searchquery = "a";
// $initialCity="CO001";
// $initialPos="";//array(4.6552961, -74.1086962);

// $radius=30; //in km!!

// $search = new SearchNMap($con);

// // echo "The insurance we are searching for is this one: ".$insurance."<br>";
// //We create a TxtReplace object which we can prepare for search and look for it in the specializations database
// $searchQuery_obj = new TxtReplace();
// $searchQuery_obj = $searchQuery_obj->prepareForSearch($searchquery);
// $searchQuery_obj = "%".$searchQuery_obj."%";
// // echo "We are searching for the following in the specializations table: ". $searchquery_obj."<br>";

// $List = $search->genFilteredDocs("city", TRUE, "specializations LIKE '%\"2\"%'","CO00", "acupuncture","CO001", NULL, NULL);


// echo "<b>". "Location search type is ".$List[1]."<br>";
// echo "Specialty search type is ";
// echo    $List[2]?"TRUE":"FALSE";
// echo  "<br>";
// //generate the functions to establish distance by radius
//   echo "The size of filtered docs is ------> ". sizeof($List[0])."<br></b>";
// echo "<br><br><br>";
//  //Now we are going to rank the results
 
//  //echo "<br><br><br>";
//  //print_r($param);
//  //echo "<br>";
//  ////echo $_SERVER['HTTP_USER_AGENT'];
//  //echo "<br>";
//  //echo $_SERVER['SCRIPT_NAME'] . "<br>";
//  //print_r(array_merge_recursive(array_column($filteredDocs, 'username'), array_column($filteredDocs, 'office')));


// print_r($List[0]);
// if (!empty($List[0])){
//     $filteredDocs = $search->givePoints($List[0],$List[1], $radius);
//     $filteredDocs = $search->rankDocs($filteredDocs);
//     echo "<br><br><br>";
//     echo "The following is the id's found and the count for each: <br>";
//     print_r($search->docCount($filteredDocs));
//     echo "<br><br><br>";
//     echo "Your search parameters are <br>";
//     print_r($search->docCount($filteredDocs));
// }
$crypt = new Crypt;
$key = "samplekey";
$from = "1804171018167155110";
$to = "1804162201192909295";
$string =  "holahola";

echo $string . " is the message before encryption";
echo "<br>";

$encrypted = $crypt->Encrypt($string, $key);
echo $encrypted . " is the message after regular encryption";
echo "<br>";

$decrypted = $crypt->Decrypt($encrypted, $key);
echo $decrypted . " is the messageafter regular deencryption";
echo "<br>";

$encryptedM = $crypt->EncryptM($string, $from, $to, 1);
echo $encryptedM . " is the message after M encryption";
echo "<br>";

$decryptedM = $crypt->DecryptM($encryptedM, $from, $to, 1);
echo $decryptedM . " is the message text after deencryption";
echo "<br>";

$encryptedM2 = "cXdlcnR5dWlvcGFzZGZnaDtMP5v5QZONIf4=";
$decryptedM2 = $crypt->DecryptM($encryptedM2, $from, $to, 1);
echo $decryptedM2 . " is the SECOND message text after deencryption";
echo "<br>";
echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";

$profile_username = "515351483732505059656a7a315a7a4966776e54555865694b4477584c76734c77617631334b7a355a5a456c456e413d";
$profile_owner = pack("H*",$txtrep->entities($profile_username));
echo $profile_owner . "<br>";
echo "what is happening is <br>" . $crypt->Decrypt($profile_owner) ."<br>this.";

echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";
echo <<<EOS
<script type="text/javascript" src="https://secure.skypeassets.com/i/scom/js/skype-uri.js"></script>
<div id="SkypeButton_Call_davidrumsjb_1_1">
 <script type="text/javascript">
 Skype.ui({
 "name": "call",
 "element": "SkypeButton_Call_davidrumsjb_1_1",
 "participants": ["davidrumsjb_1"],
 "imageSize": 32
 });
 </script>
</div>
EOS;


echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";echo "<br>";
echo <<<EOS
<script type="text/javascript" src="https://secure.skypeassets.com/i/scom/js/skype-uri.js"></script>
<div id="SkypeButton_Call_davidrumsjb_1_1">
 <script type="text/javascript">
 Skype.ui({
 "name": "call",
 "element": "SkypeButton_Call_davidrumsjb_1_1",
 "participants": ["davidrumsjb_1"],
 "imageSize": 32
 });
 </script>
</div>
EOS;





?>
