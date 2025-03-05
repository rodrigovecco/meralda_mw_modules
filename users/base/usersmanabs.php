<?php
//
abstract class mwmod_mw_users_base_usersmanabs extends mw_apsubbaseobj{
	private $sessionvarname;
	var $current_user_cookie_name="mw_current_user";
	var $current_user_cookie_enabled=true;
	
	var $userValidationDone=0;
	private $bruteForceMan;
	public $login_security_sess_var_name="_user_login_sec_params";
	public $use_login_session_token=false;
	public $disable_login_after_fail=false;
	public $disable_login_after_fail_tries=3;
	public $disable_login_after_fail_timeout=5;//seconds client will receive data plus 1 second
	private $login_js_response;

	public $disableUserIPsessionValidations=false;
	
	
	private $tokensMan;
	
	private $usersinitdone;
	private $users=array();
	private $_all_users;
	private $currentuser;
	private $_active_users;
	
	private $tblman;
	private $tblname;
	private $users_by_idname=array();
	
	private $_treedataman;
	
	
	private $_permission_man;
	private $_rols_man;
	
	private $_main_admin_user;
	
	private $_userdataman;
	private $_user_mailer;
	private $_pass_policy;
	private $_groups_man;
	var $login_fail_msg;
	private $manRelPathRoot="users";
	private $userRelPath="user";
	private $usersList;
	function usersListCreate($list){
		$tblman=$this->get_tblman();
		$query=$tblman->new_query();
		$query->select->add_select("id");
		$query->select->add_select("name");
		$query->select->add_select("complete_name");
		if($data=$query->get_array_data_from_sql()){
			foreach($data as $id=>$d){
				$o=new mwmod_mw_util_itemsbycod_data($id,$d);
				$o->name=$d["name"]." ".$d["complete_name"];
				$list->add_item($o);
			}
		}
	}
	final function __get_priv_usersList(){
		if(!isset($this->usersList)){
			$this->usersList=new mwmod_mw_util_itemsbycod();
			$this->usersListCreate($this->usersList);
			
		}
		return $this->usersList;
		
	}
	final function __get_priv_tokensMan(){
		if(!isset($this->tokensMan)){
			if(!$this->tokensMan=$this->createtokensMan()){
				return false;	
			}
		}
		return $this->tokensMan;
		
	}
	function createtokensMan(){
		//mwmod_mw_users_tokens_man
		return false;	
	}
	
	
	
	function login_input_user_name(){
		$i=new mwmod_mw_datafield_input("login_userid",$this->lng_common_get_msg_txt("user","Usuario"));
		$i->set_required();
		return $i;
		
	}
	function login_input_password(){
		$i=new mwmod_mw_datafield_password("login_pass",$this->lng_common_get_msg_txt("password","Contraseña"));
		$i->set_required();
		return $i;
		
	}
	function login_input_token(){
		$i=new mwmod_mw_datafield_hidden("login_token");
		$i->set_value($this->get_login_session_token());
		return $i;
		
	}
	function add_login_inputs2cr($cr,&$user_name_input,&$pass_input){
		$user_name_input=$this->login_input_user_name();
		$cr->add_item($user_name_input);
		$pass_input=$this->login_input_password();
		$cr->add_item($pass_input);
		if($this->login_session_token_enabled()){
			$i=$this->login_input_token();
			$cr->add_item($i);	
		}
		
	}

	function get_data_params_debug_info(){
		$r=array(
			"sessionvarname"=>$this->sessionvarname,
			"current_user_cookie_name"=>$this->current_user_cookie_name,
			"login_security_sess_var_name"=>$this->login_security_sess_var_name,
			"tblname"=>$this->tblname,
			"manRelPathRoot"=>$this->manRelPathRoot,
			"userRelPath"=>$this->userRelPath,
			"__get_man_path"=>$this->__get_man_path(),
		);
		return $r;	
	}
	final function setRelPathsAndCods($pathForMan="users",$pathForUser="user"){
		$this->manRelPathRoot=$pathForMan;
		$this->userRelPath=$pathForUser;
	}
	final function initAsSecondaryUsersMan($ap,$tbl,$pathForMan="users",$pathForUser="user"){
		$sessvarname="__current_".$pathForUser."_data";
		$this->init_tbl_mode($ap,$tbl,$sessvarname);
		$this->setAsSecondaryUserMan($pathForMan,$pathForUser);
		
		//__current_user_data
	}
	final function setAsSecondaryUserMan($pathForMan="users",$pathForUser="user"){
		$this->setRelPathsAndCods($pathForMan,$pathForUser);
		$this->current_user_cookie_name="mw_current_".$pathForUser;
		$this->login_security_sess_var_name="_".$pathForUser."_login_sec_params";
	}
	//final function init_tbl_mode($ap,$tbl,$sessionvar="__current_user_data"){
	
	function __get_man_path(){
		$p=$this->manRelPathRoot."/man";
		return $p;
			
	}
	final function __get_priv_manRelPathRoot(){
		return $this->manRelPathRoot;
		
	}
	final function __get_priv_userRelPath(){
		return $this->userRelPath;
		
	}
	function get_user_rel_path($user){
		$p=$this->userRelPath."/".$user->get_id();
		return $p;
		
	}
	
	
	final function __get_priv_login_js_response(){
		if(!isset($this->login_js_response)){
			$this->login_js_response=new mwmod_mw_jsobj_obj();	
		}
		return $this->login_js_response; 	
	}
	function set_login_response($msg="",$ok=false){
		$js=$this->__get_priv_login_js_response();
		$js->set_prop("ok",$ok);
		$js->set_prop("msg",$msg);
		$this->login_fail_msg=$msg;
		return $js;
			
	}
	
	function enable_login_session_token($val=true){
		if($val){
			$this->use_login_session_token=true;	
		}else{
			$this->use_login_session_token=false;	
		}
	}
	
	function register_permission_request_if_enabled($permision){
		return false;	
	}
	function real_escape_string($txt){
		if(!$tblman=$this->get_tblman()){
			return false;	
		}
		return $tblman->real_escape_string($txt);
			
	}
	function get_users_with_mail($mail,$onlyactive=true){
		if(!$mail=trim($mail)){
			return false;	
		}
		if(!mw_checkemail($mail)){
			return false;	
		}
		$mail=strtolower($mail);
		if(!$q=$this->new_users_query()){
			return false;	
		}
		if($onlyactive){
			$q->where->add_where_crit("active",1);	
		}
		$mail=$this->real_escape_string($mail);
		//$q->where->add_where("LOWER(email)='$mail'");
		$q->where->add_where("LOWER(name)='$mail'");
		return $this->get_users_by_query($q);
		//return $q;
		
	}
	
	//creación de objetos
	final function get_groups_man(){
		if(!isset($this->_groups_man)){
			$this->_groups_man=$this->create_groups_man();	
		}
		return $this->_groups_man;
	}
	function create_groups_man(){
		return false;	
	}
	
	function create_user_data_man(){
		$man= new mwmod_mw_users_userdata($this);
		return $man;
	}
	function create_user_mailer(){
		$man= new mwmod_mw_users_usermailer($this);
		return $man;
	}
	
	function new_user($tblitem){
		$user= new mwmod_mw_users_user($this,$tblitem);
		return $user;
	}
	function create_pass_policy(){
		$man= new mwmod_mw_users_passpolicy($this);
		return $man;
		
	}
	
	//passwords
	function check_crypted_password($password_entered,$password_hash){
		$man=$this->get_pass_policy();
		return $man->check_crypted_password($password_entered,$password_hash);
	}
	function crypt_password($password){
		$man=$this->get_pass_policy();
		return $man->crypt_password($password);
		
	}

	
	
	//editar
	function create_new_user($name,$passinput,$data,&$msg=""){
		//deshabilitada
		return false;
		$msg="";
		
		if(!$tblman=$this->get_tblman()){
			
			return false;	
		}
		if(!$name=$this->check_user_name_loose($name)){
			$msg=$this->get_msg("Nombre de usuario no válido.");
			return false;
		}
		if($user=$this->get_user_by_idname($name)){
			$msg=$this->get_msg("Usuario ya existe.");
			return false;
		}
		if(!is_array($passinput)){
			$msg=$this->get_msg("Contraseña no válida.");
			return false;
		}
		if(!$pass=$passinput["pass"]){
			$msg=$this->get_msg("Contraseña no válida.");
			return false;
		}
		if(!is_string($pass)){
			$msg=$this->get_msg("Contraseña no válida.");
			return false;	
		}
		if(strlen($pass)<5){
			$msg=$this->get_msg("Contraseña no válida.");
			return false;	
		}
		
		if($pass!==$passinput["pass1"]){
			$msg=$this->get_msg("Contraseña no coincide.");
			
			return false;	
		}
		$update=array();
		if($passinput["secpass"]){
			$update["pass"]=crypt($pass,$pass);
			$update["secpass"]=1;
		}else{
			$update["pass"]=$pass;
			$update["secpass"]=0;
		}
		$update["name"]=$name;
		if(is_array($data)){
			/*
			if(mw_checkemail($data["email"])){
				$update["email"]=$data["email"];
			}
			*/
			
			if($data["complete_name"]){
				$update["complete_name"]=$data["complete_name"];	
			}
			
		}
		$update["active"]=0;
		$update["is_main"]=0;
		
		
		if(!$tblitem=$tblman->insert_item_width_id($update)){
			$msg=$this->get_msg("Error al crear usuario.");
			
			return false;	
				
		}
		$msg=$this->get_msg("Usuario creado.");
		return $this->load_and_create_user_by_tblitem($tblitem);
	}
	function create_main_admin_user($name,$passinput,$data,&$msg=""){
		$dataman=$this->get_user_data_man();
		return $dataman->create_main_admin_user($name,$passinput,$data,$msg);
	}
	function get_new_user_data($input,$allowed=false){
		$dm=$this->get_user_data_man();
		return $dm->set_allowed_change_user_data($input,$allowed);
	
	}
	function check_user_name($name){
		if(!$name){
			return false;
		}
		$dm=$this->get_user_data_man();
		return $dm->check_user_name($name);
	}
	//get users: obtener de criterio
	function new_users_query(){
		if(!$man=$this->get_tblman()){
			return false;	
		}
		return $man->new_query();
	}
	
	
	function get_users_rol_ids($ids,$rol){
		if(!is_array($ids)){
			return false;
		}
		$idsok=array();
		foreach($ids as $id1){
			if($id=$id1+0){
				$idsok[$id]=$id;	
			}
		}
		if(!sizeof($idsok)){
			return false;	
		}
		//$this->fix_rols_tbl_fields();
		if(!$cod=$this->get_rol_tbl_field($rol)){
			return false;	
		}
		if(!$tblman=$this->get_tblman()){
			return false;	
		}

		if(!$sql=$tblman->get_sql_load_all_start()){
			return false;	
		}
		$idsstr=implode(",",$idsok);
		$sql.=" where $cod=1 and active=1 and id in ($idsstr) order by name";
		
		if(!$tblitems=$tblman->get_items_by_sql($sql)){
			return false;	
		}
		$r=array();
		foreach($tblitems as $id=>$tblitem){
			if($u=$this->load_and_create_user_by_tblitem($tblitem)){
				$r[$u->get_id()]=$u;	
			}
		}
		return $r;
	
	}
	function get_users_rol($rol){
		if(!$cod=$this->get_rol_tbl_field($rol)){
			return false;	
		}
		if(!$tblman=$this->get_tblman()){
			return false;	
		}
		//$this->fix_rols_tbl_fields();

		if(!$sql=$tblman->get_sql_load_all_start()){
			return false;	
		}
		$sql.=" where $cod=1 and active=1 order by name";
		
		if(!$tblitems=$tblman->get_items_by_sql($sql)){
			return false;	
		}
		$r=array();
		foreach($tblitems as $id=>$tblitem){
			if($u=$this->load_and_create_user_by_tblitem($tblitem)){
				$r[$u->get_id()]=$u;	
			}
		}
		return $r;

	}
	
	


	//roles
	function create_rols_man(){
		return false;	
	}
	final function set_rols_man($man){
		$this->_rols_man=$man;
	}
	final function get_rols_man(){
		if(isset($this->_rols_man)){
			return 	$this->_rols_man;
		}
		if(!$this->_rols_man=$this->create_rols_man()){
			$this->_rols_man=false;	
		}
		
		return 	$this->_rols_man;
	}
	function get_rol_tbl_field($rol){
		if(!$rol=$this->check_str_key($rol)){
			return false;	
		}
		$rol=$this->real_escape_string($rol);
		$cod=$this->get_rol_pref().$rol;
		return $cod;
			
	}
	function get_rol_pref(){
		return 	"rol_";
	}
	function fix_rols_tbl_fields(){
		if($man=$this->get_rols_man()){
			$man->fix_tbl_fields();	
		}
	}
	
	//permisos
	function allow_no_user($action,$params){
		if(!$this->check_str_key($action)){
			return false;	
		}
		if($man=$this->get_permission_man()){
			return $man->allow_no_user_by_permission($action,$params);	
		}
		
		/*
		$method="do_allow_no_user_".$action;
		if(method_exists($this,$method)){
			return $this->$method($params);	
		}else{
			
		}
		*/
		return false;
		
	}
	function allow_list($actions,$params=false){
		if(!is_array($actions)){
			return false;	
		}
		foreach($actions as $action){
			if($this->allow($action,$params)){
				return true;	
			}
		}
		return false;
	}
	function allow($action,$params=false){
		if(is_array($action)){
			return $this->allow_list($action,$params);	
		}
		$this->register_permission_request_if_enabled($action);
		if($user=$this->get_current_user()){
			return $user->allow($action,$params);	
		}
		return $this->allow_no_user($action,$params);
		
	}
	function create_permission_man(){
		return false;	
	}
	final function set_permissions_man($man){
		$this->_permission_man=$man;	
	}
	final function get_permission_man(){
		if(isset($this->_permission_man)){
			return 	$this->_permission_man;
		}
		if(!$this->_permission_man=$this->create_permission_man()){
			$this->_permission_man=false;	
		}
		return 	$this->_permission_man;
	}
	
	//usuario principal
	function load_main_admin_user(){
		if(!$tblman=$this->get_tblman()){
			return false;	
		}
		if(!$sql=$tblman->get_sql_load_all_start()){
			return false;	
		}
		$sql.=" where id='1' and is_main=1 limit 1";
		
	
		return $this->load_and_create_user_by_tblitem($tblman->get_item_by_sql($sql));
	
	}
	final function unset_main_admin_user(){
		unset($this->_main_admin_user);
	}
	final function get_main_admin_user(){
		if(isset($this->_main_admin_user)){
			return $this->_main_admin_user;
		}
		if(!$this->_main_admin_user=$this->load_main_admin_user()){
			$this->_main_admin_user=false;	
		}
		return $this->_main_admin_user;
	}

	
	
	//fuentes de datos
	final function set_tbl_name($tbl){
		$this->tblname=$tbl;
	}
	function get_tblman_code(){
		return $this->__get_priv_tblname();
		
	}
	final function __get_priv_tblman(){
		if(!isset($this->tblman)){
			if(!$this->tblman=	$this->get_init_tblman()){
				$this->tblman=	false;
			}
		}
		return $this->tblman;
	}
	
	function get_init_tblman(){
		if(!$code=$this->get_tblman_code()){
			return false;	
		}
		
		if($db=$this->mainap->get_submanager("db")){
			return $db->get_tbl_manager($code);	
		}
	}

	final function get_tblman(){
		return $this->__get_priv_tblman();
	}
	final function __get_priv_tblname(){
		return $this->tblname; 	
	}
	
	function get_treedata_item($code="data",$path=false){
		if($m=$this->get_treedataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	function can_create_treedata(){
		return true;	
	}

	final function get_treedataman(){
		if(isset($this->_treedataman)){
			return 	$this->_treedataman;
		}
		if($m=$this->get_init_treedataman()){
			$this->_treedataman=$m;
			return 	$this->_treedataman;
		}
	}
	function get_init_treedataman(){
		if(!$this->can_create_treedata()){
			return false;		
		}
		if(!$p=$this->get_treedata_path()){
			return false;		
		}
		
		$m= new mwmod_mw_data_tree_man($p);
		return $m;
	}
	function get_treedata_path(){
		if(!$p=$this->__get_man_path()){
			return false;	
		}
		
		return $p."/data";	
	}

	//sesión
	function current_user_cookie_enabled(){
		return $this->current_user_cookie_enabled;	
	}
	function after_user_changed(){
		if(!$this->current_user_cookie_enabled()){
			return false;
		}
		if(!$cookie_name=$this->current_user_cookie_name){
			return false;	
		}
		
		if(!$val=$this->get_current_user_id()){
			$val="none";
		}
		
		
		if($_COOKIE[$cookie_name]??null!=$val){
			setcookie($cookie_name, $val, time() + (86400 * 30), "/"); // 86400 = 1 day	
			$_COOKIE[$cookie_name]=$val;
		}
			
	}
	
	function logout(){
		$this->unset_currentuser();
		$this->after_user_changed();
	}
	function get_current_user_no_validation_mode(){
		if($user=$this->get_current_user()){
			return $user;
		}
		if(!$id=$this->get_sv_data("id")){
			return false;
		}
		if(!$user=$this->get_user($id)){
			return false;
		}
		return $user;
		
	}
	function exec_user_validation(){
		$this->userValidationDone++;
		$this->unset_currentuser_obj();
		if(!$id=$this->get_sv_data("id")){
			$this->after_user_changed();
			return false;
		}
		if(!$user=$this->get_user($id)){
			$this->after_user_changed();
			return false;
		}
		if($cookie_name=$this->get_token_name()){
			if(!$token=$this->get_sv_data("token")){
				$this->after_user_changed();
				return false;
			}
			if($_COOKIE[$cookie_name]!=$token){
				$this->after_user_changed();
				return false;
			}
				
		}
		if(!$this->disableUserIPsessionValidations){
			
			if(!$ip=$this->get_sv_data("ip")){
				$this->after_user_changed();
				return false;
			}
			
			if($ip!=$_SERVER['REMOTE_ADDR']){
				//die("IP ".$ip." ".$_SERVER['REMOTE_ADDR']);
				$this->after_user_changed();
				return false;	
			}

		}
		

		$r= $this->set_currentuser_obj($user);
		$this->after_user_changed();
		return $r;
	}
	function login($username,$pass){
		$this->unset_currentuser();

		if(!$user=$this->get_user_by_idname($username)){
			$this->after_user_changed();
			return false;
		}
		
		if(!$user->check_login_pass($pass)){
			$this->after_user_changed();
			return false;	
		}
		if(!$user->can_login()){
			$this->after_user_changed();
			return false;	
		}
		
		return $this->login_user($user);
		
		//return $this->set_current_user_and_session_var($user);
	}
	function login_user($user){
		$this->unset_currentuser();
		if(!$user->can_login()){
			$this->after_user_changed();
			return false;	
		}
		
		$this->set_current_session_var($user);
		$user->on_login();
		$this->exec_user_validation();	
		return $user;
	
	}
	
	///session login security
	function login_session_token_enabled(){
		return $this->use_login_session_token;	
	}
	
	function get_login_session_token($generate=true){
		if($t=$this->get_login_security_sess_data("token")){
			return $t;	
		}
		if(!$generate){
			return false;	
		}
		$t=randPass(20);
		$this->set_login_security_sess_data($t,"token");
		return $t;
		
	}
/////////////

	final function get_login_security_sess_var_name(){
		if(!$this->check_str_key($this->login_security_sess_var_name)){
			return false;	
		}
		return $this->login_security_sess_var_name;
	}

	function set_login_security_sess_data($val,$key){
		if(!$sv=$this->get_login_security_sess_var_name()){
			return false;	
		}
		
		if(!is_array($_SESSION[$sv]??null)){
			$_SESSION[$sv]=array();	
		}
		mw_array_set_sub_key($key,$val,$_SESSION[$sv]);
		
		return true;
		
	}
	function get_login_security_sess_data($key=false){
		if(!$sv=$this->get_login_security_sess_var_name()){
			return false;	
		}
		if(!is_array($_SESSION[$sv]??null)){
			return false;	
		}
		return mw_array_get_sub_key($_SESSION[$sv],$key);	
	
	}
	function login_session_token_check($input){
		if(!$input){
			return false;	
		}
		if(!is_string($input)){
			return false;	
		}
		if(!$t=$this->get_login_session_token(false)){
			return false;	
		}
		if($input==$t){
			return true;
		}
		return false;
	}


	function set_disable_login_after_fail($enabled=true,$seconds=5,$tries=3){
		if($enabled){
			$this->disable_login_after_fail=true;	
		}else{
			$this->disable_login_after_fail=false;		
		}
		$seconds=round($seconds+0);
		if($seconds>0){
			$this->disable_login_after_fail_timeout=$seconds;	
		}
		$tries=round($tries+0);
		if($tries>=0){
			$this->disable_login_after_fail_tries=$tries;	
		}
		
		
		
	}
	function disable_login_after_fail_enabled(){
		return $this->disable_login_after_fail;	
	}
	function get_disable_login_after_fail_timeout(){//seconds
		return round($this->disable_login_after_fail_timeout);	
	}
	function session_register_login_fail($msg){
		$this->set_login_response($msg,false);
		$js=$this->__get_priv_login_js_response();
		//$js->set_prop("ok",$ok);
		

		$n=$this->get_login_security_sess_data("fails_num")??0;
		if(!is_numeric($n)){
			$n=0;
		}
		$num=round($n)+1;
		$this->set_login_security_sess_data($num,"fails_num");
		if($this->disable_login_after_fail_enabled()){
			$num=round($this->get_login_security_sess_data("fails_num_after_reallowed"))+1;
			if($num>=$this->disable_login_after_fail_tries){
				$num=0;
				$this->set_login_security_sess_data(1,"login_temp_disabled");
				$to=$this->get_disable_login_after_fail_timeout();
				$date_to=date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." + ".$to." seconds"));
				$this->set_login_security_sess_data($date_to,"login_reallowed_time");
				$js->set_prop("login_not_allowed_timeout.not_allowed",true);	
				$js->set_prop("login_not_allowed_timeout.date",$date_to);	
				$js->set_prop("login_not_allowed_timeout.seconds",$to+1);	
				$js->set_prop("login_not_allowed_timeout.real_seconds",$to);
					
			}
			$this->set_login_security_sess_data($num,"fails_num_after_reallowed");
		}
			
	}
	function session_register_login_ok($msg=""){
		$this->set_login_response($msg,true);
		$this->set_login_security_sess_data(0,"fails_num");	
		$this->set_login_security_sess_data(0,"fails_num_after_reallowed");	
		
		
		$this->set_login_security_sess_data(0,"login_temp_disabled");
		$this->set_login_security_sess_data("","login_reallowed_time");
	}
	function check_login_allowed_by_timeout(){
		$js=$this->__get_priv_login_js_response();
		if(!$this->disable_login_after_fail_enabled()){
			$js->set_prop("test.disable_login_after_fail_enabled",true);	
			return true;	
		}
		if(!$this->get_login_security_sess_data("login_temp_disabled")){
			$js->set_prop("test.login_temp_disabled",true);	
			return true;	
		}
		if(!$date=$this->get_login_security_sess_data("login_reallowed_time")){
			$js->set_prop("test.date",$date);	
			return true;
		}
		
		$time=strtotime($date);
		$js->set_prop("test.time",$time);	
		if($time<=0){
			return true;
		}
		$current=time();
		if($time>$current){
			$to=($time-$current);
			$msg=$this->lng_common_get_msg_txt("login_temporarily_disabled_please_try_again_in_X_seconds","Inicio de sesión temporalmente desactivado. Por favor, inténtalo nuevamente en %S% segundos.",array("S"=>($to+1)));
			$js=$this->set_login_response($msg,false);
			$js->set_prop("login_not_allowed_timeout.not_allowed",true);	
			$js->set_prop("login_not_allowed_timeout.date",$date);
			
			$js->set_prop("login_not_allowed_timeout.seconds",$to+1);	
			$js->set_prop("login_not_allowed_timeout.real_seconds",$to);
			
			return false;
		}
		//$js->set_prop("test.current",$current);	
		$this->set_login_security_sess_data(0,"login_temp_disabled");
		$this->set_login_security_sess_data("","login_reallowed_time");
		$this->set_login_security_sess_data(0,"fails_num_after_reallowed");	
		return true;
		
	}
	
	final function __get_priv_bruteForceMan(){
		if(!isset($this->bruteForceMan)){
			if(!$this->bruteForceMan=$this->loadBruteForceMan()){
				$this->bruteForceMan=false;
			}
		}
		return $this->bruteForceMan;
		
	}
	
	function loadBruteForceMan(){
		return $this->mainap->get_submanager("bruteforce");
	}
	
	function exec_login_and_user_validation(){
		
		if($_REQUEST["logout"]??false){
			return $this->logout();	
		}
		if($_REQUEST["login_userid"]){

			
			
			if(!$this->check_login_allowed_by_timeout()){
				
				return false;
			}
			
			if($this->login_session_token_enabled()){
				if(!$this->login_session_token_check($_REQUEST["login_token"])){
					$this->session_register_login_fail($this->lng_get_msg_txt("invalid_session_token","Clave de control se sesión no válida"));	
					return false;
				}
			}
			
			
			if($bruteForceMan=$this->__get_priv_bruteForceMan()){
				if($bruteForceMan->isEnabled()){
					if($bfBlackList=$bruteForceMan->getCurrentIPBlacklistItem()){
						$this->set_login_response($this->lng_get_msg_txt("unableToLogin","No es posible iniciar sesión"),false);
						return false;
					}
					if($curretIPActivity=$bruteForceMan->getCurrentIPActivityItem()){
						if(!$whilelistItem=$bruteForceMan->getCurrentIPWhitelistItem()){
							if($lockTimeUntil=$curretIPActivity->isLocked()){
								$current=time();
								$date=date("Y-m-d H:i:s",$lockTimeUntil);
								$to=($lockTimeUntil-$current);
								$msg=$this->lng_common_get_msg_txt("login_temporarily_disabled_please_try_again_in_X_seconds","Inicio de sesión temporalmente desactivado. Por favor, inténtalo nuevamente en %S% segundos.",array("S"=>($to+1)));
								$js=$this->set_login_response($msg,false);
								$js->set_prop("login_not_allowed_timeout.not_allowed",true);	
								$js->set_prop("login_not_allowed_timeout.date",$date);
				
								$js->set_prop("login_not_allowed_timeout.seconds",$to+1);	
								$js->set_prop("login_not_allowed_timeout.real_seconds",$to);
								return false;
							}							
						}
						

					}

				}
			}
			
			

			if($this->login($_REQUEST["login_userid"],$_REQUEST["login_pass"])){
				$this->session_register_login_ok("");
				if($bruteForceMan=$this->__get_priv_bruteForceMan()){
					if($bruteForceMan->isEnabled()){
						$bruteForceMan->registerCurrentIPsuccessfullLogin($_REQUEST["login_userid"]);
					}
				}
				return true;
			}else{
				$this->session_register_login_fail($this->lng_common_get_msg_txt("invalid_user_or_password","Usuario o contraseña no válidos"));	
				if($bruteForceMan=$this->__get_priv_bruteForceMan()){
					if($bruteForceMan->isEnabled()){
						if(!$whilelistItem=$bruteForceMan->getCurrentIPWhitelistItem()){
							if($curretIPActivity=$bruteForceMan->registerCurrentIPfailedLogin($_REQUEST["login_userid"])){
								if($lockTimeUntil=$curretIPActivity->isLocked()){
									$current=time();
									$date=date("Y-m-d H:i:s",$lockTimeUntil);
									$to=($lockTimeUntil-$current);
									$msg=$this->lng_common_get_msg_txt("login_temporarily_disabled_please_try_again_in_X_seconds","Inicio de sesión temporalmente desactivado. Por favor, inténtalo nuevamente en %S% segundos.",array("S"=>($to+1)));
									$js=$this->set_login_response($msg,false);
									$js->set_prop("login_not_allowed_timeout.not_allowed",true);	
									$js->set_prop("login_not_allowed_timeout.date",$date);
					
									$js->set_prop("login_not_allowed_timeout.seconds",$to+1);	
									$js->set_prop("login_not_allowed_timeout.real_seconds",$to);
								}
							}

						}
					}
				}
				return false;
			}
		}
		return $this->exec_user_validation();
		
	}
	
	
	final function unset_session_data(){
		if(!$sv=$this->get_sv_var_name()){
			return false;	
		}
		unset($_SESSION[$sv]);
		return true;
	
	}
	final function unset_currentuser(){
		$this->unset_currentuser_obj();
		$this->unset_session_data();
	}
	final function unset_currentuser_obj(){
		unset($this->currentuser);
	}
	function get_current_user_id(){
		if($u=$this->get_current_user()){
			return $u->get_id();	
		}
	}
	final function set_currentuser_obj($user){
		$this->currentuser=$user;
		return $user;
	}
	function create_token_cookie(){
		if($cookie_name=$this->get_token_name()){
			$cookie_value = randPass(20);
			setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day
			$_COOKIE[$cookie_name]=$cookie_value;
			return $cookie_value;	
		}
			
	}
	function get_token_name(){
		if(!$n=$this->get_sv_var_name()){
			return false;	
		}
		return $n."_token";
	}
	
	function set_current_session_var($user){
		$this->unset_currentuser();
		$this->set_sv_data($user->get_id(),"id");
		if($t=$this->create_token_cookie()){
			$this->set_sv_data($t,"token");	
		}
		$this->set_sv_data($_SERVER['REMOTE_ADDR'],"ip");
	}
	function set_current_user_and_session_var($user){
		//no usada
		$this->set_current_session_var($user);
		$this->exec_user_validation();	
	}
	final function get_current_user(){
		return $this->currentuser;
	}
	final function get_sv_var_name(){
		if(!$this->check_str_key($this->sessionvarname)){
			return false;	
		}
		return $this->sessionvarname;
	}
	final function set_sv_data($val,$key){
		if(!$sv=$this->get_sv_var_name()){
			return false;	
		}
		
		if(!is_array($_SESSION[$sv]??null)){
			$_SESSION[$sv]=array();	
		}
		mw_array_set_sub_key($key,$val,$_SESSION[$sv]);
		
		return true;
		
	}
	final function get_sv_data($key=false){
		if(!$sv=$this->get_sv_var_name()){
			return false;	
		}
		if(!isset($_SESSION[$sv])){
			return false;	
		}
		if(!is_array($_SESSION[$sv])){
			return false;	
		}
		return mw_array_get_sub_key($_SESSION[$sv],$key);	
	
	}
	///
	//get and load users as list
	function get_active_users_or_in_idlist($idlist){
		$q=$this->new_users_query();
		$q->order->add_order("complete_name");
		if($strlist=$q->get_str_list_numeric($idlist)){
			$q->where->add_where("(active=1 or id in ($strlist))");		
		}else{
			$q->where->add_where("active=1");
		}
		
		
		return $this->get_users_by_query($q);
		
	}
	
	function load_active_users(){
		$q=$this->new_users_query();
		$q->order->add_order("complete_name");
		$q->where->add_where("active=1");
		
		return $this->get_users_by_query($q);
	
	}
	final function get_active_users(){
		if(!isset($this->_active_users)){
			$this->_active_users=$this->load_active_users();
			
		}
		return $this->_active_users;
		
	}
	final function get_loaded_users(){
		$this->init_users();
		return $this->users;
		
	}
	final function get_loaded_users_by_idname(){
		$this->init_users();
		return $this->users_by_idname;
		
	}
	function do_load_all_useres(){
		if(!$tblman=$this->get_tblman()){
			return false;
		}
		if(!$items=$tblman->get_all_items()){
			return false;	
		}
		return $this->get_users_by_tbl_items($items);//
	}
	final function get_all_useres(){
		$this->load_all_users();
		return $this->_all_users;
	}
	final function load_all_users(){
		if(isset($this->_all_users)){
			return;	
		}
		$this->_all_users=array();
		if(!$items=$this->do_load_all_useres()){
			return;	
		}
		if(is_array($items)){
			$this->_all_users=$items;	
		}
	}
	///////////////////////
	//getting single user
	function get_user_by_idname_case_insensitive($idname){
		if($user=$this->get_user_by_idname($idname)){
			return $user;	
		}
		if(!$idname=trim($idname."")){
			return false;	
		}
		//$idname=$this->real_escape_string($idname);
		$idname=strtoupper($idname);
		$q=$this->new_users_query();
		
		$q->where->add_where_crit("UPPER(name)",$idname);
		if($users=$this->get_users_by_query($q)){
			foreach($users as $u){
				return $u;	
			}
		}
		
	}
	function get_user_by_tbl_item($tblitem){
		if(!$id=	$tblitem->get_id()){
			return false;	
		}
		if($item=$this->get_user_if_loaded($id)){
			return $item;	
		}
		if($item=$this->load_and_create_user_by_tblitem($tblitem)){
			return $item;	
		}
		

	}
	final function get_user($id){

		if(!$id=mw_get_number($id)){
			return false;	
		}

		if($item=$this->get_user_if_loaded($id)){
			return $item;	
		}
		return $this->_load_user($id);
	}
	final function get_user_if_loaded($id){
		if(!$id=mw_get_number($id)){
			return false;	
		}

		$this->init_users();
		if(isset($this->users[$id])){
			return $this->users[$id]; 	
		}
	
	}
	final function get_user_by_idname($idname){
		if(!$this->check_user_name_loose($idname)){
			return false;	
		}
		if($item=$this->get_user_by_idname_if_loaded($idname)){
			return $item;	
		}
		return $this->_load_user_by_idname($idname);
	}
	function check_user_name_loose($idname){
		if(!$idname){
			return false;	
		}
		if(is_string($idname)){
			return trim($idname);	
		}
	}
	final function get_user_by_idname_if_loaded($idname){
	
		if(!$this->check_user_name_loose($idname)){
			return false;	
		}
		$this->init_users();
		if(isset($this->users_by_idname[$idname])){
			return $this->users_by_idname[$idname]; 	
		}
	
	}
	//loading single user
	function load_and_create_user_by_idname($idname){
		if(!$this->check_user_name_loose($idname)){
			return false;	
		}
		
		if(!$tblman=$this->get_tblman()){
			return false;	
		}
		
		$idname=$this->real_escape_string($idname);
		if(!$sql=$tblman->get_sql_load_all_start()){
			return false;	
		}
		$sql.=" where name='$idname' limit 1";
		//echo $sql;
	
		return $this->load_and_create_user_by_tblitem($tblman->get_item_by_sql($sql));
		
	}

	function load_and_create_user_by_tblitem($tblitem){
		if(!$tblitem){
			return false;	
		}
		
		if(!$id=$tblitem->get_id()){
			return false;	
		}
		$user= $this->new_user($tblitem);
		
		return $this->init_and_add_user($user);
		
	}
	function add_to_preload($id){
		if(!$tblman=$this->get_tblman()){
			return false;	
		}
		return $tblman->add_to_preload($id);
		
	}
	function load_and_create_user($id){
		
		if(!$tblman=$this->get_tblman()){

			return false;	
		}


		return $this->load_and_create_user_by_tblitem($tblman->get_item($id));
		
	}
	//adding and loading user: for interna use only
	private function _load_user($id){
		if(!$id=mw_get_number($id)){
			return false;	
		}
		if($item=$this->load_and_create_user($id)){
			return $this->_add_user($item);	
		}
	}
	private function _load_user_by_idname($idname){
		if(!$this->check_user_name_loose($idname)){
			return false;	
		}
		
		if($item=$this->load_and_create_user_by_idname($idname)){
			return $this->_add_user($item);	
		}
	}
	function init_and_add_user($user){
		if(!$user->do_init()){
			return false;
		}
		
		return $this->add_user($user);
	}
	final function add_user($user){
		if(!$this->allow_add_user()){
			return false;
		}
		return $this->_add_user($user);
	}
	function allow_add_user(){
		return true;	
	}
	private function _add_user($user){
	
		if(!$id=$user->get_id()){
			return false;	
		}
		if(!$idname=$user->get_idname()){
			return false;	
		}
		if($existing=$this->users_by_idname[$idname]??null){
			if($existing->get_id()==$id){
				return $existing;	
			}
			
			return false;
		}
		if($existing=$this->users[$id]??null){
			if($existing->get_idname()==$idname){
				return $existing;	
			}
			return false;
		}
		if(!$user->check()){
			return false;	
		}
		
		$this->users_by_idname[$idname]=$user;
		$this->users[$id]=$user;
		return $user;
	}
	//getting users lists
	function get_users_by_ids($ids){
		if(!$ids){
			return false;	
		}
		if(!is_array($ids)){
			$ids=explode(",",$ids."");	
		}
		$newlist=array();
		foreach($ids as $id){
			if($id=mw_get_number($id)){
				$newlist[$id]=$id;	
			}
		}
		if(!sizeof($newlist)){
			return false;	
		}
		if(!$tblman=$this->get_tblman()){
			return false;	
		}

		if(!$sql=$tblman->get_sql_load_all_start()){
			return false;	
		}
		$idsstr=implode(",",$newlist);
		$sql.=" where  id in ($idsstr) order by name";
		
		if(!$tblitems=$tblman->get_items_by_sql($sql)){
			return false;	
		}
		return $this->get_users_by_tbl_items($tblitems);
		
		
		//
	}
	
	function get_users_by_tbl_items($tblitems){
		if(!is_array($tblitems)){
			return false;
		}
		$r=array();
		foreach($tblitems as $tblitem){
			if($u=$this->get_user_by_tbl_item($tblitem)){
				$id=$u->get_id();
				$r[$id]=$u;	
			}
		}
		if(!sizeof($r)){
			return false;
		}
		return $r;

	}
	function get_users_by_query($query){
		if(!$query){
			return false;	
		}
		if(!$tblman=$this->get_tblman()){
			return false;	
		}
		/*
		if(!$sql=$query->get_sql()){
			return false;
		}
		*/
		return $this->get_users_by_tbl_items($tblman->get_items_by_mwQuery($query));
	}
	////////
	
	
	
	
	//initiating users list
	final function __get_priv_usersinitdone(){
		return $this->usersinitdone; 	
	}
	function do_users_init(){
		if($this->usersinitdone){
			return false;	
		}
		//acá podrías crearse usuarios modo no db
		//meditante init_and_add_user
		return true;	
			
	}
	final function init_users(){
		if($this->usersinitdone){
			return true;	
		}
		$this->do_users_init();
		$this->usersinitdone=true;
	}
	//initializing sub managers
	final function get_pass_policy(){
		//user_data_man establece campos de datos del usuario y valida inputs
		if(!isset($this->_pass_policy)){
			$this->_pass_policy=$this->create_pass_policy();	
		}
		return $this->_pass_policy;
	}
	
	final function get_user_mailer(){
		//user_data_man establece campos de datos del usuario y valida inputs
		if(!isset($this->_user_mailer)){
			$this->_user_mailer=$this->create_user_mailer();	
		}
		return $this->_user_mailer;
	}
	
	final function get_user_data_man(){
		//user_data_man establece campos de datos del usuario y valida inputs
		if(!isset($this->_userdataman)){
			$this->_userdataman=$this->create_user_data_man();	
		}
		return $this->_userdataman;
	}
	///inicializating manager
	final function init($ap,$sessionvar="__current_user_data"){
		$this->sessionvarname=$sessionvar;
		$this->set_mainap($ap);	
	}
	final function init_tbl_mode($ap,$tbl,$sessionvar="__current_user_data"){
		$this->init($ap,$sessionvar);
		$this->set_tbl_name($tbl);
	}
	
	

}
?>