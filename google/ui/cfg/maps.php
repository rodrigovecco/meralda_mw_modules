<?php
class mwmod_mw_google_ui_cfg_maps extends mwmod_mw_google_ui_cfg_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("maps","Mapas"));
		
	}

	function set_js_inputs_data($inputsgrdata){
	
		$input=$inputsgrdata->addNewChild("apikey");
		$input->set_prop("lbl","Apikey");
		
		if($man=$this->getGoogleMan()){
			if($td=$man->getJsonDataItem("maps")){
				$inputsgrdata->set_value($td->get_data());	
			}
		}
	}
	function save_from_request(){
		$input=new mwmod_mw_helper_inputvalidator_request("cfg");
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if($nd=$input->get_value_as_list()){
			if($man=$this->getGoogleMan()){
				if($td=$man->getJsonDataItem("maps")){
					$td->set_data($nd);	
					$td->save();
				}
			}
		}
			
	}

	function do_exec_page_in(){
		$this->save_from_request();
		$container=$this->get_ui_dom_elem_container_empty();
		
		$frmcontainer=$this->set_ui_dom_elem_id("frmcontainer");
		$container->add_cont($frmcontainer);
		$map=$this->set_ui_dom_elem_id("map");
		$map->set_style("min-height","calc(100vh - 270px)");
		$container->add_cont($map);
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_js_in_page($js);
		$var=$this->get_js_ui_man_name();
		$js->add_cont("window.mw_google_maps_man.onLoad(function(){\n");
		$js->add_cont("var me=".$var.".get_ui_elem('map');\n");
		$js->add_cont("var map=new google.maps.Map(me,{
          zoom: 4,
          center: { lat: 49.496675, lng: -102.65625 },
        });\n");
        $js->add_cont("});\n");



		/*
		map = new google.maps.Map(document.getElementById("map"), {
          zoom: 4,
          center: { lat: 49.496675, lng: -102.65625 },
        });
		 */
		echo $js->get_js_script_html();
		
		

		
	}
	function prepare_before_exec_no_sub_interface(){
		
		$p=new mwmod_mw_html_manager_uipreparers_htmlfrm($this);
		$p->preapare_ui();
		$util=new mwmod_mw_devextreme_util();
		$util->preapare_ui_webappjs($this);
		$util= new mwmod_mw_html_manager_uipreparers_ui($this);
		$util->preapare_ui();
		
		$jsman=$this->maininterface->jsmanager;
		$jsman->add_item_by_cod_def_path("inputsman.js");
		$jsman->add_item_by_cod_def_path("url.js");
		$jsman->add_item_by_cod_def_path("ajax.js");
		$jsman->add_item_by_cod_def_path("mw_objcol.js");
		$jsman->add_item_by_cod_def_path("ui/mwui.js");
		$jsman->add_item_by_cod_def_path("mw_date.js");
		$jsman->add_item_by_cod_def_path("inputs/date.js");
		
		

		
		if($man=$this->getGoogleMan()){
			$man->prepareMainUIForMaps($this->maininterface);
		}
		$item=$this->create_js_man_ui_header_declaration_item();
		$util->add_js_item($item);
	}

	
}
?>