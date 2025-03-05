<?php
//
class mwmod_mw_users_permissions_permissionsman extends mw_apsubbaseobj{
	private $_items=array();
	private $usersman;
	private $rolsman;
	function __construct($usersman,$rolsman){
		$this->init($usersman,$rolsman);	
	}
	final function add_item($permission){
		if(!$cod=$this->check_str_key_alnum_underscore($permission->get_code())){
			return false;	
		}
		$this->_items[$cod]=$permission;	
		return $permission;
		
	}
	function init_rols(){
		if($items=$this->get_items()){
			foreach($items as $cod=>$item){
				$item->init_rols();	
			}
		}
	
	}
	
	
	
	function allow_by_permission($user,$cod,$params=false){
		//$this->init_roles();
		if(!$item=$this->get_item($cod)){
			return false;
		}
		//echo $cod."<br>";

		return $item->allow($user,$params);
	}
	function allow_no_user_by_permission($cod,$params=false){
		if(!$item=$this->get_item($cod)){
			return false;
		}

		return $item->allow_no_user($params);
	}
	final function get_item($cod){
		if(!$this->check_str_key_alnum_underscore($cod)){
			return false;	
		}
		
		return $this->_items[$cod]??null;	
	}
	final function get_items(){
		//$this->_init_items();
		return $this->_items;	
	}
	final function __get_priv_usersman(){
		return $this->usersman; 	
	}

	final function __get_priv_rolsman(){
		return $this->rolsman; 	
	}
	
	final function init($usersman,$rolsman){
		$this->usersman=$usersman;
		$this->rolsman=$rolsman;
		$this->set_mainap($this->usersman->mainap);	
	}

}
?>