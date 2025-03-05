<?php

class mwmod_mw_datafield_template extends mwmod_mw_templates_basetemplate{
	function __construct($htmlclasspref="sys_inteface"){
		if($htmlclasspref){
			$this->htmlclasspref=$htmlclasspref;	
		}
		$this->init();
	}
	
	final function init(){
		$this->set_mainap();
	}
	function get_full_btn_html($datafield){
		
		return $this->get_html_tag_open("btn").$datafield->get_input_html()."</div>";	
		
			
	}
	function get_full_btnsgroup_html($datafield){
		$r.=$this->get_html_tag_open("btns_group");
		$r.=$datafield->get_full_inputs_children_html();
		$r.="</div>\n";
		return $r;
			
	}
	
	
	function get_full_group_html($datafield){
		//corregir!!!!
		$r.=$this->get_html_tag_open("input_group");
		//"<div class='sys_inteface_input_group'>";
		$id=$datafield->get_frm_field_id();
		if($lbl=$datafield->get_lbl()){
			if($datafield->use_hide_show_children()){
				$r.="<div class='".$this->get_html_class("input_group_title_visible")."' onclick=\"mw_toggle_visible_elemid_by_id(this,'$id','".$this->get_html_class("input_group_title_visible")."','".$this->get_html_class("input_group_title_hidden")."');\">";
				$r.=$lbl;
				$r.="</div>";
					
			}else{
				$r.=$this->get_html_tag_open("input_group_title");
				//$r.="<div class='sys_inteface_input_group_title'>";
				$r.=$lbl;
				$r.="</div>";
					
			}
			/*
			*/
			
		}
		$r.="<div class='".$this->get_html_class("input_group_body")."' id='$id'>";
		$r.=$datafield->get_full_inputs_children_html();
		$r.="</div>\n";
		$r.="</div>\n";
		return $r;
			
	}
	function get_full_input_html($datafield){
		$r.=$this->get_full_input_html_open();
		$r.=$this->get_input_html_lbl($datafield);
		$r.=$this->get_input_html_ctr($datafield);
		$r.=$this->get_full_input_html_close();
		return $r;
			
	}
	function get_input_html_ctr($datafield){
		return $this->get_html_tag_open("input_ctr").$datafield->get_input_html()."</div>";	
	}
	function get_input_html_lbl($datafield){
		if(!$lbl=$datafield->get_lbl()){
			return false;
		}
		$id=$datafield->get_frm_field_id();
		return $this->get_html_tag_open("input_lbl")."<label for='$id'>$lbl:</label></div>";	
	}
	function get_full_input_html_open(){
		return 	$this->get_html_tag_open("input_field");
	}
	function get_full_input_html_close(){
		return 	"</div>\n";
	}

	
	function get_date_def_format(){
		return 	"d-m-Y";
	}
	function format_date($date,$format=false){
		if(!$format){
			$format=$this->get_date_def_format();
		}
		if(!$date){
			return false;	
		}
		if($date=="0000-00-00 00:00:00"){
			return false;	
		}
		if($date=="0000-00-00"){
			return false;	
		}
		if(!$time=strtotime($date)){
			return false;	
		}
		return date($format,$time);
	}
	
	
	function __call($a,$b){
		return false;	
	}

	
	
}
?>