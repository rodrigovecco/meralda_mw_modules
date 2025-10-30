<?php
abstract class mwmod_mw_ui_base_frm extends mwmod_mw_ui_base_basesubui{
	
	public $frmTitle;
	public $jsfrm;
	public $js_ui_class_name="mw_ui_frm";
	function create_js_ctrs(){
		$frmjs=new mwmod_mw_jsobj_inputs_frmonpanel("nd");
		if($lbl=$this->getFrmTitle()){
			$frmjs->set_prop("lbl",$lbl);	
				
		}
		$this->set_js_inputs($frmjs);
		$this->jsfrm=$frmjs;
		return $frmjs;
	
	}
	function save_from_request(){
		
		$input=new mwmod_mw_helper_inputvalidator_request("nd");
		if(!$input->is_req_input_ok()){
			return false;	
		}
		//mw_array2list_echo($input->get_value());
		if($nd=$input->getValueArray("data")){
			$this->saveData($nd);
		}
	
	}
	function saveData($nd){
		//override in child classes
	}
	function set_js_in_page($js){
		$ctrs=$this->create_js_ctrs();
		
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		$this->ui_js_init_params->set_prop("ctrs",$ctrs);
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		
	}

	function set_js_inputs($jsfrm){
		$inputsgrdata=$jsfrm->add_data_main_gr("nd");
		$gr=$inputsgrdata->addNewGr("data");
		$this->set_js_inputs_data($gr);
		$inputjs=$jsfrm->add_submit($this->lng_get_msg_txt("save","Guardar"));
	}
	/**
	 * @param mwmod_mw_jsobj_inputs_gr $inputsgrdata 
	 * @return void 
	 */
	function set_js_inputs_data($inputsgrdata){
		$input=$inputsgrdata->addNewChild("sample_input");
		$input->set_prop("lbl",$this->lng_get_msg_txt("sample_input","Input de ejemplo"));
	}
	
	function getFrmTitle(){
		if($t=$this->frmTitle){
			return $t;	
		}
		return $this->get_title();
	}


	
	
	
	
	function do_exec_page_in(){
		$this->save_from_request();
		$container=$this->get_ui_dom_elem_container_empty();
		
		$frmcontainer=$this->set_ui_dom_elem_id("ctrs");
		$container->add_cont($frmcontainer);
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_js_in_page($js);
		echo $js->get_js_script_html();
		
		

		
	}
	
	function prepare_before_exec_no_sub_interface(){
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$this->maininterface->jsmanager;
		
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("mw_date.js");
		
		$jsman->add_item_by_cod_def_path("inputs/inputs.js");
		$jsman->add_item_by_cod("/res/js/mw_bootstrap_helper.js");
		$jsman->add_item_by_cod("/res/js/inputs/frm.js");
		$jsman->add_item_by_cod("/res/js/inputs/other.js");
		$jsman->add_item_by_cod("/res/js/inputs/date.js");
		$jsman->add_item_by_cod("/res/js/inputs/dx.js");
		$jsman->add_item_by_cod("/res/js/arraylist.js");
		
		$jsman->add_item_by_cod("/res/js/ui/mwui.js");
		$jsman->add_item_by_cod("/res/js/ui/mwui_frm.js");
		
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		$jsman->add_item_by_cod_def_path("inputs/frm.js");
		$jsman->add_item_by_cod_def_path("inputs/container.js");

		
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);

	}
	
	
}
?>