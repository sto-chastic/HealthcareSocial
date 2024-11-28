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

switch ($lang){
	
	case("en"):
		switch ($stat) {
			case 'height':
				$table = $user_obj->getHeight();
				$column = $stat;
				$title = ucwords($column);
				$units = "m";
				$width_inp = 32.5;
				$ex = '1.73';
				break;
			case 'weight':
				$table = $user_obj->getWeight();
				$column = $stat;
				$title = ucwords($column);
				$units = "kg";
				$width_inp = 32.5;
				$ex = '78';
				break;
			case 'bmi':
				$table = $user_obj->getBMI();
				$column = $stat;
				$title = ucwords($column);
				$width_inp = 32.5;
				$units = "";
				$ex = '';
				break;
			case 'bp':
				$table = $user_obj->getBloodPressure();
				$column = "BPSys";
				$addi_column = "BPDia";
				$width_inp = 24.2;
				$units = "mmHg";
				$ex = '120';
				$ex2 = '80';
				$title = "Systolic";
				$title2 = "Diastolic";
				break;
			default:
				$stat = "";
		}
		break;
		
	case("es"):
		switch ($stat) {
			case 'height':
				$table = $user_obj->getHeight();
				$column = $stat;
				$title = "Altura";
				$units = "m";
				$width_inp = 32.5;
				$ex = '1.73';
				break;
			case 'weight':
				$table = $user_obj->getWeight();
				$column = $stat;
				$title = "Peso";
				$units = "kg";
				$width_inp = 32.5;
				$ex = '78';
				break;
			case 'bmi':
				$table = $user_obj->getBMI();
				$column = $stat;
				$title = "IMC";
				$width_inp = 32.5;
				$units = "";
				$ex = '';
				break;
			case 'bp':
				$table = $user_obj->getBloodPressure();
				$column = "BPSys";
				$addi_column = "BPDia";
				$width_inp = 24.2;
				$units = "mmHg";
				$ex = '120';
				$ex2 = '80';
				$title = "Sistólica";
				$title2 = "Diastólica";
				break;
			default:
				$stat = "";
		}
		break;
}


	
if($column == "BPSys"){
	
	switch ($lang){
		
		case("en"):
			$str = "
		<h4>Insert new data: </h4>
		<div class='data_stats_container'>
			<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " (" . $units . ")</p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title2 . " (" . $units . ")</p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>Date </p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>Time </p>
			<input type='hidden' name='today_check' value='0'>
			<input type='text' class='health_stat_data_inp' name='new_data_value' style='width: " . $width_inp . "%;' placeholder='" . $ex ."'>
			<input type='text' class='health_stat_data_inp' name='new_data_value2' style='width: " . $width_inp . "%;' placeholder='" . $ex2 ."'>
			<input type='text' class='health_stat_data_inp'name='new_data_date' style='width: " . $width_inp . "%;' placeholder='Ex: 20/8/17'>
			<input type='text' class='health_stat_data_inp' name='new_data_time' style='width: " . $width_inp . "%;' placeholder='Ex: 2:31pm'>
		</div>";
			break;
			
		case("es"):
			$str = "
		<h4>Insertar datos: </h4>
		<div class='data_stats_container'>
			<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " (" . $units . ")</p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title2 . " (" . $units . ")</p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>Fecha </p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>Hora </p>
			<input type='hidden' name='today_check' value='0'>
			<input type='text' class='health_stat_data_inp' name='new_data_value' style='width: " . $width_inp . "%;' placeholder='" . $ex ."'>
			<input type='text' class='health_stat_data_inp' name='new_data_value2' style='width: " . $width_inp . "%;' placeholder='" . $ex2 ."'>
			<input type='text' class='health_stat_data_inp'name='new_data_date' style='width: " . $width_inp . "%;' placeholder='Ej: 20/8/17'>
			<input type='text' class='health_stat_data_inp' name='new_data_time' style='width: " . $width_inp . "%;' placeholder='Ej: 2:31pm'>
		</div>";
			break;
	}
	

}
elseif($column == "bmi"){
	switch ($lang){
		
		case("en"):
			$str = "
				<h4>Insert new data: </h4>
				<p>To obtain your <b>BMI (Body Mass Index)</b>,<br> UPDATE YOUR HEIGHT AND WEIGHT <br>and press the button 'Generate'. Body Mass Index (BMI) is a measurement based on your latest weight and height information. Your BMI is classified in 4 groups: 'Underweight', 'Normal', 'Overweight', and 'Obese'.</p>
				<div class='data_stats_container'>";
			$str .= <<<EOS
			<a href='javascript:void(0);' onclick='generate_bmi_data_stats()'>
				<div class='save_data_stats_butt generate_bmi_butt' id='butt_generate'>   Generate  </div>
			</a>
EOS;
			break;
			
		case("es"):
			$str = "
				<h4>Insertar datos: </h4>
				<p>Para obtener tu <b>IMC (Índice de Masa Corporal)</b>,<br> ACTUALIZA TU PESO Y ALTURA <br>y haz click en 'Generar'. El Índice de Masa Corporal (IMC) es una medida basada en tu último peso y altura. Tu IMC está clasificado en 4 grupos: 'Infrapeso', 'Normal', 'Sobrepeso', y 'Obesidad'.<p>
				<div class='data_stats_container'>";
			$str .= <<<EOS
			<a href='javascript:void(0);' onclick='generate_bmi_data_stats()'>
				<div class='save_data_stats_butt generate_bmi_butt' id='butt_generate'>   Generar  </div>
			</a>
EOS;
			break;
	}

	

	$str .= "</div>";
}
else{
	switch ($lang){
		
		case("en"):
			$str = "
		<h4>Insert new data: </h4>
		<div class='data_stats_container'>
			<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " (" . $units . ")</p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>Date </p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>Time</p>
			<input type='hidden' name='today_check' value='0'>
			<input type='text' class='health_stat_data_inp' name='new_data_value' style='width: " . $width_inp . "%;' placeholder='" . $ex ."'>";
			$str .= <<<EOS
			<input type='text' class='health_stat_data_inp' name='new_data_date' style='width: $width_inp%;' placeholder='Ex: 20/8/17' onkeyup="assistDateInput(this.value,'new_data_date')">
			<input type='text' class='health_stat_data_inp' name='new_data_time' style='width: $width_inp%;' placeholder='Ex: 2:31pm' onkeyup="assistTimeInput(this.value,'new_data_time')">
		</div>
EOS;
			break;
			
		case("es"):
			$str = "
		<h4>Insertar datos: </h4>
		<div class='data_stats_container'>
			<p style='width: " . $width_inp . "%; display:inline-block;'>" . $title . " (" . $units . ")</p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>Fecha </p>
			<p style='width: " . $width_inp . "%; display:inline-block;'>Hora</p>
			<input type='hidden' name='today_check' value='0'>
			<input type='text' class='health_stat_data_inp' name='new_data_value' style='width: " . $width_inp . "%;' placeholder='" . $ex ."'>";
			$str .= <<<EOS
			<input type='text' class='health_stat_data_inp' name='new_data_date' style='width: $width_inp%;' placeholder='Ej: 20/8/17' onkeyup="assistDateInput(this.value,'new_data_date')">
			<input type='text' class='health_stat_data_inp' name='new_data_time' style='width: $width_inp%;' placeholder='Ej: 2:31pm' onkeyup="assistTimeInput(this.value,'new_data_time')">
		</div>
EOS;
			break;
	}

}

if($column != "bmi"){
	switch ($lang){
		
		case("en"):
			$str .= <<<EOS
				<a href='javascript:void(0);' onclick='save_data_stats("$stat")'>
					<div class='save_data_stats_butt'> Save </div>
				</a>
EOS;
			break;
			
		case("es"):
			$str .= <<<EOS
				<a href='javascript:void(0);' onclick='save_data_stats("$stat")'>
					<div class='save_data_stats_butt'> Guardar </div>
				</a>
EOS;
			break;
	}

}

$date_time = new DateTime();
$date_php = $date_time->format("d/m/y");
$time_php = $date_time->format("h:ia");

$str .= <<<EOS
			<script>
                	$(document).ready(function(){
    
    			     //Clear date inputs
    			
        			     $('.health_stat_data_inp').on('click focusin', function() {
                        this.value = '';
                     });
                 });
				$("input[name^=new_data_value]").focusin(function() {
					if($("input[name=today_check]").val() == 0){
						$("input[name=today_check]").val('1');
						var date_val = '$date_php';
						var day_val = '$time_php';
						$("input[name=new_data_date]").attr("placeholder", "");
						$("input[name=new_data_time]").attr("placeholder", "");

						$("input[name=new_data_date]").val(date_val);
						$("input[name=new_data_time]").val(day_val);
					}
				});

				$('input[name^="new_data_"]').keydown(function (e) {
					var str_length = $(this).length;
				    var position = $(this).getCursorPosition();
				    var deleted = '';
				    var val = $(this).val();
				    if (e.which == 8) {
				        if (position[0] == position[1]) {
				            if (position[0] == 0)
				                deleted = '';
				            else
				                deleted = val.substr(position[0] - 1, 1);
				        }
				        else {
				            deleted = val.substring(position[0], position[1]);
				        }
				    }
				    else if (e.which == 46) {
				        var val = $(this).val();
				        if (position[0] == position[1]) {
				            
				            if (position[0] === val.length)
				                deleted = '';
				            else
				                deleted = val.substr(position[0], 1);
				        }
				        else {
				            deleted = val.substring(position[0], position[1]);
				        }
				    }
				    if(deleted == "/"){
				    		$("input[name=new_data_date]").val(val.slice(0,position[0]-1));
					}
					else if(deleted == ":"){
				    		$("input[name=new_data_time]").val(val.slice(0,position[0]-1));
					}
					else if(deleted == "m"){
				    		$("input[name=new_data_time]").val(val.slice(0,position[0]-1));
					}
				});
			</script>
EOS;
	
echo $str;

?>
