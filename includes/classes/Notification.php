<?php  
    include_once("Crypt.php");
    
	/*This class is for each conversation*/
	class Notification{
		private $user_obj;
		private $con;
		private $my_notifications_tab;

		public function __construct($con, $user, $user_e){
			$this->con = $con; //this->con means you are referencing the con from THIS class
			$this->user_obj = new User($con, $user, $user_e);		
			$temp_user_obj = new User($con, $user, $user_e);
			$this->my_notifications_tab = $temp_user_obj->getNotificationsTable();
		}

		public function getNotifications($data, $limit){
			$lang = $_SESSION['lang']; 
			
			$my_notifications_tab = $this->my_notifications_tab;
			$page = $data['page'];
			$txt_rep = new TxtReplace();

			$userLoggedIn =$this->user_obj->getUsername();
			$return_str = "";

			if($page == 1)
				$start = 0;
			else
				$start = ($page - 1) * $limit;

			$set_viewed_query = mysqli_query($this->con, "UPDATE $my_notifications_tab SET viewed = 'yes' WHERE user_to = '$userLoggedIn'");

			$query = mysqli_query($this->con,"SELECT * FROM $my_notifications_tab WHERE user_to='$userLoggedIn' ORDER BY datetime DESC");

			if(mysqli_num_rows($query) == 0){
				switch ($lang){
					
					case("en"):
						echo "<h4> You have no notifications </h4>";
						break;
						
					case("es"):
						echo "<h4> No tienes notificaciones </h4>";
						break;
				}
				
				return;
			}

			$num_iter = 0; //number of messages checked
			$count = 1; //number of messages posted

			while($arr = mysqli_fetch_array($query)){

				if($num_iter++ < $start)
					continue;

				if($count > $limit) //this is equivalent to the if for num_iter.
					break;
				else
					$count++;

				$user_from = $arr['user_from'];
                $crypt = new Crypt();
                $user_from_e = $crypt->EncryptU($user_from);
				$temp_notification_user = new User($this->con, $user_from, $user_from_e);

				//Create timeframe

				$time_frame = New TimeStamp();

				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($arr['datetime']); //Time of post
				$end_date = new DateTime($date_time_now); //Current time
				$interval = $start_date->diff($end_date); //Difference between dates

				$time_message = $time_frame->getTimeStamp($interval);


				//check if open

				$profile_pic = $temp_notification_user->getProfilePicFast();


				$opened = $arr['opened'];
				$style = ($arr['opened'] == 'no') ? "background-color: #DDEDFF;" : "";

				$return_str .= "<a href='" . $arr['link'] . "'> 
									<div class='resultDisplay resultDisplayNotification' style = '" . $style . "'>
										<div class='notificationsProfilePic'>
											<img src='" . $txt_rep->entities($profile_pic) ."'>
										</div>
										<p class='timestamp_smaller' id='not_time'>" . $txt_rep->entities($time_message) . "</p>" . $txt_rep->entities($arr['message']) . "
									</div>
								</a>";
			}
			//If posts were loaded
			if($count > $limit){
				$return_str .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'>
								<input type='hidden' class='noMoreDropdownData' value = 'false'>";/*
								<p>page:" . ($page + 1) . ", count:" . $count . ", numit: ". $num_iter . " false </p>";*/
			}
			else{
				switch ($lang){
					
					case("en"):
						$return_str .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align:center;padding-top: 5px;'> No more notifications.</p>";
						break;
						
					case("es"):
						$return_str .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align:center;padding-top: 5px;'> No más notificaciones.</p>";
						break;
				}
			}

			return $return_str;
		}
	

		public function getUnreadNumber(){
			$userLoggedIn =  $this->user_obj->getUsername();
			$my_notifications_tab = $this->my_notifications_tab;
			$query = mysqli_query($this->con, "SELECT * FROM $my_notifications_tab WHERE viewed='no' AND user_to='$userLoggedIn'");
			return mysqli_num_rows($query);
		}


		public function insertNotification($post_id, $user_to, $type){
			
			$userLoggedIn =  $this->user_obj->getUsername();
			$crypt =  new Crypt();
			$user_to_e= $crypt->EncryptU($user_to);
			$user_to_obj = new User($this->con, $user_to, $user_to_e);
			include_once("Settings.php");
			$user_to_settings = new Settings($this->con, $user_to, $user_to_e);
			$user_to_lang = $user_to_settings->getLang();
			$user_to_notifications_tab = $user_to_obj->getNotificationsTable();
			$userLoggedIn_name = $this->user_obj->getFirstAndLastNameFast();
			$date_time = date("Y-m-d H:i:s");
			
			switch ($user_to_lang){
				
				case("en"):
					switch($type){ // if the variable type matches the case, it executes that line of code
						case 'comment':
							$message = $userLoggedIn_name . " commented on your post.";
							break;
						case 'like':
							$message = $userLoggedIn_name . " liked your post.";
							break;
						case 'profile_post':
							$message = $userLoggedIn_name . " posted on your profile.";
							break;
						case 'comment_non_owner':
							$message = $userLoggedIn_name . " commented on a post you commented.";
							break;
						case 'profile_comment':
							$message = $userLoggedIn_name . " commented on your profile post.";
							break;
					}
					break;
					
				case("es"):
					switch($type){ // if the variable type matches the case, it executes that line of code
						case 'comment':
							$message = $userLoggedIn_name . " comentó en tu publicación.";
							break;
						case 'like':
							$message = "A " . $userLoggedIn_name . " le gustó tu publicación.";
							break;
						case 'profile_post':
							$message = $userLoggedIn_name . " publicó en tu perfil.";
							break;
						case 'comment_non_owner':
							$message = $userLoggedIn_name . " comentó en una publicación que tu comentaste.";
							break;
						case 'profile_comment':
							$message = $userLoggedIn_name . " comentó una publicación en tu perfil.";
							break;
					}
					break;
			}

			$link = "post.php?id=" . $post_id;

			$stmt = $this->con->prepare("INSERT INTO $user_to_notifications_tab VALUES('', ?, ?, ?, ?, ?, 'no', 'no')");
		
			
			$stmt->bind_param("sssss", $user_to, $userLoggedIn, $message, $link, $date_time);
			$stmt->execute();

		}
	}
?>