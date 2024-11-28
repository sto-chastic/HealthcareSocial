$(document).ready(function(){
	
	if(!(typeof currentDiv === 'undefined')){
		
		switch(currentDiv){
			case "home": {
							break;
							}
			case "login": {
								toggleRegisterForms('login');
								break;
							} 
			case "patient": {
								toggleRegisterForms('intro_patients');
								break;
							}
			case "doctor": {
								toggleRegisterForms('intro_doctors');
								break;
							}
		}
	}
	
	//Manage the actions in the top bar
	$("#top_home").click(function(){
		toggleRegisterForms('intro');
		$(".login_display").hide();	
		$("#logo_login").attr('src', 'assets/images/icons/login.png');
		});
	$("#top_register_patients").click(function(){
		toggleRegisterForms('intro_patients');
		$(".login_display").hide();
		$("#logo_login").attr('src', 'assets/images/icons/login.png');
		});
	$("#top_register_doctors").click(function(){
		toggleRegisterForms('intro_doctors');
		$(".login_display").hide();	
		$("#logo_login").attr('src', 'assets/images/icons/login.png');
		});
	$("#top_login").click(function(){
		toggleRegisterForms('login');
		$(".login_display").hide();	
		$("#logo_login").attr('src', 'assets/images/icons/loginh.png');
		});
	$("#logo_login").mouseover(function(){
		$(".login_display").hide();	
		$("#logo_login").attr('src', 'assets/images/icons/loginh.png');
		});
	$("#logo_login").mouseout(function(){
		$(".login_display").hide();	
		$("#logo_login").attr('src', 'assets/images/icons/login.png');
		});
	
	
	
	//Manage the actions in the mobile version menu
	$("#top_home_mobile").click(function(){
		toggleRegisterForms('intro');
		$(".login_display").hide();	
		$("#logo_login_mobile").attr('src', 'assets/images/icons/loginmobile.png');
		});
	$("#top_register_patients_mobile").click(function(){
		toggleRegisterForms('intro_patients');
		$(".login_display").hide();
		$("#logo_login_mobile").attr('src', 'assets/images/icons/loginmobile.png');
		});
	$("#top_register_doctors_mobile").click(function(){
		toggleRegisterForms('intro_doctors');
		$(".login_display").hide();	
		$("#logo_login_mobile").attr('src', 'assets/images/icons/loginmobile.png');
		});
	$("#top_login_mobile").click(function(){
		toggleRegisterForms('login');
		$(".login_display").hide();	
		$("#logo_login_mobile").attr('src', 'assets/images/icons/loginh.png');
		});
	$("#logo_login_mobile").mouseover(function(){
		$("#logo_login_mobile").attr('src', 'assets/images/icons/loginh.png');
		});
	$("#logo_login_mobile").mouseout(function(){	
		$("#logo_login_mobile").attr('src', 'assets/images/icons/loginmobile.png');
		});
	$("#top_login_mobile").mouseover(function(){	
		$("#logo_login_mobile").attr('src', 'assets/images/icons/loginh.png');
		});
	$("#top_login_mobile").mouseout(function(){	
		$("#logo_login_mobile").attr('src', 'assets/images/icons/loginmobile.png');
		});
	
	
	
	
	//Manage the actions in the body
	$("#search_4a_doctor").click(function(){toggleRegisterForms('intro_search');});
	
	
	//On click signup, hide login and show registration form
	$(".signup").click(function(){
		$('.log_form').slideUp("slow", function(){
			$('.reg_form').slideDown("slow");
		})
	});
	$("#login_button").submit(function(){
		
	});

	//On click signup, hide registration and show login form
	$(".signin").click(function(){
		$('.reg_form').slideUp("slow", function(){
			$('.log_form').slideDown("slow");
		})
	});
	
	//Manage the actions in the search bar
	//$("#search_specialist").click(function(){alert("you clicked on search specialist")});
	
	$("#search_location").click(function(){
		getMainSearchBarLocation('', 'docsearch_location_reg', 'search_location');
		});
	$("#search_location").keyup(function(){
		clearHiddenDiv("#ds_city_code");
		clearHiddenDiv("#ds_lat");
		clearHiddenDiv("#ds_lng");
		getMainSearchBarLocation(this.value, 'docsearch_location_reg', 'search_location');
		});
	$("#search_location").blur(function(){
		setTimeout(clearResultsDiv,300,'docsearch_location_reg','search_location');
	});
	
	$("#search_insurance").click(function(){
		getMainSearchBarInsurance('', 'docsearch_insurance_reg', 'search_insurance');});
	$("#search_insurance").keyup(function(){
		clearHiddenDiv("#ds_ins_code");
		getMainSearchBarInsurance(this.value, 'docsearch_insurance_reg', 'search_insurance');
		});
	$("#search_insurance").blur(function(){
		setTimeout(clearResultsDiv,300,'docsearch_insurance_reg','search_insurance');
		});
	$("#current_search_radius").hide();
	$("#search_radius").on('change',function(){
		let a = $(this);
		let w = a.width();
		let n = (a.val()-a.attr("min"))/(a.attr("max")-a.attr("min"));
		let o = - w /8  ; 
		if(n<0){np=0;}
		else if (n>1){np=w}
		else {np=w*n;}
		
		a.next("span")
		.css({
			left:np,
			marginLeft: o*(n) 
		})
		.text(a.val());
		a.next("span").show();
		//setTimeout($("#current_search_radius").hide,100);
	});
	
	
});

/**
 * Does something
 * @author VICTOR
 * */

function toggleDivs() {
    var $inner = $("#inner");

    if ($inner.position().left == 0) {
        $inner.animate({
            left: "-300px"
        });
    }
    else {
        $inner.animate({
            left: "0px"
        });
    }
}

/**
 * Does something
 * @author VICTOR
 * */

function toggleRegisterForms(value){
 
	//alert(value);
	if(value == 'intro_search'){
            $(".register_background").css({"display": "none"}); 
			$(".for_patient").css({"display": "none"});
			$(".for_doctor").css({"display": "none"}); 
			$(".for_search").css({"display": "initial"}); 
			$(".login_box").css({"display": "none"}); 
			$(".wrapper_dark").css({"background-color": "rgba(69,69,69,0.7)"});	
			

    }
	else if(value == 'intro_doctors'){
            $(".register_background").css({"display": "none"}); 
			$(".for_patient").css({"display": "none"});
			$(".for_doctor").css({"display": "initial"}); 
			$(".for_search").css({"display": "none"});
			$(".login_box").css({"display": "none"}); 
			$(".wrapper_dark").css({"background-color": "rgba(69,69,69,0.5)"});
    }
	else if(value == 'intro'){
            $(".register_background").css({"display": "initial"}); 
			$(".for_patient").css({"display": "none"});
			$(".for_doctor").css({"display": "none"}); 	
			$(".for_search").css({"display": "none"}); 
			$(".login_box").css({"display": "none"}); 			
			$(".wrapper_dark").css({"background-color": "rgba(69,69,69,0.5)"});
			
    }
	else if(value == 'intro_patients'){
            $(".register_background").css({"display": "none"}); 
			$(".for_patient").css({"display": "initial"});
			$(".for_doctor").css({"display": "none"});
			$(".for_search").css({"display": "none"});
			$(".login_box").css({"display": "none"}); 		
			$(".wrapper_dark").css({"background-color": "rgba(69,69,69,0.5)"});
	}
	else if(value == 'login' && $(".login_box").css("display") == "none" ){
            $(".login_box").css({"display": "initial"}); 
        	$(".register_background").css({"display": "initial"}); 
			$(".for_patient").css({"display": "none"});
			$(".for_doctor").css({"display": "none"}); 	
			$(".for_search").css({"display": "none"}); 		
    }
    else {
    		$(".login_box").css({"display": "none"}); 
    }
}
//Main search bar functions

/**Creates the datepicker element
 * Generates a calendar based on JQuery UI's datepicker, sets default value to today, selection period up to 1 month from now, up two months shown at a time
 **/
$( function() {
	$( "#datepicker" ).datepicker({ minDate: -0, maxDate: "+1M", numberOfMonths:2, dateFormat: "dd-mm-yy"});
	//Get the current date in a dateobject
	  var currentDate = new Date();
	  //Offset the current date by how many days you need
	  currentDate.setDate(currentDate.getDate());
	  //Insert placeholder date
	  $("#datepicker").datepicker("setDate", currentDate);
  });

/**Creates the dropdown menu options for the search bar insurance field
 * @param {string} value - the user submitted query
 * @results_div_id the id of the div the user submitted in
 * @input_div_id the id of the div the user submitted in
 **/
function getMainSearchBarInsurance(value, results_div_id, input_div_id){
	$.post("includes/handlers/ajax_docsearch_insurance.php", {query:value, results_div_id:results_div_id, input_div_id: input_div_id}, function(data){
		$('#docsearch_insurance_reg').html(data);
	});
}
/**Creates the dropdown menu options for the search bar location field
 * @param {string} value - the user submitted query
 * @results_div_id the id of the div to put the results in
 * @input_div_id the id of the div for the input
 **/
function getMainSearchBarLocation(value, results_div_id, input_div_id){
	$.post("includes/handlers/ajax_docsearch_location.php", {query:value, results_div_id:results_div_id, input_div_id: input_div_id}, function(data){
		$('#docsearch_location_reg').html(data);
	});
}
/**Looks for the users position using browser functions
 * 
 * @return alert if geolocation is not supported, otherwise error 
 * @see showError
 * @see setPosition
 **/
function setUserSearchLocation() {
    if (navigator.geolocation) {
    		blockNLoad();
        navigator.geolocation.getCurrentPosition(setPosition, showError);
    } else { 
        alert("Geolocation is not supported by this browser.");
    }
	
}
/**Sets the position in the input fields given by the browser
 * 
 * @param position - generated by the browser
 **/
function setPosition(position) {
//    alert("Latitude: " + position.coords.latitude + 
//    "<br>Longitude: " + position.coords.longitude);
	$("#ds_city_code").val("");
	$("#ds_lat").val(position.coords.latitude);
	$("#ds_lng").val(position.coords.longitude);
	$("#search_location").val("Current Location");
	unblockNLoad();
//    document.getElementById('ds_city_code').value = '';
//    document.getElementById('ds_lat').value = position.coords.latitude;
//    document.getElementById('ds_lng').value = position.coords.longitude;
//    document.getElementById('search_location').value = "Current Location";
}
/**Handles error messages when trying to find the location
 * 
 * @param {string} error - the error produced by the server
 * @returns alert containing the error (based on online)
 **/
function showError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
    			unblockNLoad();	
            alert("User denied the request for Geolocation.");
            break;
        case error.POSITION_UNAVAILABLE:
			unblockNLoad();
            alert("Location information is unavailable.");
            break;
        case error.TIMEOUT:
			unblockNLoad();
            alert("The request to get user location timed out.");
            break;
        case error.UNKNOWN_ERROR:
			unblockNLoad();
            alert("An unknown error occurred while trying to find your location.");
            break;
    }
}
/**Selects a unique search result, clears the previous results from the input field
 * 
 * @param {string} i_title - the title to input submit to the form
 * @param {string} results_div_id - the id of the div to insert the results
 * @param {string} input_div_id - the id of the div where results are related to
 * @param {string} h_code - the code of the result to submit to the form
 * @param {string} h_input_div_id - the id of the hidden div to assign the results to
 * 
 **/
function selectSearchResultUnique(i_title, results_div_id, input_div_id, h_code, h_input_div_id){
	let new_text;
	if(i_title.indexOf(".") !== -1){
		new_text = i_title;
	}
	else {
		new_text = i_title + ".";
	}
	if (h_input_div_id == "ds_city_code") {
		clearHiddenDiv("#ds_lat");
		clearHiddenDiv("#ds_lng");
	}
	
	$('#' + input_div_id).val(new_text);
	$('#' + h_input_div_id).val(h_code);
	$('#' + results_div_id).html("");
	$('#' + input_div_id).focus();
}
/**Selects a unique search result, clears the previous results from the input field
 * 
 * @param {string} results_div_id - the id of the div to clear
 * @param {string} input_div_id - the id of the div where info is input
 **/
function clearResultsDiv(results_div_id, input_div_id){
	if(document.activeElement.id != input_div_id){
		$('#' + results_div_id).empty();
	}
}
/**Empties the selected div, ideally a hidden one
 * 
 * @param {string} div_ref - the id or name of the div to clear
 **/
function clearHiddenDiv(div_ref){
	$(div_ref).val("");
}
/**Shows the loading div (grey box with logo on)
 * 
 **/
function blockNLoad(){
	$(".loading").show();
}
/**Hides the loading div (grey box with logo on)
 * 
 **/
function unblockNLoad(){
	$(".loading").hide();
}
/**
 * Does something
 * @author DAVID
 **/
function sanitizeSearchInsurance(value,lang_col,search_col,results_div_id, input_div_id){
	$.post("includes/handlers/ajax_sanitize_search.php",{query:value}, function(data){
		getLiveSearchInsurance(data,lang_col,search_col,results_div_id, input_div_id);
	});
}
/**
 * Does something
 * @author DAVID
 * */
function getLiveSearchInsurance(value,lang_col,search_col,results_div_id, input_div_id){
	$.post("includes/handlers/ajax_search_insurance.php",{query:value,lang_col:lang_col,search_col:search_col,results_div_id:results_div_id, input_div_id:input_div_id}, function(data){
		$('#' + results_div_id).html(data);
	});
}
/**
 * Does something
 * @author DAVID
 * */
function selectSearchResultUniversal(i_title, prev_elements, results_div_id, input_div_id){
	if(prev_elements == ""){
		var new_text = i_title + ", ";
	}
	else{
		var new_text = prev_elements + ", " + i_title + ", ";
	}

	$('#' + input_div_id).val(new_text);
	$('#' + results_div_id).html("");
	$('#' + input_div_id).focus();

}
/**
 * Does something
 * @author DAVID
 * */
function sanitizeSearchSpecialization(value,lang_col,search_col,results_div_id, input_div_id, hidden_inp){
	$.post("includes/handlers/ajax_sanitize_search.php",{query:value}, function(data){
		getLiveSearchSpecialization(data,lang_col,search_col,results_div_id, input_div_id, hidden_inp);
	});
}
/**
 * Does something
 * @author DAVID
 * */
function getLiveSearchSpecialization(value,lang_col,search_col,results_div_id, input_div_id, hidden_inp){
	var prev_ids = $('#'+hidden_inp).val();
	$.post("includes/handlers/ajax_search_specialization.php",{query:value,prev_ids:prev_ids,lang_col:lang_col,search_col:search_col,results_div_id:results_div_id, input_div_id:input_div_id,hidden_inp:hidden_inp}, function(data){
		$('#' + results_div_id).html(data);
	});
}
/**
 * Does something
 * @author DAVID
 * */
function getLiveSearchSpecialization_old(value,lang_col,search_col,results_div_id, input_div_id, hidden_inp){
	$.post("includes/handlers/ajax_search_specialization.php",{query:value,lang_col:lang_col,search_col:search_col,results_div_id:results_div_id, input_div_id:input_div_id,hidden_inp:hidden_inp}, function(data){
		$('#' + results_div_id).html(data);
	});
}
/**
 * Does something
 * @author DAVID
 * */
function selectSearchResultToHidInp_old(i_title, id, results_div_id, input_div_id,hidden_inp){
	$('#' + input_div_id).val(i_title);
	$('#' + hidden_inp).val(id);
	$('#' + results_div_id).html("");
}
/**
 * Does something
 * @author DAVID
 * */
function selectSearchResultToHidInp(i_title, id, prev_elements, prev_ids, results_div_id, input_div_id, hidden_inp){
	if(prev_elements == ""){
		var new_text = i_title + ", ";
		var new_text_arr = i_title;
	}
	else{
		var new_text = prev_elements + ", " + i_title + ", ";
		var new_text_arr = prev_elements + ", " + i_title;
	}
	
	if(prev_ids == ""){
		var new_ids = id;
	}
	else{
		var new_ids = prev_ids + "," + id;
	}
	
	$('#' + input_div_id).val(new_text);
	$('#' + input_div_id + "_holder").val(new_text_arr);
	$('#' + input_div_id).focus();
	$('#' + hidden_inp).val(new_ids);
	$('#' + results_div_id).html("");
	
}

$.fn.getCursorPosition = function() {
    var el = $(this).get(0);
    var pos = 0;
    var posEnd = 0;
    if('selectionStart' in el) {
        pos = el.selectionStart;
        posEnd = el.selectionEnd;
    } else if('selection' in document) {
        el.focus();
        var Sel = document.selection.createRange();
        var SelLength = document.selection.createRange().text.length;
        Sel.moveStart('character', -el.value.length);
        pos = Sel.text.length - SelLength;
        posEnd = Sel.text.length;
    }
    return [pos, posEnd];
};

/**
 * cambios victor
 * @author VICTOR
 * */
$(window).resize(function() {
if($(".login_display").css("width") > "866px"){
	$(".login_display").hide();	
}	
});

function menuRegister(){
	$(".login_display").toggle();
	
}
