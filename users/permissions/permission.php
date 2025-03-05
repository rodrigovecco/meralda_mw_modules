<?php
//falta subir
class mwmod_mw_users_permissions_permission extends mw_apsubbaseobj{
	private $cod;
	private $permisionsman;
	private $_is_allowed_for_no_user=false;
	private $_is_allowed_all_users=false;
	private $_roles_codes_str;
	private $_rols;
	private $_allways_allow_main_user=true;
	private $_catch_all_requests=true;//avoid user object to evaluate persimison by itself;
	private $_enabled;
	
	private $parent_permissions_cods=array();//if one of this is allowed, ok
	private $parent_permissions_enabled=false;
	public $namedef;
	public $name;
	
	var $allow_debug_info="";
	
	
	function __construct($cod,$namedef,$roles_codes,$permisionsman){
		$this->init($cod,$namedef,$roles_codes,$permisionsman);	
		
	}
	function is_parent_permissions_enabled(){
		return $this->parent_permissions_enabled;	
	}
	final function __get_priv_parent_permissions_enabled(){
		return $this->parent_permissions_enabled;	
	}
	final function set_parent_permissions_enabled($enable=true){
		$this->parent_permissions_enabled=$enable;	
	}
	function get_parent_permissions_cods(){
		if($list=$this->__get_priv_parent_permissions_cods()){
			if(sizeof($list)){
				return $list;	
			}
		}
	}
	final function __get_priv_parent_permissions_cods(){
		return $this->parent_permissions_cods;	
	}
	
	final function add_parent_permissions($cods,$enable=true){
		if($enable){
			$this->set_parent_permissions_enabled($enable);	
		}
		if(!is_array($cods)){
			$cods=explode(",",$cods."");	
		}
		$r=0;
		foreach($cods as $cod){
			if($cod=trim($cod)){
				$this->parent_permissions_cods[$cod]=$cod;
				$r++;	
			}
		}
		return $r;
	}
	function load_enabled(){
		return true;	
	}
	final function is_enabled(){
		if(isset($this->_enabled)){
			return $this->_enabled;	
		}
		$this->_enabled=true;
		if(!$this->load_enabled()){
			$this->_enabled=false;	
		}
		return $this->_enabled;	
	}
	function get_debug_data(){
		$r=array(
			"cod"=>$this->cod,
			"name"=>$this->get_name(),
			"class"=>get_class($this),
			"catch_all_requests"=>$this->catch_all_requests(),
			"allways_allow_main_user"=>$this->allways_allow_main_user(),
			"is_allowed_for_no_user"=>$this->is_allowed_for_no_user(),
			"rols"=>$this->get_rols_codes(),
			"parents"=>$this->get_parent_permissions_cods(),
			"parents_enabled"=>$this->is_parent_permissions_enabled(),
		);
		return $r;	
	}
	final function set_catch_all_requests($val=true){
		$this->_catch_all_requests=$val;	
	}
	final function get_catch_all_requests(){
		return $this->_catch_all_requests;
	}
	function catch_all_requests(){
		return $this->get_catch_all_requests();	
	}
	
	final function init_rols(){
		if(isset($this->_rols)){
			return;
		}
		$this->_rols=array();
		if(!$items=$this->load_rols()){
			return;	
		}
		foreach($items as $cod=>$rol){
			if($rol->add_permission($this)){
				$this->_rols[$cod]=$rol;
			}
		}
		
	}
	function load_rols(){
		if(!$cods=$this->get_rols_codes()){
			return false;	
		}
		if(!$man=$this->permisionsman->rolsman){
			return false;	
		}
		$r=array();
		foreach($cods as $cod){
			if($item=$man->get_item($cod)){
				$r[$cod]=$item;
			}
		}
		return $r;
	}
	final function get_rols_codes(){
		if(!$this->_roles_codes_str){
			return false;	
		}
		$a=explode(",",$this->_roles_codes_str);
		$r=array();
		foreach($a as $c){
			$r[]=trim($c);
		}
		return $r;
	}
	//_is_allowed_all_users
	
	final function set_allways_allow_main_user($val=true){
		$this->_allways_allow_main_user=$val;
	}
	final function get_allways_allow_main_user(){
		return $this->_allways_allow_main_user;
	}
	function allways_allow_main_user(){
		return $this->get_allways_allow_main_user();	
	}
	
	final function set_allowed_for_no_user($val=true){
		$this->_is_allowed_for_no_user=$val;
	}
	final function get_allowed_for_no_user(){
		return $this->_is_allowed_for_no_user;
	}
	final function set_allowed_for_all_users($val=true){
		$this->_is_allowed_all_users=$val;
	}
	final function get_allowed_for_all_users(){
		return $this->_is_allowed_all_users;
	}
	function is_allowed_for_all_users(){
		return $this->get_allowed_for_all_users();	
	}
	function get_name(){
		return $this->name;	
	}
	function allow_no_user($params=false){
		return $this->allow(false,$params);
	}
	function is_allowed_for_no_user(){
		return $this->get_allowed_for_no_user();	
	}
	function user_can_add($user){
		if(!$user){
			return false;	
		}
		return true;
	}
	function user_can_have($user){
		if(!$user){
			return $this->is_allowed_for_no_user();
			return false;	
		}
		if($user->is_main_user()){
			return true;	
		}
		if($user->is_active()){
			return true;	
		}
			
	}
	function allow_user_checked($user,$params=false){
		return true;
	}
	final function other_permission_allowed($cod,$user,$params=false){
		if(!$cod){
			return false;	
		}
		$codthis=$this->get_code();
		if(!$other=$this->permisionsman->get_item($cod)){
			return false;	
		}
		if(!$cod=$other->get_code()){
			return false;	
		}
		if($cod==$codthis){
			return false;	
		}
		if(!is_array($params)){
			$params=array();	
		}
		if(!is_array($params["_other_permissions_chain"])){
			$params["_other_permissions_chain"]=array();	
		}
		if($params["_other_permissions_chain"][$codthis]){
			
			return false;
		}
		$params["_other_permissions_chain"][$codthis]=$this;
		return $other->allow($user,$params);
		
	}
	function parent_permissions_allow($user,$params=false){
		if(!$this->is_parent_permissions_enabled()){
			return false;
		}
		if(!$list=$this->get_parent_permissions_cods()){
			return false;	
		}
		foreach($list as $cod){
			if($this->other_permission_allowed($cod,$user,$params)){
				
				$this->allow_debug_info="allowed by $cod";
				return true;	
			}
		}
	}
	function allowed($params=false){
		//curent user;
		$user=false;
		if($this->permisionsman){
			if($this->permisionsman->usersman){
				$user=	$this->permisionsman->usersman->get_current_user();
			}
			//	
		}
		return $this->allow($user,$params);
		
		
	}
	function allow($user,$params=false){
		if(!$this->is_enabled()){
			$this->allow_debug_info="Not enabled";
			return false;	
		}
		if(!$this->user_can_have($user)){
			$this->allow_debug_info="user can not have";
			return false;	
		}
		if(!$user){
			$this->allow_debug_info="no user";
			return false;	
		}
		
		if($user->is_main_user()){
			if($this->allways_allow_main_user()){
				$this->allow_debug_info="is_main_user";
				return true;	
			}
		}
		if($this->is_allowed_for_all_users()){
			$this->allow_debug_info="is_allowed_for_all_users";
			return true;	
		}
		if($user->get_permission($this->get_code())){
			if($this->allow_user_checked($user,$params)){
				$this->allow_debug_info="user has permission";
				return true;	
			}
		}
		if($this->parent_permissions_allow($user,$params)){
			return true;	
		}
		
		/*
		if($user->get_permission($this->get_code())){
			return $this->allow_user_checked($user,$params);
		}
		*/
		return false;	
	}
	final function get_code(){
		return $this->cod;	
	}
	final function __get_priv_cod(){
		return $this->get_code(); 	
	}

	final function init($cod,$namedef,$roles_codes,$permisionsman){
		$this->cod=$cod;
		$this->namedef=$namedef;
		$this->permisionsman=$permisionsman;
		$this->_roles_codes_str=$roles_codes;
		$this->set_mainap($this->permisionsman->mainap);	
		$this->name=$this->get_msg($namedef);
	}

}
?>