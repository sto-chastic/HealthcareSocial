<?php
include("includes/header2.php");
require 'includes/form_handlers/basic_info_form.php';

//Previous info load

$stmt = $con->prepare("SELECT * FROM basic_info_patients WHERE username=?");
$stmt->bind_param("s",$userLoggedIn);
$stmt->execute();
$basic_info = mysqli_fetch_array($stmt->get_result());

//Language Tables:
$months_row_lang = 'months_eng';
$days_week_row_lang = 'days_short_eng';

if(isset($_SESSION['lang'])){
    $lang = $_SESSION['lang'];
} else $lang = "es";


if(isset($_POST['patho_butt'])){
    $link = '#personalInfoTabs a[href="#pathologies"]';
    echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
}

if(isset($_POST['surgeries_butt'])){
    $link = '#personalInfoTabs a[href="#surgical_trauma"]';
    echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
}

if(isset($_POST['hereditary_butt'])){
    $link = '#personalInfoTabs a[href="#hereditary"]';
    echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
}
if(isset($_POST['medicines_butt'])){
    $link = '#personalInfoTabs a[href="#pharmacology"]';
    echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
}
if(isset($_POST['allergies_butt'])){
    $link = '#personalInfoTabs a[href="#allergies"]';
    echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
}
if(isset($_POST['save_women_info'])){
    $link = '#personalInfoTabs a[href="#OBGYN"]';
    echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
}
if(isset($_POST['save_habits_info'])){
    $link = '#personalInfoTabs a[href="#habits"]';
    echo "<script>
				$(function(){
					$('" . $link . "').tab('show');
				});
			</script>";
}
?>
<script>
    $(document).ready(function(){
    	$('.nav-tabs > li'). css('width', 'calc(100% / 3)');
    	for ( var i=4; i<= $('.nav-tabs > li').length ; i++){
        	$('.nav-tabs > li:nth-child('+i+')'). css('width', 'calc(100% / 4)');
    	}
    });
</script>

<!-- CSS -->
<link rel="stylesheet" type="text/css" href="assets/css/appointment_details.css">
<link rel="stylesheet" type="text/css" href="assets/css/style.css">


<style type="text/css">
	.wrapper{
		
		margin-left: 0px;
		padding-left:0px;
	}
	.dashboard_tag_block {
        width: 50%;
    }
</style>
<div class= "top_banner_title">
    
    <div class="top_banner_title_text_container">
    	<h1><?php 
    	switch ($lang){
    		
    		case("en"):
    			echo "Medical Information";
    			break;
    			
    		case("es"):
    			echo "Información Médica";
    			break;
    	}
    	?>
    	</h1>
    	<h2>
    	<?php 
    	switch ($lang){
    		
    		case("en"):
    			echo "Register relevant information for your caregivers";
    			break;
    			
    		case("es"):
    			echo "Registra informacion importante para tus médicos";
    			break;
    	}
    	?>
    	</h2>
    </div>
</div>

<div class="wrapper">

	<div class="main_column column" id="main_column">

		
		<?php 
		switch($lang){
		    case("en"):
		        echo '
            <ul class="nav nav-tabs" role="tablist" id="personalInfoTabs">
        			<li role="presentation" class="active"><div class="arrow-down"></div><a href="#basic_info" aria-controls="basic_info" role="tab" data-toggle="tab"><span id="basic_tab"></span>Basic Information</a></li>
					<li role="presentation"><div class="arrow-down"></div><a href="#pathologies" aria-controls="pathologies" role="tab" data-toggle="tab"><span id="illness_tab"></span>Illnesses / Hospitalizations</a></li>
					<li role="presentation"><div class="arrow-down"></div><a href="#surgical_trauma" aria-controls="surgical_trauma" role="tab" data-toggle="tab"><span id="surgeries_tab"></span>Surgeries / Traumas</a></li>
					<li role="presentation"><div class="arrow-down"></div><a  href="#hereditary" aria-controls="hereditary" role="tab" data-toggle="tab"><span id="hereditary_tab"></span>Hereditaries</a></li>
					<li role="presentation"><div class="arrow-down"></div><a href="#pharmacology" aria-controls="pharmacology" role="tab" data-toggle="tab"><span id="medicine_tab"></span>Medicines</a></li>
					<li role="presentation"><div class="arrow-down"></div><a  href="#allergies" aria-controls="allergies" role="tab" data-toggle="tab"><span id="allergies_tab"></span>Allergies</a></li>
					<li role="presentation"><div class="arrow-down"></div><a  href="#habits" aria-controls="habits" role="tab" data-toggle="tab"><span id="habits_tab"></span>Habits</a></li>
                        ';
		        $stmt = $con->prepare("SELECT sex FROM basic_info_patients WHERE username = ? AND sex = ?");
		        $stmt->bind_param("ss",$userLoggedIn,$female);
		        $female = "f";
		        $stmt->execute();
		        $sex_query = $stmt->get_result();
		        if(mysqli_num_rows($sex_query) == 1){
		            echo '<div id="sex_selected"><a href="#OBGYN" aria-controls="OBGYN" role="tab" data-toggle="tab">OBGYN</a></div>';
		        }
		      
		        break;
		    case("es"):
		        echo '
            <ul class="nav nav-tabs" role="tablist" id="personalInfoTabs">
                    <li role="presentation" class="active"><div class="arrow-down"></div><a href="#basic_info" aria-controls="basic_info" role="tab" data-toggle="tab"><span id="basic_tab"></span>Información Básica</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a href="#pathologies" aria-controls="pathologies" role="tab" data-toggle="tab"><span id="illness_tab"></span>Enfermedades / Hospitalizaciones</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a href="#surgical_trauma" aria-controls="surgical_trauma" role="tab" data-toggle="tab"><span id="surgeries_tab"></span>Cirugías / Trauma</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a  href="#hereditary" aria-controls="hereditary" role="tab" data-toggle="tab"><span id="hereditary_tab"></span>Hereditario</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a href="#pharmacology" aria-controls="pharmacology" role="tab" data-toggle="tab"><span id="medicine_tab"></span>Medicamentos</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a  href="#allergies" aria-controls="allergies" role="tab" data-toggle="tab"><span id="allergies_tab"></span>Alergias</a></li>
                    <li role="presentation"><div class="arrow-down"></div><a  href="#habits" aria-controls="habits" role="tab" data-toggle="tab"><span id="habits_tab"></span>Hábitos</a></li>
                        ';
		        $stmt = $con->prepare("SELECT sex FROM basic_info_patients WHERE username = ? AND sex = ?");
		        $stmt->bind_param("ss",$userLoggedIn,$female);
		        $female = "f";
		        $stmt->execute();
		        $sex_query = $stmt->get_result();
		        if(mysqli_num_rows($sex_query) == 1){
		            echo '<div id="sex_selected"><a href="#OBGYN" aria-controls="OBGYN" role="tab" data-toggle="tab">Ginecológico/Obstétrico</a></div>';
		        }
		        break;
		}

			?>
		</ul>
			
			<div class="tab-health_info">
				<div class="tab-content" style=" width: 100%;">
				
					<div role="tabpanel" class="tab-pane fade in active" id="basic_info">
						<h3>
						<?php 
				
							switch ($lang){
								
								case("en"):
									echo "Add your personal informaion";
									break;
									
								case("es"):
									echo "Agrega tu información personal";
									break;
							}
							
						?></h3>
					 	<form action="health_info_input.php" method="POST">
						 	<div class="form_area">
						 		<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "sex";
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Sex *";
									        break;
									    case("es"):
									        echo "Sexo *";
									        break;
									}
									?>
									</p>
									
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> required>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] === $arr['id']){
    														echo "<option value='" . $arr['id']. "' selected='selected' >" . $arr[$lang] . "</option>";
    													}
    													else{
    														echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "blood_type"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Blood Type/Rh *";
									        break;
									    case("es"):
									        echo "Tipo de Sangre/Rh *";
									        break;
									}
									?>
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> required>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] == $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "birthdate"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Birthdate (YYYY-MM-DD) *";
									        break;
									    case("es"):
									        echo "Fecha de Nacimiento (AAAA-MM-DD) *";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: 1978-05-30";
    									        break;
    									    case("es"):
    									        echo "Ej: 1978-05-30";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($basic_info[$current_box] != '' && $basic_info[$current_box] != '0000-00-00'){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>" required
    									>
    									<?php 
    									if(in_array($current_box,$basic_error_array)){
    										switch ($lang){
    											case "en":
    												echo "<div id='wrong_input'>Incorrect date format, insert as YYYY-MM-DD <br>(Example: 1980-02-22)</div>";
    												break;
    											case "es":
    												echo "<div id='wrong_input'>Formato de fecha incorrecto, debe ser como AAAA-MM-DD <br> (Ejemplo: 1980-02-22)</div>";
    												break;
    										}
    										
    									}
    									?>
									</div>
								</div>
								
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "children"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Number of Children";
									        break;
									    case("es"):
									        echo "Número de hijos";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: 2";
    									        break;
    									    case("es"):
    									        echo "Ej: 2";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    											if($basic_info[$current_box] != ''){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>">
    								</div>		
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "marital_status"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Marital Status";
									        break;
									    case("es"):
									        echo "Estado Civil";
									        break;
									}
									?>
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?>>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "education_level"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Education Level";
									        break;
									    case("es"):
									        echo "Nivel de educación";
									        break;
									}
									?>
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?>>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "occupation"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Occupation";
									        break;
									    case("es"):
									        echo "Oficio";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: Lawyer";
    									        break;
    									    case("es"):
    									        echo "Ej: Abogado";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    											if($basic_info[$current_box] != ''){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>">
    								</div>		
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "religion"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Religion";
									        break;
									    case("es"):
									        echo "Religión";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: Catholic";
    									        break;
    									    case("es"):
    									        echo "Ej: Católico";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    											if($basic_info[$current_box] != ''){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>">
    								</div>		
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "languages"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Language(s)";
									        break;
									    case("es"):
									        echo "Idioma(s)";
									        break;
									}
									?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    									switch($lang){
    									    case("en"):
    									        echo "Ex: English, Spanish";
    									        break;
    									    case("es"):
    									        echo "Ej: Español, Inglés";
    									        break;
    									}
    									?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    											if($basic_info[$current_box] != ''){
    												echo $txt_rep->entities($basic_info[$current_box]);
    											}
    										?>">
    								</div>		
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "insurance_CO"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Insurance *";
									        break;
									    case("es"):
									        echo "Seguro o prepagada *";
									        break;
									}
									?>
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> required>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info['insurance'] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info['insurance'] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "laterality"; ?>
									<p class="dashboard_tag">
									<?php 
									switch($lang){
									    case("en"):
									        echo "Laterality (Which hand do you use the most to perform actions)";
									        break;
									    case("es"):
									        echo "Lateralidad (Con qué mano predominan sus acciones)";
									        break;
									}
									?>	
									</p>
									<div class="dashboard_arrow">
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?>>
    										<?php 
    											$stmt = $con->prepare("SELECT $lang,id FROM $current_box");
    											$stmt->execute();
    											$res = $stmt->get_result();
    											
    											if($basic_info[$current_box] == ''){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											while($arr = mysqli_fetch_array($res)){
    												if($basic_info[$current_box] === $arr['id']){
    													echo "<option value='" . $arr['id'] . "' selected='selected' >" . $arr[$lang] . "</option>";
    												}
    												else{
    													echo "<option value='" . $arr['id'] . "'>" . $arr[$lang] . "</option>";
    												}
    											}
    										?>
    									</select>
    								</div>	
								</div>		
						 	
						 	</div>
							<div class="three_button_navigation">
								<div class="left_3_button_navigation">
								</div>
								<div class="right_3_button_navigation">
								</div>
								<div class="center_3_button_navigation">
		
									<input type="submit" id="save_data_stats_butt" name="save_personal_info" value="<?php 
									switch($lang){
									    case("en"):
									        echo "Save";
									        break;
									    case("es"):
									        echo "Guardar";
									        break;
									}
									?>">
		
								</div>
							</div>
					 	</form>
					</div>
		
					<div role="tabpanel" class="tab-pane fade" id="pathologies">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Illnesses and/or Hospitalizations";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega Enfermedades y/o Hospitalizaciones";
		        					        break;
		        					}
							?></h3>
					 	<form action="health_info_input.php" method="POST" name="form_pathologies" class="form_pathologies">
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Illnesses / Hospitalizations";
		        					        break;
		        					    case("es"):
		        					        echo "Enfermedades / Hospitalizaciones";
		        					        break;
		        					}
							?></p>
			
								<div class="dashboard_info">
    								<input type="text" name="patho_desc" id="select_illness" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Diabetes";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: Diabetes";
    		        					        break;
    		        					}
    							?>" required>
    							</div>
							</div>
							
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Approximated Date of Diagnosis or Hospitalization (YYYY-MM-DD)";
		        					        break;
		        					    case("es"):
		        					        echo "Fecha aproximada de diagnóstico u hospitalización (AAAA-MM-DD)";
		        					        break;
		        					}
							?></p>
								<div class="dashboard_info">
								<input type="text" name="patho_date" id="select_illness" placeholder="<?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Ex: 2005-07-30";
		        					        break;
		        					    case("es"):
		        					        echo "Ej: 2005-07-30";
		        					        break;
		        					}
							?>" required>
							
							</div> 
							<input type="submit" name="patho_butt" id="save_data_stats_add" value="+">
								<b class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "(You can input just a year, year and month, or the full date.)";
		        					        break;
		        					    case("es"):
		        					        echo "Puedes escribir sólo un año, un mes y un año o la fecha completa";
		        					        break;
		        					}
							?></b>
						
		
			
								<?php 
								if(in_array("pathologies",$basic_error_array)){
								    switch($lang){
								        case("en"):
								            echo "<div class='warning_dashboard'><b>Incorrect date format, insert as YYYY-MM-DD, YYYY-MM, or YYYY.</b></div>";
								            break;
								        case("es"):
								            echo "<div class='warning_dashboard'><b>Formato de fecha incorrecto, insértalo como AAAA-MM-DD, AAAA-MM, o AAAA.</b></div>";
								            break;
								    }
								}
								?>
								
							</div>
							
					 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia";
		        					        break;
		        					}
							?></h3>
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Illnesses / Hospitalizations";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Enfermedades / Hospitalizaciones";
		                    					        break;
		                    					}
		            					?></p>
								</div>
								<div class="box_right">
									<p><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Approximate Date";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Fecha Aproximada";
		                    					        break;
		                    					}
		            					?></p>
								</div>
							</div>
		
				
							<div class="added_box style-2" id="pathologies_box">
								<?php 
									
									echo $user_obj->getPathologiesData(date("Y-m-d H:i:s"));
								?>
							</div>
							
						</div>
					</div>
					
					
					<div role="tabpanel" class="tab-pane fade" id="surgical_trauma">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Surgeries and/or Traumas";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega cirugías y/o trauma";
		        					        break;
		        					}
							?> </h3>
					 	<form action="health_info_input.php" method="POST" name="form_surgeries" class="form_surgeries">
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Surgeries / Traumas";
		        					        break;
		        					    case("es"):
		        					        echo "Cirugías / Trauma";
		        					        break;
		        					}
							?></p>
							<div class="dashboard_info">	
    								<input type="text" name="surgeries_desc" id="select_surgery"  placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Bone fracture, appendectomy";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: Fractura de hueso, apendicectomía";
    		        					        break;
    		        					}
    							?>" required>
    							</div>
			
							</div>
							
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Approximated Date (YYYY-MM-DD)";
		        					        break;
		        					    case("es"):
		        					        echo "Fecha aproximada (AAAA-MM-DD)";
		        					        break;
		        					}
							?></p>
							
							<div class="dashboard_info">	
    								<input type="text" name="surgeries_date" id="select_surgery" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: 2005-07-30";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: 2005-07-30";
    		        					        break;
    		        					}
    							?>" required>
    						
    						</div>	
    						<input type="submit" name="surgeries_butt" id="save_data_stats_add" value="+">	
							<b class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "(You can input just a year, year and month, or the full date.)";
		        					        break;
		        					    case("es"):
		        					        echo "Puedes escribir sólo un año, un mes y un año o la fecha completa";
		        					        break;
		        					}
							?></b>
			
								<?php 
								if(in_array("surgeries",$basic_error_array)){
								    switch($lang){
								        case("en"):
								            echo "<div class='warning_dashboard'><b>Incorrect date format, insert as YYYY-MM-DD, YYYY-MM, or YYYY.</b></div>";
								            break;
								        case("es"):
								            echo "<div class='warning_dashboard'><b>Formato de fecha incorrecto, insértalo como AAAA-MM-DD, AAAA-MM, o AAAA.</b></div>";
								            break;
								    }
									
								}
								?>
							</div>
							
								
							
					 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia";
		        					        break;
		        					}
							?></h3>
				
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Surgery / Trauma";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Cirugía / Trauma";
		                    					        break;
		                    					}
		            					?></p>
								</div>
								<div class="box_right">
									<p><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Approximate Date";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Fecha Aproximada";
		                    					        break;
		                    					}
		            					?></p>
								</div>
							</div>
						
				
							<div class="added_box style-2" id="surgical_trauma_box">
								<?php  
									echo $user_obj->getSurgeriesData(date("Y-m-d H:i:s"));
								?>
							</div>
						</div>
					</div>
					
					<div role="tabpanel" class="tab-pane fade" id="hereditary">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Important Diseases in Your Family.";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega enfermedades importantes en tu familia";
		        					        break;
		        					}
							?></h3>
			 	<form action="health_info_input.php" method="POST" name="form_hereditary" class="form_surgeries">
		
		          <div class="dashboard_tag_block" >
					 		<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Disease:";
		        					        break;
		        					    case("es"):
		        					        echo "Enfermedad:";
		        					        break;
		        					}
							?></p>
							<div class="dashboard_info">
    				 			<input type="text" onkeyup="sanitizeSearchHealth(this.value,'<?php echo 'hereditary_diseases'; ?>','<?php echo $lang; ?>')" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Breast Cancer, Heart Disease";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Cáncer de seno, Enfermedad coronaria";
    		        					        break;
    		        					}
    							?>" autocomplete="off" class="search_health_info" name="hereditary_diseases_input" >
    						</div>	
    							<div class="button_holder_search_health">
    								<img src="assets/images/icons/search-icon-pink.png">
    							</div>
    						</div>	
						 <div class="dashboard_tag_block" >	
							<p class="dashboard_tag">
							<?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Relative:";
		        					        break;
		        					    case("es"):
		        					        echo "Familiar:";
		        					        break;
		        					}
							?></p>
							<div class="dashboard_info">
    							<input type="text" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Father, Sister";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: Padre, hermana";
    		        					        break;
    		        					}
    							?>" autocomplete="off" class="search_health_info" name="hereditary_relatives" id="relatives">
   
							
							
							
						</div>	
						<input type="submit" name="hereditary_butt" value="+" id="save_data_stats_add">
		          </div>	
			 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia:";
		        					        break;
		        					}
							?></h3>
				
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Diseases";
		        					        break;
		        					    case("es"):
		        					        echo "Enfermedades";
		        					        break;
		        					}
							?></p>
								</div>
								<div class="box_right">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Relatives";
		        					        break;
		        					    case("es"):
		        					        echo "Familiares";
		        					        break;
		        					}
							?></p>
								</div>
							</div>
							
				
							<div class="added_box style-2" id="hereditary_box">
								<?php  
									echo $user_obj->getHereditariesData();
								?>
							</div>
						</div>
					</div>
					
					
					<div role="tabpanel" class="tab-pane fade" id="pharmacology">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Medicines you Use";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega medicamentos que uses";
		        					        break;
		        					}
							?></h3>
			 	<form action="health_info_input.php" method="POST" name="form_pharmacology" class="form_surgeries">
		
		         <div class="dashboard_tag_block">
		         		
					 		<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Medicine name:";
		        					        break;
		        					    case("es"):
		        					        echo "Nombre del medicamento";
		        					        break;
		        					}
							?></p>
						<div class="dashboard_info">	
							<input type="text" onkeyup="sanitizeSearchHealth(this.value,'<?php echo 'medicines2dosage'; ?>','<?php echo $lang; ?>')" placeholder="<?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Ex: Ibuprofen, Omeprazol";
		        					        break;
		        					    case("es"):
		        					        echo "Ej: Ibuprofeno, Omeprazol";
		        					        break;
		        					}
							?>" autocomplete="off" class="search_health_info" name="medicines2dosage_input" >
							<div class="search_history_results style-2" id="search_medicines2dosage"></div>
							
						</div>
						<div class="button_holder_search_health">
								<img src="assets/images/icons/search-icon-pink.png">
						</div>
				</div>		
		         <div class="dashboard_tag_block">
		          				<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Dosage:";
		        					        break;
		        					    case("es"):
		        					        echo "Dosis:";
		        					        break;
		        					}
							?></p>
	
    				 		<div class="dashboard_info">						
    							<input type="text" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: 50 mg in the morning";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ej: 50 mg cada mañana";
    		        					        break;
    		        					}
    							?>" autocomplete="off" class="search_health_info" name="medicines2dosage_dosage_input" id="dosage">
    						</div>	
							<input type="submit" name="medicines_butt" value="+"  id="save_data_stats_add">
							
							
							</div>	
							<input type="hidden" name="lang" value=<?php echo '"' . $lang . '"'; ?>>
							<input type="hidden" name="searched_id_medicines2dosage">
		     			
				 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia";
		        					        break;
		        					}
							?></h3>
				
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Medicines:";
		        					        break;
		        					    case("es"):
		        					        echo "Medicamentos:";
		        					        break;
		        					}
							?></p>
								</div>
								<div class="box_right">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Dosage";
		        					        break;
		        					    case("es"):
		        					        echo "Dosis";
		        					        break;
		        					}
							?></p>
								</div>
							</div>
							
				
							<div class="added_box style-2" id="medicines_box">
								<?php  
									echo $user_obj->getMedicinesData();
								?>
							</div>
						</div>
					</div>		
					
					
					<div role="tabpanel" class="tab-pane fade" id="allergies">
						<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Add Known Allergies";
		        					        break;
		        					    case("es"):
		        					        echo "Agrega alergias conocidas";
		        					        break;
		        					}
							?> </h3>
			 	<form action="health_info_input.php" method="POST" name="form_allergies" class="form_surgeries">
		
					 		<div class="dashboard_tag_block">
								<p class="dashboard_tag"><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Allergies:";
		        					        break;
		        					    case("es"):
		        					        echo "Alergias:";
		        					        break;
		        					}
							?></p>
								<div class="dashboard_info">
    								<input type="text" name="allergies_input" id="select_allergies" placeholder="<?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Ex: Penicillin, Peanuts";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Ex: Penicilina, Fresas";
    		        					        break;
    		        					}
    							?>"  required>
    							</div>
    		
			
								<input id="save_data_stats_add" type="submit" name="allergies_butt" value="+"  id="save_data_stats_add">
							</div>
									
				 	</form>
					 	<hr>
					 	<div class="box_div_container">
							<h3><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Added to your Record:";
		        					        break;
		        					    case("es"):
		        					        echo "Agregado a tu Historia";
		        					        break;
		        					}
							?></h3>
				
							<div class="added_box_header">
								<div class="box_left">
									<p><?php 
		        					switch($lang){
		        					    case("en"):
		        					        echo "Allergies:";
		        					        break;
		        					    case("es"):
		        					        echo "Alergias:";
		        					        break;
		        					}
							?></p>
								</div>
							</div>
							
				
							<div class="added_box style-2" id="allergies_box">
								<?php  
									echo $user_obj->getAllergiesData();
								?>
							</div>
						</div>
					</div>
					
					
					<div role="tabpanel" class="tab-pane fade" id="habits" >
						<h3><?php 
				
							switch ($lang){
								
								case("en"):
									echo "Fill-in the next cells according your habits";
									break;
									
								case("es"):
									echo "Completa los siguientes campos de acuerdo a tus hábitos";
									break;
							}
							
						?></h3>
						<?php
							$habits_table = $user_obj->getHabitsTable();
							$stmt = $con->prepare("SELECT * FROM $habits_table ORDER BY id DESC LIMIT 1");
							$stmt->execute();
							$habits_info = mysqli_fetch_array($stmt->get_result());
						?>
					
					 	<form action="health_info_input.php" method="POST">
						 	<div class="form_area">
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "smoking"; ?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Do you smoke? How much and how often?";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Fumas? Qué tanto y qué tan seguido?";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: 3 packs a day.";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ex: 3 packetes diarios";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="30" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
									></div>
								</div>
								
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "alcohol"; ?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Do you drink alcohol? How much and how often?";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Bebes alcohol? Qué tanto y qué tan seguido?";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: 2 beers a week";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ex: 2 cervezas semanales";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="30" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
    									></div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "diet"; ?>
									<p class="dashboard_tag"><?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Describe your diet briefly";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Describe tu dieta de manera concisa";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: Vegetarian, Low Sodium";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Vegetariana, Baja en sodio";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="30" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
    									></div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "physical_activity"; ?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Are you physically active? How so?";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Eres físicamente activo? Explica";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: 20 minute walk daily";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Camino 20 minutos al día";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="30" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
    									></div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php $current_box = "other"; ?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Others";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Otros";
		                    					        break;
		                    					}
		            					?>
									</p>
									<div class="dashboard_info">
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: Drugs consumption";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Consumo de drogas";
    		                    					        break;
    		                    					}
    		            					?>" autocomplete="off" maxlength="30" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($habits_info[$current_box] != ''){
    											echo $txt_rep->entities($crypt->decryptStringPI($habits_info[$current_box], $userLoggedIn, $user_obj->user_info['signup_date']));
    										}
    										?>"
    									></div>
								</div>
								
								<div class="three_button_navigation">
									<div class="left_3_button_navigation">
									</div>
									<div class="right_3_button_navigation">
									</div>
									<div class="center_3_button_navigation">
										<input type="submit" id="save_data_stats_butt" name="save_habits_info" value="<?php 
        									switch($lang){
        									    case("en"):
        									        echo "Save";
        									        break;
        									    case("es"):
        									        echo "Guardar";
        									        break;
        									}
        									?>">
									</div>
								</div>
								
							</div>
						</form>
					</div>			
					
					<div role="tabpanel" class="tab-pane fade" id="OBGYN" >
						<?php
							$OBGYN_table = $user_obj->getOBGYNTable();
							$stmt = $con->prepare("SELECT * FROM $OBGYN_table ORDER BY id DESC LIMIT 1");
							$stmt->execute();
							$OBGYN_info = mysqli_fetch_array($stmt->get_result());
						?>
				 		<form action="health_info_input.php" method="POST">
						 	<div class="form_area">
    						 	<h3><?php 
    		        					switch($lang){
    		        					    case("en"):
    		        					        echo "Add Gynecologic Data";
    		        					        break;
    		        					    case("es"):
    		        					        echo "Agrega Información Ginecológica";
    		        					        break;
    		        					}
    							?></h3>
    		
						 		<div class="dashboard_tag_block">
						 			<?php $current_box = "menarche"; ?>
									<p class="dashboard_tag">
										<?php 
		                        					switch($lang){
		                        					    case("en"):
		                        					        echo "Age of first menstrual period";
		                        					        break;
		                        					    case("es"):
		                        					        echo "Edad de la primera menstruación";
		                        					        break;
		                        					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											$session_string = "select_" . $current_box;
    											for($i=7;$i<=22;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif($_SESSION[$session_string] !== '' && isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otra </option>";
    											        break;
    											}
    											
    										?>
    									</select>
									</div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "lmp"; 
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                        					switch($lang){
		                        					    case("en"):
		                        					        echo "First day of last menstrual period (YYYY-MM-DD):";
		                        					        break;
		                        					    case("es"):
		                        					        echo "Fecha de primer dia de última regla (AAAA-MM-DD)";
		                        					        break;
		                        					}
		                					?>
									</p>
									<div class="dashboard_info" >
    									<input type="text" placeholder="Ex: <?php echo date('Y-m-d');?>" autocomplete="off" maxlength="10" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($OBGYN_info[$current_box] != '' && $OBGYN_info[$current_box] != '0000-00-00'){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											if($_SESSION[$session_string] !== ''){
    												echo $_SESSION[$session_string];
    											}
    											else{
    												echo date('Y') . '-';
    											}
    										}
    										else{
    											echo date('Y') . '-';
    										}
    										?>"
    									>
    								</div>
    									<?php
    										if(in_array($current_box,$women_error_array)){
    										    switch($lang){
    										        case("en"):
    										            echo "<div class='warning_dashboard'><b>Incorrect date format, insert as YYYY-MM-DD or leave blank</b></div>";
    										            break;
    										        case("es"):
    										            echo "<div class='warning_dashboard'><b>Formato de fecha incorrecto, inserta YYYY-MM-DD o deja en blanco</b></div>";
    										            break;
    										    }
    										}
    									?>
    								</div>
								
								<div class="dashboard_tag_block">
							 		<?php 
							 			$current_box = "cycles";
							 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                        					switch($lang){
		                        					    case("en"):
		                        					        echo "Cycle description:";
		                        					        break;
		                        					    case("es"):
		                        					        echo "Descripción de ciclos:";
		                        					        break;
		                        					}
		                					?>
									</p>
									<div class="dashboard_info" >
    									<input type="text" placeholder="<?php 
    		                        					switch($lang){
    		                        					    case("en"):
    		                        					        echo "Ex: irregular, every 27 days for 3 days, ...";
    		                        					        break;
    		                        					    case("es"):
    		                        					        echo "Ex: irregular, cada 27 días por 3 días, ...";
    		                        					        break;
    		                        					}
    		                					?>" autocomplete="off" maxlength="20" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> value="<?php 
    										if($OBGYN_info[$current_box] != ''){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											if($_SESSION[$session_string] !== ''){
    												echo $_SESSION[$session_string];
    											}
    										}
    										
    										?>"
    									>
    									</div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "gestations";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 10;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Gestations (total pregnancies):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Gestaciones (número total de embarazos):";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    									</div>
    								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "parity";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 10;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Births (number of children previously born):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Cantidad de nacimientos:";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "abortions";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 10;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Abortions:";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Cantidad de abortos:";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "csections";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 10;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "C-Sections::";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Cantidad de cesáreas:";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>						
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "ectopic";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 0;
						 				$higher_value = 3;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Ectopic pregnancies:";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Embarazos ectópicos:";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otro </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 				$current_box = "menopause";
						 				$session_string = "select_" . $current_box;
						 				$lower_value = 30;
						 				$higher_value = 70;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Age of Final Menstruation (Menopause):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Edad de menstruación final (menopausia)";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_arrow" >	
    									<select name=<?php echo '"select_' . $current_box . '"'; ?> id=<?php echo '"select_' . $current_box . '"'; ?> >
    										<?php 									
    											if($OBGYN_info[$current_box] == '' || $OBGYN_info[$current_box] == '-1'){
    												echo "<option selected='selected' value=''>-</option>";
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='DNA'> Does not apply</option>";
    											        break;
    											    case("es"):
    											        echo "<option value='NA'> No aplica </option>";
    											        break;
    											}
    											for($i=$lower_value;$i<=$higher_value;$i++){
    												if($OBGYN_info[$current_box] === $i){
    													echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    												}
    												elseif(isset($_SESSION[$session_string])){
    													if($_SESSION[$session_string] !== '' && $_SESSION[$session_string] == $i){
    														echo "<option value='" . $i . "' selected='selected' >" . $i . "</option>";
    													}
    													else{
    														echo "<option value='" . $i . "'>" . $i . "</option>";
    													}
    												}
    												else{
    													echo "<option value='" . $i . "'>" . $i . "</option>";
    												}
    											}
    											switch($lang){
    											    case("en"):
    											        echo "<option value='Other'> Other </option>";
    											        break;
    											    case("es"):
    											        echo "<option value='Otra'> Otra </option>";
    											        break;
    											}
    										?>
    									</select>
    								</div>	
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "birthcontrol";
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Birth Control (Method and/or Name):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Planificación (Método y/o nombre)";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_info" >	
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: Daily pill, Copper T";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Inyección trimestral, T de cobre";
    		                    					        break;
    		                    					}
    		                					?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> maxlength="30" value="<?php 
    										if($OBGYN_info[$current_box] != ''){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											echo $_SESSION[$session_string];
    										}
    										?>"
    									></div>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "mammography_date";
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Last Mammography Date (YYYY-MM-DD):";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Fecha de última mamografía (AAAA-MM-DD):";
		                    					        break;
		                    					}
		                					?>
									</p>
									<div class="dashboard_info" >	
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: 2017-06-14";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: 2017-06-14";
    		                    					        break;
    		                    					}
    		                					?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> maxlength="10" value="<?php 
    										if($OBGYN_info[$current_box] != '' && $OBGYN_info[$current_box] != '0000-00-00'){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											echo $_SESSION[$session_string];
    										}
    										?>"
    									></div>
									<?php 
										if(in_array($current_box,$women_error_array)){
										    switch($lang){
										        case("en"):
										            echo "<div class='warning_dashboard'><b>Incorrect date format, insert as YYYY-MM-DD</b></div>";
										            break;
										        case("es"):
										            echo "<div class='warning_dashboard'><b>Formato de fecha incorrecto, insertar como AAAA-MM-DD</b></div>";
										            break;
										    }
										}
									?>
								</div>
								
								<div class="dashboard_tag_block">
						 			<?php 
						 			$current_box = "mammography_result";
						 			$session_string = "select_" . $current_box;
						 			?>
									<p class="dashboard_tag">
										<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Last Mammography Results:";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Resultados de última mamografía:";
		                    					        break;
		                    					}
		                					?>
										
									</p>
									<div class="dashboard_info" >	
    									<input type="text" placeholder="<?php 
    		                    					switch($lang){
    		                    					    case("en"):
    		                    					        echo "Ex: Negative, Abnormal";
    		                    					        break;
    		                    					    case("es"):
    		                    					        echo "Ej: Negativa, Anormal";
    		                    					        break;
    		                    					}
    		                					?>" autocomplete="off" id=<?php echo '"select_' . $current_box . '"'; ?> name=<?php echo '"select_' . $current_box . '"';?> maxlength="80" value="<?php 
    										if($OBGYN_info[$current_box] != ''){
    											echo $txt_rep->entities($OBGYN_info[$current_box]);
    										}
    										elseif(isset($_SESSION[$session_string])){
    											echo $_SESSION[$session_string];
    										}
    										?>"
    									></div>
								</div>
								
								<div class="three_button_navigation">
									<div class="left_3_button_navigation">
									</div>
									<div class="right_3_button_navigation">
									</div>
									<div class="center_3_button_navigation">
		
										<input type="submit" id="save_data_stats_butt" name="save_women_info" value="<?php 
		                    					switch($lang){
		                    					    case("en"):
		                    					        echo "Save";
		                    					        break;
		                    					    case("es"):
		                    					        echo "Guardar";
		                    					        break;
		                    					}
		                					?>">
		
									</div>
								</div>
								
							</div>
						</form>
					</div>	
					
		 		</div> <!-- end of the tabbed area -->
		</div>
	</div>
</div>