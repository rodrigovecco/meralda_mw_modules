<?php
abstract class mwmod_mw_ui_base_basesubuia extends mwmod_mw_ui_base_basesubui{
	function allowcreatesubinterfacechildbycode(){
		
		return true;	
	}
	function add_mnu_items_side($mnu){
		
		
		$mnuitem=new mwmod_mw_mnu_items_dropdown1($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu);
		$this->prepare_mnu_item($mnuitem);
		$mnu->add_item_by_item($mnuitem);
	}
	function do_exec_page_in(){
		
		echo "<div class='card'><div class='card-body'>";
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			echo "<div class='list-group'>";
			foreach($subs as $su){
				echo "<a href='".$su->get_url()."' class='list-group-item list-group-item-action'>".$su->get_mnu_lbl()."</a>";	
			}
			echo "</div>";
			
		}
		echo "</div></div>";
		
	}
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();
		
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		
		return $mnu;
	}
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	
}
?>