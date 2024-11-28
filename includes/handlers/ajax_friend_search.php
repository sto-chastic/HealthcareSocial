<?php 
	include("../../config/config.php"); //allows connecton to the db
	include("../classes/User.php");
	include("../classes/TxtReplace.php");
	include("../classes/Message.php");
	
	$crypt = new Crypt();

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
			$userLoggedIn_e = $temp_user_e;
		}
		else{
			$userLoggedIn = "";
			session_start();
			session_destroy();
			header("Location: ../../register.php");
			$stmt->close();
		}
		
		$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
		$message = new Message($con, $userLoggedIn, $userLoggedIn_e);
		
		$lang = $_SESSION['lang'];
		
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}

	$query = strtolower($_POST['query']);

	if(trim($query) != ""){
	   
	    $names = explode(" ", $query); //query is what the user types in, it splits it at the spaces.
	    $connections_table = $user_obj->getConnections_tab();
	    $search_str = "SELECT username_friend FROM " . $connections_table . " WHERE ?";
	    $one = '1';
	    $stmt = $con->prepare($search_str);
	    $stmt->bind_param("s", $one);
	    $stmt->execute();
	    $users_returned = $stmt->get_result();
	    
	    if(mysqli_num_rows($users_returned)!=0){
	        $users_returned_arr_e = [];
	        while($uReturn = mysqli_fetch_row($users_returned)){
	            $users_returned_arr_e[] = "'" . $crypt->EncryptU($uReturn[0]) . "'";
	        }
	        $users_str2 = array_map(
	            function($n){
	                return 'username = ' . $n;
	            },
	            $users_returned_arr_e);
	        $users_str = implode(' OR ',$users_str2);
	        
	        $search_str = "SELECT username, profile_pic, first_name, last_name, user_type
						FROM users WHERE ".$users_str;
	    } else {
	        $search_str = "SELECT username, profile_pic, first_name, last_name, user_type
						FROM users WHERE user_type = '1' ";
	    }
	    
	    
	    $usersReturned2 = [];
	    
	    //IF there are two words, assume they are first and last names respectively
	    if(count($names) == 2){ //this means they are searching for a name and a last name
	        $stmt = $con->prepare($search_str . "AND (first_name_d LIKE ? AND last_name_d LIKE ?) AND user_closed = 'no' LIMIT 100");
	        
	        $stmt->bind_param("ss", $name1, $name2);
	        $name1 = '%' . $names[0] . '%';
	        $name2 = '%' . $names[1] . '%';
	        $stmt->execute();
	        $usersReturned = $stmt->get_result();
	        
	        if(mysqli_num_rows($usersReturned) <= 20){
	            $stmt = $con->prepare("SELECT * FROM users WHERE (first_name_d LIKE ? AND last_name_d LIKE ?) AND user_closed = 'no' AND user_type=1 LIMIT 100");
	            
	            $stmt->bind_param("ss", $name1, $name2);
	            $name1 = '%' . $names[0] . '%';
	            $name2 = '%' . $names[1] . '%';
	            $stmt->execute();
	            $usersReturned2 = $stmt->get_result();
	        }
	        
	    }
	    
	    else if(count($names) == 3){
	        
	        $stmt = $con->prepare("(". $search_str . "AND (first_name_d LIKE ? AND last_name_d LIKE ?) AND user_closed='no' AND username!=? LIMIT 6)
				UNION ALL
				(". $search_str . "AND (first_name_d LIKE ? AND last_name_d LIKE ?) AND user_closed='no' AND username!=? LIMIT 100)");
	        
	        $stmt->bind_param("ssssss", $name1, $name2, $userLoggedIn_e, $name3, $name4, $userLoggedIn_e);
	        $name1 = '%' . $names[0] . '%' . $names[1] . '%';
	        $name2 = '%' .$names[2] . '%';
	        $name3 =  '%' .$names[0] . '%';
	        $name4 = '%' .$names[1] . '%' . $names[2] . '%';
	        $stmt->execute();
	        $usersReturned = $stmt->get_result();
	        
	        if(mysqli_num_rows($usersReturned) <= 20){
	            $stmt = $con->prepare("(SELECT * FROM users WHERE (first_name_d LIKE ? AND last_name_d LIKE ?) AND user_closed='no' AND user_type=1 AND username!=? LIMIT 100)
				UNION ALL
				(SELECT * FROM users WHERE (first_name_d LIKE ? AND last_name_d LIKE ?) AND user_closed='no' AND user_type=1 AND username!=? LIMIT 100)");
	            
	            $stmt->bind_param("ssssss", $name1, $name2, $userLoggedIn_e, $name3, $name4, $userLoggedIn_e);
	            $name1 = '%' . $names[0] . '%' . $names[1] . '%';
	            $name2 = '%' .$names[2] . '%';
	            $name3 =  '%' .$names[0] . '%';
	            $name4 = '%' .$names[1] . '%' . $names[2] . '%';
	            $stmt->execute();
	            $usersReturned2 = $stmt->get_result();
	        }
	        
	    }
	    
	    //middle and last name
	    
	    else if(count($names) == 4){
	        
	        $stmt = $con->prepare($search_str . "AND (first_name_d LIKE ? AND last_name_d LIKE ?) AND user_closed='no' AND username!=? LIMIT 100");
	        
	        $stmt->bind_param("sss", $name1, $name2, $userLoggedIn_e);
	        $name1 = $names[0] . '%' . $names[1] . '%';
	        $name2 = $names[2] . '%' . $names[3] . '%';
	        $stmt->execute();
	        $usersReturned = $stmt->get_result();
	        
	        if(mysqli_num_rows($usersReturned) <= 20){
	            $stmt = $con->prepare("SELECT * FROM users WHERE (first_name_d LIKE ? AND last_name_d LIKE ?) AND user_closed='no' AND user_type=1 AND username!=? LIMIT 100");
	            
	            $stmt->bind_param("sss", $name1, $name2, $userLoggedIn_e);
	            $name1 = $names[0] . '%' . $names[1] . '%';
	            $name2 = $names[2] . '%' . $names[3] . '%';
	            $stmt->execute();
	            $usersReturned2 = $stmt->get_result();
	        }
	    }
	    
	    //IF query has one word only, search first names or last names.
	    else{
	        $query_string_full = $search_str . "AND (first_name_d LIKE ? OR last_name_d LIKE ?) AND user_closed='no' AND user_type=1 AND username!=? LIMIT 100";
	        //echo $query_string_full;
	        $stmt = $con->prepare($query_string_full);
	        $stmt->bind_param("sss", $name1, $name1, $userLoggedIn_e);
	        $name1 = '%' . $names[0] . '%';
	        $stmt->execute();
	        $usersReturned = $stmt->get_result();
	        
	        if(mysqli_num_rows($usersReturned) <= 20){
	            $stmt = $con->prepare("SELECT * FROM users WHERE (first_name_d LIKE ? OR last_name_d LIKE ? ) AND user_closed='no' AND user_type=1 AND username!=? LIMIT 100");
	            
	            $stmt->bind_param("sss", $name1, $name1, $userLoggedIn_e);
	            $name1 = '%' . $names[0] . '%';
	            $stmt->execute();
	            $usersReturned2 = $stmt->get_result();
	        }
	    }
	


		$user = new User($con, $userLoggedIn, $userLoggedIn_e);
		$txt_rep = new TxtReplace();
		
		$shown_users = [];
		while($arr =  mysqli_fetch_array($usersReturned)){
		    $arr['username_decrypted'] = $crypt->Decrypt($arr['username']);
			if(array_key_exists($arr['username_decrypted'], $shown_users)){
				continue;
			}
			$_temp_user_obj = new User($con, $arr['username_decrypted'], $arr['username']);
			if($arr['user_type'] == 1){
			    
				$_temp_specialization = $_temp_user_obj->getSpecializationsText($lang);
				$_temp_name = "<b>Dr.</b> " . $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
			}
			else{
				switch ($lang){
					
					case("en"):
						$_temp_specialization = "Patient";
						break;
						
					case("es"):
						$_temp_specialization = "Paciente";
						break;
				}
				
				$_temp_name = $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
			}
			
			$shown_users[$arr['username_decrypted']] = true;
			if($arr['username_decrypted'] != $userLoggedIn){
			    switch($lang){
			        case("en"): $mutual_friends = $user->getMutualFriends($arr['username_decrypted']) . " connections in common";
			                    break;         
			        case("es"): $mutual_friends = $user->getMutualFriends($arr['username_decrypted']) . " conexiones en común";
			                     break;
			    }
				
			}
			else{
				$mutual_friends = "";
			}

			echo "<a href='messages_frame.php?u=" . bin2hex($txt_rep->entities($arr['username'])) . "'>
					<div class='resultDisplayMessages'>
						<div class='liveSearchProfilePic'>
							<img src='" . $txt_rep->entities($_temp_user_obj->getProfilePicFast()) . "'>
						</div>

						<div class='liveSearchText'>
							" . $_temp_name . "
							<p style='margin-top:5px;'>" .  $txt_rep->entities($_temp_specialization) . "</p>
							<h4 id='grey'>" . $txt_rep->entities($mutual_friends) . "</h4>
						</div>
					</div>
				</a>";
			
		}
		while($arr2 =  mysqli_fetch_array($usersReturned2)){
		    $arr2['username_decrypted'] = $crypt->Decrypt($arr2['username']);
			if(array_key_exists($arr2['username_decrypted'], $shown_users)){
				continue;
			}
			
			if($arr2['user_type'] == 1){
				$_temp_user_obj = new User($con, $arr2['username_decrypted'], $arr2['username']);
				$_temp_specialization = $_temp_user_obj->getSpecializationsText($lang);
				$_temp_name = "<b>Dr.</b> " . $txt_rep->entities($arr2['first_name']) . " " . $txt_rep->entities($arr2['last_name']);
			}
			else{
				switch ($lang){
					
					case("en"):
						$_temp_specialization = "Patient";
						break;
						
					case("es"):
						$_temp_specialization = "Paciente";
						break;
				}
				$_temp_name = "<b>Dr.</b> " . $txt_rep->entities($arr2['first_name']) . " " . $txt_rep->entities($arr2['last_name']);
			}
			
			$shown_users[$arr2['username_decrypted']] = true;
			if($arr2['username_decrypted'] != $userLoggedIn){
				switch ($lang){
					
					case("en"):
						$mutual_friends = $user->getMutualFriends($arr2['username_decrypted']) . " connections in common";
						break;
						
					case("es"):
						$mutual_friends = $user->getMutualFriends($arr2['username_decrypted']) . " connections in common";
						break;
				}
				
			}
			else{
				$mutual_friends = "";
			}
			
			echo "<a href='messages_frame.php?u=" . bin2hex($txt_rep->entities($arr2['username'])) . "'>
					<div class='resultDisplayMessages'>
						<div class='liveSearchProfilePic'>
							<img src='" . $txt_rep->entities($_temp_user_obj->getProfilePicFast()) . "'>
						</div>
									
						<div class='liveSearchText'>
							" . $_temp_name . "
							<p style='margin-top:5px;'>" .  $txt_rep->entities($_temp_specialization) . "</p>
							<h4 id='grey'>" . $txt_rep->entities($mutual_friends) . "</h4>
						</div>
					</div>
				</a>";
			
		}
	}
?>

