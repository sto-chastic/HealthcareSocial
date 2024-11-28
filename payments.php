<?php
include('includes/header.php');
require 'vendor/autoload.php';

// $user_agent = $_SERVER['HTTP_USER_AGENT'];
// $ip = $_SERVER['REMOTE_ADDR']?:($_SERVER['HTTP_X_FORWARDED_FOR']?:$_SERVER['HTTP_CLIENT_IP']);

//Get IP address

// if (isset($_SERVER["HTTP_CLIENT_IP"]))
// {
// 	$ip = $_SERVER["HTTP_CLIENT_IP"];
// }
// elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
// {
// 	$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
// }
// elseif (isset($_SERVER["HTTP_X_FORWARDED"]))
// {
// 	$ip = $_SERVER["HTTP_X_FORWARDED"];
// }
// elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]))
// {
// 	$ip = $_SERVER["HTTP_FORWARDED_FOR"];
// }
// elseif (isset($_SERVER["HTTP_FORWARDED"]))
// {
// 	$ip = $_SERVER["HTTP_FORWARDED"];
// }
// else
// {
// 	$ip = $_SERVER["REMOTE_ADDR"];
// }

if(!empty($_SERVER['HTTP_CLIENT_IP'])){
	$ip=$_SERVER['HTTP_CLIENT_IP'];
}
elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
	$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
}
else{
	$ip=$_SERVER['REMOTE_ADDR'];
}

$invalid_arr = [];
$invalid_pay_arr = [];

function is_valid_CCN($number) {
	settype($number, 'string');
	$sumTab = array(
			array(0,1,2,3,4,5,6,7,8,9),
			array(0,2,4,6,8,1,3,5,7,9));
	$sum = 0;
	$flip = 0;
	for ($i = strlen($number) - 1; $i >= 0; $i--) {
		$sum += $sumTab[$flip++ & 0x1][$number[$i]];
	}
	return $sum % 10 === 0;
}

function company_name($number){
	$sub_num3 = substr($number, 0, 3);
	$sub_num2 = substr($number, 0, 2);
	$sub_num1 = substr($number, 0, 1);
	
	$type = "";
	if($sub_num3 == "300" || $sub_num3 == "305"){
		$type = "DINERS";
	}
	else	if($sub_num2== "36" || $sub_num2 == "38"){
		$type = "DINERS";
	}
	else	if($sub_num2== "37"){
		$type = "AMEX";
	}
	else	if($sub_num1== "3"){
		$type = "AMEX";
	}
	else	if($sub_num1== "4"){
		$type = "VISA";
	}
	else	if($sub_num1== "5"){
		$type = "MASTERCARD";
	}
	
	return $type;
}

function check_company_vs_length($number){
	$type = company_name($number);
	
	$valid = false;
	
	switch ($type){
		case "VISA":
			if(strlen($number) == 13 || strlen($number) == 16){
				$valid = true;
			}
			break;
		case "MASTERCARD":
			if(strlen($number) == 16){
				$valid = true;
			}
			break;
		case "AMEX":
			if(strlen($number) == 15){
				$valid = true;
			}
			break;
		case "DINERS":
			if(strlen($number) == 14){
				$valid = true;
			}
			break;
	}
	
	return $valid;
}

if(isset($_POST['add_pay_email'])){
	$invalid_pay_arr = [];
	
	$em = strip_tags($_POST['pay_email']);//Remove html tags
	$em = str_replace(' ', '', $em); //Remove spaces
	$em = preg_replace('/[^a-zA-Z0-9_.@-]/', '', $em);//Remove special characters (even inputed with ascii)
	
	if((!filter_var($em, FILTER_VALIDATE_EMAIL) === false) && (!preg_match('/[^a-zA-Z0-9_.@-]/', $em))){
		$em = filter_var($em, FILTER_VALIDATE_EMAIL);
		$payments_tab = $user_obj->getPaymentsTab();
		$stmt = $con->prepare("INSERT INTO $payments_tab (id, name, cc_token, client_token, cc_4_dig, email, docu_number, rec_pay_email, phone, ip) VALUES(1,'','','','','','',?,'','') ON DUPLICATE KEY UPDATE rec_pay_email=?");
		$stmt->bind_param("ss",$em,$em);
		$stmt->execute();
		
	}
	else{
		array_push($invalid_pay_arr, "pay_email") ;
	}
}

if(isset($_POST['add_credicard'])){
	$invalid_arr = [];
	
	$name= $_POST['name'];
	if(!preg_match('/[^a-zA-Z0-9.\s-]/', $name)){
		$name= strip_tags($name);//Remove html tags
		$name= preg_replace('/[^a-zA-Z0-9.\s-]/', '', $name);
	}
	else{
		array_push($invalid_arr, "name");
	}
	
	$phone= rtrim(ltrim($_POST['phone']));
	if(!preg_match('/[^0-9]/', $phone) && strlen($phone) >=7 && strlen($phone) <=10){
		$phone= strip_tags($phone);//Remove html tags
		$phone= preg_replace('/[^0-9]/', '', $phone);
	}
	else{
		array_push($invalid_arr, "phone");
	}
	
 	
	$card_number= rtrim(ltrim($_POST['card_number']));
	if(!preg_match('/[^0-9]/', $card_number)){
		$card_number= strip_tags($card_number);//Remove html tags
		$card_number= preg_replace('/[^0-9]/', '', $card_number);
	}
	else{
		array_push($invalid_arr, "card_number");
	}
	$last_4 = "" . substr($card_number,-4);

	$cvc= rtrim(ltrim($_POST['cvc']));
	if(!preg_match('/[^0-9]/', $cvc)){
		$cvc= strip_tags($cvc);//Remove html tags
		$cvc= preg_replace('/[^0-9]/', '', $cvc);
	}
	else{
		array_push($invalid_arr, "cvc");
	}
	
	$exp_month= rtrim(ltrim($_POST['exp_month']));
	if(!preg_match('/[^0-9]/', $exp_month) && strlen($exp_month) == 2){
		$exp_month= strip_tags($exp_month);//Remove html tags
		$exp_month= preg_replace('/[^0-9]/', '', $exp_month);
	}
	else{
		array_push($invalid_arr, "month");
	}
	
	$exp_year= rtrim(ltrim($_POST['exp_year']));
	if(!preg_match('/[^0-9]/', $exp_year) && strlen($exp_year) == 4){
		$exp_year= strip_tags($exp_year);//Remove html tags
		$exp_year= preg_replace('/[^0-9]/', '', $exp_year);
	}
	else{
		array_push($invalid_arr, "year");
	}
	
	$cedula = rtrim(ltrim($_POST['cedula']));
	if(!preg_match('/[^0-9]/', $cedula)){
		$cedula= strip_tags($cedula);//Remove html tags
		$cedula= preg_replace('/[^0-9]/', '', $cedula);
	}
	else{
		array_push($invalid_arr, "cedula");
	}
	
	$email = $user_obj->getEmail();
	
	
	if(empty($invalid_arr)){
		//Check if Client exists
		$payments_tab = $user_obj->getPaymentsTab();
		$stmt = $con->prepare("SELECT * FROM $payments_tab");
		$stmt->execute();
		
		$q = $stmt->get_result();
		$nums = mysqli_num_rows($q);
		
		$epayco = new Epayco\Epayco(array(
				"apiKey" => "f83785ced1e42608d315d1a14fc3fe93",
				"privateKey" => "c1e131360d2f3ba9ca9fa49325734364",
				"lenguage" => "ES",
				"test" => true
		));
		
	
		$token = $epayco->token->create(array(
				"card[number]" => (string)$card_number,
				"card[exp_year]" => (string)$exp_year,
				"card[exp_month]" => (string)$exp_month,
				"card[cvc]" => (string)$cvc
		));
		
		$token_card_num = $token->data->id;
		
		//If doesn't exist, create. Else, update
		if($nums == 0){
			$customer = $epayco->customer->create(array(
					"token_card" => $token_card_num,
					"name" => (string)$name,
					"email" => (string)$email,
					"phone" => (string)$phone,
					"default" => true
			));
			if(property_exists($customer->data,"customerId")){
				$token_customer = $customer->data->customerId;
			}
		}
		else{
			
//			Currently, ePayCo does not have a good way to update a customer info. The following code might work in the future.
			
// 			$pay_arr = mysqli_fetch_assoc($q);
// 			$customer = $epayco->customer->update($pay_arr['client_token'], array(
// 					"token_card" => $token_card_num,
// 					"default" => true));
			
// 			$customer = $epayco->customer->update($pay_arr['client_token'], array(
// 					"name" => (string)$name,
// 					"email" => (string)$email,
// 					"phone" => "2222222"));
			
			$customer = $epayco->customer->create(array(
					"token_card" => $token_card_num,
					"name" => (string)$name,
					"email" => (string)$email,
					"phone" => (string)$phone,
					"default" => true
 			));
			//print_r($customer);
			if(property_exists($customer->data,"customerId")){
				$token_customer = $customer->data->customerId;
			}
		}
	
		if(isset($token_customer)){
			$stmt = $con->prepare("INSERT INTO $payments_tab (id, name, cc_token, client_token, cc_4_dig, email, docu_number, rec_pay_email, phone, ip) VALUES(1,?,?,?,?,?,?,'',?,?) ON DUPLICATE KEY UPDATE name=?, cc_token=?, client_token=?, cc_4_dig=?, email=?, docu_number=?, phone=?, ip=?");
			$stmt->bind_param("ssssssssssssssss",$name,$token_card_num,$token_customer,$last_4,$email,$cedula,$phone,$ip,$name,$token_card_num,$token_customer,$last_4,$email,$cedula,$phone,$ip);
			$stmt->execute();
			
			$customer = $epayco->customer->getList();
			
			switch ($lang){
				
				case("en"):
					echo "<p style=' color: rgb(0, 201, 0);'>Success: Your information was added successfully.</p>";
					break;
					
				case("es"):
					echo "<p style=' color: rgb(0, 201, 0);'>Tu información fue agregada exitosamente.</p>";
					break;
			}
		}
		else{
			switch ($lang){
				
				case("en"):
					echo "<p id='wrong_inputt'>Error: Try again later.</p>";
					break;
					
				case("es"):
					echo "<p id='wrong_input'>Error: Intenta de nuevo más tarde.</p>";
					break;
			}
		}
		//print_r($customer);
	}
}

?>



<?php 
//Financial info

$payments_tab_ = $user_obj->getPaymentsTab();
$stmt = $con->prepare("SELECT * FROM $payments_tab_");
$stmt->execute();

$q = $stmt->get_result();
$nums = mysqli_num_rows($q);

if($nums > 0){
	$reg = true;
	$fin_info_arr = mysqli_fetch_assoc($q);
}
else{
	$reg = false;
}
?>
	<style type="text/css">
		.nav-tabs > li {  
            width: 100%;
        }
        .dashboard_tag_block {
            display: inline-grid;
            padding: 5px;
            position: relative;
            float: left;        
         
    
        }
	</style>
	<div class="main_column column">
		<ul class="nav nav-tabs" role="tablist" id="profileTabs">
		  <li role="presentation" class="active"><div class="arrow-down"></div><a href="#making_div" aria-controls="making_div" role="tab" data-toggle="tab"><span id="send_pay_tab">hol</span> 
		  <?php switch($lang){
		      case("en"):
		          echo "Making Payments";     
		          break;
		      case("es"):
		          echo "Hacer Pagos";
		          break;
		  }?>
		  </a></li>
		  
		  <?php
		  
		  if($user_obj->isDoctor()){
		  	
		  ?>
		  	<style type="text/css">
            		.nav-tabs > li {  
                        width: 50%;
                    }
            </style>
			  <li role="presentation"><div class="arrow-down"></div><a href="#receiving_div" aria-controls="receiving_div" role="tab" data-toggle="tab"><span id="receive_pay_tab">hol</span>
			  <?php switch($lang){
			      case("en"):
			          echo "Receiving Payments";     
			          break;
			      case("es"):
			          echo "Recibir Pagos";
			          break;
			  }?></a></li>
		  
		  <?php 
		  }
		  ?>
		</ul>
		<div class="tab-content">		
		<div class="title_tabs" ><?php switch($lang){
    		    case("en"):
    		        echo "Payments Settings";
    		        break;
    		    case("es");
                    echo "Configuración de pagos";
                    break;
    		}?></div>
  	
        <div class="main_settings settings_div">	
    				

		<div role="tabpanel" class="tab-pane fade in active" id="making_div">
				<h1>
    			<?php 
    			switch ($lang){
    				
    				case("en"):
    					echo "Set up your information for making and/or receiving payments.";
    					break;
    					
    				case("es"):
    					echo "Establece tu información para hacer y/o recibir pagos.";
    					break;
    			}
    			?>
    		</h1>
				<h3>
				<?php switch($lang){
			    case("en"):
			    		echo "Credit Card Information";     
			        break;
			    case("es"):
			    		echo "Información de Tarjeta de Crédito";
			   		break;
				}?>
				</h3>
				<p>
				<?php switch($lang){
			    case("en"):
			    		echo "(If you need to edit the information of a field, you are required to fill all fields again.)";     
			        break;
			    case("es"):
			    		echo "(Si necesitas editar la información de un campo, es necesario que vuelvas a llenar la información de todos los campos.)";
			   		break;
				}
				?>
				</p>
		 		<form action="payments.php" method="POST" id="customer-form">
		 		<div class="tag_payment">
    		 		<div class="dashboard_tag_block">
        		 		    <label>Nombre del usuario de tarjeta</label>
               				<input name="name" type="text" data-epayco="card[name]" value="<?php
        						if ($reg) {
        							echo $txt_rep->entities ($fin_info_arr['name']);
        						}
        						?>" placeholder="Ej: Daniel Perez" required>
        					<?php 
        					if (in_array("name", $invalid_arr)){
        						echo "<p id='wrong_input'>Tu nombre solo puede contener letras, puntos y guion, sin acentos.<br></p>";
        					}
        					?>
        			</div>
        			<div class="dashboard_tag_block">		
        		 		    <label>Cédula de Ciudadanía</label>
               				<input name="cedula" type="text" value="<?php
        						if ($reg) {
        							echo "************".$txt_rep->entities (substr($fin_info_arr['docu_number'],-4));
        						}
        						?>" placeholder="Ej: 1015444337" required>
        					<?php 
        					if (in_array("cedula", $invalid_arr)){
        						echo "<p id='wrong_input'>Tu cédula solo puede contener números.<br></p>";
        					}
        					?>
        			</div>		
        			<div class="dashboard_tag_block">		
        		 		    <label>Teléfono (entre 7 y 10 dígitos)</label>
               				<input name="phone" type="text" data-epayco="card[phone]" value="<?php
        						if ($reg) {
        							echo $txt_rep->entities ($fin_info_arr['phone']);
        						}
        						?>" placeholder="Ej: 3105558855" required>
        					<?php 
        					if (in_array("phone", $invalid_arr)){
        						echo "<p id='wrong_input'>Tu teléfono solo puede contener números y tener entre 7 y 10.<br></p>";
        					}
        					?>
        <!-- 					<label>Telefono</label> -->
        <!--        				<input name="phone" type="text" data-epayco="card[phone]"> -->
        			</div>
    			
        			<div class="dashboard_tag_block">
        			        <label>Número de tarjeta de crédito</label>
        			        <input name="card_number" type="text" data-epayco="card[number]" value="<?php
        						if ($reg) {
        							echo "************".$txt_rep->entities ($fin_info_arr['cc_4_dig']);
        						}
        						?>" placeholder="Ej: 4151611527583282" required>
        					<?php 
        					if (in_array("card_number", $invalid_arr)){
        						echo "<p id='wrong_input'>Tu número de tarjeta solo puede contener números.<br></p>";
        					}
        					?>
        			</div>	
    			</div>
    			<div class="tag_payment">		
    				<div class="dashboard_tag_block">
        			        <label>CVC</label>
        			        <input name="cvc" type="text" size="4" data-epayco="card[cvc]" placeholder="Ej: 123" required>
        					<?php 
        					if (in_array("cvc", $invalid_arr)){
        						echo "<p id='wrong_input'>Tu CVC solo puede contener números.<br></p>";
        					}
        					?>
        			</div>
        			<div class="dashboard_tag_block">		
        			        <label>Mes de expiración(MM)</label>
        			        <input name="exp_month" type="text" size="2" data-epayco="card[exp_month]" placeholder="Ej: 05" required>
        					<?php 
        					if (in_array("month", $invalid_arr)){
        						echo "<p id='wrong_input'>El número del mes de expiración solo puede contener números y debe ser de 2 characteres, por ejemplo Mayo es 05.<br></p>";
        					}
        					?>
        			 </div>  
        			 <div class="dashboard_tag_block">     
        			        <label>Año de expiración(AAAA)</label>
        			        <input name="exp_year" type="text" size="4"  data-epayco="card[exp_year]" placeholder="Ej: 2018" required>
        					<?php 
        					if (in_array("year", $invalid_arr)){
        						echo "<p id='wrong_input'>El año de expiración solo puede contener números y debe ser de 4 characteres, por ejemplo 2019.<br></p>";
        					}
        					?>
    				</div>
    			</div>	
    			<div class="center_3_button_navigation">	
    			        <input type="submit" name="add_credicard" value="Guardar" id="save_data_stats_butt">
    			 </div>       
				</form>
			</div>
			
		<div role="tabpanel" class="tab-pane fade" id="receiving_div">
			<h1>
			<?php switch($lang){
		    case("en"):
		    		echo "Receiving Payments Information";     
		        break;
		    case("es"):
		    		echo "Información de Recibimiento de Pagos";
		   		break;
			}?>
			</h1>
			<p>
			<?php switch($lang){
		    case("en"):
		    	echo "This information is required to receive payments from ConfiDr. For this, you must have a Payoneer account, it's free! <br>
						Please insert your email in which you are already registered in Payoneer, or want to be registered in Payoneer. If you do not have an account we will send you the next steps to this email.";     
		        break;
		    case("es"):
		    		echo "Esta información es requerida para poder recibir pagos de ConfiDr. Para esto, debes tener una cuenta en Payoneer, ¡es gratis! <br>
						A continuación ingresa un email con el que ya tienes cuenta, o quisieras abrir tu cuenta en Payoneer, nosotros te enviaremos allí los siguientes pasos a seguir.";
		   		break;
			}
			?>
			</p>
				<form action="payments.php" method="POST" id="customer-form">
				<div class="tag_payment">		
    				<div class="dashboard_tag_block">
    					<label>Payoneer Email</label>
    			        <input name="pay_email" type="text" placeholder="Ej: user@domain.com" value="<?php
    					if ($reg) {
    						echo $txt_rep->entities ($fin_info_arr['rec_pay_email']);
    					}
    					?>" required>
    					<?php 
    					if (in_array("pay_email", $invalid_pay_arr)){
    						echo "<p id='wrong_input'>El email suministrado no es válido.<br></p>";
    					}
    					?>
    				</div>	
				</div>	
			<div class="center_3_button_navigation">	
			        <input type="submit" name="add_pay_email" value="Guardar" id="save_data_stats_butt">
			 </div>       		
			      
				</form>
			</div>
		</div>	
	</div>	
</div>
<script>

$(document).ready(function(){

    $('.grey_banner').fadeTo(500,0.8);
    $('.title_text').delay(500).animate({ paddingTop: 98}, 1000);
    $('.wrapper').delay(500).animate({ marginTop: 166}, 1000);

});
	//control navtabs when the window is resized	
	var elementPosition = $('#profileTabs').offset();
	
    
	$(window).scroll(function(){
			
			
	        if($(window).scrollTop() > (elementPosition.top-125) ){
	              	$('#profileTabs').css('position','absolute').css('top','43px'). css('width', 'calc(100%)').css('min-width',' 705px');
	              	$('.top_bar').css( 'background-color' ,'rgb(64, 64, 64)');
	              	$('.title_tabs').css( 'margin-top' ,'45px');
	              	var marginL =  $(window).scrollTop()-122 ;
	              	$('#profileTabs').css('top', marginL+'px' );
	              	
	              
	              	
	        } else {
	            	$('#profileTabs').css('position','static') . css('width', '100%');
            		$('.top_bar').css( 'background-color' ,'rgba(64, 64, 64,0.8)');
            		$('.title_tabs').css( 'margin-top' ,'0px');
            		
            		 
	        }   
	});	
</script> 