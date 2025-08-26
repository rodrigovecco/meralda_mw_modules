<?php
class mwmod_mw_users_ui_myaccount_token extends mwmod_mw_users_ui_myaccount_abs{
	
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title($this->lng_get_msg_txt("accessToken","Token de acceso"));
		//$this->js_ui_class_name="mw_ui_frm";
		
	}
	

	function do_exec_page_in(){
		if(!$user=$this->get_current_user()){
			return false;
		}
		$container=$this->get_ui_dom_elem_container_empty();
		$testresult=false;

		$inputMan=new mwmod_mw_helper_inputvalidator_request("newdata");
		$newToken=null;
		if($inputMan->is_req_input_ok()){

			if($inputMan->get_value_by_dot_cod("confirm")){
				$newToken=$user->man->jwtMan->createTokenForUser($user);
				//mw_array2list_echo($user->man->jwtMan->validateToken($newToken));


			}

			
			
			
		}
		$frm=new mwmod_mw_jsobj_inputs_frmonpanel();
		$frm->set_prop("lbl",$this->lng_get_msg_txt("accessToken","Token de acceso"));

		$submit=$frm->add_submit($this->lng_get_msg_txt("generate","Generar"));
		$mainInputsGroup=$frm->add_data_main_gr("newdata");

		
		//$gr->setTitleMode($this->lng_get_msg_txt("accessToken","Token de acceso"));
		$input=$mainInputsGroup->addNewChild("confirm","checkbox");
		$input->set_prop("lbl",$this->lng_get_msg_txt("userTokenGenerationConfirmMSG","Comprendo que este token puede ser usado para ejecutar acciones en mi cuenta y firmarlas con mis credenciales."));
		$input->set_prop("notes",$this->lng_get_msg_txt("userTokenGenerationConfirmEXTRAINFO","Los tokens se invalidan al cambiar contraseña."));
		
		if($newToken){
			$input=$mainInputsGroup->addNewChild("token","textarea");
			$input->set_prop("lbl",$this->lng_get_msg_txt("newToken","Nuevo token"));
			$input->set_value($newToken);
		}





		


		$frmcontainer=$this->set_ui_dom_elem_id("frmcontainer");
		$container->add_cont($frmcontainer);
		$container->do_output();
		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		
		$js->add_cont("var igr=".$frm->get_as_js_val().";\n");
		$js->add_cont("igr.append_to_container(".$var.".get_ui_elem('frmcontainer'));\n");
		
		echo $js->get_js_script_html();

		

		return true;
	}
	function is_allowed(){
		if(!$user=$this->get_current_user()){
			return false;
		}
		if(!$user->man->jwtMan){
			return false;
		}
		return $this->allow("owntoken");	
	}
	function prepare_before_exec_no_sub_interface(){
		
		$jsman=$this->maininterface->jsmanager;
		
		$jsman->add_item_by_cod("/res/js/util.js");
		$jsman->add_item_by_cod("/res/js/ajax.js");
		$jsman->add_item_by_cod("/res/js/url.js");
		$jsman->add_item_by_cod("/res/js/mw_date.js");
		$jsman->add_item_by_cod("/res/js/inputs/inputs.js");
		$jsman->add_item_by_cod("/res/js/inputs/container.js");
		$jsman->add_item_by_cod("/res/js/inputs/other.js");
		$jsman->add_item_by_cod("/res/js/inputs/date.js");
		$jsman->add_item_by_cod("/res/js/inputs/dx.js");
		$jsman->add_item_by_cod("/res/js/inputs/frm.js");
		$jsman->add_item_by_cod("/res/js/arraylist.js");
		$jsman->add_item_by_cod("/res/js/ui/mwui.js");
		$jsman->add_item_by_cod("/res/js/mw_bootstrap_helper.js");
		$jsman->add_item_by_cod("/res/js/validator.js");

		
		$item=$this->create_js_man_ui_header_declaration_item();
		$jsman->add_item_by_item($item);
	}
}
?>