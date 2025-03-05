<?php
class mwmod_mw_ui_debug_ckeditor extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("CKEditor");
		
	}
	function prepare_js_and_css_mans(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->addCKEditor();
		$util->preapare_ui();
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);

		
		$jsman=$util->get_js_man();
		
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("mw_events.js");
		$jsman->add_item_by_cod_def_path("mw_objcol_adv.js");
		$jsman->add_item_by_cod_def_path("mw_nav_bar.js");
		$jsman->add_item_by_cod_def_path("mw_bootstrap_helper.js");
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		$jsman->add_item_by_cod_def_path("inputs/dxnormal.js");
		$jsman->add_item_by_cod_def_path("inputs/experimental.js");
		$jsman->add_item_by_cod_def_path("inputs/dxnormal.js");	
			
		$jsman->add_item_by_cod_def_path("inputs/ckeditor.js");		
		$jsman->add_item_by_cod_def_path("mw_placeholders.js");		
		
		
		
		//$jsman->add_item_by_cod_def_path("inputs/container.js");
		
		
		
		
			
	}
	
	function do_exec_no_sub_interface(){
		$this->prepare_js_and_css_mans();
	}
	function do_exec_page_in(){
		
		$container=$this->get_ui_dom_elem_container_empty();
		$frmcontainer=$this->set_ui_dom_elem_id("frmcontainer");
		$container->add_cont($frmcontainer);
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
	

		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$frmjs=new mwmod_mw_jsobj_inputs_frmonpanel();
		$jsgr=$frmjs->add_data_main_gr();
		$jsinput=$jsgr->add_new_child("txtph","mw_datainput_item_ckeditor");
		$jsinput->set_prop("lbl","Con ph");
		$jsinput->set_prop("editorcfg.extraPlugins","placeholder");
		$pholders=new mwmod_mw_jsobj_objcoladv_base("placeholderman","mw_placeholders");
		$jsinput->set_prop("editorcfg.mw.placeholderman",$pholders);
		$ph=$pholders->add_new_child("test");
		$ph=$pholders->add_new_child("test1");
		$ph->set_prop("lbl","Otro");
		$phinputs=new mwmod_mw_jsobj_inputs_input("inputs","mw_datainput_item_group");
		$phinput=$phinputs->add_new_child("averqw");
		$phinput->set_prop("lbl","Veamos 111");
		$phinputg=$phinputs->add_data_gr("mas");
		$phinput=$phinputg->add_new_child("q");
		$phinput->set_prop("lbl","QQQ");
		$phinput=$phinputg->add_new_child("w");
		$phinput->set_prop("lbl","www");
		$ph->set_prop("inputs",$phinputs);
		
		
		$ph=$pholders->add_new_child("xxx.w");
		$ph=$pholders->add_new_child("qqq.rr");
		$ph->set_prop("lbl","Otro más");
		$phinputs=new mwmod_mw_jsobj_inputs_input("inputs","mw_datainput_item_group");
		$phinput=$phinputs->add_new_child("aver");
		$phinput->set_prop("lbl","Veamos");
		$phinputg=$phinputs->add_data_gr("mas");
		$phinput=$phinputg->add_new_child("q");
		$phinput->set_prop("lbl","QQQ");
		$phinput=$phinputg->add_new_child("w");
		$phinput->set_prop("lbl","www");
		$ph->set_prop("inputs",$phinputs);
		
		
		
		
		/*
		$frmjs=new mwmod_mw_jsobj_inputs_frmonpanel();
		$jsgr=$frmjs->add_data_main_gr();
		$jsinput=$jsgr->add_new_child("txtph","mw_datainput_item_ckeditor");
		$jsinput->set_prop("lbl","Con ph");
		*/

		
		
		
		$jsinput=$jsgr->add_new_child("txt1","mw_datainput_item_ckeditor");
		$jsinput->set_prop("lbl","Text 1");
		$jsinput->set_prop("editorcfg.extraPlugins","placeholder"); 
		$jsinput->set_prop("editorcfg.mw.placeholderman",$pholders);

		$jsinput=$jsgr->add_new_child("txt2","mw_datainput_item_ckeditor");
		$jsinput->set_prop("lbl","Text 2");
		$jsinput->set_prop("editorcfg.toolbar","OpenXML");
		
		$jsinput=$jsgr->add_new_child("wwwww","mw_datainput_item_with_placeholders");
		$jsinput->set_prop("lbl","Text 1");
		$jsinput->set_prop("placeholderman",$pholders);
		
		
		
		$jsinputbtns=$jsgr->add_new_child("btns","mw_datainput_item_btnsgroup");
		$jsinput=$jsinputbtns->add_new_child("btn","mw_datainput_item_btn");
		$jsinput->set_prop("lbl","Modal");
		$oncl=$jsinput->addFunction("onclick");
		$oncl->add_cont("modal.show();");
		$frmjs->add_submit("Guardar");
		
		
		$modal=new mwmod_mw_jsobj_newobject("mw_bootstrap_helper_modal");
		$modal->set_props_by_dot_cod_list(array(
			"title"=>"Modal",
			"body"=>"xxxx",
			"sadasd.eer.t"=>1
		));
		

		$js->add_cont("var modal=".$modal->get_as_js_val().";\n");
		$js->add_cont("modal.appendToDocument();\n");
		//$js->add_cont("modal.show();\n");
		
		$js->add_cont("var frm=".$frmjs->get_as_js_val().";\n");
		$js->add_cont("frm.append_to_container(".$var.".get_ui_elem('frmcontainer'));\n");
		
		echo nl2br($modal->get_as_js_val());
		echo $js->get_js_script_html();
		
		
		
		return;
		
		

		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>