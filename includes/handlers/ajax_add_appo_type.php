<?php 
require '../../config/config.php';
include('../classes/User.php');
include('../classes/Calendar.php');
include_once('../classes/TxtReplace.php');

$userLoggedIn = "";
if(isset($_SESSION['username']) && isset($_SESSION['messages_token'])){
    $temp_user = $_SESSION['username'];
    $temp_user_e = $_SESSION['username_e'];
    //$temp_passwrd = $_SESSION['passwrd'];
    $temp_messages_token= $_SESSION['messages_token'];
    
    $stmt = $con->prepare("SELECT * FROM users WHERE username=? AND messages_token=?");
    
    $stmt->bind_param("ss", $temp_user_e, $temp_messages_token);
    $stmt->execute();
    $verification_query = $stmt->get_result();
    
    if(mysqli_num_rows($verification_query) == 1){
        $userLoggedIn = $temp_user;
        $userLoggedIn_e = $temp_user_e;//Retrieves username
        $user = mysqli_fetch_array($verification_query);
        $stmt->close();
        
        $lang = $_SESSION['lang'];
        
    }
    else{
        $is1 = $_SESSION['username'];
        $is3 = mysqli_num_rows($verification_query);
        $userLoggedIn = "";
        session_start();
        session_destroy();
        header("Location: register.php");
    }
    
}
else{
    $is1 = $_SESSION['username'];
    $userLoggedIn = "";
    session_start();
    session_destroy();
    header("Location: register.php");
}

if(isset($_POST['appo_desc']) && isset($_POST['appo_duration']) && isset($_POST['cost_input'])){
    $txtrep = new TxtReplace();
    $description = htmlspecialchars($_POST['appo_desc']);
    $description = strip_tags($description);
    $description = $txtrep->entities($description);
    $description = preg_replace('/_/', ' ', $description);
    
    $duration = htmlspecialchars($_POST['appo_duration']);
    $duration = strip_tags($duration);
    $duration = $txtrep->entities($duration);
    
    $cost = htmlspecialchars($_POST['cost_input']);
    $cost= strip_tags($cost);
    $cost= $txtrep->entities($cost);
    
    $calendar = new Calendar($con, $userLoggedIn, $userLoggedIn_e);
    
    $message = $calendar->setAppoDurationSettings($description,$duration,$cost);
    if($message==""){
        $html_to_echo = $calendar->getAppoDurationSettings();
        echo $html_to_echo;
    } else {
        echo $message;
    }
    
}
?>