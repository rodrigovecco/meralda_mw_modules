<?php
//
abstract class mwmod_mw_users_userabs extends mw_apsubbaseobj{
	private $man;
	private $id;
	private $idname;
	
	private $tblitem;
	

	private $password;
	private $password_is_encrypted;
	private $init_ok;
	private $_valid_email;
	
	
	private $_rols;
	
	private $_permissions;
	private $_new_password;
	private $profile_imgs_group;
	
	var $reset_pass_code;
	private $_treedataman;
	private $_strdataman;
	private $_jsondataman;
	
	private $_groups;
	
	//////////
	//obtener datos
	function get_public_tbl_data(){
		if(!$tblitem=$this->tblitem){
			return false;	
		}
		if(!$r=$tblitem->get_data()){
			return false;	
		}
		unset($r["pass"]);
		unset($r["reset_pass_code"]);
		return $r;
	}
	
	function get_data($cod){
		if($cod=="pass"){
			return false;	
		}
		return $this->get_tblitem_data($cod);
	}
	function is_out_of_office(){
		return $this->tblitem->get_data("out_of_office");
	}
	function get_out_of_office_replacement_id(){
		if($id=$this->tblitem->get_data("out_of_office_replacement")){
			return $id;	
		}
		return $this->tblitem->get_data("admin_user_id");
	}
	
	function is_active(){
		if($this->is_main_user()){
			return true;	
		}
		return $this->tblitem->get_data("active");
	}
		

	function get_once_valid_email(){
		return $this->tblitem->get_data("name");
	}
	function get_email(){
		return $this->get_valid_email();
	}
	function get_personal_email(){
		return $this->tblitem->get_data("personal_email");
	}
	//
	final function get_valid_email(){
		if(isset($this->_valid_email)){
			return 	$this->_valid_email;
		}
		$this->_valid_email=false;
		if($e=$this->get_once_valid_email()){
			if(mw_checkemail($e)){
				$this->_valid_email=$e;	
			}
		}
		return 	$this->_valid_email;
	}
	
	final function get_idname(){
		return $this->idname;
	}
	final function get_id(){
		return $this->id;
	}
	function get_real_name_or_idname(){
		if($r=$this->get_real_name()){
			return $r;
		}
		return $this->get_idname();
			
	}
	
	function get_real_name(){
		return $this->tblitem->get_data("complete_name");
	}
	function get_real_and_idname(){
		if(!$r=$this->get_real_name()){
			return $this->get_idname();
		}
		$idname=$this->get_idname();
		if($r==$idname){
			return $r;	
		}
		return $r." - ".$idname;
		
	}
	function get_data_for_admin(){
		$r["name"]=$this->get_idname();
		$r["data"]=$this->get_public_tbl_data();	
		$r["pass"]["secpass"]=$this->tblitem->get_data("secpass");
		$r["rols"]=array();
		if($man=$this->man->get_rols_man()){
			if($items=$man->get_assignable_items()){
				foreach($items as $cod=>$rol){
					if($this->has_rol_code($cod)){
						$r["rols"][$cod]=1;
					}
				}
			}
		}
		//$r["img"]["image"]["url"]=$this->get_img_url();
		
		return $r;
	}
	
	function get_idname_and_real(){
		if(!$r=$this->get_real_name()){
			return $this->get_idname();
		}
		$idname=$this->get_idname();
		if($r==$idname){
			return $r;	
		}
		return $idname." - ".$r;
		
	}
	function get_name(){
		if($r=$this->get_real_name()){
			return $r;
		}
		return $this->get_idname();
	}
	/////////////
	function get_jsondata_item($code="data",$path=false){
		if($m=$this->get_jsondataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	final function get_jsondataman(){
		if(isset($this->_jsondataman)){
			return 	$this->_jsondataman;
		}
		if($m=$this->get_init_jsondataman()){
			$this->_jsondataman=$m;
			return 	$this->_jsondataman;
		}
	}
	function can_create_jsondata(){
		return false;
	}
	function get_init_jsondataman(){
		if(!$this->can_create_jsondata()){
			return false;		
		}
		if(!$p=$this->get_jsondata_path()){
			return false;		
		}
		
		$m= new mwmod_mw_data_json_man($p);
		return $m;
	}
	function get_jsondata_path(){
		if($rel=$this->get_rel_path()){
			return $rel."/json";
		}
		return false;
	}
	
	
	
	
	
	
	//////
	
	
	
	function load_groups(){
		$r=array();
		if(!$man=$this->man->get_groups_man()){
			return $r;	
		}
		if(!$items=$man->get_all_active_items()){
			return $r;	
		}
		foreach($items as $id=>$item){
			if($item->contains_user($this)){
				$r[$id]=$item;	
			}
		}
		return $r;
	}
	function get_groups_str_list(){
		if(!$items=$this->get_groups()){
			return "";	
		}
		$list=array();
		foreach($items as $id=>$item){
			$list[]=$item->get_name();	
		}
		return implode(", ",$list);
	}
	final function get_groups(){
		if(isset($this->_groups)){
			return $this->_groups;	
		}
		$this->_groups=array();
		if($items=$this->load_groups()){
			foreach($items as $item){
				$id=$item->id;
				$this->_groups[$id]=$item;
			}
		}
		return $this->_groups;	
	}
	
	
	function get_lbl_for_options_list(){
		return $this->get_real_and_idname();	
	}
	function get_img_item($cod=false){
		if(!$man=$this->__get_priv_profile_imgs_group()){
			return false;	
		}
		return $man->get_item_or_def($cod); 
	}
	
	function get_img_url($cod=false){
		if(!$man=$this->__get_priv_profile_imgs_group()){
			return false;	
		}
		return $man->get_img_url($cod); 
	}
	function has_img(){
		return $this->tblitem->get_data("image");	
	}
	function get_imgs(){
		if(!$man=$this->__get_priv_profile_imgs_group()){
			return false;	
		}
		return $man->get_items(); 
	}
	
	function get_img_elem($cod=false){
		if(!$man=$this->__get_priv_profile_imgs_group()){
			return false;	
		}
		return $man->get_img_elem($cod); 
	}

	function get_strdata_item($code="data",$path=false){
		if($m=$this->get_strdataman()){
			return $m->get_datamanager($code,$path);	
		}
	}
	final function get_strdataman(){
		if(isset($this->_strdataman)){
			return 	$this->_strdataman;
		}
		if($m=$this->get_init_strdataman()){
			$this->_strdataman=$m;
			return 	$this->_strdataman;
		}
	}
	function can_create_strdata(){
		return false;
	}
	function get_init_strdataman(){
		if(!$this->can_create_strdata()){
			return false;		
		}
		if(!$p=$this->get_strdata_path()){
			return false;		
		}
		
		$m= new mwmod_mw_data_str_man($p);
		return $m;
	}
	function get_strdata_path(){
		if($rel=$this->get_rel_path()){
			return $rel."/str";
		}
		return false;
	}
	
	function get_treedata_item($code="data",$path=false){
		if($m=$this->get_treedataman()){
			return $m->get_datamanager($code,$path);	
		}
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
	function can_create_treedata(){
		return false;	
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
		if($rel=$this->get_rel_path()){
			return $rel."/data";
		}
		return false;
		
	}
	/*
	function get_userfiles_priv_path_man(){
		if(!$rel=$this->get_rel_path()){
			return false;
		}
		if($m=$this->mainap->get_sub_path_man($rel)){
			return $m;	
		}
	}
	*/
	
	final function __get_priv_profile_imgs_group(){
		if(!isset($this->profile_imgs_group)){
			$this->profile_imgs_group=false;
			$dm=$this->get_user_data_man();
			
			if($gr=$dm->create_profile_imgs_group($this)){
				$this->profile_imgs_group=$gr;	
			}
			
		}
		return $this->profile_imgs_group;
	}
	
	function reset_password_set_new_pass($pass){
		
		if($this->is_main()){
			$sec=1;	
		}else{
			$sec=$this->tblitem->get_data("secpass");
		}
		$update=array();
		if($sec){
			$update["pass"]=$this->man->crypt_password($pass);
			$update["secpass"]=1;
		}else{
			$update["pass"]=$pass;
			$update["secpass"]=0;
		}
		$update["reset_pass_enabled"]="0";
		//mw_array2list_echo($update);
		//return;
		$this->tblitem->do_update($update);
		$this->set_new_password($pass);
		return $pass;
	}
	
	function reset_password(){
		$pass=randPass(10);
		if($this->is_main()){
			$sec=1;	
		}else{
			$sec=$this->tblitem->get_data("secpass");
		}
		$update=array();
		if($sec){
			$update["pass"]=$this->man->crypt_password($pass);
			$update["secpass"]=1;
		}else{
			$update["pass"]=$pass;
			$update["secpass"]=0;
		}
		$update["reset_pass_enabled"]="0";
		$this->tblitem->do_update($update);
		$this->set_new_password($pass);
		return $pass;
	}
	function can_reset_pass(){
		if(!$this->can_login()){
			return false;
		}
		if(!$token=$this->tblitem->get_data("reset_pass_code")){
			return false;	
		}
		if(!$ok=$this->tblitem->get_data("reset_pass_enabled")){
			return false;	
		}
		if(!$date=$this->tblitem->get_data("reset_pass_expires")){
			return false;	
		}
		if(!$ext=strtotime($date)){
			return false;	
		}
		if($ext>=time()){
			return true;	
		}

		//return $this->can_login();
	}
	
	
	function check_reset_pass_token($token){
		if(!$token){
			return false;	
		}
		if($orig=$this->tblitem->get_data("reset_pass_code")){
			if($orig===$token){
				return true;
			}
		}
	}
	
	
	function create_reset_password_token(){
		$token=randPass(50);
		$exp=date("Y-m-d H:i",strtotime(date("Y-m-d H:i",time())." + 8 hours" ));
		$nd=array(
		"reset_pass_code"=>$token,
		"reset_pass_enabled"=>1,
		"reset_pass_expires"=>$exp,
		);
		$this->reset_pass_code=$token;
		$this->tblitem->do_update($nd);
		return $token;
	}
	
	function get_user_data_man(){
		return $this->man->get_user_data_man();	
	}
	//permitir ser modificado por usuario actual
	function allowadmin_admin(){
		return $this->man->allow("adminusers");	
	}
	//guardar
	function save_from_install_data($input){
		if(!$nd=$this->man->get_new_user_data($input,"name")){
			return false;	
		}
		return $this->tblitem->do_update($nd);
		
		
		
	}
	
	function save_from_admin_data($input){
		if(!$nd=$this->man->get_new_user_data($input)){
			return false;	
		}
		return $this->tblitem->do_update($nd);
		
		
	}

	final function set_new_password($pass){
		$this->_new_password=$pass;	
	}
	function check(){
		return $this->do_init();	
	}
	function on_login(){
			
	}
	

	function check_login_pass($pass){
		if(!$pass){
			return false;	
		}
		if(!is_string($pass)){
			return false;	
		}
		if(!$cp=$this->get_password()){
			return false;	
		}
		
		if($this->get_password_is_encrypted()){
			if($this->man->check_crypted_password($pass,$cp)){
				return true;	
			}else{
				return false;	
			}
			return false;
		}else{
			if($cp===$pass){
				return true;	
			}
		}
		
		
	}
	function set_ap_lng(){
			
	}
	
	//permisos
	final function get_permissions(){
		$this->_init_permisions();
		return $this->_permissions;
	}
	
	function allow_by_method($action,$params=false){
		
		if(!$this->check_str_key($action)){
			return false;	
		}
		$method="do_allow_".$action;
		if(method_exists($this,$method)){
			return $this->$method($params);	
		}
	}
	function allow($action,$params=false,$main_always=true){
		if($permission_man=$this->man->get_permission_man()){
			if($per=$permission_man->get_item($action)){
				if($per->catch_all_requests()){
					return $per->allow($this,$params);
				}
			}
		}
		
		if($main_always){
			if($this->is_main()){
				return true;	
			}
		}
		if($this->check_str_key($action)){
			$method="do_allow_".$action;
			if(method_exists($this,$method)){
				return $this->allow_by_method($action,$params);	
			}
			
		}
		return $this->allow_by_permission($action,$params);	

	}
	function allow_by_permission($action,$params=false){
		if(!$man=$this->man->get_permission_man()){
			return false;	
		}
		return $man->allow_by_permission($this,$action,$params);
		
	}
	
	final function get_permission($cod){
		if(!$cod=$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		$this->_init_permisions();
		return $this->_permissions[$cod]??null;
	}
	private function _init_permisions(){
		if(isset($this->_permissions)){
			return;
		}
		$this->_permissions=array();
		$permisions=array();
		if(!$rols=$this->get_rols()){
			return;	
		}
		foreach($rols as $rol){
			if($rolspermissions=$rol->get_permisions()){
				foreach($rolspermissions as $cod=>$p){
					$permisions[$cod]=$p;	
				}
			}
		}
		foreach($permisions as $cod=>$p){
			if($p->user_can_add($this)){
				$this->_permissions[$cod]=$p;	
			}
		}
		

	}
	//principal
	function is_main(){
		if($this->id!==1){
			return false;	
		}
		return $this->tblitem->get_data("is_main");
	}
	function is_main_user(){
		return $this->is_main();	
	}
	//roles
	final function get_rols(){
		$this->_init_rols();
		return $this->_rols;
	}
	function get_rols_names_str_list(){
		if(!$items=$this->get_rols()){
			return "";	
		}
		$list=array();
		foreach($items as $cod=>$item){
			$list[]=$item->get_name();	
		}
		return implode(", ",$list);
	}
	private function _init_rols(){
		if(isset($this->_rols)){
			return $this->_rols;
		}
		$this->_rols=array();
		if(!$rolesman=$this->man->get_rols_man()){
			return false;
		}
		if(!$roles=$rolesman->get_items()){
			return false;	
		}
		$r=array();
		foreach($roles as $cod=>$rol){
			if($rol->user_has_rol($this)){
				$this->_rols[$cod]=$rol;	
			}
		}

	}
	final function has_rol_code($rol_cod){
		if(!$cod=$this->man->get_rol_tbl_field($rol_cod)){
			return false;
		}
		return $this->tblitem->get_data($cod)+0;
	}
	final function unset_rols_codes(){
		unset($this->_rols);	
	}
	
	
	function can_login(){
		return $this->is_active();
	}
	final function get_tbl_item(){
		return $this->tblitem;		
	}
	final function get_tblitem_data($cod){
		if($this->tblitem){
			return $this->tblitem->get_data($cod);	
		}
	}
	
	

	

	
	//inicializar datos
	final function set_idname($name){
		if($this->is_init_done()){
			return false;	
		}
		$this->idname=$name;
		return true;
	}
	final function set_id($id){
		if($this->is_init_done()){
			return false;	
		}
		if($this->id=$id+0){
			return true;
		}
		
	}
	final function set_password($password,$isencrypted=false){
		if($this->is_init_done()){
			return false;	
		}
		$this->password=$password;
		$this->password_is_encrypted=$isencrypted;
		return true;
	}
	final function do_init(){
		if(isset($this->init_ok)){
			return $this->init_ok;	
		}
	
		$this->init_ok=false;
		if(!$this->password){
			return false;	
		}
		if(!$id=$this->id+0){
			return false;	
		}
		if(!$this->idname){
			return false;	
		}
		
		$this->init_ok=true;
		return $this->init_ok;	
		
	}
	
	final function is_init_done(){
		if(isset($this->init_ok)){
			return true;	
		}
	}
	final function get_password(){
		return $this->password;	
	}
	final function get_password_is_encrypted(){
		return $this->password_is_encrypted;	
	}
	
	final function __get_priv_tblitem(){
		return $this->tblitem; 	
	}
	final function __get_priv_usersinitdone(){
		return $this->usersinitdone; 	
	}
	final function __get_priv_sessionvarname(){
		return $this->sessionvarname; 	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}
	final function __get_priv_id(){
		return $this->id; 	
	}
	function __call($a,$b){
		return false;	
	}
	function __toString(){
		return $this->get_idname_and_real()."";	
	}
	function init_data_from_tbl_item($tblitem){
		$id=$tblitem->get_id();
		$this->set_id($id);
		$pass=$tblitem->get_data("pass");
		$isenc=$tblitem->get_data("secpass");
		$idname=$tblitem->get_data("name");
		$this->set_idname($idname);
		$this->set_password($pass,$isenc);
			
	}
	function get_rel_path(){
		return $this->man->get_user_rel_path($this);
	}

	final function init($man,$tblitem){
		$this->man=$man;
		$ap=$man->mainap;
		
		$this->set_mainap($ap);
		$this->tblitem=$tblitem;
		$this->init_data_from_tbl_item($tblitem);
		

					
	}
}
?>