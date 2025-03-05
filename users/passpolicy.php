<?php
//
class mwmod_mw_users_passpolicy extends mw_apsubbaseobj{
	private $man;
	public $pass_min_len=8;
	public $pass_max_len=100;
	public $pass_def_len=10;
	public $must_contain_uppers=true;
	public $must_contain_lowers=true;
	public $must_contain_numbers=true;
	
	var $change_password_on_remember_ui_enabled=false;
	var $pass_secure_mode=1;//1: secure, 2: optional, 3: non secure
	
	var $create_new_user_input;
	function __construct($man){
		$this->init($man);
		
	}
	function can_change_password_on_remember_ui(){
		return $this->change_password_on_remember_ui_enabled;	
	}
	function prepare_new_pass_inputs($pass,$confirm=false,$change_chkbox=false){
		
		$fnc=$pass->add_after_init_event_to_list();
		$fnc->set_js_code_in("var i =inputman.get_input();if(i){i.onkeyup=function(){inputman.validation_function_after_change(); return true;};}");
		
		
		$valid_fnc=$pass->set_js_validation_function();
		$js="var disabled=false;\n";
		
		
		if($change_chkbox){
			
			$js.="if(this.get_other_man_value('".$change_chkbox->get_frm_field_id()."')){\n";
			$js.="disabled=false;\n";
			$js.="}else{\n";
			$js.="disabled=true;\n";
			$js.="this.set_validation_status_normal();\n";
			$js.="return true;\n";
			$js.="}\n";
		}
		$js.="if(disabled){\n";
		$js.="this.set_validation_status_normal();\n";
		$js.="return true;\n";
		$js.="}\n";
		if($confirm){
			$js.="var confirm=this.get_other_man('".$confirm->get_frm_field_id()."');\n";
			$js.="if(confirm){\n";
			$js.="confirm.validation_function_after_change();\n";
			$js.="}\n";
			
		}
		
		$js.="var validator=new mw_validator();\n";
		$js.="var pass=this.get_value()+'';\n";
		$a=array("n"=>$this->pass_min_len);	
		$msg=$this->lng_get_msg_txt("password_must_have_at_least_x_chars","La contraseña debe tener al menos %n% caracteres",$a);	
		$js.="if(!validator.check_min_length(pass,".$this->pass_min_len.")){\n";
		$js.="this.set_validation_status_error('".$valid_fnc->get_txt($msg)."');\n";
		$js.="return false;\n";
		$js.="}\n";
		
		$a=array("n"=>$this->pass_max_len);	
		$msg=$this->lng_get_msg_txt("password_cant_have_more_than_x_chars","La contraseña no debe tener más de %n% caracteres",$a);	
		$js.="if(!validator.check_max_length(pass,".$this->pass_max_len.")){\n";
		$js.="this.set_validation_status_error('".$valid_fnc->get_txt($msg)."');\n";
		$js.="return false;\n";
		$js.="}\n";

		
		if($this->must_contain_lowers){
			$msg=$this->lng_get_msg_txt("password_must_contain_lowercase","La contraseña debe contener minúsculas");
			$js.="if(!validator.has_lowers(pass)){\n";
			$js.="this.set_validation_status_error('".$valid_fnc->get_txt($msg)."');\n";
			$js.="return false;\n";
			$js.="}\n";
			
		}
		if($this->must_contain_uppers){
			$msg=$this->lng_get_msg_txt("password_must_contain_uppercase","La contraseña debe contener mayúsculas");	
			$js.="if(!validator.has_uppers(pass)){\n";
			$js.="this.set_validation_status_error('".$valid_fnc->get_txt($msg)."');\n";
			$js.="return false;\n";
			$js.="}\n";
		}
		if($this->must_contain_numbers){
			$msg=$this->lng_get_msg_txt("password_must_contain_numbers","La contraseña debe contener números");	
			$js.="if(!validator.has_numbers(pass)){\n";
			$js.="this.set_validation_status_error('".$valid_fnc->get_txt($msg)."');\n";
			$js.="return false;\n";
			$js.="}\n";
			
		}

		
		$js.="if(disabled){\n";
		$js.="this.set_validation_status_normal();\n";
		$js.="}else{\n";
		$js.="this.set_validation_status_success();\n";
		$js.="}\n";
		$js.="return true;\n";
		
		$valid_fnc->set_validation_js_code_in($js);
		
		if(!$confirm){
			return;	
		}
		
		$js="var disabled=false;\n";
		
		$fnc=$confirm->add_after_init_event_to_list();
		$fnc->set_js_code_in("var i =inputman.get_input();if(i){i.onkeyup=function(){inputman.validation_function_after_change(); return true;};}");
		$valid_fnc=$confirm->set_js_validation_function();
		
		if($change_chkbox){
			$js.="if(this.get_other_man_value('".$change_chkbox->get_frm_field_id()."')){\n";
			$js.="disabled=false;\n";
			$js.="}else{\n";
			$js.="disabled=true;\n";
			$js.="this.set_validation_status_normal();\n";
			$js.="return true;\n";
			$js.="}\n";
		}
		$js.="var pass_c=this.get_value()+'';\n";
		$js.="if(!pass_c){\n";
		$js.="this.set_validation_status_normal();\n";
		$js.="return false;\n";
		$js.="}\n";
		$msg=$this->lng_get_msg_txt("password_does_not_match","La contraseña no coincide");	
		$js.="if(this.get_other_man_value('".$pass->get_frm_field_id()."')!=pass_c){\n";
		$js.="this.set_validation_status_error('".$valid_fnc->get_txt($msg)."');\n";
		$js.="return false;\n";
		$js.="}\n";
		$js.="if(disabled){\n";
		$js.="this.set_validation_status_normal();\n";
		$js.="}else{\n";
		$js.="this.set_validation_status_success();\n";
		$js.="}\n";
		$js.="return true;\n";
		$valid_fnc->set_validation_js_code_in($js);
		
		
		if($change_chkbox){
				
			$fnc=$change_chkbox->add_after_init_event_to_list();
			$js="var dis=true;\n";
			$js.="var input_pass=false;\n";
			$js.="var input_pass_confirm=false;\n";
			if($pass){
				$js.="input_pass=inputman.get_other_man('".$pass->get_frm_field_id()."');\n";	
			}
			if($confirm){
				$js.="input_pass_confirm=inputman.get_other_man('".$confirm->get_frm_field_id()."');\n";	
			}
			$js.="if(inputman.isChecked()){\n";
			$js.="dis=false;\n";
			$js.="}\n";
			
			$js.="if(input_pass){\n";
			$js.="input_pass.setDisabled(dis);";
			$js.="input_pass.set_validation_status_normal();\n";
			$js.="}\n";
			$js.="if(input_pass_confirm){\n";
			$js.="input_pass_confirm.setDisabled(dis);\n";
			$js.="input_pass_confirm.set_validation_status_normal();\n";			
			$js.="}\n";
			
			
			$jsf="var fnc=function(inputman){".$js."};\n";
			
			$fnc->set_js_code_in("$jsf; fnc(inputman); inputman.add_after_change_event(fnc);");

		}
		
		
		
		

		
		
			
	}
	
	function check_crypted_password($password_entered,$password_hash){
		if(!$password_entered){
			return false;	
		}
		if(!is_string($password_entered)){
			return false;	
		}
		if(!$password_hash){
			return false;	
		}
		if(!is_string($password_hash)){
			return false;	
		}
		if(password_verify($password_entered,$password_hash)){
			return true;

		}
		/*
		if(crypt($password_entered, $password_hash) == $password_hash) {
			return true;	
		}
		*/
		return false;
	}
	function crypt_password($password){
		if(!$password){
			return false;	
		}
		if(!is_string($password)){
			return false;	
		}
		$password_hash = password_hash($password, PASSWORD_DEFAULT);//crypt($password);
		return $password_hash;
		
	}
	
	function new_random(){
		$alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');	
		$types=array("n","u","l");
		$types_count=array();
		$typesnum=sizeof($types)-1;
		$pass_data=array();
		for($i=0;$i<$this->pass_def_len;$i++){
			$last_type=$type;
			$type_index=rand(0,$typesnum);
			$type=$types[$type_index];
			
			
			$pass_data[$i]["type"]=$type;
			$types_count[$type]++;
			$pass_data[$i]["apearancenum"]=$types_count[$type];
			if($pass_data[$i]["apearancenum"]>2){
				$pass_data[$i]["mayreaplce"]=true;	
			}
			$cur++;
		}
		
		//mw_array2list_echo($pass_data);
		$missing=array();
		reset($types);
		foreach($types as $type){
			if(!$types_count[$type]){
				$missing[]=$type;
			}
		}
		reset($missing);
		if(sizeof($missing)){
			$missing_index=0;
			reset($pass_data);
			foreach($pass_data as $i=>$d){
				if($missing_index>=sizeof($missing)){
					break;	
				}
				if($d["mayreaplce"]){
					$pass_data[$i]["typeold"]=$pass_data[$i]["type"];
					$pass_data[$i]["type"]=$missing[$missing_index];
					$missing_index++;
						
				}
			}
			
		}
		reset($pass_data);
		//mw_array2list_echo($pass_data);
		$pass_list=array();
		foreach($pass_data as $i=>$d){
			$type=$d["type"];
			if($type=="n"){
				$ch=rand(0,9);	
			}else{
				$chindex=rand(0,sizeof($alphabet)-1);
				$ch=$alphabet[$chindex];
				if($type=="u"){
					$ch=strtoupper($ch);	
				}
			}
			$pass_data[$i]["ch"]=$ch;
			$pass_list[]=$ch;
			//if($d[""]	
		}
		//mw_array2list_echo($pass_data);
		return implode("",$pass_list);
		
		
	}
	function has_number($pass){
		if(!$pass){
			return false;	
		}
		if(!is_string($pass)){
			if(is_numeric($pass)){
				return true;	
			}
			return false;	
		}
		for($x=0;$x<strlen($pass);$x++){
			$ch=substr($pass,$x,1);
			if(is_numeric($ch)){
				return true;	
			}
		}
		
	}
	
	function has_upper($pass){
		if(!$pass){
			return false;	
		}
		if(!is_string($pass)){
			return false;	
		}
		for($x=0;$x<strlen($pass);$x++){
			$ch=substr($pass,$x,1);
			if(ctype_upper($ch)){
				return true;	
			}
		}
		
	}
	function has_lower($pass){
		if(!$pass){
			return false;	
		}
		if(!is_string($pass)){
			return false;	
		}
		for($x=0;$x<strlen($pass);$x++){
			$ch=substr($pass,$x,1);
			if(ctype_lower($ch)){
				return true;	
			}
		}
		
	}
	
	function new_pass_is_secure_by_input($input){
		if($this->pass_secure_mode===2){
			if(is_null($input)){
				return 1;	
			}
			if($input){
				return 1;	
			}else{
				return 0;	
			}
		}
		if($this->pass_secure_mode===3){
			return 0;
		}
		return 1;
	}
	
	function check_passpair_input_by_array($input,&$msg=""){
		if(!is_array($input)){
			$msg=$this->lng_get_msg_txt("invalid_password","Contraseña no válida");
			return false;
		}
		if($r=$this->check_passpair_input($input["pass"],$input["pass1"],$msg)){
			return $r;	
		}
		if(!$msg){
			$msg=$this->lng_get_msg_txt("invalid_password","Contraseña no válida");	
		}
		return false;
		
	}
	function check_passpair_input($pass,$pass1,&$msg=""){
		if(!$pok=$this->check_new_pass($pass,$msg)){
			if(!$msg){
				$msg=$this->lng_get_msg_txt("invalid_password","Contraseña no válida");	
			}
			return false;	
		}
		if(!$pass1){
			$msg=$this->lng_get_msg_txt("password_does_not_match","La contraseña no coincide");	
			return false;	
		}
		if(!is_string($pass1)){
			$msg=$this->lng_get_msg_txt("password_does_not_match","La contraseña no coincide");	
			return false;	
		}
		if($pok===$pass1){
			return $pok;	
		}
		$msg=$this->lng_get_msg_txt("password_does_not_match","La contraseña no coincide");	
		return false;	
		
	}
	function check_new_pass($pass,&$msg=""){
		if(!$pass){
			$msg=$this->lng_get_msg_txt("invalid_password","Contraseña no válida");	
			return false;	
		}
		if(!is_string($pass)){
			$msg=$this->lng_get_msg_txt("invalid_password","Contraseña no válida");	
			return false;	
		}
		$pass=trim($pass);
		$invalid=array("/t","/n","/r"," ","'","\"");
		foreach($invalid as $ch){
			if(strpos($pass,$ch)!==false){
				$msg=$this->lng_get_msg_txt("password_contains_invalid_characters","La contraseña contiene caracteres no válidos");	
				return false;	
			}
				
		}
		
		if(strlen($pass)>$this->pass_max_len){
			$a=array("n"=>$this->pass_max_len);	
			$msg=$this->lng_get_msg_txt("password_cant_have_more_than_x_chars","La contraseña no debe tener más de %n% caracteres",$a);	
			return false;	
			
		}
		
		if(strlen($pass)<$this->pass_min_len){
			$a=array("n"=>$this->pass_min_len);	
			$msg=$this->lng_get_msg_txt("password_must_have_at_least_x_chars","La contraseña debe tener al menos %n% caracteres",$a);	
			return false;	
			
		}
		if($this->must_contain_lowers){
			if(!$this->has_lower($pass)){
				$msg=$this->lng_get_msg_txt("password_must_contain_lowercase","La contraseña debe contener minúsculas",$a);	
				return false;	
			}
		}
		if($this->must_contain_uppers){
			if(!$this->has_upper($pass)){
				$msg=$this->lng_get_msg_txt("password_must_contain_uppercase","La contraseña debe contener mayúsculas",$a);	
				return false;	
			}
		}
		if($this->must_contain_numbers){
			if(!$this->has_number($pass)){
				$msg=$this->lng_get_msg_txt("password_must_contain_numbers","La contraseña debe contener números",$a);	
				return false;	
			}
		}
		return $pass;
	}
	final function __get_priv_man(){
		return $this->man; 	
	}
	
	//init
	final function init($man){
		$this->man=$man;
		$ap=$man->mainap;
		
		$this->set_mainap($ap);
		$this->set_lngmsgsmancod("user");

					
	}

	
}
?>