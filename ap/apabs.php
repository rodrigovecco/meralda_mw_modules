<?php
abstract class  mwmod_mw_ap_apabs extends mw_baseobj{
	private $pathman;
	//paths
	private $root_path;
	private $system_path;
	private $instance_path;
	private $public_path;
	public $outofserviceURL="/outofservice";
	
	private $userfiles_sub_path="appdata";
	private $userfilespublic_sub_path="data";
	
	
	//private $template;
	private $___submanagers=array();
	private $____lng_order;
	
	private $autoloadermanager;
	private $adminusermanager;
	private $usermanager;
	
	private $__temporal_error_manager;
	
	private $templates_man;
	
	private $cfg;
	
	private $admin_ui;
	private $jsonCfgMan;
	function create_jsonCfgMan(){
		$man=new mwmod_mw_data_json_mancuspath("cfg/json",$this->get_path("instance"));
		return $man;
	}


	final function __get_priv_jsonCfgMan(){
		if(!isset($this->jsonCfgMan)){
			$this->jsonCfgMan=$this->create_jsonCfgMan();	
		}

		return $this->jsonCfgMan; 	
	}
	function after_init(){
		if($cfg=$this->get_cfg()){
			if($timezone=$cfg->get_value("timezone")){
				date_default_timezone_set($timezone);	
			}
		}
	
	}
	function load_admin_ui(){
		if(!$cfg=$this->get_cfg()){
			return false;	
		}
		if(!$cod=$cfg->get_value("adminui")){
			return false;
		}
		if(!$man=$this->get_submanager($cod)){
			return false;	
		}
		if(is_a($man,"mwmod_mw_ui_main_uimainabs")){
			return $man;	
		}
	}
	final function __get_priv_admin_ui(){
		if(!isset($this->admin_ui)){
			if(!$this->admin_ui=$this->load_admin_ui()){
				$this->admin_ui=false;	
			}
		}
		return $this->admin_ui;
	}
	
	function on_shutdown(){
			
	}
	function get_logs_path_man(){
		return $this->get_sub_path_man("mwaplogs","root");
	}
	function create_cfg(){
		$file=$this->get_path("system")."/cfg.ini";
		$srcs=array(
			new mwmod_mw_data_cfg_src_ini("def",$this->get_path("system")."/cfg.ini"),
			new mwmod_mw_data_cfg_src_ini("cus",$this->get_path("instance")."/cfg.ini"),
		);
		$man=new mwmod_mw_data_cfg_man($srcs,$this);
		return $man;	
	}
	final function get_cfg(){
		return $this->__get_priv_cfg();	
	}
	final function __get_priv_cfg(){
		if(!isset($this->cfg)){
			$this->cfg=$this->create_cfg();	
		}
		return $this->cfg; 	
	}
	
	function create_templates_man(){
		$m=new mwmod_mw_templates_templatesman();
		return $m;	
	}
	final function set_templates_man($man){
		$this->templates_man=$man;

	}
	final function get_templates_man(){
		if(!isset($this->templates_man)){
			$this->templates_man=$this->create_templates_man();
		}
		return $this->templates_man;
	}
	
	final function init_paths_man(){
		if(isset($this->pathman)){
			return;				
		}
		$this->pathman=array();
		$codes=array(
			"system",
			"root",
			"instance",
			"public",
			"userfiles",
			"userfilespublic"
		
		);
		foreach ($codes as $cod){
			$this->pathman[$cod]=new mwmod_mw_ap_paths($this,$cod); 	
		}
	}
	function get_sub_path_man($subpath,$mode="userfiles"){
		if(!$mode){
			return false;	
		}
		if(!$man=$this->get_path_man($mode)){
			return false;	
		}
		//echo get_class($man);
		return $man->get_sub_path_man($subpath);
	}
	final function get_path_man($cod){
		$this->init_paths_man();
		if($cod){
			return $this->pathman[$cod];	
		}
		return $this->pathman;
		
	}
	final function __get_priv_pathman(){
		$this->init_paths_man();
		return $this->pathman;
	}
	
	function get_debug_info(){
		$r=array();
		$r["class"]=get_class($this);
		$r["paths"]=array();
		if($items=$this->__get_priv_pathman()){
			$d=array();
			foreach ($items as $cod=>$item){
				$d[$cod]=$item->get_debug_info();	
			}
			$r["paths"]=$d;
		}
		return $r;
	}
	
	//error info
	final function get_temporal_error_manager(){
		return $this->__temporal_error_manager;	
	}
	final function set_temporal_error_manager($errorman=false){
		if(!$errorman){
			unset($this->__temporal_error_manager);	
			return false;
		}
		$this->__temporal_error_manager=$errorman;
		return 	$this->__temporal_error_manager;
	}
	function get_lngmsgsmancod(){
		return "def";
	}
	
	//sub command
	function allow_submancmd(){
		return false;	
	}
	function debug_exec_submancmd(){
		$r=array();
		if(!$url_p=parse_url($_SERVER['REQUEST_URI'])){
			return false;	
		}
		if(!$url=$url_p['path']){
			return false;	
		}
		$r["url"]=$url;
		if(!$this->allow_submancmd()){
			$r["msg"]="ap no acepta";
			return $r;	
		}
		
		if(!$base=$this->get_submanagerexeccmdurl()){
			return $r;	
		}
		$pos=strpos($url,$base);
		if($pos===false){
			$r["msg"]="url no valida";
			return $r;	
		}
		$len=strlen($base);
		if(!$rest=substr($url,($pos+$len+1))){
			$r["msg"]="url no valida";
			return $r;	
		}
		
		$a=explode("/",$rest);
		if(sizeof($a)<2){
			$r["msg"]="url no valida";
			return $r;	
		}
		$mancod=array_shift($a);
		$cmdcod=array_shift($a);
		if(!$mancod=$this->check_str_key_alnum_underscore($mancod)){
			$r["msg"]="man no valido";

			return $r;		
		}
		$r["mancod"]=$mancod;
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			$r["msg"]="cmd no valido";

			return $r;		
		}
		$r["cmdcod"]=$cmdcod;
		$filename=false;
		$params=array();
		
		if(!$man=$this->get_submanager($mancod)){
			$r["msg"]="man no valido";
			return $r;	
		}
		if(!$man->__accepts_exec_cmd_by_url()){
			$r["msg"]="man no acepta";
			return $r;	
		}
		$method="exec_getcmd_".$cmdcod;
		$r["method"]=$method;
		if(!method_exists($man,$method)){
			$r["msg"]="method no existe";
			return $r;		
		}
		if($s=sizeof($a)){
			if(round($s/2)!=($s/2)){
				$filename=array_pop($a);	
			}
		}
		if($s=sizeof($a)){
			for($x=0;$x<$s;$x++){
				$pcod=$a[$x];
				$x++;
				$pval=$a[$x];
				$params[$pcod]=$pval;
			}
		}
		$r["params"]=$params;
		$r["filename"]=$filename;
		return $r;
		//return $man->$method($params,$filename);
		
	}
	
	function exec_submancmd(){
		return $this->exec_submancmd_with_options(false);
		
		/*
		ob_start();
		if(!$this->allow_submancmd()){
			return false;	
		}
		if(!$url_p=parse_url($_SERVER['REQUEST_URI'])){
			return false;	
		}
		if(!$url=$url_p['path']){
			return false;	
		}
		
		
		if(!$base=$this->get_submanagerexeccmdurl()){
			return false;	
		}
		$pos=strpos($url,$base);
		if($pos===false){
			return false;	
		}
		$len=strlen($base);
		if(!$rest=substr($url,($pos+$len+1))){
			return false;		
		}
		
		$a=explode("/",$rest);
		if(sizeof($a)<2){
			return false;	
		}
		$mancod=array_shift($a);
		$cmdcod=array_shift($a);
		if(!$mancod=$this->check_str_key_alnum_underscore($mancod)){
			return false;	
		}
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			return false;	
		}
		$filename=false;
		$params=array();
		
		if(!$man=$this->get_submanager($mancod)){
			return false;
		}
		if(!$man->__accepts_exec_cmd_by_url()){
			return false;
		}
		$method="exec_getcmd_".$cmdcod;
		if(!method_exists($man,$method)){
			return false;	
		}
		if($s=sizeof($a)){
			if(round($s/2)!=($s/2)){
				$filename=array_pop($a);	
			}
		}
		if($s=sizeof($a)){
			for($x=0;$x<$s;$x++){
				$pcod=$a[$x];
				$x++;
				$pval=$a[$x];
				$params[$pcod]=$pval;
			}
		}
		ob_end_clean();
		return $man->$method($params,$filename);
		*/
		
	}
	
	private function exec_submancmd_with_options($validateUser=false){
		ob_start();
		$allowchecked=false;
		if(!$validateUser){
			if(!$this->allow_submancmd()){
				return false;	
			}
			$allowchecked=true;
		}
		if(!$url_p=parse_url($_SERVER['REQUEST_URI'])){
			return false;	
		}
		if(!$url=$url_p['path']){
			return false;	
		}
		
		
		if(!$base=$this->get_submanagerexeccmdurl()){
			return false;	
		}
		$pos=strpos($url,$base);
		if($pos===false){
			return false;	
		}
		$len=strlen($base);
		if(!$rest=substr($url,($pos+$len+1))){
			return false;		
		}
		
		$a=explode("/",$rest);
		if(sizeof($a)<2){
			return false;	
		}
		$mancod=array_shift($a);
		$cmdcod=array_shift($a);
		if(!$mancod=$this->check_str_key_alnum_underscore($mancod)){
			return false;	
		}
		if(!$cmdcod=$this->check_str_key_alnum_underscore($cmdcod)){
			return false;	
		}
		$filename=false;
		$params=array();
		
		if(!$man=$this->get_submanager($mancod)){
			return false;
		}
		if(!$man->__accepts_exec_cmd_by_url()){
			return false;
		}
		$method="exec_getcmd_".$cmdcod;
		if(!method_exists($man,$method)){
			return false;	
		}
		
		if($s=sizeof($a)){
			if(round($s/2)!=($s/2)){
				$filename=array_pop($a);	
			}
		}
		if($s=sizeof($a)){
			for($x=0;$x<$s;$x++){
				$pcod=$a[$x];
				$x++;
				$pval=$a[$x];
				$params[$pcod]=$pval;
			}
		}
		if($validateUser){
			//$methodvalidateuser="checkGetCmdOmitValidateUser";
			if(method_exists($man,"checkGetCmdOmitValidateUser")){
				if($man->checkGetCmdOmitValidateUser($cmdcod,$params,$filename)){
					$validateUser=false;
				}
			}
				
		}

		
		if($validateUser){
			$this->exec_user_validation();	
		}
		
		if(!$allowchecked){
			if(!$this->allow_submancmd()){
				return false;	
			}
		}
		
		ob_end_clean();
		return $man->$method($params,$filename);
		
	}
	
	function exec_submancmd_and_user_validation(){
		return $this->exec_submancmd_with_options(true);
		
	}
	

	function get_submanagerexeccmdurl(){
		return "/get";	
	}


	/*
	
	*/
	//user
	function set_lng_by_user(){
		if(!$user=$this->get_current_user()){
			return false;	
		}
		$user->set_ap_lng();
	}
	function exec_login_and_user_validation(){
		if($man=$this->get_user_manager()){
			return $man->exec_login_and_user_validation();	
		}
		
	}
	function exec_user_validation(){
		if($man=$this->get_user_manager()){
			return $man->exec_user_validation();	
		}
		
	}
	function get_admin_current_user(){
		if($man=$this->get_admin_user_manager()){
			return $man->get_current_user();	
		}
	}
	
	function current_admin_user_allow($action,$params=false){
		if($man=$this->get_admin_user_manager()){
			return $man->allow($action,$params);	
		}
	}
	
	
	
	function current_user_allow($action,$params=false){
		if($man=$this->get_user_manager()){
			return $man->allow($action,$params);	
		}
	}
	
	function get_current_user(){
		if($man=$this->get_user_manager()){
			return $man->get_current_user();	
		}
	}
	
	final function get_admin_user_manager(){
		if(!isset($this->adminusermanager)){
			$this->adminusermanager=$this->create_admin_user_manager();
		}
		return $this->adminusermanager;
	}
	final function __get_priv_usermanager(){
		return $this->get_user_manager(); 	
	}

	final function get_user_manager(){
		if(!isset($this->usermanager)){
			$this->usermanager=$this->create_user_manager();
		}
		return $this->usermanager;
	}
	function create_admin_user_manager(){
		return $this->get_user_manager();
	}
	function create_user_manager(){
		return false;
	}
	
	function after_connect_db_fail(){
		ob_end_clean();
		header("Location: ".$this->outofserviceURL);
		die("<p>DB not connected!</p>");
			
	}
	function after_connect_db_ok(){
		$this->after_connect_db_ok_sub();
	}
	function setDBtimezoneAfterConnect(){
		if($man=$this->get_submanager("db")){
			$connection=$man->get_link();
			 $timeZone = date('P'); // Example: +05:30
	        if (preg_match('/^[+-](0[0-9]|1[0-2]):[0-5][0-9]$/', $timeZone)) {
	            $stmt = $connection->prepare("SET time_zone = ?");
	            $stmt->bind_param("s", $timeZone);
	            $stmt->execute();
	            $stmt->close();
	        } 
			//mysqli_query($connection, "SET time_zone = '" . date('P') . "'");

		}
	}
	function after_connect_db_ok_sub(){
		$this->setDBtimezoneAfterConnect();
		
		
		
	}
	function connect_db(){
		
		if(!$file=$this->get_file_path_if_exists("db.php","cfg","instance")){
			return false;	
		}
		ob_start();
		include $file;
		ob_end_clean();
		
		if(!$man=$this->get_submanager("db")){
			return false;
		}
		if($man->set_db_cfg($data)){
			return $man->connect();	
		}
	}
	function get_msgs_man_common(){
		return $this->get_msgs_man("common");	
	}
	function get_msgs_man($cod=false){
		if(!$man=$this->get_submanager("lng")){
			return false;
		}
		return $man->get_msgs_man($cod);
			
	}
	function get_msg(){
		if(!$man=$this->get_submanager("lng")){
			return false;
		}
		$msgslist=func_get_args();
		return $man->get_msg_by_list($msgslist,$this);
	}
	
	//lng
	function get_current_lng_man(){
		if(!$man=$this->get_submanager("lng")){
			return false;
		}
		return $man->get_current_lng_man();
	
	}

	function get_lng_order(){
		return false;	
	}
	private function ___set_lng_indexes_for_codes(){
		if(isset($this->____lng_order)){
			return;	
		}
		$this->____lng_order=false;
		if($a=$this->get_lng_order()){
			$this->____lng_order=array();
			foreach($a as $index=>$code){
				$this->____lng_order[$code]=$index;	
			}
		}
		return;
	}
	final function _get_lng_index_for_codes(){
		$this->___set_lng_indexes_for_codes();
		return $this->____lng_order;
	}
	
	final function _get_lng_index_for_code($code){
		$this->___set_lng_indexes_for_codes();
		if($this->____lng_order){
			return $this->____lng_order[$code];	
		}
		return false;
	}

	function get_msg_by_code(){
		if(!$man=$this->get_submanager("lng")){
			return false;
		}
		$msgslist=func_get_args();
		return $man->get_msg_by_list_and_code($msgslist,$this);
	}
	function after_set_lng_manager($man){
		$man->add_lng_by_code("es","Español");
	}
	
	//submanagers
	function create_submanagerbydefmethod($cod){
		$subman=false;
		//inclyue file que da valor a $subman;	
	}
	function create_submanager($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;		
		}
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
	
		$method="create_submanager_".$cod;
		$subman=false;
		if(method_exists($this,$method)){
			if(!$subman=$this->$method()){
				return false;	
			}
		}else{
			if(!$subman=$this->create_submanagerbydefmethod($cod)){
				return false;	
			}
		}
		if($subman){
			if(is_a($subman,"mw_apsubbaseobj")){
				$subman->__set_ap_submanager_cod($cod);	
			}
			return $subman;
		}
		return false;		
	}
	final function get_submanager($cod){
		if(!$cod){
			return false;	
		}
		if(!is_string($cod)){
			return false;		
		}
		if(isset($this->___submanagers[$cod])){
			return 	$this->___submanagers[$cod];
		}
		if($man=$this->create_submanager($cod)){
			$this->___submanagers[$cod]=$man;
			return 	$this->___submanagers[$cod];	
		}
	}
	function create_submanager_fileman(){
		
		$man=new mwmod_mw_helper_fileman($this);
		return $man;	
	}
	function create_submanager_lng(){
		$man=new mwmod_mw_lng_lngman($this);
		$this->after_set_lng_manager($man);
		return $man;	
	}
	function create_submanager_db(){
		$man=new mwmod_mw_db_mysqli_dbman($this);
		return $man;	
	}
	
	//tamplate
	/*
	function create_subtemplate($cod){
		if($t=$this->get_template()){
			return $t->create_subtemplate($cod);	
		}
	}
	function get_subtemplate($cod){
		if($t=$this->get_template()){
			return $t->get_subtemplate($cod);	
		}
	}
	function get_input_template(){
		if($t=$this->get_template()){
			return $t->get_input_template();	
		}
	}
	function get_admininterface_template(){
		if($t=$this->get_template()){
			return $t->get_admininterface_template();	
		}
	}
	function get_adminsubinterface_template(){
		if($t=$this->get_template()){
			return $t->get_adminsubinterface_template();	
		}
	}
	*/
	/*
	function create_template(){
		
		//falta!!!!
		$m=new mw_template($this);
		return $m;	
	}
	final function get_template(){
		if(isset($this->template)){
			return 	$this->template;
		}
		if($m=$this->create_template()){
			$this->template=$m;
			return 	$this->template;	
		}
	}
	*/
	//autoloader
	/*
	function create_autoloadermanager(){
		$man=mw_get_autoload_manager();
		return $man;
		
	}
	final function get_autoloadermanager(){
		if(isset($this->autoloadermanager)){
			return 	$this->autoloadermanager;
		}
		if($m=$this->create_autoloadermanager()){
			$this->autoloadermanager=$m;
			return 	$this->autoloadermanager;	
		}
	}
	final function do_autoload($class_name){
		if(!$m=$this->get_autoloadermanager()){
			return false;
		}
		return $m->do_autoload($class_name);
	}
	*/
	//paths
	function get_file_path_if_exists($filename,$subpath,$mode="userfiles"){
		if(!$p=$this->get_file_path($filename,$subpath,$mode)){
			return false;
		}
		if(is_file($p)){
			if(file_exists($p)){
				return $p;
			}
		}
	}
	function get_file_path($filename,$subpath,$mode="userfiles"){
		if(!$filename=basename($filename)){
			return false;	
		}
		if(!$p=$this->get_sub_path($subpath,$mode)){
			
			return false;	
		}
		return $p."/".$filename;
			
	}
	function get_sub_path($subpath,$mode="userfiles"){
		if(!is_string($subpath)){
			return false;	
		}
		$subpath=trim($subpath);
		$subpath=trim($subpath,"/");
		$subpath=trim($subpath);
		
		if(!$subpath){
			return false;	
		}
		if(strpos($subpath,".")!==false){
			return false;	
		}
		
		if(!$p=$this->get_path($mode)){
			return false;	
		}
		return $p."/".$subpath;
		
	}

	function get_path($mode="userfiles"){
		switch ($mode){
			case "system":
				return $this->get_system_path();	
			case "root":
				return $this->root_path;	
			case "instance":
				return $this->instance_path;	
			case "public":
				return $this->public_path;	
			case "userfiles":
				return $this->root_path."/".$this->__get_priv_userfiles_sub_path();	
			case "userfilespublic":
				return $this->public_path."/".$this->__get_priv_userfilespublic_sub_path();	
			
		}
		
	}
	function get_public_userfiles_url_path(){
		return "/".$this->__get_priv_userfilespublic_sub_path();
	}
	final function get_system_path(){
		return 	$this->__get_priv_system_path();
	}
	final function check_public_path(){
		if($this->public_path){
			return true;	
		}
	}
	final function check_root_path(){
		if($this->root_path){
			return true;	
		}
	}
	final function set_instance_path($path){
		if($this->instance_path){
			return false;	
		}
		if(!$p=realpath($path)){
			return false;	
		}
		$this->instance_path=$p;
		
		return true;
	}
	final function set_system_path($path){
		if($this->system_path){
			return false;	
		}
		if(!$p=realpath($path)){
			return false;	
		}
		$this->system_path=$p;
		
		return true;
	}
	final function set_root_path($path){
		if($this->check_root_path()){
			return false;	
		}
		if(!$p=realpath($path)){
			return false;	
		}
		$this->root_path=$p;
	
		return true;
	}
	final function set_public_path($path){
		if($this->check_public_path()){
			return false;	
		}
		if(!$p=realpath($path)){
			return false;	
		}
		$this->public_path=$p;
		

		return true;
	}
	final function set_userfiles_sub_path($priv=false,$public=false){
		if($priv){
			if($priv=basename($priv)){
				$this->userfiles_sub_path=$priv;	
			}
		}
		if($public){
			if($public=basename($public)){
				$this->userfilespublic_sub_path =$public;	
			}
		}
	}
	
	
	
	
	//priv
	/*
	final function __get_priv_template(){
		return $this->template; 	
	}
	final function __get_priv_autoloadermanager(){
		return $this->autoloadermanager; 	
	}
		private $userfiles_sub_path="userfiles";
	private $userfilespublic_sub_path="userfiles";

	*/
	final function __get_priv_userfiles_sub_path(){
		return $this->userfiles_sub_path; 	
	}
	final function __get_priv_userfilespublic_sub_path(){
		return $this->userfilespublic_sub_path; 	
	}
	final function __get_priv_instance_path(){
		return $this->instance_path; 	
	}
	final function __get_priv_system_path(){
		return $this->system_path; 	
	}
	final function __get_priv_root_path(){
		return $this->root_path; 	
	}
	final function __get_priv_public_path(){
		return $this->public_path; 	
	}
	
}

?>