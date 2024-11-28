<?php 
	include('includes/header.php');
 ?>

 <div class="main_column column" id="main_column">
 	<div id="not_found">
 	<?php 
 	if (isset($_SESSION['lang']))
        $lang = $_SESSION['lang'];
 	else
 	    $lang = "es";
    
    switch($lang) {
        case("en"):
            echo "
            <h4>User not found.</h4>
 	
 	        <p>This user is not registered or might have closed his account.<p/>
        
         	<a href='index.php'>Click here to go back.</a>
                ";
            break;
        case("es"):
            echo "
            <h4>Usuario no encontrado.</h4>
         	    
 	        <p>Este usuario no está registrado o pudo haber cerrado su cuenta.<p/>
         	    
         	<a href='index.php'>Haga click aquí para regresar.</a>
                ";
            break;
 	}
    ?>
 	
 	<div>

 </div>