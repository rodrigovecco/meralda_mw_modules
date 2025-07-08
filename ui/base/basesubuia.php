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
		
		
		$MainContainer=$this->get_ui_dom_elem_container();
		$container=$MainContainer;
		if($this->mainPanelEnabled){
			
			if($mainpanel=$this->createMainPanel()){
				$MainContainer->add_cont($mainpanel);
				$container=$mainpanel->panel_body->add_cont_elem();
			}
		
		}
		
		$sbucontainer=$container->add_cont_elem();
		$sbucontainer1=$sbucontainer->add_cont_elem();
		//echo "<div class='card'><div class='card-body'>";
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			$listcontainer=$sbucontainer1->add_cont_elem();
			$listcontainer->addClass("list-group");
			//echo "<div class='list-group'>";
			foreach($subs as $su){
				$listcontainer->add_cont("<a href='".$su->get_url()."' class='list-group-item list-group-item-action'>".$su->get_mnu_lbl()."</a>");	
			}
			//echo "</div>";
			
		}
		echo $MainContainer->get_as_html();

		//echo "</div></div>";
		
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