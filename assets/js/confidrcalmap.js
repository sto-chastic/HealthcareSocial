$(document).ready(function(){
	let firstNLast;
	let map;
	//Functions for initial map marker download
	
	$("#address1").val($("#address1_office_1").val() + " " + $("#city_office_1").val());
	//alert($("#address1").val);
	$("#address2").val($("#address1_office_2").val() + " " + $("#city_office_2").val());
	$("#address3").val($("#address1_office_3").val() + " " + $("#city_office_3").val());
	//Functions for pin map location upon user request
	$("#address1_office_1").keyup(function(){
		$("#address1").val($(this).val() + " " + $("#city_office_1").val());
		});
	$("#city_office_1").keyup(function(){
		$("#address1").val($("#address1_office_1").val() + " " + $(this).val());
		});
	
	$("#city_office_1").click(function(){
		getPremiumCityLocation('', 'cityResults1', 'city_office_1');
		$("#cityResults1").toggle();
		});
	$("#cityResults1").click(function(){
		$('#cityResults1').css({"display" : "none"});
		});
	
	$("#prem_ad1cityName").keyup(function(){
		//clearHiddenDiv("#ds_city_code");
		//clearHiddenDiv("#ds_lat");
		//clearHiddenDiv("#ds_lng");
		getPremiumCityLocation(this.value, 'cityResults1', 'city_office_1');
		});
	
	$("#address1_office_2").keyup(function(){
		$("#address2").val($(this).val() + " " + $("#city_office_2").val());
		});
	$("#city_office_2").keyup(function(){
		$("#address2").val($("#address1_office_2").val() + " " + $(this).val());
		});
	
	$("#city_office_2").click(function(){
		getPremiumCityLocation('', 'cityResults2', 'city_office_2');
		$("#cityResults2").toggle();
		});
	$("#cityResults2").click(function(){
		$('#cityResults2').css({"display" : "none"});
		});
	
	$("#prem_ad2cityName").keyup(function(){
		getPremiumCityLocation(this.value, 'cityResults2', 'city_office_2');
		});
	
	$("#address1_office_3").keyup(function(){
		$("#address3").val($(this).val() + " " + $("#city_office_3").val());
		});
	$("#city_office_3").keyup(function(){
		$("#address3").val($("#address1_office_3").val() + " " + $(this).val());
		});
	
	$("#city_office_3").click(function(){
		getPremiumCityLocation('', 'cityResults3', 'city_office_3');
		$("#cityResults3").toggle();
		});
	$("#cityResults3").click(function(){
		$('#cityResults3').css({"display" : "none"});
		});
	$("#prem_ad3cityName").keyup(function(){
		getPremiumCityLocation(this.value, 'cityResults3', 'city_office_3');
		});
	
	//Adding office types
	
	$("#appo_type_form").submit(function(event){
		event.preventDefault();
		$.post("includes/handlers/ajax_add_appo_type.php", 
				{	appo_desc: $("#appo_description").val(),
					appo_duration: $("#appo_type_form_duration").val(), 
					cost_input: $("#cost_input").val() 
					},
				function(data){
						if(data.substring(0,6) == "Error."){
							$("#max_appo_types_error" ).html(data.substring(7));
						}
						else{
							$("#max_appo_types_error" ).html("");
							$('[name="added_appo_box"]').html(data);
						}	
			}											
		);
	});
});
/* -----------------------------End document.ready --------------------------------------*/
var map;
var infoWindow;
var markers = [];
//function myMap() {
//	  var mapCanvas = document.getElementById("map");
//	  var mapOptions = {
//	    center: new google.maps.LatLng(4.71, -74.072),
//	    zoom: 11,
//	    minZoom:4,
//        maxZoom:17,
////	    disableDefaultUI: true,
////	    zoomControl: true,
////	    scaleControl:true,
//	    mapTypeControl:false
//	  };
//	  checkForRepeatMarkers();
//	  map = new google.maps.Map(mapCanvas ,mapOptions);
//	}

function insertMarker() {
	var pos = new google.maps.LatLng(4.71, -74.072)
	 var marker = new google.maps.Marker({
	        position: pos,
	        title: 'new marker',
	        map: map
	    });
	 	marker.setMap(map);
	    map.setCenter(marker.getPosition());
}

function downloadBaseMapMarkers(url) {
	$.post(url, function(data){
          var xml = data;
          var markersXML = xml.documentElement.getElementsByTagName('marker');
          var latlngBounds = new google.maps.LatLngBounds();
          Array.prototype.forEach.call(markersXML, function(markerElem) {
        	  	var officeNick = markerElem.getAttribute('officeNick');
        	  	var id = markerElem.getAttribute('officeid');
            var name = markerElem.getAttribute('names');
            var address1 = markerElem.getAttribute('aline1');
            var address2 = markerElem.getAttribute('aline2');
            var address3 = markerElem.getAttribute('aline3');
            var point = new google.maps.LatLng(
                parseFloat(markerElem.getAttribute('lat')),
                parseFloat(markerElem.getAttribute('lng')));
            
            //var icon = customLabel[type] || {};
           
            var marker = new google.maps.Marker({
              map: map,
              position: point,
              id: id,
              icon: {url:'./assets/images/icons/icon_map.png',
            	  		scaledSize: new google.maps.Size(55, 55)}
            });
            
         // Infowindow for each doctor
            var infoContent = officeNick +  "<br>" +
            		//"<b>" + id.match(/\d+/) + ". " + 
            		"<b>" + name +"</b>"+ "<br>" 
            		+ address1 + "<br>" 
            		+ address2 + "<br>"
            		+ address3;
            var infoWindow = new google.maps.InfoWindow({
            		content:infoContent,
            		maxWidth:400
            });
            
            
            //mLarker action functions
//            $("[name=ds_result_"+id.match(/\d+/)+"]").hover(function(){
//            			if (marker.get('id').match(/\d+/)==$(this).attr('name').slice(10)){
//            				marker.setIcon(icons['parking'].icon);
//            			}
//    				},function(){
//    					if (marker.get('id').match(/\d+/)==$(this).attr('name').slice(10)){
//    					marker.setIcon(icons['parking'].icon);
//    					}
//    				});
//            $(".ds_address_box").hover(function(){
//            		var id = $(this).attr('id').split(" ");
//	    			if (marker.get('id')==id[0].slice(10)){
//	    				marker.setIcon(icons['parking'].icon);
//	    			} else if (true) {
//	    				//doNothing;
//	    			}
//				},function(){
//					marker.setIcon(icons['parking'].icon);
//				});
            marker.addListener('mouseover', function() {
              infoWindow.open(map, marker);
              marker.setIcon({url:'./assets/images/icons/icon_map_select.png',
	      	  		scaledSize: new google.maps.Size(50, 50)});
            });
            marker.addListener('mouseout', function() {
                infoWindow.close();
                marker.setIcon({url:'./assets/images/icons/icon_map.png',
	      	  		scaledSize: new google.maps.Size(50, 50)});
              });
            marker.setMap(map);
            latlngBounds.extend(point);
            markers.push(marker);
            });
          	map.fitBounds(latlngBounds);
//          	if(markers.length !=0) {
//        		for (i=0; i < markers.length; i++) {
//        	        var currentMarker = markers[i];
//        	        var pos = currentMarker.getPosition();
//        	        if (latlng.equals(pos)) {
//        	            text = text + " & " + content[i];
//        	        }
//        	    }
//        	}
          },'xml');

}


function checkForRepeatMarkers() {
	
}

function initLocatorMap() { 
		map = new google.maps.Map(document.getElementById('map'), {
		center: new google.maps.LatLng(4.71, -74.072),
	    zoom: 6,
	    minZoom:5,
        maxZoom:17,
	    mapTypeControl:false

	}); 
	
	downloadBaseMapMarkers("includes/handlers/ajax_setDocMapMarkers.php");
	var geocoder1 = new google.maps.Geocoder();
	var geocoder2 = new google.maps.Geocoder();
	var geocoder3 = new google.maps.Geocoder();
	document.getElementById('geocode1').addEventListener('click', function() { geocodeAddress(geocoder1, map, 1); 
	});
	document.getElementById('geocode2').addEventListener('click', function() { geocodeAddress(geocoder2, map, 2); 
	});
	document.getElementById('geocode3').addEventListener('click', function() { geocodeAddress(geocoder3, map, 3); 
	});
}

let currentMarker = [];

function getUserData(){
	$.post("includes/handlers/ajax_get_user_info.php", function(data){
		callBackName(data);
	});
}

function callBackName(names){
	firstNLast = names;
}

function geocodeAddress(geocoder, resultsMap, officenumber) {
	getUserData();
	//alert(markers.join('\n'));
	if(officenumber==1){
		var address = document.getElementById('address1').value;	
		//alert(document.getElementById('address1').value);
	} else if (officenumber==2){
		var address = document.getElementById('address2').value;
		//alert(document.getElementById('address2').value);
	} else if (officenumber==3){
		var address = document.getElementById('address3').value;
	}
	 
	geocoder.geocode({
		'address': address, 
		componentRestrictions: {
			country: 'CO'
			}
		}, function(results, status) {
				if (status === 'OK') {
					if (currentMarker[0]){
						prevMarker = currentMarker[0];
						prevMarker.setMap(null);
					}
					resultsMap.setCenter(results[0].geometry.location); 
					resultsMap.setZoom(15);
					 // biblioteca de iconos a los cuales se puede acceder
		            var iconBase = './assets/images/icons/icon_map.png';
		            var icons = {
		              confidr1: {
		                icon: iconBase
		                
		              },
		              confidr2: {
		                icon: iconBase 
		              },
		              confidr3: {
		                icon: iconBase 
		              }
		            };
		            //
					var marker = new google.maps.Marker({ 
						map: resultsMap, 
						position: results[0].geometry.location,
						draggable:true,
						icon: {url:icons['confidr1'].icon, scaledSize: new google.maps.Size(45, 45)} 
						});
					currentMarker[0] = marker;
					
					switch(officenumber){
						case 1: {
							var infoContent = "<b>" + firstNLast +"</b><br>" 
							            		+ $("#address1_office_1").val() + "<br>" 
							            		+ $("#address2_office_1").val() + "<br>"
							            		+ $("#address3_office_1").val();
							$("#lat_ad1").val(marker.getPosition().lat());
				            $("#lng_ad1").val(marker.getPosition().lng());
							break;
						}
						case 2: {
							var infoContent = "<b>" + firstNLast +"</b><br>" 
							            		+ $("#address1_office_2").val() + "<br>" 
							            		+ $("#address2_office_2").val() + "<br>"
							            		+ $("#address3_office_2").val();
							$("#lat_ad2").val(marker.getPosition().lat());
				            $("#lng_ad2").val(marker.getPosition().lng());
							break;
						}
						case 3: {
							var infoContent = "<b>" + firstNLast +"</b><br>" 
							            		+ $("#address1_office_3").val() + "<br>" 
							            		+ $("#address2_office_3").val() + "<br>"
							            		+ $("#address3_office_3").val();
							$("#lat_ad3").val(marker.getPosition().lat());
				            $("#lng_ad3").val(marker.getPosition().lng());
							break;
						}
					}
					
		            var infoWindow = new google.maps.InfoWindow({
		            		content:infoContent,
		            		maxWidth:400
		            });
		            
		            marker.addListener('mouseover', function() {
		                infoWindow.open(map, marker);
		                marker.setIcon({url:'./assets/images/icons/icon_map_select.png',
			      	  		scaledSize: new google.maps.Size(50, 50)});
		              });
		              marker.addListener('mouseout', function() {
		            	infoWindow.close();
		            	marker.setIcon({url:'./assets/images/icons/icon_map.png',
			      	  		scaledSize: new google.maps.Size(50, 50)});
		                });
	              $("#prem_lat").val(marker.getPosition().lat());
	              $("#prem_lng").val(marker.getPosition().lng());
				} 
				else { 
					alert('Geocode was not successful for the following reason: ' + status); 
				} 
			}); 
}















