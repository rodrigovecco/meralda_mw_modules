<?php
//
abstract class mwmod_mw_users_list_listabs extends mw_apsubbaseobj{
	private $user_man;
	var $default_user;
	private $_items=array();
	private $_user_ids=array();//to load list
	var $allow_repeated_users=false;
	var $out_of_office_mode=false;
	function user_allowed($user,$item=false){
		if($user->is_active()){
			if($this->out_of_office_mode){
				if($user->is_out_of_office()){
					return false;	
				}
			}
			return true;	
		}
	}
	function get_user_replacement($user,$item=false){
		if(!$id=$user->get_out_of_office_replacement_id()){
			return false;	
		}
		if($uman=$this->__get_priv_user_man()){
			return $uman->get_user($id);
		}
		
		
	}
	function get_items_by_final_user(){
		$r=array();
		if($items=$this->get_items()){
			foreach($items as $item){
				if($id=$item->final_user_id){
					if(!$r[$id]){
						$r[$id]=$item;	
					}
				}
			}
		}
		return $r;
			
	}

	function get_items_by_original_user(){
		$r=array();
		if($items=$this->get_items()){
			foreach($items as $item){
				if($id=$item->orig_user_id){
					if(!$r[$id]){
						$r[$id]=$item;	
					}
				}
			}
		}
		return $r;
			
	}
	
	function get_debug_data(){
		$r=array();
		$r["items_by_final_user"]=array();
		if($items=$this->get_items_by_final_user()){
			foreach($items as $id=>$item){
				$r["items_by_final_user"][$id]=$item->get_debug_data();	
				//
			}
		}
		$r["items"]=array();
		if($items=$this->get_items()){
			foreach($items as $id=>$item){
				$r["items"][$id]=$item->get_debug_data();	
				//
			}
		}
		return $r;
		
			
	}
	function process(){
		$this->pre_load_users();
		if($items=$this->get_items()){
			foreach($items as $item){
				$item->process();	
			}
		}
	}
	function pre_load_users(){
		//en el futuro puede solo precargar usuarios requeridos en _user_ids
		if($uman=$this->__get_priv_user_man()){
			$uman->get_all_useres();
		}
		//	
	}
	
	function add_items_by_groups_and_users_str_list($strlist){
		if(!is_string($strlist)){
			return false;	
		}
		$r=array();
		$list=explode(",",$strlist);
		foreach($list as $cod){
			if($cod=="def"){
				if($this->default_user){
					if($item=$this->add_item_by_user_id($this->default_user->get_id())){
						$r[]=$item;	
					}
						
				}
			}else{
				$a=explode("_",$cod);
				if($id=$a[1]+0){
					if($a[0]=="u"){
						if($item=$this->add_item_by_user_id($id)){
							$r[]=$item;	
						}
					}elseif($a[0]=="g"){
						if($items=$this->add_items_by_group_id($id)){
							foreach($items as $item){
								$r[]=$item;		
							}
							
						}
						
					}
				}
			}
		}
		return $r;
	}
	
	
	function add_items_by_group_id($id){
		if(!$id=$id+0){
			return false;	
		}
		
		if(!$uman=$this->__get_priv_user_man()){
			return false;	
		}
		if(!$man=$uman->get_groups_man()){
			return false;	
		}
		if($group=$man->get_item($id)){
			return $this->add_item_by_user_ids_list($group->get_users_id_list());	
		}
		return false;
		
	}
	function add_item_by_user_ids_list($ids){
		if(!is_array($ids)){
			return false;	
		}
		$r=array();
		foreach($ids as $id){
			if($item=$this->add_item_by_user_id($id)){
				$r[$id]=$item;
			}
		}
		return $r;
	}
	
	function add_item_by_user_id($id){
		if(!$id=$id+0){
			return false;	
		}
		if(!$this->allow_repeated_users){
			if($this->user_id_exists($id)){
				return false;	
			}
		}
		if(!$item=$this->create_item($id)){
			return false;	
		}
		return $this->add_item($item);
	}
	
	function create_item($user_id=false){
		$item = new mwmod_mw_users_list_item($user_id);
		return $item;
	}
	final function add_item($item){
		$index=sizeof($this->_items)+1;
		$this->_items[$index]=$item;
		$item->set_list($index,$this);
		if($id=$item->user_id+0){
			$this->_user_ids[$id]=$id;	
		}
		return $item;
	}
	final function get_items(){
		return $this->_items;	
	}
	final function user_id_exists($id){
		if(!$id=$id+0){
			return false;	
		}
		if($this->_user_ids[$id]){
			return true;	
		}
	}
	
	final function __get_priv_user_man(){
		if(!isset($this->user_man)){
			$this->user_man=false;
			if($man=$this->mainap->get_user_manager()){
				$this->user_man=$man;	
			}
		}
		return $this->user_man; 	
	}


}
?>