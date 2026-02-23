<?php
/**
 * Base subinterface class for admin sections with list-based navigation.
 *
 * Extends basesubui to provide automatic rendering of subinterfaces as a list group,
 * with dropdown menu support and submenu responsibility.
 */
abstract class mwmod_mw_ui_base_basesubuia extends mwmod_mw_ui_base_basesubui{
	/**
	 * Indicates whether child subinterfaces can be created by code.
	 *
	 * Override in child classes to control dynamic subinterface creation.
	 *
	 * @return bool True to allow creation, false otherwise.
	 */
	function allowcreatesubinterfacechildbycode(){
		
		return true;	
	}
	
	/**
	 * Adds this subinterface as a dropdown menu item to the side menu.
	 *
	 * Creates a dropdown menu item with the subinterface's code and label,
	 * prepares it, and adds it to the provided menu.
	 *
	 * @param mwmod_mw_mnu_mnu $mnu The menu to add the dropdown item to.
	 * @return void
	 */
	function add_mnu_items_side($mnu){
		
		
		$mnuitem=new mwmod_mw_mnu_items_dropdown1($this->get_cod_for_mnu(),$this->get_mnu_lbl(),$mnu);
		$this->prepare_mnu_item($mnuitem);
		$mnu->add_item_by_item($mnuitem);
	}
	
	/**
	 * Renders the page content with a list of subinterfaces.
	 *
	 * Creates a container (optionally with a main panel) and renders all subinterfaces
	 * from the sucods property as a Bootstrap list group with clickable links.
	 *
	 * @return void Outputs HTML directly.
	 */
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
	
	/**
	 * Creates a menu for subinterfaces defined in the sucods property.
	 *
	 * Retrieves all subinterfaces by their comma-separated codes in sucods
	 * and asks each to add itself to the menu.
	 *
	 * @param mwmod_mw_ui_sub_uiabs|false $su Optional subinterface parameter (unused).
	 * @return mwmod_mw_mnu_mnu The created menu with subinterface items.
	 */
	function create_sub_interface_mnu_for_sub_interface($su=false){
		$mnu = new mwmod_mw_mnu_mnu();

		if($this->mnuStructure){
			
			$this->add_mnu_items_by_structure($mnu,$this->mnuStructure);
		}
		
		if($subs=$this->get_subinterfaces_by_code($this->sucods,true)){
			
			foreach($subs as $su){
				$su->add_2_sub_interface_mnu($mnu);	
			}
		}
		
		
		return $mnu;
	}
	function add_mnu_items_by_structure($mnu,$structure){
		if(!is_array($structure)){
			return;
		}

		
		foreach($structure as $cod=>$structureData){
			if(is_string($structureData)){
				if($subs=$this->get_subinterfaces_by_code($structureData)){
					foreach($subs as $su){
						$su->add_2_sub_interface_mnu($mnu);	
					}
				}
			}else if(is_array($structureData)){
				if(isset($structureData["subbui"])){
					if($subs=$this->get_subinterfaces_by_code($structureData["subbui"],true)){
						$lbl=isset($structureData["lbl"])?$structureData["lbl"]:$cod;
						$item=new mwmod_mw_mnu_items_dropdown_single($this->get_cod_for_mnu()."_".$cod,$lbl,$mnu,false);
						$mnu->add_item_by_item($item);


						
						foreach($subs as $su){
							$sitem=new mwmod_mw_mnu_mnuitem($su->get_cod_for_mnu(),$su->get_mnu_lbl(),$item,$su->get_url());
							$item->add_item_by_item($sitem);
							if($su->is_current()){
								$sitem->active=true;	
							}
						}
						//$su->add_2_sub_interface_mnu($mnu);
					}
					
				}				
			}
			
		}
		
		
	}
	
	/**
	 * Indicates that this subinterface is responsible for managing its submenu.
	 *
	 * When true, this subinterface handles the creation and management of
	 * subinterface menus rather than delegating to the parent.
	 *
	 * @return bool Always returns true.
	 */
	function is_responsable_for_sub_interface_mnu(){
		return true;	
	}
	
}
?>