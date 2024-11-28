<?php  
include_once("Crypt.php");
class Post{
	private $user_obj;
	private $con;
	private $username_e;

	public function __construct($con, $user, $user_e){
		try{
			$this->con = $con;
			$this->user_obj = new User($con, $user, $user_e);
			$this->username_e = $user_e;
		} 
		catch ( Exception $e ){
			$this->con = "";
			$this->$user_obj = "";
			throw new Exception( $e->getMessage() );
		}
	}

	private function submit_post_friends_table($user_to, $body, $added_by, $date_added, $glob_id){
	    $crypt = new Crypt();
		if($user_to == "0000"){
			$friends_arr = $this->user_obj->getFriends();

			foreach($friends_arr as $i){
			    $i['username_friend_e'] = $crypt->EncryptU($i['username_friend']);
				$friend_obj = new User($this->con, $i['username_friend'], $i['username_friend_e']);
				$friend_friends_table = $friend_obj->getPostsTable();


				$stmt = $this->con->prepare("INSERT INTO $friend_friends_table VALUES( ?, ?, ?, ?, 'no', 'no', '0', ?) ");
				$stmt->bind_param("sssss", $body, $added_by, $user_to, $date_added, $glob_id);
				$stmt->execute();

				//$query = mysqli_query($this->con, "INSERT INTO $friend_friends_table VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0', '$glob_id') ");
			}
			
		}
		else{
		    $user_to_e = $crypt->EncryptU($user_to);
			$user_to_obj = new User($this->con, $user_to, $user_to_e);
			$friends_arr = $user_to_obj->getFriends();

			foreach($friends_arr as $i){
                $i['username_friend_e'] =  $crypt->EncryptU($i['username_friend']);
				$friend_obj = new User($this->con, $i['username_friend'], $i['username_friend_e']);
				$friend_friends_table = $friend_obj->getPostsTable();

				$stmt = $this->con->prepare("INSERT INTO $friend_friends_table VALUES( ?, ?, ?, ?, 'no', 'no', '0', ?) ");
				$stmt->bind_param("sssss", $body, $added_by, $user_to, $date_added, $glob_id);
				$stmt->execute();

				//$query = mysqli_query($this->con, "INSERT INTO $friend_friends_table VALUES('', '$body', '$added_by', '$user_to', '$date_added', 'no', 'no', '0', '$glob_id') ");
			}
			
		}
	}

	public function submitPost($body, $user_to){
        $crypt = new Crypt();
        $txt_rep = new TxtReplace();
		//$body = strip_tags($body); //Removes any HTML tags
		//$body = mysqli_real_escape_string($this->con,$body); //removes single quotes from texts for sql safe search.
		$check_empty = preg_replace('/[\s,]+/', '', $body); // the / and / surrounds the text we want to replace, like \s+ which is spaces.
		if($check_empty != ""){
			$body = $txt_rep->entities($body);
//			$body_array = preg_split("/\s+/", $body);

// 			foreach($body_array as $key => $value){

// 				if(strpos($value, "youtube.com/watch?v=") !== false){ // \ cancels the following character (does not get compiled, its called, magic quotes)
// 					$link = preg_split("!\&!",$value);
// 					$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
// 					$value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value . "\'></iframe><br>";

// 					$body_array[$key] = $value;
// 				}

// 			}

//			$body = implode(" ", $body_array); //returns a string with the elements in the array separated by a space. opposite of explode.
			
			//Current date and time
			$date_added = date("Y-m-d H:i:s");

			//Get Username
			$added_by = $this->user_obj->getUsername();

			//If user is not on own profile, user_to is '0000'
			if($user_to == "0000"){
				$posts = $this->user_obj->getPostsTable();
			}
			else if($user_to == ""){
				$posts = $this->user_obj->getPostsTable();
				$user_to = "0000";
			}
			else{
			    $user_to_e = $crypt->EncryptU($user_to);
				$user_to_obj = new User($this->con, $user_to, $user_to_e);
				$posts = $user_to_obj->getPostsTable();
			}

			//Insert post into walls
			//$glob_id = "post_" . date("YmdHis") . $user_to . $added_by . mt_rand(100,999); //Post unique ID
			
			//Trending
			$txt_rep = new TxtReplace();
			$stopWords = "hey";
			
			$stopWordsArr = preg_split("/[\s,]+/", $stopWords); //it splits not only at spaces but also linebreaks
			
			$no_punctuation = preg_replace("/[^\p{Xwd} ]+/u", "", $body); //^ means not, negation.
			$no_punctuation = strip_tags($no_punctuation);
			$no_punctuation = $txt_rep->entities($no_punctuation);
			
			
			if(strpos($no_punctuation, "http") === false){
				$no_punctuation = preg_split("/[\s,]+/", $no_punctuation);
				
				foreach($stopWordsArr as $value){
					foreach($no_punctuation as $key => $value2){
						if(strtolower($value) == strtolower($value2)){
							$no_punctuation[$key] = "";
						}
					}
				}
				
				foreach ($no_punctuation as $value) {
					$this->calculateTrend(ucfirst($value));
				}
				
			}
			
			
			//Encrypt posts
			$body = $crypt->encryptString($body, $this->username_e."98634jghajGMHBSsd8712ugyjbdu6g!!DHnahvgA__ad++jhas%%adf");
			
			$crypt_usrnames_xtract = bin2hex($crypt->Encrypt(substr($user_to, -2).substr($added_by, -2),"-%->blaP873/|/e#r/|/ap28?iC_P0z7!"));
			$glob_id = "post_" . date("YmdHis") .$crypt_usrnames_xtract.mt_rand(1000000,9999999); //Post unique ID
			
			$this->submit_post_friends_table($user_to, $body, $added_by, $date_added, $glob_id);

			$stmt = $this->con->prepare("INSERT INTO $posts VALUES(?, ?, ?, ?, 'no', 'no', '0', ?)");
			$stmt->bind_param("sssss", $body, $added_by, $user_to, $date_added, $glob_id);
			$stmt->execute();
			

			$stmt->close();


			//Insert Notification

			if($user_to != '0000'){
				$added_by_e = $crypt->EncryptU($added_by);
				$notification = new Notification($this->con, $added_by, $added_by_e);
				$notification->insertNotification($glob_id, $user_to, "profile_post");
			}

			//Update post count for user
			$num_posts = $this->user_obj->getNumPosts();
			$num_posts++;
			$update_post = mysqli_query($this->con,"UPDATE users SET num_posts ='$num_posts' WHERE username='$added_by'");

		}
	}

	public function updatePosts($user_to_update){
		//Used to update the posts once you become someone's friend
	    $crypt = new Crypt();
		//my tables
		$posts_tab = $this->user_obj->getPostsTable();
		$comments_tab = $this->user_obj->getCommentsTable();
		$likes_tab = $this->user_obj->getLikesTable();

		$user= $this->user_obj->getUsername();
        
		//other user' tables
		
		$user_to_update_e = $crypt->EncryptU($user_to_update); 
		$other_user_obj = new User($this->con, $user_to_update, $user_to_update_e);
		$other_user_obj_tab = $other_user_obj->getPostsTable();
		$user_to_upd_comments_tab = $other_user_obj->getCommentsTable();
		$other_user_likes_tab = $other_user_obj->getLikesTable();

		//Get id's posts 

		$stmt = $this->con->prepare("SELECT global_id FROM $other_user_obj_tab WHERE (user_to=? AND added_by !=?) OR (user_to='0000' AND added_by = ?)");
		$stmt->bind_param("sss", $user_to_update, $user, $user_to_update);
		$stmt->execute();
		$ids1 = $stmt->get_result();


		$stmt = $this->con->prepare("SELECT global_id FROM $posts_tab WHERE (user_to='$user' AND added_by !='$user_to_update') OR (user_to='0000' AND added_by = '$user')");
		$stmt->bind_param("sss", $user, $user_to_update, $user);
		$stmt->execute();
		$ids2 = $stmt->get_result();

		//$ids1 = mysqli_query($this->con, "SELECT global_id FROM $other_user_obj_tab WHERE (user_to='$user_to_update' AND added_by !='$user') OR (user_to='0000' AND added_by = '$user_to_update')");

		//$ids2 = mysqli_query($this->con, "SELECT global_id FROM $posts_tab WHERE (user_to='$user' AND added_by !='$user_to_update') OR (user_to='0000' AND added_by = '$user')");



		//Update posts

		$stmt = $this->con->prepare("INSERT INTO $other_user_obj_tab (body, added_by, user_to, date_added, user_closed, deleted, likes, global_id)
			SELECT body, added_by, user_to, date_added, user_closed, deleted, likes, global_id FROM $posts_tab WHERE (user_to=? AND added_by !=?) OR (user_to='0000' AND added_by = ?) ORDER BY date_added DESC");

		$stmt->bind_param("sss", $user, $user_to_update, $user);
		$stmt->execute();

		$stmt = $this->con->prepare("INSERT INTO $posts_tab (body, added_by, user_to, date_added, user_closed, deleted, likes, global_id)
			SELECT body, added_by, user_to, date_added, user_closed, deleted, likes, global_id FROM $other_user_obj_tab WHERE (user_to=? AND added_by !=?) OR (user_to='0000' AND added_by = ?) ORDER BY date_added DESC");

		$stmt->bind_param("sss", $user_to_update, $user, $user_to_update);
		$stmt->execute();

		$stmt->close();


		//Update comments

		if (false === ($prep = $this->con->prepare("INSERT INTO $comments_tab (post_body, posted_by, posted_to, date_added, removed, post_id) SELECT post_body, posted_by, posted_to, date_added, removed, post_id FROM $user_to_upd_comments_tab WHERE post_id=?"))){
				$err = 'error preparing statement: ' . $this->con->error;
			}

		else if (!$prep->bind_param("s", $i_temp)){
		    $err = 'error binding params: ' . $prep->error;
		}

		foreach ($ids1 as $i) {
			$i_temp = $i['global_id'];
	 		if (!$prep->execute()) {
			    $err = 'error executing statement: ' . $prep->error;
			}
		}

		$prep = $this->con->prepare("INSERT INTO $user_to_upd_comments_tab (post_body, posted_by, posted_to, date_added, removed, post_id) SELECT post_body, posted_by, posted_to, date_added, removed, post_id FROM $comments_tab WHERE post_id=?");
		$prep->bind_param("s", $i_temp);

		foreach ($ids2 as $i) {
			$i_temp = $i['global_id'];
			$prep->execute();
		}

		$prep->close();

		//Update Likes

		$prep = $this->con->prepare("INSERT INTO $likes_tab (username, post_id) SELECT username, post_id FROM $other_user_likes_tab WHERE post_id=?");
		$prep->bind_param("s", $i_temp);

		foreach ($ids1 as $i) {
			$i_temp = $i['global_id'];
			$prep->execute();
		}

		$prep = $this->con->prepare("INSERT INTO $other_user_likes_tab (username, post_id) SELECT username, post_id FROM $likes_tab WHERE post_id=?");
		$prep->bind_param("s", $i_temp);

		foreach ($ids2 as $i) {
			$i_temp = $i['global_id'];
			$prep->execute();
		}

		$prep->close();


		//$posts = mysqli_query($this->con, "INSERT INTO $other_user_obj_tab (body, added_by, user_to, date_added, user_closed, deleted, likes, global_id)
			//SELECT body, added_by, user_to, date_added, user_closed, deleted, likes, global_id FROM $posts_tab WHERE (user_to='$user' AND added_by !='$user_to_update') OR (user_to='0000' AND added_by = '$user') ORDER BY id DESC");

		//$posts = mysqli_query($this->con, "INSERT INTO $posts_tab (body, added_by, user_to, date_added, user_closed, deleted, likes, global_id)
			//SELECT body, added_by, user_to, date_added, user_closed, deleted, likes, global_id FROM $other_user_obj_tab WHERE (user_to='$user_to_update' AND added_by !='$user') OR (user_to='0000' AND added_by = '$user_to_update') ORDER BY id DESC");

	}

	public function deletePosts($other_user){
		//used for friendship termination. It means I only keep posts that have to do with me, and my friends see them too. The other posts are deleted.
        $crypt = new Crypt();
		//My tables
		$posts_tab = $this->user_obj->getPostsTable();
		$comments_tab = $this->user_obj->getCommentsTable();
		$likes_tab = $this->user_obj->getLikesTable();


		$user= $this->user_obj->getUsername();

		//Other user tables
		$other_user_e = $crypt->EncryptU($other_user); 
		$user_to_update_obj = new User($this->con, $other_user, $other_user_e);
		$user_to_update_tab = $user_to_update_obj->getPostsTable();
		$user_to_upd_comments_tab = $user_to_update_obj->getCommentsTable();
		$other_user_likes_tab = $user_to_update_obj->getLikesTable();

		//IDs
		$ids1 = mysqli_query($this->con, "SELECT global_id FROM $posts_tab WHERE (user_to='$other_user' AND added_by !='$user') OR (user_to='0000' AND added_by = '$other_user')");

		$ids2 = mysqli_query($this->con, "SELECT global_id FROM $user_to_update_tab WHERE (user_to='$user' AND added_by !='$other_user') OR (user_to='0000' AND added_by = '$user')");



		//Update comments

		$prep = $this->con->prepare("DELETE FROM $comments_tab WHERE post_id=?");
		$prep->bind_param("s", $i_temp);

		foreach ($ids1 as $i) {
			$i_temp = $i['global_id'];
			$prep->execute();
		}

		$prep = $this->con->prepare("DELETE FROM $user_to_upd_comments_tab WHERE post_id=?");
		$prep->bind_param("s", $i_temp);

		foreach ($ids2 as $i) {
			$i_temp = $i['global_id'];
			$prep->execute();
		}

		$prep->close();



		//Update Likes

		$prep = $this->con->prepare("DELETE FROM $likes_tab WHERE post_id=?");
		$prep->bind_param("s", $i_temp);

		foreach ($ids1 as $i) {
			$i_temp = $i['global_id'];
			$prep->execute();
		}

		$prep = $this->con->prepare("DELETE FROM $other_user_likes_tab WHERE post_id=?");
		$prep->bind_param("s", $i_temp);

		foreach ($ids2 as $i) {
			$i_temp = $i['global_id'];
			$prep->execute();
		}

		$prep->close();



		//Update posts

		$posts = mysqli_query($this->con, "DELETE FROM $posts_tab WHERE (user_to='$other_user' AND added_by !='$user') OR (user_to='0000' AND added_by = '$other_user')");

		$posts = mysqli_query($this->con, "DELETE FROM $user_to_update_tab WHERE (user_to='$user' AND added_by !='$other_user') OR (user_to='0000' AND added_by = '$user')");

	}

	public function removePosts($post_id, $user_from, $user_to){
	    $crypt = new Crypt();
		//IMPORTANT user_to must be the creator of the instance of the class Post, if not posted to itself.

		if($user_to != "0000"){
		    $user_to_e = $crypt->EncryptU($user_to);
			$owner_obj = new User($this->con, $user_to, $user_to_e);
			$owner_username = $user_to;
			$owner_posts_tab = $owner_obj->getPostsTable();
		}


		else{
		    $user_from_e = $crypt->EncryptU($user_from);
			$owner_obj = new User($this->con, $user_from, $user_from_e);
			$owner_username = $user_from;
			$owner_posts_tab = $owner_obj->getPostsTable();
		}

		$query_likes = mysqli_query($this->con, "SELECT likes FROM $owner_posts_tab WHERE global_id='$post_id'");
		$likes_post = mysqli_fetch_row($query_likes);

		$query = mysqli_query($this->con, "UPDATE $owner_posts_tab SET deleted='yes' WHERE global_id='$post_id' ");

		//Update post count for user
		$num_posts = $owner_obj->getNumPosts();
		$num_posts--;
		$update_post = mysqli_query($this->con,"UPDATE users SET num_posts ='$num_posts' WHERE username='$owner_username'");
        
		//Update likes count for user 
		$num_likes = $owner_obj->getNumLikes();
		$new_num_likes = $num_likes - $likes_post[0];
		$update_post = mysqli_query($this->con,"UPDATE users SET num_likes ='$new_num_likes' WHERE username='$owner_username'");

		$friends_arr = $owner_obj->getFriends();
		foreach($friends_arr as $i){
		    $i['username_friend_e'] = $crypt->EncryptU($i['username_friend']);
			$friend_obj = new User($this->con, $i['username_friend'], $i['username_friend_e']);
			$friend_friends_table = $friend_obj->getPostsTable();
			$query = mysqli_query($this->con, "UPDATE $friend_friends_table SET deleted='yes' WHERE global_id='$post_id' ");
		}

	}

	public function calculateTrend($term){
		if($term != ''){

			$stmt = $this->con->prepare("SELECT * FROM trends WHERE title=?");
			$stmt->bind_param("s", $term1);
			$term1 = $term;
			$stmt->execute();
			$stmt->store_result();
			//$query = mysqli_query($this->con,"SELECT * FROM trends WHERE title='$term'");

			//if(mysqli_num_rows($query) == 0){

			if($stmt->num_rows == 0){
				$stmt2 = $this->con->prepare("INSERT INTO trends(title, hits) VALUES(?, '1')");
				$stmt2->bind_param("s", $term2);
				$term2 = $term;

				$stmt2->execute();
				//$insert_query = mysqli_query($this->con,"INSERT INTO trends(title, hits) VALUES('$term', '1')");
			}
			else{
				$stmt2 = $this->con->prepare("UPDATE trends SET hits=hits+1 WHERE title=?");
				$stmt2->bind_param("s", $term);

				$stmt2->execute();
				//$insert_query = mysqli_query($this->con,"UPDATE trends SET hits=hits+1 WHERE title='$term'");
			}
			$stmt->close();
			$stmt2->close();
		}
	}

	public function loadPostsFriends($data, $limit){
		$lang = $_SESSION['lang']; 
		$page = $data['page'];
		$userLoggedIn = $this->user_obj->getUsername();
		$userLoggedIn_e = $this->user_obj->getUsernameE();
		$posts_table = $this->user_obj->getPostsTable();
		$comments = $this->user_obj->getCommentsTable();
       
		if($page == 1){
			$start = 0;
		}
		else{
			$start = ($page - 1) * $limit;
		}

		$str = ""; //string to return;
		$dataQuery = mysqli_query($this->con, "SELECT * FROM $posts_table WHERE deleted='no' ORDER BY date_added DESC ");

		if(mysqli_num_rows($dataQuery) > 0){
		    $crypt = new Crypt();
		    $txt_rep = new TxtReplace();
			$num_iterations = 0; //Number of results checked (not necessarily posted)
			$count = 0; //number results loaded

			while($arr=mysqli_fetch_array($dataQuery)){
				$body = $arr['body'];
				$added_by = $arr['added_by'];
				$date_time = $arr['date_added'];
				$global_id = $arr['global_id'];
				$u_f = $arr['added_by'];
				
				$u_f_e = $crypt->EncryptU($u_f);

				//HTML Cleaning
				$body = $crypt->decryptString($arr['body'], $u_f_e."98634jghajGMHBSsd8712ugyjbdu6g!!DHnahvgA__ad++jhas%%adf");
				$body = $txt_rep->replace($body);
				$body = $txt_rep->entities($body);
				$body = $txt_rep->replaceLineBreak($body);
				
				$global_id = $txt_rep->entities($arr['global_id']);

				//prepare posts that are not posted to a user.

				if($arr['user_to'] == "0000"){
					$user_to = "";
					$u_t = "";
				}
				else{
				    $arr['user_to_e'] = $crypt->EncryptU($arr['user_to']);
					$user_to_obj = new User($this->con,$arr['user_to'], $arr['user_to_e']);
					$user_to_name = $user_to_obj->getFirstAndLastNameFast();
					switch($lang){
                        case("en"):
                        		$user_to = " to <a href='" . bin2hex($arr['user_to_e']) . "'>" . $user_to_name . "</a>";
                                    break;
                        case("es"):
                        		$user_to = " a <a href='" . bin2hex($arr['user_to_e']). "'>" . $user_to_name . "</a>";
                                    break;
					}
					$u_t = $arr['user_to'];
				}

				$user_logged_obj =  new User($this->con, $userLoggedIn, $userLoggedIn_e); //TODO: User is loaded twice!
				if($user_logged_obj->isFriend($added_by) || $userLoggedIn == $added_by){
					//if account posting is open, display, if it was closed, do not display
					$added_by_e = $crypt->EncryptU($added_by);
					$added_by_obj = new User($this->con, $added_by, $added_by_e);
					if($added_by_obj->isClosed()){
						continue;
					}

					if($num_iterations++ < $start) continue; //This is so it loads only FROM the start until the limit.

					//Once 10 posts have been loaded, then break, Load UNTIL the limit.

					if($count > $limit) {
						break;
					}
					else {
						$count ++;
					}
					
					if($userLoggedIn == $arr['added_by']){
						$delete_button = "<button class='delete_button btn-danger' id = 'post$global_id'>x</button>";
					}
					else if($userLoggedIn == $arr['user_to']){
						$delete_button = "<button class='delete_button btn-danger' id = 'post$global_id'>x</button>";
					}
					else $delete_button = "";

					//Select info of poster
					//TODO: use objects
					$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by_e'");
					$user_arr = mysqli_fetch_array($user_details_query);
					$first_name = $txt_rep->entities($user_arr['first_name']);
					$last_name = $txt_rep->entities($user_arr['last_name']);
					$profile_pic = $txt_rep->entities($added_by_obj->getProfilePicFast());

					?>
					<script>
						function toggle<?php echo $global_id; ?>() {

							var target = $(event.target);
							if(!target.is("a") && !target.is(":button")) {
								var element = document.getElementById("toggleComment<?php echo $global_id; ?>");

								if(element.style.display == "block"){
									element.style.display = "none";
								}
								else{
									element.style.display = "block";
								}
							}

						}
					</script>

					<?php



					$comment_check = mysqli_query($this->con, "SELECT * FROM $comments WHERE post_id ='$global_id'");
					$comment_check_num = mysqli_num_rows($comment_check);

					//Create the TimeFrame stamps

					$date_time_now = date("Y-m-d H:i:s");
					$start_date = new DateTime($date_time); //Time of post
					$end_date = new DateTime($date_time_now); //Current time
					$interval = $start_date->diff($end_date); //Difference between dates

					$time_stamp = new TimeStamp();
					$time_message = $time_stamp->getTimeStamp($interval);


					//Accumulate all posts.

					
						switch ($lang){
							
						    case("en"): {
						        $str .= "<div class='status_post' onClick='javascript:toggle$global_id();'>
                								<div class='post_profile_pic'>
                									<img src='$profile_pic' width='50px' height='50px'>
                								</div>
                								
                								<div class='posted_by' style='color:#ACACAC;'>
                									<a href='".bin2hex($added_by_e)."'> $first_name $last_name </a> $user_to - &nbsp;$time_message &nbsp;&nbsp; $delete_button
                								</div>
                								<div id='post_body'>
                									$body
                									
                								</div>
                								
                								<div class='newsfeedPostOptions'>
                                                    <img src='assets/images/icons/comments.png'>
                                                     <h1>Comments&nbsp($comment_check_num)</h1>
                									<iframe src='like.php?post_id=$global_id' scrolling='no'></iframe>
                								</div>
                								
                							</div>
                							<div class='post_comment' id='toggleComment$global_id' style='display:none;'>
                								<!-- iframe is the window that shows you a different page -->
                								<iframe src='comment_frame.php?post_id=$global_id' id='comment_iframe' frameborder='0'></iframe>
                							</div>
                							
                							<hr>";
								?>
							       	<script>
										$(document).ready(function(){
											$('#post<?php echo $global_id; ?>').on('click', function(){
												bootbox.confirm("Do you want to delete this post?", function(result){
													$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $global_id; ?>&u_f=<?php echo bin2hex($crypt->EncryptU($u_f)); ?>&u_t=<?php echo bin2hex($crypt->EncryptU($u_t)); ?>", {result:result});
													
													if(result) location.reload();
												});
											});
										});
									</script>
								<?php 
						        break;
						    }
                            case("es"):{
                                $str .= "<div class='status_post' onClick='javascript:toggle$global_id();'>
                								<div class='post_profile_pic'>
                									<img src='$profile_pic' width='50px' height='50px'>
                								</div>
                								
                								<div class='posted_by' style='color:#ACACAC;'>
                									<a href='".bin2hex($added_by_e)."'> $first_name $last_name </a> $user_to - &nbsp;$time_message &nbsp;&nbsp; $delete_button
                								</div>
                								<div id='post_body'>
                									$body
                									
                								</div>
                								
                								<div class='newsfeedPostOptions'>
                                                    <img src='assets/images/icons/comments.png'>
                									<h1>($comment_check_num)&nbsp Comentarios</h1>
                									<iframe src='like.php?post_id=$global_id' scrolling='no'></iframe>
                								</div>
                								
                							</div>
                							<div class='post_comment' id='toggleComment$global_id' style='display:none;'>
                								<!-- iframe is the window that shows you a different page ---index.php----->
                								<iframe src='comment_frame.php?post_id=$global_id' id='comment_iframe' onload='resizeIframe(this);' frameborder='0'></iframe>
                							</div>
                							
                							<hr>";
								?>
									<script>
    									function resizeIframe(obj){
    									     obj.style.height = 0;
    									     obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
    									  }
										$(document).ready(function(){
											$('#post<?php echo $global_id; ?>').on('click', function(){
												bootbox.confirm("¿Quieres borrar esta publicación?", function(result){
													$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $global_id; ?>&u_f=<?php echo bin2hex($crypt->EncryptU($u_f)); ?>&u_t=<?php echo bin2hex($crypt->EncryptU($u_t)); ?>", {result:result});
													
													if(result) location.reload();
												});
											});
										});
									</script>
								<?php 
								break;
                            }
						}

				}

				
			} //End While Loop
				if($count > $limit){ $str.= "<input type='hidden' class='nextPage' value='" . ($page + 1) ."'>
												<input type='hidden' class='noMorePosts' value='false'>";}
				else{
					switch ($lang){
						
						case("en"):
							$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'> No more posts to show.</p>";
							break;
							
						case("es"):
							$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'> No más publicaciones.</p>";
							break;
					}
					
				}
		}

		echo $str;
	}


	public function loadProfilePosts($data, $limit){
	    $crypt = new Crypt();
		$lang = $_SESSION['lang'];
		$page = $data['page'];
		$profileUser = $data['profileUsername']; //Who is the owner of the profile page visited.
		$profileUser_e = $crypt->EncryptU($profileUser);
		$userLoggedIn = $this->user_obj->getUsername();
		$userLoggedIn_e = $this->user_obj->getUsernameE();
        
		$profileUser_obj = new User($this->con, $profileUser, $profileUser_e);
		$posts = $profileUser_obj->getPostsTable();
		$comments = $profileUser_obj->getCommentsTable();

		if($page == 1){
			$start = 0;
		}
		else{
			$start = ($page - 1) * $limit;
		}

		$str = ""; //string to return;
		$dataQuery = mysqli_query($this->con, "SELECT * FROM $posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='0000') OR user_to = '$profileUser') ORDER BY date_added DESC "); //Here we select the posts that are posted to a profile  or are from the profile owner TO NONE.

		if(mysqli_num_rows($dataQuery) > 0){

			$num_iterations = 0; //Number of results checked (not necessarily posted)
			$count = 0; //number results loaded

			while($arr=mysqli_fetch_array($dataQuery)){
				$body = $arr['body'];
				$added_by = $arr['added_by'];
				$added_by_e = $crypt->EncryptU($added_by);
				$added_by_obj = new User($this->con, $added_by, $added_by_e);
				$date_time = $arr['date_added'];
				$global_id = $arr['global_id'];
				$u_f = $arr['added_by'];
				
				$u_f_e = $crypt->EncryptU($u_f);

				//HTML Cleaning
				$txt_rep = new TxtReplace();
				$body = $crypt->decryptString($arr['body'], $u_f_e."98634jghajGMHBSsd8712ugyjbdu6g!!DHnahvgA__ad++jhas%%adf");
				$body = $txt_rep->replace($body);
				$body = $txt_rep->entities($body);
				$body = $txt_rep->replaceLineBreak($body);

				
				//prepare posts that are not posted to a user.

				if($arr['user_to'] == "0000"){
					$user_to = "";
					$u_t = "";
				}
				else{
				    $arr['user_to_e'] = $crypt->EncryptU($arr['user_to']);
					$user_to_obj = new User($this->con,$arr['user_to'], $arr['user_to_e']);
					$user_to_name = $user_to_obj->getFirstAndLastNameFast();
					switch($lang){
					    case("en"):
					    		$user_to = " to <a href='" . bin2hex($arr['user_to_e']) . "'>" . $user_to_name . "</a>";
					        break;
					    case("es"):
					    		$user_to = " a <a href='" . bin2hex($arr['user_to_e']) . "'>" . $user_to_name . "</a>";
					        break;
					}
					$u_t = $arr['user_to'];
				}


				$user_logged_obj =  new User($this->con, $userLoggedIn, $userLoggedIn_e); 
				if($user_logged_obj->isFriend($added_by) || $userLoggedIn == $added_by){

					//if account posting is open, display, if it was closed, do not display


					if($num_iterations++ < $start) continue; //This is so it loads only FROM the start until the limit.

					//Once N posts have been loaded, then break, Load UNTIL the limit.

					if($count > $limit) {
						break;
					}
					else {
						$count ++;
					}

					if($userLoggedIn == $arr['added_by']){
						$delete_button = "<button class='delete_button btn-danger' id = 'post$global_id'>X</button>";
					}
					else if($userLoggedIn == $arr['user_to']){
						$delete_button = "<button class='delete_button btn-danger' id = 'post$global_id'>X</button>";
					}
					else $delete_button = "";

					//Select info of poster

					$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by_e'");
					$user_arr = mysqli_fetch_array($user_details_query);
					$first_name = $txt_rep->entities($user_arr['first_name']);
					$last_name = $txt_rep->entities($user_arr['last_name']);
					$profile_pic = $txt_rep->entities($added_by_obj->getProfilePicFast());

					?>
					<script>
						function toggle<?php echo $global_id; ?>() {

							var target = $(event.target);
							if(!target.is("a") && !target.is(":button")) {
								var element = document.getElementById("toggleComment<?php echo $global_id; ?>");

								if(element.style.display == "block"){
									element.style.display = "none";
								}
								else{
									element.style.display = "block";
								}
							}

						}
					</script>

					<?php

					$comment_check = mysqli_query($this->con, "SELECT * FROM $comments WHERE post_id ='$global_id'");
					$comment_check_num = mysqli_num_rows($comment_check);

					//Create the TimeFrame stamps

					$date_time_now = date("Y-m-d H:i:s");
					$start_date = new DateTime($date_time); //Time of post
					$end_date = new DateTime($date_time_now); //Current time
					$interval = $start_date->diff($end_date); //Difference between dates

					$time_stamp = new TimeStamp();
					$time_message = $time_stamp->getTimeStamp($interval);

					//Accumulate all posts.
					switch ($lang){
						
						case("en"):
							$str .= "<div class='status_post' onClick='javascript:toggle$global_id();'>
							<div class='post_profile_pic'>
							<img src='$profile_pic' width='50px' height='50px'>
							</div>
							
							<div class='posted_by' style='color:#ACACAC;'>
							<a href='". bin2hex($txt_rep->entities($crypt->EncryptU($added_by))) ."'> $first_name $last_name </a> $user_to  - &nbsp; $time_message &nbsp;&nbsp; $delete_button
							</div>
							<div id='post_body'>
							$body<br>
							<br>
							<br>
							</div>
							
							<div class='newsfeedPostOptions'>
							<img src='assets/images/icons/comments.png'>
							<h1>($comment_check_num) Comments</h1>
							<iframe src='like.php?post_id=$global_id' scrolling='no'></iframe>
							</div>
							
							</div>
							<div class='post_comment' id='toggleComment$global_id' style='display:none;'>
							<!-- iframe is the window that shows you a different page -->
							<iframe src='comment_frame.php?post_id=$global_id' id='comment_iframe' frameborder='0' scrolling='yes'></iframe>
							</div>
							
							<hr>";
							break;
							
						case("es"):
							$str .= "<div class='status_post' onClick='javascript:toggle$global_id();'>
							<div class='post_profile_pic'>
							<img src='$profile_pic' width='50px' height='50px'>
							</div>
							
							<div class='posted_by' style='color:#ACACAC;'>
							<a href='". bin2hex($txt_rep->entities($crypt->EncryptU($added_by))) ."'> $first_name $last_name </a> $user_to  - &nbsp; $time_message &nbsp;&nbsp; $delete_button
							</div>
							<div id='post_body'>
							$body<br>
							<br>
							<br>
							</div>
							
							<div class='newsfeedPostOptions'>
							<img src='assets/images/icons/comments.png'>
							<h1>($comment_check_num) Comentarios</h1>
							<iframe src='like.php?post_id=$global_id' scrolling='no'></iframe>
							</div>
							
							</div>
							<div class='post_comment' id='toggleComment$global_id' style='display:none;'>
							<!-- iframe is the window that shows you a different page -->
							<iframe src='comment_frame.php?post_id=$global_id' id='comment_iframe' frameborder='0' scrolling='yes'></iframe>
							</div>
							
							<hr>";
							break;
					}

				}
				?>
				<?php 
					switch ($lang){
							 		
						case("en"):
				?>
							<script>
								$(document).ready(function(){
									$('#post<?php echo $global_id; ?>').on('click', function(){
										bootbox.confirm("Do you want to delete this post?", function(result){
											$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $global_id; ?>&u_f=<?php echo bin2hex($crypt->EncryptU($u_f)); ?>&u_t=<?php echo bin2hex($crypt->EncryptU($u_t)); ?>", {result:result});
		
											if(result) location.reload();
										});
									});
								});
							</script>
				<?php 
						        break;
						
						case("es"):
				?>
							<script>
								$(document).ready(function(){
									$('#post<?php echo $global_id; ?>').on('click', function(){
										bootbox.confirm("¿Quieres borrar esta publicación?", function(result){
											$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $global_id; ?>&u_f=<?php echo bin2hex($crypt->EncryptU($u_f)); ?>&u_t=<?php echo bin2hex($crypt->EncryptU($u_t)); ?>", {result:result});
		
											if(result) location.reload();
										});
									});
								});
							</script>
				<?php 
							break;
					}
				
			} //End While Loop
				if($count > $limit){ $str.= "<input type='hidden' class='nextPage' value='" . ($page + 1) ."'>
					<input type='hidden' class='noMorePosts' value='false'>";}
				else{
				    switch($lang){
				        case("en"):
				            $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'> No more posts to show.</p>";
				            break;
				        case("es"):
				            $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'> No más publicaciones.</p>";
				            break;
				    }   
				}
		}

		echo $str;
	}
	
	public function loadProfilePosts_public($data, $limit){
		$crypt = new Crypt();
		$lang = $_SESSION['lang'];
		$page = $data['page'];
		$profileUser = $this->user_obj->getUsername();//Who is the owner of the profile page visited.
		$profileUser_e = $this->user_obj->getUsernameE();
		
		$profileUser_obj = new User($this->con, $profileUser, $profileUser_e);
		$posts = $profileUser_obj->getPostsTable();
		$comments = $profileUser_obj->getCommentsTable();
		
		if($page == 1){
			$start = 0;
		}
		else{
			$start = ($page - 1) * $limit;
		}
		
		$str = ""; //string to return;
		$dataQuery = mysqli_query($this->con, "SELECT * FROM $posts WHERE deleted='no' AND ((added_by='$profileUser' AND user_to='0000') OR user_to = '$profileUser') ORDER BY date_added DESC "); //Here we select the posts that are posted to a profile  or are from the profile owner TO NONE.
		
		if(mysqli_num_rows($dataQuery) > 0){
			
			$num_iterations = 0; //Number of results checked (not necessarily posted)
			$count = 0; //number results loaded
			
			$settings =  new Settings($this->con, $profileUser, $profileUser_e);
			$prof_priv = $settings->getSettingsValues("profile_privacy");
			
			while($arr=mysqli_fetch_array($dataQuery)){
				$body = $arr['body'];
				$added_by = $arr['added_by'];
				$added_by_e = $crypt->EncryptU($added_by);
				$added_by_obj = new User($this->con, $added_by, $added_by_e);
				$date_time = $arr['date_added'];
				$global_id = $arr['global_id'];
				$u_f = $arr['added_by'];
				
				$u_f_e = $crypt->EncryptU($u_f);
				
				//HTML Cleaning
				$txt_rep = new TxtReplace();
				$body = $crypt->decryptString($arr['body'], $u_f_e."98634jghajGMHBSsd8712ugyjbdu6g!!DHnahvgA__ad++jhas%%adf");
				$body = $txt_rep->replace($body);
				$body = $txt_rep->entities($body);
				$body = $txt_rep->replaceLineBreak($body);
				
				//prepare posts that are not posted to a user.
				
				if($arr['user_to'] == "0000"){
					$user_to = "";
					$u_t = "";
				}
				else{
					continue;
					//TODO:UNCOMMENT BELOW to show third person posts' on a public wall, disabled for now 
// 					$arr['user_to_e'] = $crypt->EncryptU($arr['user_to']);
// 					$user_to_obj = new User($this->con,$arr['user_to'], $arr['user_to_e']);
// 					$user_to_name = $user_to_obj->getFirstAndLastNameFast();
// 					switch($lang){
// 						case("en"):
// 							$user_to = " to <a href='" . bin2hex($arr['user_to_e']) . "'>" . $user_to_name . "</a>";
// 							break;
// 						case("es"):
// 							$user_to = " a <a href='" . bin2hex($arr['user_to_e']) . "'>" . $user_to_name . "</a>";
// 							break;
// 					}
// 					$u_t = $arr['user_to'];
				}
				
				if($prof_priv === 0){
					//if account posting is open, display, if it was closed, do not display
					
					
					if($num_iterations++ < $start) continue; //This is so it loads only FROM the start until the limit.
					
					//Once N posts have been loaded, then break, Load UNTIL the limit.
					
					if($count > $limit) {
						break;
					}
					else {
						$count ++;
					}
					
					$delete_button = "";
					
					//Select info of poster
					
					$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by_e'");
					$user_arr = mysqli_fetch_array($user_details_query);
					$first_name = $txt_rep->entities($user_arr['first_name']);
					$last_name = $txt_rep->entities($user_arr['last_name']);
					$profile_pic = $txt_rep->entities($added_by_obj->getProfilePicFast());
					
					?>
					<script>
						function toggle<?php echo $global_id; ?>() {

							var target = $(event.target);
							if(!target.is("a") && !target.is(":button")) {
								var element = document.getElementById("toggleComment<?php echo $global_id; ?>");

								if(element.style.display == "block"){
									element.style.display = "none";
								}
								else{
									element.style.display = "block";
								}
							}

						}
					</script>

					<?php

					$comment_check = mysqli_query($this->con, "SELECT * FROM $comments WHERE post_id ='$global_id'");
					$comment_check_num = mysqli_num_rows($comment_check);

					//Create the TimeFrame stamps

					$date_time_now = date("Y-m-d H:i:s");
					$start_date = new DateTime($date_time); //Time of post
					$end_date = new DateTime($date_time_now); //Current time
					$interval = $start_date->diff($end_date); //Difference between dates

					$time_stamp = new TimeStamp();
					$time_message = $time_stamp->getTimeStamp($interval);

					//Accumulate all posts.
					switch ($lang){
						
						case("en"):
							$str .= "<div class='status_post' onClick='javascript:toggle$global_id();'>
							<div class='post_profile_pic'>
							<img src='$profile_pic' width='50px' height='50px'>
							</div>
							
							<div class='posted_by' style='color:#ACACAC;'>
							<a href='". bin2hex($txt_rep->entities($crypt->EncryptU($added_by))) ."'> $first_name $last_name </a> $user_to  - &nbsp; $time_message &nbsp;&nbsp; $delete_button
							</div>
							<div id='post_body'>
							$body<br>
							<br>
							<br>
							</div>
							
							<div class='newsfeedPostOptions'>
							<img src='assets/images/icons/comments.png'>
							<h1>($comment_check_num) Comments</h1>
							</div>
							
							</div>
							<div class='post_comment' id='toggleComment$global_id' style='display:none;'>
							<!-- iframe is the window that shows you a different page -->
							<iframe src='createaccount_frame.php' id='comment_iframe' frameborder='0' scrolling='yes'></iframe>
							</div>
							
							<hr>";
							break;
							
						case("es"):
							$str .= "<div class='status_post' onClick='javascript:toggle$global_id();'>
							<div class='post_profile_pic'>
							<img src='$profile_pic' width='50px' height='50px'>
							</div>
							
							<div class='posted_by' style='color:#ACACAC;'>
							<a href='". bin2hex($txt_rep->entities($crypt->EncryptU($added_by))) ."'> $first_name $last_name </a> $user_to  - &nbsp; $time_message &nbsp;&nbsp; $delete_button
							</div>
							<div id='post_body'>
							$body<br>
							<br>
							<br>
							</div>
							
							<div class='newsfeedPostOptions'>
							<img src='assets/images/icons/comments.png'>
							<h1>($comment_check_num) Comentarios</h1>
							</div>
							
							</div>
							<div class='post_comment' id='toggleComment$global_id' style='display:none;'>
							<!-- iframe is the window that shows you a different page -->
							<iframe src='createaccount_frame.php' id='comment_iframe' frameborder='0' scrolling='yes'></iframe>
							</div>
							
							<hr>";
							break;
					}

				}
				?>
				<?php 
					switch ($lang){
							 		
						case("en"):
				?>
							<script>
								$(document).ready(function(){
									$('#post<?php echo $global_id; ?>').on('click', function(){
										bootbox.confirm("Do you want to delete this post?", function(result){
											$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $global_id; ?>&u_f=<?php echo bin2hex($crypt->EncryptU($u_f)); ?>&u_t=<?php echo bin2hex($crypt->EncryptU($u_t)); ?>", {result:result});
		
											if(result) location.reload();
										});
									});
								});
							</script>
				<?php 
						        break;
						
						case("es"):
				?>
							<script>
								$(document).ready(function(){
									$('#post<?php echo $global_id; ?>').on('click', function(){
										bootbox.confirm("¿Quieres borrar esta publicación?", function(result){
											$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $global_id; ?>&u_f=<?php echo bin2hex($crypt->EncryptU($u_f)); ?>&u_t=<?php echo bin2hex($crypt->EncryptU($u_t)); ?>", {result:result});
		
											if(result) location.reload();
										});
									});
								});
							</script>
				<?php 
							break;
					}
				
			} //End While Loop
				if($count > $limit){ $str.= "<input type='hidden' class='nextPage' value='" . ($page + 1) ."'>
					<input type='hidden' class='noMorePosts' value='false'>";}
				else{
				    switch($lang){
				        case("en"):
				            $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;clear: both;'> No more posts to show.</p>";
				            break;
				        case("es"):
				            $str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;clear: both;'> No más publicaciones.</p>";
				            break;
				    }   
				}
		}

		echo $str;
	}

	public function getSinglePost($post_id){
        $crypt = new Crypt();
        $lang = $_SESSION['lang'];
		//userLoggedIn is the creator of the instance Post, and it is the one that is trying to retrieve a single post. This value is verified in the creation of the class
    
		$userLoggedIn = $this->user_obj->getUsername();
		$userLoggedIn_e = $this->user_obj->getUsernameE();
		$posts = $this->user_obj->getPostsTable();
		$notifications = $this->user_obj->getNotificationsTable();
		$comments = $this->user_obj->getCommentsTable();

		$opened_query = mysqli_query($this->con, "UPDATE $notifications SET opened='yes' WHERE user_to = '$userLoggedIn' AND link LIKE '%=$post_id'");

		$str = ""; //string to return;

		$stmt = $this->con->prepare("SELECT * FROM $posts WHERE deleted='no' AND global_id = ?");

		$stmt->bind_param("s", $post_id);
		$stmt->execute();
		$dataQuery = $stmt->get_result();

		if(mysqli_num_rows($dataQuery) > 0){

			$arr=mysqli_fetch_array($dataQuery);
				$body = $arr['body'];
				$added_by = $arr['added_by'];
				$added_by_e = $crypt->EncryptU($added_by);
				$date_time = $arr['date_added'];
				$global_id = $arr['global_id'];
				$u_f = $arr['added_by'];

				$u_f_e = $crypt->EncryptU($u_f);
				
				//HTML Cleaning
				$txt_rep = new TxtReplace();
				$body = $crypt->decryptString($arr['body'], $u_f_e."98634jghajGMHBSsd8712ugyjbdu6g!!DHnahvgA__ad++jhas%%adf");
				$body = $txt_rep->replace($body);
				$body = $txt_rep->entities($body);
				$body = $txt_rep->replaceLineBreak($body);
				
				
				//prepare posts that are not posted to a user.

				if($arr['user_to'] == "0000"){
					$user_to = "";
					$u_t = "";
					$userto_string = "0000";
				}
				else{
				    $arr['user_to_e'] = $crypt->EncryptU($arr['user_to']);
					$user_to_obj = new User($this->con,$arr['user_to'], $arr['user_to_e']);
					$userto_string = $user_to_obj->getUsername();
					$user_to_name = $user_to_obj->getFirstAndLastNameFast();
					switch($lang){
					    case("en"):
					    		$user_to = " to <a href='" . bin2hex($txt_rep->entities($arr['user_to_e'])) . "'>" . $user_to_name . "</a>";
					        break;
					    case("es"):
					    		$user_to = " a <a href='" . bin2hex($txt_rep->entities($arr['user_to_e'])). "'>" . $user_to_name . "</a>";
					        break;
					}
					$u_t = $arr['user_to'];
				}

				$user_logged_obj =  new User($this->con, $userLoggedIn, $userLoggedIn_e); //TODO: User is loaded twice!
				if(($userto_string == "0000" && ($user_logged_obj->isFriend($added_by) || $userLoggedIn == $added_by)) || ($userto_string == $userLoggedIn || $user_logged_obj->isFriend($userto_string))){
				//if($user_logged_obj->isFriend($added_by) || $userLoggedIn == $added_by){

					//if account posting is open, display, if it was closed, do not display
                    $added_by_e = $crypt->EncryptU($added_by);
					$added_by_obj = new User($this->con, $added_by, $added_by_e);
					if($added_by_obj->isClosed()){
						return;
					}

					if($userLoggedIn == $added_by_obj->getUsername()){
						$delete_button = "<button class='delete_button btn-danger' id = 'post$global_id'>X</button>";
					} 
					else $delete_button = "";

					//Select info of poster

					$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE username='$added_by_e'");
					$user_arr = mysqli_fetch_array($user_details_query);
					$first_name = $txt_rep->entities($user_arr['first_name']);
					$last_name = $txt_rep->entities($user_arr['last_name']);
					$profile_pic = $txt_rep->entities($added_by_obj->getProfilePicFast());

					?>
					<script>
						function toggle<?php echo $global_id; ?>() {

							var target = $(event.target);
							if(!target.is("a")) {
								var element = document.getElementById("toggleComment<?php echo $global_id; ?>");

								if(element.style.display == "block"){
									element.style.display = "none";
								}
								else{
									element.style.display = "block";
								}
							}

						}
					</script>

					<?php

					$comment_check = mysqli_query($this->con, "SELECT * FROM $comments WHERE post_id ='$global_id'");
					$comment_check_num = mysqli_num_rows($comment_check);

					//Create the TimeFrame stamps

					$date_time_now = date("Y-m-d H:i:s");
					$start_date = new DateTime($date_time); //Time of post
					$end_date = new DateTime($date_time_now); //Current time
					$interval = $start_date->diff($end_date); //Difference between dates

					$time_stamp = new TimeStamp();
					$time_message = $time_stamp->getTimeStamp($interval);

					//Accumulate all posts.
					
					switch ($lang){
							 		
						case("en"):
						    {
						        $str .= "<div class='status_post' onClick='javascript:toggle$global_id();'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50px' height='50px'>
								</div>
								
								<div class='posted_by' style='color:#ACACAC;'>
								<a href='".bin2hex($txt_rep->entities($crypt->EncryptU($added_by)))."'> $first_name $last_name </a> $user_to &nbsp;$time_message &nbsp;&nbsp; $delete_button
								</div>
								<div id='post_body'>
									$body
								</div>
								
								<div class='newsfeedPostOptions'>
									($comment_check_num)&nbsp Comments;&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$global_id' scrolling='no'></iframe>
								</div>
								
							</div>
							<div class='post_comment' id='toggleComment$global_id' style='display:none;'>
								<!-- iframe is the window that shows you a different page -->
								<iframe src='comment_frame.php?post_id=$global_id' id='comment_iframe' frameborder='0' scrolling='yes'></iframe>
							</div>
							
							<hr>";
						    
				?>
							<script>
								$(document).ready(function(){
									$('#post<?php echo $global_id; ?>').on('click', function(){
										bootbox.confirm("Do you want to delete this post?", function(result){
											$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $global_id; ?>&u_f=<?php echo bin2hex($crypt->EncryptU($u_f)); ?>&u_t=<?php echo bin2hex($crypt->EncryptU($u_t)); ?>", {result:result});
		
											if(result) location.reload();
										});
									});
								});
							</script>
				<?php 
						        break;
						    }
                        case("es"): {
                            $str .= "<div class='status_post' onClick='javascript:toggle$global_id();'>
								<div class='post_profile_pic'>
									<img src='$profile_pic' width='50px' height='50px'>
								</div>
								
								<div class='posted_by' style='color:#ACACAC;'>
									<a href='".bin2hex($txt_rep->entities($crypt->EncryptU($added_by)))."'> $first_name $last_name </a> $user_to &nbsp;$time_message &nbsp;&nbsp; $delete_button
								</div>
								<div id='post_body'>
									$body
								</div>
								
								<div class='newsfeedPostOptions'>
									($comment_check_num)&nbsp Comentarios;&nbsp;&nbsp;&nbsp;
									<iframe src='like.php?post_id=$global_id' scrolling='no'></iframe>
								</div>
								
							</div>
							<div class='post_comment' id='toggleComment$global_id' style='display:none;'>
								<!-- iframe is the window that shows you a different page -->
								<iframe src='comment_frame.php?post_id=$global_id' id='comment_iframe' frameborder='0' scrolling='yes'></iframe>
							</div>
							
							<hr>";
				?>
							<script>
								$(document).ready(function(){
									$('#post<?php echo $global_id; ?>').on('click', function(){
										bootbox.confirm("¿Quieres borrar esta publicación?", function(result){
											$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $global_id; ?>&u_f=<?php echo bin2hex($crypt->EncryptU($u_f)); ?>&u_t=<?php echo bin2hex($crypt->EncryptU($u_t)); ?>", {result:result});
		
											if(result) location.reload();
										});
									});
								});
							</script>
				<?php 
							break;
                        }
					}
				}
				else{
					echo "<p>404 post not found.</p>";
					return;
				}
	
		}
		else{
			echo "<p>404 post not found.</p>";
			return;
		}

		echo $str;
	}
}

?>