<?php
class mwmod_mw_paymentapi_debugui_mod_createfiles extends mwmod_mw_paymentapi_debugui_mod_subabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Crear archivos de datos");
		
	}

	
	function do_exec_page_in(){
		if(!$m=$this->getCurrentModule()){
			"Invalid module";	
		}
		$html=new mwmod_mw_html_elem();
		
		$pm=$m->logsPathMan;
		$e=$html->add_cont_elem();
		$e->add_cont_elem("<h3>Logs</h3>");
		$e->add_cont_elem($pm->check_and_create_path());
		/*
		$cods=explode(",","publickey,privatekey");
		foreach($cods as $c){
			$r[$c]=array();
			if($di=$this->get_key_item($c)){
				$e->add_cont_elem("<h3>$c</h3>");
				
				
				//$r[$c]["file"]=$di->get_file_full_path();
			}
		}
		*/
		
		echo $html;
		
		$frm=$this->new_frm();
		$frm->title="Actualizar claves";
		$cr=$this->new_datafield_creator();
		if($_REQUEST["nd"]??null){
			//mw_array2list_echo($_REQUEST["nd"]);
			$valman=new mwmod_mw_helper_inputvalidator_request("nd");
			if($val=$valman->get_value_by_dot_cod("newkeys.public").""){
				if($di=$m->get_key_item("publickey")){
					$di->set_data_and_save($val);	
				}
			}
			if($val=$valman->get_value_by_dot_cod("newkeys.private").""){
				if($di=$m->get_key_item("privatekey")){
					$di->set_data_and_save($val);	
				}
			}
		}
		$cr->items_pref="nd";
		$gr=$cr->add_item(new mwmod_mw_datafield_group("newkeys"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("public","public"));
		$i=$gr->add_item(new mwmod_mw_datafield_input("private","private"));
		
		$submit=$cr->add_submit();
		$frm->set_datafieldcreator($cr);
		
		echo  $frm->get_html();

		
		
	}

}
?>