<?php
class mwmod_mw_mapbox_jsinit extends mwmod_mw_html_manager_item_js{
	public $mapboxMan;
	function __construct($man,$cod="mapboxapi"){
		$this->init_item($cod);
		$this->mapboxMan=$man;
		
	}
	function get_src(){
		
		return $this->mapboxMan->get_js_src();
	}
	function get_html_declaration(){
		if(!$this->mapboxMan){
			return "";	
		}
		if(!$this->mapboxMan->isEnabled()){
			return false;	
		}
		//$id=$this->mapboxMan->getAppID();
		$r="<script src='".$this->get_src()."'></script>\n";
		$r.="<link href='https://api.mapbox.com/mapbox-gl-js/v2.4.1/mapbox-gl.css' rel='stylesheet' />\n";
		if($token=$this->mapboxMan->getAppID()){
			$r.="<script>\n";
			$r.="mapboxgl.accessToken='".mw_text_nl_js($token)."';\n";
			$r.="</script>\n";
				
		}
		return $r;
	}

	
	
	
}
?>