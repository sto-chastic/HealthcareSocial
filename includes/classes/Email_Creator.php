<?php
class Email_Creator{
	
	public function __construct(){
	}
	
	public function confirmation_email($title, $greet,
			$confirmation_message, $button, $url, $confirmation_message_2){
		
		$str = <<<EOS
			<!DOCTYPE html>
			<html>
			<head>
				<meta name="viewport" content="width=device-width" />
			    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			    <title>{$title}</title>
				
				<style>
			    @font-face{
			        font-family: 'Coves';
			        src: url('http://www.confidr.com/assets/font/Coves-Bold.otf');
			        font-weight: bold;
			        font-style: normal;
			      }
				</style>
			</head>
			<body style= "background-color:#eee;">
				<div class="wrapper">
					<div class="top_register" style="background-color:rgb(70,70,70);">
						<div class="logo_register" >
							<a href="http://www.confidr.com">
								<img src="http://www.confidr.com/assets/images/icons/logo2.png" style=" width: 250px;" alt="Logo">
							</a>
						</div>
					</div>
					
					<div style=" height: 500px; background: url('http://www.confidr.com/assets/images/backgrounds/desktop.jpg'); background-position: top center; background-size:auto 100%; background-repeat: repeat-y ; position: relative;">
			             <div style=" background-color: rgba(66, 37, 47, 0.75); position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
			             	<h1 style=" margin: 15px 15%; font-family: 'Coves'; color: rgb(155, 210, 245); text-align: center;">
			             		{$greet}
			             	</h1>
			             		
			             	<p style=" margin: 40px 15%; color: rgb(255, 255, 255); font-family: 'Coves'; text-align: center; font-size: 13.5px;">
			             		{$confirmation_message}
			             	</p>
			             	<a href={$url} style=" display: inline-block; position: relative; left: calc(50% - 150px); text-decoration: none;">
			             		<div style=" background-color: #f95c8b; border: 1px solid #f95c8b; height: 40px; width: 300px; margin: 0; border-radius: 2px; color: #fff; font-family: 'Coves'; font-size: 20px; position: relative; margin-top: 2vh; text-align: center; left: calc(50% - 150px); line-height: 40px;">
			             			{$button}
			             		</div>
			             	</a>
							<p style=" margin: 40px 15%; color: rgb(255, 255, 255); font-family: 'Coves'; text-align: center; font-size: 12.5px;">
			             		{$confirmation_message_2}
			             	</p>
			             </div>
					</div>
				</div>
				<p style=" margin: 40px 15%; color: rgba(192, 192, 192, 1); font-family: 'Coves'; text-align: center;">
					ConfiDr. Desarrollado por Neuronaptic S.A.S. <br>
					TEL:+(57)312 412 57 15 | support@confidr.com <br>
					Bogotá, Colombia. 
				</p>
			</body>
			</html>
EOS;
		
		
		return $str;
	}
	
	
	public function doc_appointment_email_2_buttons($title, $greet,
			$confirmation_message, $button, $url, $confirmation_message_2,
			$button_2, $url_2){
				
				$str = <<<EOS
			<!DOCTYPE html>
			<html>
			<head>
				<meta name="viewport" content="width=device-width" />
			    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			    <title>{$title}</title>
			    
				<style>
			    @font-face{
			        font-family: 'Coves';
			        src: url('http://www.confidr.com/assets/font/Coves-Bold.otf');
			        font-weight: bold;
			        font-style: normal;
			      }
				</style>
			</head>
			<body style= "background-color:#eee;">
				<div class="wrapper">
					<div class="top_register" style="background-color:rgb(70,70,70);">
						<div class="logo_register" >
							<a href="http://www.confidr.com">
								<img src="http://www.confidr.com/assets/images/icons/logo2.png" style=" width: 250px;" alt="Logo">
							</a>
						</div>
					</div>
					
					<div style=" height: 700px; background: url('http://www.confidr.com/assets/images/backgrounds/desktop.jpg'); background-position: top center; background-size:auto 100%; background-repeat: repeat-y ; position: relative;">
			             <div style=" background-color: rgba(66, 37, 47, 0.75); position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
			             	<h1 style=" margin: 15px 15%; font-family: 'Coves'; color: rgb(155, 210, 245); text-align: center;">
			             		{$greet}
			             	</h1>
			             	
			             	<p style=" margin: 40px 15%; color: rgb(255, 255, 255); font-family: 'Coves'; text-align: center; font-size: 13.5px;">
			             		{$confirmation_message}
			             	</p>
			             	<a href={$url} style=" display: inline-block; position: relative; left: calc(50% - 150px); text-decoration: none;">
			             		<div style=" background-color: #f95c8b; border: 1px solid #f95c8b; height: 40px; width: 300px; margin: 0; border-radius: 2px; color: #fff; font-family: 'Coves'; font-size: 20px; position: relative; margin-top: 2vh; text-align: center; left: calc(50% - 150px); line-height: 40px;">
			             			{$button}
			             		</div>
			             	</a>
							
			             	<p style=" margin: 40px 15%; color: rgb(255, 255, 255); font-family: 'Coves'; text-align: center; font-size: 12.5px;">
			             		{$confirmation_message_2}
			             	</p>
			             	<a href={$url_2} style=" display: inline-block; position: relative; left: calc(50% - 150px); text-decoration: none;">
			             		<div style=" background-color: #f95c8b; border: 1px solid #f95c8b; height: 40px; width: 300px; margin: 0; border-radius: 2px; color: #fff; font-family: 'Coves'; font-size: 20px; position: relative; margin-top: 2vh; text-align: center; left: calc(50% - 150px); line-height: 40px;">
			             			{$button_2}
			             		</div>
			             	</a>
			             </div>
					</div>
				</div>
				<p style=" margin: 40px 15%; color: rgba(192, 192, 192, 1); font-family: 'Coves'; text-align: center;">
					ConfiDr. Desarrollado por Neuronaptic S.A.S. <br>
					TEL:+(57)312 412 57 15 | support@confidr.com <br>
					Bogotá, Colombia.
				</p>
			</body>
			</html>
EOS;
			             		
			             		
			             		return $str;
	}
	
	
	public function confirmation_email_2_buttons($title, $greet,
			$confirmation_message, $button, $url, $confirmation_message_2,
			$button_2, $url_2, $confirmation_message_3){
				
				$str = <<<EOS
			<!DOCTYPE html>
			<html>
			<head>
				<meta name="viewport" content="width=device-width" />
			    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			    <title>{$title}</title>
			    
				<style>
			    @font-face{
			        font-family: 'Coves';
			        src: url('http://www.confidr.com/assets/font/Coves-Bold.otf');
			        font-weight: bold;
			        font-style: normal;
			      }
				</style>
			</head>
			<body style= "background-color:#eee;">
				<div class="wrapper">
					<div class="top_register" style="background-color:rgb(70,70,70);">
						<div class="logo_register" >
							<a href="http://www.confidr.com">
								<img src="http://www.confidr.com/assets/images/icons/logo2.png" style=" width: 250px;" alt="Logo">
							</a>
						</div>
					</div>
					
					<div style=" height: 700px; background: url('http://www.confidr.com/assets/images/backgrounds/desktop.jpg'); background-position: top center; background-size:auto 100%; background-repeat: repeat-y ; position: relative;">
			             <div style=" background-color: rgba(66, 37, 47, 0.75); position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
			             	<h1 style=" margin: 15px 15%; font-family: 'Coves'; color: rgb(155, 210, 245); text-align: center;">
			             		{$greet}
			             	</h1>
			             	
			             	<p style=" margin: 40px 15%; color: rgb(255, 255, 255); font-family: 'Coves'; text-align: center; font-size: 13.5px;">
			             		{$confirmation_message}
			             	</p>
			             	<a href={$url} style=" display: inline-block; position: relative; left: calc(50% - 150px); text-decoration: none;">
			             		<div style=" background-color: #f95c8b; border: 1px solid #f95c8b; height: 40px; width: 300px; margin: 0; border-radius: 2px; color: #fff; font-family: 'Coves'; font-size: 20px; position: relative; margin-top: 2vh; text-align: center; left: calc(50% - 150px); line-height: 40px;">
			             			{$button}
			             		</div>
			             	</a>
			             	
			             	<p style=" margin: 40px 15%; color: rgb(255, 255, 255); font-family: 'Coves'; text-align: center; font-size: 13.5px;">
			             		{$confirmation_message_2}
			             	</p>
			             	<a href={$url_2} style=" display: inline-block; position: relative; left: calc(50% - 150px); text-decoration: none;">
			             		<div style=" background-color: #f95c8b; border: 1px solid #f95c8b; height: 40px; width: 300px; margin: 0; border-radius: 2px; color: #fff; font-family: 'Coves'; font-size: 20px; position: relative; margin-top: 2vh; text-align: center; left: calc(50% - 150px); line-height: 40px;">
			             			{$button_2}
			             		</div>
			             	</a>
							<p style=" margin: 40px 15%; color: rgb(255, 255, 255); font-family: 'Coves'; text-align: center; font-size: 12.5px;">
			             		{$confirmation_message_3}
			             	</p>
			             </div>
					</div>
				</div>
				<p style=" margin: 40px 15%; color: rgba(192, 192, 192, 1); font-family: 'Coves'; text-align: center;">
					ConfiDr. Desarrollado por Neuronaptic S.A.S. <br>
					TEL:+(57)312 412 57 15 | support@confidr.com <br>
					Bogotá, Colombia.
				</p>
			</body>
			</html>
EOS;
			             		
			             		
			             		return $str;
	}
}
?>