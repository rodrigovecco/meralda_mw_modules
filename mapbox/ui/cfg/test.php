<?php
class mwmod_mw_mapbox_ui_cfg_test extends mwmod_mw_mapbox_ui_cfg_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("test","Pruebas"));
		$this->js_ui_class_name="mw_mapbox_test_ui";
		
	}
	function do_exec_page_in(){
		
		
		
		$container=$this->get_ui_dom_elem_container_empty();
		
		
		
		$container->add_cont("<div id='map' style='width: 400px; height: 300px;'></div>");
		
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$js->add_cont("var map = new mapboxgl.Map({container: 'map', style: 'mapbox://styles/mapbox/streets-v11',center: [-74.5, 40],zoom: 9});\n");
		echo $js->get_js_script_html();
		
		
		

		
	}
	function is_allowed(){
		return $this->allow("admin");	
	}
	function prepare_before_exec_no_sub_interface(){
		
		
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$this->maininterface->jsmanager;
		if($man=$this->getmapboxMan()){
			if($item=$man->getJSInitItem()){
				$util->add_js_item($item);	
			}
		}
		
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");

	}
	
}
?>