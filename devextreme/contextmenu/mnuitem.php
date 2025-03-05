<?php
class mwmod_mw_devextreme_contextmenu_mnuitem extends mwmod_mw_jsobj_objcoladv_base{
	function __construct($cod,$objclass="mw_dx_context_menu_item"){
		$this->def_js_class="mw_dx_context_menu_item";
		$this->init_obj($cod,$objclass);
		
	}
	function setLinkModeNewWindow($url,$target="_blank",$objclass="mw_dx_context_menu_item_link"){
		return $this->setLinkMode($url,$target,$objclass);
	}
	function setLinkMode($url,$target=false,$objclass="mw_dx_context_menu_item_link"){
		//no probado
		if($objclass){
			$this->set_js_class($objclass);			
		}
		if($url){
			$this->set_prop("link_mode.url",$url);	
		}
		if($target){
			$this->set_prop("link_mode.target",$target);	
		}
		return true;
	}
	function onSetDxOptions($fnc=false){
		if(!$fnc){
			$fnc=new mwmod_mw_jsobj_functionext();
			$fnc->add_fnc_arg("options");	
			$fnc->add_fnc_arg("elem");	
			$fnc->add_fnc_arg("man");	
		}
		$this->set_prop("onSetDxOptions",$fnc);
		return $fnc;
	}
	
	
	function onClick($fnc=false){
		$addargs=true;
		if($fnc){
			$addargs=false;	
		}
		$r=$this->addEventListener("click",$fnc);
		if($addargs){
			$r->add_fnc_arg("e");	
		}
		return $r;
	}
	function add_mnu_item($cod,$lbl=false,$objclass=false){
		if(!$ch=$this->add_new_child($cod,$objclass)){
			return false;
		}
		if($lbl){
			$ch->set_prop("options.text",$lbl);	
		}
		return $ch;
	}
	
	function create_new_child($cod,$objclass=false){
		$ch=new mwmod_mw_devextreme_contextmenu_mnuitem($cod,$objclass);
		return $ch;	
	}

	
}
?>