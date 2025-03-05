<?php
class mwmod_mw_users_ui_user extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("user","Usuario"));
		
	}
	function getUman(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->getUman();
		}
		
	}
	
	
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		
		$mnu = new mwmod_mw_mnu_mnu();
		if($this->parent_subinterface){
			$item=$this->parent_subinterface->add_2_mnu($mnu);	
		}
		if($user=$this->get_or_set_current_item_by_req()){
			//$item->etq="dd";
		}
		$item=$this->add_2_mnu($mnu);
		$item->etq=$this->lng_get_msg_txt("information","Información");
		
		if($subs=$this->get_subinterfaces_by_code("userdata,userimg,userrols,useraccessdata,changepass",true)){
			foreach($subs as $su){
				$su->set_current_edit_user($user);
				if($su->is_allowed_for_current_item()){
				
					$su->add_2_sub_interface_mnu($mnu);	
				}
			}
		}
		
		return $mnu;
	}
	
	function load_all_subinterfases(){
		
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_user_data("userdata",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_user_accessdata("useraccessdata",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_user_rols("userrols",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_user_pass("changepass",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_user_img("userimg",$this));
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
	function get_html_for_parent_chain_on_child_title(){
		$txt=$this->get_title_route();
		$url=$this->get_url();
		return "<a href='$url'>$txt</a>";
	}
	
	
	function get_title_route(){
		if($user=$this->get_or_set_current_item_by_req()){
			return $user->get_idname();
		}
		return $this->get_title();	
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
	function get_title_for_box(){
		if($user=$this->get_current_item()){
			return $user->get_real_and_idname();
		}
		return $this->get_title();	
	}

	function do_exec_page_in(){
		
		if(!$user=$this->get_current_item()){
			return false;
		}

		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$dm=$user->get_user_data_man();
		$dm->set_user_info_cr($user,$cr);
		$frm->set_datafieldcreator($cr);
		echo "<div class='mw_ui_frm_container'>";
		echo $frm->get_html();
		echo "</div>";

		
		return;
		

		
	}
	function is_allowed(){
		return $this->allow("adminusers");	
	}
	
}
?>