<?php
class mwmod_mw_ui_debug_paths extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		$this->set_def_title("Paths");
		
	}
	function do_exec_no_sub_interface(){
	}
	function do_exec_page_in(){
		$data=array();
		if($items=$this->mainap->pathman){
			foreach($items as $cod=>$item){
				$data[$cod]=$item->get_debug_info();	
			}
		}
		
		
		mw_array2list_echo($data,"appaths");
		
	}
	function is_allowed(){
		if($this->parent_subinterface){
			return 	$this->parent_subinterface->is_allowed();
		}
		//return $this->allow("debug");	
	}
	
}
?>