<?php
abstract class mwmod_mw_ui_itemsman_cfg extends mwmod_mw_ui_itemsman_itemsmanui{
	function do_exec_no_sub_interface(){
		if(!$this->items_man){
			return false;	
		}
		//use input manager!!!
		//todo
		if(is_array($_REQUEST["nd"])){
			$this->save_cfg($_REQUEST["nd"]);
			
		}
		
		
	}
	
	function save_cfg($input){
		if(!$this->items_man){
			return false;	
		}
		if(!is_array($input)){
			return false;
		}
		if($td=$this->items_man->get_treedata_item("cfg")){
			$td->set_data($input);
			$td->save();
		}
		
	}
	function set_datafield_creator_cfg($cr){
		//extender	
	}
	function get_datafield_creator_cfg(){
		$cr=new mwmod_mw_datafield_creator();
		$this->set_datafield_creator_cfg($cr);
		if($this->items_man){
			if($td=$this->items_man->get_treedata_item("cfg")){
				$cr->set_value($td->get_data());
			}
				
		}
		return $cr;
	
	}

	function do_exec_page_in(){
		
		
		$frm=$this->new_frm();
		$cr=$this->get_datafield_creator_cfg();
		$cr->items_pref="nd";
		$cr->add_submit($this->get_msg("Guardar"));
		$frm->set_datafieldcreator($cr);
		echo $frm->get_html();
		
		

		
	}
	
	function is_allowed(){
		if(!$this->items_man){
			return false;	
		}
		return $this->items_man->allow_cfg();
	}
	
}
?>