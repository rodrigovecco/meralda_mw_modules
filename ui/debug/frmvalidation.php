<?php
class mwmod_mw_ui_debug_frmvalidation extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->get_msg("Valicación de Formularios"));
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		$frm=$this->new_frm();
		$frm->title=$msg_man->get_msg_txt("test_form","Formulario de prueba - Validación");
		$cr=$this->new_datafield_creator();
		if($_REQUEST["testdata"]){
			//mw_array2list_echo($_REQUEST["testdata"]);
		}
		$cr->items_pref="testdata";
		$gr=$cr->add_item(new mwmod_mw_datafield_group("gr"));
		$subgr=$gr->add_item(new mwmod_mw_datafield_groupwithtitle("gr",$msg_man->get_msg_txt("group","Grupo")));
		$i=$subgr->add_item(new mwmod_mw_datafield_input("input","input"));
		$i=$subgr->add_item(new mwmod_mw_datafield_input("inputtags","inputtags"));
		$i=$subgr->add_item(new mwmod_mw_datafield_checkbox("checkbox","checkbox"));
		$i=$subgr->add_item(new mwmod_mw_datafield_date("date","date"));
		$i=$subgr->add_item(new mwmod_mw_datafield_decimal("decimal","decimal"));
		$i=$subgr->add_item(new mwmod_mw_datafield_file("file","file"));
		$i=$subgr->add_item(new mwmod_mw_datafield_html("html","html"));
		$i=$subgr->add_item(new mwmod_mw_datafield_img("img","img"));
		$i=$subgr->add_item(new mwmod_mw_datafield_number("number","number"));
		$ipass=$subgr->add_item(new mwmod_mw_datafield_password("password","password"));
		$fnc=$ipass->add_after_init_event_to_list();
		$fnc->set_js_code_in("inputman.add_check_before_submit(function(inputman){
		var v=inputman.get_value();
		if(v){
			v=v+'';
			if(v.length>5){
				return true;	
			}
		}
		inputman.set_error_msg('".$fnc->get_txt("La contraseña debe tener más de 5 caracteres")."');\n;
		},true);\n
		");
		
		$ipass1=$subgr->add_item(new mwmod_mw_datafield_password("password1","password_confirm"));
		$fnc=$ipass1->add_after_init_event_to_list();
		$fnc->set_js_code_in("inputman.add_check_before_submit(function(inputman){
		var v=inputman.get_value();
		var orig=inputman.get_other_man('".$ipass->get_frm_field_id()."');
		if(!orig){
			return;
		}
		
		if(v){
			
			if(v==orig.get_value()){
				return true;	
			}
		}
		inputman.set_error_msg('".$fnc->get_txt("La contraseña No coincide")."');\n;
		},true);\n
		");
		
		$i=$subgr->add_item(new mwmod_mw_datafield_select("select","select"));
		$options=array("1"=>"uno",2=>"dos");
		$i->create_optionslist($options);
		$i=$subgr->add_item(new mwmod_mw_datafield_text("text","text"));
		$jsvalid="return true;";
		$validfnc=$i->set_js_validation_function($jsvalid);
		
		
		$i=$subgr->add_item(new mwmod_mw_datafield_textarea("textarea","textarea"));
		$subgr1=$subgr->add_item(new mwmod_mw_datafield_groupwithtitle("gr","Grupo 1"));
		$i=$subgr1->add_item(new mwmod_mw_datafield_input("input","input"));
		$i->set_required(true);
		$i=$subgr1->add_item(new mwmod_mw_datafield_input("input1","input dis"));
		$i->set_disabled(true);
		
		$i=$subgr1->add_item(new mwmod_mw_datafield_checkbox("checkbox","checkbox"));
		$fnc=$i->add_after_init_event_to_list();
		$fnc->set_js_code_in("inputman.add_check_before_submit(function(inputman){
		if(inputman.isChecked()){
			return true;	
		}
		inputman.set_error_msg('".$fnc->get_txt("Debe checkearse\n <br> jajja ' sd")."');\n;
		});\n
		inputman.add_after_change_event(function(inputman){
			inputman.clear_error_msg();
		});\n
		");
		
		$i=$subgr1->add_item(new mwmod_mw_datafield_checkbox("checkbox1","checkbox dis"));
		$i->set_disabled(true);
	
		
		
		
		/*
		$i=$cr->add_item(new mwmod_mw_datafield_input("login_userid",$msg_man->get_msg_txt("user","Usuario")));
		$i->set_placeholder_from_lbl();
		$i->set_required();

		$i=$cr->add_item(new mwmod_mw_datafield_password("login_pass",$msg_man->get_msg_txt("password","Contraseña")));
		$i->set_placeholder_from_lbl();
		$i->set_required();
		*/
		$submit=$cr->add_submit($msg_man->get_msg_txt("send","Enviar"));
		$params=$submit->get_bootstrap_params();
		//$params->set_prop("btn.class","btn btn-lg btn-success btn-block");
		if($_REQUEST["testdata"]){
			$valman=new mwmod_mw_helper_inputvalidator_man();
			$valman->set_value($_REQUEST["testdata"]);
			if($val=$valman->get_item_by_dot_cod("gr.gr.inputtags")){
				$val->dont_strip_tags=true;	
			}
			//mw_array2list_echo($valman->get_value());
			$cr->set_data($valman->get_value());	
		}
		$frm->set_datafieldcreator($cr);
		$frm->ask_before_submit_debug=true;
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