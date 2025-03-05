<?php
class mwmod_mw_datafield_group extends mwmod_mw_datafield_datafielabs{
	
	var $showtitle=false;
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);	
	}
	function new_js_mw_datainput(){
		$jsinput=new mwmod_mw_jsobj_newobject("mw_datainput_item_group");	
		$jsinput->set_prop("cod",$this->get_cod());
		if(is_array($this->items)){
			reset($this->items);
			$children=new mwmod_mw_jsobj_array();
			$jsinput->set_prop("childrenList",$children);
			
			foreach ($this->items as $i){
				if($ch=$i->get_js_mw_datainput()){
					$children->add_data($ch);	
				}

			}
			reset($this->items);
			
		}
		//$jsinput->set_prop("lbl",$this->get_lbl());
		return $jsinput;
		
	}

	
	function set_title_mode($v=true){
		$this->showtitle=$v;
	}
	function is_group_with_title(){
		return $this->showtitle;	
	}
	function use_hide_show_children(){
		return $this->is_group_with_title();
	}
	
	function get_html_bootstrap($bt_output_man){
		if($this->is_group_with_title()){
			return $bt_output_man->get_html_group_with_title($this);
		}else{
			return $this->get_html_bootstrap_children($bt_output_man);
		}
	}
	


	
	
	function add2jsreqclasseslist(&$list){
		if(is_array($this->items)){
			
			foreach ($this->items as $i){
				$i->add2jsreqclasseslist($list);	
			}
			reset($this->items);
			
		}

		
	}

	function get_js_init_code_for_frm($frm=false){
		
		if(is_array($this->items)){
			foreach ($this->items as $i){
				$r[]=$i->get_js_init_code_for_frm($frm);	
			}
			return $r;
		}
	}

	function get_full_input_html(){
		if($this->is_group_with_title()){
			if(!$t=$this->get_template()){
				return false;	
			}
			return $t->get_full_group_html($this);
	
		}else{
			return $this->get_full_inputs_children_html();
		}
	}

	function set_value($value){
		return $this->set_value_for_children($value);
	}
	
}
?>