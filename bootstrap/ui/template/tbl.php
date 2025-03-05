<?php
class  mwmod_mw_bootstrap_ui_template_tbl extends mwmod_mw_templates_tbl{
	//public $bt_tbl_class="table table-hover";
	public $bt_tbl_class="table table-striped table-bordered table-hover";
	
	
	function __construct($bt_tbl_class=false){
		if($bt_tbl_class){
			$this->bt_tbl_class=$bt_tbl_class;	
		}
	}
	function get_tbl_open(){
		return "<table class='".$this->bt_tbl_class."'>\n";
			
	}
	function get_tbl_close(){
		return "</table>\n";
			
	}
	function get_next_tr_open(){
		return "<tr>\n";
			
	}
	


	
}
?>