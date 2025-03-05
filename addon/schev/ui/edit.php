<?php
class mwmod_mw_addon_schev_ui_edit extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->set_items_man($parent->items_man);
		
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("edit_scheduled_task","Editar tarea programada"));
		$this->js_ui_class_name="ui_mwaddon_schev_edit";
		//
		$this->debug_mode=true;
		
	}
	function get_selected_ui_header_title(){
		if(!$item=$this->get_current_item()){
			return $this->get_title_for_box();	
		}
		return $this->get_selected_ui_header_title_and_sub_title($item->get_name());
	}
	
	/*
	function get_js_gr_add_schedule(){
		$gr=new mwmod_mw_jsobj_inputs_input("add","mw_datainput_item_group");
		
		
		
			
	}
	*/
	function do_exec_page_in(){
		if(!$this->is_allowed()){
			return false;	
		}
		
		if(!$item=$this->get_current_item()){
			return false;
		}
		$this->set_ui_js_params();
		
		
		
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if($input->is_req_input_ok()){
			$notificationjs=new mwmod_mw_devextreme_objs_notifycfg();
			$this->ui_js_init_params->set_prop("popup_notify",$notificationjs);
			$item->save_result_notification_js=$notificationjs;
			if($item->save_from_input_man($input)){
					
			}
			
		}

		
		
		$container=$this->get_ui_dom_elem_container_empty();
		$frmcontainer=$this->set_ui_dom_elem_id("frm");
		
		
		
		$container->add_cont($frmcontainer);
		
		
		
		
		
		
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		//$this->set_ui_js_params();
		$frmjs=new mwmod_mw_jsobj_inputs_frmonpanel();
		
		
		$frmjs->add_submit($this->lng_common_get_msg_txt("save","Guardar"));
		$inputsgrmain=$frmjs->add_data_main_gr("nd");
		$item->set_edit_js_inputs($inputsgrmain);
		$this->ui_js_init_params->set_prop("frm",$frmjs);
		$var=$this->get_js_ui_man_name();
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");

		echo $js->get_js_script_html();
		
		
		
		//mw_array2list_echo($this->items_man->debug_sumary_dates());
		
		

		
		
		
		
	}
	function do_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$item=new mwmod_mw_html_manager_item_jsexternal("ui_mwaddon_schev_edit","/res/mwaddon/schev/ui_edit.js");
		$util->add_js_item($item);

		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		$jsman->add_item_by_cod_def_path("inputs/datagrid.js");
		
		
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);
		
		
		if(!$this->items_man){
			return false;	
		}
		if(!$item=$this->items_man->get_item($_REQUEST["iditem"])){
			return false;	
		}
		$this->set_current_item($item);
		$this->set_url_param("iditem",$item->get_id());
		
		
		
	}
	function is_allowed(){
		if($this->items_man){
			return $this->items_man->is_allowed_ui($this);	
		}
		return false;
	}

}
?>