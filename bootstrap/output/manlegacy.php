<?php
class mwmod_mw_bootstrap_output_manlegacy extends mw_apsubbaseobj{
	//var $panel=true;
	public $panel=true;//20231021
	function __construct(){
		//	
	}
	function get_frm_class(){
		return false;	
	}
	function get_html_frm($frm){
		$r=$this->get_html_frm_before($frm);	
		$r.=$this->get_html_frm_open($frm);	
		$r.=$this->get_html_frm_in($frm);	
		$r.=$this->get_html_frm_close($frm);	
		$r.=$this->get_html_frm_after($frm);	
		$r.=$this->get_html_frm_jsscript($frm);
		return $r;

	}
	function get_html_frm_before($frm){
		if(!$this->panel){
			return "";	
		}
		$html="<div class='card card-default'>\n";
		if($t=$frm->get_html_title()){
			$html.="<div class='card-header'>\n";
			$html.=$t;
			$html.="</div>\n";
		}
		$html.="<div class='card-body'>\n";
		return $html;
	}
	function get_html_frm_after($frm){
		if(!$this->panel){
			return "";	
		}
		$html="</div>\n";
		$html.="</div>\n";
		return $html;
	}
	function get_html_frm_open($frm){
		$r= "<form  id='".$frm->get_frm_id()."' name='".$frm->get_frm_name()."'";
		$r.=" method='".$frm->method."' action='".$frm->get_action()."' enctype='".$frm->enctype."' autocomplete='off' ";
		if($c=$this->get_frm_class()){
			$r.=" class='$c' ";	
				
		}
		if($c=$frm->target){
			$r.=" target='$c' ";		
		}
		$r.=">\n";
		return $r;
			
	}
	function get_html_frm_close($frm){
		$r= "</form>\n";
		return $r;
	}
	function get_html_frm_in($frm){
		if(!$cr=$frm->get_datafieldcreator()){
			return "";
		}
		if(!$items=$cr->get_items()){
			return "";	
		}
		$r="";
		
		
		foreach ($items as $i){
			$r.=$i->get_html_bootstrap($this);	
		}
		
		return $r;
	
	}
	function get_html_frm_jsscript($frm){
		if(!$js=$this->get_frm_jsscript($frm)){
			return "";	
		}
		$html="\n<script language='javascript'  type='text/jscript'>\n";
		$html.=$js;
		$html.="</script>\n";
		//$html.=nl2br($js);
		return $html;

	}
	function get_frm_jsscript($frm){
		$js="";
		$frm->bootstrap_req_classes_reset();
		$frm->bootstrap_after_create_reset();
		if($cr=$frm->get_datafieldcreator()){
			$cr->prepare_js_bootstrap($frm);
		}
		$classes=new mwmod_mw_jsobj_array();
		if($elems=$frm->get_bootstrap_req_classes()){
			foreach($elems as $e){
				$classes->add_data($e);	
			}
		}
		$fnc=$frm->get_bootstrap_after_create_add_inputs_fnc();
		$fnc->add_fnc_arg("frmman");
		$list=$frm->get_bootstrap_after_create_add_inputs();
		$fnc->set_js_code_in("frmman.add_input_managers_bt_mode(".$list->get_as_js_val().")");
		$jsafter=$frm->get_bootstrap_after_create();
		if($final=$frm->get_bootstrap_after_create_final_fncs_list()){
			foreach($final as $f){
				$jsafter->add_data($f);	
			}
		}
		$js.="___mw_main_frm_manager.create_frm_manager_bt_mode('".$frm->get_frm_name()."',";
		$js.=$classes->get_as_js_val().",";
		$js.=$jsafter->get_as_js_val()."";
		$js.=");\n";
		return $js;
	
	}
	function get_html_group_with_title($input){
		//falta ocultar etc
		if(!$lbl=$input->get_lbl()){
			return $input->get_html_bootstrap_children($this);
		}
		$r="";
		$gr_id=$input->get_frm_field_id_plain()."_pgr";
		$p_id=$input->get_frm_field_id_plain()."_p";
		$r.="<div class='card-group' id='$gr_id'>\n";
		$r.="<div class='card card-default'>\n";
		
		$r.="<div class='card-header'>\n";
		$r.="<div data-toggle='collapse' data-parent='#$gr_id' href='#$p_id' style='cursor:pointer' class='' aria-expanded='true'>\n";
		//<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="" aria-expanded="true">Collapsible Group Item #2</a>
		$r.="$lbl";
		$r.="</div>\n";
		$r.="</div>\n";
		
		$r.="<div  id='$p_id' class='card-collapse collapse show' aria-expanded='true'>\n";
		$r.="<div class='card-body'>\n";
		$r.= $input->get_html_bootstrap_children($this);
		$r.="</div>\n";
		$r.="</div>\n";
		
		$r.="</div>\n";
		$r.="</div>\n";
		return $r;
		
		
	}
	function get_full_btnsgroup_html($input){
		$r="";
		$r.="<div class='mw-subinterface-btns-container'>\n";
		$r.= $input->get_html_bootstrap_children($this);
		$r.="</div>\n";
		return $r;
		
		
	}
	
	function get_html_btn($input){
		$r="<div class='form-group'>\n";
		$r.=$this->get_html_btn_crt($input);
		$r.="</div>\n";
		return $r;
		
	}
	
	function get_html_btn_crt($input){
		
		$params=$input->get_bootstrap_params();
		if($v=$params->get_prop("btn.class")){
			$input->att["class"]=$v;
		}else{
			$input->att["class"]="btn btn-default";
				
		}
		$r=$input->get_input_html();
		return $r;
	}
	function get_html_checkbox($input){
		if($input->output_as_html){
			return $this->get_html_def($input);	
		}
		
		$r="<div class='form-group'>\n";
		$r.="<div class='form-check'>\n";
		$r.=$this->get_html_def_crt($input);
		$lbl=$input->get_lbl();
		$id=$input->get_frm_field_id();
		$r.="<label class='form-check-label' for='$id'>";
		$r.="$lbl</label>\n";	
		$r.="</div>\n";
		$r.="</div>\n";
		return $r;
		
	}
	function get_html_date($input){
		$r="<div class='form-group'>\n";
		if(!$input->omit_lbl){
			$r.=$this->get_html_def_lbl($input);
		}
		$idgr=$input->get_frm_field_id_plain()."_gr";
		$r.="<div class='input-group date' id='$idgr'>\n";
		$r.="<input type='text' class='form-control' ";
		if($input->disabled){
			$r.=" disabled='disabled' ";
		}
		if($input->readonly){
			$r.=" readonly='readonly' ";
		}
		if($input->req){
			$r.=" required='required' ";
		}

		$r.=">";
		$r.="<span  class='input-group-addon'>";
		$r.="<span  class='glyphicon glyphicon-calendar'>";
		$r.="</span></span>";
		$r.="</div>\n";
		$r.="<input id='".$input->get_frm_field_id()."'  name='".$input->get_frm_field_name()."' ";
		$r.=" value ='".$input->get_value_for_input()."' ";
		$r.=">";
		$r.="</div>\n";
		return $r;
		
	}
	/*
	function get_html_as_html($input){
		$r="<div class='form-group'>\n";
		if(!$input->omit_lbl){
			$r.=$this->get_html_def_lbl($input);
		}
		$r.=$this->get_html_crt_as_html($input);
		$r.="</div>\n";
		return $r;
		
	}
	function get_html_crt_as_html($input){
		
		$params=$input->get_bootstrap_params();
		if($v=$params->get_prop("input.class")){
			$input->att["class"]=$v;
		}else{
			$input->att["class"]="form-control";
				
		}
		if($pl=$this->get_placeholder($input)){
			$input->att["placeholder"]=htmlspecialchars($pl);	
		}
		$r=$input->get_input_html()."ddd";
		return $r;
	}
	*/
	
	function get_html_def($input){
		$r="<div class='form-group'>\n";
		if(!$input->omit_lbl){
			$r.=$this->get_html_def_lbl($input);
		}
		$r.=$this->get_html_def_crt($input);
		$r.="</div>\n";
		return $r;
		
	}
	function get_placeholder($input){
		return $input->placeholder;
	}
	function get_html_def_crt($input){
		
		$params=$input->get_bootstrap_params();
		if($v=$params->get_prop("input.class")){
			$input->att["class"]=$v;
		}else{
			$input->att["class"]="form-control";
				
		}
		if($pl=$this->get_placeholder($input)){
			$input->att["placeholder"]=htmlspecialchars($pl);	
		}
		if($input->output_as_html){
			return $input->get_value_as_html();	
		}
		$r=$input->get_input_html();
		return $r;
	}
	function get_html_def_lbl($input){
		if(!$lbl=$input->get_lbl()){
			return false;
		}
		$id=$input->get_frm_field_id();
		return "<label for='$id'>$lbl</label>\n";	
		
	}
	
}
?>