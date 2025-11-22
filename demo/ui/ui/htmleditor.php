<?php
class mwmod_mw_demo_ui_ui_htmleditor extends mwmod_mw_demo_ui_abs{
	public $fileProvider;
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title($this->lng_get_msg_txt("htmlEditor","Editor HTML"));
		

		
	}
	function createFileProvider(){
		return false;//por ahora lo anulamos estamos probaldno
		if(!$demoMan=$this->getDemoMan()){
			return false;	
		}
		$pathMan=$demoMan->get_sub_path_man("htmleditorfiles",false);
		$fileProvider=new mwmod_mw_devextreme_filemanager_provider($pathMan);
		$fileProvider->setAllowAll();
		return $fileProvider;
	}
	function getFileProvider(){
		if(!$this->fileProvider){
			$this->fileProvider=$this->createFileProvider();
			
		}
		return $this->fileProvider;
		
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
	function execfrommain_getcmd_json_test($params=array(), $filename=false){
		$data=array();
		$data["ok"]=true;
		$data["msg"]="Test JSON OK";
		$data["time"]=date("Y-m-d H:i:s");
		
		$this->json_output_data($data);
		
	}
	function execfrommain_getcmd_json_fileman($params=array(), $filename=false){
		if(!$this->is_allowed()){
			return $this->json_output_data(["success"=>false,"msg"=>"Not allowed"]);
		}
		if(!$fileProvider=$this->getFileProvider()){
			return $this->json_output_data(["success"=>false,"msg"=>"No file provider"]);
		}

		$action = $_REQUEST["action"] ?? "";
		$res = $fileProvider->handle_request($action);

		// Importantísimo: convertir el array del provider en JSON real
		return $this->json_output_data($res);
	}
	
	function do_exec_page_in(){

		$jsonURL=$this->get_exec_cmd_json_url("test",array("action"=>"list"));
		echo "<a href='".$jsonURL."' target='_blank'>Test JSON URL $jsonURL</a><br/>";
		
		$strdataMan=null;
		if($demoMan=$this->getDemoMan()){
			$strdataMan=$demoMan->get_strdata_item("html","ui/htmleditor");
		
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
		$filemanagerContainer=$this->set_ui_dom_elem_id("filemanager");
		$filemangerID=$filemanagerContainer->get_dom_id();
		$container->add_cont($frmcontainer);
		$container->add_cont($filemanagerContainer);
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
	

		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$frmjs=new mwmod_mw_jsobj_inputs_frmonpanel();
		$jsgr=$frmjs->add_data_main_gr();
		$jsinput=$jsgr->add_new_child("html","mw_datainput_item_ckeditor");
		if($strdataMan){
			$jsinput->set_value($strdataMan->get_data());
		}

		$jsinput->set_prop("lbl",$this->lng_get_msg_txt("htmlContent","Contenido HTML"));
		$jsinput->set_prop("editorcfg.extraPlugins","placeholder");
		$pholders=new mwmod_mw_jsobj_objcoladv_base("placeholderman","mw_placeholders");
		$jsinput->set_prop("editorcfg.mw.placeholderman",$pholders);
		$ph=$pholders->add_new_child("customer.fullName");
		$ph->set_prop("lbl","Customer full name");
		$ph=$pholders->add_new_child("customer.address");
		$ph->set_prop("lbl","Customer address");
		$phinputs=new mwmod_mw_jsobj_inputs_input("inputs","mw_datainput_item_group");
		$phinput=$phinputs->add_new_child("addressLine1");
		$phinput->set_prop("lbl","Street");
		$phinputg=$phinputs->add_data_gr("addressExtra");
		$phinput=$phinputg->add_new_child("city");
		$phinput->set_prop("lbl","City");
		$phinput=$phinputg->add_new_child("postalCode");
		$phinput->set_prop("lbl","Postal code");
		$ph->set_prop("inputs",$phinputs);


		
		
		$ph=$pholders->add_new_child("order.number");
		$ph->set_prop("lbl","Order number");
		$ph=$pholders->add_new_child("order.summary");
		$ph->set_prop("lbl","Order summary");
		$phinputs=new mwmod_mw_jsobj_inputs_input("inputs","mw_datainput_item_group");
		$phinput=$phinputs->add_new_child("headline");
		$phinput->set_prop("lbl","Headline");
		$phinputg=$phinputs->add_data_gr("details");
		$phinput=$phinputg->add_new_child("intro");
		$phinput->set_prop("lbl","Intro text");
		$phinput=$phinputg->add_new_child("cta");
		$phinput->set_prop("lbl","Call to action");
		$ph->set_prop("inputs",$phinputs);
		
		
		
	
		
		
		
		$frmjs->add_submit("Guardar");
		
		
		
		$js->add_cont("var frm=".$frmjs->get_as_js_val().";\n");
		$js->add_cont("frm.append_to_container(".$var.".get_ui_elem('frmcontainer'));\n");

		$filemanagerParams=new mwmod_mw_jsobj_obj();
		$filemanagerParams->set_prop("name",$filemangerID);
		
		$filemanagerParams->set_prop("height",400);
		if($fp=$this->getFileProvider()){
			$fp->setDxFileManagerProps($filemanagerParams);
		}
		/*
		$filemanagerParams->set_prop("permissions.create",true);
		$filemanagerParams->set_prop("permissions.delete",true);
		$filemanagerParams->set_prop("permissions.rename",true);
		$filemanagerParams->set_prop("permissions.upload",true);
		$filemanagerParams->set_prop("permissions.download",true);
		*/
		$fileproviderparams=new mwmod_mw_jsobj_newobject("MeraldaFileManagerHelper");
		$fileproviderparams->set_prop("url",$this->get_exec_cmd_json_url("fileman"));
		$js->add_cont("var fileProvider=".$fileproviderparams->get_as_js_val().";\n");
		
		$js->add_cont("var fmops=".$filemanagerParams->get_as_js_val().";\n");
		$js->add_cont("fmops.fileSystemProvider=fileProvider.createProvider();\n");

		
		$js->add_cont("$('#".$filemangerID."').dxFileManager(fmops);\n");

		
		///echo nl2br($modal->get_as_js_val());
		echo $js->get_js_script_html();
		
		
		
		return;
		
		

		
		
	}
	/** @return false|mwmod_mw_demo_man_man|null  */
	function getDemoMan(){
		return $this->mainap->get_submanager("demo");
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}


	
}
?>
