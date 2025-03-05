<?php
//
class mwmod_mw_users_user extends mwmod_mw_users_userabs{
	function __construct($man,$tblitem){
		$this->init($man,$tblitem);	
	}
	function on_login(){
		$nd=array();
		$nd["last_login_date"]=date('Y-m-d H:i:s') ;
		$nd["last_login_ip"]=$_SERVER['REMOTE_ADDR'];
		$this->tblitem->do_update($nd);
	}
	function can_create_jsondata(){
		return true;	
	}
	function can_create_treedata(){
		return true;	
	}
	function can_create_strdata(){
		return true;
	}


}
?>