<?php
class mwmod_mw_ui_debug_frmdates extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->get_msg("Fechas"));
		
	}
	function add_req_js_scripts(){
		return false;
		if(!$man=$this->maininterface->jsmanager){
			return false;	
		}
		$man->add_item_by_cod_def_path("inputsman/calendar.js");
		$item=new mwmod_mw_html_manager_item_js("/res/mwcalendar/mw_calendar.js");
		$man->add_item_by_item($item);
		

	}
	function add_req_css(){
		//return false;
		if(!$man=$this->maininterface->cssmanager){
			return false;	
		}
		//$item=new mwmod_mw_html_manager_item_css("mwcalendar","/res/mwcalendar/mw_calendar.css");
		//$man->add_item_by_item($item);
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		$frm=$this->new_frm();
		$frm->title=$msg_man->get_msg_txt("test_form","Formulario de prueba");
		$cr=$this->new_datafield_creator();
		if($_REQUEST["testdata"]){
			//mw_array2list_echo($_REQUEST["testdata"]);
		}
		$cr->items_pref="testdata";
		$gr=$cr->add_item(new mwmod_mw_datafield_group("gr"));
		$subgr=$gr->add_item(new mwmod_mw_datafield_groupwithtitle("gr",$msg_man->get_msg_txt("group","Grupo")));
		$i=$subgr->add_item(new mwmod_mw_datafield_date("date","date"));
		$i=$subgr->add_item(new mwmod_mw_datafield_date("dateminmax","dateminmax"));
		$i->set_param("mindate","2015-01-15");
		$i->set_param("maxdate","2015-01-25");
		//$i=$subgr->add_item(new mwmod_mw_datafield_date("dateonlyhour","dateonlyhour"));
		//$i->set_param("onlyhour",true);
		$i=$subgr->add_item(new mwmod_mw_datafield_date("datenohour","datenohour"));
		$i->set_param("nohour",true);
		
		$i=$subgr->add_item(new mwmod_mw_datafield_date("datetime","date time"));
		$i->set_time_mode(true);
		$i=$subgr->add_item(new mwmod_mw_datafield_date("datedis","date dis"));
		$i->set_disabled(true);
		$submit=$cr->add_submit($msg_man->get_msg_txt("send","Enviar"));
		$params=$submit->get_bootstrap_params();
		//$params->set_prop("btn.class","btn btn-lg btn-success btn-block");
		if($_REQUEST["testdata"]){
			$cr->set_data($_REQUEST["testdata"]);	
		}
		$frm->set_datafieldcreator($cr);
		
		echo  $frm->get_html();

		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>