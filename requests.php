<?php
include ('includes/header.php');

if(isset($_SESSION['lang'])){
    $lang = $_SESSION['lang'];
}
else{
    $lang = "es";
}
        
    
$stmt = $con->prepare("SELECT * FROM $connection_requests WHERE user_to = ?");
$stmt->bind_param("s", $userLoggedIn);
$stmt->execute();
$verification_query = $stmt->get_result();

while($arr = mysqli_fetch_array($verification_query)){
	$user_from= $arr['user_from'];
	$user_from_e = $crypt->EncryptU($user_from);
	switch($lang){
	    case("en"):{
	        if(isset($_POST['accept_request' . bin2hex($user_from_e)])){
	            $user_obj->addConnection($user_from);
	            echo "<div class='request_info'> You are now connected.</div>";
	            header("Location: requests.php");
	        }
	        if(isset($_POST['ignore_request' . bin2hex($user_from_e)])){
	            $delete_query = mysqli_query($con, "DELETE FROM $connection_requests WHERE user_to='$userLoggedIn' AND user_from = '$user_from'");
	            echo "<div class='request_info'> Request ignored.</div>";
	            header("Location: requests.php");
	        }
	       }
	       break;
	    case("es"):{
	    		if(isset($_POST['accept_request' . bin2hex($user_from_e)])){
	            $user_obj->addConnection($user_from);
	            echo "<div class='request_info'> Están conectados.</div>";
	            header("Location: requests.php");
	        }
	        if(isset($_POST['ignore_request' . bin2hex($user_from_e)])){
	            $delete_query = mysqli_query($con, "DELETE FROM $connection_requests WHERE user_to='$userLoggedIn' AND user_from = '$user_from'");
	            echo "<div class='request_info'> Solicitud ignorada.</div>";
	            header("Location: requests.php");
	        }
	    }
	    break;
	}
	
}


?>

<div class="main_column column" id="main_column">

	<div class="requests_holder">

		<div class="title_tabs" ><?php switch($lang){
		    case("en"):
		        echo "Connections";
		        break;
		    case("es");
                echo "Conexiones";
                break;
		}?></div>
	<div class= request_div>	
	
    		<div class="title_connection"><?php switch($lang){
    		    case("en"):
    		        echo "Connection Requests";
    		        break;
    		    case("es");
                    echo "Solicitudes de conexión";
                    break;
    		}?></div>
    
    		<button class="changer_butt" id="req_changer_butt_left" style=" border: none;">
    			<span class="text-vertical-center">&#10092;</span>
    		</button>
    		
    		<button class="changer_butt" id="req_changer_butt_right" style="  border: none;">
    	        <span class="text-vertical-center">&#10093;</span>
    		</button>
    	
    		<div class="center_content">
    			<div id="req_center_content">
    			</div>
    		</div>
    	</div>
    	
</div>	
	<div class= "requests_holder">
    	<div class= "request_div">	
    		<div class="title_connection"><?php switch($lang){
    		    case("en"):
    		        echo "Doctor Connections";
    		        break;
    		    case("es");
                    echo "Conexiones: Doctores";
                    break;
    		}?></div>
    		
    		
    		<input type="text" onkeyup="searchConnections(this.value,1)" name="doc_q" placeholder="<?php switch($lang){
    		    case("en"):
    		        echo "Search Doctor Connections (type a name and/or specialization)";
    		        break;
    		    case("es");
                    echo "Busca tus doctores (Escribe un nombre y/o especialización)";
                    break;
    
    		}?>" autocomplete="off" class="universal_search_bar" id="search_text_input_connections_doc" >
    		<div class="button_holder_request">
    			<img src="assets/images/icons/search-icon.png">
    		</div>
    		<button class="changer_butt" id="doc_changer_butt_left" style="border: none;">
    			<span class="text-vertical-center">&#10092;</span>
    		</button>
    		
    		<button class="changer_butt" id="doc_changer_butt_right" style=" border: none;">
    	        <span class="text-vertical-center">&#10093;</span>
    		</button>
    	
    		<div class="center_content">
    			<div id="doc_center_content">
    			</div>
    		</div>
		</div>	
		
	</div>
	
	<?php if($user_obj->isDoctor()){?>
		<div class="requests_holder">
			<div class= request_div>	
    			<div class="title_connection" ><?php switch($lang){
    
    		    case("en"):
    		        echo "Patient Connections";
    		        break;
    		    case("es");
                    echo "Conexiones de Pacientes";
                    break;
    		}?></div>
    			
    			
    			<input type="text" onkeyup="searchConnections(this.value,0)" name="pat_q" placeholder="<?php switch($lang){
    		    case("en"):
    		        echo "Search Patient Connections";
    		        break;
    		    case("es");
                    echo "Busca pacientes en tus conexiones (Escribe un nombre)";
                    break;
    
    		    }?>" autocomplete="off" class="universal_search_bar" id="search_text_input_connections_pat">
    			<div class="button_holder_request" >
    				<img src="assets/images/icons/search-icon.png">
    
    			</div>
    			<button class="changer_butt" id="pat_changer_butt_left" style=" border: none;">
    				<span class="text-vertical-center">&#10092;</span>
    			</button>
    			
    			<button class="changer_butt" id="pat_changer_butt_right" style="  border: none;">
    		        <span class="text-vertical-center">&#10093;</span>
    			</button>
    		
    			<div class="center_content">
    				<div id="pat_center_content">
    				</div>
    			</div>
    			
    			
    		</div>
    	</div>	
	<?php 
	}
	?>
</div>

<script>
$(document).ready(function(){

	//Doc initial load
	loadConnections(0, 1);
	//Reqs initial load		
	loadRequests(0);
	//pats initial load		
	loadConnections(0, 0);

	//Buttons control
	
	//requests
	$('#req_changer_butt_right').click(function() {
		
		$('#req_center_content').animate({
			scrollLeft: "+=" + eval("2*" + $(".internal_element").outerWidth()) + "px"
		}, "slow");

	});

	$('#req_changer_butt_left').click(function() {
		
		$('#req_center_content').animate({
			scrollLeft: "-=" + eval("2*" + $(".internal_element").outerWidth()) + "px"
		}, "slow");

	});

	//doctors
	$('#doc_changer_butt_right').click(function() {
		
		$('#doc_center_content').animate({
			scrollLeft: "+=" + eval("2*" + $(".internal_element_small").outerWidth()) + "px"
		}, "slow");

	});

	$('#doc_changer_butt_left').click(function() {
		
		$('#doc_center_content').animate({
			scrollLeft: "-=" + eval("2*" + $(".internal_element_small").outerWidth()) + "px"
		}, "slow");

	});

	//patient
	$('#pat_changer_butt_right').click(function() {
		
		$('#pat_center_content').animate({
			scrollLeft: "+=" + eval("2*" + $(".internal_element_small").outerWidth()) + "px"
		}, "slow");

	});

	$('#pat_changer_butt_left').click(function() {
		
		$('#pat_center_content').animate({
			scrollLeft: "-=" + eval("2*" + $(".internal_element_small").outerWidth()) + "px"
		}, "slow");

	});


	$('#req_center_content').bind('scroll', function(){

		var latest_loaded_element_id = $('#req_center_content').find('#latest_loaded_element_id_req').val();
		var ending = $('#req_center_content').find('#ending_req').val();
		
		if($(this).scrollLeft() + $(this).innerWidth() >=  $(this)[0].scrollWidth && ending == 0){
			//scrollHeight:tamaño todo el contenido scrollWidth()
			//scrollTop: numero de pixeles que se han scrolleado scrollLeft()
			//innerHeight: tamaño del div  scrolltop + innerheight = scrollheight innerWidth()
			loadRequests(latest_loaded_element_id);
		}
		return false;
	});

	$('#doc_center_content').bind('scroll', function(){

		var latest_loaded_element_id = $(this).find('#latest_loaded_element_id_').val();
		var ending = $(this).find('#ending_').val();

		if($(this).scrollLeft() + $(this).innerWidth() >=  $(this)[0].scrollWidth && ending == 0){
			//scrollHeight:tamaño todo el contenido scrollWidth()
			//scrollTop: numero de pixeles que se han scrolleado scrollLeft()
			//innerHeight: tamaño del div  scrolltop + innerheight = scrollheight innerWidth()
			loadConnections(latest_loaded_element_id, 1);
		}
		return false;
	});

	$('#pat_center_content').bind('scroll', function(){

		var latest_loaded_element_id = $(this).find('#latest_loaded_element_id_').val();
		var ending = $(this).find('#ending_').val();

		if($(this).scrollLeft() + $(this).innerWidth() >=  $(this)[0].scrollWidth && ending == 0){
			//scrollHeight:tamaño todo el contenido scrollWidth()
			//scrollTop: numero de pixeles que se han scrolleado scrollLeft()
			//innerHeight: tamaño del div  scrolltop + innerheight = scrollheight innerWidth()
			loadConnections(latest_loaded_element_id, 0);
		}
		return false;
	});

});

function searchConnections(terms,doctor){
	if($('#search_text_input_connections_doc').val() != ""){
		$.ajax({
			url: "includes/handlers/ajax_connections_search.php",
			type: "POST",
			data: "search_terms=" + terms + "&doctor=" + doctor + "&latest_element_id=0",
			cache:  false,
	
			success: function(data){
				if(doctor == 1){
					$('#doc_center_content').scrollLeft(0);
					
					$('#doc_center_content').hide().html(data).fadeIn();
				}else{
					$('#pat_center_content').scrollLeft(0);
					
					$('#pat_center_content').hide().html(data).fadeIn();
				}
			}
		});
	}
	else{
		if(doctor == 1){
			$('#doc_center_content').scrollLeft(0);
			loadConnections(0, 1);
		}
		else{
			$('#pat_center_content').scrollLeft(0);
			loadConnections(0, 0);
		}
	}
}


function loadConnections(latest_loaded_element_id, doctor){
	if(doctor == 1){
		if($('#search_text_input_connections_doc').val() != ""){
			var terms =  $('#search_text_input_connections_doc').val();
			$.ajax({
				url: "includes/handlers/ajax_connections_search.php",
				type: "POST",
				data: "search_terms=" + terms + "&doctor=" + doctor + "&latest_element_id=" + latest_loaded_element_id,
				cache:  false,

				success: function(response){
					$('#doc_center_content').find('#latest_loaded_element_id_').remove();
					$('#doc_center_content').find('#ending_').remove();
		
					if(latest_loaded_element_id == 0){
						$('#doc_center_content').hide().html(response).fadeIn();
					}
					else{
						$('#doc_center_content').append(response).fadeIn();
					}
				}
			});
		}
		else{
			var ajax = $.ajax({
				url: "includes/handlers/ajax_connections_continous_scroll.php",
				type: "POST",
				data: "latest_element_id=" + latest_loaded_element_id + "&doctor=" + doctor,
				cache:false,
		
				success: function(response){
					$('#doc_center_content').find('#latest_loaded_element_id_').remove();
					$('#doc_center_content').find('#ending_').remove();
		
					if(latest_loaded_element_id == 0){
						$('#doc_center_content').hide().html(response).fadeIn();
					}
					else{
						$('#doc_center_content').append(response).fadeIn();
					}
				}
			});
		}
	}
	else{
		if($('#search_text_input_connections_pat').val() != ""){
			var terms =  $('#search_text_input_connections_pat').val();
			$.ajax({
				url: "includes/handlers/ajax_connections_search.php",
				type: "POST",
				data: "search_terms=" + terms + "&doctor=" + doctor + "&latest_element_id=" + latest_loaded_element_id,
				cache:  false,

				success: function(response){
					$('#pat_center_content').find('#latest_loaded_element_id_').remove();
					$('#pat_center_content').find('#ending_').remove();
		
					if(latest_loaded_element_id == 0){
						$('#pat_center_content').hide().html(response).fadeIn();
					}
					else{
						$('#pat_center_content').append(response).fadeIn();
					}
				}
			});
		}
		else{
			var ajax = $.ajax({
				url: "includes/handlers/ajax_connections_continous_scroll.php",
				type: "POST",
				data: "latest_element_id=" + latest_loaded_element_id + "&doctor=" + doctor,
				cache:false,
		
				success: function(response){
					$('#pat_center_content').find('#latest_loaded_element_id_').remove();
					$('#pat_center_content').find('#ending_').remove();
		
					if(latest_loaded_element_id == 0){
						$('#pat_center_content').hide().html(response).fadeIn();
					}
					else{
						$('#pat_center_content').append(response).fadeIn();
					}
				}
			});
		}
	}
}

function loadRequests(latest_loaded_element_id){
	var ajax = $.ajax({
		url: "includes/handlers/ajax_friend_requests_continous_scroll.php",
		type: "POST",
		data: "latest_element_id=" + latest_loaded_element_id,
		cache:false,

		success: function(response){
			$('#req_center_content').find('#latest_loaded_element_id_req').remove();
			$('#req_center_content').find('#ending_req').remove();

			//$('#loading').hide();
			if(latest_loaded_element_id == 0){
				$('#req_center_content').hide().html(response).fadeIn();
			}
			else{
				$('#req_center_content').append(response).fadeIn();
			}
		}
	});
}

</script>