<?php
include("../../config/config.php");
include("../classes/User.php");

if(isset($_SESSION['username']) && isset($_SESSION['messages_token'])){
	$temp_user = $_SESSION['username'];
	$temp_user_e = $_SESSION['username_e'];
	//$temp_passwrd = $_SESSION['passwrd'];
	$temp_messages_token= $_SESSION['messages_token'];
	
	$stmt = $con->prepare("SELECT * FROM users WHERE username=? AND messages_token=?");
	
	$stmt->bind_param("ss", $temp_user_e, $temp_messages_token);
	$stmt->execute();
	$verification_query = $stmt->get_result();
	
	if(mysqli_num_rows($verification_query) == 1){
		$userLoggedIn = $temp_user;
		$userLoggedIn_e = $temp_user_e;
	}
	else{
		$userLoggedIn = "";
		session_start();
		session_destroy();
		header("Location: ../../register.php");
		$stmt->close();
	}
	
	$user_obj = new User($con, $userLoggedIn, $userLoggedIn_e);
	$lang = $_SESSION['lang'];
	
}
else{
	$userLoggedIn = "";
	session_start();
	session_destroy();
	header("Location: ../../register.php");
	$stmt->close();
}


$stat= $_REQUEST['stat'];
$key= $_REQUEST['key'];


switch ($lang){
	
	case("en"):
		switch ($stat) {
			case 'height':
				$table = $user_obj->getHeight();
				$column = $stat;
				$title = ucwords($column);
				$units = "m";
				$width_inp = 31;
				break;
			case 'weight':
				$table = $user_obj->getWeight();
				$column = $stat;
				$title = ucwords($column);
				$units = "kg";
				$width_inp = 31;
				break;
			case 'bmi':
				$table = $user_obj->getBMI();
				$column = $stat;
				$title = "BMI";
				$units = "";
				$width_inp = 31;
				break;
			case 'bp':
				$table = $user_obj->getBloodPressure();
				$column = "BPSys";
				$addi_column = "BPDia";
				$title = "Systolic";
				$title2= "Diastolic";
				$width_inp = 23;
				break;
			default:
				$stat = "";
				$table = "";
				$column = "";
				$addi_column = "";
				$title ="";
				$title2= "";
				$width_inp ="";
		}
		break;
		
	case("es"):
		switch ($stat) {
			case 'height':
				$table = $user_obj->getHeight();
				$column = $stat;
				$title = "Altura";
				$units = "m";
				$width_inp = 31;
				break;
			case 'weight':
				$table = $user_obj->getWeight();
				$column = $stat;
				$title = "Peso";
				$units = "kg";
				$width_inp = 31;
				break;
			case 'bmi':
				$table = $user_obj->getBMI();
				$column = $stat;
				$title = "IMC";
				$units = "";
				$width_inp = 31;
				break;
			case 'bp':
				$table = $user_obj->getBloodPressure();
				$column = "BPSys";
				$addi_column = "BPDia";
				$title = "Sistólica";
				$title2= "Diastólica";
				$width_inp = 23;
				break;
			default:
				$stat = "";
				$table = "";
				$column = "";
				$addi_column = "";
				$title ="";
				$title2= "";
				$width_inp ="";
		}
		break;
}



if($stat== 'bp'){
	$stmt = $con->prepare("SELECT $column,$addi_column,date_time,PHID FROM $table ORDER BY date_time DESC, PHID DESC");
}
else{
	if ($column != ""){
		$stmt = $con->prepare("SELECT $column,date_time,PHID FROM $table ORDER BY date_time DESC, PHID DESC");
	}
}

$stmt->execute();
$query = $stmt->get_result();

if(mysqli_num_rows($query)){

	$disp_object = array();
	$low_key = -10;
	$high_key = -10;
	
	foreach ($query as $arr_key => $arr){
		if($key + 1 == $arr_key){
			$low_key = $arr_key;
		}
		elseif($key - 1 == $arr_key){
			$high_key = $arr_key;
		}
		elseif($key == $arr_key){
			$disp_object['key'] = $arr_key;
			$disp_object['column'] = $arr[$column];
			if($stat == 'bp'){
				$disp_object['column2'] = $arr[$addi_column];
			}
			$disp_object['date_time'] = new DateTime($arr['date_time']);
			$disp_object['id'] = $arr['PHID'];
		}
	}
	
	switch ($lang){
		
		case("en"):
			$str = "<h4>Review previous data: </h4> ";
			break;
			
		case("es"):
			$str = "<h4>Datos anteriores: </h4> ";
			break;
	}
	
	
	$str .= <<<EOS
				<div class='move_container'> &nbsp
					
EOS;
	
	if($low_key > -10){
		$str .= <<<EOS
					
					<a href='javascript:void(0);' onclick='move_data_h_stats("$low_key","$stat")'>
						<div class='move_data_health_stats' id='pos_left'> &#10092; </div>
					</a>
					
EOS;
	}
		$str .= <<<EOS
				</div>
					
EOS;
	
	if($stat == 'bp'){
		switch ($lang){
			
			case("en"):
				$str .= "<div class='data_stats_container' id='data_stats_scroll'>
					<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " (mmHg)</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title2 . " (mmHg)</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Date </p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Time </p>
					<input type='text' name='displayed_data_value' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['column']. "' required>
					<input type='text' name='displayed_data_value2' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['column2']. "' required>
					<input type='text' name='displayed_data_date' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('d/m/y') . "' required>
					<input type='text' name='displayed_data_time' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('h:ia') . "' required>
				</div>";
				break;
				
			case("es"):
				$str .= "<div class='data_stats_container' id='data_stats_scroll'>
					<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " (mmHg)</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title2 . " (mmHg)</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Fecha </p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Hora </p>
					<input type='text' name='displayed_data_value' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['column']. "' required>
					<input type='text' name='displayed_data_value2' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['column2']. "' required>
					<input type='text' name='displayed_data_date' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('d/m/y') . "' required>
					<input type='text' name='displayed_data_time' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('h:ia') . "' required>
				</div>";
				break;
		}

	}
	else	if($stat == 'bmi'){
		//BMI scale
		
		switch ($lang){
			
			case("en"):
				if($units == ""){
					if($disp_object['column']< 18.5 && $disp_object['column']!== NULL)
						$units ="<span style='color:rgb(243, 216, 9);'> Underweight </span>";
						elseif ($disp_object['column']>= 18.5 && $disp_object['column']< 24.9)
						$units ="<span style='color:rgb(105, 249, 131);'> Normal </span>";
						elseif ($disp_object['column']>= 25 && $disp_object['column']< 29.9)
						$units ="<span style='color:rgb(247, 144, 69);'> Overweight </span>";
						elseif ($disp_object['column']>= 30)
						$units ="<span style='color:red;'> Obese </span>";
						else
							$units ="";
				}
				break;
				
			case("es"):
				if($units == ""){
					if($disp_object['column']< 18.5 && $disp_object['column']!== NULL)
						$units ="<span style='color:rgb(243, 216, 9);'> Infrapeso </span>";
						elseif ($disp_object['column']>= 18.5 && $disp_object['column']< 24.9)
						$units ="<span style='color:rgb(105, 249, 131);'> Normal </span>";
						elseif ($disp_object['column']>= 25 && $disp_object['column']< 29.9)
						$units ="<span style='color:rgb(247, 144, 69);'> Sobrepeso </span>";
						elseif ($disp_object['column']>= 30)
						$units ="<span style='color:red;'> Obesidad </span>";
						else
							$units ="";
				}
				break;
		}

		switch ($lang){
			
			case("en"):
				$str .= "<div class='data_stats_container' id='data_stats_scroll'>
					<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " - <span class='bmi_h_stats'>" . $units . "</span></p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Date</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Time</p>
					<input type='text' name='displayed_data_value' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['column']. "' readonly>
					<input type='text' name='displayed_data_date' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('d/m/y') . "'required>
					<input type='text' name='displayed_data_time' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('h:ia') . "' required>
				</div>";
				break;
				
			case("es"):
				$str .= "<div class='data_stats_container' id='data_stats_scroll'>
					<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " - <span class='bmi_h_stats'>" . $units . "</span></p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Fecha</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Hora</p>
					<input type='text' name='displayed_data_value' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['column']. "' readonly>
					<input type='text' name='displayed_data_date' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('d/m/y') . "'required>
					<input type='text' name='displayed_data_time' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('h:ia') . "' required>
				</div>";
				break;
		}

	}
	else	{
		switch ($lang){
			
			case("en"):
				$str .= "<div class='data_stats_container' id='data_stats_scroll'>
					<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " (" . $units . ")</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Date</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Time</p>
					<input type='text' name='displayed_data_value' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['column']. "' required>
					<input type='text' name='displayed_data_date' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('d/m/y') . "'required>
					<input type='text' name='displayed_data_time' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('h:ia') . "' required>
				</div>";
				break;
				
			case("es"):
				$str .= "<div class='data_stats_container' id='data_stats_scroll'>
					<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " (" . $units . ")</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Fecha</p>
					<p style='width: " . $width_inp . "%; display:inline-block;'>Hora</p>
					<input type='text' name='displayed_data_value' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['column']. "' required>
					<input type='text' name='displayed_data_date' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('d/m/y') . "'required>
					<input type='text' name='displayed_data_time' class='input_health_stat' style='width: " . $width_inp . "%;' value='" . $disp_object['date_time']->format('h:ia') . "' required>
				</div>";
				break;
		}

	}

				$str .= <<<EOS
				<div class='move_container'> &nbsp
					
EOS;
	if($high_key > -10){
		$str .= <<<EOS
			
					<a href='javascript:void(0);' onclick='move_data_h_stats("$high_key","$stat")'>
						<div class='move_data_health_stats' id='pos_right'> &#10093; </div>
					</a>
			
EOS;
	}				
				$str .= <<<EOS
				</div>
					
EOS;
	

	$id = $disp_object['id'];
	
	switch ($lang){
		
		case("en"):
			$str .= <<<EOS
				<div class='save_delete_div'>
					<a href='javascript:void(0);' onclick='save_data_stats("$stat","$id")'>
						<div class='save_data_stats_butt review_data_stats_butt' > Save </div>
					</a>
					
					<a href='javascript:void(0);' onclick='del_data_stats("$stat","$id")'>
						<div class='del_data_stats_butt' > Delete </div>
					</a>
				</div>
EOS;
			break;
			
		case("es"):
			$str .= <<<EOS
				<div class='save_delete_div'>
					<a href='javascript:void(0);' onclick='save_data_stats("$stat","$id")'>
						<div class='save_data_stats_butt review_data_stats_butt' > Guardar </div>
					</a>
					
					<a href='javascript:void(0);' onclick='del_data_stats("$stat","$id")'>
						<div class='del_data_stats_butt' > Eliminar </div>
					</a>
				</div>
EOS;
			break;
	}
	

	
	echo $str;
}
?>
