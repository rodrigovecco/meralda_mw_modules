<?php
/**
 * Demo page for mw_datainput_item_tinymce.
 *
 * It extends the CKEditor demo so it inherits the DevExtreme file manager
 * provider, its JSON commands (json_fileman / file_dlf) and the fileman/imgfm
 * popup subinterfaces. Only the form and the JS/CSS list change.
 *
 * URL: /admin/index.php?sui1=tinymceeditor&sui=ui&ui=demo
 */
class mwmod_mw_demo_ui_ui_tinymceeditor extends mwmod_mw_demo_ui_ui_htmleditor{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title($this->lng_get_msg_txt("htmlEditorTinyMCE","Editor HTML (TinyMCE)"));
	}
	function prepare_js_and_css_mans(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);

		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->addTinyMCE();
		$util->preapare_ui();
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);

		$jsman=$util->get_js_man();

		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("mw_events.js");
		$jsman->add_item_by_cod_def_path("mw_objcol_adv.js");
		$jsman->add_item_by_cod_def_path("mw_bootstrap_helper.js");
		$jsman->add_item_by_cod_def_path("inputs/dxnormal.js");

		$jsman->add_item_by_cod_def_path("inputs/tinymce.js");
		$jsman->add_item_by_cod_def_path("mw_placeholders.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/filemanager.js");
	}
	function do_exec_page_in(){
		$filemanUIUrl=$this->get_url_subinterface("fileman");
		$imgFMURL=$this->get_url_subinterface("imgfm");
		echo "<p>TinyMCE input. File browser served by the same DevExtreme file manager.</p>";
		echo "<p><a href='".$filemanUIUrl."' target='_blank'>File Manager UI</a></p>";

		$strdataMan=null;
		if($demoMan=$this->getDemoMan()){
			$strdataMan=$demoMan->get_strdata_item("html","ui/tinymceeditor");
		}
		$inputMan=new mwmod_mw_helper_inputvalidator_request("data");
		if($inputMan->is_req_input_ok()){
			if($val=$inputMan->getTextRawValue("html")){
				if($strdataMan){
					$strdataMan->set_data_and_save($val);
				}
			}
		}

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

		// All the editor configuration goes through the PHP helper, so the demo
		// also documents the intended usage.
		$jsinput=$jsgr->addNewTinyMCE("html",$this->lng_get_msg_txt("htmlContent","Contenido HTML"));
		if($strdataMan){
			$jsinput->set_value($strdataMan->get_data());
		}
		$jsinput->setHeight(420)
			->setFilePickerUrls($filemanUIUrl,$imgFMURL);

		// Placeholders: same catalog definition used by the CKEditor demo.
		$jsinput->addPlaceholder("customer.fullName","Customer full name");
		$addressInputs=$jsinput->addPlaceholderWithParams("customer.address","Customer address");
		$addressInputs->add_new_child("addressLine1")->set_prop("lbl","Street");
		$extraGroup=$addressInputs->add_data_gr("addressExtra");
		$extraGroup->add_new_child("city")->set_prop("lbl","City");
		$extraGroup->add_new_child("postalCode")->set_prop("lbl","Postal code");

		$jsinput->addPlaceholder("order.number","Order number");

		$frmjs->add_submit("Guardar");
		$js->add_cont("var frm=".$frmjs->get_as_js_val().";\n");
		$js->add_cont("frm.append_to_container(".$var.".get_ui_elem('frmcontainer'));\n");

		echo $js->get_js_script_html();
	}
}
?>
