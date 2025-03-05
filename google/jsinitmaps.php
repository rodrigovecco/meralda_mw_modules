<?php
class mwmod_mw_google_jsinitmaps extends mwmod_mw_html_manager_item_jsexternal{
	public $libraries;
	function __construct($cod,$src){
		$this->init_item($cod);
		$this->extenal_src=$src;
	}
	function get_html_declaration(){
		return "<script type='text/javascript'  language='javascript' src='".$this->get_src()."' defer></script>\n";	
	}
	function get_src(){
		$r=$this->extenal_src;
		if($this->libraries){
			$r.="&libraries=".implode(",",$this->libraries);
		}	
		return $r;
	}
	function addLibraries($list){
		if($list){
			if(is_string($list)){
				$list=explode(",",$list);
			}
			if(is_array($list)){
				foreach($list as $l){
					$this->addLibrary($l);
				}
			}
		}
	}
	function addLibrary($library){
		if(is_string($library)){
			if($library=trim($library)){
				if(!$this->libraries){
					$this->libraries=array();
				}
				$this->libraries[$library]=$library;
			}
		}
	}

	
	
	
}
?>