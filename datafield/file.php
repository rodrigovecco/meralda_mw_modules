<?php
class mwmod_mw_datafield_file extends mwmod_mw_datafield_input{
	function __construct($name,$lbl=false,$value=NULL,$req=false,$att=array(),$style=array()){
		$this->init($name,$lbl,$value,$req,$att,$style);
		//$this->set_def_params();
	}
	
	function get_existing_file_url(){
		if(!$v=$this->get_value()){
			return false;
		}
		if(!is_array($v)){
			return false;
		}
		return $v["url"];
		
	}
	function get_existing_file_target(){
		if($this->link_target){
			return $this->link_target;		
		}
		return "_blank";
	}
	function get_existing_file_link(){
		if(!$url=$this->get_existing_file_url()){
			return false;	
		}
		$txt=$this->get_existing_file_link_txt();
		$t=$this->get_existing_file_target();
		return "<a href='$url' target='$t'>$txt</a>";
		
	}
	function get_existing_file_link_txt(){
		if($this->link_txt){
			return $this->link_txt;	
		}
		if(!$url=$this->get_existing_file_url()){
			return false;	
		}
		return basename($url);
	}
	function get_input_html_existing(){
		if(!$l=$this->get_existing_file_link()){
			return false;	
		}
		return "<div>$l</div>";
	}
	function get_input_html_delete(){
		if(!$this->file_exists()){
			return false;	
		}
		if(!$this->add_delete_input){
			return false;	
		}
		$r=" <span>";
		$id=$this->get_frm_subfield_name("delete");
		$r.="<label for='$id'>";
		$r.=$this->get_msg("Eliminar:")." ";
		$r.="</label>";
		$r.="<input type='checkbox' id='$id' name='$id' value='1'>";
		$r.="</span>";
		
		return $r;
	}
	function file_exists(){
		return $this->get_existing_file_url();
		
	}
	function get_input_file_name(){
		if($this->input_file_name){
			return $this->input_file_name;	
		}
		$code=$this->get_frm_field_name();
		$code=str_replace("[","",$code);
		$code=str_replace("]","",$code);
		$code.="_file";
		return $code;
		
		
			
	}
	function get_input_html_params(){
		$r=$this->get_html_input_param("fileinputname",$this->get_input_file_name());
		return $r;	
	}
	function get_input_html_file(){
		$name=$this->get_input_file_name();
		$m="";
		if($this->multiple){
			return "<input type='file' name='{$name}[]' multiple='multiple'>";
			//$m="multiple='multiple'";	
		}
		return "<input type='file' name='$name' >";
		
	}
	function get_input_html(){
		$r=$this->get_input_html_existing();
		$r.=$this->get_input_html_file();
		$r.=$this->get_input_html_delete();
		$r.=$this->get_input_html_params();
		
		return $r;
	}

	

}
?>