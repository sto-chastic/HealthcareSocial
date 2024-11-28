<?php
include_once("Crypt.php");
include_once("User.php");
class Settings{
	
	private $settings = [];
	private $settings_table;
	private $con;
	
	public function __construct($con,$username, $username_e){
	    $crypt = new Crypt();
		$this->con = $con;
		//$filtered_username = preg_replace("/[^A-Za-z0-9 ]/", '', $username);
		$stmt = $con->prepare("SELECT * FROM users WHERE username=?");
		
		$stmt->bind_param("s", $username_e);
		$stmt->execute();
		$user_query = $stmt->get_result();
		
		//Get basic details
		$user_info = mysqli_fetch_array($user_query);

		$nnpswd = "<-></\|eUro#/\|apt!C-2017!4!2394%nnY";
		$username = $crypt->Decrypt($user_info['username']);
		
		$user = new User($con,$username, $username_e);
		$settings_table= $user->getSettingsTab();
		$this->settings_table = $user->getSettingsTab();
		//echo $settings_table;
		//$hash = md5(hash_pbkdf2("sha256", $user_info['password']. $nnpswd . $username, $user_info['signup_date'], 20000));
		//AVOID INJECTION, make username CAN BE TRUSTED
		//$filtered_username = preg_replace("/[^A-Za-z0-9 ]/", '', $username);
		//$this->settings_table = $settings_table = $hash. "__settings";
		
		$stmt = $con->prepare("SELECT * FROM $settings_table");
		$stmt->execute();
		
		$query = $stmt->get_result();
		
		$this->settings = mysqli_fetch_assoc($query);
		//print_r($this->settings);
	}
	
	public function getSettingsTab(){
		return $this->settings_table;
	}
	
	public function getLang(){
		$value = $this->settings["lang"];
		return $value;
	}
	
	public function setLang($lang){
		$this->setSettingsValues_strings("lang", $lang);
	}
	
	public function getSettingsValues($setting_name){
		//print_r($this->settings);
		$value = $this->settings[$setting_name];
		return $value;
	}
	
	public function setSettingsValues_integers($setting_name_t,$value){
		$table = $this->getSettingsTab();
		switch($setting_name_t){
			case "payed_messages":
				$setting_name = "payed_messages";
				break;
			case "payed_messages_cost":
				$setting_name = "payed_messages_cost";
				break;
			case "profile_privacy":
				$setting_name = "profile_privacy";
				break;
			default:
				return;
		}
		$sql_q = "INSERT INTO " . $table . " (id, " . $setting_name ." ) VALUES(1, ?)
			ON DUPLICATE KEY UPDATE	" . $setting_name . " = ?";
		
		$stmt = $this->con->prepare($sql_q);
		$stmt->bind_param("ii",$value,$value);
		$stmt->execute();
	}
	
	public function setSettingsValues_strings($setting_name_t,$value){
		$table = $this->getSettingsTab();
		switch($setting_name_t){
			case "payed_messages":
				$setting_name = "payed_messages";
				break;
			case "payed_messages_cost":
				$setting_name = "payed_messages_cost";
				break;
			case "lang":
			    $setting_name = "lang";
			    break;
			case "last_login":
				$setting_name = "last_login";
				break;
			default:
				return;
		}
		$sql_q = "INSERT INTO " . $table . " (id, " . $setting_name ." ) VALUES(1, ?)
			ON DUPLICATE KEY UPDATE	" . $setting_name . " = ?";
		
		$stmt = $this->con->prepare($sql_q);
		$stmt->bind_param("ss",$value,$value);
		$stmt->execute();
	}
	
}