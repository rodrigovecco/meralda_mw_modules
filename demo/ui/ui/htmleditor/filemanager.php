<?php
class mwmod_mw_demo_ui_ui_htmleditor_filemanager extends mwmod_mw_demo_ui_abs{
	public $imageOnlyMode=false;
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title($this->lng_get_msg_txt("selectFile","Seleccionar archivo"));
		

		
	}
	function getFileProvider(){
		if($fp=$this->parent_subinterface->getFileProvider()){
			if($this->imageOnlyMode){
				$fp->setImagesOnlyMode();
			}
			return $fp;
		}
		
	}
	function is_single_mode(){
		return true;
	}
	function getFileProviderPathMan(){
		return $this->parent_subinterface->getFileProviderPathMan();
	}
	function do_exec_page_in(){

	
		
		$container=$this->get_ui_dom_elem_container_empty();
		$filemanagerContainer=$this->set_ui_dom_elem_id("filemanager");
		$filemangerID=$filemanagerContainer->get_dom_id();
		$container->add_cont($filemanagerContainer);
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
	

		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		

		$filemanagerParams=new mwmod_mw_jsobj_obj();
		$filemanagerParams->set_prop("name",$filemangerID);
		
		$filemanagerParams->set_prop("height",400);
		if($fp=$this->getFileProvider()){
			$fp->setDxFileManagerProps($filemanagerParams);
		}
		$params=array();
		if($this->imageOnlyMode){
			$params["mode"]="images";
		}
		
		$fileproviderparams=new mwmod_mw_jsobj_newobject("MeraldaFileManagerHelper");
		$fileproviderparams->set_prop("url",$this->parent_subinterface->get_exec_cmd_json_url("fileman",$params));
		if($dlurl=$this->parent_subinterface->get_exec_cmd_file_url("dlf")){
			$fileproviderparams->set_prop("downloadUrl",$dlurl);
		}
		$fnc=new mwmod_mw_jsobj_functionext();
		$fnc->add_fnc_arg("e");
		$fnc->add_cont("console.log('onSelectedFileOpened',e);\n");
		$fnc->add_cont("if(!e.file){\n");
		$fnc->add_cont("    console.warn('No se ha seleccionado ningún archivo');\n");
		$fnc->add_cont("    return;\n");
		$fnc->add_cont("}\n");
		
		$dlurl=$this->parent_subinterface->get_exec_cmd_file_url("dlf");
		$fnc->add_cont("var fileUrl='".$dlurl."'+e.file.path;\n");
		$fnc->add_cont("const params = new URLSearchParams(window.location.search);\n");
		$fnc->add_cont("const CKEditorFuncNum = params.get('CKEditorFuncNum');\n");
		$fnc->add_cont("if (window.opener && CKEditorFuncNum) {\n");
		$fnc->add_cont("    window.opener.CKEDITOR.tools.callFunction(CKEditorFuncNum, fileUrl);\n");
		$fnc->add_cont("    window.close();\n");
		$fnc->add_cont("} else {\n");
		$fnc->add_cont("    console.warn('CKEditorFuncNum no encontrado');\n");
		$fnc->add_cont("}\n");

	
		$filemanagerParams->set_prop("onSelectedFileOpened",$fnc);
		
		
		$js->add_cont("var fileProvider=".$fileproviderparams->get_as_js_val().";\n");
		
		$js->add_cont("var fmops=".$filemanagerParams->get_as_js_val().";\n");
		$js->add_cont("fmops.fileSystemProvider=fileProvider.createProvider();\n");

		
		$js->add_cont("$('#".$filemangerID."').dxFileManager(fmops);\n");

		
		///echo nl2br($modal->get_as_js_val());
		echo $js->get_js_script_html();
		
		
		
		return;
		
		

		
		
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
		$jsman->add_item_by_cod_def_path("mwdevextreme/filemanager.js");		
		
		
		
		//$jsman->add_item_by_cod_def_path("inputs/container.js");
		
		
		
		
			
	}
	
	function do_exec_no_sub_interface(){
		$this->prepare_js_and_css_mans();
	}


}