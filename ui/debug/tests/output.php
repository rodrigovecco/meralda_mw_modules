<?php
class mwmod_mw_ui_debug_tests_output extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		
		$this->set_def_title("Output");
		
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function execfrommain_getcmd_sxml_test($params=array(),$filename=false){
		
		$output=$this->createOutput();
		$output->xml->root_do_all_output();
		
		
	}

	function createOutput(){
		$output=new mwmod_mw_helper_output_uidef();
		$output->setOutputXMLJSAlertMode();
		$output->alert->setMsgSuccess("Alerta");
		$alert=$output->xml->htmlItem("test.html")->addCont(new mwmod_mw_bootstrap_html_specialelem_alert());
		$alert->setMsgSuccess("qqq");
		$alert->dismissible=true;
		$output->xml->htmlItem("test.html")->addCont("hola");
		$output->xml->htmlItem("tessdfsft.html")->addCont("hola");
		$e=$output->html->addCont("probando");
		$e->add_cont("más");

		$e=$output->html->add_cont_elem("xxxx");
		$e->add_cont("más");
		$output->xml->jsItem("test.html")->set_prop("x","hola");
		$output->xml->jsItem("test.html")->set_prop("xxx","hola");
		$output->xml->jsItem("test.js")->set_prop("x","hola");
		$output->xml->jsItem("test.js")->set_prop("xxx","hola");
		$output->xml->jsItem("js")->set_prop("xxx","hola");
		
		
		return $output;
			
	}
	function do_exec_page_in(){
		$container= new mwmod_mw_html_elem();
		$container->add_cont_elem("Utilidad para output en UI y otros, puede pasar el parametro output a otros manejadores, ver pastipan payment");
		
		$output=$this->createOutput();
		
		/*
		$js=new mwmod_mw_jsobj_script();
		$js->addVarDeclaration("a",$output->xml->jsItem("js"));
		$js->addVarDeclaration("c",$jso);
		$js->add_cont("console.log('a',a);\n");
		$js->addVarDeclaration("b",$output->js);
		$js->add_cont("console.log('b',b);\n");
		$js->add_cont("console.log('c',c);\n");
		//$output->xml->root_do_all_output();
		//echo $output->html;
		*/
		$url=$this->get_exec_cmd_sxml_url("test");
		$sub=$container->add_cont_elem("<h3>HTML</h3>");
		$sub->add_cont($output->html);
		$sub=$container->add_cont_elem("<a href='$url' target='_blank'>Ver XML</a>");
		$sub=$container->add_cont_elem("<h3>JS</h3><textarea width='100%'>".$output->js."</textarea>");
		$sub=$container->add_cont_elem("<h3>Alert</h3>".$output->alert);
		
		
		
		echo $container;
		
		


		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>