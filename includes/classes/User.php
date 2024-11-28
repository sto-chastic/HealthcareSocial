<?php  
include_once('Crypt.php');
class User{
	//private $user;
	public $user_info;
	private $con;
	public $username;
	public $username_e;
	private $crypt;
	
	private $health_tables = [];
	private $basic_info_tables= [];
	private $basic_info_patient=[];
	private $upToDate = -1;
	private $isDoctor = -1;
	private $hash;
	private $uhash;
	private $thash;
	
	public function __construct($con, $temp_user, $temp_user_e){ //Should only get array with username, and use that in future queries.
	    $this->crypt = new Crypt();
	    
	    $this->con = $con;
		$stmt = $con->prepare("SELECT * FROM users WHERE username=?");
		$stmt->bind_param("s", $temp_user_e);
		$stmt->execute();
		$user_query = $stmt->get_result();

		if(mysqli_num_rows($user_query) == 1){
			//Get basic details
			$this->user_info = mysqli_fetch_array($user_query);
			$this->user_info['username_e'] = $this->user_info['username'];
			$this->user_info['username'] = $this->crypt->Decrypt($this->user_info['username']);
			
			if($this->user_info['username'] != $temp_user){
				throw new Exception( 'Invalid Username' );
				die;
			}
			
 			$this->username = $temp_user;
 			$this->username_e = $temp_user_e;
 			$nnpswd = "<-></\|eUro#/\|apt!C-2017!4!2394%nnY";
 			$this->hash = $hash = hash_pbkdf2("sha256", $nnpswd . $temp_user, $this->user_info['signup_date'], 20000 , 32 , FALSE);
 			
 			$salt = "som/|/euR@N@p7iC84Ltm(8)";
 			$this->uhash = hash_pbkdf2("sha256", $nnpswd .$salt. $hash, $salt, 20000 , 32 , FALSE);
 			
 			$salt = "jgsad87KJH23JHG235KdBJHh9786723876AFfss";
 			$this->thash = hash_pbkdf2("sha256", $nnpswd .$salt. $hash, $salt, 20000 , 32 , FALSE);
 			
		}
		else{
			$this->user = "";
			$this->user_info = "";
			$this->con = "";
			throw new Exception( 'Invalid Username' );
		}
		
	}
	
	public function loadHealthTables(){
		//Get health tables
		$temp_user = $this->username;
		
		if(empty($this->health_tables)){
			$stmt = $this->con->prepare("SELECT * FROM users_health_tables WHERE username=?");
			$stmt->bind_param("s", $temp_user);
			$stmt->execute();
			$tables_query = $stmt->get_result();
			$this->health_tables = mysqli_fetch_array($tables_query);
		}
		return $this->health_tables;
	}
	
	public function reLoadHealthTables(){
		//Get health tables
		$temp_user = $this->username;
		
		if(!empty($this->health_tables)){
			$stmt = $this->con->prepare("SELECT * FROM users_health_tables WHERE username=?");
			$stmt->bind_param("s", $temp_user);
			$stmt->execute();
			$tables_query = $stmt->get_result();
			$this->health_tables = mysqli_fetch_array($tables_query);
		}
		return $this->health_tables;
	}
	
	public function loadBasicInfo(){
		//Get basic info patients
		$temp_user = $this->username;
		if(empty($this->basic_info_patient)){
			
			$stmt = $this->con->prepare("SELECT * FROM basic_info_patients WHERE username=?");
			$stmt->bind_param("s", $temp_user);
			$stmt->execute();
			$user_tables_query = $stmt->get_result();
			$_arr = mysqli_fetch_array($user_tables_query);
			$this->basic_info_patient = $_arr;
		}
		return $this->basic_info_patient;
	}
	
	public function getInsuranceCompany_Patient(){
		//The patient retrieves his insurance company. NOT for doctors
		$_basic_info_patients = $this->loadBasicInfo();
		return $_basic_info_patients['insurance'];
	}
	
	public function isUpToDate(){
	    $temp_user = $this->username;
	    if($this->upToDate == -1){
	        $stmt = $this->con->prepare("SELECT up_to_date FROM basic_info_doctors WHERE username=?");
	        $stmt->bind_param("s", $temp_user);
	        $stmt->execute();
	        $q = $stmt->get_result();
	        $_arr = mysqli_fetch_assoc($q);
	        $res = $_arr['up_to_date'];
	        $this->upToDate = $res;
            return $res;
	    }
	    else{
	        return $this->upToDate;
	    }
	}
	
	public function getAvailableOfficesByInsurance($insurance){
		//Retrives an array with the available offices based on a given insurance
		
		$doc_user = $this->username;
		
		$stmt = $this->con->prepare("SELECT insurance_accepted_1,insurance_accepted_2,insurance_accepted_3 FROM basic_info_doctors WHERE username=?");
		$stmt->bind_param("s", $doc_user);
		$stmt->execute();
		$offices_query = $stmt->get_result();
		$_arr = mysqli_fetch_array($offices_query);
		
		$offices_array = [];
		for($i=1;$i<=3;$i++){
			$_temp_ind = "insurance_accepted_" . $i;
			if( $_arr[$_temp_ind] == ""){
				continue;
			}
			$_temp_insurance_array = unserialize($_arr[$_temp_ind]);
			if(in_array($insurance, $_temp_insurance_array)){
				$offices_array[] = $i;
			}
		}
		
		return $offices_array;
	}
	
	public function getInsurancesTable(){
		return "insurance_" . $this->getCountry();
	}

	public function getFirstAndLastName(){
		/*$username = $this->user['username'];
		$query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE username='$username'");
		$arrayFL = mysqli_fetch_array($query);
		return $arrayFL['first_name'] . " " . $arrayFL['last_name'];*/
		$txt_rep = new TxtReplace();
		return $txt_rep->entities($this->user_info['first_name']) . " " . $txt_rep->entities($this->user_info['last_name']);
	}

	public function getFirstAndLastNameFast(){
		$txt_rep = new TxtReplace();
		return $txt_rep->entities($this->user_info['first_name']) . " " . $txt_rep->entities($this->user_info['last_name']);
	}
	
	public function getCountry_Doctor(){
		$stmt = $this->con->prepare("SELECT adcountry FROM basic_info_doctors WHERE username = ?");
		$stmt->bind_param("s", $this->username);
		$stmt->execute();
		$q_r = $stmt->get_result();
		$arr = mysqli_fetch_assoc($q_r);
		
		$country =  $arr['adcountry'];
		return $country;
	}

	public function getFirstName(){
		$txt_rep = new TxtReplace();
		return $txt_rep->entities($this->user_info['first_name']);
	}
	
	public function getEmail(){
		$txt_rep = new TxtReplace();
		return $txt_rep->entities($this->user_info['email']);
	}
	
	public function getFirstNameShort($length){
		$first_name = $this->getFirstName();
		if(strlen($first_name) >= $length)
			$show_first= substr($first_name, 0, $length) . "...";
		else
			$show_first = $first_name;
		return $show_first;
	}
	
	public function getFirstAndLastNameShort($length){
		$first_name = $this->getFirstName();
		$last_name = $this->getLastName();
		if(strlen($first_name) + strlen($last_name) + 1 >= $length){
			$total_l = strlen($first_name . " " . $last_name);
			$cut = ($total_l < $length - 1)? $total_l : $length - 1;
			$show_first= substr($first_name . " " . $last_name, 0, $length - 1) . "...";
		}
		else
			$show_first = $first_name . " " . $last_name;
		return $show_first;
	}
	
	public function getLastName(){
		$txt_rep = new TxtReplace();
		return $txt_rep->entities($this->user_info['last_name']);
	}
	
	public function getSpecializationsText($lang){
		$username = $this->username;
		$stmt = $this->con->prepare("SELECT specializations FROM basic_info_doctors WHERE username=?");
		$stmt->bind_param("s",$username);
		$stmt->execute();
		$res_q = $stmt->get_result();
		$res_arr = mysqli_fetch_assoc($res_q);
		$code_specialization = $res_arr['specializations'];
		
		$unser_code_specialization = unserialize($code_specialization);
		
		$stmt = $this->con->prepare("SELECT $lang FROM specializations WHERE id=?");
		$stmt->bind_param("s",$unser_code_specialization[0]);
		$stmt->execute();
		$res_q = $stmt->get_result();
		$res_arr = mysqli_fetch_assoc($res_q);
		
		$spec = $res_arr[$lang];
		if(strpos($spec, '\\') > 0){
			$specialization= substr($spec, 0, strpos($spec, '\\'));
		}
		else{
			$specialization = $spec;
		}
		return $specialization;
	}
	
	public function getSpecializationsCode($lang){
		$username = $this->username;
		$stmt = $this->con->prepare("SELECT specializations FROM basic_info_doctors WHERE username=?");
		$stmt->bind_param("s",$username);
		$stmt->execute();
		$res_q = $stmt->get_result();
		$res_arr = mysqli_fetch_assoc($res_q);
		$code_specialization = $res_arr['specializations'];
		
		return $code_specialization;
	}
	
	public function getSpecializationsTextShort($lang,$length){
		$spec_long = $this->getSpecializationsText($lang);
		if(strlen($spec_long) >= $length)
			$show_last = substr($spec_long, 0, $length) . "...";
		else
			$show_last = $spec_long;
		return $show_last;
	}
	
	public function getLastNameShort($length){
		$last_name = $this->getLastName();
		if(strlen($last_name) >= $length)
			$show_last = substr($last_name, 0, $length) . "...";
		else
			$show_last = $last_name;
		return $show_last;
	}

	public function getProfilePicFast(){
		$u_e = $this->username_e;
		$username_e_e = $this->crypt->Encrypt($u_e,"--jasd>/|/e#rNImg674Cod3)%ap28?iC_!");
		$coded = bin2hex($username_e_e);
		
		return "gtimg.php?img=" . $coded;
	}
	
	public function getProfilePicPATH(){
		//Only for retreiving the path, it will NOT work as a link
		$txt_rep = new TxtReplace();
		return $txt_rep->entities($this->user_info['profile_pic']);
	}
	
	public function getUserLikeFast(){
		return $this->user_info['num_likes'];
	}

	public function getUsername(){
		return $this->user_info['username'];
	}
	
	public function getUsernameE(){
	    return $this->user_info['username_e'];
	}

	public function getNumLikes(){
		return $this->user_info['num_likes'];
	}
	
	public function getUserType(){
		return $this->user_info['user_type'];
	}
	
	public function getCountry(){
		$txt_rep = new TxtReplace();
		return $txt_rep->entities($this->user_info['country']);
	}

	public function getConnectionsTable(){
		return $this->hash . "__connections";//
	}
	
	public function getPhoneTable(){
		return $this->thash . "__telephones";//
	}

	public function getCommentsTable(){
		return $this->hash . "__comments";//
	}

	public function getRequestsTable(){
		return $this->hash . "__connection_requests";//
	}

	public function getLikesTable(){
		return $this->hash . "__likes";//
	}

	public function getMessagesTable(){
		return $this->hash . "__messages";//
	}

	public function getNotificationsTable(){
		return $this->hash . "__notifications";//
	}

	public function getPostsTable(){
		return $this->hash . "__posts";//
	}

	public function getAppoDurationTable(){
		return $this->hash . "__appo_duration_tab";//
	}

	public function getAvailableCalendar(){
		return $this->hash . "__calendar_availability";//
	}

	public function getAppointmentsCalendar(){
		return $this->hash . "__appointments_calendar_doc";//
	}

	public function getAppointmentsCalendar_Patient(){
		return $this->hash . "__appointments_calendar_patient";//
	}

	public function getAppointmentsDetails_Patient(){
		return $this->hash . "__appointments_details_pat";//
	}

	public function getAppointmentsDetails_Doctor(){
		return $this->hash . "__appointment_details_doc";//
	}
	public function getAppointmentsSymptoms_Doctor(){
		return $this->hash . "__symptoms_doc";//
	}
	public function getAppointmentsSymptoms_Patient(){
		return $this->hash . "__symptoms_pat";//
	}
	public function getAppointmentsMedicines_Doctor(){
		return $this->hash . "__medicines_doc";//
	}
	public function getAppointmentsMedicines_Patient(){
		return $this->hash . "__medicines_pat";//
	}
	public function getExternalPatients_Tab(){
		return $this->hash . "__external_patients";//
	}
	public function getAwardsPatient_Tab(){
		return $this->hash . "__awards_patient";//
	}
	public function getPaymentsTab(){
		return $this->hash . "__payments";//
	}
	public function getPaymentsHistTab(){
		return $this->hash . "__payments_hist";//
	}

	public function getSettingsTab(){
		return $this->hash . "__settings";//
	}
	
	public function getPatientsRecurrent(){
		//only for doctor users
		//DOESNT WORK, needs to be extracted from connections table and connection_type
	}
	
	public function getPatientsSeen(){
		//only for doctor users
		//DOESNT WORK, needs to be extracted from connections table and connection_type
	}
	
	public function getDoctorsSeen(){
		//only for doctor users
		//DOESNT WORK, needs to be extracted from connections table and connection_type
	}
	
	public function getMessagesStatusTable(){
		return $this->hash . "__messages_status";//
	}
	
	public function getDoctorsRIPS_tablename(){
	    //only for doctor users
	    return $this->hash . "__rips";
	}
	
	public function getWeight(){
		return $this->uhash . "__weight";
	}
	public function getHeight(){
		return $this->uhash . "__height";
	}
	public function getBMI(){
		return $this->uhash . "__bmi";
	}
	public function getBloodPressure(){
		return $this->uhash . "__blood_pressure";
	}
	public function getBloodSugar(){
		return $this->uhash . "__blood_sugar";
	}
	public function getSymptoms_DoctorPOV(){
		return $this->hash . "__symptoms_doc";
	}
	
	public function getEducation_tab(){
		return $this->hash . "__education";
	}
	
	public function getJobs_tab(){
		return $this->hash . "__jobs";
	}
	
	public function getConferences_tab(){
		return $this->hash . "__conferences";
	}
	
	public function getDescription_tab(){
		return $this->hash . "__description";
	}
	
	public function getAwards_tab(){
		return $this->hash . "__awards";
	}
	
	public function getPublications_tab(){
		return $this->hash . "__publications";
	}
	
	public function getWebpages_tab(){
		return $this->hash . "__webpages";
	}
	
	public function getDescription(){
		$desc_tab = $this->getDescription_tab();
		$query = mysqli_query($this->con, "SELECT description FROM $desc_tab ORDER BY id LIMIT 1");
		$arr = mysqli_fetch_array($query);
		return $arr['description'];
	}
	
	public function addDescription($desc){
		$desc_tab = $this->getDescription_tab();
		$stmt = $this->con->prepare("INSERT INTO $desc_tab (id,description) VALUES (1,?) ON DUPLICATE KEY UPDATE id=1, description=?");
		$stmt->bind_param("ss",$desc,$desc);
		$stmt->execute();
	}
	
	public function isDoctor(){
	    if($this->isDoctor == -1){
        		$username_e = $this->username_e;
        		$query = mysqli_query($this->con, "SELECT user_type FROM users WHERE username = '$username_e'");
        		$arr = mysqli_fetch_array($query);
        		if($arr['user_type'] == 1){
        			$ret = TRUE;
        		}
        		else{
        			$ret = FALSE;
        		}
        		$this->isDoctor = $ret;
        		return $ret;
	    }
	    else{
	        return $this->isDoctor;
	    }
	}
	
	public function getConnections_tab(){
		return $this->hash . "__connections";
	}
	
	public function getDoctorConnections_array(){
		$connections_tab = $this->getConnections_tab();
		$connections_query = mysqli_query($this->con, "SELECT username_friend FROM $connections_tab WHERE doctor = 1");
		$connection_array = [];
		foreach($connections_query as $key => $val){
			$connection_array[$key] = $val['username_friend'];
		}
		return $connection_array;
	}
	
	public function getDoctorConnections_num(){
		$connections_tab = $this->getConnections_tab();
		$connections_query = mysqli_query($this->con, "SELECT username_friend FROM $connections_tab WHERE doctor = 1");
		
		return mysqli_num_rows($connections_query);
	}

	public function getPatientConnections_array(){
		//doctors only
		$connections_tab = $this->getConnections_tab();
		$connections_query = mysqli_query($this->con, "SELECT username_friend FROM $connections_tab WHERE doctor = 0");
		$connection_array = [];
		foreach($connections_query as $key => $val){
			$connection_array[$key] = $val['username_friend'];
		}
		return $connection_array;
	}
	
	public function getPatientConnections_num(){
		//doctors only
		$connections_tab = $this->getConnections_tab();
		$connections_query = mysqli_query($this->con, "SELECT username_friend FROM $connections_tab WHERE doctor = 0");
		
		return mysqli_num_rows($connections_query);
	}

	public function getFriends(){
		//deprecated
		$friends_table = $this->getConnections_tab();
		$friends_query = mysqli_query($this->con, "SELECT username_friend FROM $friends_table");
		$friend_array = [];
		while($arr = mysqli_fetch_array($friends_query)){
			$friend_array[] = $arr;
		}
		return $friend_array;
	}

	public function getNumFriends(){
		$friends_table = $this->getConnections_tab();
		$friends_query = mysqli_query($this->con, "SELECT username_friend FROM $friends_table");
		return mysqli_num_rows($friends_query);
	}

	public function getNumFriendRequests(){
		$username = $this->username;
		$connection_requests = $this->getRequestsTable();
		$query = mysqli_query($this->con, "SELECT * FROM $connection_requests WHERE user_to='$username'");
		return mysqli_num_rows($query);
	}

	public function getNumPosts(){
		$username_e = $this->username_e;
		$query = mysqli_query($this->con, "SELECT num_posts FROM users WHERE username='$username_e'");
		$arrayPosts = mysqli_fetch_array($query);
		return $arrayPosts['num_posts'];
	}

	public function isClosed(){
		$username_e = $this->username_e;
		$query = mysqli_query($this->con,"SELECT user_closed FROM users WHERE username='$username_e'");
		$arr = mysqli_fetch_array($query);
		if($arr['user_closed'] == 'yes'){
			return true;
		}
		else{
			return false;
		}
	} //TODO: could be made more efficient.

	public function isFriend($username_to_check){
		$friends_table = $this->getConnectionsTable();
		$friends_query = mysqli_query($this->con, "SELECT * FROM $friends_table WHERE username_friend='$username_to_check'");
		if(mysqli_num_rows($friends_query) > 0)
			return true;
		else
			return false;
	}

	public function didSendRequest($user_to){
		$user_from = $this->username;
		$user_to_e = $this->crypt->EncryptU($user_to);
		$user_to_obj = new User($this->con, $user_to, $user_to_e);
		$connection_requests = $user_to_obj->getRequestsTable();
		
		$stmt = $this->con->prepare("SELECT * FROM $connection_requests WHERE user_to=? AND user_from = ?");
		$stmt->bind_param("ss",$user_to,$user_from);
		$stmt->execute();
		
		$check_request_query = $stmt->get_result();
		
		//$check_request_query = mysqli_query($this->con, "SELECT * FROM $connection_requests WHERE user_to='$user_to' AND user_from = '$user_from'");
		if(mysqli_num_rows($check_request_query) > 0)
			return true;
		else 
			return false;
	}

	public function didReceiveRequest($user_from){
		$user_to = $this->username;
		$connection_requests = $this->getRequestsTable();
		$check_request_query = mysqli_query($this->con, "SELECT * FROM $connection_requests WHERE user_to='$user_to' AND user_from = '$user_from'");
		if(mysqli_num_rows($check_request_query) > 0){
			return true;
		}
		else{ return false;}
	}

	public function addConnection($user_to_add){
		
		$username = $this->username;
		$username_e = $this->username_e;
		$user_to_add_e = $this->crypt->EncryptU($user_to_add);
		$connection_obj = new User($this->con, $user_to_add, $user_to_add_e);
		$connection_username = $connection_obj->username;
		
		$my_connections_table = $this->getConnections_tab();
		
		$connection_is_doctor = ($connection_obj->isDoctor())?1:0;
		$im_doctor = ($this->isDoctor())?1:0;		
		
		$query = mysqli_query($this->con, "INSERT INTO $my_connections_table VALUES('', '$connection_username', $connection_is_doctor)");//THIS CURRENT USER's TABLE

		$connection_connections_table = $connection_obj->getConnections_tab();
		$query = mysqli_query($this->con, "INSERT INTO $connection_connections_table VALUES('', '$username',$im_doctor)");

		//DELETE request
		$connection_requests = $this->getRequestsTable();
		$delete_query = mysqli_query($this->con, "DELETE FROM $connection_requests WHERE user_to='$username' AND user_from = '$connection_username'");

		//UPDATE posts

		$post_obj = new Post($this->con, $username, $username_e);
		$post_obj->updatePosts($user_to_add);
		
		//update his 
		$con = $this->con;
		
		$md_conn_q = mysqli_query($con, "SELECT sum(`doctor`) AS doctor_sum FROM $connection_connections_table");
		$md_conn_arr = mysqli_fetch_assoc($md_conn_q);
		$md_conn_num = $md_conn_arr['doctor_sum'];
		
		if($im_doctor){
			$stmt = $con->prepare("UPDATE basic_info_doctors SET md_conn = ? WHERE username = ?");
			$stmt->bind_param("is",$md_conn_num,$connection_username);
			$stmt->execute();
		}else{
			$pat_conn_q = mysqli_query($con, "SELECT `username_friend` FROM $connection_connections_table");
			$total_conn_num = mysqli_num_rows($pat_conn_q);
			$pat_conn_num = $total_conn_num - $md_conn_num;
			
			$stmt = $con->prepare("UPDATE basic_info_doctors SET pat_conn = ? WHERE username = ?");
			$stmt->bind_param("is",$pat_conn_num,$connection_username);
			$stmt->execute();
		}
		
		//update mine
		
		$md_conn_q = mysqli_query($con, "SELECT sum(`doctor`) AS doctor_sum FROM $my_connections_table");
		$md_conn_arr = mysqli_fetch_assoc($md_conn_q);
		$md_conn_num = $md_conn_arr['doctor_sum'];
		
		if($connection_is_doctor){
			$stmt = $con->prepare("UPDATE basic_info_doctors SET md_conn = ? WHERE username = ?");
			$stmt->bind_param("is",$md_conn_num,$username);
			$stmt->execute();
		}else{
			$pat_conn_q = mysqli_query($con, "SELECT `username_friend` FROM $my_connections_table");
			$total_conn_num = mysqli_num_rows($pat_conn_q);
			$pat_conn_num = $total_conn_num - $md_conn_num;
			
			$stmt = $con->prepare("UPDATE basic_info_doctors SET pat_conn = ? WHERE username = ?");
			$stmt->bind_param("is",$pat_conn_num,$username);
			$stmt->execute();
		}
	}

	public function removeFriend($user_to_remove){
		$username = $this->username;
		$username_e = $this->username_e;
		$user_to_remove_e = $this->crypt->EncryptU($user_to_remove);
		$connection_obj= new User($this->con, $user_to_remove, $user_to_remove_e);

		$my_connections_table= $this->getConnections_tab();
		$query = mysqli_query($this->con, "DELETE FROM $my_connections_table WHERE username_friend='$user_to_remove'");

		$connection_connections_table= $connection_obj->getConnectionsTable();
		$query = mysqli_query($this->con, "DELETE FROM $connection_connections_table WHERE username_friend='$username'");

		//remove posts and comments

		$post_obj = new Post($this->con, $username, $username_e);
		$psst = $post_obj->deletePosts($user_to_remove);
		
		
		$connection_is_doctor = ($connection_obj->isDoctor())?1:0;
		$im_doctor = ($this->isDoctor())?1:0;	
		
		//update his
		$con = $this->con;
		
		$md_conn_q = mysqli_query($con, "SELECT sum(`doctor`) AS doctor_sum FROM $connection_connections_table");
		$md_conn_arr = mysqli_fetch_assoc($md_conn_q);
		$md_conn_num = $md_conn_arr['doctor_sum'];
		
		if($im_doctor){
			$stmt = $con->prepare("UPDATE basic_info_doctors SET md_conn = ? WHERE username = ?");
			$stmt->bind_param("is",$md_conn_num,$user_to_remove);
			$stmt->execute();
		}else{
			$pat_conn_q = mysqli_query($con, "SELECT `username_friend` FROM $connection_connections_table");
			$total_conn_num = mysqli_num_rows($pat_conn_q);
			$pat_conn_num = $total_conn_num - $md_conn_num;
			
			$stmt = $con->prepare("UPDATE basic_info_doctors SET pat_conn = ? WHERE username = ?");
			$stmt->bind_param("is",$pat_conn_num,$user_to_remove);
			$stmt->execute();
		}
		
		//update mine
		
		$md_conn_q = mysqli_query($con, "SELECT sum(`doctor`) AS doctor_sum FROM $my_connections_table");
		$md_conn_arr = mysqli_fetch_assoc($md_conn_q);
		$md_conn_num = $md_conn_arr['doctor_sum'];
		
		if($connection_is_doctor){
			$stmt = $con->prepare("UPDATE basic_info_doctors SET md_conn = ? WHERE username = ?");
			$stmt->bind_param("is",$md_conn_num,$username);
			$stmt->execute();
		}else{
			$pat_conn_q = mysqli_query($con, "SELECT `username_friend` FROM $my_connections_table");
			$total_conn_num = mysqli_num_rows($pat_conn_q);
			$pat_conn_num = $total_conn_num - $md_conn_num;
			
			$stmt = $con->prepare("UPDATE basic_info_doctors SET pat_conn = ? WHERE username = ?");
			$stmt->bind_param("is",$pat_conn_num,$username);
			$stmt->execute();
		}
	}

	public function sendRequest($user_to){

		$logged_from = $this->getUsername();
        $user_to_e = $this->crypt->EncryptU($user_to);
		$user_to_obj = new User($this->con, $user_to, $user_to_e);
		$connection_requests = $user_to_obj->getRequestsTable();
		$query = mysqli_query($this->con,"INSERT INTO $connection_requests VALUES('', '$user_to', '$logged_from')");


	}

	public function getMutualFriends($user_to_check){
		$mutualFriends = 0;
		$user_friends_table = $this->getConnections_tab();
		$user_to_check_e = $this->crypt->EncryptU($user_to_check);
		$user_to_check_obj = new User($this->con, $user_to_check, $user_to_check_e);
		$user_to_check_friends_table = $user_to_check_obj->getConnectionsTable();


		$user_friends = mysqli_query($this->con, "SELECT username_friend FROM $user_friends_table");

		$user_to_check_friends = mysqli_query($this->con, "SELECT username_friend FROM $user_to_check_friends_table");

		foreach($user_friends as $i){
			foreach ($user_to_check_friends as $j) {
				if($i == $j && $i != ""){
					$mutualFriends++;
				}
			}
		}
		return $mutualFriends;
	}
	
	public function getMutualDoctors($user_to_check){
		$mutualFriends = 0;
		$user_friends_table = $this->getConnections_tab();
		$user_to_check_e = $this->crypt->EncryptU($user_to_check);
		$user_to_check_obj = new User($this->con, $user_to_check, $user_to_check_e);
		$user_to_check_friends_table = $user_to_check_obj->getConnectionsTable();
		
		
		$user_friends = mysqli_query($this->con, "SELECT username_friend FROM $user_friends_table WHERE doctor = 1");
		
		$user_to_check_friends = mysqli_query($this->con, "SELECT username_friend FROM $user_to_check_friends_table WHERE doctor = 1");
		
		foreach($user_friends as $i){
			foreach ($user_to_check_friends as $j) {
				if($i == $j && $i != ""){
					$mutualFriends++;
				}
			}
		}
		return $mutualFriends;
	}
	
	public function getPathologiesTable(){
		return $this->uhash . "__pathologies";
	}
	
	public function insertPathologiesData($illness, $approx_date){
		$table = $this->getPathologiesTable();
		
		$uc_illness = ucwords($illness);
		
		$creation= $this->user_info['signup_date'];
		$uc_illness= $this->crypt->encryptStringPI($uc_illness, $this->username, $creation);
		
		$stmt = $this->con->prepare("SELECT id FROM $table WHERE illnesses = ?");
		$stmt->bind_param("s", $uc_illness);
		$stmt->execute();
		$repeated_test_q = $stmt->get_result();
		$num_repeated_test = mysqli_num_rows($repeated_test_q);
		
		if($num_repeated_test < 1){
			$stmt = $this->con->prepare("INSERT INTO $table (id, illnesses, approx_date) VALUES('', ?, ?)");
			$stmt->bind_param("ss", $uc_illness, $approx_date);
			$stmt->execute();
		}
	}
	
	public function getPathologiesData(/*Cuttent Date*/ $current_date,/*if the info can be deleted*/ $editable = True){
		$lang = $_SESSION['lang'];
		$ts =  new TimeStamp();
		$con = $this->con;
		$userLoggedIn = $this->username;
		$table = $this->getPathologiesTable();
		
		$stmt = $con->prepare("SELECT * FROM $table ORDER BY approx_date DESC");
		$stmt->execute();
		$query = $stmt->get_result();
		
		$num_res = mysqli_num_rows($query);
		
		if($num_res == 0){

			switch ($lang){
				
				case("en"):
				    echo "<b>No illnesses or hospitalizations added.</b>";
					break;
					
				case("es"):
					echo "<b>No se han añadido enfermedades o hospitalizaciones.</b>";
					break;
			}
			
		}
		
		$str = "";
		foreach($query as $key=> $value){
			$name = $value['illnesses'];
			
			$creation= $this->user_info['signup_date'];
			$name= $this->crypt->decryptStringPI($name, $this->username, $creation);
			
			$id = $value['id'];
			$approx_date = $value['approx_date'];
			$shown_period_time = $ts->getTimeStampFromDates($approx_date, $current_date);
				
			if($editable){
				$str .= "<div class='added_box_element'><div class='table_element translucid_left'>" . $name . "</div><div class='table_element translucid_right'>" . $shown_period_time. "</div><div class='delete_element' id='del_pathologies_" . $id . "'>x</div></div>";
			?>
				<script>
					$(document).ready(function(){
						$('#del_pathologies_<?php echo $id; ?>').on('click',function(){
							$.post("includes/form_handlers/delete_helath_info.php?id=<?php echo $id; ?>&t=<?php echo 'pathologies'; ?>", function(data){
								$('#pathologies_box').html(data); 
								//location.reload();									
							});
						});
					});
				</script>
			<?php
			}
			else{
				$str .= "<div class='added_box_element'><div class='table_element translucid_left_small'>" . $name . "</div><div class='table_element translucid_right_small'>" . $shown_period_time. "</div></div>";
			}
		}
		return $str;
	}
	
	
	public function getSurgeriesTable(){
		return $this->uhash. "__surgical_trauma";
	}
	
	public function insertSurgeriesData($surgeries, $approx_date){
		$table = $this->getSurgeriesTable();
		
		$uc_surgeries = ucwords($surgeries);
		
		$creation= $this->user_info['signup_date'];
		$uc_surgeries= $this->crypt->encryptStringPI($uc_surgeries, $this->username, $creation);

		$stmt = $this->con->prepare("SELECT id FROM $table WHERE surgical_trauma = ?");
		$stmt->bind_param("s", $uc_surgeries);
		$stmt->execute();
		$repeated_test_q = $stmt->get_result();
		$num_repeated_test = mysqli_num_rows($repeated_test_q);
		
		if($num_repeated_test < 1){	
			$stmt = $this->con->prepare("INSERT INTO $table (id, surgical_trauma, approx_date) VALUES('', ?, ?)");
			$stmt->bind_param("ss", $uc_surgeries, $approx_date);
			$stmt->execute();
		}
	}
	
	public function getSurgeriesData(/*Cuttent Date*/ $current_date,/*if the info can be deleted*/ $editable = True){
		$lang = $_SESSION['lang'];
		$ts =  new TimeStamp();
		$con = $this->con;
		$userLoggedIn = $this->username;
		$table = $this->getSurgeriesTable();
		
		$stmt = $con->prepare("SELECT * FROM $table ORDER BY approx_date DESC");
		$stmt->execute();
		$query = $stmt->get_result();
		
		$num_res = mysqli_num_rows($query);
		
		if($num_res == 0){

			switch ($lang){
				
			    case("en"):
			        echo "<b>No surgeries or traumas added.</b>";
			        break;
			        
			    case("es"):
			        echo "<b>No se han añadido cirugías o traumas.</b>";
			        break;
			}
			
		}
		
		$str = "";
		foreach($query as $key=> $value){
		    
			$name = $value['surgical_trauma'];
			$creation= $this->user_info['signup_date'];
			$name= $this->crypt->decryptStringPI($name, $this->username, $creation);
			
			$id = $value['id'];
			$approx_date = $value['approx_date'];
			
			//$approx_date= $this->crypt->encryptStringPI($approx_date, $this->username, $creation);
			
			$shown_period_time = $ts->getTimeStampFromDates($approx_date, $current_date);
			
			if($editable){
				$str .= "<div class='added_box_element'> <div class='table_element translucid_left'>" . $name . "</div><div class='table_element translucid_right'>" . $shown_period_time. "</div><div class='delete_element' id='del_surgical_trauma_" . $id . "'>x</div></div>";
			
			?>
				<script>
					$(document).ready(function(){
						$('#del_surgical_trauma_<?php echo $id; ?>').on('click',function(){
							$.post("includes/form_handlers/delete_helath_info.php?id=<?php echo $id; ?>&t=<?php echo 'surgical_trauma'; ?>", function(data){
								$('#surgical_trauma_box').html(data); 
								//location.reload();									
							});
						});
					});
				</script>
			<?php
			}
			else{
				$str .= "<div class='added_box_element'><div class='table_element translucid_left_small'>" . $name . "</div><div class='table_element translucid_right_small'>" . $shown_period_time. "</div></div>";
			}
		}
		return $str;
	}
	
	
	public function getHereditariesTable(){
		return $this->uhash. "__hereditary";
	}
	
	public function insertHereditariesData($dissease, $relatives){
		$table = $this->getHereditariesTable();
		
		$uc_dissease = ucwords($dissease);
		$uc_relatives = ucwords($relatives);
		
		$creation= $this->user_info['signup_date'];
		$uc_dissease= $this->crypt->encryptStringPI($uc_dissease, $this->username, $creation);
		$uc_relatives= $this->crypt->encryptStringPI($uc_relatives, $this->username, $creation);
		
		$stmt = $this->con->prepare("SELECT id FROM $table WHERE diseases = ? AND relatives = ?");
		$stmt->bind_param("ss", $uc_dissease, $uc_relatives);
		$stmt->execute();
		$repeated_test_q = $stmt->get_result();
		$num_repeated_test = mysqli_num_rows($repeated_test_q);
		
		if($num_repeated_test < 1){
			$stmt = $this->con->prepare("INSERT INTO $table (id, diseases, relatives) VALUES('', ?, ?)");
			$stmt->bind_param("ss", $uc_dissease, $uc_relatives);
			$stmt->execute();
		}
	}
	
	public function getHereditariesData($editable = True /*if lines can be deleted*/){
		$lang = $_SESSION['lang'];
		$con = $this->con;
		$userLoggedIn = $this->username;
		$table = $this->getHereditariesTable();
		
		$stmt = $con->prepare("SELECT * FROM $table ORDER BY id DESC");
		$stmt->execute();
		$query = $stmt->get_result();
		
		$num_res = mysqli_num_rows($query);
		
		if($num_res == 0){

			switch ($lang){
				
			    case("en"):
			        echo "<b>No hereditary diseases added.</b>";
			        break;
			        
			    case("es"):
			        echo "<b>No se han añadido enfermedades.</b>";
			        break;
			}
			
		}
		
		$str = "";
		foreach($query as $key=> $value){
			$name = $value['diseases'];
			$id = $value['id'];
			$relatives = $value['relatives'];
			
			$creation= $this->user_info['signup_date'];
			$name= $this->crypt->decryptStringPI($name, $this->username, $creation);
			$relatives= $this->crypt->decryptStringPI($relatives, $this->username, $creation);
			
			if($editable){
			$str .= "<div class='added_box_element'><div class='table_element translucid_left'>" . $name . "</div><div class='table_element translucid_right'>" . $relatives . "</div><div class='delete_element' id='del_hereditary_" . $id . "'>x</div></div>";
			
			?>
				<script>
					$(document).ready(function(){
						$('#del_hereditary_<?php echo $id; ?>').on('click',function(){
							$.post("includes/form_handlers/delete_helath_info.php?id=<?php echo $id; ?>&t=<?php echo 'hereditary'; ?>", function(data){
								$('#hereditary_box').html(data); 
								//location.reload();									
							});
						});
					});
				</script>
			<?php
			}
			else{
				$str .= "<div class='added_box_element'><div class='table_element translucid_left_small'>" . $name . "</div><div class='table_element translucid_right_small'>" . $relatives . "</div></div>";
			}
		}
		return $str;
	}
	
	
	public function getMedicinesTable(){
		return $this->uhash. "__pharmacology";
	}
	
	public function insertMedicinesData($medicine, $dosage){
		$table = $this->getMedicinesTable();
		
		$uc_medicine = ucwords($medicine);
		$uc_dosage = ucwords($dosage);
		
		$creation= $this->user_info['signup_date'];
		$uc_medicine= $this->crypt->encryptStringPI($uc_medicine, $this->username, $creation);
		$uc_dosage= $this->crypt->encryptStringPI($uc_dosage, $this->username, $creation);
		
		$stmt = $this->con->prepare("SELECT id FROM $table WHERE medicines = ? AND dosage = ?");
		$stmt->bind_param("ss", $uc_medicine, $uc_dosage);
		$stmt->execute();
		$repeated_test_q = $stmt->get_result();
		$num_repeated_test = mysqli_num_rows($repeated_test_q);
		
		if($num_repeated_test < 1){
			$stmt = $this->con->prepare("INSERT INTO $table (id, medicines, dosage) VALUES('', ?, ?)");
			$stmt->bind_param("ss", $uc_medicine, $uc_dosage);
			$stmt->execute();
		}
	}
	
	public function getMedicinesData($editable = True/*if info can be deleted*/ ){
		$lang = $_SESSION['lang'];
		$con = $this->con;
		$userLoggedIn = $this->username;
		$table = $this->getMedicinesTable();
		
		$stmt = $con->prepare("SELECT * FROM $table ORDER BY id DESC");
		$stmt->execute();
		$query = $stmt->get_result();
		
		$num_res = mysqli_num_rows($query);
		
		if($num_res == 0){

			switch ($lang){
				
			    case("en"):
			        echo "<b>No medicines added.</b>";
			        break;
			        
			    case("es"):
			        echo "<b>No se han añadido medicamentos.</b>";
			        break;
			}

		}
		
		$str = "";
		foreach($query as $key=> $value){
			$name = $value['medicines'];
			$creation= $this->user_info['signup_date'];
			$name= $this->crypt->decryptStringPI($name, $this->username, $creation);
			$id = $value['id'];
			$dosage = $value['dosage'];
			$dosage= $this->crypt->decryptStringPI($dosage, $this->username, $creation);
			
			if($editable){
			$str .= "<div class='added_box_element'> <div class='table_element translucid_left'>" . $name . "</div><div class='table_element translucid_right'>" . $dosage. "</div><div class='delete_element' id='del_medicines_" . $id . "'>x</div></div>";
			?>
				<script>
					$(document).ready(function(){
						$('#del_medicines_<?php echo $id; ?>').on('click',function(){
							$.post("includes/form_handlers/delete_helath_info.php?id=<?php echo $id; ?>&t=<?php echo 'medicines'; ?>", function(data){
								$('#medicines_box').html(data);
								//location.reload();									
							});
						});
					});
				</script>
			<?php
			}
			else{
				$str .= "<div class='added_box_element'> <div class='table_element translucid_left'>" . $name . "</div><div class='table_element translucid_right'>" . $dosage. "</div></div>";
			}
		}
		return $str;
	}
	
	
	public function getAllergiesTable(){
		return $this->uhash. "__allergies";
	}
	
	public function insertAllergiesData($allergies){
		
		$table = $this->getAllergiesTable();
		
		$uc_allergies = ucwords($allergies);
		$creation= $this->user_info['signup_date'];
		$uc_allergies = $this->crypt->encryptStringPI($uc_allergies, $this->username, $creation);
		
		$stmt = $this->con->prepare("SELECT id FROM $table WHERE allergies = ?");
		$stmt->bind_param("s", $uc_allergies);
		$stmt->execute();
		$repeated_test_q = $stmt->get_result();
		$num_repeated_test = mysqli_num_rows($repeated_test_q);
		
		if($num_repeated_test < 1){
			$stmt = $this->con->prepare("INSERT INTO $table (id, allergies) VALUES('', ?)");
			$stmt->bind_param("s", $uc_allergies);
			$stmt->execute();
		}
	}
	
	public function getAllergiesData($editable = True/*if info can be deleted*/){
		$creation= $this->user_info['signup_date'];
		$lang = $_SESSION['lang'];
		$con = $this->con;
		$table = $this->getAllergiesTable();
		
		$stmt = $con->prepare("SELECT * FROM $table ORDER BY id DESC");
		$stmt->execute();
		$query = $stmt->get_result();
		
		$num_res = mysqli_num_rows($query);
		
		if($num_res == 0){

			switch ($lang){
				
			    case("en"):
			        echo "<b>No allergies added.</b>";
			        break;
			        
			    case("es"):
			        echo "<b>No se han añadido alergias.</b>";
			        break;
			}
			
		}
		
		$str = "";
		foreach($query as $key=> $value){
			$name = $value['allergies'];
			$name = $this->crypt->decryptStringPI($name, $this->username, $creation);
			$id = $value['id'];
			
			if($editable){
			$str .= "<div class='added_box_element'> <div class='table_element translucid_left'>" . $name . "</div><div class='table_element translucid_right'></div><div class='delete_element' id='del_allergies_" . $id . "'>x</div></div>";
			
			?>
				<script>
					$(document).ready(function(){
						$('#del_allergies_<?php echo $id; ?>').on('click',function(){
							$.post("includes/form_handlers/delete_helath_info.php?id=<?php echo $id; ?>&t=<?php echo 'allergies'; ?>", function(data){
								$('#allergies_box').html(data);
								//location.reload();									
							});
						});
					});
				</script>
			<?php
			}
			else{
				$str .= "<div class='added_box_element'> <div class='table_element translucid_left_small'>" . $name . "</div><div class='table_element translucid_right_small'></div></div>";
			}
		}
		return $str;
	}
	
	public function getOBGYNTable(){
		return $this->uhash . "__OBGYN";
	}

	public function getHabitsTable(){
		return $this->uhash . "__habits";
	}
	
	public function getSymptomsFrecTable(){
		$username = $this->username;
		$con = $this->con;
		
		$stmt = $con->prepare("SELECT symptoms_table FROM symptoms_tables WHERE username=?");
		$stmt->bind_param("s",$username);
		$stmt->execute();
		$q = $stmt->get_result();
		
		$num_rows = mysqli_num_rows($q);
		
		if($num_rows < 1){
			
			$created_table = $this->hash. "__symptoms_types_doc";
			
			$stmt = $con->prepare("CREATE TABLE $created_table(
					id varchar(5) NOT NULL PRIMARY KEY,
					symptoms varchar(30) NOT NULL,
					probability decimal(6,3)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin ROW_FORMAT=COMPACT"
					);
			$stmt->execute();
			
			$stmt = $con->prepare("REPLACE INTO symptoms_tables (`username`,`symptoms_table`) VALUES (?,?)");
			$stmt->bind_param("ss",$username,$created_table);
			$stmt->execute();
			
			$table = $created_table;
		}
		else{
			$table = mysqli_fetch_array($q)['symptoms_table'];
		}
		return $table;
	}
	
	public function getOfficeDetails(){
		$username = $this->username;
		$stmt = $this->con->prepare("SELECT `insurance_accepted_1`, `insurance_accepted_2`, `insurance_accepted_3`, `ad1nick`, `ad1ln1`, `ad1ln2`, `ad1ln3`, `ad1city`, `ad1adm2`, `ad1lat`, `ad1lng`, `ad2nick`, `ad2ln1`, `ad2ln2`, `ad2ln3`, `ad2city`, `ad2adm2`, `ad2lat`, `ad2lng`, `ad3nick`, `ad3ln1`, `ad3ln2`, `ad3ln3`, `ad3city`, `ad3adm2`, `ad3lat`, `ad3lng` FROM `basic_info_doctors` WHERE `username` = ?");
		$stmt->bind_param("s",$username);
		$stmt->execute();
		
		$qres = $stmt->get_result();
		
		return mysqli_fetch_array($qres); 
	}
	
}

?>