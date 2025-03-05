<?php
class mwmod_mw_demo_ui_extmods_phpmailer extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title("PHPmailer");
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		



		

		$container=$this->get_ui_dom_elem_container_empty();


		


		$frmcontainer=$this->set_ui_dom_elem_id("frmcontainer");
		$container->add_cont($frmcontainer);

		$finalvalue=null;
		$inputMan=new mwmod_mw_helper_inputvalidator_request("newdata");
		if($inputMan->is_req_input_ok()){
			
			$finalvalue=$inputMan->get_value_by_dot_cod();
			
			if($to=$inputMan->getValueStrTrim("to")){
				$mailerMan=new mwmod_mw_mail_phpmailer_man();
				if($to=$mailerMan->checkemail($to)){
					$phpMailer=$mailerMan->preparePHPMailer();
					
					$phpMailer->addAddress($to);
					$phpMailer->Subject=$inputMan->getValueStrTrim("subject")."";
					$phpMailer->msgHTML(nl2br($inputMan->getValueStrTrim("msg").""));
					if($phpMailer->send()){
						$alert=new mwmod_mw_bootstrap_html_specialelem_alert("Mensaje enviado.","success");
						$container->add_cont($alert);
					}else{
						$alert=new mwmod_mw_bootstrap_html_specialelem_alert("Error al enviar Mensaje.","danger");
						$container->add_cont($alert);


					}


				}
			}
			
			
			
			
		
		}

		$container->do_output();


		$frm=new mwmod_mw_jsobj_inputs_frmonpanel();

		$submit=$frm->add_submit("Enviar");

		//$mainInputsGroup=new mwmod_mw_jsobj_inputs_gr("gr");

		$mainInputsGroup=$frm->add_data_main_gr("newdata");


		$input=$mainInputsGroup->addNewChild("to");
		$input->set_prop("lbl",$this->lng_get_msg_txt("email","E-Mail"));

		$input=$mainInputsGroup->addNewChild("subject");
		$input->set_prop("lbl",$this->lng_get_msg_txt("subject","Asunto"));

		$input=$mainInputsGroup->addNewChild("msg","textarea");
		$input->set_prop("lbl",$this->lng_get_msg_txt("body","Mensaje"));

		

		$mainInputsGroup->set_value($finalvalue);


		//


		

		$js=new mwmod_mw_jsobj_jquery_docreadyfnc();
		$this->set_ui_js_params();
		$var=$this->get_js_ui_man_name();
		
		$js->add_cont($var.".init(".$this->ui_js_init_params->get_as_js_val().");\n");
		//$js->add_cont("var igr=".$mainInputsGroup->get_as_js_val().";\n");
		$js->add_cont("var igr=".$frm->get_as_js_val().";\n");
		$js->add_cont("igr.append_to_container(".$var.".get_ui_elem('frmcontainer'));\n");
		
		echo $js->get_js_script_html();
		
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