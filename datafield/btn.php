<?php
class mwmod_mw_datafield_btn extends mwmod_mw_datafield_datafielabs{
	var $input_type="button";
	private $_btn_html_elem;
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);	
	}
	function create_btn_html_elem(){
		$btn= new mwmod_mw_bootstrap_html_specialelem_inputbtn();
		
		$this->init_btn_html_elem($btn);
		
		return $btn;
	}
	function init_btn_html_elem($btn){
		//	
	}
	function fix_btn_html_elem_before_output($btn){
		if(!$btn->get_att("id")){
			if($id=$this->get_frm_field_id()){
				$btn->set_att("id",$id);
			}
		}
		if(!$btn->get_att("name")){
			if($id=$this->get_frm_field_name()){
				$btn->set_att("name",$id);
			}
		}
		
		if(!$btn->get_att("value")){
			if($id=$this->get_lbl()){
				$btn->set_att("value",$id);
			}
		}
		if($this->disabled){
			$btn->set_att("disabled","disabled");
		}
		$btn->set_att("type",$this->input_type);

	}
	function get_btn_html_elem_for_output(){
		if(!$e=$this->get_btn_html_elem()){
			return false;	
		}
		$this->fix_btn_html_elem_before_output($e);
		return $e;
	}
	final function get_btn_html_elem(){
		if(!isset($this->_btn_html_elem)){
			$this->_btn_html_elem=$this->create_btn_html_elem();	
		}
		return $this->_btn_html_elem;
	}
	function get_html_bootstrap($bt_output_man){
		if($elem=$this->get_btn_html_elem_for_output()){
			return $elem->get_as_html();
		}
		
		return $bt_output_man->get_html_btn($this);
	}

	
	function get_full_input_html(){
		if(!$t=$this->get_template()){
			return false;	
		}
		return $t->get_full_btn_html($this);
	}

	
	
	function get_input_att_as_array(){
		$a=$this->att;
		if(!is_array($a)){
			$a=array();	
		}
		$a["type"]=$this->input_type;	
		if($id=$this->get_frm_field_id()){
			$a["id"]=$id;	
		}
		if($name=$this->get_frm_field_name()){
			$a["name"]=$name;	
		}
		if($lbl=$this->get_lbl()){
			$a["value"]=$lbl;	
		}
		if($this->disabled){
			$a["disabled"]="disabled";	
		}
		if($s=$this->get_input_style_att()){
			$a["style"]=$s;		
		}
		
		return $a;
	}

}
?>