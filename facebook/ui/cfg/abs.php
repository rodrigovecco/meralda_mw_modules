<?php
abstract class mwmod_mw_facebook_ui_cfg_abs extends mwmod_mw_ui_sub_uiabs{
	public $sucods;
	public $frmTitle;
	function new_js_frm(){
		$frmjs=new mwmod_mw_jsobj_inputs_frmonpanel("frm");
		if($lbl=$this->getFrmTitle()){
			$frmjs->set_prop("lbl",$lbl);	
				
		}
		$this->set_js_inputs($frmjs);
		$this->jsfrm=$frmjs;
		return $frmjs;
	
	}
	function save_from_request(){
			
	}
	function set_js_in_page($js){
		$frmjs=$this->new_js_frm();
		
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		if($frmjs){
			$js->add_cont("var frm=".$frmjs->get_as_js_val().";\n");
			$js->add_cont("frm.append_to_container(".$var.".get_ui_elem('frmcontainer'));\n");
		}
	}

	function set_js_inputs($jsfrm){
		$inputsgrdata=$jsfrm->add_data_main_gr("cfg");
		$this->set_js_inputs_data($inputsgrdata);
		$inputjs=$jsfrm->add_submit($this->lng_get_msg_txt("save","Guardar"));
	}
	function set_js_inputs_data($inputsgrdata){
			
	}
	
	function getFrmTitle(){
		if($t=$this->frmTitle){
			return $t;	
		}
		return $this->get_title();
	}


	
	
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
	}
	function allowcreatesubinterfacechildbycode(){
		return true;	
	}
	
	
	function do_exec_page_in(){
		///////
		$container=$this->get_ui_dom_elem_container();
		
		$container->add_cont("");
		echo $container->get_as_html();
		

		
	}
	function getFBMan(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->getFBMan();
		}
			
	}
	
	function prepare_before_exec_no_sub_interface(){
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("inputsman.js");
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);

	}
	
	
}
?>