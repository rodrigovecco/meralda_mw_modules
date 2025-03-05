<?php
class mwmod_mw_ui_debug_frm extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->get_msg("Formularios"));
		
	}
	
	function do_exec_no_sub_interface(){
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();

		$item=new mwmod_mw_html_manager_item_jsexternal("ckeditor","/res/ckeditor/ckeditor.js");
		$util->add_js_item($item);
		$item=new mwmod_mw_html_manager_item_jsexternal("popper","/res/popper/popper.min.js");
		$util->add_js_item($item);
		
	}
	function do_exec_page_in(){
		if(!$msg_man=$this->mainap->get_msgs_man_common()){
			return false;	
		}
		$frm=$this->new_frm();
		$frm->title=$msg_man->get_msg_txt("test_form","Formulario de prueba");
		$cr=$this->new_datafield_creator();
		if($_REQUEST["testdata"]??null){
			//mw_array2list_echo($_REQUEST["testdata"]);
		}
		$cr->items_pref="testdata";
		$gr=$cr->add_item(new mwmod_mw_datafield_group("gr"));
		$subgr=$gr->add_item(new mwmod_mw_datafield_groupwithtitle("gr",$msg_man->get_msg_txt("group","Grupo")));
		$subgr->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_input("input","input"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_input("inputtags","inputtags"));
		$i=$subgr->add_item(new mwmod_mw_datafield_ckeditor("ckeditor","ckeditor"));
		$json=new mwmod_mw_jsobj_json("[
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		'/',
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
		{ name: 'about', groups: [ 'about' ] }
	]");
		$json=new mwmod_mw_jsobj_json("[
		{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] }
	]");
	
		//$i->set_param("editorcfg.toolbarGroups.",$json);
		
		
		
		$i=$subgr->add_item(new mwmod_mw_datafield_checkbox("checkbox","checkbox"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_date("date","date"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_decimal("decimal","decimal"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_file("file","file"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_html("html","html"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_img("img","img"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_number("number","number"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_password("password","password"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_select("select","select"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_text("text","text"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$i=$subgr->add_item(new mwmod_mw_datafield_textarea("textarea","textarea"));
		$i->set_param("tooltip.title","dsfsdfsdf\nssss");
		$subgr1=$subgr->add_item(new mwmod_mw_datafield_groupwithtitle("gr","Grupo 1"));
		$i=$subgr1->add_item(new mwmod_mw_datafield_input("input","input"));
		$i->set_required(true);
		$i=$subgr1->add_item(new mwmod_mw_datafield_input("input1","input dis"));
		$i->set_disabled(true);
		
		$i=$subgr1->add_item(new mwmod_mw_datafield_checkbox("checkbox","checkbox"));
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
		$submit->set_param("tooltip.title","dsfsdfsdf\nssss");
		$params=$submit->get_bootstrap_params();
		//$params->set_prop("btn.class","btn btn-lg btn-success btn-block");
		if($_REQUEST["testdata"]??null){
			$valman=new mwmod_mw_helper_inputvalidator_man();
			$valman->set_value($_REQUEST["testdata"]);
			if($val=$valman->get_item_by_dot_cod("gr.gr.inputtags")){
				$val->dont_strip_tags=true;	
			}
			if($val=$valman->get_item_by_dot_cod("gr.gr.ckeditor")){
				$val->dont_strip_tags=true;	
			}
			
			//mw_array2list_echo($valman->get_value());
			$cr->set_data($valman->get_value());	
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