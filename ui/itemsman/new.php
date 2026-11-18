<?php
/**
 * @deprecated Do not extend this class for new screens.
 *
 * It renders the form through new_frm() + a mwmod_mw_datafield_creator, whose
 * markup expects the `___mw_main_frm_manager` JS bundle. Current UIs do not load
 * that bundle, so the form renders but throws
 * `ReferenceError: ___mw_main_frm_manager is not defined` and submits nothing.
 *
 * Build admin forms on mwmod_mw_ui_base_frm with mwmod_mw_jsobj_inputs_*
 * instead. See docs/ai/user-interfaces/jsobj-inputs-pattern.md.
 */
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
	/**
	 * @deprecated Use mwmod_mw_ui_base_frm::set_js_inputs_data() instead. The
	 *             datafield creator it returns cannot be rendered by the current
	 *             UI (missing `___mw_main_frm_manager` JS bundle).
	 * @return mwmod_mw_datafield_creator|false
	 */
	function get_new_item_datafield_creator(){
		if(!$this->items_man){
			return false;	
		}
		if($cr=$this->items_man->get_new_item_datafield_creator()){
			return $cr;
		}
			
	}

	/**
	 * @deprecated Renders a datafield form that never initialises in current
	 *             UIs. Override do_exec_page_in() on a mwmod_mw_ui_base_frm
	 *             subclass instead.
	 */
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