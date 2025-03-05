<?php
class mwmod_mw_bootstrap_html_grid_colclass extends mw_apsubbaseobj{
	var $mode;
	var $width=12;
	var $offset=false;
	var $pull=false;
	var $push=false;
	var $active=false;
	function __construct($mode){
		$this->mode=$mode;
	}
	function set_width($v=12){
		$this->active=true;
		$this->width=$v;
		
	}
	function set_offset($v=1){
		$this->active=true;
		$this->offset=$v;
		
	}
	function set_pull($v=1){
		$this->active=true;
		$this->pull=$v;
		
	}
	function set_push($v=1){
		$this->active=true;
		$this->push=$v;
		
	}
	function add_2_class_list(&$list){
		if(!$this->active){
			return false;
		}
		
		$list[]="col-".$this->mode."-".$this->width;
		if($this->offset!==false){
			$list[]="col-".$this->mode."-offset-".$this->offset;	
		}
		if($this->pull!==false){
			$list[]="col-".$this->mode."-pull-".$this->pull;	
		}
		if($this->push!==false){
			$list[]="col-".$this->mode."-push-".$this->push;	
		}
	}
	
}

?>