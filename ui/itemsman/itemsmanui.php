<?php
abstract class mwmod_mw_ui_itemsman_itemsmanui extends mwmod_mw_ui_sub_uiabs{
	function is_allowed(){
		if(!$this->items_man){
			return false;	
		}
		return $this->items_man->allow_admin();
	}

}
?>