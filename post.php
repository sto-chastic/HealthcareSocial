<?php
	include("includes/header.php");

	if(isset($_GET['id'])){
		$id_temp = $_GET['id'];
		$stmt = $con->prepare("SELECT user_to FROM $posts WHERE deleted = 'no' AND global_id = ?");

		$stmt->bind_param("s", $id_temp);
		$stmt->execute();
		$verification_query = $stmt->get_result();

		if(mysqli_num_rows($verification_query) > 0)
			$id = $id_temp;
		else
			$id = "invalid";
	}
	else{
		$id = "invalid";
	}
	$stmt->close();
?>

<!--<div class="user_details column">
	<a href="<?php 
			//echo bin2hex($txt_rep->entities($userLoggedIn_e));
			?>"> <img src="<?php //echo $txt_rep->entities($user_obj->getProfilePicFast()); ?>"> </a>
	<div class="user_details_left_right">
		<a href="
			<?php 
				//echo $txt_rep->entities($userLoggedIn);
			 ?>
		">
			<?php 
				//echo $txt_rep->entities($user['first_name']) . " " . $txt_rep->entities($user['last_name']) . "<br>";
			 ?>
		 </a>
		 <div id='commsLikes'>
			 <?php
// 			 switch($lang){
// 			     case("en"):
// 			         echo "Posts: " . $txt_rep->entities($user['num_posts']) . "<br>";
// 			         echo "Likes: " . $txt_rep->entities($user['num_likes']);
// 			         break;
// 			     case("es"):
// 			         echo "Publicaciones: " . $txt_rep->entities($user['num_posts']) . "<br>";
// 			         echo "Me gusta: " . $txt_rep->entities($user['num_likes']);
// 			         break;
// 			 }	 	
			  ?>
		  </div>
	</div>
</div> -->

<div class="main_column column" id="main_column">
	<div class="posts_area">
		
		<?php
			if($id != "invalid"){
				$post = new Post($con, $userLoggedIn, $userLoggedIn_e);
				$post->getSinglePost($id);
			}
			else {
			    switch($lang){
			        case("en"):
			            echo "<p>404 post not found.</p>";
			            break;
			        case("es"):
			            echo "<p>404 publicación no encontrada.</p>";
			            break;
			    }
			}
				
		?>

	</div>
</div>
