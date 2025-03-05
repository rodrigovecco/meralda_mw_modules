<?php
class mwmod_mw_ui_debug_dx_barchart extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Bar chart");
		
	}
	
	function do_exec_no_sub_interface(){
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_charts($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$util->get_js_man();		
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_datagrid_helper.js");
		$jsman->add_item_by_cod_def_path("mwdevextreme/mw_chart_helper.js");
		
		
	}
	function do_exec_page_in(){
		echo "dd";
		$helper=new mwmod_mw_devextreme_chart_helper();
		$serie=$helper->add_serie_by_cod("s1","Serie 1","bar");
		//$serie->set_prop("options.color","#5da6d8");
		//creo que no es lo que quiere
		//$serie=$helper->add_serie_by_cod("execnum",$this->lng_get_msg_txt("executions_number","Número de ejecuciones"),"bar");

		$helper->set_prop("chartoptions.argumentAxis.argumentType","string");
		$helper->set_prop("chartoptions.argumentAxis.label.visible",false);
		$cuspointfnc= new mwmod_mw_jsobj_functionext();
		$cuspointfnc->add_fnc_arg("point");
		$cuspointfnc->add_cont("if (point.value>=4){return { color: '#ff0000' }}else{return { color: '#ffff00' }}");
		$helper->set_prop("chartoptions.customizePoint",$cuspointfnc);
		//$helper->set_prop("chartoptions.tooltip.enabled","true");
		//$helper->set_prop("chartoptions.legend.visible",false);
		//$helper->set_prop("argumentNameField","model_name");
		//$helper->set_prop("tooltipFromArgumentName",true);
		
		
		$helper->set_key("id");
		
		$helper->add_data(array("id"=>1,"s1"=>6,"s2"=>4));	
		$helper->add_data(array("id"=>2,"s1"=>5,"s2"=>2));	
		$helper->add_data(array("id"=>3,"s1"=>2,"s2"=>4));	
		$helper->add_data(array("id"=>4,"s1"=>8,"s2"=>3));	
		$helper->add_data(array("id"=>5,"s1"=>3,"s2"=>4));	
		$helper->add_data(array("id"=>6,"s1"=>5,"s2"=>2));	
		$helper->add_data(array("id"=>7,"s1"=>4,"s2"=>4));	
		
		echo "<div id='xxx'></div>";
		
		$js= new mwmod_mw_jsobj_jquery_docreadyfnc();
		
		$js->add_cont("var helper=".$helper->get_as_js_val().";\n");
		$js->add_cont("helper.init_from_params();\n");
		$js->add_cont("helper.create_chart('#xxx');\n");
		
		
		echo nl2br($js->get_as_js_val());
		echo $js->get_js_script_html();
		
		
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
	}
	
}
?>