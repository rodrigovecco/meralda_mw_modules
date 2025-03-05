<?php
class mwmod_mw_ui_debug_tests_crop extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		
		$this->set_def_title("Crop");
		
		
	}
	
	function execfrommain_getcmd_sxml_test($params=array(),$filename=false){
		echo "Hola";
		
		
	}


	function do_exec_page_in(){
		$container= new mwmod_mw_html_elem();
		$url=$this->get_exec_cmd_sxml_url("test");
		
		
		$iframcontainer=$this->create_ui_dom_elem_iframe_and_frm_container();
		$container->add_cont($iframcontainer);
		$idiframe=$this->get_ui_elem_id_and_set_js_init_param("iframe");

		$uploaderhtml =new mwmod_mw_helper_croppermaster_uploaderhtml($url);
		$uploaderhtml->frm->set_att("target",$idiframe);
		$imgitemidinput=$uploaderhtml->frm->add_cont_elem();
		$imgitemidinput->set_tagname("input");
		$imgitemidinput->setAtts("name='itemid' type='hidden' id='itemUploadImgId'");
		
		$testinputs=$uploaderhtml->frm->add_cont_elem();
		$testinputs->add_class("input-group");
		$testinput=$testinputs->add_cont_elem("","input");
		$testinput->setAtts("id='cropTestCMD' class='form-control' placeholder='cmd'");
		$testinput=$testinputs->add_cont_elem("","input");
		$testinput->setAtts("id='cropTestMethod' class='form-control' placeholder='Method'");
		$e=$testinputs->add_cont_elem("","span");
		//$e->add_class("input-group-addon");
		$testinput=$e->add_cont_elem("","input");
		$testinput->setAtts("id='cropTestOption' class='form-control'  placeholder='Option'");
		$e=$testinputs->add_cont_elem("","span");
		$e->add_class("input-group-btn");
		$testinput=$e->add_cont_elem("TEST","button");
		$testinput->setAtts("id='cropTestDo' type='button'");
		$testinput->add_class("btn btn-default");
		
		
		$modal= new mwmod_mw_bootstrap_html_template_modal("avatar-modal",$this->lng_get_msg_txt("image","Imagen"));
		$modal_cont=$modal->cont_elem;
		$modal_cont->add_cont($uploaderhtml);
		
		$btn=$container->add_cont_elem("","button");
		$btn->setAtts('type="button" id="openCrop"');
		$btn->add_cont("Abrir dialogo");

		//$btn=$modal->new_open_btn($this->lng_get_msg_txt("change_image","Cambiar imagen"));
		//$container->add_cont($btn);
		$e=$container->add_cont_elem();
		$e->setAtts("id='pimgCrop'");
		
		$e->add_cont($modal);
		
		
		
		
		
		
		
		echo $container;
		
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont("var c=new CropAvatar($('#pimgCrop'),{aspectRatio:1,autoCropArea:1});\n");
		$js->add_cont("$('#openCrop').click(function(){c.click()});\n");
		$js->add_cont("$('#cropTestDo').click(function(){
			
			var e={
					method:	$('#cropTestMethod').val(),
					option:	$('#cropTestOption').val(),
					cmd:	$('#cropTestCMD').val(),
					
				}
			console.log('do ',e);
			c.cmd(e);
			});\n");
		echo $js;
		
		


		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	function prepare_js_and_css_mans(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);
		$cmutil=new mwmod_mw_helper_croppermaster_util();
		$cmutil->preapare_ui($this);
		$cmutil->preapare_ui_avatar($this);

		
		$jsman=$util->get_js_man();
		
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("mw_events.js");
		$jsman->add_item_by_cod_def_path("mw_objcol_adv.js");
		$jsman->add_item_by_cod_def_path("mw_nav_bar.js");
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		$jsman->add_item_by_cod_def_path("inputs/dxnormal.js");
		$jsman->add_item_by_cod_def_path("inputs/experimental.js");
		//$jsman->add_item_by_cod_def_path("inputs/container.js");
		
		
		
		
			
	}
	function do_exec_no_sub_interface(){
		$this->prepare_js_and_css_mans();
	}
}
?>