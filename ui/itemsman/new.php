<?php
abstract class mwmod_mw_ui_itemsman_new extends mwmod_mw_ui_itemsman_itemsmanui{
	
	function on_new_item_created($item){
		if(!$item){
			return false;	
		}
		$this->go_to_parent();
					
			
		return $item;
	
	}
	function create_item_on_request(){
		if(!$this->items_man){
			return false;	
		}
		//todo: use input manager!
		if(is_array($_REQUEST["ndnew"])){
			if(is_array($_REQUEST["ndnew"])){
				if($item=$this->items_man->create_new_item_from_full_input($_REQUEST["ndnew"])){
					return $this->on_new_item_created($item);	
				}
			}
			
		}
		
		
		//	
	}
	function do_exec_no_sub_interface(){
		if($this->create_item_on_request()){
			return;	
		}
			
	}
	function get_new_item_datafield_creator(){
		if(!$this->items_man){
			return false;	
		}
		if($cr=$this->items_man->get_new_item_datafield_creator()){
			return $cr;
		}
			
	}

	function do_exec_page_in(){
		if(!$cr=$this->get_new_item_datafield_creator()){
			return false;
		}
		
		$frm=$this->new_frm();
		$cr->items_pref="ndnew";
		$cr->add_submit($this->get_msg("Crear"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();

		
		

		
	}
}
?>