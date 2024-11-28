<?php 

	include("includes/header.php");
	if(isset($_GET['q'])){
		$query_temp = $_GET['q'];
		$query_temp = strip_tags($query_temp);
		$query = $txt_rep->entities($query_temp);
		$lang = $_SESSION['lang'];
	}
	else
		$query = "";

	$type = "name";

?>

<div class="main_column column" id="main_column">

<div class="title_tabs" ><?php switch($lang){
		    case("en"):
		        echo "Connections Search";
		        break;
		    case("es");
                echo "Busqueda de Conexiones";
                break;
		}?></div>
		
	<div class= search_div>	
	    	<?php 
	    	
	    	$query = strtolower($query);
    		if(trim($query)== ""){
    			switch ($lang){
    				
    				case("en"):
    					echo "No searchable criteria. Please type in some text and try again.";
    					break;
    					
    				case("es"):
    					echo "No hay información para buscar. Inserte algún texto e intente de nuevo.";
    					break;
    			}
    			
    		}
		
			
		else{
    		    if(trim($query) != ""){		
                $names = explode(" ", $query); 
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
						FROM users WHERE '1' AND user_type = '1'";
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
        		        $query_string_full = $search_str . "AND (first_name_d LIKE ? OR last_name_d LIKE ?) AND user_closed='no' AND username!=? LIMIT 100";
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
        				//Iteration
        				$shown_users = [];
        				
        				while($arr = mysqli_fetch_array($usersReturned)){
        					$arr['username_decrypted'] =  $crypt->Decrypt($arr['username']);
        					if(array_key_exists($arr['username_decrypted'], $shown_users)){
        						continue;
        					}
        					$shown_users[$arr['username_decrypted']] = true;
        					
        					$button = "";
        					$mutual_connections = "";
        					
        					if($arr['user_type'] == 1){
        						$_user_type_bool = TRUE;
        					}
        					else{
        						$_user_type_bool = FALSE;
        					}
        
        					//Button forms functionality
        					if(isset($_POST["remove_connection_" . $txt_rep->entities($arr['username_decrypted'])])){
        						$user_obj->removeFriend($arr['username_decrypted']);
        						header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        					}
        
        					if(isset($_POST["request_connection_" . $txt_rep->entities($arr['username_decrypted'])]) && $_user_type_bool){
        						$user_obj->sendRequest($arr['username_decrypted']);
        						header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        					}
        
        					if(isset($_POST["respond_request_" . $txt_rep->entities($arr['username_decrypted'])])){
        						$user_respond = $txt_rep->entities($arr['username_decrypted']);
        						$add_connection_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$user_respond,') WHERE username='$userLoggedIn'");
        						$add_connection_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$user_respond'");
        
        						$delete_query = mysqli_query($con, "DELETE FROM $connection_requests WHERE user_to='$userLoggedIn' AND user_from = '$user_respond'");
        						header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        					}
        
        					if(isset($_POST["request_sent_" . $txt_rep->entities($arr['username_decrypted'])])){
        						
        					}
        
        					//Generate button depending on friendship status
        
        					if($user_obj->isFriend($arr['username_decrypted'])){
        					    switch($lang){
        					        case("en"):
        					            $button = '<input type="submit" name="remove_connection_' . $txt_rep->entities($arr['username_decrypted']) . '" class="danger" value="Remove Connection"><br>';
        					            break;
        					        case("es"):
        					            $button = '<input type="submit" name="remove_connection_' . $txt_rep->entities($arr['username_decrypted']) . '" class="danger" value="Remover Conexión"><br>';
        					            break;
        					    }
         					}
         					else if($user_obj->didReceiveRequest($arr['username_decrypted'])){
         					    switch($lang){
         					        case("en"):
         					            $button = '<input type="submit" name="respond_request_' . $txt_rep->entities($arr['username_decrypted']) . '" class="warning" value="Respond to Request"><br>';
         					            break;
         					        case("es"):
         					            $button = '<input type="submit" name="respond_request_' . $txt_rep->entities($arr['username_decrypted']) . '" class="warning" value="Responder Solicitud"><br>';
         					            break;
         					    }
         					}
         					else if($user_obj->didSendRequest($arr['username_decrypted'])){
         					    switch($lang){
         					        case("en"):
         					            $button = '<input type="submit" name="request_sent_' . $txt_rep->entities($arr['username_decrypted']) . '" class="default" value="Request Sent"><br>';
         					            break;
         					        case("es"):
         					            $button = '<input type="submit" name="request_sent_' . $txt_rep->entities($arr['username_decrypted']) . '" class="default" value="Solicitud Enviada"><br>';
         					            break;
         					    }
         					}
         					elseif($_user_type_bool){
         					    switch($lang){
         					        case("en"):
         					            $button = '<input type="submit" name="request_connection_' . $txt_rep->entities($arr['username_decrypted']) . '" class="success" value="Add Connection"><br>';
         					            break;
         					        case("es"):
         					            $button = '<input type="submit" name="request_connection_' . $txt_rep->entities($arr['username_decrypted']) . '" class="success" value="Agregar Conexión"><br>';
         					            break;
         					    }
         					}
         					$_temp_user_obj = new User($con, $arr['username_decrypted'], $arr['username']);

         					switch($lang){
         					    case("en"): {
         					        if($arr['user_type'] == 1){
         					            $_temp_specialization = $_temp_user_obj->getSpecializationsText('en');
         					            $_temp_name = "Dr."." ". $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
         					        }
         					        else{
         					            $_temp_specialization = "Patient";
         					            $_temp_name = $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
         					        }
         					        break;
         					    }
         					    case("es"): {
         					        if($arr['user_type'] == 1){
         					            $_temp_specialization = $_temp_user_obj->getSpecializationsText('es');
         					            $_temp_name = "Dr."." ". $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
         					        }
         					        else{
         					            $_temp_specialization = "Paciente";
         					            $_temp_name = $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
         					        }
         					        break;
         					    }

         					}
         					
         					$mutual_friends = $user_obj->getMutualFriends($arr['username_decrypted']);
         					if($mutual_friends > 1 || $mutual_friends == 0){
         					    if($lang=="en"){
         					        $mutual_friends = $txt_rep->entities($mutual_friends)." " . " connections in common.";
         					    } else if ($lang=="es") {
         					        $mutual_friends = $txt_rep->entities($mutual_friends)." " . " conexiones en común.";
         					    }
         					}
         					
         					else {
         					    if($lang=="en"){
         					        $mutual_friends = $txt_rep->entities($mutual_friends)." " . " connection in common.";
         					    } else if ($lang=="es") {
         					        $mutual_friends = $txt_rep->entities($mutual_friends)." " . " conexión en común.";
         					    }
         					}		
         							
         						    echo "<div class='search_results_search'>
        							<hr>
        							<div class='searchPageFriendButtons'>
        								<form action = '' method='POST'>
        									" . $button . "
        									<br>
        								</form>
        							</div>
         						        
        							<div class='result_profile_pic'>
        								<a href='" . bin2hex($txt_rep->entities($arr['username'])) . "'>
        									<img src='" . $txt_rep->entities($_temp_user_obj->getProfilePicFast()) . "'>
        								</a>
        							</div>
                                 <div class='result_name_and_info'>
        								<a href='" . bin2hex($txt_rep->entities($arr['username'])) . "'>
        									" . $_temp_name . "
        									<p style='margin:0;'>" .  $txt_rep->entities($_temp_specialization) . "</p>
                                        <b>"
        								    . $txt_rep->entities($mutual_friends) ."
        								</b></a>
                                  </div>
        						      <br>
        						</div>
        					";

        				}
        				
        				while($arr = mysqli_fetch_array($usersReturned2)){
        				    $arr['username_decrypted'] =  $crypt->Decrypt($arr['username']);
        					if(array_key_exists($arr['username_decrypted'], $shown_users)){
        						continue;
        					}
        					
        					$shown_users[$arr['username_decrypted']] = true;
        					
        					$button = "";
        					$mutual_connections = "";
        					
        					if($arr['user_type'] == 1){
        						$_user_type_bool = TRUE;
        					}
        					else{
        						$_user_type_bool = FALSE;
        					}
        					
        					//Button forms functionality
        					if(isset($_POST["remove_connection_" . $txt_rep->entities($arr['username_decrypted'])])){
        						$user_obj->removeFriend($arr['username_decrypted']);
        						header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        					}
        					
        					if(isset($_POST["request_connection_" . $txt_rep->entities($arr['username_decrypted'])]) && $_user_type_bool){
        						$user_obj->sendRequest($arr['username_decrypted']);
        						header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        					}
        					
        					if(isset($_POST["respond_request_" . $txt_rep->entities($arr['username_decrypted'])])){
        						$user_respond = $txt_rep->entities($arr['username_decrypted']);
        						$add_connection_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$user_respond,') WHERE username='$userLoggedIn'");
        						$add_connection_query = mysqli_query($con, "UPDATE users SET friend_array=CONCAT(friend_array, '$userLoggedIn,') WHERE username='$user_respond'");
        						
        						$delete_query = mysqli_query($con, "DELETE FROM $connection_requests WHERE user_to='$userLoggedIn' AND user_from = '$user_respond'");
        						header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        					}
        					
        					if(isset($_POST["request_sent_" . $txt_rep->entities($arr['username_decrypted'])])){
        						
        					}
        					
        					//Generate button depending on friendship status
        					if($user_obj->isFriend($arr['username_decrypted'])){
        					    switch($lang){
        					        case("en"):
        					            $button = '<input type="submit" name="remove_connection_' . $txt_rep->entities($arr['username_decrypted']) . '" class="danger" value="Remove Connection"><br>';
        					            break;
        					        case("es"):
        					            $button = '<input type="submit" name="remove_connection_' . $txt_rep->entities($arr['username_decrypted']) . '" class="danger" value="Remover Conexión"><br>';
        					            break;
        					    }
        					}
        					else if($user_obj->didReceiveRequest($arr['username_decrypted'])){
        					    switch($lang){
        					        case("en"):
        					            $button = '<input type="submit" name="respond_request_' . $txt_rep->entities($arr['username_decrypted']) . '" class="warning" value="Respond to Request"><br>';
        					            break;
        					        case("es"):
        					            $button = '<input type="submit" name="respond_request_' . $txt_rep->entities($arr['username_decrypted']) . '" class="warning" value="Responder Solicitud"><br>';
        					            break;
        					    }
        					}
        					else if($user_obj->didSendRequest($arr['username_decrypted'])){
        					    switch($lang){
        					        case("en"):
        					            $button = '<input type="submit" name="request_sent_' . $txt_rep->entities($arr['username_decrypted']) . '" class="default" value="Request Sent"><br>';
        					            break;
        					        case("es"):
        					            $button = '<input type="submit" name="request_sent_' . $txt_rep->entities($arr['username_decrypted']) . '" class="default" value="Solicitud Enviada"><br>';
        					            break;
        					    }
        					}
        					elseif($_user_type_bool){
        					    switch($lang){
        					        case("en"):
        					            $button = '<input type="submit" name="request_connection_' . $txt_rep->entities($arr['username_decrypted']) . '" class="success" value="Add Connection"><br>';
        					            break;
        					        case("es"):
        					            $button = '<input type="submit" name="request_connection_' . $txt_rep->entities($arr['username_decrypted']) . '" class="success" value="Agregar Conexión"><br>';
        					            break;
        					    }
        					}
        					switch($lang){
        					    case("en"): {
        					        if($arr['user_type'] == 1){
        					            $_temp_user_obj = new User($con, $arr['username_decrypted'], $arr['username']);
        					            $_temp_specialization = $_temp_user_obj->getSpecializationsText('en');
        					            $_temp_name = "Dr." ." ". $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
        					        }
        					        else{    
        					            $_temp_specialization = "Patient";
        					            $_temp_name = $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
        					        }
        					        break;
        					    }
        					    case("es"): {
        					        if($arr['user_type'] == 1){
        					            $_temp_specialization = $_temp_user_obj->getSpecializationsText('es');
        					            $_temp_name = "Dr."." ". $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
        					        }
        					        else{
        					            $_temp_specialization = "Paciente";
        					            $_temp_name = $txt_rep->entities($arr['first_name']) . " " . $txt_rep->entities($arr['last_name']);
        					        }
        					        break;
        					    }
        					}
        					
        					$mutual_friends = $user_obj->getMutualFriends($arr['username_decrypted']);
        					if($mutual_friends > 1 || $mutual_friends == 0){
        					    if($lang=="en"){
        					        $mutual_friends = $txt_rep->entities($mutual_friends)." " . " connections in common.";
        					    } else if ($lang=="es") {
        					        $mutual_friends = $txt_rep->entities($mutual_friends)." " . " conexiones en común.";
        					    }
        					}
        						
        					else {
        					    if($lang=="en"){
        					        $mutual_friends = $txt_rep->entities($mutual_friends)." " . " connection in common.";
        					    } else if ($lang=="es") {
        					        $mutual_friends = $txt_rep->entities($mutual_friends)." " . " conexión en común.";
        					    }
        					}
        						
        							
        					echo "<div class='search_results_search'>
        							<hr>
        							<div class='searchPageFriendButtons'>
        								<form action = '' method='POST'>
        									" . $button . "
        									<br>
        								</form>
        							</div>
        								
        							<div class='result_profile_pic'>
        								<a href='" . bin2hex($txt_rep->entities($arr['username'])) . "'>
        									<img src='" . $txt_rep->entities($_temp_user_obj->getProfilePicFast()) . "'>
        								</a>
        							</div>
                                 <div class='result_name_and_info'>
        								<a href='" . bin2hex($txt_rep->entities($arr['username'])) . "'>
        									" . $_temp_name . "
        									<p style='margin:0;'>" .  $txt_rep->entities($_temp_specialization) . "</p>
                                        <b>"
        									. $txt_rep->entities($mutual_friends) ."
        								</b></a>
                                  </div>
        						      <br>
        						</div>
        					";

        				}
    		    }
		
	?>
	<div class="results_found_searchphp">
		<?php
		switch($lang){
		    case("en"): { 
                        if(sizeof($shown_users) == 0)
                            echo "No users found with a name like:" . $query;
                        else
                            echo sizeof($shown_users) . " results found: <br>";
                        break;
		          }
		    case("es"): {
		        if(sizeof($shown_users) == 0)
		            echo "No se encontraron usuarios con un nombre como:" . $query;
		            else
		                echo sizeof($shown_users) . " resultados encontrados: <br>";
		                break;
		    }
		}
			
		?>
	</div>
	<?php 
	}
	?>
	
	</div>

</div>
</body>