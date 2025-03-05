<?php
class mwmod_mw_users_rols_rol extends mw_apsubbaseobj{
	private $cod;
	private $man;
	//private $_is_allowed_for_no_user=false;
	public $name;
	public $description;
	public $namedef;
	public $name_for_permitions_option;
	public $is_permitions_option=true;
	public $allways_for_mainadmin=false;
	public $color; 
	private $_permissions=array();
	function __construct($cod,$namedef,$man){
		$this->init($cod,$namedef,$man);	
	}
	function is_permitions_option(){
		return $this->is_permitions_option;	
	}
	function get_name_for_permitions_option(){
		if($this->name_for_permitions_option){
			return $this->name_for_permitions_option;	
		}
		return $this->get_name();
	}
	function get_debug_data(){
		$r=array();
		$r["cod"]=$this->get_code();
		$r["name"]=$this->get_name();
		$r["allowedfornouser"]=$this->is_allowed_for_no_user();
		$r["allowedforanyuser"]=$this->is_allowed_for_any_user();
		$r["class"]=get_class($this);
		$r["permisions"]=$this->get_permisions();
		return $r;
		
		
			
	}
	function get_tbl_field_name(){
		return $this->man->usersman->get_rol_tbl_field($this->get_code());	
	}
	function can_add_permision($item){
		return true;
	}
	final function get_permisions(){
		return 	$this->_permissions;
	}
	function add_permission($item){
		if(!$this->can_add_permision($item)){
			return false;
		}
		$cod=$item->get_code();
		$this->_permissions[$cod]=$item;
		return $item;
	}
	/*
	final function set_allowed_for_no_user($val=true){
		$this->_is_allowed_for_no_user=$val;
	}
	final function get_allowed_for_no_user(){
		return $this->_is_allowed_for_no_user;
	}
	*/
	function get_name(){
		return $this->name;	
	}
	
	final function get_code(){
		return $this->cod;	
	}
	final function __get_priv_man(){
		return $this->man; 	
	}
	final function __get_priv_cod(){
		return $this->get_code(); 	
	}

	final function init($cod,$namedef,$man){
		$this->cod=$cod;
		$this->man=$man;
		$this->namedef=$namedef;
		$this->set_mainap($this->man->mainap);	
		$this->name=$this->get_msg($namedef);
	}
	function is_assignable(){
		return true;
	}
	function is_allowed_for_no_user(){
		return false;
	}
	function is_allowed_for_any_user(){
		return false;
	}
	function user_has_rol($user){
		if(!$this->user_can_have($user)){
			return false;	
		}
		if(!$user){
			
			return false;	
		}
		if($this->allways_for_mainadmin){
			if($user){
				if($user->is_main_user()){
					return true;	
				}
	
			}
		}
		return $user->has_rol_code($this->get_code());
		
	}
	function user_can_have($user){
		if(!$user){
			//return $this->is_allowed_for_no_user();
			return false;	
		}
		if($user->is_main_user()){
			return true;	
		}
		if($user->is_active()){
			return true;	
		}
			
	}
	//por verificar
	/*
	function allow_no_user($params=false){
		return $this->allow(false,$params);
	}
	function allow($user,$params=false){
		if(!$this->user_can_have($user)){
			return false;	
		}
		if(!$user){
			return false;	
		}
		
		if($user->is_main_user()){
			return true;	
		}
		
		return false;	
	}
	*/

}
?>