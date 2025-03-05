<?php
class mwmod_mw_google_ui_cfg_keys extends mwmod_mw_google_ui_cfg_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("claves","Claves"));
		
		
	}
	
	function set_js_inputs_data($inputsgrdata){
		$gr=$inputsgrdata->addNewGr("keys");
		$inputappid=$gr->addNewChild("appId");
		$inputappid->set_prop("lbl","Client ID");
		$grsec=$gr->addNewGr("secret");
		$grsec->setTitleMode("Client secret");
		$grsec->set_prop("collapsed",true);
		$inputsec=$grsec->addNewChild("appsecret");
		$inputsec->set_prop("placeholder","Establecer clave secreta");
		$input=$grsec->addNewChild("update","checkbox");
		$input->set_prop("lbl","Actualizar");
		
		
		//se podría crea un grupo data para otros datos
		if($man=$this->getGoogleMan()){
			if($td=$man->getJsonDataItem("keys")){
				$inputappid->set_value($td->get_data("appId"));
				if($td->get_data("appsecret")){
					$inputsec->set_prop("placeholder","Cambiar clave secreta");
				}
				//$gr->set_value($td->get_data());	
			}
		}
	}
	function save_from_request(){
		$input=new mwmod_mw_helper_inputvalidator_request("cfg");
		if(!$input->is_req_input_ok()){
			return false;	
		}
		if($man=$this->getGoogleMan()){
			if($td=$man->getJsonDataItem("keys")){
				$s=false;
				if($nd=$input->get_value_by_dot_cod_as_list("keys.data")){
					$s=true;
					$td->set_data($nd,"data");	
				}
				if($input->get_value_by_dot_cod("keys.secret.update")){
					$s=true;
					$td->set_data($input->get_value_by_dot_cod("keys.secret.appsecret")."","appsecret");	
				}
				if($input->value_exists("keys.appId")){
					$td->set_data($input->get_value_by_dot_cod("keys.appId")."","appId");
					$s=true;	
				}
				
				if($s){
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