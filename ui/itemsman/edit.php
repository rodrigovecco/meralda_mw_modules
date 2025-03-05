<?php
abstract class mwmod_mw_ui_itemsman_edit extends mwmod_mw_ui_itemsman_itemsmanui{
	function do_exec_no_sub_interface(){
		if(!$this->items_man){
			return false;	
		}
		if(!$item=$this->items_man->get_item($_REQUEST["iditem"])){
			return false;	
		}
		$this->set_current_item($item);
		$this->set_url_param("iditem",$item->get_id());
		if(is_array($_REQUEST["nd"]??null)){
			$item->save($_REQUEST["nd"]);
			
		}
		
	}
	function get_title_for_box(){
		if($item=$this->get_current_item()){
			return $item->get_name();
		}
		return $this->get_title();	
	}
	function do_exec_page_in(){
		if(!$this->items_man){
			return false;	
		}
		if(!$item=$this->get_current_item()){
			return false;
		}
		
		$frm=$this->new_frm();
		$cr=$item->get_datafield_creator();
		$cr->items_pref="nd";
		$cr->add_submit($this->get_msg("Guardar"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();

		
		

		
	}
	function prepare_before_exec_no_sub_interface(){
		$p=new mwmod_mw_html_manager_uipreparers_default($this);
		$p->preapare_ui();
	}
}
?>