<?php  
	/*This class is for each conversation*/
    include_once("Crypt.php");
    
	class Message{
		private $user_obj;
		private $con;
		private $my_messages_tab;
		private $crypt;

		public function __construct($con, $user, $user_e){
		    
			try{
			    $this->crypt = new Crypt();
				$this->con = $con;
				$this->user_obj = $temp_user_obj = new User($con, $user, $user_e);
				$this->my_messages_tab = $temp_user_obj->getMessagesTable();
			}	
			catch ( Exception $e ){
				$this->con = "";
				$this->$user_obj = "";
				$this->my_messages_tab = "";
				throw new Exception( $e->getMessage() );
			}
		}

		public function getMostRecentUser(){
			$userLoggedIn = $this->user_obj->getUsername();
			$my_messages_tab = $this->my_messages_tab;

			$stmt = $this->con->prepare("SELECT user_to, user_from FROM $my_messages_tab WHERE user_to=? OR user_from=? ORDER BY date DESC LIMIT 1");
			
			$stmt->bind_param("ss", $userLoggedIn, $userLoggedIn);
			$stmt->execute();
			$result = $stmt->get_result();

			//$query = mysqli_query($this->con, "SELECT user_to, user_from FROM $my_messages_tab WHERE user_to='$userLoggedIn' OR user_from='$userLoggedIn' ORDER BY date DESC LIMIT 1"); //only returns 1 result

			if(mysqli_num_rows($result) == 0)
				return false;
			
			//$arr = mysqli_fetch_array($query);
			$arr = mysqli_fetch_array($result);
			$user_to = $arr['user_to'];
			$user_from = $arr['user_from'];

			$stmt->close();
			if($user_to	!= $userLoggedIn)
				return $user_to;
			else
				return $user_from;
		}

		public function sendMessage($user_to,$body,$date){
		    $user_to_e = $this->crypt->EncryptU($user_to);
			$user_to_obj = new User($this->con, $user_to, $user_to_e);
			$user_to_messages_tab = $user_to_obj->getMessagesTable();
			$my_messages_tab = $this->my_messages_tab;

			//DONE ALREADY
			//$body = mysql_real_escape_string($body);//escape sql. //NOT USED
			
// 			OLD WORKING, HAS TO BE CHANGED
// 			$body=preg_replace("/(\\\\r\\\\n)+/","\\\\r\\\\n",$body);//parentheses and plus allow the pattern to be found as many times as it repeats, and be replaced once

// 			$empt = preg_replace("/(\\\\r\\\\n)+/","",$body);
// 			$empt=preg_replace("/[\s,]+/","",$empt);
			
			$body=preg_replace("/(\\\\n)+/","\\\\n",$body);//parentheses and plus allow the pattern to be found as many times as it repeats, and be replaced once
			$body=preg_replace('/\b1b66f76804fd4e8fe3b601040f1aec83alertpayment2017\b/u','',$body);
			$userLoggedIn =$this->user_obj->getUsername();
			$creation = $this->user_obj->user_info['signup_date'];
			$body_e = $this->crypt->EncryptM($body,$userLoggedIn,$user_to,$creation);
			$empt = preg_replace("/(\\\\n)+/","",$body);
			$empt=preg_replace("/[\s,]+/","",$empt);

			if($body != "" && $empt != ""){

				$stmt = $this->con->prepare("INSERT INTO $my_messages_tab VALUES('', ?, ?, ?, ?, 'no', 'no', 'no')");
				$stmt->bind_param("ssss", $user_to, $userLoggedIn, $body_e, $date);
				$stmt->execute();

				$stmt = $this->con->prepare("INSERT INTO $user_to_messages_tab VALUES('', ?, ?, ?, ?, 'no', 'no', 'no')");
				$stmt->bind_param("ssss", $user_to, $userLoggedIn, $body_e, $date);
				$stmt->execute();

				$stmt->close();

			}
		}
		
		public function getMessagesTable(){
			return $this->my_messages_tab;
		}

		public function getMessages($otherUser, $latest_loaded_message_id=0){
			$crypt = new Crypt();
			$lang = $_SESSION['lang'];
			$messages_to_load = 20;
			$messages_to_load_query = $messages_to_load+1;//so that the display of messages is bounded by the PHP, except if there are no more messages at all, then the query bounds it
			
			$userLoggedIn =$this->user_obj->getUsername();
			$my_messages_tab = $this->my_messages_tab;

			$data = "";
			
			$stmt = $this->con->prepare("UPDATE $my_messages_tab SET opened='yes',viewed = 'yes' WHERE user_to=? OR user_from = ?");
			$stmt->bind_param("ss", $otherUser, $otherUser);

			$stmt->execute();

			if($latest_loaded_message_id == 0){
				$query_str = "SELECT * FROM $my_messages_tab WHERE (user_to=? || user_from = ?) ORDER BY date DESC LIMIT $messages_to_load_query";
				$stmt = $this->con->prepare($query_str);
				$stmt->bind_param("ss", $otherUser, $otherUser);
			}
			else{
				$query_str = "SELECT * FROM $my_messages_tab WHERE ((user_to=? || user_from = ?) AND id<?) ORDER BY date DESC LIMIT $messages_to_load_query";
				$stmt = $this->con->prepare($query_str);
				$stmt->bind_param("ssi", $otherUser, $otherUser, $latest_loaded_message_id);
			}

			$stmt->execute();
			$get_messages_query = $stmt->get_result();
			$messages_count = 0;
			$ending = 0;
			
			$arr_ordered = [];
			$i = 0;
			while($arr = mysqli_fetch_array($get_messages_query)){
				//print_r($arr);
				$arr_ordered[$i] = $arr;
				$i++;
			}
			//print_r($arr_ordered);
			for($j=$i-1;$j>=0;$j--){
				$val = $arr_ordered[$j];
				
				if($j==$i-1){
					$latest_loaded_message_id = $val['id'];
				}
				$user_to = $val['user_to'];
				$user_from = $val['user_from'];
				$body = $val['body'];
				$_date = $val['date'];

				$txt_rep = new TxtReplace();
				$user_from_obj = new User($this->con, $user_from, $this->crypt->EncryptU($user_from));
				$creation = $user_from_obj->user_info['signup_date'];
				$body = $this->crypt->DecryptM($body, $user_from, $user_to, $creation);
				$body = $txt_rep->replace($body);
				$body = $txt_rep->entities($body);
				$body = $txt_rep->replaceLineBreak($body);
				
				if($body!= "1b66f76804fd4e8fe3b601040f1aec83alertpayment2017"){
						$div_top = ($user_to == $userLoggedIn) ? "<div class='message' id = 'green'>" : "<div class='message' id='blue'>"; //? end of condition, : else.
						$data = $data . $div_top . $body . "</div><br><br>";
				}
				else{
					//Means the usert_to is the doctor
					$user_to_e = $crypt->EncryptU($user_to);
					$doctor_user_obj = new User($this->con, $user_to, $user_to_e);
					$doctor_messages_status_table = $doctor_user_obj->getMessagesStatusTable();
					
					$stmt = $this->con->prepare("SELECT `accepted_payment`,`message_desc`,`date_payed` FROM $doctor_messages_status_table WHERE `secondary_interlocutor`=?");
					$stmt->bind_param("s", $user_from);
					$stmt->execute();
					
					$acc_payment = $stmt->get_result();
					$acc_payment_val = mysqli_fetch_assoc($acc_payment);
					
					$_num_days_before = 2;
					$_num_days_before_str= 'P' . $_num_days_before. 'D';
					$_two_days_ago = new DateTime();
					$_two_days_ago_obj = $_two_days_ago->sub(new DateInterval($_num_days_before_str));
					$_two_days_ago_formated = $_two_days_ago_obj->format("Y-m-d H:i:s");
					
					$_message_dt_obj = DateTime::createFromFormat('Y-m-d H:i:s', $_date);
					$two_days_ago_datetime = DateTime::createFromFormat('Y-m-d H:i:s',$_two_days_ago_formated);
					
					$res = $two_days_ago_datetime->diff($_message_dt_obj);
					
					//print_r($res);
					
					//echo $res->format('%R%a days');
					
					if($acc_payment_val['accepted_payment']== "0" && $res->format('%R%a days') >= 0){
						
						$message_desc = $txt_rep->entities($acc_payment_val['message_desc']);
						$user_from_e = $this->crypt->EncryptU($user_from);
						$user_from_obj = new User($this->con, $user_from, $user_from_e);
						$user_from_name = $user_from_obj->getFirstAndLastNameFast();
						$message_dt = $val['date'];
						
						$_exp_days = 'P' . 2 . 'D';
						$message_dt_obj = DateTime::createFromFormat('Y-m-d H:i:s', $message_dt);
						$payment_exp_date_obj = $message_dt_obj->add(new DateInterval($_exp_days));
						
						$_today = new DateTime();
						if($payment_exp_date_obj > $_today){
							
							$payment_exp_date = $payment_exp_date_obj->format('Y/m/d, g:ia');
							
							switch ($lang){
								
								case("en"):
								    $data = $data . '<div class="message payment_request_text"> The patient <b style="float:none;">'.' '. $user_from_name .' '. '</b> has the following doubt:<br>
											<br><i>'.'"'. $message_desc .'"'.'</i><br><br>
											and has offered to pay your messages fee, which -if you accept- means he/she can chat with you for 2 weeks with regard of this and follow-up questions. To accept this, you need to write a message back and start a conversation. After this period ends, the patient will be allowed to grade your messages service, which serves as guides for future customers. <b>If by ' . $payment_exp_date . ' you have not written to the pacient, the payment offer would be considered as rejected by you.</b><br>
											<b>NOTE: Only accept if you can help the patient through messages, otherwise, do not accept (by not replying anything).</b></div>
											<input type="hidden" id="pending_acceptance" name="pending_acceptance" value="1">';
									break;
									
								case("es"):
								    $data = $data . '<div class="message payment_request_text"> El paciente <b style="float:none;">' .' '. $user_from_name . ' '.'</b> tiene la siguiente duda:<br>
											<br><i>'.'"'. $message_desc .'"'.'</i><br><br>
											y ofreció pagar por tu servicio de mensajes, por lo tanto, si lo aceptas, el podrá escribirte por 2 semanas respecto a esta pregunta y preguntas subsiguientes. Para aceptar este pago, sólo debes escribirle un mensaje y comenzar una conversación. Luego de que este periodo termine, el paciente podrá evaluar tu servicio, sirviendo de guía para futuros usuarios. <b>Si para el ' . $payment_exp_date . ' tu no le has escrito al paciente, la oferta de pago será considerada como rechazada por ti.</b><br>
											<b>NOTA: Sólo acepta si puedes ayudar al paciente a través de mensajes, de lo contrario, no aceptes (no respondiendo nada).</b></div>
											<input type="hidden" id="pending_acceptance" name="pending_acceptance" value="1">';
									break;
							}
							
							
						}
					}
				}
				
				$ending = 0;
				if($messages_count >= $messages_to_load){
					break;
				}
				$messages_count++;
				
				$ending= 1;
				
			}	
			$data .= "<div id='scroll_marker'></div>
					<input type='hidden' id='latest_loaded_message_id' name='latest_loaded_message_id' value='" . $latest_loaded_message_id . "'>
					<input type='hidden' id='ending' name='ending' value='" . $ending . "'>";
			
			return $data;
		}

		public function getLatestMessage($userLoggedIn,$user2){
			$lang = $_SESSION['lang'];
			$user2_e = $this->crypt->EncryptU($user2);
			$details_array = array();
			$my_messages_tab = $this->my_messages_tab;

			$query = mysqli_query($this->con, "SELECT body, user_to, user_from, date FROM $my_messages_tab WHERE (user_to='$userLoggedIn' AND user_from = '$user2') OR (user_to='$user2' AND user_from = '$userLoggedIn') ORDER BY date DESC LIMIT 1");

			$arr = mysqli_fetch_array($query);
			$user2_obj = new User($this->con, $user2, $user2_e);
			$user2_fullname = $user2_obj->getFirstName();
			
			switch ($lang){
				
				case("en"):
					$sent_by = ($arr['user_to'] == $userLoggedIn) ? $user2_fullname . " said: " : "You said: ";
					break;
					
				case("es"):
					$sent_by = ($arr['user_to'] == $userLoggedIn) ? $user2_fullname . " dijo: " : "Tu dijiste: ";
					break;
			}

			//Create the TimeFrame stamps

			$date_time_now = date("Y-m-d H:i:s");
			$start_date = new DateTime($arr['date']); //Time of post
			$end_date = new DateTime($date_time_now); //Current time
			$interval = $start_date->diff($end_date); //Difference between dates

			$time_stamp = new TimeStamp();
			$time_message = $time_stamp->getTimeStamp($interval);

			$txt_rep = new TxtReplace();
			$user_from_obj = new User($this->con, $arr['user_from'], $this->crypt->EncryptU($arr['user_from']));
			$creation = $user_from_obj->user_info['signup_date'];
			
			$body=$this->crypt->DecryptM($arr['body'],$arr['user_from'], $arr['user_to'],$creation);
			$body = $txt_rep->replace($body);
			$body = $txt_rep->entities($body);
			$body = $txt_rep->replaceLineBreak($body);

			array_push($details_array, $sent_by);
			array_push($details_array, $body);
			array_push($details_array, $time_message);

			return $details_array;
		}

		public function getConvos($selected_user = 0){ //who and when sent a message
			$lang = $_SESSION['lang'];
			$userLoggedIn =$this->user_obj->getUsername();
			$my_messages_tab = $this->my_messages_tab;
			$return_str = "";
			$txt_rep = new TxtReplace();
			$time_stamp = new TimeStamp();
			$sql_q = "
					SELECT m1.*
					FROM $my_messages_tab m1 
					LEFT JOIN $my_messages_tab m2 
					ON ((m1.`user_to` = m2.`user_to` AND m1.`user_from` = m2.`user_from`) 
					OR (m1.`user_to` = m2.`user_from` AND m1.`user_from` = m2.`user_to`)) 
					AND m1.`date` < m2.`date` 
					WHERE m2.`date` IS NULL
					ORDER BY m1.`date` DESC";
			$query = mysqli_query($this->con,$sql_q);
			
			$first_bool = 1;
			
			while($arr=mysqli_fetch_array($query)){
				$username= ($arr['user_to'] != $userLoggedIn) ? $arr['user_to'] : $arr['user_from'];
				$username_e = $this->crypt->EncryptU($username);
				
				$username_e_h = bin2hex($username_e);
				$user_found_obj = new User($this->con, $username, $username_e);
				$message = $arr['body'];
				
				$user_from_obj = new User($this->con, $arr['user_from'], $this->crypt->EncryptU($arr['user_from']));
				$creation = $user_from_obj->user_info['signup_date'];
				
				$message = $this->crypt->decryptM($message,$arr['user_from'], $arr['user_to'],$creation);
				$time = $time_stamp->getTimeStampFromDates_noSmallIntervals($arr['date'], date("Y-m-d H:i:s"));
				
				if($message != "1b66f76804fd4e8fe3b601040f1aec83alertpayment2017"){
					$dots = (strlen($message) >= 12) ? "..." : "";
					$split = str_split($message, 12);
					$split = $split[0] . $dots;
				}
				else{
					switch ($lang){
						
						case("en"):
							$split = "Payment offered";
							break;
							
						case("es"):
							$split = "Pago ofrecido";
							break;
					}
					
				}
				$unopened_conversation = $arr['opened'];
				
				if($first_bool == 1){
					$return_str .= "<input type='hidden' name='first_in_list' id='first_in_list' value='$username_e_h'>";
					$first_bool = $first_bool & 0;
				}
				
				$return_str .= ($unopened_conversation == 'no')? '<span class="unread_message_dot" id="alert_dot_' . $username_e_h.'">&nbsp;</span>':'<span class="unread_message_dot hidden" id="alert_dot_' . $username_e_h.'">&nbsp;</span>';
				$return_str .= "<a href='messages_frame.php?u=$username_e_h'>";
				
			
				$return_str .= ($selected_user == $username_e_h)? "<div class='messages_banner selected_message_banner'>" : "<div class='messages_banner'>";	
				$return_str .=			"<img src='" . $txt_rep->entities($user_found_obj->getProfilePicFast()) . "'>
										" . $txt_rep->entities($user_found_obj->getFirstAndLastNameShort(18)) . "<br>";
				
				if($unopened_conversation == 'no'){	
					$return_str .= " <p class = 'preview_messages_text' id='message_" . $username_e_h. "' style='margin: 0;'><b>" . $txt_rep->removeLineBreak($txt_rep->entities($split)) . "</b></p>   
                                     <span class='timestamp_smaller' id='time_" . $username_e_h. "'><b>" . $txt_rep->entities($time) . "</b></span><br>";
				}
				else{
					$return_str .=" <p class = 'preview_messages_text' id='message_" . $username_e_h. "' style='margin: 0;'>" . $txt_rep->removeLineBreak($txt_rep->entities($split)) . "</p>
                                   <span class='timestamp_smaller' id='time_" . $username_e_h. "'>" . $txt_rep->entities($time) . "</span>";
				}
				
				$return_str .="		</div>
								</a>";
				
			}
			return $return_str;
		}

		public function getConvosDropdown($data, $limit){
			$lang = $_SESSION['lang'];
			$page = $data['page'];
			$my_messages_tab = $this->my_messages_tab;
			$txt_rep =  new TxtReplace();

			$userLoggedIn =$this->user_obj->getUsername();
			$return_str = "";
			$convos = array();

			if($page == 1)
				$start = 0;
			else
				$start = ($page - 1) * $limit;

			$set_viewed_query = mysqli_query($this->con, "UPDATE $my_messages_tab SET viewed = 'yes' WHERE user_to = '$userLoggedIn'");

			$query = mysqli_query($this->con,"SELECT user_to, user_from FROM $my_messages_tab WHERE user_to='$userLoggedIn' OR user_from='$userLoggedIn' ORDER BY date DESC");

			while($arr=mysqli_fetch_array($query)){
				$user_to_push = ($arr['user_to'] != $userLoggedIn) ? $arr['user_to'] : $arr['user_from'];

				if(! in_array($user_to_push, $convos)){
					array_push($convos, $user_to_push);
				}
			}

			$num_iter = 0; //number of messages checked
			$count = 1; //number of messages posted

			foreach($convos as $username){

				if($num_iter++ < $start)
					continue;

				if($count > $limit) //this is equivalent to the if for num_iter.
					break;
				else
					$count++;

				$is_unread_query =  mysqli_query($this->con, "SELECT opened FROM $my_messages_tab WHERE user_to= '$userLoggedIn' AND user_from = '$username' ORDER BY date DESC LIMIT 1"); //checks if the message is for me.
				$arr = mysqli_fetch_array($is_unread_query);
				$style = ($arr['opened'] == 'no') ? "background-color: #DDEDFF;" : "";
                $username_e = $this->crypt->EncryptU($username);
				$user_found_obj = new User($this->con, $username, $username_e);
				$latest_message_details = $this->getLatestMessage($userLoggedIn,$username);
				
				$username_e_h = bin2hex($username_e);
				
				if($latest_message_details[1] != "1b66f76804fd4e8fe3b601040f1aec83alertpayment2017"){
					$dots = (strlen($latest_message_details[1]) >= 27) ? "..." : "";
					$split = str_split($latest_message_details[1], 27);
					$split = $split[0] . $dots;
				}
				else{
					switch ($lang){
						
						case("en"):
							$split = "Payment offered";
							break;
							
						case("es"):
							$split = "Pago ofrecido";
							break;
					}
					
				}
				
				//TODO: make this messages better. tabChanger 
				$return_str .= <<<EOS
								<a href='javascript:void()' onclick='checkIfMessagesIFrameExists("$username_e_h");selectMessageIFrame("$username_e_h");tabChanger("messages")'>
EOS;
				$return_str .= 		"<div class='user_found_messages' style='" . $style . "'>
										<img src='" . $txt_rep->entities($user_found_obj->getProfilePicFast()) . "' margin-right:5px;'>
										<p>" . $txt_rep->entities($user_found_obj->getFirstAndLastNameShort(30)) . "</p>
										<p id = 'not_messages' style=' margin: 0; display: inline-block; width: 247px; float: left;'><b>" . $txt_rep->entities($latest_message_details[0]) ."</b>". $split . "</p>
										<span class='timestamp_smaller' id='not_time'> " . $txt_rep->entities($latest_message_details[2]) . " </span>
									</div>
								</a>";

			}
			//If posts were loaded
			if($count > $limit){
				$return_str .= "<input type='hidden' class='nextPageDropdownData' value='" . ($page + 1) . "'>
								<input type='hidden' class='noMoreDropdownData' value = 'false'>";
			}
			else{
			    switch($data['lang']){
			        case('en'):{
			            $return_str .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align:center;padding-top: 10px;margin-bottom: 10px;'>
                                No more messages to show.
                                </p>";
			            break;
			        }
			        case('es'):{
			            $return_str .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align:center;padding-top: 10px;margin-bottom: 10px;'>
                                No hay más mensajes para mostrar.
                                </p>";
			            break;
			        }
			    }
			}

			return $return_str;
		}

		public function getUnreadNumber(){
			$userLoggedIn =  $this->user_obj->getUsername();
			$my_messages_tab = $this->my_messages_tab;
			$query = mysqli_query($this->con, "SELECT * FROM $my_messages_tab WHERE viewed='no' AND user_to='$userLoggedIn'");
			return mysqli_num_rows($query);
		}
		
		public function checkAvailabilityToMessage($doctor_user){
			//check if the patient can message the doctor
			$doctor_user_e = $this->crypt->EncryptU($doctor_user);
			$doctor_user_obj = new User($this->con, $doctor_user, $doctor_user_e);
			
			if($this->user_obj->isDoctor()){
				//echo "here0";
				return 1;
			}
			
			//WRITE NEW CODE BELOW HERE
			
			$settings_obj = new Settings($this->con, $doctor_user, $doctor_user_e);
			$payed_messages_settings = $settings_obj->getSettingsValues('payed_messages');
			
			if($payed_messages_settings == 0){
				//echo "here1";
				return 1;
			}
			
			$doctor_messages_status_table = $doctor_user_obj->getMessagesStatusTable();
			$userLoggedIn =  $this->user_obj->getUsername();
			
			$stmt = $this->con->prepare("SELECT * FROM $doctor_messages_status_table WHERE secondary_interlocutor = ?");
			$stmt->bind_param("s",$userLoggedIn);
			$stmt->execute();
			
			$query = $stmt->get_result();
			
			if(mysqli_num_rows($query) == 0){
				//echo "here2";
				return 0;
			}
			
			$arr = mysqli_fetch_assoc($query);
			
			$available = 0;
			
			if($arr['enabled'] == 1){
				//echo "here3";
				$available = 1;
			}
			elseif($arr['payed'] == 1){
				$_lower_bound = DateTime::createFromFormat('Y-m-d H:i:s', $arr['date_activated']);
				$_upper_bound = DateTime::createFromFormat('Y-m-d H:i:s', $arr['date_termination']);
				$_today = new DateTime();
				if($_today <= $_upper_bound && $_today >= $_lower_bound){
					//echo "here4";
					$available = 1;
				}
			}
			return $available;
		}
		
		public function createMessagingPaymentInstance($doctor_user,$message_desc,$price){	

			//Patient instantiated the class. It creates an offer from the patient to the doctor for messaging services
			$message_desc= strip_tags($message_desc);//Remove html tags
			$message_desc= mysqli_real_escape_string($this->con,$message_desc);
			$message_desc=preg_replace("/[^\p{Xwd} ]+/u", "", $message_desc);
			$doctor_user_e = $this->crypt->EncryptU($doctor_user);
			$doctor_user_obj = new User($this->con, $doctor_user, $doctor_user_e);
			$doctor_messages_status_table = $doctor_user_obj->getMessagesStatusTable();
			$userLoggedIn =  $this->user_obj->getUsername();
			$user_obj = $this->user_obj;
			$_today = new DateTime();
			$_today_formated = $_today->format('Y-m-d H:i:s');
			
			$bill_number = "M" . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9);
			
			$sql_query = "
				INSERT INTO $doctor_messages_status_table (`secondary_interlocutor`, `enabled`, `payed`, `payed_messages_cost`, `date_payed`, `accepted_payment`, `date_activated`, `date_termination`, `bill_number`, `message_desc`, `score`) VALUES(?,0,1,?,?,0,'','',?,?,-1) 
				ON DUPLICATE KEY 
				UPDATE payed=1,payed_messages_cost=?,date_payed=?,accepted_payment=0,date_activated='',date_termination='',bill_number=?,message_desc=?";
			$stmt = $this->con->prepare($sql_query);
			$stmt->bind_param("sisssisss",$userLoggedIn,$price,$_today_formated,$bill_number,$message_desc,$price,$_today_formated,$bill_number,$message_desc);
			$stmt->execute();
			
			//Insert in patient payment history
			
			$description = "Messaging-Service Purchased";
			$patient_payment_hist =  $user_obj->getPaymentsHistTab();
			
			$sql_query = "
			INSERT INTO $patient_payment_hist (`bill_number`, `description`, `amount`, `datetime_issued`, `charged`) VALUES(?,?,?,?,'n')";
			$stmt = $this->con->prepare($sql_query);
			$stmt->bind_param("ssis",$bill_number,$description,$price,$_today_formated);
			$stmt->execute();
			
			//Insert payment message into doctor's table
			
			$doctor_messages_tab = $doctor_user_obj->getMessagesTable();
			
			$body="1b66f76804fd4e8fe3b601040f1aec83alertpayment2017";
			$_date = $_today->format('Y-m-d H:i:s');
			$userLoggedIn =$this->user_obj->getUsername();
			$creation = $this->user_obj->user_info['signup_date'];
			$body_e = $this->crypt->EncryptM($body,$userLoggedIn,$doctor_user,$creation);
			
			$stmt = $this->con->prepare("INSERT INTO $doctor_messages_tab VALUES('', ?, ?, ?, ?, 'no', 'no', 'no')");
			$stmt->bind_param("ssss", $doctor_user, $userLoggedIn, $body_e, $_date);
			$stmt->execute();
			
			$stmt->close();
		}
		
		public function checkPendingPaymentAcceptance($doctor_user){
			//Patient instantiated the class. Checks if the patient already sent a request. Intended to only be executed after the function "checkAvailabilityToMessage", as if that function is true, this shouldn't run
			$doctor_user_e = $this->crypt->EncryptU($doctor_user);
			$doctor_user_obj = new User($this->con, $doctor_user, $doctor_user_e);
			$doctor_messages_status_table = $doctor_user_obj->getMessagesStatusTable();
			$userLoggedIn =  $this->user_obj->getUsername();
			
			$stmt = $this->con->prepare("SELECT payed,accepted_payment FROM $doctor_messages_status_table WHERE secondary_interlocutor = ?");
			$stmt->bind_param("s",$userLoggedIn);
			$stmt->execute();
			
			$query = $stmt->get_result();
			$arr = mysqli_fetch_assoc($query);
			
			if($arr['accepted_payment'] == 0){
				return $arr['payed'];
			}
			else{
				return 0;
			}
		}
		
		public function cancelPendingPaymentAcceptance($doctor_user){
			//Patient instantiated the class. Checks if the patient already sent a request. Intended to only be executed after the function "checkAvailabilityToMessage", as if that function is true, this shouldn't run
			$doctor_user_e = $this->crypt->EncryptU($doctor_user);
			$doctor_user_obj = new User($this->con, $doctor_user, $doctor_user_e);
			$doctor_messages_status_table = $doctor_user_obj->getMessagesStatusTable();
			$userLoggedIn = $this->user_obj->getUsername();
			
			$stmt = $this->con->prepare("SELECT payed, accepted_payment FROM $doctor_messages_status_table WHERE secondary_interlocutor = ?");
			$stmt->bind_param("s",$userLoggedIn);
			$stmt->execute();
			
			$query = $stmt->get_result();
			$arr = mysqli_fetch_assoc($query);
			
			$cancel_availability = $arr['accepted_payment'];
			
			if($cancel_availability != 1){
				$stmt = $this->con->prepare("UPDATE $doctor_messages_status_table SET payed = 0, date_payed='' WHERE secondary_interlocutor = ?");
				$stmt->bind_param("s",$userLoggedIn);
				$stmt->execute();
			}
		}
		
		public function checkEnabledChat($patient_user){
			//Doctor instantiated the class. Checks if the patient's chat is enabled
			
			$doctor_messages_status_table = $this->user_obj->getMessagesStatusTable();
			
			$stmt = $this->con->prepare("SELECT enabled FROM $doctor_messages_status_table WHERE secondary_interlocutor = ?");
			$stmt->bind_param("s",$patient_user);
			$stmt->execute();
			
			$query = $stmt->get_result();
			$arr = mysqli_fetch_assoc($query);
			
			return $arr['enabled'];
		}
		
		public function getRemainingTime($patient_user,$doctor_user){
		    $doctor_user_e = $this->crypt->EncryptU($doctor_user);
			$doctor_user_obj = new User($this->con, $doctor_user, $doctor_user_e);
			$doctor_messages_status_table = $doctor_user_obj->getMessagesStatusTable();
			
			$stmt = $this->con->prepare("SELECT * FROM $doctor_messages_status_table WHERE secondary_interlocutor = ?");
			$stmt->bind_param("s",$patient_user);
			$stmt->execute();
			
			$query = $stmt->get_result();
			$arr = mysqli_fetch_assoc($query);
			
			$_lower_bound = DateTime::createFromFormat('Y-m-d H:i:s', $arr['date_activated']);
			$_upper_bound = DateTime::createFromFormat('Y-m-d H:i:s', $arr['date_termination']);
			$_today = new DateTime();
			
			if($_today <= $_upper_bound && $_today >= $_lower_bound){
					$interval = $_today->diff($_upper_bound);
					//return $interval->format("%s") . "mins:" . $interval->format("%i") . "hours:" .$interval->format("%H") . "days:" .$interval->format("%d");
					return $interval->format("%s") + $interval->format("%i")*60 + $interval->format("%H")*3600 + $interval->format("%d")*86400 + 10;
			}
			else{
				return -1;
			}
		}
	
	}

?>