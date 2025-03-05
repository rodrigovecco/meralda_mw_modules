<?php
abstract class mwmod_mw_ui_itemsman_main extends mwmod_mw_ui_itemsman_itemsmanui{
	function do_exec_no_sub_interface(){
		
	}
	function do_exec_page_in(){
		$this->do_exec_page_in_items_list();
		
	}
	function do_exec_page_in_items_list(){
		if(!$items=$this->get_items()){
			return $this->do_exec_page_in_no_items();	
		}
		$tbl=$this->new_tbl_template();
		$tits=array(
			"id"=>$this->get_msg("ID"),
			"name"=>$this->get_msg("Nombre"),
			"_mnu"=>""
		);
		echo $tbl->get_tbl_open_header_and_set_cols_cods($tits);
		
		foreach($items as $id=>$item){
			$data=$item->get_data();
			$url=$this->get_url_subinterface("edit",array("iditem"=>$id));
			$data["_mnu"]="<a href='$url'>".$this->get_msg("EDITAR")."</a>";
			echo $tbl->get_row_ordered($data);	
		}
		echo $tbl->get_tbl_close();

	}
	function do_exec_page_in_no_items(){
		echo "<p>".$this->get_msg("No hay elementos.")."</p>";	
	}
	function get_items(){
		if(!$this->items_man){
			return false;	
		}
		if($items=$this->items_man->get_all_items()){
			return $items;	
		}
		return false;	
	
			
	}
	
}
?>