<?php
//
class mwmod_mw_users_list_item extends mwmod_mw_users_list_itemabs{
	function __construct($user_id=false){
		if($user_id){
			$this->set_orig_user_id($user_id);	
		}
	}
	


}
?>