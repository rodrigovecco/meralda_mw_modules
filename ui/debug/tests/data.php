<?php
class mwmod_mw_ui_debug_tests_data extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		
		$this->set_def_title("Data");
		
		
	}
	
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$dm=new mwmod_mw_data_man("dmtests");
		$subdm=$dm->getItemXML("d","q/r");
		if($subdm->isNew()){
			echo "nuevo<br>";	
		}else{
			echo "no nuevo<br>";	
		}
		$subdm->set_data("a","a.b");
		if($subdm->isNew()){
			echo "nuevo<br>";	
		}else{
			echo "no nuevo<br>";	
		}
		//$subdm->save();
		mw_array2list_echo($subdm->get_sub_data_debug_info("a"));
		mw_array2list_echo($subdm->get_sub_data_debug_info("a.b"));
		mw_array2list_echo($subdm->get_sub_data_debug_info("b"));
		mw_array2list_echo($subdm->get_sub_data_debug_info("b.b"));
		mw_array2list_echo($subdm->get_sub_data_debug_info());
		
		$ss=$subdm->get_sub_item("a");
		mw_array2list_echo($ss->get_sub_data_debug_info("a"));
		mw_array2list_echo($ss->get_sub_data_debug_info("a.b"));
		mw_array2list_echo($ss->get_sub_data_debug_info("b"));
		mw_array2list_echo($ss->get_sub_data_debug_info("b.b"));
		mw_array2list_echo($ss->get_sub_data_debug_info());
		if($ss->isNew()){
			echo "ss nuevo<br>";	
		}else{
			echo "ss no nuevo<br>";	
		}
		
		$ss=$subdm->get_sub_item("c");
		mw_array2list_echo($ss->get_sub_data_debug_info());
		if($ss->isNew()){
			echo "ss nuevo<br>";	
		}else{
			echo "ss no nuevo<br>";	
		}
		$ss->set_data("x","a.b");
		//$ss->save();
		
		$str=$dm->getItemStr("str","q/r");
		if($str->isNew()){
			echo "nuevo<br>";	
		}else{
			echo "no nuevo<br>";	
		}
		$str->set_data("");
		if($str->isNew()){
			echo "nuevo<br>";	
		}else{
			echo "no nuevo<br>";	
		}
		//$str->save();
		
		
		mw_array2list_echo($dm->get_debug_data());

		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>