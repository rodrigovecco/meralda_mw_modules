<?php
/**
 * Base class for DevExtreme-compatible elements with support for JS configuration.
 *
 * @property-read string $cod Element identifier code.
 * @property-read mwmod_mw_jsobj_obj $js_data JavaScript configuration object for DevExtreme integration.
 */
abstract class mwmod_mw_devextreme_elem extends mw_apsubbaseobj{
	private $js_data;
	private $cod;
	var $active=true;
	function is_active(){
		return $this->active;	
	}
	function get_cod(){
		return $this->cod;	
	}
	function get_ready_js_data(){
		$this->__get_priv_js_data();
		return $this->js_data;	
	}
	final function set_cod($cod){
		$this->cod=$cod;
	}
	final function __get_priv_cod(){
		return $this->cod;
	}
	final function __get_priv_js_data(){
		if(!$this->js_data){
			$this->js_data=new mwmod_mw_jsobj_obj();
		}
		return $this->js_data;
	}
}
?>