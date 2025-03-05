<?php
//
class mwmod_mw_users_groups_item extends mwmod_mw_manager_item{
	private $_users_ids;
	function __construct($tblitem,$man){
		$this->init($tblitem,$man);	
		$this->enable_strdata(true);
		$this->enable_treedata(true);
		
	}
	final function unset_data(){
		unset($this->_users_ids);
	}
	function save_from_checkbox_input($input){
		if(!is_array($input)){
			return false;	
		}
		$list=array();
		foreach($input as $id=>$v){
			if($id=mw_get_number($id)){
				if($v){
					$list[$id]=$id;	
				}
			}
		}
		$srt=implode(",",$list);
		if(!$data=$this->get_strdata_item("users")){
			return;
		}
		$data->set_data($srt);
		$data->save();
		$this->unset_data();
		return true;
		
		
		
	}
	function save_user_belongs($user,$val){
		if(!$user){
			return false;	
		}
		if(!$val){
			if(!$this->contains_user($user)){
				return false;	
			}
		}else{
			if($this->contains_user($user)){
				return false;	
			}
				
		}
		return $this->do_save_user_belongs($user->id,$val);
		
			
	}
	final function do_save_user_belongs($id,$val){
		if(!$id=$id+0){
			return false;	
		}
		$this->init_users_ids();
		if(!$val){
			unset($this->_users_ids[$id]);	
		}else{
			$this->_users_ids[$id]=$id;		
		}
		if(!$data=$this->get_strdata_item("users")){
			return;
		}
		$data->set_data(implode(",",$this->_users_ids));
		$data->save();
		return true;
		
	}
	function contains_user($user){
		if(!$user){
			return false;	
		}
		return $this->contains_user_id($user->id);
	}
	final function get_users_id_list(){
		$this->init_users_ids();
		return $this->_users_ids;
			
	}
	final function init_users_ids(){
		if(isset($this->_users_ids)){
			return;	
		}
		$this->_users_ids=array();
		if(!$data=$this->get_strdata_item("users")){
			return;
		}
		if(!$str=$data->get_data()){
			return;	
		}
		$list=explode(",",$str);
		foreach($list as $id){
			if($id=mw_get_number($id)){
				$this->_users_ids[$id]=$id;	
			}
		}
		
	}
	final function contains_user_id($id){
		if(!$id=$id+0){
			return false;	
		}
		$this->init_users_ids();
		return $this->_users_ids[$id];
	}
	function do_save($input){
		if(!is_array($input)){
			return false;	
		}
		$this->do_save_data($input["data"]);
		
	
	}
	function set_admin_inputs($gr){
		$subgr=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_group("data"));
		$this->set_admin_inputs_data($subgr);
		
		
	
	}

}
?>