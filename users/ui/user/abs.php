<?php
class mwmod_mw_users_ui_user_abs extends mwmod_mw_ui_sub_withfrm{
	function getUman(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->getUman();
		}
		
	}
	function get_or_set_current_item_by_req(){
		if($user=$this->get_current_item()){
			return $user;
		}
		if(!$uman=$this->getUman()){
			return false;	
		}
		if(!$user=$uman->get_user($_REQUEST["iditem"])){
			return false;	
		}
		$this->set_current_item($user);
		$this->set_url_param("iditem",$user->get_id());
		return $user;
			
	}
	
	function set_current_edit_user($user){
		if(!$user){
			return false;	
		}
		$this->set_current_item($user);
		$this->set_url_param("iditem",$user->get_id());
		return $user;

	}
	
	function do_exec_no_sub_interface(){
		if(!$uman=$this->getUman()){
			return false;	
		}
		if(!$user=$uman->get_user($_REQUEST["iditem"])){
			return false;	
		}
		$this->set_current_item($user);
		$this->set_url_param("iditem",$user->get_id());

		
	}
	
	function get_html_for_parent_chain_on_child_title(){
		$txt=$this->get_title_route();
		$url=$this->get_url();
		return "<a href='$url'>$txt</a>";
	}
	function get_title_for_box(){
		if($user=$this->get_current_item()){
			return $user->get_real_and_idname()." - ".$this->get_title();
		}
		return $this->get_title();	
	}
	
	
	function get_title_route(){
		if($user=$this->get_current_item()){
			return $user->get_real_and_idname();
		}
		return $this->get_title();	
	}
	
	function is_allowed_for_current_item(){
		if(!$user=$this->get_current_item()){
			return false;
		}
		return $user->allowadmin_admin();
	}
	function is_allowed(){
		return $this->allow("adminusers");	
	}
	
}
?>