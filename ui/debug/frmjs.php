<?php
class mwmod_mw_ui_debug_frmjs extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Formularios JS");
		
	}
	function prepare_js_and_css_mans(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);

		
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
	function get_js_nav_bar(){
		
		$nav=new mwmod_mw_jsobj_objcoladv_base("nav","mw_nav_bar");
		$fnc=$nav->addEventListener("childClick");
		$fnc->add_fnc_arg("evtn");
		$fnc->add_cont("console.log('childClick',evtn)");
		
		$item=$nav->add_new_child("e1","mw_nav_bar_item");
		$item->set_prop("lbl","Hola");
		$fnc=$item->addEventListener("click");
		$fnc->add_fnc_arg("evtn");
		$fnc->add_cont("console.log('click',evtn)");
		
		$subitem=$item->add_new_child("ss","mw_nav_bar_item");
		$subitem->set_prop("lbl","Hola 111");
		$subitem=$item->add_new_child("xxx","mw_nav_bar_item");
		$subitem->set_prop("lbl","ssdsd 111");
		
		
		$item=$nav->add_new_child("e2","mw_nav_bar_item");
		$item->set_prop("lbl","Hola 2");
		$subitem=$item->add_new_child("ss","mw_nav_bar_item");
		$subitem->set_prop("lbl","Hola 111");
		$subitem=$item->add_new_child("xxx","mw_nav_bar_item");
		$subitem->set_prop("lbl","ssdsd 111");
		
		$subitemsub=$subitem->add_new_child("xxx","mw_nav_bar_item");
		$subitemsub->set_prop("lbl","ssdsd 111");
		
		
				
		
		//mwmod_mw_jsobj_objcoladv_base
		/*
		$nav=new mwmod_mw_jsobj_newobject("mw_nav_bar");
		$nav->set_prop("cod","nav");
		$children=$nav->get_array_prop("children");
		$childrenAsData=$nav->get_array_prop("children");
		
		$item=new mwmod_mw_jsobj_newobject("mw_nav_bar_item");
		//$fnc=new 
		$item->set_prop("lbl","Hola");
		$children->add_data($item);
		
		$subchildren=$item->get_array_prop("children");
		$subchildrenAsData=$item->get_array_prop("children");
		$subitem=new mwmod_mw_jsobj_newobject("mw_nav_bar_item");
		$subitem->set_prop("lbl","Hola 111");
		$subchildren->add_data($subitem);
		$subchildrenAsData->add_data(array("lbl"=>"as data sin cod"));
		$subchildrenAsData->add_data(array("lbl"=>"as data con cod","cod"=>"asdata"));
		
		
		
		
		
		$item=new mwmod_mw_jsobj_newobject("mw_nav_bar_item");
		$item->set_prop("cod","elem1");
		$item->set_prop("lbl","Hola 1");
		$children->add_data($item);
		
		$item=new mwmod_mw_jsobj_obj();
		$item->set_prop("lbl","Hola xxxx data");
		$subchildren=$item->get_array_prop("children");
		$subchildrenAsData=$item->get_array_prop("children");
		$subitem=new mwmod_mw_jsobj_newobject("mw_nav_bar_item");
		$subitem->set_prop("lbl","Hola 111");
		$subchildren->add_data($subitem);
		$subchildrenAsData->add_data(array("lbl"=>"as data sin cod"));
		$subchildrenAsData->add_data(array("lbl"=>"as data con cod","cod"=>"asdata"));
		
		
		$childrenAsData->add_data($item);
		$childrenAsData->add_data(array("lbl"=>"as data sin cod"));
		$childrenAsData->add_data(array("lbl"=>"as data con cod","cod"=>"asdata"));
		$childrenAsData->add_data("xxx");
		*/
		
		
		return $nav;
		
		
		
		
		
		
	}
	function do_exec_page_in(){
		
		$container=$this->get_ui_dom_elem_container_empty();
		$panel=new mwmod_mw_bootstrap_html_template_panelcollapse(false);
		//$panel->set_collapsed(false);
		$panel->create_panel();	
		$t="Nav mw";
		$panel->title_elem->add_cont($t);	
		$body=$this->set_ui_dom_elem_id("nav");
		$panel->cont_elem->add_cont($body);
		
		
		
		$container->add_cont($panel);
		
		$frmcontainer=$this->set_ui_dom_elem_id("frmcontainer");
		$container->add_cont($frmcontainer);
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
	

		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		$frmjs=new mwmod_mw_jsobj_newobject("mw_datainput_item_frm");
		$frmchildren=new mwmod_mw_jsobj_array();
		$frmjs->set_prop("childrenList",$frmchildren);

		$frmafterappend=new mwmod_mw_jsobj_array();
		$frmjs->set_prop("afterAppendFncs",$frmafterappend);
		
		$fncafterappend=new mwmod_mw_jsobj_functionext();
		$fncafterappend->add_fnc_arg("e");
		$fncafterappend->add_cont("var iContainer=e.getChildByDotCod('containertop');\n");
		$fncafterappend->add_cont("if(!iContainer){return false}\n");
		$fncafterappend->add_cont("iContainer.appendInputItem(e.getChildByDotCod('gr.input'));\n");
		$fncafterappend->add_cont("iContainer.appendInputItem(e.getChildByDotCod('gr.sub'));\n");
		/*
		$newch->add_cont("var op={cod:cod,lbl:'".$newch->get_txt("Comentario")." "."'+cod};\n");
		$newch->add_cont("return new mw_datainput_item_textarea(op)");
		$jsinput->set_prop("newchild",$newch);
		$grchildren->add_data($jsinput);
		*/
		$frmafterappend->add_data($fncafterappend);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_container");	
		$jsinput->set_prop("cod","containertop");
		$jsinput->set_prop("childrenOnCols",true);
		
		$frmchildren->add_data($jsinput);
		
		
		$jsinputgr=new mwmod_mw_jsobj_newobject("mw_datainput_item_group");	
		$jsinputgr->set_prop("cod","gr");
		$jsinputgr->set_prop("input_name","gr");
		
		$grchildren=new mwmod_mw_jsobj_array();
		$jsinputgr->set_prop("childrenList",$grchildren);
		$frmchildren->add_data($jsinputgr);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_number");	
		$jsinput->set_prop("cod","dec");
		$jsinput->set_prop("lbl","dec");
		$jsinput->set_prop("value","ssss");
		$jsinput->set_prop("tooltip.title","decimal");
		$grchildren->add_data($jsinput);
		
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_normal_dx_textbox");	
		$jsinput->set_prop("cod","textbox");
		$jsinput->set_prop("lbl","textbox");
		$jsinput->set_prop("state.readOnly",true);
		$jsinput->set_prop("tooltip.title","textbox");
		
		
		
		
		//$jsinput->set_prop("decimal",true);
		$grchildren->add_data($jsinput);
		
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_input");	
		$jsinput->set_prop("cod","input");
		$jsinput->set_prop("lbl","input ttt");
		$jsinput->set_prop("tooltip.title","xxxxx\n<br>dsfsd");
		$grchildren->add_data($jsinput);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_input");	
		$jsinput->set_prop("cod","input1");
		$jsinput->set_prop("lbl","input1");
		$grchildren->add_data($jsinput);
		
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_input");	
		$jsinput->set_prop("cod","inputoncol");
		$jsinput->set_prop("lbl","inputoncol");
		$grchildren->add_data($jsinput);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_input");	
		$jsinput->set_prop("cod","inputoncol1");
		$jsinput->set_prop("lbl","inputoncol1");
		$grchildren->add_data($jsinput);
		
		$jsinputsubgr=new mwmod_mw_jsobj_newobject("mw_datainput_item_group");	
		$jsinputsubgr->set_prop("cod","sub");
		$grchildren->add_data($jsinputsubgr);
		
		$subgrchildren=new mwmod_mw_jsobj_array();
		$jsinputsubgr->set_prop("childrenList",$subgrchildren);
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_input");	
		$jsinput->set_prop("cod","inputoncol");
		$jsinput->set_prop("lbl","inputoncolsub");
		$jsinput->set_prop("colsnumOnOtherContainer",6);
		
		$subgrchildren->add_data($jsinput);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_input");	
		$jsinput->set_prop("cod","inputoncol1");
		$jsinput->set_prop("lbl","inputoncolsub1");
		$jsinput->set_prop("colsnumOnOtherContainer",6);
		$subgrchildren->add_data($jsinput);
		
		
		
		


		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_date");	
		$jsinput->set_prop("cod","date");
		$jsinput->set_prop("lbl","date");
		$jsinput->set_prop("value",date("Y-m-d H:i:s"));
		$jsinput->set_prop("tooltip.title","xxxxx\n<br>dsfsd");
		
		$grchildren->add_data($jsinput);
		/*
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_date");	
		$jsinput->set_prop("cod","date1");
		$jsinput->set_prop("lbl","date");
		$jsinput->set_prop("placeholder","xxxx");
		$jsinput->set_prop("timeonly",true);
		$jsinput->set_prop("dateboxOptions.showClearButton",true);
		$grchildren->add_data($jsinput);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_date");	
		$jsinput->set_prop("cod","date2");
		$jsinput->set_prop("lbl","date");
		$jsinput->set_prop("nohour",true);
		$jsinput->set_prop("dateboxOptions.showClearButton",false);
		$grchildren->add_data($jsinput);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_date");	
		$jsinput->set_prop("cod","date3");
		$jsinput->set_prop("lbl","date");
		$jsinput->set_prop("state.required",true);
		$grchildren->add_data($jsinput);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_date");	
		$jsinput->set_prop("cod","date4");
		$jsinput->set_prop("lbl","date");
		$jsinput->set_prop("state.disabled",true);
		$grchildren->add_data($jsinput);
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_date");	
		$jsinput->set_prop("cod","date5");
		$jsinput->set_prop("lbl","date");
		$jsinput->set_prop("state.readOnly",true);
		$grchildren->add_data($jsinput);
		*/
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_submit");	
		$jsinput->set_prop("cod","submit");
		$jsinput->set_prop("lbl","send");
		$jsinput->set_prop("tooltip.title","xxxxx\n<br>dsfsd");
		$jsinput->set_prop("tooltip.html",true);
		
		

		$frmchildren->add_data($jsinput);
		
		
		//
		/*
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_group_multiple_children");	
		$jsinput->set_prop("cod","coments");
		$jsinput->set_prop("lbl","coments");
		$jsinput->set_prop("autoaddchild",true);
		$newch=new mwmod_mw_jsobj_functionext();
		$newch->add_fnc_arg("e");
		$newch->add_cont("var cod=e.get_next_child_index();\n");
		
		$newch->add_cont("var op={cod:cod,lbl:'".$newch->get_txt("Comentario")." "."'+cod};\n");
		$newch->add_cont("return new mw_datainput_item_textarea(op)");
		$jsinput->set_prop("newchild",$newch);
		$grchildren->add_data($jsinput);
		
		
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_group_multiple_children");	
		$jsinput->set_prop("cod","documents");
		$jsinput->set_prop("lbl","documents");
		$jsinput->set_prop("autoaddchild",true);
		$newch=new mwmod_mw_jsobj_functionext();
		$newch->add_fnc_arg("e");
		$newch->add_cont("var cod=e.get_next_child_index();\n");
		
		$newch->add_cont("var op={cod:cod,lbl:'".$newch->get_txt("Documento")." "."'+cod};\n");
		$newch->add_cont("var gr= new mw_datainput_item_groupwithtitle(op)");
		$jsinputsub=new mwmod_mw_jsobj_newobject("mw_datainput_item_input");
		$jsinputsub->set_prop("lbl","Nombre");
		$newch->add_cont("gr.addItem(".$jsinputsub->get_as_js_val().",'name');\n");
		$jsinputsub=new mwmod_mw_jsobj_newobject("mw_datainput_item_file");
		$jsinputsub->set_prop("lbl","Archivo");
		
		$newch->add_cont("var fileinput=".$jsinputsub->get_as_js_val().";\n");
		$newch->add_cont("fileinput.options.set_param('doc_file_'+cod,'input_name')\n");
		$newch->add_cont("gr.addItem(fileinput,'file');\n");
		$jsinputsub=new mwmod_mw_jsobj_newobject("mw_datainput_item_textarea");
		$jsinputsub->set_prop("lbl","Descripción");
		$newch->add_cont("gr.addItem(".$jsinputsub->get_as_js_val().",'description');\n");


		$newch->add_cont("return gr");
		$jsinput->set_prop("newchild",$newch);
		$grchildren->add_data($jsinput);
		*/
		
		$nav=$this->get_js_nav_bar();
		$js->add_cont("var nav=".$nav->get_as_js_val().";\n");
		$js->add_cont("nav.append_to_container(".$var.".get_ui_elem('nav'));\n");
		$js->add_cont("var frm=".$frmjs->get_as_js_val().";\n");
		$js->add_cont("frm.append_to_container(".$var.".get_ui_elem('frmcontainer'));\n");
		$js->add_cont("frm.onsubmit=function(){console.log(this.get_input_value()); return false};\n");
		
		//echo nl2br($js->get_as_js_val());
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