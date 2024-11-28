<?php
include ('includes/profile_header_aux.php');

if(isset($_SESSION['lang'])){
    $lang = $_SESSION['lang'];
}
else{
    $lang = "es";
    //echo $lang + "not set";
}

if(isset($_GET['profile_username'])){
	$viewing_doctor = $_GET['profile_username'];
	$viewing_doctor_e = pack("H*",$viewing_doctor);
	$viewing_doctor = $crypt->Decrypt($viewing_doctor_e);
}
else{
	header("Location: index.php");
}

$viewing_doctor_obj = new User($con, $viewing_doctor, $viewing_doctor_e);

?>

<div class="main_column column" id="main_column">
	<div class= "requests_holder">
	    	<div class= "request_div">
	    		<div class="title_connection"><?php switch($lang){
	    		    case("en"):
	    		    		echo "<span class='blue_highlight_title'>".$viewing_doctor_obj->getFirstAndLastNameShort(30) ."</span>'s Doctor Connections";
	    		        break;
	    		    case("es");
	    		    		echo "Conexiones de   <span class='blue_highlight_title'>".$viewing_doctor_obj->getFirstAndLastNameShort(30) . "</span>";
                    	break;
	    		}?></div>
	    		
	    		
	    		<input type="text" onkeyup="searchConnections(this.value)" name="doc_q" placeholder="<?php switch($lang){
	    		    case("en"):
	    		        echo "Search Doctor Connections (type a name and/or specialization)";
	    		        break;
	    		    case("es");
	                    echo "Busca doctores (Escribe un nombre y/o especialización)";
	                    break;
	    
	    		}?>" autocomplete="off" class="universal_search_bar" id="search_text_input_connections_doc" style=" width: 38%;">
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

</div>

<script>
$(document).ready(function(){

	//Doc initial load
	loadConnections(0);

	//header appearance 
// 	$('.grey_banner').fadeTo(1500,0.8);
// 	$('.title_text').delay(1500).animate({ paddingTop: 98}, 2000);
// 	$('.wrapper').delay(1500).animate({ marginTop: 166}, 2000);
	
	//Buttons control

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

	$('#doc_center_content').bind('scroll', function(){

		var latest_loaded_element_id = $(this).find('#latest_loaded_element_id_').val();
		var ending = $(this).find('#ending_').val();

		if($(this).scrollLeft() + $(this).innerWidth() >=  $(this)[0].scrollWidth && ending == 0){
			//scrollHeight:tamaño todo el contenido scrollWidth()
			//scrollTop: numero de pixeles que se han scrolleado scrollLeft()
			//innerHeight: tamaño del div  scrolltop + innerheight = scrollheight innerWidth()
			loadConnections(latest_loaded_element_id);
		}
		return false;
	});

});

function searchConnections(terms){
	if($('#search_text_input_connections_doc').val() != ""){
		$.ajax({
			url: "includes/handlers/ajax_connections_search_ext_pat.php",
			type: "POST",
			data: "search_terms=" + terms + "&d=" + "<?php echo bin2hex($viewing_doctor_e);?>" + "&latest_element_id=0",
			cache:  false,
	
			success: function(data){
				$('#doc_center_content').scrollLeft(0);
				
				$('#doc_center_content').hide().html(data).fadeIn();
				
			}
		});
	}
	else{
		$('#doc_center_content').scrollLeft(0);
		loadConnections(0);
	}
}


function loadConnections(latest_loaded_element_id){
	if($('#search_text_input_connections_doc').val() != ""){
		var terms =  $('#search_text_input_connections_doc').val();
		$.ajax({
			url: "includes/handlers/ajax_connections_search_ext_pat.php",
			type: "POST",
			data: "search_terms=" + terms + "&d=" + "<?php echo bin2hex($viewing_doctor_e);?>" + "&latest_element_id=" + latest_loaded_element_id,
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
			url: "includes/handlers/ajax_connections_continous_scroll_ext_pat.php",
			type: "POST",
			data: "latest_element_id=" + latest_loaded_element_id + "&d=" + "<?php echo bin2hex($viewing_doctor_e);?>",
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

</script>