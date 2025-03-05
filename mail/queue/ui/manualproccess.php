<?php
class mwmod_mw_mail_queue_ui_manualproccess extends mwmod_mw_ui_sub_uiabs{
	
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("mailqueue_manualsend","Cola de correos - Envío manual"));
		$this->js_ui_class_name="mw_modules_mailqueue_ui_manualproccess";
		$this->debug_mode=true;
		
	}
	function execfrommain_getcmd_sxml_procqueue($params=array(),$filename=false){
		$xml=$this->new_getcmd_sxml_answer(false);
		if(!$this->is_allowed()){
			$xml->root_do_all_output();
			return false;
		}
		$xml->set_prop("ok",true);
		$js=new mwmod_mw_jsobj_obj();
		//
		$xml_js=new mwmod_mw_data_xml_js("jsresponse",$js);
		
		
		$xml->add_sub_item($xml_js);
		$queuemailman=$this->mainap->get_submanager("mailqueue");
		$queuemailman->jsDebugResponse=$js;
		if($_REQUEST["debugmode"]){
			$queuemailman->dontMarkMsgAsSent=true;
		}
		$queuemailman->send_queue();
		$js->set_prop("msgsleft",$queuemailman->load_msgs_to_send_num());
		
		

		$xml->root_do_all_output();
	
	}

	function getJSCtrs(){
		$inputsgr=new mwmod_mw_jsobj_inputs_input("ctrs","mw_datainput_item_group");
		
		$inputjs=$inputsgr->add_child(new mwmod_mw_jsobj_inputs_input("auto","mw_datainput_item_checkbox"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("automatic_proccess","Proceso automático"));
		$inputjs->set_prop("horizontal",true);
		
		$inputjs=$inputsgr->add_child(new mwmod_mw_jsobj_inputs_input("debugmode","mw_datainput_item_checkbox"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("debugmode","Modo de pruebas"));
		$inputjs->set_prop("horizontal",true);
		$btnsgr=new mwmod_mw_jsobj_inputs_input("recordbtns","mw_datainput_item_btnsgroup");
		$btnsgr=$inputsgr->add_child(new mwmod_mw_jsobj_inputs_input("btns","mw_datainput_item_btnsgroup"));
		$btnsgr->set_prop("horizontal",true);
		$inputjs=$btnsgr->add_child(new mwmod_mw_jsobj_inputs_input("do","mw_datainput_item_btn"));
		$inputjs->set_prop("lbl",$this->lng_get_msg_txt("proccess_queue","Procesar cola"));
		return $inputsgr;

	}
	function do_exec_page_in(){
		
		$queuemailman=$this->mainap->get_submanager("mailqueue");
		$container=$this->get_ui_dom_elem_container_empty();
		$div=$container->add_cont_as_html(new  mwmod_mw_html_elem("div"));
		$div->add_cont($this->lng_get_msg_txt("sentMsgs","Mensajes enviado").": ");
		$e=$this->set_ui_dom_elem_id("msgssent","span");
		$e->add_cont("...");
		$div->add_cont($e);
		
		$div=$container->add_cont_as_html(new  mwmod_mw_html_elem("div"));
		$div->add_cont($this->lng_get_msg_txt("msgs_in_queue","Mensajes en cola").": ");
		$e=$this->set_ui_dom_elem_id("msgstosend","span");
		$e->add_cont("...");
		$div->add_cont($e);
		
		$e=$this->set_ui_dom_elem_id("controls");
		$container->add_cont($e);
		$container->add_cont("<hr>");
		
		$e=$this->set_ui_dom_elem_id("msgdonelist");
		$e->set_style("border","#000000 1px solid");
		$e->set_style("height","400px");
		$e->set_style("overflow","auto");
		$container->add_cont($e);
		
		
		
		
		
		$container->do_output();
		
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
		$this->ui_js_init_params->set_prop("controls",$this->getJSCtrs());
		$this->ui_js_init_params->set_prop("msgtosend",$queuemailman->load_msgs_to_send_num());
		$js=new mwmod_mw_jsobj_codecontainer();
	

		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		echo $js->get_js_script_html();
	
		
		
		/*
		
		$url=$this->get_url(array("sendtest"=>"true"));
		echo "<div><a href='$url'>Enviar prueba</a></div>";
		$url=$this->get_url(array("sendqueue"=>"true"));
		echo "<div><a href='$url'>Enviar cola</a></div>";
		
		$jobs_man=$queuemailman->jobs_man;
		*/
		//mw_array2list_echo($jobs_man->get_debug_data());
		
		
		
		

		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	function do_exec_no_sub_interface(){
		
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		//$util->add_css_mw_compact($this);
		//$util->preapare_ui_zip($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		//$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		//$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper_adv.js");
		//$jsman->add_item_by_cod_def_path("mw_events.js");
		//$jsman->add_item_by_cod_def_path("mw_bootstrap_helper.js");
		//$jsman->add_item_by_cod_def_path("mw_date.js");
		//$jsman->add_item_by_cod_def_path("inputs/date.js");
		//$jsman->add_item_by_cod_def_path("inputs/datagrid.js");
		$jsman->add_item_by_cod_def_path("inputs/dxnormal.js");		

		//$jsman->add_item_by_cod_def_path("ui/mwui_grid.js");
		$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem.js");
		//$jsman->add_item_by_cod_def_path("ui/helpers/ajaxelem/devextreme_datagrid.js");
		/*
		$item=new mwmod_mw_html_manager_item_jsexternal("mc_finacial_accounts_regs_ui","/res/mc/financial/ui/accounts_regs.js");
		$util->add_js_item($item);
		*/
		$item=new mwmod_mw_html_manager_item_jsexternal("mw_modules_mailqueue_ui_manualproccess","/res/modules/mailqueue/ui_manualproccess.js");
		$util->add_js_item($item);

		
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);
		/*
		$item= new mwmod_mw_html_manager_item_css("mcfinacialui","/res/mc/financial/ui/ui.css");
		$util->add_css_item($item);
		*/
		
		

	}
	
	
	
	
}
?>