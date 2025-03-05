<?php
class mwmod_mw_addon_schev_ui_new extends mwmod_mw_ui_sub_uiabs{
	
	public $selected_type;
	function __construct($cod,$parent){
		$this->set_items_man($parent->items_man);
		
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("new_scheduled_task","Nueva tarea programada"));
		$this->js_ui_class_name="ui_mwaddon_schev_edit";

		
	}
	function get_selected_ui_header_title(){
		
		if(!$this->selected_type){
			return $this->get_title_for_box();	
		}
		return $this->selected_type->new_ui_title();
		return $this->get_selected_ui_header_title_and_sub_title($this->get_title(),$this->selected_type->new_ui_title());
	}
	
	function set_selected_type_from_request(){
		if(!$this->items_man){
			return false;	
		}
		if(!$t=$_REQUEST["type"]){
			return false;	
		}
		if($type=$this->items_man->get_type($t)){
			$this->set_url_param("type",$type->cod);
			$this->selected_type=$type;
			return $type;	
		}
			
	}
	function do_exec_no_sub_interface(){
		$this->set_selected_type_from_request();
		
		if($item=$this->proc_input()){
			
			if($this->parent_subinterface){
				$url=$this->parent_subinterface->get_url_sub_interface_by_dot_cod("selitem.edit",array("iditem"=>$item->get_id()));
				//echo $url;
				ob_end_clean();
				header("Location: $url");
				die();
			}
			
		}
		
		$this->prepare_js_and_css_mans();
	}
	function proc_input(){
		if(!$this->is_allowed()){
			return false;	
		}
		if(!$this->items_man){
			return false;	
		}
		
		
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if(!$input->is_req_input_ok()){
			return false;
		}
		if(!$nd=$input->get_value_by_dot_cod_as_list("data")){
			return false;	
		}
		if(!$user=$this->get_admin_current_user()){
			return false;
		}
		if($this->selected_type){
			if(!$nd["type"]){
				$nd["type"]=$this->selected_type->cod;	
			}
		}
		
		$nd["owner"]=$user->get_id();
		$nd["active"]=false;
		if($item=$this->items_man->create_new_item($nd)){
			if($this->selected_type){
				$item->save_from_input_man($input);	
			}
			return $item;
		}
			
	}
	function set_js_inputs_no_type($inputsgrmain){
		$inputsgrdata=$inputsgrmain->add_data_gr("data");
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_input("name"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("name","Nombre"));
		$inputjs->set_prop("state.required",true);
		$inputjs=$inputsgrdata->add_child(new mwmod_mw_jsobj_inputs_select("type"));
		$inputjs->set_prop("lbl",$this->lng_common_get_msg_txt("type","Tipo"));
		$inputjs->set_prop("state.required",true);
		if($types=$this->items_man->get_types()){
			foreach($types as $cod=>$type){
				$inputjs->add_select_option($cod,$type->get_name());	
			}
		}
		
			
	}
	function add_2_sub_interface_mnu($mnu,$checkallowed=true){
		if($this->items_man){
			if($this->items_man->ui_new_by_type){
				return $this->add_2_sub_interface_mnu_by_types($mnu);	
			}
		}
		//ver mwmod_mw_ui_debug_htmltemplate
		$this->add_2_mnu($mnu,$checkallowed);
	}
	function add_2_sub_interface_mnu_by_types($mnu,$checkallowed=true){
		if(!$mnu){
			return false;	
		}
		if($checkallowed){
			if(!$this->is_allowed_on_mnu()){
				return false;
			}
		}
		if(!$types=$this->items_man->get_types()){
				
			return false;
		}
		if(sizeof($types)==1){
			if($type=array_pop($types)){
				$item=$mnu->add_new_item($this->get_cod_for_mnu(),$type->new_ui_title(),$this->get_url(array("type"=>$type->cod)));
				return;
			}
		
		}
		
		$item=new mwmod_mw_mnu_items_dropdown_single($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu,$this->get_url(array("type"=>"_none")));
		$mnu->add_item_by_item($item);
		
		if($this->is_current()){
			$item->set_active(true);	
		}
		
		foreach($types as $type){
			$sitem=new mwmod_mw_mnu_mnuitem($this->get_cod_for_mnu()."_type_".$type->cod,$type->new_ui_title(),$item,$this->get_url(array("type"=>$type->cod)));
			$item->add_item_by_item($sitem);
			if($this->selected_type){
				if($this->selected_type->cod==$type->cod){
					$sitem->active=true;	
				}
			}
		}

		/*
		if(!$item=$mnu->add_new_item($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$this->get_url())){
			
			return false;	
		}
		$this->prepare_mnu_item($item);
		*/
		
		/*
		if($mnu->allow_sub_menus()){
			$this->add_sub_mnus($item,$checkallowed);	
		}
		*/
		
		return $item;

	
	}

	function do_exec_page_in(){
		
		$container=$this->get_ui_dom_elem_container_empty();
		$frmcontainer=$this->set_ui_dom_elem_id("frm");
		
		
		
		$container->add_cont($frmcontainer);
		
		
		
		
		
		
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$frmjs=new mwmod_mw_jsobj_inputs_frmonpanel();
		
		
		$frmjs->add_submit($this->lng_common_get_msg_txt("create","Crear"));
		$inputsgrmain=$frmjs->add_data_main_gr("nd");
		if($this->selected_type){
			$this->selected_type->set_new_js_inputs($inputsgrmain);	
		}else{
			$this->set_js_inputs_no_type($inputsgrmain);	
		}
		
		
		
		
		//$item->set_edit_js_inputs($inputsgrmain);
		$this->ui_js_init_params->set_prop("frm",$frmjs);
		$var=$this->get_js_ui_man_name();
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");

		echo $js->get_js_script_html();
		
		return;

		
		
		
		

		
	}
	function prepare_js_and_css_mans(){
		/*
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);

		
		$jsman=$util->get_js_man();
		
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		*/
		
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
		
		

		
		
			
	}
	
	function is_allowed(){
		if($this->items_man){
			return $this->items_man->is_allowed_ui($this);	
		}
		return false;
	}

}
?>