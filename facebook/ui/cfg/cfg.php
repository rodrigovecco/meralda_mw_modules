<?php
class mwmod_mw_facebook_ui_cfg_cfg extends mwmod_mw_facebook_ui_cfg_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("configuration","Configuración"));
		
	}
	function set_js_inputs_data($inputsgrdata){
		$input=$inputsgrdata->addNewChild("enabled","checkbox");
		$input->set_prop("lbl",$this->lng_get_msg_txt("enabled","Habilitado"));
		if($man=$this->getFBMan()){
			if($td=$man->get_treedata_item("cfg")){
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
			if($man=$this->getFBMan()){
				if($td=$man->get_treedata_item("cfg")){
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
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_js_in_page($js);
		echo $js->get_js_script_html();
		
		

		
	}
	
}
?>