<?php
class mwmod_mw_datafield_select extends mwmod_mw_datafield_datafielabs{
	public $optionslist;
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);	
	}
	function set_input_att_value(&$a=array()){
		if($this->readonly){
			$a["disabled"]="disabled";
		}
		return false;
	}
	function create_optionslist($options=false){
		if($options){
			if(is_a($options,"mwmod_mw_listmanager_listman")){
				$this->optionslist= $options; 	
				return 	$this->optionslist;
			}
		}
		
		$this->optionslist= new mwmod_mw_listmanager_listman($options); 	
		return 	$this->optionslist;
	}
	function add_options_items($options){
		$ol=$this->get_optionslist();
		return $ol->add_items($options);
	}
	function add_options_item($item){
		$ol=$this->get_optionslist();
		return $ol->add_item($item);
	}
	function get_optionslist(){
		if(isset($this->optionslist)){
			return 	$this->optionslist;
		}
		return $this->create_optionslist();
		
	}
	function check_optionslist(){
		//sólo para elementos con options lists defnidas;
	}
	
	function get_value_as_html(){
		$this->check_optionslist();
		//$this->get_value();	
		if($this->optionslist){
			if($r=$this->optionslist->get_sel_option_html($this->get_value())){
				return $r;	
			}
		}
		return $this->get_value();	
	}

	function get_input_html(){
		if($this->always_as_html){
			return $this->get_value_as_html();	
		}
		$this->check_optionslist();
		
		
		

		
		$r="<select ".$this->get_input_att().">\n";
		if($this->optionslist){
			$r.=$this->optionslist->get_options_html($this->get_value());
		}
		$r.="</select>\n";
		return $r;
	}


	/*

	function get_input_html(){
		return $r="<textarea ".$this->get_input_att()." >".$this->get_value_for_input()."</textarea>";
	}
	*/

	
}
?>