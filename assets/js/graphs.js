function closeGraphWindow(){
	$(".graph_window").css({"padding": "0px", "height" : "0px" , "border" : "none"});
	$("#canvas_graph_div").html("<canvas id='canvas_graph' width='500px' height='260px'>Your browser does not support the canvas element.</canvas>");
}

function clearDrawGraphProfile(){
	$("#canvas_graph_div").html('');
	$("#canvas_graph_div").html('<canvas id="canvas_graph_index" width="400px" height="180px">Your browser does not support the canvas element.</canvas>');
}

function drawGraph(patient_user,doctor_user,aid,table,num_points){
	if($(".graph_window").css("height") == "0px"){
		
		var ajaxreq = $.ajax({
			url: "includes/handlers/ajax_draw_graph.php",
			type: "POST",
			data: "patient_user=" + patient_user + "&doctor_user=" + doctor_user + "&aid=" + aid + "&table=" + table + "&num_points=" + num_points,
			dataType:"json",

			success: function(response){
				$(".graph_window").css({"padding": "0px", "height" : "270px" , "border" : " 3px solid #f38ead", "box-shadow":" 0px 3px 1px #ccc" ,"border-radius" : "3px"});
				
				var lang = "<?php echo $_SESSION['lang'];?>";
				//invert arrays
				var date_time = new Array();
				for(i=0;i<response.date_time.length;i++){
					date_time[i] = response.date_time[response.date_time.length - i - 1];
				}
				var data = response.data;
				var data2 = response.data2;
				var title = response.title;
				var min_arr = Math.min(Math.min.apply(null, data),Math.min.apply(null, data2)) ,
			    		max_arr = Math.max(Math.max.apply(null, data),Math.max.apply(null, data2));
				var num_lines = 4;
				var height_y = 150, division_unit = (max_arr - min_arr)/num_lines, width_x = 500;
				var fact = (max_arr-min_arr);
				var num_points = data.length;
				var line_space = new Array();
				var line_numbers = new Array();
				var order = 0, neg_order = 0;
				var point_values = new Array();
				
				var message_spacing = (width_x - 30 - 180)/(num_points-1); //30 is the point where the bottom line starts horizontally; 180 is the width of the message
				
				var bottom_space = 50;
				
				if(max_arr != min_arr){
					order = Math.floor(Math.log(max_arr - min_arr) / Math.LN10 + 0.000000001);
					order = order - 1;
					neg_order = -order;
				}
				
				var division_unit_n = division_unit*Math.pow(10,neg_order);
				division_unit_n = Math.round(division_unit_n);
				
				//Set points XY-axes position
				
				var point_coords =  new Array();
				var point_coords2 =  new Array();
				var point_x_spacing = (width_x - 70)/(num_points-1);
				for(i=0;i<num_points;i++){
					temp_x = 50 + point_x_spacing*i;
					
					temp = (data[num_points - i - 1] - min_arr)/fact;
					temp_y = temp*height_y;
				
					point_coords[i] = new Array(temp_x,temp_y);
					point_values[i] = data[num_points - i - 1];
				}
				
				//Set position of the lines
				for(i=0;i<=num_lines;i++){
					temp =  min_arr + division_unit_n*i*Math.pow(10,order); //gives the label of the line
					line_numbers[i] = temp;
					temp = (temp-min_arr)/fact;
					line_space[i] = height_y*temp; //gives the actual position of the line
				}
				
				//TODO: Check array has at least 2 elements
				
				var canvas = document.getElementById('canvas_graph');
				var context = canvas.getContext('2d');
				context.translate(0.5, 0.5);
				//context.clearRect(0, 0, canvas.width, canvas.height);
				context.transform(1, 0, 0, -1, 0, canvas.height);
				context.translate(0, bottom_space);
				
				//Bottom axis line
				context.beginPath();
		        context.moveTo(30, 0); //Starts bottom line at position 30, not 0
		        context.lineTo(width_x, 0);
		        context.strokeStyle = '#000';
		        context.lineWidth = 2;
		        context.stroke();
		        context.save();
				
				//Print grid lines
				for(i=1;i<=num_lines;i++){
					context.setLineDash([2, 6])
					context.beginPath();
			        context.moveTo(30, line_space[i]);
			        context.lineTo(width_x, line_space[i]);
			        context.strokeStyle = 'rgb(93, 93, 93)';
			        context.lineWidth = 1;
			        context.stroke();
				}

				context.restore();
		        
				//Print grid line numbers
				for(i=0;i<=num_lines;i++){

					//Moves the coordinate axis so that each number can be refleted properly
					context.translate(0, line_space[i]);

					context.fillStyle = "grey";
					context.scale(1, -1);
					context.font = "10px Coves";
					context.textAlign = "left";
					context.fillText(line_numbers[i].toPrecision(3), 0, 0);

					//Revert to previous coordinate axis
					context.scale(1, -1);
					context.translate(0, -line_space[i]);
				}
				
				//Print line connecting points
		        
				for(i=0;i<num_points;i++){
					if(i==0){
						context.beginPath();
						context.lineJoin="round";
						context.moveTo(point_coords[i][0],point_coords[i][1]);
					}
					else{
						context.lineTo(point_coords[i][0],point_coords[i][1]);
					}
				}
		        context.strokeStyle = 'rgb(203, 110, 141)';
		        context.lineWidth = 5;
				context.stroke();
				context.restore();
				
				
				//Print points
		        
				for(i=0;i<num_points;i++){
					context.beginPath();
					context.arc(point_coords[i][0],point_coords[i][1],5,0,2*Math.PI);
			        context.strokeStyle = 'rgb(93, 93, 93)';
			        context.lineWidth = 5;
			        context.fillStyle = 'rgb(203, 110, 141)';
			        context.fill();
					context.stroke();
					
					//Moves the coordinate axis
					context.translate(point_coords[i][0] - 5, point_coords[i][1] + 20);

					context.fillStyle = "rgb(100, 100, 100)";
					context.scale(1, -1);
					context.font = "16px Coves-Bold";
					context.textAlign = "left";
					context.fillText(point_values[i], 0, 0);

					//Revert to previous coordinate axis
					context.scale(1, -1);
					context.translate(-point_coords[i][0] + 5, -point_coords[i][1] - 20);
				}
				
				//Print second set of values
				if(data2.length > 0){
					
					var point_coords2 =  new Array();
					var point_values2 = new Array();
					for(i=0;i<num_points;i++){
						temp_x = 50 + point_x_spacing*i;
						
						temp = (data2[num_points - i - 1] - min_arr)/fact;
						temp_y = temp*height_y;
					
						point_coords2[i] = new Array(temp_x,temp_y);
						point_values2[i] = data2[num_points - i - 1];
					}
					
					//Print line connecting points
			        
					for(i=0;i<num_points;i++){
						if(i==0){
							context.beginPath();
							context.lineJoin="round";
							context.moveTo(point_coords2[i][0],point_coords2[i][1]);
						}
						else{
							context.lineTo(point_coords2[i][0],point_coords2[i][1]);
						}
					}
			        context.strokeStyle = 'rgb(155,205,230)';
			        context.lineWidth = 5;
					context.stroke();
					context.restore();
					
					
					//Print points
			        
					for(i=0;i<num_points;i++){
						context.beginPath();
						context.arc(point_coords2[i][0],point_coords2[i][1],5,0,2*Math.PI);
				        context.strokeStyle = 'rgb(93, 93, 93)';
				        context.lineWidth = 5;
				        context.fillStyle = 'rgb(203, 110, 141)';
				        context.fill();
						context.stroke();
						
						//Moves the coordinate axis
						context.translate(point_coords2[i][0] - 5, point_coords2[i][1] + 20);

						context.fillStyle = "rgb(100, 100, 100)";
						context.scale(1, -1);
						context.font = "16px Coves";
						context.textAlign = "left";
						context.fillText(point_values2[i], 0, 0);

						//Revert to previous coordinate axis
						context.scale(1, -1);
						context.translate(-point_coords2[i][0] + 5, -point_coords2[i][1] - 20);
					}
				}
				
				//Print title
				
				var title_height = height_y + 35;
				
				context.translate(canvas.width/2 - 30, title_height); //The 30 is for the translation of the grid of 30 points to the right for the begining of the bottom line

				context.fillStyle = "rgb(93, 93, 93)";
				context.scale(1, -1);
				context.font = "20px Coves";
				context.textAlign = "left";
				context.fillText(title, 0, 0);

				context.scale(1, -1);
				context.translate(-canvas.width/2 + 30, -title_height);
				
				//Print conventions if required
				
				if(data2.length > 0){
					if(title = "bp"){
						var conv1 = "- sys";
						var conv2 = "- dias";
					}
					//draw line 1
					context.beginPath();
					context.lineJoin="round";
					context.moveTo(canvas.width/2 - 30 + 60, title_height + 10);
					context.lineTo(canvas.width/2 - 30 + 90, title_height + 10);
			        context.strokeStyle = 'rgb(203, 110, 141)';
			        context.lineWidth = 5;
					context.stroke();
					
					//draw line 2
					context.beginPath();
					context.lineJoin="round";
					context.moveTo(canvas.width/2 - 30 + 60, title_height);
					context.lineTo(canvas.width/2 - 30 + 90, title_height);
			        context.strokeStyle = 'rgb(155,205,230)';
			        context.lineWidth = 5;
					context.stroke();
					
					//Print convention 1
					context.translate(canvas.width/2 - 30 + 100, title_height + 10 - 3); //The 30 is for the translation of the grid of 30 points to the right for the begining of the bottom line

					context.fillStyle = "grey";
					context.scale(1, -1);
					context.font = "10px Coves";
					context.textAlign = "left";
					context.fillText(conv1, 0, 0);

					context.scale(1, -1);
					context.translate(-canvas.width/2 + 30 - 100, -title_height - 10 + 3);
					
					//Print convention 2
					context.translate(canvas.width/2 - 30 + 100, title_height - 3); //The 30 is for the translation of the grid of 30 points to the right for the begining of the bottom line

					context.fillStyle = "grey";
					context.scale(1, -1);
					context.font = "10px Coves";
					context.textAlign = "left";
					context.fillText(conv2, 0, 0);

					context.scale(1, -1);
					context.translate(-canvas.width/2 + 30 - 100, -title_height + 3);
				}
				
				//Print bottom-left instructions
				
	      		context.translate(30, -45);
	      		
				context.fillStyle = "rgb(93, 93, 93)";
				context.scale(1, -1);
				context.font = "14px Coves";
				context.textAlign = "left";
				if(lang == "en"){
					context.fillText("Move mouse over the points to see their dates.", 0, 0);
				}
				else{
					context.fillText("Mueve el cursor sobre los puntos para ver las fechas.", 0, 0);
				}
				
				context.scale(1, -1);
				context.translate(-30, 45);

		   		function writeValues(canvas, mouse_x, mouse_y, point_coords, point_coords2, point_values,date_time,point_x_spacing,message_spacing) {
					var context1 = canvas.getContext('2d');
					var contact_radious = point_x_spacing/2;
					var RR = Math.pow(contact_radious,2);
					for(i=0;i<num_points;i++){
						temp_rr = Math.pow(point_coords[i][0]-mouse_x,2) + Math.pow(point_coords[i][1]-mouse_y,2);
						if(point_coords2 !== 0){
							temp_rr2 = Math.pow(point_coords2[i][0]-mouse_x,2) + Math.pow(point_coords2[i][1]-mouse_y,2);
						}
						else{
							temp_rr2 = RR+1;
						}
						if(temp_rr < RR || temp_rr2 < RR){
							//printPointValue(point_coords[i], point_values[i]); Temporary deprecated
							message_movement_quantity = message_spacing*i;
							printDates(date_time[i],message_movement_quantity);
							
							//Print lines connecting the points to the dates
							temp_x = 50 + point_x_spacing*i;
							context.beginPath();
							context.lineJoin="round";
							context.moveTo(temp_x,-30);
							context.lineTo(temp_x,-8);
					        context.strokeStyle = 'rgb(93, 93, 93)';
					        context.lineWidth = 2;
							context.stroke();
							context.restore();
						}
					}
				}
		      	function getMousePossition(canvas, evt) {
			        var canvRectangle = canvas.getBoundingClientRect();
			        return {
			        		x: evt.clientX - canvRectangle.left,
			        		y: -(evt.clientY - canvRectangle.bottom) - bottom_space
			        };
			      }
		      	
		      	function printPointValue(point_coords,point_value)/*currently not used, but working*/{
					//Moves the coordinate axis
					context.translate(point_coords[0], point_coords[1] + 20);

					context.fillStyle = "black";
					context.scale(1, -1);
					context.font = "12px Coves";
					context.textAlign = "left";
					context.fillText(point_value, 0, 0);

					//Revert to previous coordinate axis
					context.scale(1, -1);
					context.translate(-point_coords[0], -point_coords[1] - 20);
		      	}
		      	
		      	function printDates(date_time,message_movement_quantity){
		      		var MILLISECS_PER_HOUR = 60 /* min/hour */ * 60 /* sec/min */ * 1000 /* ms/s */;
		      		date_time = date_time.replace(" ", "T");
		      		formatedDate = new Date(Date.parse(date_time + "Z") + 5*MILLISECS_PER_HOUR);
		      		optionsDate = { year: "2-digit", month: "short",  
		      			    day: "2-digit" };
		      		optionsTime = {hour: "2-digit",  
		      				minute: "2-digit", year: "2-digit", month: "short",  
		      			    day: "2-digit", hour12: "true", timeZone : 'America/Bogota'};
					
		      		if(lang == "en"){
		      			displayedText =  "From: " + /*formatedDate.toLocaleDateString("en-GB", optionsDate) + */ formatedDate.toLocaleDateString("en-GB", optionsTime);
					}
					else{
						displayedText =  "Del: " + /*formatedDate.toLocaleDateString("en-GB", optionsDate) + */ formatedDate.toLocaleDateString("es-ES", optionsTime);
					}
		      		
		      		
		      		//The rectangle that clears previous inputs
		      		context.clearRect(30, -50, canvas.width, 42);
		      		
		      		//Moves the coordinate axis
		      		context.translate(30 + message_movement_quantity, -43);
		      		
					context.fillStyle = "rgb(93, 93, 93)";
					context.scale(1, -1);
					context.font = "16px Coves";
					context.textAlign = "left";
					context.fillText(displayedText, 0, 0);

					//Revert to previous coordinate axis
					context.scale(1, -1);
					context.translate(-30 - message_movement_quantity, 43);
		      	}
			
			    canvas.addEventListener('mousemove', function(evt) {
			    		var point_x_spacing_t = point_x_spacing;
			    		var message_spacing_t = message_spacing;
			    		var points = point_coords;
			    		if(data2.length > 0){
			    			var points2 = point_coords2;
			    		}
			    		else{
			    			var points2 = 0;
			    		}
			    		var point_values_t = point_values;
			    		var date_time_t = date_time;
			    		var mousePos = getMousePossition(canvas, evt);
			     	writeValues(canvas, mousePos.x, mousePos.y, points, points2, point_values_t,date_time_t,point_x_spacing,message_spacing);
			      	
			    }, false);
			},
			error: function(jqXHR, exception) {
				if (jqXHR.status === 404) {
					var name_id = table + "_g_button";
					var name_id2 = table + "_g_toggle";
					var element = document.getElementById(name_id);
					var butt = document.getElementById(name_id2);
					
					if (element) {
						element.backgroundColor = "red";
						element.innerHTML = "<b>NA</b>";
						butt.removeAttribute('onclick');
						butt.removeAttribute('href');
					}
				}
			}
		});		
	}
}





function drawGraphProfile(table,num_points){
	var ajaxreq = $.ajax({
		url: "includes/handlers/ajax_draw_graph.php",
		type: "POST",
		data: "table=" + table + "&num_points=" + num_points,
		dataType:"json",

		success: function(response){
			//invert arrays
			//alert(response);
			var lang = "<?php echo $_SESSION['lang'];?>";
			var date_time = new Array();
			for(i=0;i<response.date_time.length;i++){
				date_time[i] = response.date_time[response.date_time.length - i - 1];
			}
			var data = response.data;
			var data2 = response.data2;
			var title = response.title;
			var min_arr = Math.min(Math.min.apply(null, data),Math.min.apply(null, data2)) ,
		    		max_arr = Math.max(Math.max.apply(null, data),Math.max.apply(null, data2));
			var num_lines = 4;
			var height_y = 80, division_unit = (max_arr - min_arr)/num_lines, width_x = 350;
			var fact = (max_arr-min_arr);
			var num_points = data.length;
			var line_space = new Array();
			var line_numbers = new Array();
			var order = 0, neg_order = 0;
			var point_values = new Array();
			
			var message_spacing = (width_x - 30 - 180)/(num_points-1); //30 is the point where the bottom line starts horizontally; 180 is the width of the message
			
			var bottom_space = 50;
			
			if(max_arr != min_arr){
				order = Math.floor(Math.log(max_arr - min_arr) / Math.LN10 + 0.000000001);
				order = order - 1;
				neg_order = -order;
			}
			
			var division_unit_n = division_unit*Math.pow(10,neg_order);
			division_unit_n = Math.round(division_unit_n);
			
			//Set points XY-axes position
			
			var point_coords =  new Array();
			var point_coords2 =  new Array();
			var point_x_spacing = (width_x - 70)/(num_points-1);
			for(i=0;i<num_points;i++){
				temp_x = 50 + point_x_spacing*i;
				
				temp = (data[num_points - i - 1] - min_arr)/fact;
				temp_y = temp*height_y;
			
				point_coords[i] = new Array(temp_x,temp_y);
				point_values[i] = data[num_points - i - 1];
			}
			
			//Set position of the lines
			for(i=0;i<=num_lines;i++){
				temp =  min_arr + division_unit_n*i*Math.pow(10,order); //gives the label of the line
				line_numbers[i] = temp;
				temp = (temp-min_arr)/fact;
				line_space[i] = height_y*temp; //gives the actual position of the line
			}
			
			//TODO: Check array has at least 2 elements
			
			var canvas = document.getElementById('canvas_graph_index');
			var context = canvas.getContext('2d');
			context.translate(0.5, 0.5);
			context.transform(1, 0, 0, -1, 0, canvas.height);
			context.translate(0, bottom_space);
			
			//Bottom axis line
			context.beginPath();
	        context.moveTo(30, 0); //Starts bottom line at position 30, not 0
	        context.lineTo(width_x, 0);
	        context.strokeStyle = '#000';
	        context.lineWidth = 2;
	        context.stroke();
	        context.save();
			
			//Print grid lines
			for(i=1;i<=num_lines;i++){
				context.setLineDash([2, 6])
				context.beginPath();
		        context.moveTo(30, line_space[i]);
		        context.lineTo(width_x, line_space[i]);
		        context.strokeStyle = 'rgb(93, 93, 93)';
		        context.lineWidth = 1;
		        context.stroke();
			}

			context.restore();
	        
			//Print grid line numbers
			for(i=0;i<=num_lines;i++){

				//Moves the coordinate axis so that each number can be refleted properly
				context.translate(0, line_space[i]);

				context.fillStyle = "grey";
				context.scale(1, -1);
				context.font = "10px Coves";
				context.textAlign = "left";
				context.fillText(line_numbers[i].toPrecision(3), 0, 0);

				//Revert to previous coordinate axis
				context.scale(1, -1);
				context.translate(0, -line_space[i]);
			}
			
			//Print line connecting points
	        
			for(i=0;i<num_points;i++){
				if(i==0){
					context.beginPath();
					context.lineJoin="round";
					context.moveTo(point_coords[i][0],point_coords[i][1]);
				}
				else{
					context.lineTo(point_coords[i][0],point_coords[i][1]);
				}
			}
	        context.strokeStyle = 'rgb(203, 110, 141)';
	        context.lineWidth = 5;
			context.stroke();
			context.restore();
			
			
			//Print points
	        
			for(i=0;i<num_points;i++){
				context.beginPath();
				context.arc(point_coords[i][0],point_coords[i][1],5,0,2*Math.PI);
		        context.strokeStyle = 'rgb(93, 93, 93)';
		        context.lineWidth = 5;
		        context.fillStyle = 'rgb(203, 110, 141)';
		        context.fill();
				context.stroke();
				
				//Moves the coordinate axis
				context.translate(point_coords[i][0] - 5, point_coords[i][1] + 20);

				context.fillStyle = "rgb(0, 0, 0)";
				context.scale(1, -1);
				context.font = "12px Coves";
				context.textAlign = "left";
				context.fillText(point_values[i], 0, 0);

				//Revert to previous coordinate axis
				context.scale(1, -1);
				context.translate(-point_coords[i][0] + 5, -point_coords[i][1] - 20);
			}
			
			//Print second set of values
			if(data2.length > 0){
				
				var point_coords2 =  new Array();
				var point_values2 = new Array();
				for(i=0;i<num_points;i++){
					temp_x = 50 + point_x_spacing*i;
					
					temp = (data2[num_points - i - 1] - min_arr)/fact;
					temp_y = temp*height_y;
				
					point_coords2[i] = new Array(temp_x,temp_y);
					point_values2[i] = data2[num_points - i - 1];
				}
				
				//Print line connecting points
		        
				for(i=0;i<num_points;i++){
					if(i==0){
						context.beginPath();
						context.lineJoin="round";
						context.moveTo(point_coords2[i][0],point_coords2[i][1]);
					}
					else{
						context.lineTo(point_coords2[i][0],point_coords2[i][1]);
					}
				}
		        context.strokeStyle = 'rgb(155,205,230)';
		        context.lineWidth = 5;
				context.stroke();
				context.restore();
				
				
				//Print points
		        
				for(i=0;i<num_points;i++){
					context.beginPath();
					context.arc(point_coords2[i][0],point_coords2[i][1],5,0,2*Math.PI);
			        context.strokeStyle = 'rgb(93, 93, 93)';
			        context.lineWidth = 5;
			        context.fillStyle = 'rgb(203, 110, 141)';
			        context.fill();
					context.stroke();
					
					//Moves the coordinate axis
					context.translate(point_coords2[i][0] - 5, point_coords2[i][1] + 20);

					context.fillStyle = "rgb(0, 0, 0)";
					context.scale(1, -1);
					context.font = "12px Coves";
					context.textAlign = "left";
					context.fillText(point_values2[i], 0, 0);

					//Revert to previous coordinate axis
					context.scale(1, -1);
					context.translate(-point_coords2[i][0] + 5, -point_coords2[i][1] - 20);
				}
			}
			
			//Print title
			
			var title_height = height_y + 35;
			
			context.translate(canvas.width/2 - 30, title_height); //The 30 is for the translation of the grid of 30 points to the right for the begining of the bottom line

			context.fillStyle = "rgb(93, 93, 93)";
			context.scale(1, -1);
			context.font = "20px Coves";
			context.textAlign = "left";
			context.fillText(title, 0, 0);

			context.scale(1, -1);
			context.translate(-canvas.width/2 + 30, -title_height);
			
			//Print conventions if required
			
			if(data2.length > 0){
				if(title = "bp"){
					var conv1 = "- sys";
					var conv2 = "- dias";
				}
				//draw line 1
				context.beginPath();
				context.lineJoin="round";
				context.moveTo(canvas.width/2 - 30 + 60, title_height + 10);
				context.lineTo(canvas.width/2 - 30 + 90, title_height + 10);
		        context.strokeStyle = 'rgb(203, 110, 141)';
		        context.lineWidth = 5;
				context.stroke();
				
				//draw line 2
				context.beginPath();
				context.lineJoin="round";
				context.moveTo(canvas.width/2 - 30 + 60, title_height);
				context.lineTo(canvas.width/2 - 30 + 90, title_height);
		        context.strokeStyle = 'rgb(155,205,230)';
		        context.lineWidth = 5;
				context.stroke();
				
				//Print convention 1
				context.translate(canvas.width/2 - 30 + 100, title_height + 10 - 3); //The 30 is for the translation of the grid of 30 points to the right for the begining of the bottom line

				context.fillStyle = "grey";
				context.scale(1, -1);
				context.font = "10px Coves";
				context.textAlign = "left";
				context.fillText(conv1, 0, 0);

				context.scale(1, -1);
				context.translate(-canvas.width/2 + 30 - 100, -title_height - 10 + 3);
				
				//Print convention 2
				context.translate(canvas.width/2 - 30 + 100, title_height - 3); //The 30 is for the translation of the grid of 30 points to the right for the begining of the bottom line

				context.fillStyle = "grey";
				context.scale(1, -1);
				context.font = "10px Coves";
				context.textAlign = "left";
				context.fillText(conv2, 0, 0);

				context.scale(1, -1);
				context.translate(-canvas.width/2 + 30 - 100, -title_height + 3);
			}
			
			//Print bottom-left instructions
			
      		context.translate(30, -45);
      		
			context.fillStyle = "rgb(93, 93, 93)";
			context.scale(1, -1);
			context.font = "14px Coves";
			context.textAlign = "left";
			
			if(lang == "en"){
				context.fillText("Move mouse over the points to see their dates.", 0, 0);
			}
			else{
				context.fillText("Mueve el cursor sobre los puntos para ver las fechas.", 0, 0);
			}
			
			context.scale(1, -1);
			context.translate(-30, 45);

	   		function writeValues(canvas, mouse_x, mouse_y, point_coords, point_coords2, point_values,date_time,point_x_spacing,message_spacing) {
				var context1 = canvas.getContext('2d');
				var contact_radious = point_x_spacing/2;
				var RR = Math.pow(contact_radious,2);
				for(i=0;i<num_points;i++){
					temp_rr = Math.pow(point_coords[i][0]-mouse_x,2) + Math.pow(point_coords[i][1]-mouse_y,2);
					if(point_coords2 !== 0){
						temp_rr2 = Math.pow(point_coords2[i][0]-mouse_x,2) + Math.pow(point_coords2[i][1]-mouse_y,2);
					}
					else{
						temp_rr2 = RR+1;
					}
					if(temp_rr < RR || temp_rr2 < RR){
						//printPointValue(point_coords[i], point_values[i]); Temporary deprecated
						message_movement_quantity = message_spacing*i;
						printDates(date_time[i],message_movement_quantity);
						
						//Print lines connecting the points to the dates
						temp_x = 50 + point_x_spacing*i;
						context.beginPath();
						context.lineJoin="round";
						context.moveTo(temp_x,-30);
						context.lineTo(temp_x,-8);
				        context.strokeStyle = 'rgb(93, 93, 93)';
				        context.lineWidth = 2;
						context.stroke();
						context.restore();
					}
				}
			}
	      	function getMousePossition(canvas, evt) {
		        var canvRectangle = canvas.getBoundingClientRect();
		        return {
		        		x: evt.clientX - canvRectangle.left,
		        		y: -(evt.clientY - canvRectangle.bottom) - bottom_space
		        };
		      }
	      	
	      	function printPointValue(point_coords,point_value)/*currently not used, but working*/{
				//Moves the coordinate axis
				context.translate(point_coords[0], point_coords[1] + 20);

				context.fillStyle = "black";
				context.scale(1, -1);
				context.font = "12px Coves";
				context.textAlign = "left";
				context.fillText(point_value, 0, 0);

				//Revert to previous coordinate axis
				context.scale(1, -1);
				context.translate(-point_coords[0], -point_coords[1] - 20);
	      	}
	      	
	      	function printDates(date_time,message_movement_quantity){
	      		var MILLISECS_PER_HOUR = 60 /* min/hour */ * 60 /* sec/min */ * 1000 /* ms/s */;
	      		date_time = date_time.replace(" ", "T");
	      		formatedDate = new Date(Date.parse(date_time + "Z") + 5*MILLISECS_PER_HOUR);
	      		optionsDate = { year: "2-digit", month: "short",  
	      			    day: "2-digit" };
	      		optionsTime = {hour: "2-digit",  
	      				minute: "2-digit", year: "2-digit", month: "short",  
	      			    day: "2-digit", hour12: "true", timeZone : 'America/Bogota'}; 
	      		
	      		if(lang == "en"){
	      			displayedText =  "From: " + /*formatedDate.toLocaleDateString("en-GB", optionsDate) + */ formatedDate.toLocaleDateString("en-GB", optionsTime);
				}
				else{
					displayedText =  "Del: " + /*formatedDate.toLocaleDateString("en-GB", optionsDate) + */ formatedDate.toLocaleDateString("es-ES", optionsTime);
				}
	      		
	      		//The rectangle that clears previous inputs
	      		context.clearRect(30, -50, canvas.width, 42);
	      		
	      		//Moves the coordinate axis
	      		context.translate(30 + message_movement_quantity, -43);
	      		
				context.fillStyle = "rgb(93, 93, 93)";
				context.scale(1, -1);
				context.font = "16px Coves";
				context.textAlign = "left";
				context.fillText(displayedText, 0, 0);

				//Revert to previous coordinate axis
				context.scale(1, -1);
				context.translate(-30 - message_movement_quantity, 43);
	      	}
		
		    canvas.addEventListener('mousemove', function(evt) {
		    		var point_x_spacing_t = point_x_spacing;
		    		var message_spacing_t = message_spacing;
		    		var points = point_coords;
		    		if(data2.length > 0){
		    			var points2 = point_coords2;
		    		}
		    		else{
		    			var points2 = 0;
		    		}
		    		var point_values_t = point_values;
		    		var date_time_t = date_time;
		    		var mousePos = getMousePossition(canvas, evt);
		     	writeValues(canvas, mousePos.x, mousePos.y, points, points2, point_values_t,date_time_t,point_x_spacing,message_spacing);
		      	
		    }, false);
		},
		error: function(jqXHR, exception) {
			var lang = "<?php echo $_SESSION['lang'];?>";
			if (jqXHR.status === 404) {
				if(lang == "en"){
					$('#canvas_graph_div').html('<p> A graph will be displayed as soon as you have 2 or more measurements.</p>');
				}
				else{
					$('#canvas_graph_div').html('<p> La gráfica se mostrará una vez se tenga 2 o más datos.</p>');
				}
			}
		}
	});
}