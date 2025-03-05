<?php
class mwmod_mw_demo_ui_inputs extends mwmod_mw_demo_ui_abs{
	function __construct($cod,$parent){
		$this->init_as_main_or_sub($cod,$parent);
		$this->set_lngmsgsmancod("demo");
		$this->set_def_title($this->lng_get_msg_txt("forms","Formularios"));
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){


		//mw_array2list_echo($_REQUEST);
		/*
		if($dataingresada=$_REQUEST['newdata']??null){
			if(is_array($dataingresada)){
				mw_array2list_echo($dataingresada);
			}
		}
		*/
		$finalvalue=null;
		$inputMan=new mwmod_mw_helper_inputvalidator_request("newdata");
		if($inputMan->is_req_input_ok()){
			//echo "Sí se han enviado datos.";
			$finalvalue=$inputMan->get_value_by_dot_cod();
			//mw_array2list_echo($finalvalue);
			/*
			echo $inputMan->getValueStrTrim("subgr.boolval");
			
			$subitem=$inputMan->get_item_by_dot_cod("multiline");
			echo "<textarea>".$subitem->getValueStrTrim()."</textarea><hr>";
			echo "<textarea>".$subitem->get_orig_value()."</textarea><hr>";
			echo $subitem->get_orig_value();
			*/
			
		}else{
			//echo "No se han enviado datos.";
		}



		

		$container=$this->get_ui_dom_elem_container_empty();


		


		$frmcontainer=$this->set_ui_dom_elem_id("frmcontainer");
		$container->add_cont($frmcontainer);
		$container->do_output();


		$frm=new mwmod_mw_jsobj_inputs_frmonpanel();

		$submit=$frm->add_submit("Enviar datos");

		//$mainInputsGroup=new mwmod_mw_jsobj_inputs_gr("gr");

		$mainInputsGroup=$frm->add_data_main_gr("newdata");


		$input=$mainInputsGroup->addNewChild("normal");
		$input->set_prop("lbl",$this->lng_get_msg_txt("enterAValue","Ingresa un dato"));

		$input=$mainInputsGroup->addNewChild("multiline","textarea");
		$input->set_prop("lbl","Ingresa un dato varias líneas");

		$subGR=$mainInputsGroup->addNewGr("subgr");
		$input=$subGR->addNewChild("boolval","checkbox");
		$input->set_prop("lbl","¿Es verdad?");

		$input=$subGR->addNewSelect("selector");
		$input->set_prop("lbl","Selecciona un valor");
		$input->add_select_option("1","Uno");
		$input->add_select_option("2","Dos");
		$input->add_select_option("3","Tres");

		$input=$subGR->addNewChild("datetime","date");
		$input->set_prop("lbl","Fecha y hora");

		$input=$subGR->addNewChild("date","date");
		$input->set_prop("lbl","Fecha");
		$input->set_prop("nohour",true);

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