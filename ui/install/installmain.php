<?php
class mwmod_mw_ui_install_installmain extends mwmod_mw_uitemplates_sbadmin_main{
	var $session_var_name="install_credentials";
	private $_cfg_data;
	var $token="asdfw3rfsdafasf5t54sdfhsdgfgasfgsdfgwfgar44";
	function __construct($ap){
		$this->set_mainap($ap);	
		$this->subinterface_def_code="def";
		$this->url_base_path="/install/";
	}
	function add_mnu_items_toplinks($mnu){
		
	}
	/*
	function exec_page_nav_top_links($subinterface){
		echo "sddfsdfsd";
	}
	*/
	
	
	function add_mnu_items_side($mnu){
		$msg_man=$this->mainap->get_msgs_man_common();
		$this->add_sub_interface_to_mnu_by_code($mnu,"def,adminuser,loginasuser,phpinfo");
		
		if($this->install_credentials_ok()){
			$mnu->add_new_item("logout",$msg_man->get_msg_txt("logout","Cerrar sesión"),"index.php?logout=true");	
		}
	
		
	}
	
	function add_mnu_items($mnu){
		/*
		$this->add_sub_interface_to_mnu_by_code($mnu,"def,adminuser");
		
		if($this->install_credentials_ok()){
			$mnu->add_new_item("logout",$this->get_msg("Cerrar sesión"),"index.php?logout=true");	
		}
		*/
	}
	function logout(){
		$_SESSION[$this->session_var_name]=array();
		$_SESSION[$this->session_var_name]["ok"]=false;
		
	}
	function login($pass){
		$_SESSION[$this->session_var_name]=array();
		$_SESSION[$this->session_var_name]["ok"]=false;
	}
	function exec_login_and_user_validation(){
		if($_REQUEST["logout"]??null){
			return $this->logout();	
		}
		if(!is_array($_SESSION[$this->session_var_name]??null)){
			$_SESSION[$this->session_var_name]=array();
			$_SESSION[$this->session_var_name]["ok"]=false;
		}
		if($token_input=$_REQUEST["login_token"]??null){
			if($token_input===$this->get_cfg_data("pass")){
				if($this->get_cfg_data("allowed")){
					$_SESSION[$this->session_var_name]["ok"]=$this->token;	
					$_SESSION[$this->session_var_name]["sessionvalidtime"]=strtotime(date("Y-m-d H:i:s")." + 15 minutes");
				}
					
			}
		}else{
			if(	$this->install_credentials_ok()){
				$_SESSION[$this->session_var_name]["sessionvalidtime"]=strtotime(date("Y-m-d H:i:s")." + 15 minutes");	
			}
		}
		
	}
	function install_credentials_ok(){
		if(!$this->install_ui_enabled()){
			return false;
		}
		if(!$_SESSION[$this->session_var_name]){
			return false;	
		}
		if(!is_array($_SESSION[$this->session_var_name])){
			return false;	
		}
		if(!$token=$_SESSION[$this->session_var_name]["ok"]){
			return false;
		}
		if(!$t=$_SESSION[$this->session_var_name]["sessionvalidtime"]+0){
			return false;
		}
		if($t<time()){
			return false;	
		}
		if($token===$this->token){
			return true;	
		}
		
	}
	function load_all_subinterfases(){
		$si=$this->add_new_subinterface(new mwmod_mw_ui_install_uiinstall("def",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_install_adminuser("adminuser",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_install_loginasuser("loginasuser",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_install_phpinfo("phpinfo",$this));
	}

	function admin_user_ok(){
		return true;
	}
	function get_ui_title_for_nav(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return $this->get_page_title();	
		}

		return $this->get_page_title()." - ".$msg_man->get_msg_txt("install","Instalación");	
	}
	
	final function get_cfg_data($cod=false){
		if(!isset($this->_cfg_data)){
			$this->_cfg_data=array();
			if($file=$this->mainap->get_file_path_if_exists("install.php","cfg","instance")){
				ob_start();
				include $file;
				ob_end_clean();
				if(is_array($data)){
					$this->_cfg_data=$data;
				}
			}
		}
		if(!$cod){
			return $this->_cfg_data;	
		}else{
			return mw_array_get_sub_key($this->_cfg_data,$cod);
		}
	}
	function install_ui_enabled(){
		if(!$this->get_cfg_data("allowed")){
			return false;
		}
		if(!$this->get_cfg_data("all_ips")){
			$ips=$this->get_cfg_data("allowed_ips");
			if(!is_array($ips)){
				return false;	
			}
			if(!in_array($_SERVER['REMOTE_ADDR'],$ips)){
				return false;	
			}
			
		}
		return true;
			
	}
	function __call($a,$b){
		return false;	
	}
	
}
?>