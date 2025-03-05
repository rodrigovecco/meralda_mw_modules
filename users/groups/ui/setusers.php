<?php
class mwmod_mw_users_groups_ui_setusers extends mwmod_mw_ui_sub_withfrm{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_common_get_msg_txt("users_on_group","Usuarios del grupo"));
		
	}
	function do_exec_no_sub_interface(){
		
		
		/*
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		*/
		/*


		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("ui/users/admin_groups.js");
		*/
		
		if(!$uman=$this->mainap->get_user_manager()){
			return false;	
		}
		if(!$man=$uman->get_groups_man()){
			return false;	
		}
		if($item=$man->get_item($_REQUEST["iditem"])){
			$this->set_current_item($item);
			$this->set_url_param("iditem",$item->get_id());
				
		}
		

	}
	
	function do_exec_page_in(){
		
		
		if(!$item=$this->get_current_item()){
			$this->output_bt_operation_not_allowed_html_elem();
			return false;
		}
		if(!$uman=$this->mainap->get_user_manager()){
			return false;	
		}
		
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if($input->is_req_input_ok()){
			if($nd=$input->get_value_by_dot_cod_as_list("selectedusers")){
				$item->save_from_checkbox_input($nd);
				//mw_array2list_echo($nd);	
			}
			//return false;	
		}
		/*
		if($input->get_value_by_dot_cod("new.data.name")){
			if($nd=$input->get_value_by_dot_cod_as_list("new")){
				//mw_array2list_echo($nd);
				$man->create_new_item_from_full_input($nd);
			}
		}
		*/
		
		
		
		$frm=$this->new_frm();
		$cr=$this->new_datafield_creator();
		$cr->items_pref="nd";
		$tit=$this->lng_common_get_msg_txt("users_on_group","Usuarios del grupo")." ".$item->get_name();
		$inputgr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("selectedusers",$tit));
		
		if($users=$uman->get_active_users_or_in_idlist($item->get_users_id_list())){
			foreach($users as $idu=>$u){
				$chk=$inputgr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_checkbox($idu,$u->get_real_and_idname()));
				if($item->contains_user($u)){
					$chk->set_value(1);
				}
				
			}
			
		}
		$cr->add_submit($this->lng_common_get_msg_txt("save","Guardar"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();
		
		

		return;
		
		

		
	}
	

	function is_allowed(){
		return $this->allow("adminusers");	
	}
	
}
?>