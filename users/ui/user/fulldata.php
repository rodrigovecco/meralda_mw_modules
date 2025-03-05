<?php
class mwmod_mw_users_ui_user_fulldata extends mwmod_mw_users_ui_user_abs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("edit_user","Editar usuario"));
		
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
		$item->etq=$this->lng_get_msg_txt("editar","Editar");
		
		if($subs=$this->get_subinterfaces_by_code("userimg",true)){
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
		$si=$this->add_new_subinterface(new mwmod_mw_users_ui_user_img("userimg",$this));
	}
	
	function get_html_for_parent_chain_on_child_title(){
		$txt=$this->get_title_route();
		$url=$this->get_url();
		return "<a href='$url'>$txt</a>";
	}

	function do_exec_page_in(){

		if(!$user=$this->get_current_item()){

			return false;

		}
		
		if(!$user->allowadmin_admin()){
			return false;	
		}
		$frm=$this->new_frm();
		$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		$dm=$user->get_user_data_man();
		$msg="";
		$cr->items_pref="nduser";
		$msgs=new mwmod_mw_html_elem();
		$msgs->only_visible_when_has_cont=true;
		
		$dm->save_full_data(new mwmod_mw_helper_inputvalidator_request("nduser.usernd"),$user,$msgs);
		//mw_array2list_echo($_REQUEST);
		$dm->set_user_datafull_cr($user,$cr);
		$btn=$cr->add_submit($this->lng_common_get_msg_txt("save","Guardar")."");
		$btnp=$btn->get_bootstrap_params();
		$btnp->set_prop("btn.class","btn btn-default btncus");

		/*
		$btn=$cr->add_btn("test","bbb");
		$btnhtml=$btn->get_btn_html_elem();
		$btnhtml->set_display_mode("info");
		$btnhtml->set_att("onclick","alert('sss')");
		*/
		$frm->set_datafieldcreator($cr);
		echo "<div class='mw_ui_frm_container'>";
		echo $frm->get_html();
		echo $msgs->get_as_html();
		echo "</div>";
		/*
		if($msg){
			$alert=new mwmod_mw_bootstrap_html_specialelem_alert($msg);
			echo $alert->get_as_html();
		}
		*/

		
		return;
		

		
	}
	
}
?>