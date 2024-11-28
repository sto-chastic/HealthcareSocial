$(document).ready(function(){
	let firstNLast;
	$("[name^='ds_result_']").hover(function(){
		$(this).css("background-color", "#f5f5f5");
		$(this).find(".ds_doctor_data").css("border-left", "calc(3px + 0.7vw)solid #f38ead");
		
	},function(){
		$(this).css("background-color", "white");
		$(this).find(".ds_doctor_data").css("border-left", "calc(3px + 0.7vw)solid #eee");
	});
	
	let wasInactive;
	$(".ds_address_box").hover(function(){
		wasInactive = $(this).hasClass("inactive");
		//alert(isActive ? "true":"false");
		if(wasInactive){
			$(this).siblings("[name='active']").addClass("inactive");
			$(this).removeClass("inactive");
		}
		else {
			//doNothing;
		}
	},function(){
		if(wasInactive && $(this).attr('name') != "active"){
			$(this).siblings("[name='active']").removeClass("inactive");
			$(this).addClass("inactive");
		} 
	});	
});
/* -----------------------------End document.ready --------------------------------------*/
var map;
var infoWindow;
var markers = [];


function myMap() {
	  var mapCanvas = document.getElementById("map");
	  var mapOptions = {
	    center: new google.maps.LatLng(4.71, -74.072),
	    zoom: 11,
	    minZoom:4,
        maxZoom:17,
//	    disableDefaultUI: true,
//	    zoomControl: true,
//	    scaleControl:true,
	    mapTypeControl:false
	  };
	  map = new google.maps.Map(mapCanvas ,mapOptions);
	  downloadMapMarkers("includes/handlers/ajax_setMapMarkers.php");
	  checkForRepeatMarkers();
	}

function insertMarker() {
	var pos = new google.maps.LatLng(4.71, -74.072)
	 var marker = new google.maps.Marker({
	        position: pos,
	        title: 'new marker',
	        map: map,
	        icon: {url:'./assets/images/icons/icon_map.png',
    	  		scaledSize: new google.maps.Size(50, 50)}
	    });
	 	marker.setMap(map);
	    map.setCenter(marker.getPosition());
	    
    
   
}

function downloadMapMarkers(url) {
	$.post(url, function(data){
          var xml = data;
          var markersXML = xml.documentElement.getElementsByTagName('marker');
          var latlngBounds = new google.maps.LatLngBounds();
          Array.prototype.forEach.call(markersXML, function(markerElem) {
            var id = markerElem.getAttribute('pageid');
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
      	  		scaledSize: new google.maps.Size(50, 50)},
      	  	  label: {color: 'white', fontSize: '12px', fontWeight: '600',fontFamily: 'Coves-Bold', text: ""+id.match(/\d+/)+""}	
      	  		
              
            });
            
            
            	
            
         // Infowindow for each doctor
            var infoContent = "<b>" + id.match(/\d+/) + "." + name +"</b>"+ "<br>" 
            		+ address1 + "<br>" 
            		+ address2 + "<br>"
            		+ address3;
            var infoWindow = new google.maps.InfoWindow({
            		content:infoContent,
            		maxWidth:400
            });
            
            
            //mLarker action functions
            $("[name=ds_result_"+id.match(/\d+/)+"]").hover(function(){
            			if (marker.get('id').match(/\d+/)==$(this).attr('name').slice(10)){
            				marker.setIcon({url:'./assets/images/icons/icon_map_select.png',
				      	  		scaledSize: new google.maps.Size(50, 50)});
            				
            			}
    				},function(){
    					if (marker.get('id').match(/\d+/)==$(this).attr('name').slice(10)){
    						marker.setOptions({
    							icon: {url:'./assets/images/icons/icon_map.png',
    				      	  		scaledSize: new google.maps.Size(50, 50)}
    	    					});
    					}
    				});
            $(".ds_address_box").hover(function(){
            		var id = $(this).attr('id').split(" ");
	    			if (marker.get('id')==id[0].slice(10)){
	    				marker.setIcon({url:'./assets/images/icons/icon_map_select.png',
			      	  		scaledSize: new google.maps.Size(50, 50)});
	    			} else if (true) {
	    				//doNothing;
	    			}
				},function(){
					
					marker.setOptions({
						icon: {url:'./assets/images/icons/icon_map.png',
			      	  		scaledSize: new google.maps.Size(50, 50)}
    					});
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
            marker.setMap(map);
            latlngBounds.extend(point);
            markers.push(marker);
            });
          	map.fitBounds(latlngBounds);
          	if(markers.length !=0) {
          		//var thisMarkerPos = marker.position;
          		var thisMarkerPos = 0;
        		for (i=0; i < markers.length; i++) {
        				
        	        var currentMarker = markers[i];
        	        var pos = currentMarker.getPosition();
        	
        	        if (thisMarkerPos == pos) {
        	            text = text + " & " + content[i];
        	        }
        	    }
        	}
          },'xml');
//    var request = window.ActiveXObject ?
//        new ActiveXObject('Microsoft.XMLHTTP') :
//        new XMLHttpRequest;
//    request.onreadystatechange = function() {
//      if (request.readyState == 4) {
//        request.onreadystatechange = doNothing;
//        callback(request, request.status);
//      }
//    };
//    request.open('GET', url, true);
//    request.send(null);
}
// Dummy function to manage request from server
//function doNothing() {}

function checkForRepeatMarkers() {
	
}

function drawCircle (pos, rad) {
	var myCirc = new google.maps.Circle({
		center: pos,
		radius:rad,
		strokeColor:"#000000",
		strokeOpacity:1,
		strokeWeight:2,
		fillColor:"#0000FF",
		fillOpacity:0.3
	});
	myCirc.setMap(map);	
}

function initLocatorMap() { 
	var map = new google.maps.Map(document.getElementById('map'), {
		center: new google.maps.LatLng(4.71, -74.072),
	    zoom: 5,
	    minZoom:5,
        maxZoom:17,
	    mapTypeControl:false

	}); 
	var geocoder = new google.maps.Geocoder();
	document.getElementById('geocode').addEventListener('click', function() { geocodeAddress(geocoder, map); 
	}); 
}

let currentMarker = [];
let firstNLast;
function getUserData(){
	$.post("includes/handlers/ajax_get_user_info.php", function(data){
		callBackName(data);
	});
}

function callBackName(names){
	firstNLast = names;
}

function geocodeAddress(geocoder, resultsMap) {
	getUserData();
	var address = document.getElementById('address').value; 
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
					//premium2.php
					var infoContent = "<b>" + firstNLast +"</b>"+ "<br><p>" 
		            		+ $("#prem_ad1ln1").val() + "</p>" 
		            		+ $("#prem_ad1ln2").val() + "<br>"
		            		+ $("#prem_ad1ln3").val();
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















