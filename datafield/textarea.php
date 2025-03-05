<?php
class mwmod_mw_datafield_textarea extends mwmod_mw_datafield_input{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		//$this->set_def_params();
	}
	function set_input_att_value(&$a=array()){
		return false;
	}	

	function get_input_html(){
		$v=$this->value;
		if($this->fix_slashes_and_quotes){
			$v=$this->fix_slashes_and_quoutes_str($v);
		}
		
		
		return $r="<textarea ".$this->get_input_att()." >".$v."</textarea>";
	}
	

}
?>