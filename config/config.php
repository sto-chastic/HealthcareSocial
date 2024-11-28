<?php

// Testing Setup

ob_start();//Turns on output buffering
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$timezone = date_default_timezone_set("America/Bogota");
$database_name='confidrV5';
$con = mysqli_connect("localhost","root","",$database_name); //connection variable
if(mysqli_connect_errno()){
	echo "Failed to connect:" . mysqli_connect_errno(); // dot is add to string echo is print on screen.
}
mysqli_query($con,"SET NAMES utf8");
mysqli_set_charset($con, 'UTF-8');
mb_internal_encoding('UTF-8');




// Deployment Setup AWS

// ob_start();//Turns on output buffering
// if (session_status() == PHP_SESSION_NONE) {
// 	session_start();
// }
// $timezone = date_default_timezone_set("America/Bogota");
// $database_name='confidrV5';
// $db_username= 'basic_users';
// $db_passwrd = '<-></|/euro#Naptic-20!7Bas!c_Usr<-';
// //$con = mysqli_connect(host,username,password,dbname,port,socket);
// $con = mysqli_connect("localhost",$db_username,$db_passwrd,$database_name); //connection variable
// if(mysqli_connect_errno()){
// 	echo "Failed to connect:" . mysqli_connect_errno(); // dot is add to string echo is print on screen.
// }
// mysqli_query($con,"SET NAMES utf8");
// mysqli_set_charset($con, 'UTF-8');
// mb_internal_encoding('UTF-8');
// error_reporting(0);
// ini_set('display_errors', 0);




// Deployment Setup OLD

// ob_start();//Turns on output buffering
// if (session_status() == PHP_SESSION_NONE) {
// 	session_start();
// }
// $timezone = date_default_timezone_set("America/Bogota");
// $database_name='db732511229';
// $con = mysqli_connect("db732511229.db.1and1.com","dbo732511229","<-></\|eUro#/\|aptiC-2017!412394%",$database_name,"3306"); //connection variable
// if(mysqli_connect_errno()){
// 	echo "Failed to connect:" . mysqli_connect_errno(); // dot is add to string echo is print on screen.
// }
// mysqli_query($con,"SET NAMES utf8");
// mysqli_set_charset($con, 'UTF-8');
// mb_internal_encoding('UTF-8');

?>