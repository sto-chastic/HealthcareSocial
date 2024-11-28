
<?php include("includes/header.php");

$profile_id = bin2hex($txt_rep->entities($user_obj->username_e));
$imgSrc = "";
$result_path = "";
$msg = "";
$name_short="";

ini_set('post_max_size', '8M'); //or bigger by multiple files
ini_set('upload_max_filesize', '8M');
ini_set('max_file_uploads', 1);

$sizeLimit = 8000000;

$pth_str = explode("/",$_SERVER['DOCUMENT_ROOT']);

// $path = "";
// for($i=0;$i<= count($pth_str)-2 ;$i++){
// 	$path .= $pth_str[$i] . "/";
// }
$path = "/home/bitnami/";
$path .= "images/profile_pics/";

/***********************************************************
	0 - Remove The Temp image if it exists
***********************************************************/
	if (!isset($_POST['x']) && !isset($_FILES['image']['name']) ){
		//Delete users temp image
		$temppath = $path.$profile_id.'_temp.png';
		//$temppath = 'assets/images/profile_pics/'.$profile_id.'_temp.png';
		if (file_exists ($temppath)){ @unlink($temppath); }
		//free up temp table
		$del_pic_query_temp = mysqli_query($con, "DELETE FROM `temp_imgs` WHERE `username` = '$userLoggedIn_e' ");
	} 


if(isset($_FILES['image']['name'])){
	
/***********************************************************
	1 - Upload Original Image To Server
***********************************************************/	
	//Get Name | Size | Temp Location
		$name_short = $txt_rep->entities($_FILES['image']['name']);
		$ImageName = $txt_rep->entities($_FILES['image']['name']);
		$ImageSize = $txt_rep->entities($_FILES['image']['size']);
		
		if ($ImageSize > $sizeLimit){
			switch($lang){
				case("en"):
					die ('Image is too large. Max. size: 8M. Click <a href="upload.php">here</a> and try with another image.');
					break;
				case("es"):
					die ('La imagen es muy grande. Tamaño max.: 8M. Has click <a href="upload.php">aquí</a> e intenta con otra imagen.');
					break;
			}
			
		}
		$ImageTempName = $txt_rep->entities($_FILES['image']['tmp_name']);
	//Get File Ext  
		$ImageType = @explode('/', $_FILES['image']['type']);
		$type_temp = $ImageType[1]; //file type	

	
		switch ($type_temp){
			case("jpeg"):
				$type = "jpeg";
				$in = 'imagecreatefromjpeg';
				break;
			case("png"):
				$type = "png";
				$in = 'imagecreatefrompng';
				break;
			default:
				switch($lang){
					case("en"):
						die ('Unsupported image type. Only "PNG" or "JPG" are supported. Click <a href="upload.php">here</a> and try with another image.');
						break;
					case("es"):
						die ('Tipo de imagen no soportada. Sólo se soportan imagens tipo "PNG" o "JPG". Has click <a href="upload.php">aquí</a> e intenta con otra imagen.');
						break;
				}
		}
		
	//Checks if image is corrupted
		if (!@$in($ImageTempName)){
			die("Bad image");
		}
		
	//Get EXIF rotation info (Whitelisted later)
		try{
			$exif = @exif_read_data($ImageTempName);
		} catch (Exception $e) {
			$exif = false;
		}
		
	//Set Upload directory
		//$uploaddir = $_SERVER['DOCUMENT_ROOT'].'/Confidr/assets/images/profile_pics';
		$uploaddir = $path;
	//Set File name	
		$file_temp_name = $profile_id.'_original.'.md5(time()).'n'.$type; //the temp file name
		$fullpath = $uploaddir.$file_temp_name; // the temp file path
		//$fullpath = $uploaddir."/".$file_temp_name; // the temp file path
		$file_name = $profile_id.'_temp.png'; //$profile_id.'_temp.'.$type; // for the final resized image
		$fullpath_2 = $uploaddir.$file_name; //for the final resized image
		//$fullpath_2 = $uploaddir."/".$file_name; //for the final resized image
	//Move the file to correct location
		$move = move_uploaded_file($ImageTempName ,$fullpath) ;
		
		$noExecMode = 0644; //* (owning) User: read & write
							//* Group: read
							//* Other: read
		chmod($fullpath, $noExecMode);
		//chmod($fullpath, 0777); 
		
		
		//Check for valid uplaod
		if (!$move) {
		    die ('File did not upload');
	
		} else { 
			//$imgSrc= "assets/images/profile_pics/".$file_name; // the image to display in crop area
			$imgSrc_temp_path = $path.$file_name;
			
			//insert temp in database TEMPORALY
			$insert_pic_query_temp = mysqli_query($con, "INSERT INTO `temp_imgs` (`username`, `profile_pic`) VALUES ('$userLoggedIn_e', '$imgSrc_temp_path') ");
			echo mysqli_error($con);
			$imgSrc = $user_obj->getProfilePicFast() . "&t=1";
			$msg= "Upload Complete!";  	//message to page
			$src = $file_name;	 		//the file name to post from cropping form to the resize
			$name_short = $src;
		
		} 

/***********************************************************
	2  - Resize The Image To Fit In Cropping Area
***********************************************************/		
		//get the uploaded image size	
			clearstatcache();
			$original_size = getimagesize($fullpath);
			
			if(isset($exif['Orientation'])){
        			if($exif['Orientation'] >= 5 && $exif['Orientation'] <= 8){
        				$original_width = $original_size[1];
        				$original_height = $original_size[0];
        				// Specify The new size
        				$main_height = 300; // set the width of the image
        				$main_width = $original_width / ($original_height/ $main_height);	// this sets the height in ratio
        			}
        			else if($exif['Orientation'] == 1){
        				$original_width = $original_size[0];
        				$original_height = $original_size[1];
        				// Specify The new size
        				$main_height = 300; // set the width of the image
        				$main_width = $original_width / ($original_height/ $main_height);	// this sets the height in ratio
        			}
        			else{
        				$original_width = $original_size[0];
        				$original_height = $original_size[1];
        				// Specify The new size
        				$main_width = 500; // set the width of the image
        				$main_height = $original_height / ($original_width / $main_width);	// this sets the height in ratio
        				
        				if($main_height > 300){
        					// Specify The new size
        					$main_height = 300; // set the width of the image
        					$main_width = $original_width / ($original_height/ $main_height);	// this sets the height in ratio
        				}
        			}
			}
			else{
			    $original_width = $original_size[0];
			    $original_height = $original_size[1];
			    // Specify The new size
			    $main_width = 500; // set the width of the image
			    $main_height = $original_height / ($original_width / $main_width);	// this sets the height in ratio
			    
			    if($main_height > 300){
			        // Specify The new size
			        $main_height = 300; // set the width of the image
			        $main_width = $original_width / ($original_height/ $main_height);	// this sets the height in ratio
			    }
			}
			//echo $main_width ."<br>";
			//echo $main_height."<br>";
			//echo $original_width."<br>";
			//echo $original_height."<br>";


		//create new image using correct php func			
			if($_FILES["image"]["type"] == "image/gif"){
				//$src2 = imagecreatefromgif($fullpath);
			}elseif($_FILES["image"]["type"] == "image/jpeg" || $_FILES["image"]["type"] == "image/pjpeg"){
				$src2 = imagecreatefromjpeg($fullpath);
				
			}elseif($_FILES["image"]["type"] == "image/png"){
				$src2 = imagecreatefrompng($fullpath);
			}else{ 
			    switch($lang){
			        case("en"):
			            $msg='"There was an error uploading the file. Please upload a .jpg, or .png file." <br/>';
			            break;
			        case("es");
			            $msg ='"Lo sentimos pero hay un error en la subida. Por favor sube un archivo .jpg, o .png ." <br/>';
			        break;
			    }
				
			}

		// Add EXIF Info  (Whitelisted)
			if($exif!==false){
				
				# Get orientation
			    if(isset($exif['Orientation'])){
				    $orientation = $exif['Orientation'];
			    
    				    # Manipulate image
        				switch ($orientation) {
        					case 2:
        						imageflip($src2, IMG_FLIP_HORIZONTAL);
        						break;
        					case 3:
        						$src2= imagerotate($src2, 180, 0);
        						break;
        					case 4:
        						imageflip($src2, IMG_FLIP_VERTICAL);
        						break;
        					case 5:
        						$src2 = imagerotate($src2, -90, 0);
        						imageflip($src2, IMG_FLIP_HORIZONTAL);
        						break;
        					case 6:
        						$src2= imagerotate($src2, -90, 0);
        						break;
        					case 7:
        						$src2= imagerotate($src2, 90, 0);
        						imageflip($src2, IMG_FLIP_HORIZONTAL);
        						break;
        					case 8:
        						$src2= imagerotate($src2, 90, 0);
        						break;
        				}
			    }
			}
		
		//create the new resized image
			$main = imagecreatetruecolor($main_width,$main_height);
			
			imagecopyresampled($main,$src2,0, 0, 0, 0,$main_width,$main_height,$original_width,$original_height);
		//upload new version
			$main_temp = $fullpath_2;
			imagepng($main, $main_temp, 0);//la guardo como png
			
			$noExecMode = 0644; //* (owning) User: read & write
								//* Group: read
								//* Other: read
			chmod($main_temp, $noExecMode);
		//free up memory
		    
			imagedestroy($src2);
			imagedestroy($main);
			//imagedestroy($fullpath);
			@ unlink($fullpath); // delete the original upload
									
} else{
        //file dont load
        switch($lang){
            case("en"):
                $name_short ="File did not upload";
                break;
            case("es");
                $name_short ="Archivo no cargado";
                break;
    }
    
    
}//ADD Image 	

/***********************************************************
	3- Cropping & Converting The Image To Jpg
***********************************************************/
if (isset($_POST['x'])){
	
	//the file type posted
		$type = $_POST['type'];	
	//the image src
		//$src = 'assets/images/profile_pics/'.$_POST['src'];
		$src = $path.$_POST['src'];
		$finalname = $profile_id.md5(time());	
		
	if($type == 'png' || $type == 'PNG'){
		
		//the target dimensions 220x220 instead of 150x150
		$targ_w = $targ_h = 220; //$targ_w = $targ_h = 150;
		//quality of the output
		$compression = 0;
		//create a cropped copy of the image
		$img_r = imagecreatefrompng($src);
		$dst_r = imagecreatetruecolor( $targ_w, $targ_h );
		imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
				$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
		imagepng($dst_r,$path.$finalname."n.png", 0);//la guardo como png
		//imagepng($dst_r, "assets/images/profile_pics/".$finalname."n.png", 0);//la guardo como png
		//imagejpeg($dst_r, "assets/images/profile_pics/".$finalname."n.png", 90); 
						
	}
	else{
		die("Unsupported format.");
	}
	$noExecMode = 0644; //* (owning) User: read & write
						//* Group: read
						//* Other: read
	chmod($path.$finalname."n.png", $noExecMode);
	//chmod("assets/images/profile_pics/".$finalname."n.png", $noExecMode);

	//free up memory
		imagedestroy($img_r); // free up memory
		imagedestroy($dst_r); //free up memory
		@ unlink($src); // delete the original upload					
	
	//return cropped image to page	
		$result_path = $path.$finalname."n.png";
	//$result_path ="assets/images/profile_pics/".$finalname."n.png";
	
	//free up temp table
		$del_pic_query_temp = mysqli_query($con, "DELETE FROM `temp_imgs` WHERE `username` = '$userLoggedIn_e' ");
		
	//Delete previous image
		if (strpos($user_obj->getProfilePicPATH(), 'defaults') === false) {
		    @unlink($user_obj->getProfilePicPATH());
		}
		
		
	//Insert image into database
	$insert_pic_query = mysqli_query($con, "UPDATE users SET profile_pic='$result_path' WHERE username='$userLoggedIn_e'");
	header("Location: index.php");
														
}// post x
?>

<div class="main_column column" style="padding-bottom:10px;">
    <div class="title_tabs" ><?php switch($lang){
            		    case("en"):
            		        echo "Settings";
            		        break;
            		    case("es");
                            echo "Configuración";
                            break;
            		}?></div>
        <!-- ---------------cambios victor -----------  -->   		
    	<div class="main_settings upload_div">

    		
        	<div id="formExample">	
        	    
            	    <form action="upload.php" method="post"  enctype="multipart/form-data">
            	    		<?php 
            	    		if (isset($_SESSION['lang']))
            	    		    $lang = $_SESSION['lang'];
            	    		    else
            	    		        $lang = "es";       	    		        
                        	        switch($lang) {
                        	            case("en"):
                        	                echo "
                                            <h1>Change your picture profile</h1>";
                        	                break;
                        	            case("es"):
                        	                echo "
                                            <h1>Cambia tu foto de perfil</h1>";
                        	                break;
                        	        }
            	    		?>
            	        <input type="file" name="image" id="image" class="inputfile"/>
            			<label for="image"><?php switch($lang){
            	            case("en"):
            	                echo "Submit a photo";
                               break;
            	            case ("es"):
            	                echo "Selecciona el archivo";
            	                break;
            	        }?>
            			</label>
            				<script>
                 				$(document).ready(function(){
                 				    $('#image').click(function(){
                 				    		$('#img_status').html('<?php  //$name_short = $_FILES['image']['name'];
						            	        switch($lang){
							            	        	case("en"):
							            	        		echo "Image selected.";
							            	        		break;
							            	        	case ("es"):
							            	        		echo "Imagen seleccionada.";
							            	        		break;
						            	        }
	                				    		?>');
	                				    		$('#charge').css({"display" : "inline-block"});
                 				    });
                 				    
                 				});	
                			</script>   
            			<h3 id="img_status"> <?php echo $name_short;?> </h3>
            	        <input type="submit" style="display: none" id="charge" value="<?php switch($lang){
            	            case("en"):
                               echo "Submit";
                               break;
            	            case ("es"):
            	                echo "Cargar";
            	                break;
            	        }?>" />
            	    </form><br />
            	     <p><b> <?php echo $msg;?> </b></p>
            	    
            	</div> <!-- Form-->  
    	
    
        <?php
        if($imgSrc){ //if an image has been uploaded display cropping area?>
    	    <script>
    			$('#formExample').hide();
    			$('.title_search').css({ height: 166, paddingTop: 97, backgroundSize: '100%'});
    	    </script>    	    
    	    <div id="CroppingContainer">  
    	    
    	        <div id="CroppingArea">	
    	            <img src="<?=$imgSrc?>" border="0" id="jcrop_target"  />
    	        </div>  
    
    	        <div id="InfoArea">	
    	           <p>
    	           <?php 
    	               switch($lang){
    	                   case("en"):
    	                       echo "
                                    <b>Crop Profile Image</b><br /><br />
                    	                    Crop / resize your uploaded profile image. <br />
                    	                    Once you are happy with your profile image then please click save.
                                    ";
    	                       break;
    	                   case("es"):
    	                       echo "
                                    <b>Recortar imagen de perfil</b><br /><br />
                    	                    Recorta/ajusta tu imagen de perfil. <br />
                    	                    Una vez estés satisfecho con tu foto por favor haz click en guardar.
                                    ";
    	                       break;
    	               }
    	           ?>         
    	                
    	           </p>
    	        </div>  
    
    	        <br/>
    
    	        <div id="CropImageForm"  >  
    	            <form action="upload.php" method="post" onsubmit="return checkCoords();">
    	                <input type="hidden" id="x" name="x" />
    	                <input type="hidden" id="y" name="y" />
    	                <input type="hidden" id="w" name="w" />
    	                <input type="hidden" id="h" name="h" />
    	                <input type="hidden" value="png" name="type" /> <?php // $type ?> 
    	                <input type="hidden" value="<?=$src?>" name="src" />
    	                <input type="submit" value="<?php switch($lang){
    	            		    case("en"):
    	            		        echo "Save";
    	            		        break;
    	            		    case("es"):
    	            		        echo "Guardar";
    	            		        break;
    	            		}?>"  id="up_photo" />
    	            </form>
    	        </div>
    
    	        <div id="CropImageForm2" >  
    	            <form action="upload.php" method="post" onsubmit="return cancelCrop();">
    	                <input type="submit" value="<?php switch($lang){
    	            		    case("en"):
    	            		        echo "Cancel Crop";
    	            		        break;
    	            		    case("es"):
    	            		        echo "Cancelar";
    	            		        break;
    	            		}?>" id="up_photo_cancel" />
    	            </form>
    	        </div>            
    	            
    	    </div><!-- CroppingContainer -->
    	<?php 
    	} 
    	else {?>
    	    <script>
    	    $(document).ready(function(){
    	        //header appearance 
    	        $('.grey_banner').fadeTo(1500,0.8);
    	        $('.title_text').delay(1500).animate({ paddingTop: 98}, 2000);
    	        $('.wrapper').delay(1500).animate({ marginTop: 166}, 2000);	
    	    });  
    	        
    	     </script>      
    	<?php } ?>
    </div>
</div> 
 
 
 
 
 <?php if($result_path) {
	 ?>
	 
     <img src="<?=$result_path?>" style="position:relative; margin:10px auto; width:150px; height:150px;" />
	 
 <?php } ?>
 

