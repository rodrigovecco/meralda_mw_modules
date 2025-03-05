<?php
class mwmod_mw_ui_debug_tests_dbextradata extends mwmod_mw_ui_sub_uiabs{
	function __construct($cod,$parent){
		$this->init_as_subinterface($cod,$parent);
		
		$this->set_def_title("Datos adicionales db");
		
	}
	
	function do_exec_page_in(){
		/*
		
		$query=new mwmod_mw_db_sql_query("ffii_funds");
		$query->select->add_select("ffii_funds.*");
		$query->select->add_select("count(ffii_companies.id)","numcomps");
		$query->from->add_from_join_external("ffii_companies","ffii_funds.id","id_fund");
		$query->group->add_group("ffii_funds.id","id");
		echo $query->get_sql();
		$tblman=$query->dbman->get_tbl_manager("ffii_funds");
		$tblman->get_item(8);
		$tblman->get_item(1);
		$tblman->get_items_by_sql($query->get_sql(),true);
		$d=array();
		if($items=$tblman->get_loaded_items()){
			foreach($items as $id=>$item){
				$d[$id]=$item->get_data();
			}
		}
		mw_array2list_echo($d);
		*/
		
		
		
		

		
	}
	function do_exec_no_sub_interface(){
		//$this->__get_priv_maininterface();
		//$this->x
		
		
		
		
		
		
	}
	function is_allowed(){
		return $this->allow("debug");	
	}
	
}
?>