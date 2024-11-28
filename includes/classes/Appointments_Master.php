<?php  
    include_once("Crypt.php");
	class Appointments_Master{
		private $user_obj;
		private $con;
		private $appointments_details_doctor;
		private $appointments_details_patient;


		public function __construct($con, $user, $user_e){
			try{
				//TODO: constructor
				$this->con = $con;
				$this->user_obj = $user_obj = new User($con, $user, $user_e);

			}	
			catch ( Exception $e ){
				$this->con = "";
				$this->user_obj = "";
				$user_obj = "";
				throw new Exception( $e->getMessage() );
			}
				
			
			if($user_obj->isDoctor()){
				$temp_table = $user_obj->getAppointmentsDetails_Doctor();
			}
			else{
				$temp_table = $user_obj->getAppointmentsDetails_Patient();
			}
			$stmt = $con->prepare("SELECT * FROM $temp_table ORDER BY consult_id DESC");
			$stmt->execute();
			$query = $stmt->get_result();
			$temp_array = [];
			
			while($row = mysqli_fetch_array($query)){
				$temp_array[] = $row;
			}
			
			if($user_obj->isDoctor()){
				$this->appointments_details_doctor = $temp_array;
				$this->appointments_details_patient = [];
			}
			else{
				$this->appointments_details_doctor = [];
				$this->appointments_details_patient = $temp_array;
			}			

			$stmt->close();
		}
		
		public function getLastNViewdDoctors($n){
			$doctors_array = [];
			$_rep_docs_array = [];
			for($i=0;$i<sizeof($this->appointments_details_patient) && $i < $n;$i++){
				//echo "key " . $key;
				if(!in_array($this->appointments_details_patient[$i]['doctor_username'], $_rep_docs_array)){
					$_rep_docs_array[] = $this->appointments_details_patient[$i]['doctor_username'];
					$doctors_array[] = array(
							"username" => $this->appointments_details_patient[$i]['doctor_username'],
							"cid" => $this->appointments_details_patient[$i]['consult_id']);
				}
			}
			return $doctors_array;
		}
		
		public function getOfficeData($doctor_username,$payment_method,$ap_start,$ap_end,$day,$month,$year){
			
			$stmt = $this->con->prepare("SELECT `insurance_accepted_1`, `insurance_accepted_2`, `insurance_accepted_3`, `ad1nick`, `ad1ln1`, `ad1ln2`, `ad1ln3`, `ad1city`, `ad1adm2`, `adcountry`, `ad1lat`, `ad1lng`, `ad2nick`, `ad2ln1`, `ad2ln2`, `ad2ln3`, `ad2city`, `ad2adm2`, `ad2lat`, `ad2lng`, `ad3nick`, `ad3ln1`, `ad3ln2`, `ad3ln3`, `ad3city`, `ad3adm2`, `ad3lat`, `ad3lng` FROM basic_info_doctors WHERE username=?");
			$stmt->bind_param("s",$doctor_username);
			$stmt->execute();
			$query = $stmt->get_result();
			$res = mysqli_fetch_array($query);
			
			return $res;
		}
		
		public function getPatient_view_Doctor($consult_id){
			//Means the doctor that instated the class wants to know the patient of the appointment
			$array = $this->appointments_details_patient;
			$usernames = array_column($array, 'doctor_username', 'consult_id');
			$doctor_username = $usernames[$consult_id];
			return $doctor_username;
		}
		
		public function getDoctor_view_Patient($consult_id){
			//Means the patient that instated the class wants to know the doctor of the appointment
			$array = $this->appointments_details_doctor;
			$usernames = array_column($array, 'patient_username', 'consult_id');
			$patient_username = $usernames[$consult_id];
			return $patient_username;
		}
		
		public function patient_view_IsConfirmed($consult_id){
			//patient wants to know if its appointment is confirmed
			$table = $this->user_obj->getAppointmentsCalendar_Patient();
			$stmt = $this->con->prepare("SELECT confirmed_pat FROM $table WHERE consult_id=?");
			$stmt->bind_param("s",$consult_id);
			$stmt->execute();
			$query = $stmt->get_result();
			return mysqli_fetch_array($query)['confirmed_pat'];
			
		}
		
		public function doctor_view_IsConfirmed($consult_id){
			//doctor wants to know if its appointment is confirmed
			$table = $this->user_obj->getAppointmentsCalendar();
			$stmt = $this->con->prepare("SELECT confirmed_doc FROM $table WHERE consult_id=?");
			$stmt->bind_param("s",$consult_id);
			$stmt->execute();
			$query = $stmt->get_result();
			return mysqli_fetch_array($query)['confirmed_doc'];
		}
		
		public function doctor_view_ConfirmAppointment($consult_id){
			//doctor wants to confirm his appointment
			$table = $this->user_obj->getAppointmentsCalendar();
			$stmt = $this->con->prepare("UPDATE $table SET confirmed_doc = 1 WHERE consult_id=?");
			$stmt->bind_param("s",$consult_id);
			$stmt->execute();
		}
		
		public function getPatient_view_AppoType($consult_id){
			$array = $this->appointments_details_patient;
			$appo_types = array_column($array, 'appo_type', 'consult_id');
			$appo_type = $usernames[$consult_id];
			return $appo_type;
		}
		
		public function getDoctor_view_AppoType($consult_id){
			$array = $this->appointments_details_doctor;
			$appo_types = array_column($array, 'appo_type', 'consult_id');
			$appo_type = $usernames[$consult_id];
			return $appo_type;
		}
		
		public function getAppointmentTimeDate($consult_id,$view_type){
			if($view_type == 1)
				$table = $this->user_obj->getAppointmentsCalendar_Patient();
			elseif($view_type == 2)
				$table = $this->user_obj->getAppointmentsCalendar();

			$stmt = $this->con->prepare("SELECT year,month,day,time_start,time_end FROM $table WHERE consult_id=?");
			$stmt->bind_param("s",$consult_id);
			$stmt->execute();
			$query = $stmt->get_result();
			return mysqli_fetch_array($query);
		}
		
		public function insert_diagnosis_cie_10($diagnosis_num, $aid, $usr, $cie_code){
		    $rips_tab = $this->user_obj->getDoctorsRIPS_tablename();
		    
		    switch ($diagnosis_num){
		      case 1:
		          $row = "cod_diag_1";
		          break;
		      case 2:
		          $row = "cod_diag_2";
		          break;
		      case 3:
		          $row = "cod_diag_3";
		          break;
		      case 4:
		          $row = "cod_diag_4";
		          break;
		    }
		    //$sql = "REPLACE " . $rips_tab . " SET " . $row . " = ? WHERE consult_id = ?";
		    $sql = "INSERT INTO $rips_tab (consult_id, $row)
		    VALUES (?,?)
		    ON DUPLICATE KEY UPDATE $row=?";
		    $stmt = $this->con->prepare($sql);
		    $stmt->bind_param("sss",$aid,$cie_code,$cie_code);
		    $stmt->execute();
		}
		
		public function getDoctorViewPrevNextAppo_id_usern($consult_id){
			$table = $this->user_obj->getAppointmentsCalendar();
			$query = mysqli_query($this->con, "SELECT consult_id FROM $table");
			
			$prev = 404;
			$prev_next_aid = [];
			$found = 0;
			
			foreach ($query as $key => $value){
				if($found){
					$prev_next_aid[1] = $value['consult_id'];
					break;
				}	
				if($consult_id == $value['consult_id']){
					$prev_next_aid[0] = $prev;
					$prev_next_aid[1] = 404;
					$found = 1;
				}
				else
					$prev = $value['consult_id'];
			}
			
			$prev_next_id[0] = ($prev_next_aid[0] != 404)? $this->getDoctor_view_Patient($prev_next_aid[0]) : "";
			$prev_next_id[1] = ($prev_next_aid[1] != 404)? $this->getDoctor_view_Patient($prev_next_aid[1]) : "";
			
			$prev_next['aid'] = $prev_next_aid;
			$prev_next['id'] = $prev_next_id;
			
			return $prev_next;
		}
		
		public function printPreviousAppointments($patient_obj, $category_temp = '', $search_term = '', $limit = 4, $count_prev = 0, $doc_usr = 0){
			$lang = $_SESSION['lang'];
			
			if($search_term == ''){
				$category_temp = '';
			}
			
			if($search_term != ''){
				if(trim($search_term," ") == ""){
					$search_term = '';
					$category_temp = '';
				}
			}
			
			$appo_details = $patient_obj->getAppointmentsDetails_Patient();
			$appo_dates = $patient_obj->getAppointmentsCalendar_Patient();
			
			switch($category_temp){
				case "specialty":
					$category = "specialty";
					break;
				case "name":
					$category = "name";
					break;
				case "plan":
					$category = "plan";
					break;
				case "symptoms":
					$category = "symptoms";
					break;
				default:
					$category = "";
			}
			
			if($category == "A.plan"){
				$sql = "SELECT A.consult_id, A.doctor_username, A.specializations, A.plan, B.year, B.month, B.day
						FROM " . $appo_details . " AS A 
						LEFT JOIN " . $appo_dates . " AS B
						ON A.consult_id = B.consult_id
						WHERE LOWER(" . $category . ") LIKE ?
						ORDER BY B.year DESC, B.month DESC, B.day DESC, B.time_start DESC";
				
				$search_term = "%" . strtolower($search_term) . "%";
				$this->con->prepare($sql);
				$stmt->bind_param("s",$search_term);
			}
			else{
				$sql = "SELECT A.consult_id, A.doctor_username, A.specializations, A.plan, B.year, B.month, B.day
						FROM " . $appo_details . " AS A LEFT JOIN " . $appo_dates . " AS B
						ON A.consult_id = B.consult_id
						ORDER BY B.year DESC, B.month DESC, B.day DESC, B.time_start DESC";
				
				$stmt = $this->con->prepare($sql);
			}
			
			$stmt->execute();
			$query = $stmt->get_result();
			
			$str = '';
			$length = 20;
			
			//fnmatch("*gr[ae]y", $color)
			
			$query_num_res = mysqli_num_rows($query);
			
			$count = 0;
			$displayed = 0;
			foreach($query as $key => $arr){
				if($count < $count_prev){
					$count = $count + 1;
					continue;
				}
				
				$txt_rep = new TxtReplace();
				$specialist_username = $arr['doctor_username'];
				
				if($doc_usr != 0 && $doc_usr != $specialist_username){
					continue;
				}
				
				$plan = $arr['plan'];
				$crypt = new Crypt();
				$specialist_obj = new User($this->con, $specialist_username, $crypt->EncryptU($specialist_username));
				$specialist_last_name = $specialist_obj->getLastNameShort($length);
				$specialization = $specialist_obj->getSpecializationsText($lang);
				
				if($category == "specialty" || $category == "name" || $category == "plan"){
					
					switch($category){
						case "specialty":
							$heystack = $txt_rep->entities($specialization);
							break;
						case "name":
							$heystack = $txt_rep->entities($specialist_obj->getFirstAndLastNameFast());
							break;
						case "plan":
							$heystack = $txt_rep->entities($plan);
							break;
					}
					
					$search_term_wild = "*" . strtolower($search_term) . "*";
					if($category == "plan"){
						if(strpos(strtolower($heystack), strtolower($search_term)) === false){
							continue;
						}
					}
					else{
						if(!fnmatch($search_term_wild, strtolower($heystack))){
							continue;
						}
					}
					
					$_array = explode(strtolower($search_term), strtolower($heystack));
					
					$_highlited_string = $_array[0];
					for($i=1;$i<sizeof($_array);$i++){
						$_highlited_string .= "<span class='highlighted_text'>" . ucwords($search_term) . "</span>" . $_array[$i];
					}
					$sympts_str = ucwords($_highlited_string);
					
					switch($category){
						case "specialty":
							$specialization = ucwords($_highlited_string);
							break;
						case "name":
							$specialist_last_name = ucwords($_highlited_string);
							break;
						case "plan":
							$plan = ucwords($_highlited_string);
							break;
					}
					
					
				}
				
				$profile_pic = $specialist_obj->getProfilePicFast();
				
				$appo_symptoms = $patient_obj->getAppointmentsSymptoms_Patient();
				$temp_consult_id = $arr['consult_id'];
				
				$sql = "SELECT title FROM " . $appo_symptoms . " WHERE consult_id = ?";
				$stmt = $this->con->prepare($sql);
				$stmt->bind_param("s",$temp_consult_id);
				$stmt->execute();
				
				$query_sympts = $stmt->get_result();
				$query_sympts_num = mysqli_num_rows($query_sympts);
				
				$sympts_str = "";
				foreach ($query_sympts as $key2 => $arr2){
					if($key2 + 1 < $query_sympts_num){
						$sympts_str .= $arr2['title'] . ", ";
					}
					else{
						$sympts_str .= $arr2['title'] . ".";
					}
				}
				
				if($category == "symptoms"){
					$heystack = $sympts_str;
					if(strpos(strtolower($heystack), strtolower($search_term)) === false){
						continue;
					}
					
					$_array = explode(strtolower($search_term), strtolower($heystack));
					
					$_highlited_string = $_array[0];
					for($i=1;$i<sizeof($_array);$i++){
						$_highlited_string .= "<span class='highlighted_text'>" . ucwords($search_term) . "</span>" . $_array[$i];
					}
					
					$sympts_str = ucwords($_highlited_string);
				}
				
				//$plan = $arr['plan'];
				$date = $arr['day'] . '/' . $arr['month'] . '/' . $arr['year'];
				
				switch ($lang){
					
					case("en"):
					    $str .= '<div class="row_container_dashboard">
							<div class="row_element_dashboard dash_search_name style-2">
								<div class="view_search" ><img  src="' . $txt_rep->entities($profile_pic) . '"> </div>
								<p class="dashboard_tag name_tag_dash_search">Dr. ' . $specialist_last_name. '</p>
								<p class="dashboard_tag"><b> Specialty: </b><br>' . $specialization . '</p>
								<p class="dashboard_tag"><b> Date: </b>' . $date . '</p>
							</div>
							<div class="row_element_dashboard dash_search_symptoms style-2">
								<p class="dashboard_tag">' . $sympts_str . '</p>
							</div>
							<div class="row_element_dashboard dash_search_plan style-2">
								<p class="dashboard_tag">' . $plan . '</p>
							</div>
						</div>';
						break;
						
					case("es"):
					    $str .= '<div class="row_container_dashboard">
							<div class="row_element_dashboard dash_search_name style-2">
								<div class="view_search" ><img src="' . $txt_rep->entities($profile_pic) . '"> </div>
								<div class="dashboard_tag name_tag_dash_search">Dr. ' . $specialist_last_name. '</div>
								<p class="dashboard_tag"><b> Especialidad: </b><br>' . $specialization . '</p>
								<p class="dashboard_tag"><b> Fecha: </b>' . $date . '</p>
							</div>
							<div class="row_element_dashboard dash_search_symptoms style-2">
								<p class="dashboard_tag">' . $sympts_str . '</p>
							</div>
							<div class="row_element_dashboard dash_search_plan style-2">
								<p class="dashboard_tag">' . $plan . '</p>
							</div>
						</div>';
						break;
				}
				

				$count = $count + 1;
				$displayed = $displayed + 1;
				//echo "displayed" . $displayed;
				if($displayed >= $limit){
					$str.= '<input type="hidden" class="count_search_dash" value="' . $count .'">
							<input type="hidden" class="end_search_dash" value="0">';
					break;
				}
				
			}
			$str.= '<input type="hidden" class="end_search_dash" value="1">';
			return $str;
		}
		
		
		public function printAppointmentSymptoms($consult_id){
			//Doctor sees patient's symptoms for this consult. This returns a sring for printing the appointment dashboard's list of symptoms for each consult
			
			$lang = $_SESSION['lang'];
			$patient_username = $this->getPatient($consult_id);
			$symptoms_table = $this->user_obj->getSymptoms_DoctorPOV();
			
			$stmt = $this->con->prepare("SELECT title,description,id FROM $symptoms_table WHERE consult_id = ?");
			$stmt->bind_param("s",$consult_id);
			$stmt->execute();
			
			$query = $stmt->get_result();
			$str = "";
			
			if(mysqli_num_rows($query) > 0){
				while($arr = mysqli_fetch_assoc($query)){
					$str .= "<div class='appo_dash_sympt_cont'>
								<h4>" . $arr['title'] . "<div class='delete_element' id='del_id_" . $arr['id']. "'>x</div></h4>
								<p class='dashboard_tag'>" . $arr['description'] . "</p>
							</div>";
					$str .= <<<EOS
					<script>
					$(document).ready(function(){
					$('#del_id_{$arr['id']}').on('click',function(){
						
							$.ajax({
								url: "includes/form_handlers/delete_symp_med_doc.php",
								type: "POST",
								data: "id={$arr['id']}&aid={$consult_id}&type=symptoms&u={$patient_username}",
								cache:false,
								
								success: function(data){
									//alert(data);
								},
								
							});
							
								var cid = '{$consult_id}';
								$.ajax({
									url: "includes/handlers/ajax_reload_symptoms.php",
									type: "POST",
									data: "cid=" + cid,
									cache:false,
									
									success: function(data){
										$('#dashboard_symptoms_cont_box').html("");
										$('#dashboard_symptoms_cont_box').html(data);
										var ddd = document.getElementById('dashboard_symptoms_cont_box');
										ddd.scrollTop = ddd.scrollHeight;
									}
								});
									//location.reload();
						});
					});
					</script>
EOS;
				}
			}
			else{
				switch ($lang){
					
				    case("en"):
				        $str = "<b> No symptoms were given.</b>";
				        break;
				        
				    case("es"):
				        $str = "<b>No se encontraron síntomas.</b>";
				        break;
				}
				
			}
			
			return $str;
		}
		
		public function getDoctor($consult_id){
			$appo_dets = $this->user_obj->getAppointmentsDetails_Patient();
			
			$stmt = $this->con->prepare("SELECT * FROM $appo_dets WHERE consult_id = ?");
			$stmt->bind_param("s", $consult_id);
			
			$stmt->execute();
			$get_symptoms_query = $stmt->get_result();
			$arr = mysqli_fetch_assoc($get_symptoms_query);
			return $arr['doctor_username'];
		}
		
		public function getPatient($consult_id){
			$appo_dets = $this->user_obj->getAppointmentsDetails_Doctor();
			
			$stmt = $this->con->prepare("SELECT * FROM $appo_dets WHERE consult_id = ?");
			$stmt->bind_param("s", $consult_id);
			
			$stmt->execute();
			$get_symptoms_query = $stmt->get_result();
			$arr = mysqli_fetch_assoc($get_symptoms_query);
			return $arr['patient_username'];
		}
		
		public function printAppointmentSymptoms_Patient($consult_id){
			//patrient sees HIS symptoms for this consult. This returns a sring for printing the appointment dashboard's list of symptoms for each consult
			
			$lang = $_SESSION['lang'];
			$con = $this->con;
			
			$doctor_username = $this->getDoctor($consult_id);
			
			$symptomsTable = $this->user_obj->getAppointmentsSymptoms_Patient();
			$type = "symptoms";
			
			$stmt = $con->prepare("SELECT * FROM $symptomsTable WHERE consult_id = ? ORDER BY id");
			$stmt->bind_param("s", $consult_id);
			
			$stmt->execute();
			$get_symptoms_query = $stmt->get_result();
			$num_types = mysqli_num_rows($get_symptoms_query);
			$stmt->close();
			
			$data="";
			if($num_types > 0){
				
				while($arr = mysqli_fetch_array($get_symptoms_query)){
					$title = $arr['title'];
					$id = $arr['id'];
					
					$txt_rep = new TxtReplace();
					$title = $txt_rep->entities($title);
					
					$line = "<div class='table_element' id='translucid_appo_type'>" . $title . "</div><div class='delete_element' id='del_id_" . $id . "'>x</div><br>";
					
					$data = $data . $line;
					
					$data = $data . <<<EOS
					<script>
					$(document).ready(function(){
						$('#del_id_{$id}').on('click',function(){
							
							$.ajax({
								url: "includes/form_handlers/delete_symp_med.php",
								type: "POST",
								data: "id={$id}&aid={$consult_id}&type={$type}&u={$doctor_username}",
								cache:false,
								
								success: function(data){
								},
								
							});
								
								var cid = '{$consult_id}';
								$.ajax({
									url: "includes/handlers/ajax_reload_symptoms.php",
									type: "POST",
									data: "cid=" + cid,
									cache:false,
									
									success: function(data){
										$('#appo_symp_med_box').html("");
										$('#appo_symp_med_box').html(data);
										var ddd = document.getElementById('appo_symp_med_box');
										ddd.scrollTop = ddd.scrollHeight;
									}
								});
									//location.reload();
						});
					});
					</script>
EOS;
					}
				}	
				else{
					$data = "<div class='table_element' id='translucid_appo_type'> Insert a symptom.</div> <div class='table_element' id='translucid_appo_durat'></div><br>";
				}

				echo $data;
		}
		
		public function viewAppoTypeId_Doctor($consult_id){
			//the class should have been started by a doctor user
			$doct_appo_dets = $this->user_obj->getAppointmentsDetails_Doctor();
			
			$stmt = $this->con->prepare("SELECT appo_type FROM $doct_appo_dets WHERE consult_id=?");
			$stmt->bind_param("s",$consult_id);
			$stmt->execute();
			$q = $stmt->get_result();
			$arr = mysqli_fetch_assoc($q);
			$appo_type = $arr['appo_type'];
			
			return $appo_type;
			
		}
		
		public function changeAppoType($consult_id,$doctor_obj,$patient_obj,$appo_type_id){
			$appo_types_tab = $doctor_obj->getAppoDurationTable();
			$stmt = $this->con->prepare("SELECT * FROM $appo_types_tab WHERE id=?");
			$stmt->bind_param("s",$appo_type_id);
			$stmt->execute();
			$q = $stmt->get_result();
			$arr = mysqli_fetch_assoc($q);
			$appo_type = $arr['appo_type'];
			$cost = $arr['cost'];
			$currency = $arr['currency'];
			$appo_type_id = $arr['id'];
			
			$appo_det_doc = $doctor_obj->getAppointmentsDetails_Doctor();
			$stmt = $this->con->prepare("UPDATE $appo_det_doc SET appo_type = ?, cost=?, currency=? WHERE consult_id=?");
			$stmt->bind_param("isss",$appo_type_id,$cost,$currency,$consult_id);
			$stmt->execute();
			
			$appo_det_pat = $patient_obj->getAppointmentsDetails_Patient();
			$stmt = $this->con->prepare("UPDATE $appo_det_pat SET appo_type = ?, cost=?, currency=? WHERE consult_id=?");
			$stmt->bind_param("isss",$appo_type_id,$cost,$currency,$consult_id);
			$stmt->execute();
		}

	}		
?>	