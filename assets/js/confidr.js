$(document).ready(function(){
	//Button search

	$('#search_text_input').focus(/*focus happens when you click on it*/function(){
		if(window.matchMedia("(min-width:800px)").matches){ /*this means that it is false if the window is smaller than 800px;, means for cellphones it wont slide*/
			/*$(this).animate({width: '250px'}, 300);*/
		}
	});

	$('.button_holder').on('click', function(){
		document.search_form.submit();
	});
	//Button for profile post
	$('#profile_post').submit(function(event){
		event.preventDefault();
		if($("#profile_post_body").val()){
			$.ajax({
				method: "POST",
				url: "includes/handlers/ajax_submit_profile_post.php",
				data: {profile_post_body: $('#profile_post_body').val(), 
						profile_owner: $('#profile_owner').val()},
				beforeSend: function(){
								$("#submit_profile_post").attr("disabled", true);
								$("#submit_profile_post").addClass("submitting_form_button");
							},
				success: function(){
							$("#submit_profile_post").removeAttr("disabled");
							$("#submit_profile_post").removeClass("submitting_form_button");
							$.post("includes/handlers/ajax_load_profile_posts.php", 
									{page: "1", profileUsername: $('#profile_owner_h').val()}, 
									function(data){
										$('#loading').hide();
										$('.posts_area').html(data);
									});
							$('#profile_post_body').val("");
							},
				error: function(){
							$("#submit_profile_post").removeAttr("disabled");
							$("#submit_profile_post").removeClass("submitting_form_button");
							}
			});
		}
		//$("#submit_profile_post").attr("disabled", true);
		//$(this).find(':submit').attr('disabled','disabled');
//		$.post("includes/handlers/ajax_submit_profile_post.php",
//				{profile_post_body: $('#profile_post_body').val(), profile_owner: $('#profile_owner').val()}, 
//				function(){
//					$.post("includes/handlers/ajax_load_profile_posts.php", 
//						{page: "1", profile_owner: $('#profile_owner').val()}, 
//						function(data){
//							$('#loading').hide();
//							$('.posts_area').html(data);
//						});
//					$('#profile_post_body').val("");
//			});
	});
	
	//Button for deleting office post
	$('#delete_office_butt1').click(function(){
		
		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_delete_office.php",
			data: $('form.del_office_form1').serialize(), //What we send!
			success: function(msg) {
				//alert(msg);
				$("#del_office_form1").modal('hide'); //hides the form.
				location.reload();
			},
			error: function() {
				alert("Could not be done at this time, please try again later.");
			}
		});
	});
	
	$('.search').css({"box-shadow" : "none"});
	
	$('#delete_office_butt2').click(function(){

		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_delete_office.php",
			data: $('form.del_office_form2').serialize(), //What we send!
			success: function(msg) {
				//alert(msg);
				$("#del_office_form").modal('hide'); //hides the form.
				location.reload();
			},
			error: function() {
				alert("Could not be done at this time, please try again later.");
			}
		});
	});
	
	$('#delete_office_butt3').click(function(){

		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_delete_office.php",
			data: $('form.del_office_form3').serialize(), //What we send!
			success: function(msg) {
				//alert(msg);
				$("#del_office_form").modal('hide'); //hides the form.
				location.reload();
			},
			error: function() {
				alert("Could not be done at this time, please try again later.");
			}
		});
	});
	
	$("[name='confirm_plan']").click(function(){
		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_confirm_plan.php",
			data: $('form.plan_form').serialize(), //What we send!
			success: function(msg) {
				//alert(msg);
				//$("#plan_form").modal('hide'); //hides the form.
				//$("#post_plan").attr("disabled","disabled");
				//location.reload();
				$("#confirm_plan").modal('hide');
				selfHideAlert("Saved",3000,"post_plan");
				$('#plan_not_saved').css({"display":"none"});
				$('#post_plan').css({"border":"1px solid #ccc"});
			},
			error: function() {
				alert("Could not be done at this time, please try again later.");
			}
		});
	});
	
	$("#prem_ad1ln1").keyup(function(){
		$("#address").val($(this).val() + " " + $("#prem_ad1cityName").val());
		});
	$("#prem_ad1cityName").keyup(function(){
		$("#address").val($("#prem_ad1ln1").val() + " " + $(this).val());
		});
	
	$("#prem_ad1cityName").click(function(){
		getPremiumCityLocation('', 'premCityResultsBox', 'prem_ad1cityName');
		});
	$("#prem_ad1cityName").keyup(function(){
		//clearHiddenDiv("#ds_city_code");
		//clearHiddenDiv("#ds_lat");
		//clearHiddenDiv("#ds_lng");
		getPremiumCityLocation(this.value, 'premCityResultsBox', 'prem_ad1cityName');
		});
	
	//City search for the calendar and office settings page
	$("#city_office_1").click(function(){
		getOfficeCityLocation('', 'cityResults1', 'city_office_1', '1');
		});
	$("#city_office_1").keyup(function(){
		getOfficeCityLocation(this.value, 'cityResults1', 'city_office_1', '1');
		});
	$("#city_office_2").click(function(){
		getOfficeCityLocation('', 'cityResults2', 'city_office_2', '2');
		});
	$("#city_office_2").keyup(function(){
		getOfficeCityLocation(this.value, 'cityResults2', 'city_office_2', '2');
		});
	$("#city_office_3").click(function(){
		getOfficeCityLocation('', 'cityResults3', 'city_office_3', '3');
		});
	$("#city_office_3").keyup(function(){
		getOfficeCityLocation(this.value, 'cityResults3', 'city_office_3', '3');
		});
	
//	$("#prem_ad1cityName").blur(function(){
//		setTimeout(clearResultsDiv,300,'premCityResultsBox','prem_ad1city');
//	});
	
	//Settings
	//Language settings
	$("#lang_pref_en").click(function(){
		$("#lang_pref_val").val("en");
		$("#lang_pref_en").addClass("active");
		$("#lang_pref_es").removeClass("active");
	});
	$("#lang_pref_es").click(function(){
		$("#lang_pref_val").val("es");
		$("#lang_pref_es").addClass("active");
		$("#lang_pref_en").removeClass("active");
	});
	
	//Delete profile info
}); 
/* -----------------------------End document.ready --------------------------------------*/

/**Creates the dropdown menu options for the search bar location field
 * @param {string} value - the user submitted query
 * @results_div_id the id of the div to put the results in
 * @input_div_id the id of the div for the input
 * @author JMZAM
 **/
function getPremiumCityLocation(value, results_div_id, input_div_id){
	$.post("includes/handlers/ajax_premium_location.php", {query:value, results_div_id:results_div_id, input_div_id: input_div_id}, function(data){
		$('#'+results_div_id).html(data);
	});
}

/**Creates the dropdown menu options for the search bar location field
 * @param {string} value - the user submitted query
 * @results_div_id the id of the div to put the results in
 * @input_div_id the id of the div for the input
 * @author JMZAM
 **/
function getOfficeCityLocation(value, results_div_id, input_div_id, officeNumber){
	$.post("includes/handlers/ajax_city_location.php", {query:value, results_div_id:results_div_id, input_div_id: input_div_id, officeNumber: officeNumber}, function(data){
		$('#'+results_div_id).html(data);
	});
}

/**Selects a unique search result, clears the previous results from the input field
 * 
 * @param {string} i_title - the title to input submit to the form
 * @param {string} results_div_id - the id of the div to insert the results
 * @param {string} input_div_id - the id of the div where results are related to
 * @param {string} h_code - the code of the result to submit to the form
 * @param {string} h_input_div_id - the id of the hidden div to assign the results to
 * @param {string} dept_div_id - id of the div to display the department information in
 * @param {string} dept - the name of the department to display
 * 
 * @author JMZAM
 **/
function selectCityAndDepartment(i_title, results_div_id, input_div_id, h_code, h_input_div_id, dept_div_id, dept){
	let new_text;
	if(i_title.indexOf(".") !== -1){
		new_text = i_title;
	}
	else {
		new_text = i_title + ".";
	}
	$('#' + input_div_id).val(new_text);
	$('#' + h_input_div_id).val(h_code);
	$('#' + dept_div_id).val(dept);
	$('#' + results_div_id).html("");
	$('#' + input_div_id).focus();
}
/**Selects a unique search result, clears the previous results from the input field
 * 
 * @param {string} results_div_id - the id of the div to clear
 * @param {string} input_div_id - the id of the div where info is input
 * @author JMZAM
 **/
function clearResultsDiv(results_div_id, input_div_id){
	if(document.activeElement.id != input_div_id){
		$('#' + results_div_id).empty();
	}
}
/**Empties the selected div, ideally a hidden one
 * 
 * @param {string} div_ref - the id or name of the div to clear
 * @author JMZAM
 **/
function clearHiddenDiv(div_ref){
	$(div_ref).val("");
}

	
	
$(document).click(function(e){//e is the click, target is what you clicked on, and class is the class of what you clicked.

	if(e.target.class != "search_results" && e.target.id != "search_text_input"){
		$('.search_results').html("");
		$('.search_results').css({"height" : "0"});
		$('.search_results_footer').html("");
		$(".search_results_footer").toggleClass("search_results_footer_empty");
		$(".search_results_footer").toggleClass("search_results_footer");
		/*$('#search_text_input').animate({width: '120px'}, 300);*/
	}

	if($('.dropdown_data_window').css("height") != "0px" && e.target.class != "dropdown_data_window"){
		if(e.target.id == "message_drop_down_button" && $("#dropdown_data_type").val() == 'notification'){
			$('.dropdown_data_window').html("");
			$('.dropdown_data_window').css({"padding" : "0px", "height" : "0px" , "border" : "none"});
			//var user = $("#dropdown_data_user").val();
			getDropdownData('message');
		}
		else if(e.target.id == "notification_drop_down_button" && $("#dropdown_data_type").val() == 'message'){
			$('.dropdown_data_window').html("");
			$('.dropdown_data_window').css({"padding" : "0px", "height" : "0px" , "border" : "none"});
			//var user = $("#dropdown_data_user").val();
			getDropdownData('notification');
		}
		else{
			$('.dropdown_data_window').html("");
			$('.dropdown_data_window').css({"padding" : "0px", "height" : "0px" , "border" : "none"});
		}
	}

});

function sanitizeSearchMessages(value_ini,user){
	//FULLY DEPRECATED
	alert(value_ini);
	/*$.post("includes/handlers/ajax_sanitize_search.php",{query:value_ini}, function(data){
		getUsers(data,user);
	});*/ //SANITIZE SEARCH FUNCTION DEPRECATED AS IT IS UNNECESSARY AND INTERFERES WITH THE PROPER FUNCTION OF THE SEARCH
	getUsers(value_ini,user);
	
}

function getUsers(value){
	$.post("includes/handlers/ajax_friend_search.php", {query:value},function(data){/* query is the name of the value and value is passed as a parameter to that variable.*/
	$(".results").html(data); //it sends a requests to ajax_friend_search.php with the values query and user... and then sets the value of the div results with the value return from data
	if($(".results").css("height") <= "2px"){
		 $(".results").css({"border": "0px solid #9d9d9d", "box-shadow":" rgba(64, 64, 65, 0.53) 0px 2px 3px 0px"});	
	}
	else{
		$(".results").css({"border": "1px solid #9d9d9d"});
	}
	});
}

function sanitizeSearchUsers(value,user){
	//DEPRECATED
	/*$.post("includes/handlers/ajax_sanitize_search.php",{query:value}, function(data,user){

		getLiveSearchUsers(2,user);
	}*/ //SANITIZE SEARCH FUNCTION DEPRECATED AS IT IS UNNECESSARY AND INTERFERES WITH THE PROPER FUNCTION OF THE SEARCH
	getLiveSearchUsers(value,lang);
}

function getLiveSearchUsers(value, lang){

	$.post("includes/handlers/ajax_search.php",{query:value}, function(data){
		
		if($(".search_results_footer_empty")[0]) { //there might be more than one element selected with that class, [0] picks the first.
			$(".search_results_footer_empty").toggleClass("search_results_footer");//toggleClass: if it's on the page, it removes it, if it's not, it adds it
			$(".search_results_footer_empty").toggleClass("search_results_footer_empty"); //it has two classes now, so this removes the second.
		}
		
		if(data != ""){
			if(lang=="en"){
				$('.search_results_footer').html("<a href='search.php?q=" + value + "'>See all results.</a>");
			} else if(lang=="es"){
				$('.search_results_footer').html("<a href='search.php?q=" + value + "'>Ver todos los resultados.</a>");
			}
			
			$('.search_results').html(data);
			$('.search_results').css({"height" : "auto"});
			$('.search').css({"box-shadow" : "0px 2px 10px 0px #404041"});
		}
		else{
			$('.search_results').html("");
			$('.search_results').css({"height" : "0"});
			$('.search_results_footer').html("");
			$(".search_results_footer").toggleClass("search_results_footer_empty");
			$(".search_results_footer").toggleClass("search_results_footer");
			$('.search').css({"box-shadow" : "none"});
		}
	});
}

function getDropdownData(type){
	if($(".dropdown_data_window").css("height") == "0px"){
		var pageName;

		if(type == 'notification'){
			pageName = "ajax_load_notifications.php";
			$("span").remove("#unread_notifications");
		}
		else if(type == 'message'){
			pageName = "ajax_load_messages.php";
			$("span").remove("#unread_messages");
		}

		var ajaxreq = $.ajax({
			url: "includes/handlers/" + pageName,
			type: "POST",
			data: "page=1",
			cache: false,

			success: function(response){
				$(".dropdown_data_window").html(response);
				$(".dropdown_data_window").css({"padding": "0px", "height" : "auto" , "border" : "1px solid #DADADA"});
				$("#dropdown_data_type").val(type);
				//$("#dropdown_data_user").val(user);
			}
		});
	}
	else if($(".dropdown_data_window").css("height") == "280px"){
		var pageName;

		if(type == 'notification'){
			pageName = "ajax_load_notifications.php";
			$("span").remove("#unread_notifications");
		}
		else if(type == 'message'){
			pageName = "ajax_load_messages.php";
			$("span").remove("#unread_messages");
		}

		var ajaxreq = $.ajax({
			url: "includes/handlers/" + pageName,
			type: "POST",
			data: "page=1",
			cache: false,

			success: function(response){
				$(".dropdown_data_window").html(response);
				$("#dropdown_data_type").val(type);
			}
		});
	}

}

function checkIfMessagesIFrameExists(message_username){
	if($( "#messages_frame" ).length == 0){
		window.location.href = "index.php?mu=" + message_username;
	} 
}

function tabChanger(tab){
    $('.nav-tabs a[href="#' + tab + '"]').tab('show');
};

function selectMessageIFrame(usr){
	$("#messages_frame").attr('src', "messages_frame.php?u=" + usr);
}

function getDropdownCalendarIni(month,year){
	if($(".dropdown_calendar_window").css("height") == "0px"){
		if($("#selected_week_week").length == 0){
			var ajaxreq = $.ajax({
				url: "includes/handlers/ajax_dropdown_calendar.php",
				type: "POST",
				data: "month=" + month + "&year=" + year,
				cache: false,

				success: function(response){
					$(".calendar_container").html(response);
					$(".dropdown_calendar_window").css({"padding": "0px", "height" : "auto" , "border" : "1px solid #ccc","border-radius":"4px"} );
				}
			});
		}
		else{
			$(".dropdown_calendar_window").css({"padding": "0px", "height" : "auto" , "border" : "1px solid #ccc","border-radius":"4px"});
		}
	}
}

function doctorConfirmAppointment(cid){
	var span_ele;
	var ajaxreq = $.ajax({
		url: "includes/handlers/ajax_doctor_confirm_appo.php",
		type: "POST",
		data: "cid=" + cid,
		cache: false,

		success: function(response){
			span_ele = "#conf_" + cid;
			$(span_ele).html(response);
			$(span_ele).css({"left": "-15px","border":"none","cursor":"text","font-size":"11px","color": "#72c5eb", "text-transform": "uppercase", "position": "absolute","font-size": "12px", "padding": "4px", "text-align": "center", "background-color": "white","width":" 58%","height": "auto", "bottom": "0", "padding": "0px 2%"} );
		}
	});
}

function changeWeek(year,week){
	if($(".dropdown_calendar_window").css("height") != "0px"){
		var prev_week = $("#selected_week_week").val();
		var prev_year = $("#selected_week_year").val();
		$("#calendar_week_block_id_" + prev_year + prev_week).toggleClass("calendar_week_block_selected");
		$("#calendar_week_block_id_" + prev_year + prev_week).toggleClass("calendar_week_block");
		
		$("#calendar_week_block_id_" + year + week).toggleClass("calendar_week_block");
		$("#calendar_week_block_id_" + year + week).toggleClass("calendar_week_block_selected");
		$("#selected_week_week").val(week);
		$("#selected_week_year").val(year);
		$('#select_week_button').removeAttr('disabled');
		$('#select_week_button').css({"background-color":"#f38ead","color":"white"});
	}
}

function updateWeek(){
	if($(".dropdown_calendar_window").css("height") != "0px"){
		var week = $("#selected_week_week").val();
		var year = $("#selected_week_year").val();
	}
	$("#calendar_iframe").attr('src', "calendar_frame.php?w=" + week + "&y=" + year);
}

function closeDropdownCalendar(){
	if($(".dropdown_calendar_window").css("height") != "0px"){
		/*$(".calendar_container").html("");*/
		$(".dropdown_calendar_window").css({"padding" : "0px", "height" : "0px" , "border" : "none"});
	}
}

function selectDay4Booking(year,month,day,payment_method,profileUsername,selected_appo_id){
	payment_method = $("#payment_type").find(":selected").val();
	$("#day_iframe").attr('src', "day_frame.php?d=" + day + "&m=" + month + "&y=" + year + "&pm=" + payment_method + "&po=" + profileUsername + "&at=" + selected_appo_id);
}

function sanitizeSearchSymptomsMed(value,doctor,medsymp){

	$.post("includes/handlers/ajax_sanitize_search.php",{query:value}, function(data){

		getLiveSearchSymptomsMedicine(data,doctor,medsymp);
	});
}

function getLiveSearchSymptomsMedicine(value1,doctor1,medsymp1){

	$.post("includes/handlers/ajax_search_symptoms_medicine.php",{query:value1, doctor:doctor1, medsymp:medsymp1}, function(data){
		if(medsymp1 == "symptoms"){
			$('.search_symptoms_results').html(data);
		}		
		else if(medsymp1 == "medicines"){
			$('.search_medicines_results').html(data);
		}
	});
}

function sanitizeSearchHealth(value,type,lang){

	$.post("includes/handlers/ajax_sanitize_search.php",{query:value}, function(data){
		getLiveSearchHealth(data,type,lang);
	});
}

function getLiveSearchHealth(data,type,lang){
	$.post("includes/handlers/ajax_search_health.php",{query:data, type:type, lang:lang}, function(data){
		$('#search_' + type).html(data);
	});
}

function selectPreSearchResultHealth(title,type/*table name*/,lang){
	$("input[name='" + type + "_input']").val(title);
	$("input[name='" + type + "_input']").focus();
	sanitizeSearchHealth(title,type,lang);
}

function selectSearchResultHealth(title,id,type/*table name*/,additional = '0'){
	if(additional != '0'){
		$("input[name='" + type + "_" + additional + "_input']").val(title);
		$('.search_history_results').html("");
		$("input[name='" + type + "_" + additional + "_input']").focus();
		
		$("input[name='searched_id_" + type + "']").val(id);
	}
	else{
		$("input[name='" + type + "_input']").val(title);
		$('.search_history_results').html("");
		$("input[name='" + type + "_input']").focus();
		
		$("input[name='searched_id_" + type + "']").val(id);
	}
}

function selectSearchResult(i_title, prev_elements, med_symp){
	if(prev_elements == ""){
		var new_text = i_title + ", ";
	}
	else{
		var new_text = prev_elements + ", " + i_title + ", ";
	}

	if(med_symp == "symptoms"){
		$("#search_text_input_symptoms").val(new_text);
		$('.search_symptoms_results').html("");
		$("#search_text_input_symptoms").focus();
	}
	else if(med_symp == "medicines"){
		$("#search_text_input_medicines").val(new_text);
		$('.search_medicines_results').html("");
		$("#search_text_input_medicines").focus();
	}
}

function storeVal(info,type){
	var ajaxst = $.ajax({
		url: "includes/form_handlers/ajax_store_box_value.php",
		type: "POST",
		data: "info=" + info + "&type=" + type,
		cache: false,

		success: function(response){
		}
	});
}

function newOffice(office_number){
	var ajaxreq = $.ajax({
		url: "includes/form_handlers/ajax_new_office.php",
		type: "POST",
		data: "office_num=" + office_number,

		success: function(response){
			location.reload();
		}
	});
}

//David's functions
function sanitizeSearchInsurance(value,lang_col,search_col,results_div_id, input_div_id){
	$.post("includes/handlers/ajax_sanitize_search.php",{query:value}, function(data){

		getLiveSearchInsurance(data,lang_col,search_col,results_div_id, input_div_id);
	});
}

function getLiveSearchInsurance(value,lang_col,search_col,results_div_id, input_div_id){
	$.post("includes/handlers/ajax_search_insurance.php",{query:value,lang_col:lang_col,search_col:search_col,results_div_id:results_div_id, input_div_id:input_div_id}, function(data){
		$('#' + results_div_id).html(data);
	});
}

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

function sanitizeSearchSpecialization(value,lang_col,search_col,results_div_id, input_div_id, hidden_inp){
	$.post("includes/handlers/ajax_sanitize_search.php",{query:value}, function(data){
		getLiveSearchSpecialization(data,lang_col,search_col,results_div_id, input_div_id, hidden_inp);
	});
}

function getLiveSearchSpecialization(value,lang_col,search_col,results_div_id, input_div_id, hidden_inp){
	var prev_ids = $('#'+hidden_inp).val();
	$.post("includes/handlers/ajax_search_specialization.php",{query:value,prev_ids:prev_ids,lang_col:lang_col,search_col:search_col,results_div_id:results_div_id, input_div_id:input_div_id,hidden_inp:hidden_inp}, function(data){
		$('#' + results_div_id).html(data);
	});
}

function selectSearchResultToHidInp_old(i_title, id, results_div_id, input_div_id,hidden_inp){

	$('#' + input_div_id).val(i_title);
	$('#' + hidden_inp).val(id);
	$('#' + results_div_id).html("");

}

function selectSearchResultToHidInp(i_title, id, prev_elements, prev_ids, results_div_id, input_div_id, hidden_inp){
	if(prev_elements == ""){
		var new_text = i_title + ", ";
	}
	else{
		var new_text = prev_elements + ", " + i_title + ", ";
	}
	
	if(prev_ids == ""){
		var new_ids = id;
	}
	else{
		var new_ids = prev_ids + "," + id;
	}
	
	$('#' + input_div_id).val(new_text);
	$('#' + input_div_id).focus();
	$('#' + hidden_inp).val(new_ids);
	$('#' + results_div_id).html("");
	
}

function popUpPlanConfirm(){
	var plan = $("#post_plan").val();
	$.post("includes/handlers/ajax_sanitize_search.php",{query:plan}, function(data){
		//popUpPlanConfirm(data);
		$("#plan_preview").html(data);
	});
}

function searchDiagnosisCIE(query, usr, aid, diagnosis_num){
	//sanitize function is not required in these type of queries due to prepared statemtns and as the echo part is retrieved from a clean table
	$.post("includes/handlers/ajax_search_cie10.php",{query:query, usr:usr, aid:aid, diagnosis_num:diagnosis_num}, function(data){
		$("#search_diagnosis_dropdown_" + diagnosis_num).html(data);
	});
}

function selectSearchCIE(cie_code, usr, aid, diagnosis_num){
	var ajax = $.ajax({
		url: "includes/handlers/ajax_select_diagnosis_cie10.php",
		type: "POST",
		data: "cie_code=" + cie_code + "&usr=" + usr + "&aid=" + aid + "&diagnosis_num=" + diagnosis_num,
		dataType:"json",
		
		success: function(response){
			let desc = response.description;
			let code = response.code;
			//alert(desc);
			$("#search_diagnosis_dropdown_" + diagnosis_num).html("");
			$("#diag_code_" + diagnosis_num).html(code);
			$("#diag_desc_" + diagnosis_num).html(desc);
		}
	});
}

function removeClassActive(big_container_div, class_to_remove, class_to_add){
	$("." + big_container_div + " ." + class_to_remove).toggleClass(class_to_add);
	$("." + big_container_div + " ." + class_to_remove).toggleClass(class_to_remove);
}

function move_data_h_stats(key, stat){
	$.ajax({
		url: "includes/handlers/ajax_load_health_stats_review.php",
		type: "POST",
		data: "stat=" + stat + "&key=" + key,
		cache:false,

		success: function(data){
			//alert(data);
			$('#health_stats_point_review').html(data);
		}
	});
}

function load_insert_fields_h_stats(stat){
	//alert("here?");
	$.ajax({
		url: "includes/handlers/ajax_load_health_stats_inputs.php",
		type: "POST",
		data: "stat=" + stat,
		cache:false,

		success: function(data){
			//alert("here");
			//$('#health_stats_point_inputs').html("");
			$('#health_stats_point_inputs').html(data);
		}
	});
}

function assistDateInput(input,name_of_input){
	//checkDeletedChar(e);
	switch (name_of_input) {
		case 'new_data_date':
			var name_inp_final = 'new_data_date';
			break;			
	}	
	//alert(input);
	$.ajax({
		url: "includes/handlers/ajax_assist_date_input.php",
		type: "POST",
		data: "input=" + input,
		cache:false,

		success: function(data){
			//alert(data);
			$("input[name='" + name_inp_final + "']").val(data);
		}
	});
}

function assistTimeInput(input,name_of_input){
	//checkDeletedChar(e);
	switch (name_of_input) {
		case 'new_data_time':
			var name_inp_final = 'new_data_time';
			break;			
	}	
	//alert(input);
	$.ajax({
		url: "includes/handlers/ajax_assist_time_input.php",
		type: "POST",
		data: "input=" + input,
		cache:false,

		success: function(data){
			//alert(data);
			$("input[name='" + name_inp_final + "']").val(data);
		}
	});
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

function generate_bmi_data_stats(){
	$.ajax({
		url: "includes/handlers/ajax_generate_bmi.php",
		type: "POST",
		cache:false,

		success: function(data){
			//alert(data);
			load_insert_fields_h_stats("bmi");
			move_data_h_stats(0, "bmi");
			clearDrawGraphProfile();
			drawGraphProfile("bmi", 10);
			updateHStatButts("bmi");
		}
	});
}

function save_data_stats(stat,id = -1){
	//alert(id);
	if(id == -1){
		var data = $("input[name='new_data_value']").val();
		var date = $("input[name='new_data_date']").val();
		var time = $("input[name='new_data_time']").val();
		if(stat == 'bp'){
			var data2 = $("input[name='new_data_value2']").val();
		}
		else{
			var data2 = '0';
		}
	}
	else{
		var data = $("input[name='displayed_data_value']").val();
		var date = $("input[name='displayed_data_date']").val();
		var time = $("input[name='displayed_data_time']").val();
		if(stat == 'bp'){
			var data2 = $("input[name='displayed_data_value2']").val();
		}
		else{
			var data2 = '0';
		}
	}
	//alert(date);
	$.ajax({
		url: "includes/handlers/ajax_save_data_stats.php",
		type: "POST",
		data: "stat=" + stat + "&data=" + data + "&data2=" + data2 + "&date=" + date + "&time=" + time + "&id=" + id,
		cache:false,

		success: function(data){
			//alert(data);
			if(id == -1){
				load_insert_fields_h_stats(stat);
				move_data_h_stats(0, stat);
				clearDrawGraphProfile();
				drawGraphProfile(stat, 10);
			}
			else{
				move_data_h_stats(0, stat);
				clearDrawGraphProfile();
				drawGraphProfile(stat, 10);
			}
			
			updateHStatButts(stat);
			
		}
	});
}

function del_data_stats(stat,id){

	$.ajax({
		url: "includes/handlers/ajax_del_data_stats.php",
		type: "POST",
		data: "stat=" + stat + "&id=" + id,
		cache:false,

		success: function(data){
			//alert(data);
			move_data_h_stats(0, stat);
			clearDrawGraphProfile();
			drawGraphProfile(stat, 10);
			
			updateHStatButts(stat);
			
		}
	});
}

function updateHStatButts(stat){
	var lang = "<?php echo $_SESSION['lang'];?>";
	$.ajax({
		url: "includes/handlers/ajax_refresh_h_stats_butt.php",
		type: "POST",
		data: "stat=" + stat,
		dataType:"json",

		success: function(response){

			var data = response.data;
			var date_time = response.date_time;

			if(data != -1){
				$("#health_stat_title_" + stat).html(data);
				$("#health_stat_date_" + stat).text(date_time);
			}
			else{
				if(lang == "en"){
					$("#health_stat_title_" + stat).text("Insert Measurement");
				}
				else{
					$("#health_stat_title_" + stat).text("Insertar Medida");
				}
			}
		}
	});
}

function add_about_info(element){
	
	let data_str = "";
	switch (element) {
		case 'education':
			
			let edu_title = $("input[name='edu_title']").val();
			let edu_institution = $("input[name='edu_institution']").val();
			let edu_start_date = $("input[name='edu_start_date']").val();
			let edu_end_date = $("input[name='edu_end_date']").val();
			
			data_str = "about_element=" + element + "&edu_title=" + edu_title + "&edu_institution=" + edu_institution + "&edu_start_date=" + edu_start_date + "&edu_end_date=" + edu_end_date;
			break;
		case 'job':
			
			let job_title = $("input[name='job_title']").val();
			let job_institution = $("input[name='job_institution']").val();
			let job_start_date = $("input[name='job_start_date']").val();
			let job_end_date = $("input[name='job_end_date']").val();
			let job_checkbox = $("#job_current_check").is(":checked");  
			
			data_str = "about_element=" + element + "&job_title=" + job_title + "&job_institution=" + job_institution + "&job_start_date=" + job_start_date + "&job_end_date=" + job_end_date + "&job_checkbox=" + job_checkbox;
			break;
		case 'conference':
			
			let conf_title = $("input[name='conf_title']").val();
			let conf_role = $("input[name='conf_role']").val();
			let conf_date = $("input[name='conf_date']").val();
			
			data_str = "about_element=" + element + "&conf_title=" + conf_title + "&conf_role=" + conf_role + "&conf_date=" + conf_date;
			break;
		case 'description':
			let description = $("input[name='profile_description_inp']").val();
			data_str = "about_element=" + element + "&description=" + description;
			break;
		case 'webpage':
			let webpage_url = $("input[name='webpage_url']").val();
			let webpage_code = $("#webpage_code").find(":selected").val();
			data_str = "about_element=" + element + "&webpage_code=" + webpage_code + "&webpage_url=" + webpage_url;
			break;
		case 'publication':
			
			let publi_title = $("input[name='publi_title']").val();
			let publi_authors = $("input[name='publi_authors']").val();
			let publi_journal = $("input[name='publi_journal']").val();
			let publi_volume = $("input[name='publi_volume']").val();
			let publi_date = $("input[name='publi_date']").val();
			
			data_str = "about_element=" + element + "&publi_title=" + publi_title + "&publi_authors=" + publi_authors + "&publi_journal=" + publi_journal + "&publi_volume=" + publi_volume + "&publi_date=" + publi_date;
			break;
	}
	//alert(data_str);
	$.ajax({
		url: "includes/handlers/ajax_add_about_info.php",
		type: "POST",
		data: data_str,
		cache:false,

		success: function(data){
			//alert(data);
			read_about_info(element);
		}
	});
}

function read_about_info(element,profile_username = ''){
	$.ajax({
		url: "includes/handlers/ajax_read_about_info.php",
		type: "POST",
		data: "about_element=" + element + "&profile_username=" + profile_username,
		cache:false,

		success: function(data){
			//alert(data);
			switch (element) {
				case 'education':
					$('#education_editable_list').html(data);				
					break;
				case 'job':
					$('#jobs_editable_list').html(data);				
					break;
				case 'conference':
					$('#conferences_editable_list').html(data);				
					break;
				case 'description':
					$('#name_description').html(data);			
					break;
				case 'webpage':
					$('#websites_editable_list').html(data);				
					break;
				case 'publication':
					$('#publications_editable_list').html(data);				
					break;
			}
			
		}
	});
}

function delete_table_element(table, id_column, id){
	$.post("includes/handlers/ajax_delete_element.php", {t: table, idc: id_column, id: id}, function(data){
		read_about_info(table);
	});
}

function hide_add_about_info(element){
	$('#add_' + element + '_link').css({'display':'inline-block'});
	$('#add_' + element + '_holder').slideToggle('fast','linear');
}

function show_add_about_info(element){
	$('#add_' + element + '_link').css({'display':'none'});
	$('#add_' + element + '_holder').slideToggle('fast','linear');
}

function searchCalendar(term,isDoctor){
	$.ajax({
		url: "includes/handlers/ajax_search_calendar.php",
		type: "POST",
		data: "search=" + term + "&isDoctor=" + isDoctor,
		cache:  false,

		success: function(data){
			$('.search_cal_results').html(data);
			//$(".search_cal_results").css({"border" : "1px solid black"});
		}
	});
}

function openMessageSettings(){
	if($("#message_settings_dropdown").css("height") == "0px"){
		
		var ajaxreq = $.ajax({
			url: "includes/handlers/ajax_message_settings_dropdown.php",
			type: "POST",
			cache: false,

			success: function(response){
				$("#message_settings_dropdown").html(response);
				$("#message_settings_dropdown").css({"height" : "auto" ,"background-color": "#f5f5f5", "border" : "4px solid white"} );
			}
		});
	}
}

function closeDropdownMessageSettings(){
	if($("#message_settings_dropdown").css("height") != "0px"){
		$("#message_settings_dropdown").html("");
		$("#message_settings_dropdown").css({"height" : "0px" , "border" : "none"});
	}
}