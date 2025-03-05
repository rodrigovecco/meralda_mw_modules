<?php
class mwmod_mw_ui_debug_uidebug extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$maininterface){
		$this->init($cod,$maininterface);
		$this->set_lngmsgsmancod("debug");
		$this->set_def_title($this->get_msg("Depuración"));
		$this->subinterface_def_code="mainui";
		
	}
	function before_exec(){
		///esto se ha hecho ya que sus subinterfaces requieren scripñts que antes se cargaban con todas
		$p=new mwmod_mw_html_manager_uipreparers_default($this);
		$p->preapare_ui();
		$this->add_req_js_scripts();	
		$this->add_req_css();
	}
	
	
	

	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		
		//$this->add_2_mnu($mnu);
		if($subs=$this->get_subinterfaces_by_code("appdebug,mainui,modules,rols,htmltemplate,financial,dx,system,mnus,tests,others,util,db",true)){
			
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		if($subs=$this->get_subinterfaces_by_code("frm,frmdates,frmvalidation,frmjs,ckeditor",true)){
			$item=new mwmod_mw_mnu_items_dropdown_single($this->get_cod_for_mnu()."_frms_","Formularios",$mnu,$this->get_url());
			$mnu->add_item_by_item($item);
			
			foreach($subs as $su){
				$sitem=new mwmod_mw_mnu_mnuitem($su->get_cod_for_mnu(),$su->get_mnu_lbl(),$item,$su->get_url());
				$item->add_item_by_item($sitem);
				if($su->is_current()){
					$sitem->active=true;	
				}
			}
			
		}
		
		return $mnu;
	}
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	
	function add_mnu_items_side($mnu){
		
		
		$mnuitem=new mwmod_mw_mnu_items_dropdown1($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu);
		$this->prepare_mnu_item($mnuitem);
		$mnu->add_item_by_item($mnuitem);
		if($subs=$this->get_subinterfaces_by_code("rols,htmltemplate,altclass",true)){
			//$mnuitem->set_dropdown();
			foreach($subs as $su){
				//corregiorr!!!
				$su->add_as_sub_mnu_item($mnuitem);	
			}
		}
		
		/*
		$mnuitem= new mwmod_mw_mnu_items_dropdown($this->cod,$this->get_msg("Usuario"),$mnu);
		$mnuitem->add_param("class","dropdown-user");
		$mnu->add_item_by_item($mnuitem);
		*/

	}
	function prepare_mnu_item($item){
		$item->addInnerHTML_icon("fa fa-bug");
	}
	
	
	/*
	function add_sub_mnus($mnuitem,$checkallowed=true){
		if($subs=$this->get_subinterfaces_by_code("rols,htmltemplate,altclass",$checkallowed)){
			$mnuitem->set_dropdown();
			foreach($subs as $su){
				$su->add_as_sub_mnu_item($mnuitem);	
			}
		}
		
	}
	*/
	
	/*

	
	function add_mnu_items($mnu){
		$this->add_sub_interface_to_mnu_by_code($mnu,"rols");
		$this->add_sub_interface_to_mnu_by_code($mnu,"frm");
		$this->add_sub_interface_to_mnu_by_code($mnu,"htmltemplate");
		$this->add_sub_interface_to_mnu_by_code($mnu,"altclass");
		
		
	}
	*/
	
	
	function create_app_debug_ui(){
		
		if(method_exists($this->maininterface,"create_app_debug_ui")){
			return $this->maininterface->create_app_debug_ui($this,"appdebug");
		}
		
	}
	function load_all_subinterfases(){
		
		if($si=$this->create_app_debug_ui()){
			$this->add_new_subinterface($si);
		}
		
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_mainuitest("mainui",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_modulesman_ui_mainui("modules",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_db_db("db",$this));
		
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_rols("rols",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_htmltemplate("htmltemplate",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_altclass("altclass",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_frm("frm",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_frmjs("frmjs",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_frmdates("frmdates",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_frmvalidation("frmvalidation",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_ckeditor("ckeditor",$this));
		
		//$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_php("php",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_system("system",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_mnus("mnus",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_others("others",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_util("util",$this));
		
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_dx_dx("dx",$this));
		
		//$si=$this->add_new_subinterface(new mwmod_mw_phpoffice_test_debugui("phpoffice",$this));
		
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_tests("tests",$this));
		$si=$this->add_new_subinterface(new mwmod_mw_ui_debug_financial("financial",$this));
		
	}

	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		if(!$user=$this->get_admin_current_user()){
			return false;
		}
		$frm=$this->new_frm();
		//$frm->set_enctype_urlencoded();
		$cr=$this->new_datafield_creator();
		
		
		
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("name",$this->get_msg("Usuario")));
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->get_msg("Nombre completo")),"data");
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("email",$this->get_msg("Email")),"data");
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_date("date",$this->get_msg("Fecha")),"data");
		$input->set_value("2013-07-21 4:15:4");
		$input=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_date("time",$this->get_msg("Fecha time")),"data");
		$input->set_value(strtotime("2013-07-21 4:15:4"));
		$input->set_time_mode();
		$gr=$cr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_groupwithtitle("xxxx",$this->get_msg("grupo")));
	
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_html("name",$this->get_msg("Usuario")));
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("complete_name",$this->get_msg("Nombre completo")),"data");
		$input=$gr->add_sub_item_by_dot_cod(new mwmod_mw_datafield_input("email",$this->get_msg("Email")),"data");
		
		
		
		
		$cr->items_pref="nd";
		$cr->add_submit($this->get_msg("Guardar"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html_bootstrap_mode();
		echo "<hr>";
		echo $frm->get_html();
		

		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>